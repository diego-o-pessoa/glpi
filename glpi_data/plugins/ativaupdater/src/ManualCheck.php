<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaupdater;

/**
 * "Verificar agora" handshake between the dashboard and the Windows service.
 *
 * The dashboard increments check_request_seq; the service sees a pending
 * command on /commands (polled every 15 s) and its next /status report copies
 * the sequence to check_ack_seq. Integer sequences, unlike the previous
 * requested/acknowledged datetimes, do not depend on timezones.
 */
final class ManualCheck
{
    /** Services older than this do not poll /commands. */
    public const MIN_SERVICE_VERSION = '1.2.0';

    /** The service polls every 15 s; allow for a slow network before warning. */
    public const RESPONSE_TIMEOUT_SECONDS = 120;

    public const STATE_NONE = 'none';
    public const STATE_UNSUPPORTED = 'unsupported';
    public const STATE_BUSY = 'busy';
    public const STATE_WAITING = 'waiting';
    public const STATE_NO_RESPONSE = 'no_response';

    /** Statuses during which the service is supervising an installer and does not poll commands. */
    public const BUSY_STATUSES = ['downloading', 'installing'];

    public static function isPending(array $client): bool
    {
        return (int) ($client['check_request_seq'] ?? 0) > (int) ($client['check_ack_seq'] ?? 0);
    }

    /** Fields acknowledging the pending request when the service reports. */
    public static function acknowledgement(array $client, string $now): array
    {
        if (!self::isPending($client)) {
            return [];
        }
        return [
            'check_ack_seq'         => (int) $client['check_request_seq'],
            'check_acknowledged_at' => $now,
        ];
    }

    public static function supports(string $updaterVersion): bool
    {
        return ReleasePolicy::isValidVersion($updaterVersion)
            && version_compare($updaterVersion, self::MIN_SERVICE_VERSION, '>=');
    }

    public static function state(array $client, int $now): string
    {
        if (!self::isPending($client)) {
            return self::STATE_NONE;
        }
        if (!self::supports((string) ($client['updater_version'] ?? ''))) {
            return self::STATE_UNSUPPORTED;
        }
        if (in_array((string) ($client['status'] ?? ''), self::BUSY_STATUSES, true)) {
            return self::STATE_BUSY;
        }
        $requestedAt = ServerClock::toTimestamp($client['check_requested_at'] ?? null);
        if ($requestedAt > 0 && $now - $requestedAt > self::RESPONSE_TIMEOUT_SECONDS) {
            return self::STATE_NO_RESPONSE;
        }
        return self::STATE_WAITING;
    }
}
