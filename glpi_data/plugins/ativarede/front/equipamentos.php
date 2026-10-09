<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativarede\Collector;
use GlpiPlugin\Ativarede\Desks;
use GlpiPlugin\Ativarede\Inventory;
use GlpiPlugin\Ativarede\Schema;
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
    // "Pedir envio agora" nas maquinas sem posicao.
    if (($_POST['action'] ?? '') === 'collect') {
        $result = Collector::requestAll();
        Session::addMessageAfterRedirect(htmlescape($result['message']), false, $result['ok'] ? INFO : ERROR);
        Html::redirect($selfUrl . '#missing');
    }
    // Nome do usuario da maquina, corrigido a mao.
    if (($_POST['action'] ?? '') === 'machine_user') {
        Schema::upgrade();
        $result = Desks::saveMachineUser((int) ($_POST['id'] ?? 0), (string) ($_POST['user_label'] ?? ''));
        Session::addMessageAfterRedirect(htmlescape($result['message']), false, $result['ok'] ? INFO : ERROR);
        Html::redirect($selfUrl . '#machine-' . (int) ($_POST['id'] ?? 0));
    }
    // "Nao e switch" / desfazer.
    if (in_array($_POST['action'] ?? '', ['ignore_switch', 'restore_switch'], true)) {
        Schema::upgrade();
        $id = (int) ($_POST['id'] ?? 0);
        $result = $_POST['action'] === 'ignore_switch' ? Desks::ignoreSwitch($id) : Desks::restoreSwitch($id);
        Session::addMessageAfterRedirect(htmlescape($result['message']), false, $result['ok'] ? INFO : ERROR);
        Html::redirect($selfUrl . '#switches');
    }
    $result = Desks::saveSwitchLabel((int) ($_POST['id'] ?? 0), (string) ($_POST['label'] ?? ''));
    Session::addMessageAfterRedirect(htmlescape($result['message']), false, $result['ok'] ? INFO : ERROR);
    Html::redirect($selfUrl . '#switches');
}

// Colunas novas (ignored, diagnostic...) antes de listar.
Schema::upgrade();
$machines = Inventory::decoratedMachines();
foreach ($machines as &$machine) {
    $desk = Inventory::deskOf($machine);
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
    'missing'    => Inventory::guardianWithoutReport(),
    'switches'   => $switches,
    'ignored_switches' => Inventory::ignoredSwitches(),
    'self_url'   => $selfUrl,
    'can_manage' => PluginAtivaredeProfile::canManage(),
    'css_url'    => $CFG_GLPI['root_doc'] . '/plugins/ativarede/css/ativarede.css?v=' . PLUGIN_ATIVAREDE_VERSION,
]);
Html::footer();
