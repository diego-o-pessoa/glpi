<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaguardian;

/**
 * Health vocabulary shared by the API (validation) and the dashboard (rendering).
 *
 * A component reports one of the reportable statuses below. "offline" is never
 * reported by a component -- a machine that is offline does not phone home at all;
 * it is derived on the server from how long the machine has been silent. The
 * machine's overall status is the worst of its components, unless it is offline.
 */
final class HealthStatus
{
    public const HEALTHY          = 'healthy';
    public const WARNING          = 'warning';
    public const ERROR            = 'error';
    public const OFFLINE          = 'offline';
    public const SERVICE_STOPPED  = 'service_stopped';
    public const PROCESS_STOPPED  = 'process_stopped';
    public const FILE_MISSING     = 'file_missing';
    public const VERSION_OUTDATED = 'version_outdated';
    public const UNKNOWN          = 'unknown';

    /** Statuses a component is allowed to report through the heartbeat. */
    public const COMPONENT_REPORTABLE = [
        self::HEALTHY,
        self::WARNING,
        self::ERROR,
        self::SERVICE_STOPPED,
        self::PROCESS_STOPPED,
        self::FILE_MISSING,
        self::VERSION_OUTDATED,
        self::UNKNOWN,
    ];

    /**
     * How bad each status is. The overall machine status is the highest severity
     * among its components. Higher number = worse.
     */
    private const SEVERITY = [
        self::HEALTHY          => 0,
        self::UNKNOWN          => 1,
        self::VERSION_OUTDATED => 2,
        self::WARNING          => 2,
        self::SERVICE_STOPPED  => 3,
        self::PROCESS_STOPPED  => 3,
        self::FILE_MISSING     => 3,
        self::ERROR            => 3,
        self::OFFLINE          => 4,
    ];

    /**
     * The severity-3 statuses all mean "broken", but the machine-level summary
     * collapses them to a single ERROR so the dashboard KPIs stay simple. The
     * per-component detail keeps the precise status.
     */
    public static function overall(array $componentStatuses, bool $offline): string
    {
        if ($offline) {
            return self::OFFLINE;
        }
        if ($componentStatuses === []) {
            return self::UNKNOWN;
        }

        // Track the highest severity directly, so an unmapped status maps to the
        // UNKNOWN severity rather than silently collapsing back to HEALTHY.
        $maxSeverity = self::SEVERITY[self::HEALTHY];
        foreach ($componentStatuses as $status) {
            $key = is_string($status) ? $status : self::UNKNOWN;
            $severity = self::SEVERITY[$key] ?? self::SEVERITY[self::UNKNOWN];
            $maxSeverity = max($maxSeverity, $severity);
        }

        return match ($maxSeverity) {
            0       => self::HEALTHY,
            1       => self::UNKNOWN,
            2       => self::WARNING,
            default => self::ERROR,
        };
    }

    /** Is this a status a component may legitimately report? */
    public static function isValidComponentStatus(string $status): bool
    {
        return in_array($status, self::COMPONENT_REPORTABLE, true);
    }

    /**
     * Component names are slugs so future Ativa components need no code change,
     * while still being strictly validated: lowercase, 1-64 chars, [a-z0-9_].
     */
    public static function isValidComponentName(string $name): bool
    {
        return preg_match('/^[a-z0-9_]{1,64}$/D', $name) === 1;
    }

    /** [label, css class] for a badge in the dashboard. */
    public static function badge(string $status): array
    {
        return match ($status) {
            self::HEALTHY          => ['Saudável', 'bg-success'],
            self::WARNING          => ['Atenção', 'bg-warning text-dark'],
            self::ERROR            => ['Com falha', 'bg-danger'],
            self::OFFLINE          => ['Offline', 'bg-secondary'],
            self::SERVICE_STOPPED  => ['Serviço parado', 'bg-danger'],
            self::PROCESS_STOPPED  => ['Processo parado', 'bg-danger'],
            self::FILE_MISSING     => ['Arquivo ausente', 'bg-danger'],
            self::VERSION_OUTDATED => ['Desatualizado', 'bg-warning text-dark'],
            default                => ['Desconhecido', 'bg-secondary'],
        };
    }
}
