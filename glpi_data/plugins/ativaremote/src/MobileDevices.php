<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaremote;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Config;
use GLPIKey;
use Throwable;

/**
 * Celulares com o app Ativa Suporte (RustDesk da Ativa para Android).
 *
 * - Token proprio dos celulares, separado do token dos computadores (Ativa
 *   Updater): vai no QR code de cadastro e pode ser trocado a qualquer hora.
 * - O celular entra na mesma tabela dos computadores, marcado como "mobile",
 *   com o nome de quem usa. "Pedir ajuda" no celular abre a sessao sozinho
 *   (o colaborador ja autorizou no proprio aparelho).
 * - Colunas novas criadas na hora (git pull basta, sem atualizar o plugin).
 */
final class MobileDevices
{
    private const TOKEN_KEY = 'mobile_token';
    private const SCHEMA_KEY = 'mobile_schema';
    private const SCHEMA_VERSION = '1';

    /** Colunas dos celulares: nome => definicao. */
    public const COLUMNS = [
        'device_type'       => "varchar(16) NOT NULL DEFAULT 'computer'",
        'device_owner'      => 'varchar(128) DEFAULT NULL',
        'help_requested_at' => 'datetime DEFAULT NULL',
    ];

    private static bool $schemaChecked = false;

    public static function ensureSchema(): void
    {
        if (self::$schemaChecked) {
            return;
        }
        self::$schemaChecked = true;
        $config = Config::getConfigurationValues(Settings::CONTEXT, [self::SCHEMA_KEY]);
        if (($config[self::SCHEMA_KEY] ?? '') === self::SCHEMA_VERSION) {
            return;
        }
        global $DB;
        foreach (self::COLUMNS as $column => $definition) {
            if (!$DB->fieldExists(ClientRepository::TABLE, $column, false)) {
                $DB->doQuery(sprintf('ALTER TABLE `%s` ADD `%s` %s', ClientRepository::TABLE, $column, $definition));
            }
        }
        Config::setConfigurationValues(Settings::CONTEXT, [self::SCHEMA_KEY => self::SCHEMA_VERSION]);
    }

    /** Token dos celulares (gerado na primeira vez, guardado criptografado). */
    public static function token(): string
    {
        $config = Config::getConfigurationValues(Settings::CONTEXT, [self::TOKEN_KEY]);
        $stored = (string) ($config[self::TOKEN_KEY] ?? '');
        if ($stored !== '') {
            try {
                $token = (string) (new GLPIKey())->decrypt($stored);
                if (strlen($token) >= 32) {
                    return $token;
                }
            } catch (Throwable) {
                // Chave do GLPI trocada: gera outro token (os celulares recadastram).
            }
        }
        return self::rotateToken();
    }

    /** Novo token: os celulares cadastrados com o anterior param de falar com o GLPI. */
    public static function rotateToken(): string
    {
        $token = bin2hex(random_bytes(24));
        Config::setConfigurationValues(Settings::CONTEXT, [self::TOKEN_KEY => (new GLPIKey())->encrypt($token)]);
        return $token;
    }

    public static function verify(string $token): bool
    {
        if (strlen($token) < 32) {
            return false;
        }
        $config = Config::getConfigurationValues(Settings::CONTEXT, [self::TOKEN_KEY]);
        if (($config[self::TOKEN_KEY] ?? '') === '') {
            return false;
        }
        return hash_equals(self::token(), $token);
    }

    /** Endereco da API para os celulares (URL do GLPI em Configurar > Geral). */
    public static function apiUrl(): string
    {
        global $CFG_GLPI;
        return rtrim((string) ($CFG_GLPI['url_base'] ?? ''), '/') . '/plugins/ativaremote/api/v1/mobile';
    }

    /** Texto do QR de cadastro (lido pelo app). */
    public static function setupUri(): string
    {
        return 'ativasuporte://setup?' . http_build_query(['api' => self::apiUrl(), 'token' => self::token()], '', '&', PHP_QUERY_RFC3986);
    }

    /** QR code de cadastro em SVG (biblioteca que ja vem com o GLPI). */
    public static function setupQrSvg(int $size = 260): string
    {
        $writer = new Writer(new ImageRenderer(new RendererStyle($size, 2), new SvgImageBackEnd()));
        return $writer->writeString(self::setupUri());
    }
}
