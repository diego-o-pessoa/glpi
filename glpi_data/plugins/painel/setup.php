<?php
define('PAINEL_VERSION', '1.0.0');

function plugin_init_painel() {
    global $PLUGIN_HOOKS;
    $PLUGIN_HOOKS['csrf_compliant']['painel'] = true;
    
    // Intercepta qualquer followup (mesmo os criados na tela nativa do GLPI)
    $PLUGIN_HOOKS['pre_item_add']['painel'] = [
        'ITILFollowup' => 'plugin_painel_pre_item_add_followup'
    ];
    
    // Roda no post_init para interceptar as URLs e redirecionar
    $PLUGIN_HOOKS['post_init']['painel'] = 'plugin_painel_post_init';
}

function plugin_version_painel() {
    return [
        'name'           => 'Painel de Chamados',
        'version'        => PAINEL_VERSION,
        'author'         => 'Ativa',
        'license'        => 'GPLv2+',
        'homepage'       => '',
        'minGlpiVersion' => '10.0.0'
    ];
}

function plugin_painel_check_prerequisites() {
    return true;
}

function plugin_painel_check_config() {
    return true;
}
