<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaupdater;

/**
 * Version rules shared by the API, the dashboard and the installer.
 *
 * The Windows service mirrors these rules in unified_updater_service.py
 * (decide_action). Keep both implementations aligned.
 */
final class ReleasePolicy
{
    /**
     * First unified package able to be a rollback target. Older packages
     * re-register the Wallpaper Client with a bootstrap secret that may have
     * been rotated since the build, which aborts the installation halfway.
     */
    public const ROLLBACK_MIN_VERSION = '1.6.0';

    public const ACTION_UPGRADE = 'upgrade';
    public const ACTION_DOWNGRADE = 'downgrade';
    public const ACTION_CURRENT = 'current';
    public const ACTION_BLOCKED_DOWNGRADE = 'blocked_downgrade';

    private const VERSION_PATTERN = '/^\d{1,5}\.\d{1,5}\.\d{1,5}$/D';

    public static function isValidVersion(string $version): bool
    {
        return preg_match(self::VERSION_PATTERN, $version) === 1;
    }

    public static function canRollbackTo(string $version): bool
    {
        return self::isValidVersion($version)
            && version_compare($version, self::ROLLBACK_MIN_VERSION, '>=');
    }

    /**
     * What a computer with $installed must do to follow the published $target.
     */
    public static function clientAction(string $installed, string $target, bool $allowDowngrade): string
    {
        if (!self::isValidVersion($target)) {
            return self::ACTION_CURRENT;
        }
        if (!self::isValidVersion($installed)) {
            return self::ACTION_UPGRADE;
        }

        $comparison = version_compare($installed, $target);
        if ($comparison < 0) {
            return self::ACTION_UPGRADE;
        }
        if ($comparison === 0) {
            return self::ACTION_CURRENT;
        }

        return $allowDowngrade && self::canRollbackTo($target)
            ? self::ACTION_DOWNGRADE
            : self::ACTION_BLOCKED_DOWNGRADE;
    }

    public static function requiresInstall(string $installed, string $target, bool $allowDowngrade): bool
    {
        return in_array(
            self::clientAction($installed, $target, $allowDowngrade),
            [self::ACTION_UPGRADE, self::ACTION_DOWNGRADE],
            true
        );
    }

    /**
     * Whether the computer already complies with the published release: the
     * exact version during a rollback, otherwise the same or a newer version.
     */
    public static function isOnTarget(string $installed, string $target, bool $allowDowngrade): bool
    {
        return self::isValidVersion($target)
            && !self::requiresInstall($installed, $target, $allowDowngrade);
    }
}
