<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaremote\Controller;

use Exception;
use Glpi\Controller\AbstractController;
use GlpiPlugin\Ativaremote\ApiException;
use GlpiPlugin\Ativaremote\ClientRepository;
use GlpiPlugin\Ativaremote\DashboardData;
use GlpiPlugin\Ativaremote\ProtectionPolicy;
use GlpiPlugin\Ativaremote\RendezvousConfig;
use GlpiPlugin\Ativaremote\TechSessions;
use GlpiPlugin\Ativaremote\TiPasswordGate;
use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;
use Toolbox;

/**
 * API do app "Ativa Remote T.I." (Windows), usado so pelo T.I.
 *
 * Login com o usuario do GLPI (POST /tech/login) devolve um token; as demais
 * chamadas usam "Authorization: Bearer <token>". Mesmas regras do painel:
 * computadores protegidos pedem a senha do T.I., a senha da sessao so sai
 * depois que ela confere, e tudo fica em files/_log/ativaremote.log.
 */
final class TechController extends AbstractController
{
    private const MAX_BODY = 8192;

    #[Route('/api/v1/tech/login', name: 'ativaremote_tech_login', methods: ['POST'])]
    public function login(Request $request): Response
    {
        return $this->run(function () use ($request): array {
            $body = $this->body($request);
            return TechSessions::login(
                (string) ($body['login'] ?? ''),
                (string) ($body['password'] ?? ''),
                (string) ($body['totp'] ?? ''),
                (string) ($body['device'] ?? ''),
                $this->ip($request)
            );
        });
    }

    #[Route('/api/v1/tech/logout', name: 'ativaremote_tech_logout', methods: ['POST'])]
    public function logout(Request $request): Response
    {
        return $this->run(function () use ($request): array {
            $tech = $this->tech($request);
            TechSessions::logout($tech['id']);
            return ['ok' => true];
        });
    }

    /** Quem esta logado e o que o app precisa para se configurar. */
    #[Route('/api/v1/tech/me', name: 'ativaremote_tech_me', methods: ['GET'])]
    public function me(Request $request): Response
    {
        return $this->run(function () use ($request): array {
            $tech = $this->tech($request);
            return [
                'user'       => ['id' => $tech['users_id'], 'name' => $tech['name']],
                'can_admin'  => $tech['can_admin'],
                'rendezvous' => RendezvousConfig::forClients(),
            ];
        });
    }

    /** Lista ao vivo (o app consulta a cada poucos segundos; sem senhas). */
    #[Route('/api/v1/tech/clients', name: 'ativaremote_tech_clients', methods: ['GET'])]
    public function clients(Request $request): Response
    {
        return $this->run(function () use ($request): array {
            $tech = $this->tech($request);
            $payload = DashboardData::payload(false);
            $repo = new ClientRepository();
            foreach ($payload['clients'] as &$client) {
                $row = $repo->findById((int) $client['id']);
                $client['ti_verified'] = $row !== null && $client['protected'] && TiPasswordGate::isVerifiedForTech($row, $tech['id']);
                $client['protected_manual'] = $row !== null && (bool) $row['protected_manual'];
                unset($client['password'], $client['connect_url'], $client['computer_url']);
            }
            unset($client);
            unset($payload['signature']);
            return $payload;
        });
    }

    /** "Conectar": pede o acesso ao computador (ou devolve o pedido ja aberto). */
    #[Route('/api/v1/tech/clients/{id}/request', name: 'ativaremote_tech_request', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function requestAccess(Request $request, int $id): Response
    {
        return $this->run(function () use ($request, $id): array {
            $tech = $this->tech($request);
            $body = $this->body($request);
            $repo = new ClientRepository();
            $client = $this->client($repo, $id);
            $protected = ProtectionPolicy::isProtected($client);
            if ($protected) {
                $this->checkTiPassword($body, $client, 'solicitar acesso', $tech, $request);
            }
            try {
                $client = $repo->requestAccess($id, $tech['users_id']);
            } catch (Exception $e) {
                throw new ApiException('NOT_AVAILABLE', $e->getMessage(), 409);
            }
            if ($protected) {
                TiPasswordGate::markVerifiedForTech($client, $tech['id']);
            }
            TechSessions::audit(
                $protected ? 'Acesso solicitado pelo app (computador protegido, senha do T.I. conferida)' : 'Acesso solicitado pelo app',
                $tech['users_id'],
                $this->ip($request),
                (string) $client['hostname']
            );
            return $this->state($client);
        });
    }

