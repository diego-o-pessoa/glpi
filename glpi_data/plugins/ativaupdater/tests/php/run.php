<?php

declare(strict_types=1);

require_once __DIR__ . '/../../src/ReleasePolicy.php';
require_once __DIR__ . '/../../src/InstallStatus.php';

use GlpiPlugin\Ativaupdater\InstallStatus;
use GlpiPlugin\Ativaupdater\ReleasePolicy;

function check(bool $condition, string $message = 'check failed'): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$tests = [];

$tests['strict version format'] = static function (): void {
    check(ReleasePolicy::isValidVersion('1.6.0'));
    check(ReleasePolicy::isValidVersion('10.20.300'));
    foreach (['1.6', 'v1.6.0', '1.6.0-beta', '1.6.0.1', '', "1.6.0\n"] as $invalid) {
        check(!ReleasePolicy::isValidVersion($invalid), $invalid);
    }
};

$tests['rollback target must be at least the first supported package'] = static function (): void {
    check(!ReleasePolicy::canRollbackTo('1.5.1'));
    check(ReleasePolicy::canRollbackTo(ReleasePolicy::ROLLBACK_MIN_VERSION));
    check(ReleasePolicy::canRollbackTo('1.10.0'));
    check(!ReleasePolicy::canRollbackTo('invalid'));
};

$tests['client action matrix'] = static function (): void {
    $cases = [
        ['1.6.0', '1.6.1', false, ReleasePolicy::ACTION_UPGRADE],
        ['1.6.1', '1.6.1', true, ReleasePolicy::ACTION_CURRENT],
        ['1.6.1', '1.6.0', false, ReleasePolicy::ACTION_BLOCKED_DOWNGRADE],
        ['1.6.1', '1.6.0', true, ReleasePolicy::ACTION_DOWNGRADE],
        ['1.10.0', '1.9.0', true, ReleasePolicy::ACTION_DOWNGRADE],
        ['1.6.0', '1.5.1', true, ReleasePolicy::ACTION_BLOCKED_DOWNGRADE],
        ['', '1.6.0', false, ReleasePolicy::ACTION_UPGRADE],
        ['1.6.0', 'invalid', true, ReleasePolicy::ACTION_CURRENT],
    ];
    foreach ($cases as [$installed, $target, $allow, $expected]) {
        $actual = ReleasePolicy::clientAction($installed, $target, $allow);
        check($actual === $expected, "{$installed} -> {$target}: expected {$expected}, got {$actual}");
    }
};

$tests['on target depends on the downgrade policy'] = static function (): void {
    check(ReleasePolicy::isOnTarget('1.6.1', '1.6.0', false));
    check(!ReleasePolicy::isOnTarget('1.6.1', '1.6.0', true));
    check(ReleasePolicy::isOnTarget('1.6.0', '1.6.0', true));
    check(!ReleasePolicy::isOnTarget('1.5.1', '1.6.0', false));
    check(!ReleasePolicy::isOnTarget('1.6.0', 'invalid', false));
    check(ReleasePolicy::requiresInstall('1.6.1', '1.6.0', true));
    check(!ReleasePolicy::requiresInstall('1.6.1', '1.6.0', false));
};

$tests['installation start is kept across attempts and cleared when finished'] = static function (): void {
    $now = '2026-09-15 10:00:00';
    $earlier = '2026-09-15 09:00:00';
    check(InstallStatus::installStartedAt('downloading', null, '1.6.0', '1.6.0', $now) === $now);
    check(InstallStatus::installStartedAt('installing', $earlier, '1.6.0', '1.6.0', $now) === $earlier);
    check(InstallStatus::installStartedAt('retrying', $earlier, '1.6.0', '1.6.0', $now) === $earlier);
    check(InstallStatus::installStartedAt('checking', $earlier, '1.6.0', '1.6.0', $now) === $earlier);
    check(InstallStatus::installStartedAt('install_failed', $earlier, '1.6.0', '1.6.0', $now) === $earlier);
    check(InstallStatus::installStartedAt('downloading', $earlier, '1.6.0', '1.6.1', $now) === $now);
    foreach (InstallStatus::FINISHED as $finished) {
        check(InstallStatus::installStartedAt($finished, $earlier, '1.6.0', '1.6.0', $now) === null, $finished);
    }
};

$tests['stuck installations are detected'] = static function (): void {
    $now = strtotime('2026-09-15 12:00:00');
    $recent = '2026-09-15 11:50:00';
    check(!InstallStatus::isStuck('installing', '2026-09-15 11:40:00', $recent, $now));
    check(InstallStatus::isStuck('installing', '2026-09-15 11:00:00', '2026-09-15 11:10:00', $now), 'no contact');
    check(InstallStatus::isStuck('retrying', '2026-09-15 08:30:00', $recent, $now), 'too long');
    check(!InstallStatus::isStuck('current', '2026-09-15 08:30:00', '2026-09-15 08:30:00', $now));
    check(!InstallStatus::isStuck('install_failed', '2026-09-15 08:30:00', '2026-09-15 08:30:00', $now));
    check(!InstallStatus::isStuck('installing', null, $recent, $now));
};

$failed = 0;
foreach ($tests as $name => $test) {
    try {
        $test();
        echo "PASS {$name}\n";
    } catch (Throwable $exception) {
        $failed++;
        echo "FAIL {$name}: {$exception->getMessage()}\n";
    }
}

exit($failed === 0 ? 0 : 1);
