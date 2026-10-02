<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativaramal\ExtensionDirectory;
use GlpiPlugin\Ativaramal\Logger;
use GlpiPlugin\Ativaramal\TwApi;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

include '../../../inc/includes.php';

// Filial e setor dos ramais: nome por grupo de captura da TW e ajuste por
// ramal. Usuarios do GLPI com o ramal no Telefone tem prioridade sobre o
// grupo (o ajuste manual tem prioridade sobre tudo).
if (!Session::haveRight(PluginAtivaramalProfile::RIGHT_CONFIG, READ)) {
    throw new AccessDeniedHttpException();
}
$canEdit = (bool) Session::haveRight(PluginAtivaramalProfile::RIGHT_CONFIG, UPDATE);

global $CFG_GLPI, $DB;
$selfUrl = $CFG_GLPI['root_doc'] . '/plugins/ativaramal/front/ramais.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!$canEdit) {
        throw new AccessDeniedHttpException();
    }
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'save_queues') {
        $count = ExtensionDirectory::saveQueueRules(array_values(is_array($_POST['fila'] ?? null) ? $_POST['fila'] : []));
        Logger::info('Filas cadastradas', ['filas' => $count]);
        Session::addMessageAfterRedirect($count . ' fila(s) salva(s).', false, INFO);
    } elseif ($action === 'save_prefixes') {
        $count = ExtensionDirectory::savePrefixRules(array_values(is_array($_POST['faixa'] ?? null) ? $_POST['faixa'] : []));
        Logger::info('Regras por faixa de ramal salvas', ['regras' => $count]);
        Session::addMessageAfterRedirect($count . ' regra(s) por faixa salva(s).', false, INFO);
    } elseif ($action === 'save_groups') {
        $changed = ExtensionDirectory::save(ExtensionDirectory::GROUPS_TABLE, 'callgroup', is_array($_POST['grupo'] ?? null) ? $_POST['grupo'] : []);
        Logger::info('Filial/setor por grupo de captura salvos', ['alterados' => $changed]);
        Session::addMessageAfterRedirect($changed . ' grupo(s) atualizado(s).', false, INFO);
    } elseif ($action === 'save_extensions') {
        $changed = ExtensionDirectory::save(ExtensionDirectory::EXTENSIONS_TABLE, 'ramal', is_array($_POST['ramal'] ?? null) ? $_POST['ramal'] : []);
        Logger::info('Filial/setor por ramal salvos', ['alterados' => $changed]);
        Session::addMessageAfterRedirect($changed . ' ramal(is) atualizado(s).', false, INFO);
    }
    Html::redirect($selfUrl);
}

$error = '';
try {
    $extensions = TwApi::extensions();
} catch (RuntimeException $exception) {
    $extensions = [];
    $error = $exception->getMessage();
}

// Grupos de captura presentes nos ramais, com exemplos de nomes.
$groupRows = [];
$saved = ExtensionDirectory::groups();
foreach ($extensions as $ext) {
    if ((int) ($ext['ativo'] ?? 1) !== 1) {
        continue;
    }
    $group = trim((string) ($ext['callgroup'] ?? ''));
    $key = $group !== '' ? $group : '';
    if ($key === '') {
        continue;
    }
    $groupRows[$key] ??= ['grupo' => $key, 'total' => 0, 'exemplos' => [], 'filial' => $saved[$key]['filial'] ?? '', 'setor' => $saved[$key]['setor'] ?? ''];
    $groupRows[$key]['total']++;
    if (count($groupRows[$key]['exemplos']) < 4) {
        $groupRows[$key]['exemplos'][] = trim((string) ($ext['nome'] ?? ''));
    }
}
uksort($groupRows, 'strnatcmp');

$overrides = ExtensionDirectory::overrides();
$extRows = [];
foreach ($extensions as $ext) {
    if ((int) ($ext['ativo'] ?? 1) !== 1) {
        continue;
    }
    $number = (string) ($ext['ramal'] ?? '');
    $resolved = ExtensionDirectory::resolve($ext);
    $extRows[] = [
        'ramal'    => $number,
        'nome'     => trim((string) ($ext['nome'] ?? '')),
        'grupo'    => (string) ($ext['callgroup'] ?? ''),
        'resolved' => $resolved,
        'filial'   => $overrides[$number]['filial'] ?? '',
        'setor'    => $overrides[$number]['setor'] ?? '',
    ];
}
usort($extRows, static fn ($a, $b) => strnatcmp($a['ramal'], $b['ramal']));

// Sugestoes: localizacoes e grupos do GLPI.
$locations = array_column(iterator_to_array($DB->request(['SELECT' => ['name'], 'DISTINCT' => true, 'FROM' => 'glpi_locations', 'ORDER' => 'name'])), 'name');
$groups = array_column(iterator_to_array($DB->request(['SELECT' => ['name'], 'DISTINCT' => true, 'FROM' => 'glpi_groups', 'ORDER' => 'name'])), 'name');

Html::header('Ativa Ramal - Filial e setor', '', 'ativaramal', 'ramais');
TemplateRenderer::getInstance()->display('@ativaramal/ramais.html.twig', [
    'can_edit'   => $canEdit,
    'error'      => $error,
    'groups'     => array_values($groupRows),
    'extensions' => $extRows,
    'locations'  => $locations,
    'glpi_groups' => $groups,
    'action_url' => $selfUrl,
    'glpi_users' => count(ExtensionDirectory::glpiUsers()),
    'prefixes'   => ExtensionDirectory::prefixRules(),
    'queues'     => ExtensionDirectory::queueRules(),
    'tw_queues'  => TwApi::queueNames(),
]);
Html::footer();
