<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativarede\Desks;
use GlpiPlugin\Ativarede\Inventory;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

include '../../../inc/includes.php';

// Lista de maquinas (porta, mesa, usuario), monitores e switches vistos pelo
// LLDP. Quem pode editar a planta da um apelido curto a cada switch.
if (!PluginAtivaredeProfile::canView()) {
    throw new AccessDeniedHttpException();
}

global $CFG_GLPI;
$selfUrl = $CFG_GLPI['root_doc'] . '/plugins/ativarede/front/equipamentos.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!PluginAtivaredeProfile::canManage()) {
        throw new AccessDeniedHttpException();
    }
    $result = Desks::saveSwitchLabel((int) ($_POST['id'] ?? 0), (string) ($_POST['label'] ?? ''));
    Session::addMessageAfterRedirect(htmlescape($result['message']), false, $result['ok'] ? INFO : ERROR);
    Html::redirect($selfUrl . '#switches');
}

$machines = Inventory::decoratedMachines();
foreach ($machines as &$machine) {
    $desk = Inventory::deskAt($machine['switches_id'], $machine['port']);
    $machine['desk'] = $desk['name'] ?? '';
}
unset($machine);

$switches = [];
foreach (Inventory::switches() as $switch) {
    $switch['machines'] = countElementsInTable('glpi_plugin_ativarede_machines', ['switches_id' => (int) $switch['id']]);
    $switch['last_seen'] = Inventory::date($switch['last_seen']);
    $switches[] = $switch;
}

Html::header('Ativa Rede - Equipamentos', '', 'ativarede', 'equipamentos');
TemplateRenderer::getInstance()->display('@ativarede/equipamentos.html.twig', [
    'machines'   => $machines,
    'monitors'   => Inventory::monitors(),
    'switches'   => $switches,
    'self_url'   => $selfUrl,
    'can_manage' => PluginAtivaredeProfile::canManage(),
    'css_url'    => $CFG_GLPI['root_doc'] . '/plugins/ativarede/css/ativarede.css?v=' . PLUGIN_ATIVAREDE_VERSION,
]);
Html::footer();
