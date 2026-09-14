<?php

declare(strict_types=1);

require_once __DIR__ . '/../../src/ReleasePolicy.php';

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
