<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativarede\Inventory;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

include '../../../inc/includes.php';

// Planta da sala: cada mesa com a porta do switch, a maquina, os monitores e
// os alertas. A pagina abre com os dados e o navegador atualiza sozinho.
if (!PluginAtivaredeProfile::canView()) {
    throw new AccessDeniedHttpException();
}

global $CFG_GLPI;
$base = $CFG_GLPI['root_doc'] . '/plugins/ativarede';

$plans = Inventory::plans();
$planId = (int) ($_GET['plan'] ?? 0);
if ($planId <= 0 || !in_array($planId, array_map(static fn(array $p): int => (int) $p['id'], $plans), true)) {
    $planId = (int) ($plans[0]['id'] ?? 0);
}

$state = $planId > 0 ? Inventory::planState($planId) : ['plan' => null];

Html::header('Ativa Rede - Planta', '', 'ativarede', 'planta');
TemplateRenderer::getInstance()->display('@ativarede/planta.html.twig', [
    'plans'        => $plans,
    'plan_id'      => $planId,
    'initial_json' => json_encode($state, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE),
    'data_url'     => $base . '/front/planta.data.php',
    // Data dos desenhos: planta corrigida chega sem esperar o cache de 1 h.
    'plans_url'    => $base . '/front/plano.php?v=' . PLUGIN_ATIVAREDE_VERSION . '.'
        . max(array_map('filemtime', glob(PLUGIN_ATIVAREDE_DIR . '/public/plans/*') ?: [__FILE__])) . '&plan=',
    'alerts_url'   => $base . '/front/alertas.php',
    'self_url'     => $base . '/front/planta.php',
    'css_url'      => $base . '/css/ativarede.css?v=' . PLUGIN_ATIVAREDE_VERSION . '.' . (int) @filemtime(PLUGIN_ATIVAREDE_DIR . '/public/css/ativarede.css'),
    // Data do arquivo: o JS novo chega mesmo sem subir a versao do plugin.
    'js_url'       => $base . '/js/planta.js?v=' . PLUGIN_ATIVAREDE_VERSION . '.' . (int) @filemtime(PLUGIN_ATIVAREDE_DIR . '/public/js/planta.js'),
    'can_manage'   => PluginAtivaredeProfile::canManage(),
]);
Html::footer();
