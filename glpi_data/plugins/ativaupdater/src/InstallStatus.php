<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaupdater;

/**
 * Installation progress reported by the Windows service.
 *
 * The service (1.4.0+) supervises each installer run and reports "retrying"
 * or "install_failed" itself. The server still has to flag computers that stop
 * reporting in the middle of an installation (service stopped, hung installer
 * or services older than 1.4.0, which can stay in "installing").
 */
final class InstallStatus
{
    public const STATUS_RETRYING = 'retrying';
    public const STATUS_INSTALL_FAILED = 'install_failed';

    public const IN_PROGRESS = ['downloading', 'installing', 'retrying'];
    public const FINISHED = ['current', 'updated', 'waiting_release'];

    /** A supervised attempt reports again within the 20 min installer timeout plus the download. */
    public const NO_CONTACT_SECONDS = 2700;

    /** The first attempt plus three retries and their waits finish in under two hours. */
    public const MAX_DURATION_SECONDS = 10800;

    public const LOG_MAX_CHARS = 65000;

    /**
     * Value of install_started_at after a report: set when an installation
     * starts (or targets another version), kept while it is still running or
     * failing, cleared once the computer is up to date.
     */
    public static function installStartedAt(
        string $status,
        ?string $previousStartedAt,
        string $previousAvailableVersion,
        string $availableVersion,
        string $now
    ): ?string {
        if (in_array($status, self::FINISHED, true)) {
            return null;
        }
        if (!in_array($status, self::IN_PROGRESS, true)) {
            return $previousStartedAt ?: null;
        }
        if (empty($previousStartedAt) || $previousAvailableVersion !== $availableVersion) {
            return $now;
        }
        return $previousStartedAt;
    }

    public static function isStuck(string $status, ?string $installStartedAt, ?string $lastCheck, int $now): bool
    {
        if (!in_array($status, self::IN_PROGRESS, true)) {
            return false;
        }
        $lastContact = strtotime((string) $lastCheck) ?: 0;
        if ($lastContact > 0 && $now - $lastContact > self::NO_CONTACT_SECONDS) {
            return true;
        }
        $startedAt = strtotime((string) $installStartedAt) ?: 0;
        return $startedAt > 0 && $now - $startedAt > self::MAX_DURATION_SECONDS;
    }
}
