<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativacentral\Settings;
use GlpiPlugin\Ativacentral\Shell;

include '../../../inc/includes.php';

// Opcoes do Ativa Central: ligar/desligar o visual novo e ajustar o painel.
// Aberta por Configurar > Plugins > Ativa Central, pela tela "Configuracoes"
// do visual novo ou direto por este endereco (funciona com o visual desligado).
Session::checkRight(Config::$rightname, UPDATE);

$selfUrl = Shell::url('/front/config.php');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    Settings::save($_POST);
    // Quem salvou volta a ver o que escolheu para todos.
    unset($_SESSION[Shell::SESSION_CLASSIC]);
    Session::addMessageAfterRedirect('Opções do Ativa Central salvas.', false, INFO);
    Html::redirect($selfUrl);
}

Html::header('Ativa Central', '', 'config', 'plugin');
TemplateRenderer::getInstance()->display('@ativacentral/config.html.twig', [
    's'           => Settings::all(),
    'periods'     => Settings::PERIODS,
    'self_url'    => $selfUrl,
    'active'      => Shell::active(),
    'classic'     => !empty($_SESSION[Shell::SESSION_CLASSIC]),
    'classic_url' => Shell::url('/front/toggle.php?mode=classic'),
    'new_url'     => Shell::url('/front/toggle.php?mode=new'),
    'home_url'    => Shell::url('/front/home.php'),
]);
Html::footer();
