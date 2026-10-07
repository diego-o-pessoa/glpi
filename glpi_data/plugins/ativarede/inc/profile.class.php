<?php

declare(strict_types=1);

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 * Direitos do Ativa Rede (aba "Ativa Rede" no Perfil do GLPI).
 */
class PluginAtivaredeProfile extends Profile
{
    /** Ver planta, equipamentos e alertas. */
    public const RIGHT_VIEW = 'plugin_ativarede_view';
    /** Editar planta/mesas/switches e tratar alertas. */
    public const RIGHT_MANAGE = 'plugin_ativarede_manage';

    public static $rightname = 'profile';

    public static function getTypeName($nb = 0): string
    {
        return 'Ativa Rede';
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0): string
    {
        if ($item instanceof Profile && Session::haveRight('profile', READ)) {
            return self::createTabEntry('Ativa Rede');
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
                'label'  => 'Visualizar planta, equipamentos e alertas',
                'field'  => self::RIGHT_VIEW,
            ],
            [
                'rights' => [UPDATE => __('Update')],
                'label'  => 'Editar planta e mesas, nomear switches e tratar alertas',
                'field'  => self::RIGHT_MANAGE,
            ],
        ];
    }

    public static function canView(): bool
    {
        return (bool) Session::haveRight(self::RIGHT_VIEW, READ) || self::canManage();
    }

    public static function canManage(): bool
    {
        return (bool) Session::haveRight(self::RIGHT_MANAGE, UPDATE);
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
            'title'         => 'Ativa Rede',
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

    /**
     * Cria os direitos e da acesso completo a quem administra a configuracao
     * do GLPI (como nos demais plugins Ativa). Reinstalar nao reduz direitos.
     */
    public static function installRights(): void
    {
        global $DB;

        $instance = new self();
        foreach ($instance->getAllRights() as $right) {
            if (countElementsInTable('glpi_profilerights', ['name' => $right['field']]) === 0) {
                ProfileRight::addProfileRights([$right['field']]);
            }
        }

        $profileIds = [];
        foreach ($DB->request([
            'SELECT' => ['profiles_id', 'rights'],
            'FROM'   => 'glpi_profilerights',
            'WHERE'  => ['name' => 'config'],
        ]) as $row) {
            if (((int) $row['rights'] & UPDATE) === UPDATE) {
                $profileIds[] = (int) $row['profiles_id'];
            }
        }
        if (isset($_SESSION['glpiactiveprofile']['id'])) {
            $profileIds[] = (int) $_SESSION['glpiactiveprofile']['id'];
        }

        foreach (array_values(array_unique(array_filter($profileIds))) as $profileId) {
            foreach ($instance->getAllRights() as $right) {
                $value = array_sum(array_keys($right['rights']));
                $current = $DB->request([
                    'SELECT' => ['rights'],
                    'FROM'   => 'glpi_profilerights',
                    'WHERE'  => ['profiles_id' => $profileId, 'name' => $right['field']],
                ])->current();
                $value |= (int) ($current['rights'] ?? 0);
                $DB->update('glpi_profilerights', ['rights' => $value], [
                    'profiles_id' => $profileId,
                    'name'        => $right['field'],
                ]);
                if ((int) ($_SESSION['glpiactiveprofile']['id'] ?? 0) === $profileId) {
                    $_SESSION['glpiactiveprofile'][$right['field']] = $value;
                }
            }
        }
    }

    public static function uninstallRights(): void
    {
        ProfileRight::deleteProfileRights([self::RIGHT_VIEW, self::RIGHT_MANAGE]);
    }
}
