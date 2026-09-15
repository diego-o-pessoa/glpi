<?php

declare(strict_types=1);

use GlpiPlugin\Ativawallpaper\ClientRepository;
use GlpiPlugin\Ativawallpaper\DashboardService;
use GlpiPlugin\Ativawallpaper\WallpaperManager;

include '../../../inc/includes.php';

Session::checkRight(PluginAtivawallpaperProfile::RIGHT_VIEW, READ);

/**
 * Logs of one computer for the "Logs" action of the overview table.
 *
 * The server only knows what the client managed to report. Communication errors
 * (HTTP 404 while the plugin was being updated, server unavailable, revoked token)
 * are written to the log on the computer itself; those lines come from the latest
 * diagnostics collected by the Ativa Updater service of the same computer.
 */
$respond = static function (int $status, array $payload): never {
    session_write_close();
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
};

global $DB, $CFG_GLPI;
$client = (new ClientRepository())->findById((int) ($_GET['id'] ?? 0));
if ($client === null) {
    $respond(404, ['ok' => false, 'message' => 'Computador não encontrado.']);
}

$statusLabels = [
    'updated' => 'Atualizado', 'pending' => 'Pendente', 'offline' => 'Offline',
    'error' => 'Erro', 'revoked' => 'Revogado',
];
$cycleLabels = [
    'already_current'       => 'verificado; wallpaper já estava correto',
    'initial_applied'       => 'wallpaper aplicado pela primeira vez',
    'configuration_applied' => 'nova configuração aplicada',
    'forced_applied'        => 'reaplicação concluída',
    'drift_corrected'       => 'alteração detectada e wallpaper restaurado',
    'disabled'              => 'distribuição desativada',
    'error'                 => 'falha durante a verificação',
];
$value = static fn(mixed $item): string => ($item === null || $item === '') ? '—' : (string) $item;
$status = (new DashboardService())->computeStatus($client, (new WallpaperManager())->current());

// 1. What the server knows.
$summary = [
    'Computador: ' . $client['hostname'],
    'Status no painel: ' . ($statusLabels[$status] ?? $status),
    'Usuário: ' . $value($client['username']),
    'Versão do cliente: ' . $value($client['client_version']),
    'Wallpaper informado: ' . $value($client['wallpaper_version']) . ' (' . $value($client['status']) . ')',
    'Último contato: ' . $value($client['last_check']),
    'Próxima consulta prevista: ' . $value($client['next_check_at']),
    'Última ação do ciclo: ' . ($client['last_cycle_action'] ? ($cycleLabels[$client['last_cycle_action']] ?? $client['last_cycle_action']) . ' em ' . $client['last_cycle_at'] : '—'),
    'Última aplicação: ' . $value($client['last_apply']),
    'Aplicação em massa: ' . $value($client['rollout_status']),
    'IP: ' . $value($client['last_ip']),
    'Registrado em: ' . $value($client['registered_at']),
];
if (!empty($client['revoked_at'])) {
    $summary[] = 'Revogado em: ' . $client['revoked_at'] . ' (precisa ser reinstalado para voltar a sincronizar)';
}
$summary[] = '';
$summary[] = 'Último erro informado ao servidor: ' . (!empty($client['last_error_code']) || !empty($client['last_error'])
    ? '[' . $value($client['last_error_code']) . '] ' . $value($client['last_error'])
    : 'nenhum');
$summary[] = '';
$summary[] = 'Erros de comunicação (HTTP 404, servidor indisponível, token recusado) não chegam ao servidor:';
$summary[] = 'veja "Wallpaper Client: erros recentes", coletado no próprio computador pelo Ativa Updater.';

// 2. Events recorded by the server for this computer.
$events = [];
if ($DB->tableExists('glpi_plugin_ativawallpaper_client_events')) {
    $eventLabels = [
        'initial_applied' => 'Aplicação inicial', 'configuration_applied' => 'Nova configuração aplicada',
        'forced_applied' => 'Reaplicação solicitada concluída', 'drift_corrected' => 'Alteração detectada e restaurada',
    ];
    foreach ($DB->request([
        'FROM'  => 'glpi_plugin_ativawallpaper_client_events',
        'WHERE' => ['clients_id' => (int) $client['id']],
        'ORDER' => ['occurred_at DESC', 'id DESC'],
        'LIMIT' => 30,
    ]) as $event) {
        $events[$event['occurred_at'] . '-e' . $event['id']] = sprintf(
            '%s  %s (wallpaper %s, usuário %s)',
            $event['occurred_at'],
            $eventLabels[$event['event_type']] ?? $event['event_type'],
            $value($event['wallpaper_version']),
            $value($event['username'])
        );
    }
}
foreach ($DB->request([
    'FROM'  => 'glpi_plugin_ativawallpaper_audits',
    'WHERE' => ['target_type' => 'client', 'target_id' => (int) $client['id']],
    'ORDER' => ['created_at DESC', 'id DESC'],
    'LIMIT' => 20,
]) as $audit) {
    $events[$audit['created_at'] . '-a' . $audit['id']] = sprintf(
        '%s  Ação no painel: %s (por %s)',
        $audit['created_at'],
        $audit['action'] === 'revoke_client' ? 'cliente revogado' : $audit['action'],
        getUserName((int) $audit['users_id'])
    );
}
krsort($events);

