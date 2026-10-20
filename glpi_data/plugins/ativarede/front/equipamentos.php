<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativarede\Branches;
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
    // Filiais por rede ("192.168.80 = Matriz").
    if (($_POST['action'] ?? '') === 'branches') {
        $result = Branches::save((string) ($_POST['rules'] ?? ''));
        Session::addMessageAfterRedirect(htmlescape($result['message']), false, $result['ok'] ? INFO : ERROR);
        Html::redirect($selfUrl . '#branches');
    }
    // Associar roteador/rede local -> filial por MAC
    if (($_POST['action'] ?? '') === 'save_network') {
        Schema::upgrade();
        $gwIp = (string) ($_POST['gateway_ip'] ?? '');
        $gwMac = (string) ($_POST['gateway_mac'] ?? '');
        $name = trim((string) ($_POST['branch_name'] ?? ''));
        if ($name === '__custom__') {
            $name = trim((string) ($_POST['custom_branch_name'] ?? ''));
        }
        $result = Branches::saveNetwork($gwIp, $gwMac, $name);
        Session::addMessageAfterRedirect(htmlescape($result['message']), false, $result['ok'] ? INFO : ERROR);
        Html::redirect($selfUrl . '#branches');
    }
    if (($_POST['action'] ?? '') === 'delete_network') {
        Schema::upgrade();
        $id = (int) ($_POST['id'] ?? 0);
        $result = Branches::deleteNetwork($id);
        Session::addMessageAfterRedirect(htmlescape($result['message']), false, $result['ok'] ? INFO : ERROR);
        Html::redirect($selfUrl . '#branches');
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
$allSwitches = Inventory::switches();
$machines = Inventory::decoratedMachines();
foreach ($machines as &$machine) {
    $desk = Inventory::deskOf($machine);
    $machine['desk'] = $desk['name'] ?? '';
    $machine['branch'] = Branches::ofMachine($machine, $allSwitches);
    $machine['is_roaming'] = Branches::isRoaming($machine, $machine['branch']);
    // Nome principal da linha: a pessoa; sem pessoa, a maquina.
    $machine['title'] = $machine['person'] !== '' ? $machine['person'] : ($machine['computer'] ?: $machine['hostname']);
}
unset($machine);
usort($machines, static fn(array $a, array $b): int => strnatcasecmp($a['title'], $b['title']));

// Uma secao por filial: as com nome em ordem alfabetica, "Sem filial" no fim.
$groups = [];
foreach ($machines as $machine) {
    $groups[$machine['branch']][] = $machine;
}
uksort($groups, static fn(string $a, string $b): int => ($a === '') <=> ($b === '') ?: strnatcasecmp($a, $b));
$branches = [];
foreach ($groups as $name => $list) {
    $branches[] = [
        'name'     => $name,
        'key'      => $name === '' ? '-' : $name,
        'machines' => $list,
        'online'   => count(array_filter($list, static fn(array $m): bool => (bool) $m['online'])),
    ];
}
$counts = [
    'online'     => count(array_filter($machines, static fn(array $m): bool => (bool) $m['online'])),
    'offline'    => count(array_filter($machines, static fn(array $m): bool => !$m['online'])),
    'nodesk'     => count(array_filter($machines, static fn(array $m): bool => $m['desk'] === '')),
    'diagnostic' => count(array_filter($machines, static fn(array $m): bool => $m['diagnostic'] !== '')),
];

$switches = [];
foreach ($allSwitches as $switch) {
    $switch['machines'] = countElementsInTable('glpi_plugin_ativarede_machines', ['switches_id' => (int) $switch['id']]);
    $switch['last_seen'] = Inventory::date($switch['last_seen']);
    $switch['branch'] = Branches::byIp((string) $switch['mgmt_ip']);
    $switches[] = $switch;
}
$asset = static fn(string $file): string => $CFG_GLPI['root_doc'] . '/plugins/ativarede/' . $file . '?v='
    . PLUGIN_ATIVAREDE_VERSION . '.' . (int) @filemtime(PLUGIN_ATIVAREDE_DIR . '/public/' . $file);

Html::header('Ativa Rede - Equipamentos', '', 'ativarede', 'equipamentos');
TemplateRenderer::getInstance()->display('@ativarede/equipamentos.html.twig', [
    'machines'            => $machines,
    'branches'            => $branches,
    'counts'              => $counts,
    'branch_rules'        => Branches::rulesText(),
    'discovered_networks' => Branches::discoveredNetworks(),
    'known_branches'      => Branches::KNOWN_BRANCHES,
    'today'               => date('d/m/Y'),
    'monitors'            => Inventory::monitors(),
    'missing'             => Inventory::guardianWithoutReport(),
    'switches'            => $switches,
    'ignored_switches'    => Inventory::ignoredSwitches(),
    'self_url'            => $selfUrl,
    'can_manage'          => PluginAtivaredeProfile::canManage(),
    // Data do arquivo: CSS/JS novos chegam sem subir a versao do plugin.
    'css_url'             => $asset('css/ativarede.css'),
    'js_url'              => $asset('js/equipamentos.js'),
]);
Html::footer();
