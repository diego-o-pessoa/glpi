<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace;

use Plugin;

/**
 * Deteccao do ambiente e dos plugins que o Workspace vai usar. So leitura.
 */
final class Environment
{
    /** Plugins relacionados: diretorio => nome exibido. */
    private const RELATED = [
        'glpiinventory'  => 'GLPI Inventory',
        'ativaupdater'   => 'Ativa Updater',
        'ativawallpaper' => 'Ativa Wallpaper',
        'ativaremote'    => 'Ativa Remote',
    ];

    /**
     * @return list<array{name: string, directory: string, state: string, label: string, badge: string, version: string}>
     */
    public static function relatedPlugins(): array
    {
        $result = [];
        foreach (self::RELATED as $directory => $name) {
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
                'active'   => ['Detectado e ativo', 'bg-success'],
                'inactive' => ['Instalado, mas inativo', 'bg-warning'],
                default    => ['Não detectado', 'bg-secondary'],
            };

            $result[] = [
                'name'      => $name,
                'directory' => $directory,
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
