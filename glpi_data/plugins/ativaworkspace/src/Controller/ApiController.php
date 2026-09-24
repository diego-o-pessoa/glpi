<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace\Controller;

use Glpi\Controller\AbstractController;
use GlpiPlugin\Ativaworkspace\EntraStep;
use GlpiPlugin\Ativaworkspace\Event;
use GlpiPlugin\Ativaworkspace\JobStep;
use GlpiPlugin\Ativaworkspace\MachineIdentity;
use GlpiPlugin\Ativaworkspace\ProvisioningEngine;
use GlpiPlugin\Ativaworkspace\WorkspaceConfig;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * API do executor do Workspace (servico Windows nas maquinas).
 *
 * Autenticacao por token Bearer proprio do Workspace (nao usa sessao). Nenhuma
 * credencial Microsoft trafega por aqui: o executor so reporta subestado, o
 * resultado e a PROVA de ingresso (AzureAdJoined + TenantId), nunca senha.
 */
final class ApiController extends AbstractController
{
    private function checkAuth(Request $request): ?JsonResponse
    {
        if (!WorkspaceConfig::apiEnabled()) {
            return $this->error('API_DISABLED', 'A API do Workspace está desabilitada.', 503);
        }
        $header = trim((string) $request->headers->get('Authorization', ''));
        if (!preg_match('/^Bearer\s+([^\s]+)$/D', $header, $matches)) {
            return $this->error('UNAUTHORIZED', 'Token Bearer ausente ou inválido.', 401);
        }
        $expected = WorkspaceConfig::apiToken();
        if ($expected === '' || !hash_equals($expected, $matches[1])) {
            return $this->error('FORBIDDEN', 'Token sem permissão para esta API.', 403);
        }
        return null;
    }

    private function error(string $code, string $message, int $status): JsonResponse
    {
        return new JsonResponse(['error' => ['code' => $code, 'message' => $message]], $status);
    }

    /** Body JSON limitado (o executor nunca manda muito). */
    private function json(Request $request): ?array
    {
        if (strlen($request->getContent()) > 16384) {
            return null;
        }
        try {
            $data = json_decode($request->getContent(), true, 8, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }
        return is_array($data) ? $data : null;
    }

    /**
     * Etapa que o executor deve tratar agora na maquina identificada por {guid}.
     * Sem etapa: 204. Com etapa: os dados nao sensiveis para conduzir o Entra.
     */
    #[Route(
        '/api/v1/machines/{guid}/step',
        name: 'ativaworkspace_api_step',
        requirements: ['guid' => '[a-fA-F0-9-]{16,64}'],
        methods: ['GET']
    )]
    public function step(Request $request, string $guid): Response
    {
        if ($error = $this->checkAuth($request)) {
            return $error;
        }
        $computersId = MachineIdentity::computerFromGuid($guid);
        if ($computersId <= 0) {
            return $this->error(
                'MACHINE_UNKNOWN',
                'Máquina não vinculada de forma única ao inventário. Aguarde o inventário do Wallpaper/Updater e confira o hostname no GLPI.',
                404
            );
        }

        $next = ProvisioningEngine::executorNextStep($computersId);
        if ($next === null) {
            return new JsonResponse(null, 204);
        }

        return new JsonResponse([
            'job_id'  => (int) $next['job']['id'],
            'step_id' => (int) $next['step']['id'],
            'type'    => (string) $next['step']['step_type'],
            'entra'   => $next['payload'],
        ]);
    }

    /**
     * Subestado do executor (PRECHECK, OPENING_*, VERIFYING_JOIN...). Sem segredos.
     */
    #[Route('/api/v1/steps/{stepId}/progress', name: 'ativaworkspace_api_progress', requirements: ['stepId' => '\d+'], methods: ['POST'])]
    public function progress(Request $request, int $stepId): Response
    {
        if ($error = $this->checkAuth($request)) {
            return $error;
        }
        $body = $this->json($request);
        if ($body === null) {
            return $this->error('INVALID_JSON', 'Corpo inválido.', 400);
        }
        $jobId = $this->jobForStep($stepId);
        if ($jobId === 0) {
            return $this->error('STEP_NOT_FOUND', 'Etapa não encontrada.', 404);
        }

        try {
            ProvisioningEngine::executorProgress(
                $jobId,
                $stepId,
                (string) ($body['substate'] ?? ''),
                mb_substr((string) ($body['log'] ?? ''), 0, 200)
            );
        } catch (\RuntimeException $exception) {
            return $this->error('REJECTED', $exception->getMessage(), 409);
        }
        return new JsonResponse(['ok' => true], 202);
    }

    /**
     * Resultado da etapa Entra: WAITING_HUMAN (tela de login pronta), SUCCESS
     * (com prova de ingresso) ou FAILED. Nunca recebe senha.
     */
    #[Route('/api/v1/steps/{stepId}/result', name: 'ativaworkspace_api_result', requirements: ['stepId' => '\d+'], methods: ['POST'])]
    public function result(Request $request, int $stepId): Response
    {
        if ($error = $this->checkAuth($request)) {
            return $error;
        }
        $body = $this->json($request);
        if ($body === null) {
            return $this->error('INVALID_JSON', 'Corpo inválido.', 400);
        }
        $jobId = $this->jobForStep($stepId);
        if ($jobId === 0) {
            return $this->error('STEP_NOT_FOUND', 'Etapa não encontrada.', 404);
        }

        // So metadados nao sensiveis. Qualquer campo estranho e ignorado.
        $meta = [
            'azure_ad_joined' => ($body['azure_ad_joined'] ?? null) === true,
            'device_id'       => mb_substr((string) ($body['device_id'] ?? ''), 0, 64),
            'tenant_id'       => mb_substr((string) ($body['tenant_id'] ?? ''), 0, 64),
        ];
        try {
            ProvisioningEngine::executorResult(
                $jobId,
                $stepId,
                (string) ($body['result'] ?? ''),
                mb_substr((string) ($body['message'] ?? ''), 0, 200),
                $meta
            );
        } catch (\RuntimeException $exception) {
            return $this->error('REJECTED', $exception->getMessage(), 409);
        }
        return new JsonResponse(['ok' => true], 202);
    }

    /** Job atual dono da etapa (a etapa tem que ser a etapa corrente do job). */
    private function jobForStep(int $stepId): int
    {
        global $DB;

        $step = $DB->request(['SELECT' => [JobStep::JOB_FK], 'FROM' => JobStep::getTable(), 'WHERE' => ['id' => $stepId], 'LIMIT' => 1])->current();
        return is_array($step) ? (int) $step[JobStep::JOB_FK] : 0;
    }
}