    /**
     * Situacao do pedido. Liberado, devolve o ID e a senha da sessao para o
     * app conectar sozinho (inclusive ao reconectar quando o usuario sai).
     */
    #[Route('/api/v1/tech/clients/{id}/connection', name: 'ativaremote_tech_connection', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function connection(Request $request, int $id): Response
    {
        return $this->run(function () use ($request, $id): array {
            $tech = $this->tech($request);
            $body = $this->body($request);
            $repo = new ClientRepository();
            $client = $this->client($repo, $id);
            $state = $this->state($client);
            if ($client['remote_access_status'] !== ClientRepository::STATUS_ACCEPTED) {
                return $state;
            }
            if (ProtectionPolicy::isProtected($client) && !TiPasswordGate::isVerifiedForTech($client, $tech['id'])) {
                $this->checkTiPassword($body, $client, 'conectar', $tech, $request);
                TiPasswordGate::markVerifiedForTech($client, $tech['id']);
            }
            try {
                $connection = $repo->connectionFor($id);
            } catch (Exception $e) {
                throw new ApiException('NOT_AVAILABLE', $e->getMessage(), 409);
            }
            if (empty($body['reconnect'])) {
                TechSessions::audit('Conexao liberada pelo app', $tech['users_id'], $this->ip($request), (string) $client['hostname']);
            }
            return $state + [
                'rustdesk_id' => $connection['rustdesk_id'],
                'password'    => $connection['password'],
            ];
        });
    }

    /** "Encerrar": o computador troca a senha do RustDesk em seguida. */
    #[Route('/api/v1/tech/clients/{id}/close', name: 'ativaremote_tech_close', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function close(Request $request, int $id): Response
    {
        return $this->run(function () use ($request, $id): array {
            $tech = $this->tech($request);
            $repo = new ClientRepository();
            $client = $this->client($repo, $id);
            try {
                $repo->closeAccess($id);
            } catch (Exception $e) {
                throw new ApiException('NOT_AVAILABLE', $e->getMessage(), 409);
            }
            TechSessions::audit('Sessao encerrada pelo app', $tech['users_id'], $this->ip($request), (string) $client['hostname']);
            return $this->state($this->client($repo, $id));
        });
    }

    /**
     * Configuracoes do computador: autorizacao do usuario (ligada = o usuario
     * aceita cada acesso; desligada = entra direto) e "Proteger sempre"
     * (so administrador do GLPI, com a senha do T.I.).
     */
    #[Route('/api/v1/tech/clients/{id}/settings', name: 'ativaremote_tech_settings', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function settings(Request $request, int $id): Response
    {
        return $this->run(function () use ($request, $id): array {
            $tech = $this->tech($request);
            $body = $this->body($request);
            $repo = new ClientRepository();
            $client = $this->client($repo, $id);
            $ip = $this->ip($request);

            if (array_key_exists('protected_manual', $body)) {
                if (!$tech['can_admin']) {
                    throw new ApiException('FORBIDDEN', 'Só administradores do GLPI mudam a proteção.', 403);
                }
                $this->checkTiPassword($body, $client, 'mudar a proteção', $tech, $request);
                $ids = array_map(
                    static fn (array $row): int => (int) $row['id'],
                    array_filter($repo->all(), static fn (array $row): bool => (bool) $row['protected_manual'])
                );
                $ids = !empty($body['protected_manual'])
                    ? array_values(array_unique([...$ids, $id]))
                    : array_values(array_diff($ids, [$id]));
                $repo->setManualProtection($ids);
                ProtectionPolicy::reset();
                TechSessions::audit(
                    !empty($body['protected_manual']) ? 'Computador protegido pelo app' : 'Protecao manual removida pelo app',
                    $tech['users_id'],
                    $ip,
                    (string) $client['hostname']
                );
                $client = $this->client($repo, $id);
            }

            if (array_key_exists('require_consent', $body)) {
                $require = !empty($body['require_consent']);
                if (!$require && ProtectionPolicy::isProtected($client)) {
                    throw new ApiException('PROTECTED', 'Este computador é protegido: a autorização do usuário é obrigatória.', 409);
                }
                try {
                    $repo->setRequireConsent($id, $require);
                } catch (Exception $e) {
                    throw new ApiException('NOT_AVAILABLE', $e->getMessage(), 409);
                }
                TechSessions::audit(
                    $require ? 'Autorizacao do usuario ligada pelo app' : 'Acesso direto (sem autorizacao) ligado pelo app',
                    $tech['users_id'],
                    $ip,
                    (string) $client['hostname']
                );
                $client = $this->client($repo, $id);
            }

            return $this->state($client);
        });
    }

