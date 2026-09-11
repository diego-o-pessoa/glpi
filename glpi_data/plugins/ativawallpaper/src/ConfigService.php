<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativawallpaper;

use Config;
use InvalidArgumentException;

final class ConfigService
{
    public const CONTEXT = 'plugin:ativawallpaper';

    public static function defaults(): array
    {
        return [
            'schema_version'             => PLUGIN_ATIVAWALLPAPER_VERSION,
            'enabled'                    => '1',
            'server_url'                 => 'https://chamados.ativalocacao.com.br:8443/plugins/ativawallpaper/api/v1',
            'poll_interval_seconds'      => '60',
            'poll_jitter_seconds'        => '10',
            'offline_after_seconds'      => '3600',
            'max_upload_mb'              => '20',
            'max_image_dimension'        => '16384',
            'minimum_client_version'     => '1.0.0',
            'latest_client_version'      => '1.4.0',
            'registration_secret_hash'   => '',
            'registration_secret_rotated_at' => '',
            'publication_revision'       => '',
            'active_rollout_id'          => '',
            'active_rollout_started_at'  => '',
            'active_rollout_version'     => '',
            'bootstrap_configured'       => '0',
            'setup_completed'            => '0',
            'preserve_data_on_uninstall' => '1',
        ];
    }

    public static function installDefaults(): void
    {
        $existing = Config::getConfigurationValues(self::CONTEXT);
        $missing = array_diff_key(self::defaults(), $existing);
        if ($missing !== []) {
            Config::setConfigurationValues(self::CONTEXT, $missing);
        }
    }

    public static function all(): array
    {
        return array_replace(self::defaults(), Config::getConfigurationValues(self::CONTEXT));
    }

    public static function get(string $name): string
    {
        $values = self::all();
        return (string) ($values[$name] ?? '');
    }

    public static function getInt(string $name): int
    {
        return (int) self::get($name);
    }

    public static function getBool(string $name): bool
    {
        return self::get($name) === '1';
    }

    public static function set(array $values): void
    {
        $allowed = array_keys(self::defaults());
        $unknown = array_diff(array_keys($values), $allowed);
        if ($unknown !== []) {
            throw new InvalidArgumentException('Configuracao desconhecida.');
        }

        $normalized = [];
        foreach ($values as $name => $value) {
            $normalized[$name] = is_bool($value) ? ($value ? '1' : '0') : (string) $value;
        }
        Config::setConfigurationValues(self::CONTEXT, $normalized);
    }

    /** @return array{secret:string, rotated_at:string} */
    public static function rotateRegistrationSecret(): array
    {
        $secret = Security::randomToken(32);
        $rotatedAt = date('Y-m-d H:i:s');
        self::set([
            'registration_secret_hash'       => Security::hashToken($secret),
            'registration_secret_rotated_at' => $rotatedAt,
        ]);

        return ['secret' => $secret, 'rotated_at' => $rotatedAt];
    }

    public static function rotatePublicationRevision(): string
    {
        $revision = date('YmdHis') . '-' . bin2hex(random_bytes(8));
        self::set(['publication_revision' => $revision]);
        return $revision;
    }
}
