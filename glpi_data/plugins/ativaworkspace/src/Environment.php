<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace;

use Plugin;

/**
 * Deteccao do ambiente e dos plugins que o Workspace vai usar. So leitura.
 */
final class Environment
{
    /** Plugins relacionados: diretorio => nome, descricao, icone e pagina principal. */
    private const RELATED = [
        'glpiinventory' => [
            'name'        => 'GLPI Inventory',
            'description' => 'Sincronização de ativos e inventário',
            'icon'        => 'ti ti-box',
            'page'        => '/plugins/glpiinventory/front/menu.php',
        ],
        'ativaremote' => [
            'name'        => 'Ativa Remote',
            'description' => 'Acesso remoto e suporte',
            'icon'        => 'ti ti-device-desktop-share',
            'page'        => '/plugins/ativaremote/front/dashboard.php',
        ],
        'ativaupdater' => [
            'name'        => 'Ativa Updater',
            'description' => 'Gerenciamento de atualizações',
            'icon'        => 'ti ti-refresh',
            'page'        => '/plugins/ativaupdater/front/dashboard.php',
        ],
        'ativawallpaper' => [
            'name'        => 'Ativa Wallpaper',
            'description' => 'Personalização e padronização visual',
            'icon'        => 'ti ti-photo',
            'page'        => '/plugins/ativawallpaper/front/dashboard.php',
        ],
    ];

    /** Verdadeiro quando todos os plugins relacionados estao ativos. */
    public static function allRelatedActive(array $plugins): bool
    {
        foreach ($plugins as $plugin) {
            if ($plugin['state'] !== 'active') {
                return false;
            }
        }
        return $plugins !== [];
    }

    /**
     * @return list<array{name: string, directory: string, description: string, icon: string, url: string, state: string, label: string, badge: string, version: string}>
     */
    public static function relatedPlugins(): array
    {
        global $CFG_GLPI;

        $result = [];
        foreach (self::RELATED as $directory => $meta) {
            $name = $meta['name'];
            $plugin = new Plugin();
            $found  = $plugin->getFromDBbyDir($directory);

            if (!$found) {
                $state = 'missing';
            } elseif ($plugin->isActivated($directory)) {
                $state = 'active';
            } else {
                $state = 'inactive';
            }

            [$label, $badge] = match ($state) {
                'active'   => ['Detectado', 'bg-success'],
                'inactive' => ['Instalado, mas inativo', 'bg-warning'],
                default    => ['Não detectado', 'bg-secondary'],
            };

            $result[] = [
                'name'        => $name,
                'directory'   => $directory,
                'description' => $meta['description'],
                'icon'        => $meta['icon'],
                // Link so quando o plugin esta ativo (senao a pagina nao abre).
                'url'         => $state === 'active' ? $CFG_GLPI['root_doc'] . $meta['page'] : '',
                'state'     => $state,
                'label'     => $label,
                'badge'     => $badge,
                'version'   => $found ? (string) ($plugin->fields['version'] ?? '') : '',
            ];
        }
        return $result;
    }

    /**
     * @return array{workspace: string, glpi: string, php: string}
     */
    public static function versions(): array
    {
        return [
            'workspace' => PLUGIN_ATIVAWORKSPACE_VERSION,
            'glpi'      => defined('GLPI_VERSION') ? GLPI_VERSION : '',
            'php'       => PHP_VERSION,
        ];
    }
}
