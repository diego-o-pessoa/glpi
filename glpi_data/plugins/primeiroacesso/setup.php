<?php
define('PRIMEIROACESSO_VERSION', '1.0.0');

function plugin_init_primeiroacesso() {
    global $PLUGIN_HOOKS;
    $PLUGIN_HOOKS['csrf_compliant']['primeiroacesso'] = true;
    
    // Registra o hook que roda em todas as páginas após carregar a sessão
    $PLUGIN_HOOKS['post_init']['primeiroacesso'] = 'plugin_primeiroacesso_check_location';
    
    // Injeta o arquivo JS nativamente no GLPI
    $PLUGIN_HOOKS['add_javascript']['primeiroacesso'] = ['scripts/primeiroacesso.js'];
}

function plugin_version_primeiroacesso() {
    return [
        'name'           => 'Primeiro Acesso',
        'version'        => PRIMEIROACESSO_VERSION,
        'author'         => 'Antigravity',
        'license'        => 'GPLv2+',
        'homepage'       => '',
        'minGlpiVersion' => '10.0.0'
    ];
}

function plugin_primeiroacesso_check_prerequisites() {
    return true;
}

function plugin_primeiroacesso_check_config() {
    return true;
}

function plugin_primeiroacesso_install() {
    return true;
}

function plugin_primeiroacesso_uninstall() {
    return true;
}
