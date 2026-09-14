<?php

declare(strict_types=1);

require_once __DIR__ . '/../../src/ReleasePolicy.php';
require_once __DIR__ . '/../../src/ServerClock.php';
require_once __DIR__ . '/../../src/InstallStatus.php';
require_once __DIR__ . '/../../src/ManualCheck.php';

use GlpiPlugin\Ativaupdater\InstallStatus;
use GlpiPlugin\Ativaupdater\ManualCheck;
use GlpiPlugin\Ativaupdater\ReleasePolicy;
use GlpiPlugin\Ativaupdater\ServerClock;

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
    $now = ServerClock::toTimestamp('2026-09-15 12:00:00');
    $recent = '2026-09-15 11:50:00';
    check(!InstallStatus::isStuck('installing', '2026-09-15 11:40:00', $recent, $now));
    check(InstallStatus::isStuck('installing', '2026-09-15 11:00:00', '2026-09-15 11:10:00', $now), 'no contact');
    check(InstallStatus::isStuck('retrying', '2026-09-15 08:30:00', $recent, $now), 'too long');
    check(!InstallStatus::isStuck('current', '2026-09-15 08:30:00', '2026-09-15 08:30:00', $now));
    check(!InstallStatus::isStuck('install_failed', '2026-09-15 08:30:00', '2026-09-15 08:30:00', $now));
    check(!InstallStatus::isStuck('installing', null, $recent, $now));
};

$tests['server clock ignores the logged-in user timezone'] = static function (): void {
    $original = date_default_timezone_get();
    try {
        $before = ServerClock::now();
        // GLPI switches the default timezone to the user preference in web requests.
        date_default_timezone_set('Pacific/Kiritimati');
        $after = ServerClock::now();
        check(abs(ServerClock::toTimestamp($after) - ServerClock::toTimestamp($before)) <= 2, 'timezone switch shifted the clock');
        check(abs(ServerClock::toTimestamp($after) - time()) <= 2, 'round trip');
        check(ServerClock::format(0) === (new DateTimeImmutable('@0'))->setTimezone(ServerClock::timezone())->format('Y-m-d H:i:s'));
        check(ServerClock::toTimestamp('') === 0 && ServerClock::toTimestamp(null) === 0);
    } finally {
        date_default_timezone_set($original);
    }
};

$tests['manual check handshake uses sequences'] = static function (): void {
    $client = ['check_request_seq' => 3, 'check_ack_seq' => 2, 'updater_version' => '1.4.0', 'status' => 'checking'];
    check(ManualCheck::isPending($client));
    $ack = ManualCheck::acknowledgement($client, '2026-09-15 10:00:00');
    check($ack === ['check_ack_seq' => 3, 'check_acknowledged_at' => '2026-09-15 10:00:00']);
    check(!ManualCheck::isPending(array_merge($client, $ack)));
    check(ManualCheck::acknowledgement(array_merge($client, $ack), 'x') === []);
    check(!ManualCheck::isPending([]));

    // Services 1.5.0+ acknowledge only the command they actually received.
    $newer = ['check_request_seq' => 5, 'check_ack_seq' => 2, 'command' => 'reinstall'];
    check(ManualCheck::acknowledgement($newer, 'now', 2) === [], 'report before the command arrived');
    check(ManualCheck::acknowledgement($newer, 'now', 4)['check_ack_seq'] === 4);
    check(ManualCheck::acknowledgement($newer, 'now', 9)['check_ack_seq'] === 5, 'never beyond the request');
    check(ManualCheck::command($newer) === ManualCheck::COMMAND_REINSTALL);
    check(ManualCheck::command(['check_request_seq' => 1, 'check_ack_seq' => 0, 'command' => 'bogus']) === ManualCheck::COMMAND_CHECK);
    check(ManualCheck::command(['check_request_seq' => 1, 'check_ack_seq' => 1, 'command' => 'reinstall']) === '');
};

$tests['remote actions require service 1.5.0'] = static function (): void {
    check(ManualCheck::supports('1.2.0', ManualCheck::COMMAND_CHECK));
    foreach ([ManualCheck::COMMAND_REINSTALL, ManualCheck::COMMAND_RESTART_SERVICE, ManualCheck::COMMAND_SEND_LOGS] as $command) {
        check(!ManualCheck::supports('1.4.0', $command), $command);
        check(ManualCheck::supports('1.5.0', $command), $command);
    }
    check(!ManualCheck::cancelsInstallations('1.4.0') && ManualCheck::cancelsInstallations('1.5.0'));
    $now = time();
    $installing = [
        'check_request_seq' => 1, 'check_ack_seq' => 0, 'status' => 'installing',
        'check_requested_at' => ServerClock::format($now - 10),
    ];
    check(ManualCheck::state($installing + ['updater_version' => '1.5.0'], $now) === ManualCheck::STATE_WAITING, 'new service cancels');
    check(ManualCheck::state($installing + ['updater_version' => '1.4.0'], $now) === ManualCheck::STATE_BUSY, 'old service finishes first');
    check(ManualCheck::state($installing + ['updater_version' => '1.4.0', 'command' => 'send_logs'], $now) === ManualCheck::STATE_UNSUPPORTED);
};

$tests['manual check states'] = static function (): void {
    $now = time();
    $pending = [
        'check_request_seq' => 1,
        'check_ack_seq' => 0,
        'updater_version' => '1.4.0',
        'status' => 'checking',
        'check_requested_at' => ServerClock::format($now - 10),
    ];
    check(ManualCheck::state(['check_request_seq' => 1, 'check_ack_seq' => 1], $now) === ManualCheck::STATE_NONE);
    check(ManualCheck::state($pending, $now) === ManualCheck::STATE_WAITING);
    check(ManualCheck::state(array_merge($pending, ['updater_version' => '1.1.1']), $now) === ManualCheck::STATE_UNSUPPORTED);
    check(ManualCheck::state(array_merge($pending, ['updater_version' => '']), $now) === ManualCheck::STATE_UNSUPPORTED);
    check(ManualCheck::state(array_merge($pending, ['status' => 'installing']), $now) === ManualCheck::STATE_BUSY);
    check(ManualCheck::state(array_merge($pending, ['status' => 'retrying']), $now) === ManualCheck::STATE_WAITING);
    $late = array_merge($pending, ['check_requested_at' => ServerClock::format($now - ManualCheck::RESPONSE_TIMEOUT_SECONDS - 5)]);
    check(ManualCheck::state($late, $now) === ManualCheck::STATE_NO_RESPONSE);
    check(ManualCheck::supports('1.2.0') && !ManualCheck::supports('1.1.9') && !ManualCheck::supports('x'));
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
