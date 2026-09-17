<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativaremote\ClientRepository;
use GlpiPlugin\Ativaremote\Settings;

include '../../../inc/includes.php';

if (!Session::haveRight('plugin_ativaremote', READ) && !Session::haveRight('config', UPDATE)) {
    Html::displayRightError();
}

global $CFG_GLPI;

$limit = max(1, min(200, (int) ($_GET['limit'] ?? 50)));
$total = countElementsInTable(ClientRepository::TABLE);
$pages = max(1, (int) ceil($total / $limit));
$page = min($pages, max(1, (int) ($_GET['page'] ?? 1)));
// haveRight() returns the right bits (int), not a bool.
$canManage = (bool) Session::haveRight('plugin_ativaremote', UPDATE);

$rows = (new ClientRepository())->listForDashboard(($page - 1) * $limit, $limit, $canManage);
$waiting = false;
foreach ($rows as $row) {
    if (in_array($row['remote_access_status'], [ClientRepository::STATUS_PENDING, ClientRepository::STATUS_CLOSING], true)) {
        $waiting = true;
    }
}

$base = $CFG_GLPI['root_doc'] . '/plugins/ativaremote';

Html::header('Ativa Remote', '', 'admin', 'pluginativaremotemenu', 'dashboard');
// GLPI resolves "@ativaremote/..." to plugins/ativaremote/templates/ on disk.
TemplateRenderer::getInstance()->display('@ativaremote/dashboard.html.twig', [
    'clients' => [
        'rows'  => $rows,
        'total' => $total,
        'page'  => $page,
        'pages' => $pages,
        'limit' => $limit,
    ],
    'can_manage'      => $canManage,
    'waiting'         => $waiting,
    'updater_active'  => Plugin::isPluginActive('ativaupdater'),
    'online_seconds'  => ClientRepository::ONLINE_SECONDS,
    'can_configure'   => (bool) Session::haveRight('config', UPDATE),
    'default_password' => Settings::isDefaultTiPassword(),
    'urls' => [
        'action'    => $base . '/front/action.php',
        'connect'   => $base . '/front/connect.php',
        'config'    => $base . '/front/config.php',
        'dashboard' => $base . '/front/dashboard.php',
        'computer'  => $CFG_GLPI['root_doc'] . '/front/computer.form.php',
    ],
]);
Html::footer();
