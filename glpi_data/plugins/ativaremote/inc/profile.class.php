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
            ProfileRight::addProfileRights([self::$rightname]);
        }

        if (isset($_SESSION['glpiactiveprofile']['id'])) {
            $profileId = (int) $_SESSION['glpiactiveprofile']['id'];
            $DB->update('glpi_profilerights', ['rights' => READ | UPDATE], [
                'profiles_id' => $profileId,
                'name'        => self::$rightname,
            ]);
            $_SESSION['glpiactiveprofile'][self::$rightname] = READ | UPDATE;
        }
    }
}
