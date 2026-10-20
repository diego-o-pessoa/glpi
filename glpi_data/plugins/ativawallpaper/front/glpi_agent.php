<?php

declare(strict_types=1);

// The "GLPI Agent" tab was removed: the agent is distributed by the Ativa Updater plugin.
include '../../../inc/includes.php';

// Quem so ve a Visao geral (Ativa - Gestor) nao abre esta pagina.
PluginAtivawallpaperProfile::checkFullAccess();

global $CFG_GLPI;
Html::redirect($CFG_GLPI['root_doc'] . '/plugins/ativawallpaper/front/dashboard.php');
