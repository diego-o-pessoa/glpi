<?php

declare(strict_types=1);

define('PLUGIN_ATIVAWALLPAPER_VERSION', '1.0.0');

require_once __DIR__ . '/../../src/Security.php';
require_once __DIR__ . '/../../src/Version.php';
require_once __DIR__ . '/../../src/ApiConfigBuilder.php';
require_once __DIR__ . '/../../src/ImageValidator.php';
require_once __DIR__ . '/../../src/ServerClock.php';
require_once __DIR__ . '/../../src/MonitorCycle.php';

use GlpiPlugin\Ativawallpaper\ApiConfigBuilder;
use GlpiPlugin\Ativawallpaper\ImageValidator;
use GlpiPlugin\Ativawallpaper\MonitorCycle;
use GlpiPlugin\Ativawallpaper\Security;
use GlpiPlugin\Ativawallpaper\ServerClock;
use GlpiPlugin\Ativawallpaper\Version;

$tests = [];

$tests['server clock ignores the PHP default timezone'] = static function (): void {
    $previous = date_default_timezone_get();
    try {
        date_default_timezone_set('Pacific/Kiritimati');
        $written = ServerClock::format(1789500000);
        date_default_timezone_set('UTC');
        assert(ServerClock::format(1789500000) === $written);
        assert(ServerClock::toTimestamp($written) === 1789500000);
        assert(ServerClock::toTimestamp('') === 0);
    } finally {
        date_default_timezone_set($previous);
    }
};

$tests['verification cycle'] = static function (): void {
    $wait = 60;
    $grace = 100;
    $client = static fn(string $host, int $lastCheck, int $nextCheck, int $cycleAt, bool $applying = false): array =>
        ['hostname' => $host, 'last_check' => $lastCheck, 'next_check' => $nextCheck, 'cycle_at' => $cycleAt, 'applying' => $applying];

    // Analyzing: A already reported, B is due in 12 s, C stopped reporting long ago.
    $result = MonitorCycle::evaluate(['phase' => 'analyzing', 'started_at' => 1000], [
        $client('A', 1010, 1075, 1010),
        $client('B', 950, 1032, 950),
        $client('C', 100, 160, 100),
    ], 1020, $wait, $grace);
    assert($result['view']['phase'] === 'analyzing');
    assert($result['view']['total'] === 2, 'a computer that stopped reporting is left out');
    assert($result['view']['analyzed'] === 1);
    assert($result['view']['current'] === null);
    assert($result['view']['next']['hostname'] === 'B' && $result['view']['countdown_until'] === 1032);

    // B is due: "Analisando 2 de 2".
    $result = MonitorCycle::evaluate($result['state'], [
        $client('A', 1010, 1075, 1010),
        $client('B', 950, 1032, 950),
    ], 1033, $wait, $grace);
    assert($result['view']['current']['hostname'] === 'B');
    assert($result['view']['countdown_until'] === null);

    // B reported: everything analyzed -> waiting, 100%, countdown to the next cycle.
    $result = MonitorCycle::evaluate($result['state'], [
        $client('A', 1010, 1075, 1010),
        $client('B', 1034, 1100, 1035),
    ], 1036, $wait, $grace);
    assert($result['view']['phase'] === 'waiting');
    assert($result['view']['percentage'] === 100.0);
    assert($result['view']['countdown_until'] === 1035 + $wait);
    assert($result['state']['total'] === 2);

    // Still waiting before the countdown ends; a new analysis starts when it ends.
    $waiting = MonitorCycle::evaluate($result['state'], [], 1090, $wait, $grace);
    assert($waiting['view']['phase'] === 'waiting');
    $next = MonitorCycle::evaluate($result['state'], [
        $client('A', 1080, 1140, 1080),
        $client('B', 1090, 1155, 1090),
    ], 1096, $wait, $grace);
    assert($next['view']['phase'] === 'analyzing' && $next['state']['started_at'] === 1095);
    assert($next['view']['analyzed'] === 0, 'reports from the waiting time do not count');

    // Aplicar novamente: restarted state analyzes at once; a forced client shows as applying.
    $restarted = MonitorCycle::evaluate(['phase' => 'analyzing', 'started_at' => 1200], [
        $client('A', 1201, 1260, 1150, true),
    ], 1202, $wait, $grace);
    assert($restarted['view']['current']['applying'] === true);

    // Invalid or empty state starts analyzing now.
    $fresh = MonitorCycle::evaluate([], [], 5000, $wait, $grace);
    assert($fresh['state']['phase'] === 'analyzing' && $fresh['state']['started_at'] === 5000);
    assert($fresh['view']['total'] === 0);
};

