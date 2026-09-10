<?php

declare(strict_types=1);

define('PLUGIN_ATIVAWALLPAPER_VERSION', '1.0.0');

require_once __DIR__ . '/../../src/Security.php';
require_once __DIR__ . '/../../src/Version.php';
require_once __DIR__ . '/../../src/ApiConfigBuilder.php';
require_once __DIR__ . '/../../src/ImageValidator.php';

use GlpiPlugin\Ativawallpaper\ApiConfigBuilder;
use GlpiPlugin\Ativawallpaper\ImageValidator;
use GlpiPlugin\Ativawallpaper\Security;
use GlpiPlugin\Ativawallpaper\Version;

$tests = [];

$tests['wallpaper version sequence'] = static function (): void {
    assert(Version::next(null, '20260908') === '20260908-001');
    assert(Version::next('20260908-009', '20260908') === '20260908-010');
    assert(Version::next('20260907-099', '20260908') === '20260908-001');
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
