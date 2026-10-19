<?php

declare(strict_types=1);

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

class PluginAtivaprintProfile extends ProfileRight
{
    public static $rightname = 'plugin_ativaprint';

    public function getShowRights(): array
    {
        return [
            READ   => 'Ler/Visualizar',
            UPDATE => 'Atualizar/Gerenciar',
            CREATE => 'Adicionar Impressoras',
            DELETE => 'Excluir Impressoras',
        ];
    }

    public static function createAdminAccess(): void
    {
        global $DB;

        $count = countElementsInTable('glpi_profilerights', ['name' => self::$rightname]);
        if ($count == 0) {
            ProfileRight::addProfileRights([self::$rightname]);
        }

        if (isset($_SESSION['glpiactiveprofile']['id'])) {
            $profileId = (int) $_SESSION['glpiactiveprofile']['id'];
            $rights = READ | UPDATE | CREATE | DELETE;
            $DB->update('glpi_profilerights', ['rights' => $rights], [
                'profiles_id' => $profileId,
                'name'        => self::$rightname,
            ]);
            $_SESSION['glpiactiveprofile'][self::$rightname] = $rights;
        }
    }
}

