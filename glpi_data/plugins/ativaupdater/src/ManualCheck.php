<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaupdater;

/**
 * Dashboard commands ("Verificar agora", "Reinstalar", "Reiniciar serviço",
 * "Enviar logs") delivered to the Windows service.
 *
 * The dashboard increments check_request_seq and stores the command; the
 * service polls /commands every 15 s and acknowledges by sending the sequence
 * it received (command_seq) in its next /status or /diagnostics call. Services
 * older than 1.5.0 do not send command_seq: any report acknowledges them.
 * Integer sequences, unlike datetimes, do not depend on timezones.
 */
final class ManualCheck
{
    public const COMMAND_CHECK = 'check';
    public const COMMAND_REINSTALL = 'reinstall';
    public const COMMAND_RESTART_SERVICE = 'restart_service';
    public const COMMAND_SEND_LOGS = 'send_logs';

    public const COMMANDS = [
        self::COMMAND_CHECK,
        self::COMMAND_REINSTALL,
        self::COMMAND_RESTART_SERVICE,
        self::COMMAND_SEND_LOGS,
    ];

    /** Services older than this do not poll /commands. */
    public const MIN_SERVICE_VERSION = '1.2.0';

    /** First service that polls commands during installations, cancels them and runs remote actions. */
    public const REMOTE_ACTIONS_MIN_VERSION = '1.5.0';

    /** The service polls every 15 s; allow for a slow network before warning. */
    public const RESPONSE_TIMEOUT_SECONDS = 120;

    public const STATE_NONE = 'none';
    public const STATE_UNSUPPORTED = 'unsupported';
    public const STATE_BUSY = 'busy';
    public const STATE_WAITING = 'waiting';
    public const STATE_NO_RESPONSE = 'no_response';

    /** Statuses during which services older than 1.5.0 supervise an installer without polling commands. */
    public const BUSY_STATUSES = ['downloading', 'installing'];

    public static function isPending(array $client): bool
    {
        return (int) ($client['check_request_seq'] ?? 0) > (int) ($client['check_ack_seq'] ?? 0);
    }

    /** Pending command name, or '' when nothing is pending. */
    public static function command(array $client): string
    {
        if (!self::isPending($client)) {
            return '';
        }
        $command = (string) ($client['command'] ?? '');
        return in_array($command, self::COMMANDS, true) ? $command : self::COMMAND_CHECK;
    }

    /**
     * Fields acknowledging the pending request when the service reports.
     *
     * @param int|null $reportedSeq sequence sent by the service (1.5.0+), null for older services
     */
    public static function acknowledgement(array $client, string $now, ?int $reportedSeq = null): array
    {
        if (!self::isPending($client)) {
            return [];
        }
        $requested = (int) $client['check_request_seq'];
        $acknowledged = $reportedSeq === null ? $requested : min($requested, max(0, $reportedSeq));
        if ($acknowledged <= (int) ($client['check_ack_seq'] ?? 0)) {
            return [];
        }
        return [
            'check_ack_seq'         => $acknowledged,
            'check_acknowledged_at' => $now,
        ];
    }

    public static function supports(string $updaterVersion, string $command = self::COMMAND_CHECK): bool
    {
        $minimum = $command === self::COMMAND_CHECK ? self::MIN_SERVICE_VERSION : self::REMOTE_ACTIONS_MIN_VERSION;
        return ReleasePolicy::isValidVersion($updaterVersion)
            && version_compare($updaterVersion, $minimum, '>=');
    }

    /** Whether "Verificar agora" interrupts an installation in progress on this computer. */
    public static function cancelsInstallations(string $updaterVersion): bool
    {
        return self::supports($updaterVersion, self::COMMAND_REINSTALL);
    }

    public static function state(array $client, int $now): string
    {
        if (!self::isPending($client)) {
            return self::STATE_NONE;
        }
        $version = (string) ($client['updater_version'] ?? '');
        if (!self::supports($version, self::command($client))) {
            return self::STATE_UNSUPPORTED;
        }
        if (!self::cancelsInstallations($version)
            && in_array((string) ($client['status'] ?? ''), self::BUSY_STATUSES, true)
        ) {
            return self::STATE_BUSY;
        }
        $requestedAt = ServerClock::toTimestamp($client['check_requested_at'] ?? null);
        if ($requestedAt > 0 && $now - $requestedAt > self::RESPONSE_TIMEOUT_SECONDS) {
            return self::STATE_NO_RESPONSE;
        }
        return self::STATE_WAITING;
    }
}
