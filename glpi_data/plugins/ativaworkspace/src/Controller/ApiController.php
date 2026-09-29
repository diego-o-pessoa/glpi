<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace\Controller;

use Glpi\Controller\AbstractController;
use GlpiPlugin\Ativaworkspace\Application;
use GlpiPlugin\Ativaworkspace\EntraStep;
use GlpiPlugin\Ativaworkspace\Event;
use GlpiPlugin\Ativaworkspace\InstallerStorage;
use GlpiPlugin\Ativaworkspace\Inventory;
use GlpiPlugin\Ativaworkspace\Job;
use GlpiPlugin\Ativaworkspace\JobStep;
use GlpiPlugin\Ativaworkspace\MachineIdentity;
use GlpiPlugin\Ativaworkspace\ProvisioningEngine;
use GlpiPlugin\Ativaworkspace\StepType;
use GlpiPlugin\Ativaworkspace\WorkspaceConfig;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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

        $type = (string) $next['step']['step_type'];
        $response = [
            'job_id'  => (int) $next['job']['id'],
            'step_id' => (int) $next['step']['id'],
            'type'    => $type,
        ];
        // Payload sob a chave do tipo (o executor le a que corresponde).
        $response[$type === \GlpiPlugin\Ativaworkspace\StepType::SOFTWARE ? 'software' : 'entra'] = $next['payload'];
        return new JsonResponse($response);
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
    /**
     * Gera um TAP (senha temporaria) para a conta do provisionamento desta
     * etapa. O executor usa para automatizar o login na tela do Entra. O codigo
     * e de uso unico e nao e registrado.
     */
    #[Route('/api/v1/steps/{stepId}/tap', name: 'ativaworkspace_api_tap', requirements: ['stepId' => '\d+'], methods: ['POST'])]
    public function tap(Request $request, int $stepId): Response
    {
        if ($error = $this->checkAuth($request)) {
            return $error;
        }
        $jobId = $this->jobForStep($stepId);
        if ($jobId === 0) {
            return $this->error('STEP_NOT_FOUND', 'Etapa não encontrada.', 404);
        }
        if (!WorkspaceConfig::graphConfigured()) {
            return $this->error('GRAPH_OFF', 'Microsoft Graph não configurado.', 503);
        }

        global $DB;
        $job = $DB->request(['SELECT' => ['upn'], 'FROM' => \GlpiPlugin\Ativaworkspace\Job::getTable(), 'WHERE' => ['id' => $jobId], 'LIMIT' => 1])->current();
        $upn = is_array($job) ? (string) ($job['upn'] ?? '') : '';
        if ($upn === '') {
            return $this->error('NO_UPN', 'Provisionamento sem conta Microsoft (UPN).', 422);
        }

        try {
            $tap = \GlpiPlugin\Ativaworkspace\GraphClient::createTap($upn);
        } catch (\RuntimeException $exception) {
            Event::log(Event::LEVEL_WARNING, 'entra', 'Falha ao gerar TAP (executor): ' . $exception->getMessage(), [], $jobId, $stepId);
            return $this->error('TAP_FAILED', $exception->getMessage(), 422);
        }

        Event::log(Event::LEVEL_SECURITY, 'entra', 'TAP gerado para o executor', ['validade_min' => $tap['lifetime_minutes']], $jobId, $stepId);
        return new JsonResponse(['upn' => $upn, 'tap' => $tap['code'], 'lifetime_minutes' => $tap['lifetime_minutes']]);
    }

    /**
     * Baixa o instalador enviado do app da etapa SOFTWARE corrente. Token Bearer;
     * o executor confere o SHA-256 (cabecalho) contra o arquivo baixado.
     */
    #[Route('/api/v1/steps/{stepId}/installer', name: 'ativaworkspace_api_installer', requirements: ['stepId' => '\d+'], methods: ['GET'])]
    public function installer(Request $request, int $stepId): Response
    {
        if ($error = $this->checkAuth($request)) {
            return $error;
        }

        global $DB;
        $step = $DB->request(['FROM' => JobStep::getTable(), 'WHERE' => ['id' => $stepId], 'LIMIT' => 1])->current();
        if (!is_array($step) || (string) $step['step_type'] !== StepType::SOFTWARE) {
            return $this->error('NOT_SOFTWARE', 'Etapa não encontrada ou não é de software.', 404);
        }
        // So a etapa corrente do job pode servir o arquivo.
        $jobId = (int) $step[JobStep::JOB_FK];
        $job = $DB->request(['SELECT' => ['plugin_ativaworkspace_jobsteps_id'], 'FROM' => Job::getTable(), 'WHERE' => ['id' => $jobId], 'LIMIT' => 1])->current();
        if (!is_array($job) || (int) $job['plugin_ativaworkspace_jobsteps_id'] !== $stepId) {
            return $this->error('STEP_NOT_CURRENT', 'Esta etapa não está em execução.', 409);
        }

        $app = new Application();
        if (!$app->getFromDB((int) $step['plugin_ativaworkspace_applications_id'])) {
            return $this->error('APP_NOT_FOUND', 'Aplicativo da etapa não encontrado.', 404);
        }
        $stored = (string) ($app->fields['file_stored_name'] ?? '');
        $path = $stored !== '' ? InstallerStorage::path($stored) : null;
        if ($path === null || !is_file($path)) {
            return $this->error('FILE_MISSING', 'Instalador não disponível para esta etapa.', 404);
        }

        $response = new BinaryFileResponse($path);
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('X-Installer-Sha256', (string) ($app->fields['file_sha256'] ?? ''));
        $response->headers->set('Cache-Control', 'no-store');
        return $response;
    }

    /**
     * Inventario da maquina (programas, discos, memoria, CPU, processos).
     * Token Bearer; nenhum segredo trafega. Corpo maior que os demais.
     */
    #[Route('/api/v1/machines/{guid}/inventory', name: 'ativaworkspace_api_inventory', requirements: ['guid' => '[a-fA-F0-9-]{16,64}'], methods: ['POST'])]
    public function inventory(Request $request, string $guid): Response
    {
        if ($error = $this->checkAuth($request)) {
            return $error;
        }
        // Inventario pode passar de 16 KB; aceita ate 512 KB.
        if (strlen($request->getContent()) > 512 * 1024) {
            return $this->error('TOO_LARGE', 'Inventário grande demais.', 413);
        }
        try {
            $data = json_decode($request->getContent(), true, 64, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $this->error('INVALID_JSON', 'Corpo inválido.', 400);
        }
        if (!is_array($data) || !Inventory::store($guid, $data)) {
            return $this->error('MACHINE_UNKNOWN', 'Máquina não vinculada de forma única ao inventário.', 404);
        }
        return new JsonResponse(['ok' => true], 202);
    }

    private function jobForStep(int $stepId): int
    {
        global $DB;

        $step = $DB->request(['SELECT' => [JobStep::JOB_FK], 'FROM' => JobStep::getTable(), 'WHERE' => ['id' => $stepId], 'LIMIT' => 1])->current();
        return is_array($step) ? (int) $step[JobStep::JOB_FK] : 0;
    }
}
