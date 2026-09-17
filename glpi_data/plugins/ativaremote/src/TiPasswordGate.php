<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaremote;

use RuntimeException;
use Session;
use Toolbox;

/**
 * T.I. password required to request access to, and to connect to, a protected computer.
 *
 * Five wrong passwords lock the check for five minutes in the technician's session.
 * A technician who typed the password when requesting access does not type it again
 * to connect to that same session.
 */
final class TiPasswordGate
{
    private const MAX_FAILURES = 5;
    private const LOCK_SECONDS = 300;
    private const FAILURES_KEY = 'plugin_ativaremote_ti_failures';
    private const VERIFIED_KEY = 'plugin_ativaremote_ti_verified';

    /**
     * @throws RuntimeException with the message for the technician; the code is the HTTP status
     */
    public static function check(string $password, array $client, string $purpose): void
    {
        $failures = $_SESSION[self::FAILURES_KEY] ?? ['count' => 0, 'locked_until' => 0];
        $wait = (int) $failures['locked_until'] - time();
        if ($wait > 0) {
            throw new RuntimeException(
                sprintf('Muitas tentativas incorretas. Tente novamente em %d minuto(s).', (int) ceil($wait / 60)),
                429
            );
        }

        if (!Settings::verifyTiPassword($password)) {
            $failures['count'] = (int) $failures['count'] + 1;
            $message = 'Senha do T.I. incorreta.';
            if ($failures['count'] >= self::MAX_FAILURES) {
                $failures = ['count' => 0, 'locked_until' => time() + self::LOCK_SECONDS];
                $message = 'Senha do T.I. incorreta. Novas tentativas bloqueadas por 5 minutos.';
            }
            $_SESSION[self::FAILURES_KEY] = $failures;
            self::log("Senha do T.I. incorreta ($purpose)", $client);
            throw new RuntimeException($message, 403);
        }
        unset($_SESSION[self::FAILURES_KEY]);
    }

    /** Remembers the check for the access request currently open on this computer. */
    public static function markVerified(array $client): void
    {
        $_SESSION[self::VERIFIED_KEY][(int) $client['id']] = (int) $client['request_seq'];
    }

    public static function isVerified(array $client): bool
    {
        return ($client['request_action'] ?? null) === ClientRepository::ACTION_OPEN
            && ($_SESSION[self::VERIFIED_KEY][(int) $client['id']] ?? -1) === (int) $client['request_seq'];
    }

    public static function log(string $event, array $client): void
    {
        Toolbox::logInFile('ativaremote', sprintf(
            "%s: usuario %s, computador %s\n",
            $event,
            getUserName((int) Session::getLoginUserID()),
            $client['hostname'] ?? '?'
        ));
    }
}