$tests['wallpaper version sequence'] = static function (): void {
    assert(Version::next(null, '20260908') === '20260908-001');
    assert(Version::next('20260908-009', '20260908') === '20260908-010');
    assert(Version::next('20260907-099', '20260908') === '20260908-001');
    assert(Version::latestInSequence(['20260908-002', '20260908-natal', '20260908-010'], '20260908') === '20260908-010');
    assert(Version::latestInSequence(['20260908-natal'], '20260908') === null);
};

$tests['custom wallpaper names'] = static function (): void {
    assert(Version::isValidCustom('20260915-001'));
    assert(Version::isValidCustom('Natal.2026_v2'));
    assert(!Version::isValidCustom('12345'), 'digits only would be read as an id');
    assert(!Version::isValidCustom('-inicio'));
    assert(!Version::isValidCustom('com espaco'));
    assert(!Version::isValidCustom(str_repeat('a', 33)));
};

$tests['token hashing and constant-time verification'] = static function (): void {
    $token = Security::randomToken();
    $hash = Security::hashToken($token);
    assert(strlen($token) >= 43);
    assert(strlen($hash) === 64);
    assert(Security::verifyToken($token, $hash));
    assert(!Security::verifyToken($token . 'x', $hash));
};

$tests['api config construction and etag'] = static function (): void {
    $wallpaper = [
        'id' => 7, 'version' => '20260908-001', 'sha256' => str_repeat('a', 64),
        'mime_type' => 'image/jpeg', 'filesize' => 1234, 'style' => 'fill', 'lock_change' => 0,
    ];
    $settings = [
        'enabled' => '1', 'publication_revision' => 'revision',
        'poll_interval_seconds' => '10', 'poll_jitter_seconds' => '9999',
        'minimum_client_version' => '1.0.0', 'latest_client_version' => '1.0.0',
        'server_url' => 'https://chamados.ativalocacao.com.br:8443/plugins/ativawallpaper/api/v1',
    ];
    $config = ApiConfigBuilder::build($wallpaper, ['force_reapply' => 1, 'rollout_id' => 'rollout-1'], $settings);
    assert($config['enabled'] === true);
    assert($config['poll_interval_seconds'] === 60);
    assert($config['poll_jitter_seconds'] === 3600);
    assert($config['force_reapply'] === true);
    assert($config['rollout_id'] === 'rollout-1');
    assert(str_ends_with($config['download_url'], '/wallpaper/20260908-001/download'));
    assert(strlen(ApiConfigBuilder::etag($config)) === 64);
    $next = ApiConfigBuilder::build($wallpaper, ['force_reapply' => 1, 'rollout_id' => 'rollout-2'], $settings);
    assert(ApiConfigBuilder::etag($config) !== ApiConfigBuilder::etag($next));
};

$tests['image validation rejects disguised executable'] = static function (): void {
    $path = tempnam(sys_get_temp_dir(), 'ativa-test-');
    assert($path !== false);
    file_put_contents($path, "<?php echo 'not an image';");
    try {
        ImageValidator::validate($path, 'payload.jpg', 1024 * 1024, 4096);
        throw new RuntimeException('Disguised executable was accepted.');
    } catch (RuntimeException $exception) {
        assert(str_contains($exception->getMessage(), 'nao e uma imagem'));
    } finally {
        unlink($path);
    }
};

$tests['valid PNG dimensions and MIME'] = static function (): void {
    $path = tempnam(sys_get_temp_dir(), 'ativa-test-');
    assert($path !== false);
    $image = imagecreatetruecolor(16, 9);
    assert($image !== false);
    imagepng($image, $path);
    imagedestroy($image);
    try {
        $metadata = ImageValidator::validate($path, 'wallpaper.png', 1024 * 1024, 4096);
        assert($metadata['mime_type'] === 'image/png');
        assert($metadata['width'] === 16 && $metadata['height'] === 9);
        assert($metadata['extension'] === 'png');
    } finally {
        unlink($path);
    }
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
