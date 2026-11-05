<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaremote;

use Auth;
use Config;
use Glpi\Security\TOTPManager;
use Toolbox;
use User;

/**
 * Login do app "Ativa Remote T.I." (Windows) com o usuario do GLPI.
 *
 * - Usuario e senha do GLPI (local ou LDAP, pelo proprio Auth do GLPI), com o
 *   codigo do 2FA quando o usuario tiver.
 * - So entra quem tem "Atualizar/Gerenciar" no Ativa Remote em algum perfil.
 * - Devolve um token proprio (so o hash fica no banco). Vale 7 dias sem uso e
 *   no maximo 30 dias; a permissao e conferida de novo a cada chamada.
 * - Tabela criada na hora (git pull basta, sem reinstalar o plugin).
 */
final class TechSessions
{
    public const TABLE = 'glpi_plugin_ativaremote_tech_sessions';
    private const SCHEMA_KEY = 'tech_schema';
    private const SCHEMA_VERSION = '1';
    public const IDLE_SECONDS = 7 * 86400;
    public const MAX_SECONDS = 30 * 86400;
    private const MAX_FAILURES = 8;
    private const LOCK_SECONDS = 900;

    private static bool $schemaChecked = false;

    public static function ensureSchema(): void
    {
        if (self::$schemaChecked) {
            return;
        }
        self::$schemaChecked = true;
        $config = Config::getConfigurationValues(Settings::CONTEXT, [self::SCHEMA_KEY]);
        if (($config[self::SCHEMA_KEY] ?? '') === self::SCHEMA_VERSION) {
            return;
        }
        global $DB;
        $DB->doQuery(sprintf(
            'CREATE TABLE IF NOT EXISTS `%s` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `users_id` int unsigned NOT NULL,
                `token_hash` char(64) NOT NULL,
                `device` varchar(128) DEFAULT NULL,
                `ip` varchar(45) DEFAULT NULL,
                `created_at` datetime NOT NULL,
                `last_used_at` datetime NOT NULL,
                `revoked` tinyint(1) NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                UNIQUE KEY `token_hash` (`token_hash`),
                KEY `users_id` (`users_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC',
            self::TABLE
        ));
        Config::setConfigurationValues(Settings::CONTEXT, [self::SCHEMA_KEY => self::SCHEMA_VERSION]);
    }

    /**
     * @return array{token: string, expires_in: int, user: array{id: int, name: string}, can_admin: bool}
     * @throws ApiException
     */
    public static function login(string $login, string $password, string $totp, string $device, string $ip): array
    {
        self::ensureSchema();
        $login = trim($login);
        if ($login === '' || $password === '') {
            throw new ApiException('INVALID_LOGIN', 'Informe o usuário e a senha do GLPI.', 400);
        }
        $lockKey = 'ativaremote_tech_fail_' . sha1(strtolower($login) . '|' . $ip);
        self::checkLock($lockKey);

        $auth = new Auth();
        if (!$auth->validateLogin($login, $password)) {
            self::fail($lockKey, $login, $ip);
            throw new ApiException('INVALID_LOGIN', 'Usuário ou senha do GLPI incorretos.', 401);
        }
        $userId = (int) ($auth->user->fields['id'] ?? 0);
        if ($userId <= 0 || empty($auth->user->fields['is_active']) || !empty($auth->user->fields['is_deleted'])) {
            self::fail($lockKey, $login, $ip);
            throw new ApiException('INVALID_LOGIN', 'Usuário ou senha do GLPI incorretos.', 401);
        }

        $totpManager = new TOTPManager();
        if ($totpManager->is2FAEnabled($userId)) {
            $totp = preg_replace('/\s+/', '', $totp) ?? '';
            if ($totp === '') {
                throw new ApiException('TOTP_REQUIRED', 'Informe o código de verificação em duas etapas.', 401);
            }
            if (!$totpManager->verifyCodeForUser($totp, $userId) && !$totpManager->verifyBackupCodeForUser($totp, $userId)) {
                self::fail($lockKey, $login, $ip);
                throw new ApiException('TOTP_INVALID', 'Código de verificação incorreto.', 401);
            }
        }

        if (!self::canManage($userId)) {
            self::audit('Login negado no app (sem permissão no Ativa Remote)', $userId, $ip);
            throw new ApiException('FORBIDDEN', 'Seu usuário não tem permissão para gerenciar o Ativa Remote.', 403);
        }

        global $GLPI_CACHE;
        $GLPI_CACHE?->delete($lockKey);

        $token = bin2hex(random_bytes(32));
        $now = ServerClock::now();
        global $DB;
        $DB->insert(self::TABLE, [
            'users_id'     => $userId,
            'token_hash'   => hash('sha256', $token),
            'device'       => mb_substr(trim($device), 0, 128),
            'ip'           => mb_substr($ip, 0, 45),
            'created_at'   => $now,
            'last_used_at' => $now,
        ]);
        self::audit('Login no app Ativa Remote T.I.' . ($device !== '' ? " ($device)" : ''), $userId, $ip);

        return [
            'token'      => $token,
            'expires_in' => self::IDLE_SECONDS,
            'user'       => ['id' => $userId, 'name' => self::userName($userId)],
            'can_admin'  => self::canAdmin($userId),
        ];
    }

    /**
     * Sessao do token, ou null (token desconhecido, vencido, revogado ou sem permissao).
     *
     * @return array{id: int, users_id: int, name: string, can_admin: bool}|null
     */
    public static function verify(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/D', $token)) {
            return null;
        }
        self::ensureSchema();
        global $DB;
        $row = null;
        foreach ($DB->request(['FROM' => self::TABLE, 'WHERE' => ['token_hash' => hash('sha256', $token)], 'LIMIT' => 1]) as $found) {
            $row = $found;
        }
        if ($row === null || (int) $row['revoked'] === 1) {
            return null;
        }
        $now = time();
        $created = ServerClock::toTimestamp($row['created_at']);
        $lastUsed = ServerClock::toTimestamp($row['last_used_at']);
        if ($now - $created > self::MAX_SECONDS || $now - $lastUsed > self::IDLE_SECONDS) {
            return null;
        }
        $userId = (int) $row['users_id'];
        $user = new User();
        if (!$user->getFromDB($userId) || empty($user->fields['is_active']) || !empty($user->fields['is_deleted'])
            || !self::canManage($userId)) {
            return null;
        }
        if ($now - $lastUsed > 60) {
            $DB->update(self::TABLE, ['last_used_at' => ServerClock::now()], ['id' => (int) $row['id']]);
        }
        return [
            'id'        => (int) $row['id'],
            'users_id'  => $userId,
            'name'      => self::userName($userId),
            'can_admin' => self::canAdmin($userId),
        ];
    }

    public static function logout(int $sessionId): void
    {
        self::ensureSchema();
        global $DB;
        $DB->update(self::TABLE, ['revoked' => 1], ['id' => $sessionId]);
    }

    /** "Atualizar/Gerenciar" no Ativa Remote em algum perfil do usuario. */
    public static function canManage(int $userId): bool
    {
        return self::hasRight($userId, 'plugin_ativaremote', UPDATE);
    }

    /** Administrador do GLPI (Configurar > Geral): mexe em "Proteger sempre". */
    public static function canAdmin(int $userId): bool
    {
        return self::hasRight($userId, 'config', UPDATE);
    }

    private static function hasRight(int $userId, string $right, int $bit): bool
    {
        global $DB;
        foreach ($DB->request([
            'SELECT'     => ['glpi_profilerights.rights'],
            'FROM'       => 'glpi_profiles_users',
            'INNER JOIN' => [
                'glpi_profilerights' => [
                    'ON' => ['glpi_profilerights' => 'profiles_id', 'glpi_profiles_users' => 'profiles_id'],
                ],
            ],
            'WHERE'      => [
                'glpi_profiles_users.users_id' => $userId,
                'glpi_profilerights.name'      => $right,
            ],
        ]) as $row) {
            if (((int) $row['rights'] & $bit) === $bit) {
                return true;
            }
        }
        return false;
    }

    public static function userName(int $userId): string
    {
        return (string) getUserName($userId);
    }

    /** @throws ApiException */
    private static function checkLock(string $key): void
    {
        global $GLPI_CACHE;
        $state = $GLPI_CACHE?->get($key);
        $wait = is_array($state) ? (int) ($state['locked_until'] ?? 0) - time() : 0;
        if ($wait > 0) {
            throw new ApiException(
                'LOCKED',
                sprintf('Muitas tentativas incorretas. Tente de novo em %d minuto(s).', (int) ceil($wait / 60)),
                429
            );
        }
    }

    private static function fail(string $key, string $login, string $ip): void
    {
        global $GLPI_CACHE;
        $state = $GLPI_CACHE?->get($key);
        $count = (is_array($state) ? (int) ($state['count'] ?? 0) : 0) + 1;
        $state = ['count' => $count, 'locked_until' => $count >= self::MAX_FAILURES ? time() + self::LOCK_SECONDS : 0];
        $GLPI_CACHE?->set($key, $state, self::LOCK_SECONDS);
        Toolbox::logInFile('ativaremote', sprintf("Login incorreto no app Ativa Remote T.I.: login %s, IP %s\n", mb_substr($login, 0, 64), $ip));
    }

    public static function audit(string $event, int $userId, string $ip, string $hostname = ''): void
    {
        Toolbox::logInFile('ativaremote', sprintf(
            "%s: usuario %s%s, IP %s\n",
            $event,
            self::userName($userId),
            $hostname !== '' ? ", computador $hostname" : '',
            $ip
        ));
    }
}
