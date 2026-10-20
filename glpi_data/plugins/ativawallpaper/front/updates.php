<?php

declare(strict_types=1);

// The "Atualizacoes" tab was removed: packages are distributed by the Ativa Updater plugin.
// Packages released before keep being served to the clients by the API.
include '../../../inc/includes.php';

// Quem so ve a Visao geral (Ativa - Gestor) nao abre esta pagina.
PluginAtivawallpaperProfile::checkFullAccess();

global $CFG_GLPI;
Html::redirect($CFG_GLPI['root_doc'] . '/plugins/ativawallpaper/front/dashboard.php');
