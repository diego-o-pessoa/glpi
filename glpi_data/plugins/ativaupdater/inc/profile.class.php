<?php

declare(strict_types=1);

class PluginAtivaupdaterProfile extends Profile
{
    public const RIGHT_VIEW   = 'plugin_ativaupdater_view';
    public const RIGHT_MANAGE = 'plugin_ativaupdater_manage';
    public const RIGHT_CONFIG = 'plugin_ativaupdater_config';

    public static $rightname = 'profile';

    public static function getTypeName($nb = 0): string
    {
        return 'Ativa Updater';
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0): string
    {
        if ($item instanceof Profile && Session::haveRight('profile', READ)) {
            return self::createTabEntry('Ativa Updater');
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
                'rights' => [READ => __('Ler', 'ativaupdater')],
                'label'  => 'Ativa Updater - Visualizar',
                'field'  => self::RIGHT_VIEW,
            ],
            [
                'rights' => [READ => __('Ler', 'ativaupdater'), UPDATE => __('Atualizar', 'ativaupdater')],
                'label'  => 'Ativa Updater - Gerenciar',
                'field'  => self::RIGHT_MANAGE,
            ],
            [
                'rights' => [READ => __('Ler', 'ativaupdater'), UPDATE => __('Atualizar', 'ativaupdater')],
                'label'  => 'Ativa Updater - Configurar',
                'field'  => self::RIGHT_CONFIG,
            ]
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
            'title'         => 'Ativa Updater',
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
            self::RIGHT_MANAGE,
            self::RIGHT_CONFIG,
        ]);
    }
}
