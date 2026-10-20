<?php

declare(strict_types=1);

class PluginAtivawallpaperProfile extends Profile
{
    public const RIGHT_VIEW = 'plugin_ativawallpaper_view';
    public const RIGHT_PUBLISH = 'plugin_ativawallpaper_publish';
    public const RIGHT_CONFIG = 'plugin_ativawallpaper_config';
    public const RIGHT_CLIENTS = 'plugin_ativawallpaper_clients';

    public static $rightname = 'profile';

    /**
     * "Ativa - Gestor" do Marketing (cargo no Titulo, Categoria ou Grupo do
     * usuario; regra no Ativa Ramal): ve so a Visao geral e publica wallpaper
     * (sempre em "Fill" e com bloqueio: PUBLISH_STYLE / PUBLISH_LOCK), sem
     * configurar nem administrar computadores. Os outros "Ativa - Gestor" nao
     * veem o Ativa Wallpaper.
     */
    public static function applyGestorArea(): void
    {
        if (!self::gestorRuleAvailable() || !PluginAtivaramalProfile::isGestorActive()) {
            return;
        }
        try {
            $marketing = PluginAtivaramalProfile::gestorHasArea(PluginAtivaramalProfile::AREA_MARKETING);
        } catch (Throwable $exception) {
            // Erro no plugin_init faz o GLPI DESATIVAR o plugin: so registra.
            Toolbox::logInFile('ativawallpaper', 'Cargo do Ativa - Gestor nao lido: ' . $exception->getMessage() . "\n");
            $marketing = false;
        }
        $_SESSION['glpiactiveprofile'][self::RIGHT_VIEW] = $marketing ? READ : 0;
        $_SESSION['glpiactiveprofile'][self::RIGHT_PUBLISH] = $marketing ? (READ | UPDATE) : 0;
        $_SESSION['glpiactiveprofile'][self::RIGHT_CONFIG] = 0;
        $_SESSION['glpiactiveprofile'][self::RIGHT_CLIENTS] = 0;
    }

    /** Modo e bloqueio fixos na publicacao de quem so ve a Visao geral. */
    public const PUBLISH_STYLE = 'fill';
    public const PUBLISH_LOCK = true;

    /** So a aba "Visao geral" (sem Historico, Alteracoes, Configuracoes e logs). */
    public static function overviewOnly(): bool
    {
        return self::gestorRuleAvailable() && PluginAtivaramalProfile::isGestorActive();
    }

    /**
     * A regra do "Ativa - Gestor" fica no Ativa Ramal. O GLPI so registra o
     * carregador de classes de um plugin quando chega a vez dele, e o Ativa
     * Wallpaper inicia ANTES do Ativa Ramal: a classe ainda nao existia, o
     * acesso do Marketing nunca era liberado e o menu ficava vazio. Carrega o
     * arquivo direto quando preciso.
     */
    private static function gestorRuleAvailable(): bool
    {
        if (class_exists(PluginAtivaramalProfile::class, false)) {
            return true;
        }
        $file = Plugin::getPhpDir('ativaramal') . '/inc/profile.class.php';
        if (is_file($file)) {
            require_once $file;
        }
        return class_exists(PluginAtivaramalProfile::class, false);
    }

    /** Paginas alem da Visao geral: bloqueadas para quem so ve a Visao geral. */
    public static function checkFullAccess(): void
    {
        Session::checkRight(self::RIGHT_VIEW, READ);
        if (self::overviewOnly()) {
            Html::displayRightError();
        }
    }

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