    // ------------------------------------------------------------------ apoio

    /** @return array<string, mixed> */
    private function state(array $client): array
    {
        $protected = ProtectionPolicy::isProtected($client);
        return [
            'id'              => (int) $client['id'],
            'hostname'        => (string) $client['hostname'],
            'status'          => $client['remote_access_status'] ?? null,
            'status_message'  => (string) ($client['status_message'] ?? ''),
            'online'          => ClientRepository::isOnline($client),
            'protected'       => $protected,
            'protected_manual' => (bool) ($client['protected_manual'] ?? false),
            'require_consent' => ClientRepository::requiresConsent($client),
            'rustdesk_id'     => (string) ($client['rustdesk_id'] ?? ''),
        ];
    }

    /** @throws ApiException */
    private function client(ClientRepository $repo, int $id): array
    {
        $client = $repo->findById($id);
        if ($client === null) {
            throw new ApiException('NOT_FOUND', 'Computador não encontrado.', 404);
        }
        return $client;
    }

    /** @throws ApiException */
    private function checkTiPassword(array $body, array $client, string $purpose, array $tech, Request $request): void
    {
        $password = (string) ($body['ti_password'] ?? '');
        if ($password === '') {
            throw new ApiException('TI_PASSWORD_REQUIRED', 'Computador protegido: informe a senha do T.I.', 401);
        }
        try {
            TiPasswordGate::checkForTech($password, $client, $purpose, $tech, $this->ip($request));
        } catch (RuntimeException $e) {
            throw new ApiException($e->getCode() === 429 ? 'LOCKED' : 'TI_PASSWORD_INVALID', $e->getMessage(), $e->getCode() === 429 ? 429 : 403);
        }
    }

    /**
     * @return array{id: int, users_id: int, name: string, can_admin: bool}
     * @throws ApiException
     */
    private function tech(Request $request): array
    {
        $header = trim((string) $request->headers->get('Authorization', ''));
        if (!preg_match('/^Bearer\s+([^\s]+)$/D', $header, $matches)) {
            throw new ApiException('UNAUTHORIZED', 'Faça login de novo.', 401);
        }
        $tech = TechSessions::verify($matches[1]);
        if ($tech === null) {
            throw new ApiException('UNAUTHORIZED', 'Sessão vencida ou sem permissão. Faça login de novo.', 401);
        }
        return $tech;
    }

    /**
     * @return array<string, mixed>
     * @throws ApiException
     */
    private function body(Request $request): array
    {
        $content = $request->getContent();
        if ($content === '') {
            return [];
        }
        if (strlen($content) > self::MAX_BODY) {
            throw new ApiException('PAYLOAD_TOO_LARGE', 'Pedido muito grande.', 413);
        }
        try {
            $body = json_decode($content, true, 8, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new ApiException('INVALID_JSON', 'JSON inválido.', 400);
        }
        if (!is_array($body)) {
            throw new ApiException('INVALID_PAYLOAD', 'Conteúdo inválido.', 400);
        }
        return $body;
    }

    private function ip(Request $request): string
    {
        return (string) ($request->getClientIp() ?? '');
    }

    /** @param callable(): array<string, mixed> $action */
    private function run(callable $action): JsonResponse
    {
        try {
            $response = new JsonResponse($action());
        } catch (ApiException $e) {
            $response = new JsonResponse(['error' => ['code' => $e->errorCode, 'message' => $e->getMessage()]], $e->status);
        } catch (Throwable $e) {
            Toolbox::logInFile('ativaremote', 'Falha na API do app T.I.: ' . $e->getMessage() . PHP_EOL);
            $response = new JsonResponse(['error' => ['code' => 'SERVER_ERROR', 'message' => 'Erro interno.']], 500);
        }
        $response->headers->set('Cache-Control', 'no-store');
        return $response;
    }
}
