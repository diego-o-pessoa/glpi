<?php

class PluginAtivaupdaterProfile extends ProfileRight
{
    const RIGHT_VIEW   = 'plugin_ativaupdater_view';
    const RIGHT_MANAGE = 'plugin_ativaupdater_manage';
    const RIGHT_CONFIG = 'plugin_ativaupdater_config';

    public static function installRights(): void
    {
        ProfileRight::addProfileRights([
            'super-admin' => [
                self::RIGHT_VIEW   => READ,
                self::RIGHT_MANAGE => UPDATE,
                self::RIGHT_CONFIG => UPDATE,
            ],
            'admin' => [
                self::RIGHT_VIEW   => READ,
                self::RIGHT_MANAGE => UPDATE,
                self::RIGHT_CONFIG => READ,
            ],
        ]);
    }

    public static function uninstallRights(): void
    {
        ProfileRight::deleteProfileRights([
            self::RIGHT_VIEW,
            self::RIGHT_MANAGE,
            self::RIGHT_CONFIG,
        ]);
    }

    public function getShowTabs($options = [])
    {
        $tabs = [];
        if (Session::haveRight('profile', READ)) {
            $tabs[__CLASS__ . '$1'] = __('Ativa Updater', 'ativaupdater');
        }
        return $tabs;
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        self::showForProfile($item);
        return true;
    }

    public static function showForProfile(Profile $profile): void
    {
        $rights = self::getAllRights();
        $profile->displayRightsChoiceMatrix($rights);
    }

    public static function getAllRights(): array
    {
        return [
            [
                'itemtype' => __CLASS__,
                'label'    => __('Visualizar dashboard', 'ativaupdater'),
                'field'    => self::RIGHT_VIEW,
                'rights'   => [READ => __('Ler', 'ativaupdater')],
            ],
            [
                'itemtype' => __CLASS__,
                'label'    => __('Gerenciar releases', 'ativaupdater'),
                'field'    => self::RIGHT_MANAGE,
                'rights'   => [UPDATE => __('Atualizar', 'ativaupdater')],
            ],
            [
                'itemtype' => __CLASS__,
                'label'    => __('Configuracoes', 'ativaupdater'),
                'field'    => self::RIGHT_CONFIG,
                'rights'   => [
                    READ   => __('Ler', 'ativaupdater'),
                    UPDATE => __('Atualizar', 'ativaupdater')
                ],
            ],
        ];
    }
}
