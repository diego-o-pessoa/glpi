<?php

declare(strict_types=1);

use GlpiPlugin\Ativacentral\Shell;

include '../../../inc/includes.php';

// Troca, so para a sessao atual, entre o visual novo e o classico do GLPI.
// Tambem serve de saida de emergencia: abrir
//   /plugins/ativacentral/front/toggle.php?mode=classic
// devolve o GLPI original mesmo que o visual novo esteja com problema.
Session::checkCentralAccess();

if (($_GET['mode'] ?? '') === 'classic') {
    $_SESSION[Shell::SESSION_CLASSIC] = 1;
    Html::redirect(Shell::glpiUrl('/front/central.php'));
}

unset($_SESSION[Shell::SESSION_CLASSIC]);
Html::redirect(Shell::url('/front/home.php'));
