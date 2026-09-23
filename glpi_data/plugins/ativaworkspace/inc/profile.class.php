<?php

declare(strict_types=1);

use GlpiPlugin\Ativaworkspace\Event;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class PluginAtivaworkspaceProfile extends Profile
{
    public const RIGHT_VIEW         = 'plugin_ativaworkspace_view';
    public const RIGHT_PROVISION    = 'plugin_ativaworkspace_provision';
    public const RIGHT_PROFILES     = 'plugin_ativaworkspace_profiles';
    public const RIGHT_APPLICATIONS = 'plugin_ativaworkspace_applications';
    public const RIGHT_CONFIG       = 'plugin_ativaworkspace_config';

    public static $rightname = 'profile';

    public static function getTypeName($nb = 0): string
    {
        return 'Ativa Workspace';
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0): string
    {
        if ($item instanceof Profile && Session::haveRight('profile', READ)) {
            return self::createTabEntry('Ativa Workspace');
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

    /**
     * Direitos do plugin. Cada um e um campo em glpi_profilerights com bitmask.
     */
    public function getAllRights(): array
    {
        $crud = [
            READ   => __('Read'),
            CREATE => __('Create'),
            UPDATE => __('Update'),
            PURGE  => __('Delete permanently'),
        ];

        return [
            [
                'rights' => [READ => __('Read')],
                'label'  => 'Visualizar Workspace',
                'field'  => self::RIGHT_VIEW,
            ],
            [
                'rights' => [READ => __('Read'), CREATE => __('Create')],
                'label'  => 'Provisionar',
                'field'  => self::RIGHT_PROVISION,
            ],
            [
                'rights' => $crud,
                'label'  => 'Gerenciar perfis',
                'field'  => self::RIGHT_PROFILES,
            ],
            [
                'rights' => $crud,
                'label'  => 'Gerenciar aplicativos',
                'field'  => self::RIGHT_APPLICATIONS,
            ],
            [
                'rights' => [READ => __('Read'), UPDATE => __('Update')],
                'label'  => 'Gerenciar configurações',
                'field'  => self::RIGHT_CONFIG,
            ],
        ];
    }

    /**
     * Todos os bits de um direito, para conceder acesso completo na instalacao.
     */
    private static function fullValue(array $right): int
    {
        return array_sum(array_keys($right['rights']));
    }

    public static function canViewWorkspace(): bool
    {
        return (bool) Session::haveRight(self::RIGHT_VIEW, READ);
    }

    /**
     * Validacao de permissao no backend. Negado: registra um evento SECURITY
     * e exibe a tela de acesso negado do GLPI (encerra a requisicao).
     */
    public static function requireRight(string $right, int $bit): void
    {
        if (Session::haveRight($right, $bit)) {
            return;
        }

        Event::log(Event::LEVEL_SECURITY, 'permission', 'Acesso negado', [
            'right' => $right,
            'bit'   => $bit,
            'page'  => $_SERVER['REQUEST_URI'] ?? '',
        ]);
        throw new AccessDeniedHttpException();
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
            'title'         => 'Ativa Workspace',
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

        // A instalacao pode rodar pelo bin/console (sem sessao). Assim como no
        // Ativa Guardian, todo perfil que administra a configuracao do GLPI
        // recebe acesso completo; os demais ficam sem acesso ate liberar na aba.
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
        $profileIds = array_values(array_unique(array_filter($profileIds)));

        foreach ($profileIds as $profileId) {
            foreach ($instance->getAllRights() as $right) {
                $value = self::fullValue($right);
                // Reinstalar nao reduz o que ja foi concedido.
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

                if (isset($_SESSION['glpiactiveprofile']['id'])
                    && (int) $_SESSION['glpiactiveprofile']['id'] === $profileId
                ) {
                    $_SESSION['glpiactiveprofile'][$right['field']] = $value;
                }
            }
        }
    }

    public static function uninstallRights(): void
    {
        ProfileRight::deleteProfileRights([
            self::RIGHT_VIEW,
            self::RIGHT_PROVISION,
            self::RIGHT_PROFILES,
            self::RIGHT_APPLICATIONS,
            self::RIGHT_CONFIG,
        ]);
    }
}
