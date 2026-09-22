<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaguardian\Controller;

use Glpi\Controller\AbstractController;
use GlpiPlugin\Ativaguardian\ActionQueue;
use GlpiPlugin\Ativaguardian\ConfigService;
use GlpiPlugin\Ativaguardian\HealthStatus;
use GlpiPlugin\Ativaguardian\MachineRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ApiController extends AbstractController
{
    /** A heartbeat with a generous component set still fits comfortably here. */
    private const MAX_BODY_BYTES = 65536;
    private const MAX_COMPONENTS = 50;

    private function checkAuth(Request $request): ?JsonResponse
    {
        if (!ConfigService::getBool('api_enabled')) {
            return $this->error('API_DISABLED', 'A API do Ativa Guardian esta desabilitada.', 503);
        }

        $header = trim((string) $request->headers->get('Authorization', ''));
        if (!preg_match('/^Bearer\s+([^\s]+)$/D', $header, $matches)) {
            return $this->error('UNAUTHORIZED', 'Token Bearer ausente ou invalido.', 401);
        }

        $expected = (string) ConfigService::get('api_token', '');
        if ($expected === '' || !hash_equals($expected, $matches[1])) {
            return $this->error('FORBIDDEN', 'Token sem permissao para esta API.', 403);
        }

        return null;
    }

    private function error(string $code, string $message, int $status): JsonResponse
    {
        return new JsonResponse(['error' => ['code' => $code, 'message' => $message]], $status);
    }

    #[Route('/api/v1/health', name: 'ativaguardian_api_health', methods: ['GET'])]
    public function health(): Response
    {
        return new JsonResponse([
            'status'         => 'ok',
            'plugin_version' => PLUGIN_ATIVAGUARDIAN_VERSION,
            'api_version'    => '1',
        ]);
    }

    #[Route('/api/v1/heartbeat', name: 'ativaguardian_api_heartbeat', methods: ['POST'])]
    public function heartbeat(Request $request): Response
    {
        if ($error = $this->checkAuth($request)) {
            return $error;
        }

        if (strlen($request->getContent()) > self::MAX_BODY_BYTES) {
            return $this->error('PAYLOAD_TOO_LARGE', 'Heartbeat muito grande.', 413);
        }

        try {
            $payload = json_decode($request->getContent(), true, 16, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $this->error('INVALID_JSON', 'JSON invalido.', 400);
        }
        if (!is_array($payload)) {
            return $this->error('INVALID_PAYLOAD', 'Conteudo invalido.', 400);
        }

        $machineId = trim((string) ($payload['machine_id'] ?? ''));
        $hostname  = trim((string) ($payload['hostname'] ?? ''));
        if (!preg_match('/^[A-Za-z0-9._-]{1,128}$/D', $machineId)) {
            return $this->error('INVALID_PAYLOAD', 'machine_id ausente ou invalido.', 422);
        }
        if ($hostname !== '' && !preg_match('/^[A-Za-z0-9._-]{1,255}$/D', $hostname)) {
            return $this->error('INVALID_PAYLOAD', 'hostname invalido.', 422);
        }

        // Usuario da sessao de console, no formato DOMINIO\usuario. Aceita
        // letras acentuadas e espaco porque nomes de conta os permitem; vazio
        // quando ninguem esta logado.
        $username = trim((string) ($payload['username'] ?? ''));
        if ($username !== '' && !preg_match('/^[\p{L}\p{N} ._\\\\@-]{1,255}$/uD', $username)) {
            return $this->error('INVALID_PAYLOAD', 'username invalido.', 422);
        }

        $guardianVersion = trim((string) ($payload['guardian_version'] ?? ''));
        if (!self::isValidVersion($guardianVersion)) {
            return $this->error('INVALID_VERSION', 'guardian_version invalido.', 422);
        }

        $antivirus = trim((string) ($payload['antivirus'] ?? ''));
        if ($antivirus !== '' && !preg_match('/^[\p{L}\p{N} .,_\-\/()+]{1,128}$/uD', $antivirus)) {
            return $this->error('INVALID_PAYLOAD', 'antivirus invalido.', 422);
        }

        $componentsInput = $payload['components'] ?? [];
        if (!is_array($componentsInput)) {
            return $this->error('INVALID_PAYLOAD', 'components deve ser um objeto.', 422);
        }
        if (count($componentsInput) > self::MAX_COMPONENTS) {
            return $this->error('INVALID_PAYLOAD', 'Componentes em excesso.', 422);
        }

        $components = [];
        foreach ($componentsInput as $name => $component) {
            $name = strtolower(trim((string) $name));
            if (!HealthStatus::isValidComponentName($name)) {
                return $this->error('INVALID_COMPONENT', 'Nome de componente invalido.', 422);
            }
            if (!is_array($component)) {
                return $this->error('INVALID_COMPONENT', 'Componente invalido.', 422);
            }
            $status = strtolower(trim((string) ($component['status'] ?? '')));
            if (!HealthStatus::isValidComponentStatus($status)) {
                return $this->error('INVALID_COMPONENT', "Status invalido para '{$name}'.", 422);
            }
            $version = trim((string) ($component['version'] ?? ''));
            if ($version !== '' && !preg_match('/^[A-Za-z0-9.+_-]{1,64}$/D', $version)) {
                return $this->error('INVALID_VERSION', "Versao invalida para '{$name}'.", 422);
            }
            $components[$name] = ['version' => $version, 'status' => $status];
        }

        $machinesId = MachineRepository::recordHeartbeat([
            'machine_id'       => $machineId,
            'hostname'         => $hostname,
            'username'         => $username,
            'guardian_version' => $guardianVersion,
            'antivirus'        => $antivirus,
            'last_ip'          => mb_substr((string) ($request->getClientIp() ?? ''), 0, 64),
            'components'        => $components,
        ]);

        return $machinesId > 0
            ? new JsonResponse(['ok' => true, 'machine_id' => $machineId], 202)
            : $this->error('DATABASE_ERROR', 'Nao foi possivel registrar o heartbeat.', 500);
    }

    /** Versions are display-only, so accept an empty value or a bounded dotted string. */
    private static function isValidVersion(string $version): bool
    {
        return $version === '' || preg_match('/^[A-Za-z0-9.+_-]{1,64}$/D', $version) === 1;
    }

    /** Linha da máquina a partir do machine_id informado pelo serviço. */
    private function machineRow(string $machineId): ?array
    {
        global $DB;

        $iterator = $DB->request([
            'SELECT' => ['id'],
            'FROM'   => MachineRepository::MACHINES_TABLE,
            'WHERE'  => ['machine_id' => $machineId],
            'LIMIT'  => 1,
        ]);

        return count($iterator) === 1 ? $iterator->current() : null;
    }

    /**
     * Ações pendentes da máquina. A própria leitura reivindica as ações
     * (pending -> running), então uma ação nunca é entregue duas vezes, mesmo
     * que duas coletas cheguem juntas.
     */
    #[Route(
        '/api/v1/actions/{machineId}',
        name: 'ativaguardian_api_actions',
        requirements: ['machineId' => '[A-Za-z0-9._-]{1,128}'],
        methods: ['GET']
    )]
    public function actions(Request $request, string $machineId): Response
    {
        if ($error = $this->checkAuth($request)) {
            return $error;
        }

        $machine = $this->machineRow($machineId);
        if ($machine === null) {
            return $this->error('MACHINE_NOT_FOUND', 'Maquina ainda nao registrada.', 404);
        }

        return new JsonResponse([
            'actions'            => ActionQueue::claim((int) $machine['id']),
            'poll_after_seconds' => 30,
        ]);
    }

    /** Resultado da execução. Só aceita ação em running que pertença à máquina. */
    #[Route(
        '/api/v1/actions/{actionId}/result',
        name: 'ativaguardian_api_action_result',
        requirements: ['actionId' => '\d{1,10}'],
        methods: ['POST']
    )]
    public function actionResult(Request $request, string $actionId): Response
    {
        if ($error = $this->checkAuth($request)) {
            return $error;
        }
        if (strlen($request->getContent()) > self::MAX_BODY_BYTES) {
            return $this->error('PAYLOAD_TOO_LARGE', 'Resultado muito grande.', 413);
        }

        try {
            $payload = json_decode($request->getContent(), true, 8, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $this->error('INVALID_JSON', 'JSON invalido.', 400);
        }
        if (!is_array($payload)) {
            return $this->error('INVALID_PAYLOAD', 'Conteudo invalido.', 400);
        }

        $machineId = trim((string) ($payload['machine_id'] ?? ''));
        if (!preg_match('/^[A-Za-z0-9._-]{1,128}$/D', $machineId)) {
            return $this->error('INVALID_PAYLOAD', 'machine_id ausente ou invalido.', 422);
        }
        $status = strtolower(trim((string) ($payload['status'] ?? '')));
        if (!in_array($status, [ActionQueue::SUCCESS, ActionQueue::FAILED], true)) {
            return $this->error('INVALID_PAYLOAD', 'status deve ser success ou failed.', 422);
        }
        $message = mb_substr(trim((string) ($payload['message'] ?? '')), 0, 500);

        $machine = $this->machineRow($machineId);
        if ($machine === null) {
            return $this->error('MACHINE_NOT_FOUND', 'Maquina ainda nao registrada.', 404);
        }

        $recorded = ActionQueue::complete(
            (int) $actionId,
            (int) $machine['id'],
            $status === ActionQueue::SUCCESS,
            $message
        );

        // 409: a ação não estava running para esta máquina — resultado repetido,
        // expirado por timeout, ou de outra máquina. Nada é gravado.
        return $recorded
            ? new JsonResponse(['ok' => true], 202)
            : $this->error('ACTION_NOT_CLAIMABLE', 'Acao nao esta em execucao para esta maquina.', 409);
    }
}
