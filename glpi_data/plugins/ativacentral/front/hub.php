<?php

declare(strict_types=1);

use GlpiPlugin\Ativacentral\Hubs;
use GlpiPlugin\Ativacentral\Page;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

include '../../../inc/includes.php';

// Tela de modulo: um botao para cada funcao da secao pedida em ?s=.
Session::checkCentralAccess();

$hub = Hubs::get((string) ($_GET['s'] ?? ''));
if ($hub === null) {
    // Secao inexistente ou sem nenhuma funcao liberada para este perfil.
    throw new NotFoundHttpException();
}

Page::render($hub['title'], $hub['sector'], $hub['section'], 'hub', $hub);
