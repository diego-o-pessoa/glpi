<?php

declare(strict_types=1);

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 * Direitos do Ativa Ramal (aba "Ativa Ramal" no Perfil do GLPI).
 */
class PluginAtivaramalProfile extends Profile
{
    public const RIGHT_VIEW   = 'plugin_ativaramal_view';
    public const RIGHT_CONFIG = 'plugin_ativaramal_config';
    /** Sem este direito, o dashboard mostra so a filial e os setores do usuario. */
    public const RIGHT_ALL_SECTORS = 'plugin_ativaramal_allsectors';

    public static $rightname = 'profile';

    public static function getTypeName($nb = 0): string
    {
        return 'Ativa Ramal';
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0): string
    {
        if ($item instanceof Profile && Session::haveRight('profile', READ)) {
            return self::createTabEntry('Ativa Ramal');
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
                'label'  => 'Visualizar Ativa Ramal (Dashboard e Desempenho)',
                'field'  => self::RIGHT_VIEW,
            ],
            [
                'rights' => [READ => __('Read')],
                'label'  => 'Ver todos os setores e filiais (sem isso: só a filial e o setor do usuário)',
                'field'  => self::RIGHT_ALL_SECTORS,
            ],
            [
                // UPDATE: alterar credenciais, conectar/desconectar a TW Solutions.
                'rights' => [READ => __('Read'), UPDATE => __('Update')],
                'label'  => 'Gerenciar integração (TW Solutions)',
                'field'  => self::RIGHT_CONFIG,
            ],
        ];
    }

    public static function canView(): bool
    {
        return (bool) Session::haveRight(self::RIGHT_VIEW, READ);
    }

    // ------------------------------------------------------------------
    // Perfil "Ativa - Gestor" dividido pelo cargo da pessoa
    //
    // Um perfil so para os gestores, e o que cada um ve sai do cargo:
    // Comercial -> Ativa Ramal; Marketing -> Ativa Wallpaper (so a Visao
    // geral). O cargo e procurado no Titulo do usuario, na Categoria e nos
    // Grupos dele. Sem "comercial" nem "marketing" em nenhum, fica como era
    // antes (Ativa Ramal), para nao tirar o acesso de quem ja usava.
    // ------------------------------------------------------------------
    public const GESTOR_PROFILE = 'Ativa - Gestor';
    public const AREA_COMERCIAL = 'comercial';
    public const AREA_MARKETING = 'marketing';

    /** @var array<int, list<string>> */
    private static array $areas = [];

    /** O perfil ativo da sessao e o "Ativa - Gestor". */
    public static function isGestorActive(): bool
    {
        return strcasecmp(trim((string) ($_SESSION['glpiactiveprofile']['name'] ?? '')), self::GESTOR_PROFILE) === 0;
    }

    /** @return list<string> Areas do usuario logado (comercial, marketing). */
    public static function gestorAreas(): array
    {
        global $DB;

        $userId = (int) Session::getLoginUserID();
        if ($userId <= 0) {
            return [];
        }
        if (isset(self::$areas[$userId])) {
            return self::$areas[$userId];
        }
        $texts = [];
        $user = $DB->request([
            'SELECT'    => ['glpi_usertitles.name AS title', 'glpi_usercategories.name AS category'],
            'FROM'      => 'glpi_users',
            'LEFT JOIN' => [
                'glpi_usertitles'     => ['ON' => ['glpi_users' => 'usertitles_id', 'glpi_usertitles' => 'id']],
                'glpi_usercategories' => ['ON' => ['glpi_users' => 'usercategories_id', 'glpi_usercategories' => 'id']],
            ],
            'WHERE'     => ['glpi_users.id' => $userId],
            'LIMIT'     => 1,
        ])->current();
        $texts[] = (string) ($user['title'] ?? '');
        $texts[] = (string) ($user['category'] ?? '');
        foreach ($DB->request([
            'SELECT'     => ['glpi_groups.completename'],
            'FROM'       => 'glpi_groups_users',
            'INNER JOIN' => ['glpi_groups' => ['ON' => ['glpi_groups_users' => 'groups_id', 'glpi_groups' => 'id']]],
            'WHERE'      => ['glpi_groups_users.users_id' => $userId],
        ]) as $group) {
            $texts[] = (string) $group['completename'];
        }

        $haystack = ' ' . mb_strtolower(implode(' | ', $texts), 'UTF-8') . ' ';
        $areas = [];
        if (str_contains($haystack, 'comercial')) {
            $areas[] = self::AREA_COMERCIAL;
        }
        if (str_contains($haystack, 'marketing') || preg_match('/[^a-z]mkt[^a-z]/', $haystack) === 1) {
            $areas[] = self::AREA_MARKETING;
        }
        return self::$areas[$userId] = $areas !== [] ? $areas : [self::AREA_COMERCIAL];
    }

    public static function gestorHasArea(string $area): bool
    {
        return self::isGestorActive() && in_array($area, self::gestorAreas(), true);
    }

    /**
     * Ajusta, so nesta sessao, o acesso do Ativa - Gestor ao Ativa Ramal pelo
     * cargo. O perfil no banco nao muda. Roda a cada requisicao (plugin_init),
     * entao trocar o cargo vale na proxima pagina.
     */
    public static function applyGestorArea(): void
    {
        if (!self::isGestorActive()) {
            return;
        }
        $_SESSION['glpiactiveprofile'][self::RIGHT_VIEW] = self::gestorHasArea(self::AREA_COMERCIAL) ? READ : 0;
        $_SESSION['glpiactiveprofile'][self::RIGHT_CONFIG] = 0;
        $_SESSION['glpiactiveprofile'][self::RIGHT_ALL_SECTORS] = 0;
    }

    /**
     * Retorna o perfil Ativa - Gestor disponivel para a sessao atual quando
     * ele ainda nao e o perfil ativo. O endpoint nativo do GLPI valida
     * novamente essa lista e o token CSRF antes de efetuar a troca.
     */
    public static function availableGestorProfileId(): ?int
    {
        global $DB;

        // A lista de perfis e montada no login. Se um administrador atribuir o
        // perfil depois que o usuario ja estiver conectado, a sessao antiga
        // nao o enxerga e o atalho desaparece. Recarregue a lista uma vez por
        // requisicao antes de consulta-la; isso nao cria nenhuma atribuicao.
        $loginUserId = (int) Session::getLoginUserID();
        if ($loginUserId > 0) {
            Session::initEntityProfiles($loginUserId);
        }

        $active = (int) ($_SESSION['glpiactiveprofile']['id'] ?? 0);
        foreach (($_SESSION['glpiprofiles'] ?? []) as $profileId => $profile) {
            if (
                (int) $profileId !== $active
                && strcasecmp(trim((string) ($profile['name'] ?? '')), 'Ativa - Gestor') === 0
                && !empty($profile['entities'])
            ) {
                return (int) $profileId;
            }
        }

        // Fallback para sessoes/interface antigas: confirme a atribuicao no
        // banco. O perfil nunca e concedido aqui; somente uma atribuicao real
        // em glpi_profiles_users pode ser usada.
        if ($loginUserId > 0 && $DB->tableExists('glpi_profiles_users')) {
            $profile = $DB->request([
                'SELECT' => ['glpi_profiles.id'],
                'FROM' => 'glpi_profiles_users',
                'INNER JOIN' => [
                    'glpi_profiles' => [
                        'ON' => [
                            'glpi_profiles_users' => 'profiles_id',
                            'glpi_profiles' => 'id',
                        ],
                    ],
                ],
                'WHERE' => [
                    'glpi_profiles_users.users_id' => $loginUserId,
                    'glpi_profiles.name' => 'Ativa - Gestor',
                ],
                'LIMIT' => 1,
            ])->current();

            $profileId = (int) ($profile['id'] ?? 0);
            if ($profileId > 0 && $profileId !== $active) {
                // Garante que Session::changeProfile() possa validar o alvo
                // no endpoint, sem exigir que o usuario faca novo login.
                Session::initEntityProfiles($loginUserId);
                if (!empty($_SESSION['glpiprofiles'][$profileId]['entities'])) {
                    return $profileId;
                }
            }
        }

        return null;
    }

    /** Ve todas as filiais/setores (quem gerencia a integracao tambem). */
    public static function canViewAll(): bool
    {
        return (bool) Session::haveRight(self::RIGHT_ALL_SECTORS, READ)
            || (bool) Session::haveRight(self::RIGHT_CONFIG, UPDATE);
    }

    /**
     * Perfil "so dashboard": ve o Ativa Ramal e nao tem acesso a chamados,
     * ativos nem configuracao do GLPI (ex.: perfil "Ativa - Gestor").
     */
    public static function isDashboardOnly(): bool
    {
        return self::canView()
            && !Session::haveRightsOr('ticket', [CREATE, Ticket::READMY, Ticket::READALL])
            && !Session::haveRight('computer', READ)
            && !Session::haveRight('config', READ);
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
            'title'         => 'Ativa Ramal',
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

        // O perfil comercial restrito e uma configuracao conhecida do
        // ambiente.  Deixe-o pronto para uso apos a instalacao/atualizacao do
        // plugin: interface central para exibir a secao Ativa Ramal, acesso
        // somente ao dashboard/desempenho e escopo resolvido pelo grupo e
        // localizacao do proprio usuario.  Nao conceda configuracao nem
        // visualizacao de todos os setores.
        $gestor = $DB->request([
            'SELECT' => ['id'],
            'FROM'   => 'glpi_profiles',
            'WHERE'  => ['name' => 'Ativa - Gestor'],
            'LIMIT'  => 1,
        ])->current();
        if (is_array($gestor) && (int) ($gestor['id'] ?? 0) > 0) {
            $gestorId = (int) $gestor['id'];
            $DB->update('glpi_profiles', ['interface' => 'central'], ['id' => $gestorId]);
            $DB->update('glpi_profilerights', ['rights' => READ], [
                'profiles_id' => $gestorId,
                'name'        => self::RIGHT_VIEW,
            ]);
            $DB->update('glpi_profilerights', ['rights' => 0], [
                'profiles_id' => $gestorId,
                'name'        => self::RIGHT_CONFIG,
            ]);
            $DB->update('glpi_profilerights', ['rights' => 0], [
                'profiles_id' => $gestorId,
                'name'        => self::RIGHT_ALL_SECTORS,
            ]);
        }
    }

    public static function uninstallRights(): void
    {
        ProfileRight::deleteProfileRights([self::RIGHT_VIEW, self::RIGHT_CONFIG, self::RIGHT_ALL_SECTORS]);
    }
}
