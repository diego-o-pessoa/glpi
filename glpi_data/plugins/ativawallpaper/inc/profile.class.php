<?php

declare(strict_types=1);

class PluginAtivawallpaperProfile extends Profile
{
    public const RIGHT_VIEW = 'plugin_ativawallpaper_view';
    public const RIGHT_PUBLISH = 'plugin_ativawallpaper_publish';
    public const RIGHT_CONFIG = 'plugin_ativawallpaper_config';
    public const RIGHT_CLIENTS = 'plugin_ativawallpaper_clients';

    public static $rightname = 'profile';

    public static function getTypeName($nb = 0): string
    {
        return 'Ativa Wallpaper';
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0): string
    {
        if ($item instanceof Profile && Session::haveRight('profile', READ)) {
            return self::createTabEntry('Ativa Wallpaper');
        }
        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0): bool
    {
        if (!$item instanceof Profile) {
            return false;
        }

        (new self())->showRightsForm((int) $item->getID());
        return true;
    }

    public function getAllRights(): array
    {
        return [
            [
                'rights' => [READ => __('Read')],
                'label'  => 'Ativa Wallpaper - Visualizar',
                'field'  => self::RIGHT_VIEW,
            ],
            [
                'rights' => [READ => __('Read'), UPDATE => __('Update')],
                'label'  => 'Ativa Wallpaper - Publicar',
                'field'  => self::RIGHT_PUBLISH,
            ],
            [
                'rights' => [READ => __('Read'), UPDATE => __('Update')],
                'label'  => 'Ativa Wallpaper - Configurar',
                'field'  => self::RIGHT_CONFIG,
            ],
            [
                'rights' => [READ => __('Read'), UPDATE => __('Update'), PURGE => __('Delete permanently')],
                'label'  => 'Ativa Wallpaper - Administrar clientes',
                'field'  => self::RIGHT_CLIENTS,
            ],
        ];
    }

    private function showRightsForm(int $profilesId): void
    {
        $canEdit = Session::haveRight('profile', UPDATE);
        $profile = new Profile();
        $profile->getFromDB($profilesId);

        echo "<div class='firstbloc'>";
        if ($canEdit) {
            echo "<form method='post' action='" . htmlescape($profile->getFormURL()) . "'>";
        }

        $profile->displayRightsChoiceMatrix($this->getAllRights(), [
            'canedit'       => $canEdit,
            'default_class' => 'tab_bg_2',
            'title'         => 'Ativa Wallpaper',
        ]);

        if ($canEdit) {
            echo "<div class='center'>";
            echo Html::hidden('id', ['value' => $profilesId]);
            echo Html::submit(_sx('button', 'Save'), ['name' => 'update']);
            echo '</div>';
            Html::closeForm();
        }
        echo '</div>';
    }

    public static function installRights(): void
    {
        global $DB;

        $instance = new self();
        foreach ($instance->getAllRights() as $right) {
            if (countElementsInTable('glpi_profilerights', ['name' => $right['field']]) === 0) {
                ProfileRight::addProfileRights([$right['field']]);
            }
        }

        if (!isset($_SESSION['glpiactiveprofile']['id'])) {
            return;
        }

        $profileId = (int) $_SESSION['glpiactiveprofile']['id'];
        foreach ($instance->getAllRights() as $right) {
            $value = match ($right['field']) {
                self::RIGHT_VIEW => READ,
                self::RIGHT_CLIENTS => READ | UPDATE | PURGE,
                default => READ | UPDATE,
            };
            $DB->update('glpi_profilerights', ['rights' => $value], [
                'profiles_id' => $profileId,
                'name'        => $right['field'],
            ]);
            $_SESSION['glpiactiveprofile'][$right['field']] = $value;
        }
    }

    public static function uninstallRights(): void
    {
        ProfileRight::deleteProfileRights([
            self::RIGHT_VIEW,
            self::RIGHT_PUBLISH,
            self::RIGHT_CONFIG,
            self::RIGHT_CLIENTS,
        ]);
    }
}