$sections = [['title' => 'Resumo e último erro', 'content' => implode("\n", $summary)]];

// 3. Logs from the computer, collected by the Ativa Updater service.
$updater = ['available' => false, 'can_collect' => false, 'reason' => ''];
$updaterTable = 'glpi_plugin_ativaupdater_clients';
if (!(new Plugin())->isActivated('ativaupdater') || !$DB->tableExists($updaterTable)) {
    $updater['reason'] = 'O plugin Ativa Updater não está ativo: os logs do computador não podem ser coletados remotamente.';
} else {
    $iterator = $DB->request([
        'SELECT' => ['id', 'updater_version', 'check_request_seq', 'check_ack_seq', 'diagnostics_log', 'diagnostics_at'],
        'FROM'   => $updaterTable,
        'WHERE'  => ['machine_guid' => strtolower((string) $client['machine_guid'])],
        'LIMIT'  => 1,
    ]);
    $row = $iterator->current();
    if (!is_array($row)) {
        $updater['reason'] = 'Este computador não aparece no Ativa Updater: os logs locais não podem ser coletados remotamente.';
    } else {
        $version = (string) $row['updater_version'];
        $hasRight = class_exists(PluginAtivaupdaterProfile::class)
            && (Session::haveRight(PluginAtivaupdaterProfile::RIGHT_MANAGE, UPDATE) || Session::haveRight('config', UPDATE));
        $supported = $version !== '' && version_compare($version, '1.5.0', '>=');
        $updater = [
            'available'      => true,
            'id'             => (int) $row['id'],
            'version'        => $version,
            'request_seq'    => (int) ($row['check_request_seq'] ?? 0),
            'ack_seq'        => (int) ($row['check_ack_seq'] ?? 0),
            'diagnostics_at' => (string) ($row['diagnostics_at'] ?? ''),
            'collect_url'    => $CFG_GLPI['root_doc'] . '/plugins/ativaupdater/front/action.php',
            'can_collect'    => $hasRight && $supported,
            'reason'         => !$supported
                ? sprintf('O serviço Ativa Updater deste computador (%s) não coleta logs; é necessário o 1.5.0 ou superior.', $version ?: 'versão desconhecida')
                : (!$hasRight ? 'Sem permissão no Ativa Updater para pedir uma nova coleta; exibindo a última recebida.' : ''),
        ];

        $diagnostics = trim((string) ($row['diagnostics_log'] ?? ''));
        $wallpaperSections = [];
        if ($diagnostics !== '' && preg_match_all('/^== (.+?) ==$/m', $diagnostics, $headings, PREG_OFFSET_CAPTURE) > 0) {
            foreach ($headings[1] as $index => [$title, $offset]) {
                if (!str_starts_with($title, 'Wallpaper Client')) {
                    continue;
                }
                $start = $headings[0][$index][1] + strlen($headings[0][$index][0]);
                $end = $headings[0][$index + 1][1] ?? strlen($diagnostics);
                $wallpaperSections[] = ['title' => $title, 'content' => trim(substr($diagnostics, $start, $end - $start)) ?: 'Sem conteúdo.'];
            }
        }
        // Errors first: they answer "what went wrong" at a glance.
        usort($wallpaperSections, static fn(array $left, array $right): int =>
            (int) !str_contains($left['title'], 'erros') <=> (int) !str_contains($right['title'], 'erros'));
        $collectedAt = $updater['diagnostics_at'] !== '' ? ' (coletado em ' . $updater['diagnostics_at'] . ')' : '';
        foreach ($wallpaperSections as $section) {
            $sections[] = ['title' => $section['title'], 'content' => $section['content'] . "\n\n" . 'Fonte: Ativa Updater' . $collectedAt];
        }
        if ($wallpaperSections === []) {
            $sections[] = [
                'title'   => 'Log do computador',
                'content' => $diagnostics === ''
                    ? 'Nenhum log foi coletado deste computador ainda.'
                    : 'A última coleta' . $collectedAt . ' não trouxe o log do Wallpaper Client.',
            ];
        } elseif (!array_filter($wallpaperSections, static fn(array $section): bool => str_contains($section['title'], 'erros'))) {
            $updater['reason'] = trim($updater['reason'] . ' O histórico de erros aparece a partir do serviço Ativa Updater 1.6.1; até lá, só as últimas linhas do log.');
        }
    }
}

$sections[] = ['title' => 'Eventos no servidor', 'content' => $events === [] ? 'Nenhum evento registrado.' : implode("\n", $events)];

$respond(200, [
    'ok'       => true,
    'hostname' => (string) $client['hostname'],
    'sections' => $sections,
    'updater'  => $updater,
]);
