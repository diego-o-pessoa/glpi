<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativawallpaper;

final class ApiConfigBuilder
{
    public static function build(?array $wallpaper, array $client, ?array $settings = null): array
    {
        $settings ??= ConfigService::all();
        $enabled = ($settings['enabled'] ?? '0') === '1' && $wallpaper !== null;
        $config = [
            'enabled'                 => $enabled,
            'config_revision'         => (string) ($settings['publication_revision'] ?? ''),
            'poll_interval_seconds'   => max(60, min(86400, (int) ($settings['poll_interval_seconds'] ?? 900))),
            'poll_jitter_seconds'     => max(0, min(3600, (int) ($settings['poll_jitter_seconds'] ?? 120))),
            'force_reapply'           => (bool) ($client['force_reapply'] ?? false),
            'minimum_client_version'  => (string) ($settings['minimum_client_version'] ?? '1.0.0'),
            'latest_client_version'   => (string) ($settings['latest_client_version'] ?? '1.0.0'),
        ];

        if ($enabled && $wallpaper !== null) {
            $baseUrl = rtrim((string) ($settings['server_url'] ?? ''), '/');
            $config += [
                'wallpaper_id'      => (int) $wallpaper['id'],
                'wallpaper_version' => (string) $wallpaper['version'],
                'download_url'      => $baseUrl . '/wallpaper/' . rawurlencode((string) $wallpaper['version']) . '/download',
                'sha256'            => (string) $wallpaper['sha256'],
                'mime_type'         => (string) $wallpaper['mime_type'],
                'filesize'          => (int) $wallpaper['filesize'],
                'style'             => (string) $wallpaper['style'],
                'lock_change'       => (bool) $wallpaper['lock_change'],
            ];
        }

        return $config;
    }

    public static function etag(array $config): string
    {
        return hash('sha256', (string) json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
