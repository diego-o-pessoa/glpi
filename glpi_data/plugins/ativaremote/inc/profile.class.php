<?php

declare(strict_types=1);

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

class PluginAtivaremoteProfile extends ProfileRight
{
    public static $rightname = 'plugin_ativaremote';

    public function getShowRights(): array
    {
        return [
            READ   => 'Ler/Visualizar',
            UPDATE => 'Atualizar/Gerenciar'
        ];
    }

    public static function createAdminAccess(): void
    {
        global $DB;

        // Adiciona direitos na inicializacao se nao existirem
        $count = countElementsInTable('glpi_profilerights', ['name' => self::$rightname]);
        if ($count == 0) {
            ProfileRight::addProfileRights([
                'super-admin' => [self::$rightname => READ | UPDATE],
                'admin'       => [self::$rightname => READ | UPDATE],
            ]);
        }
    }
}
