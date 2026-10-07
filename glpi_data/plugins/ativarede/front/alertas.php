<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativarede\Events;
use GlpiPlugin\Ativarede\Inventory;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

include '../../../inc/includes.php';

// Alertas e historico: mudancas de mesa, monitores e situacoes. Quem pode
// editar a planta autoriza, abre chamado ou ignora.
if (!PluginAtivaredeProfile::canView()) {
    throw new AccessDeniedHttpException();
}

global $CFG_GLPI;
$selfUrl = $CFG_GLPI['root_doc'] . '/plugins/ativarede/front/alertas.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!PluginAtivaredeProfile::canManage()) {
        throw new AccessDeniedHttpException();
    }
    $result = Events::resolve((int) ($_POST['id'] ?? 0), (string) ($_POST['resolution'] ?? ''));
    $message = htmlescape($result['message']);
    if (!empty($result['ticket_url'])) {
        $message .= ' <a href="' . htmlescape($result['ticket_url']) . '">Abrir o chamado</a>';
    }
    Session::addMessageAfterRedirect($message, false, $result['ok'] ? INFO : ERROR);
    Html::redirect($selfUrl . '?' . http_build_query(array_filter([
        'status' => (string) ($_POST['status'] ?? ''),
        'type'   => (string) ($_POST['type'] ?? ''),
    ])));
}

$status = (string) ($_GET['status'] ?? Events::OPEN);
if ($status !== 'all' && !isset(Events::statusLabels()[$status])) {
    $status = Events::OPEN;
}
$type = (string) ($_GET['type'] ?? '');
if ($type !== '' && !isset(Events::labels()[$type])) {
    $type = '';
}

Html::header('Ativa Rede - Alertas', '', 'ativarede', 'alertas');
TemplateRenderer::getInstance()->display('@ativarede/alertas.html.twig', [
    'events'        => Inventory::events($status, $type),
    'status'        => $status,
    'type'          => $type,
    'types'         => Events::labels(),
    'statuses'      => Events::statusLabels(),
    'open_count'    => Inventory::countOpenEvents(),
    'self_url'      => $selfUrl,
    'plant_url'     => $CFG_GLPI['root_doc'] . '/plugins/ativarede/front/planta.php',
    'can_manage'    => PluginAtivaredeProfile::canManage(),
    'css_url'       => $CFG_GLPI['root_doc'] . '/plugins/ativarede/css/ativarede.css?v=' . PLUGIN_ATIVAREDE_VERSION,
]);
Html::footer();
