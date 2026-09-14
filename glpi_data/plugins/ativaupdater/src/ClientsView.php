<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaupdater;

use Html;
use PluginAtivaupdaterRelease;

/**
 * "Computadores" section of the dashboard, rendered both by dashboard.php and
 * by front/clients.php, which the page polls to update it in place.
 */
final class ClientsView
{
    private const TABLE = 'glpi_plugin_ativaupdater_clients';

    /** Columns needed to detect changes; the large log columns are only loaded to render. */
    private const SUMMARY_COLUMNS = [
        'id', 'hostname', 'updater_version', 'installed_version', 'available_version',
        'wallpaper_client_version', 'glpi_agent_version', 'status', 'message', 'last_check',
        'check_requested_at', 'check_request_seq', 'check_ack_seq', 'command',
        'install_started_at', 'diagnostics_at', 'recovery_note', 'recovery_at',
    ];

    private const LABELS = [
        'checking'           => ['Consultando', 'bg-info'],
        'waiting_release'    => ['Aguardando publicação', 'bg-info'],
        'waiting_check'      => ['Aguardando próxima consulta', 'bg-info'],
        'manual_check'       => ['Verificando agora', 'bg-primary'],
        'command_reinstall'  => ['Reinstalação solicitada', 'bg-primary'],
        'command_restart'    => ['Reiniciando serviço', 'bg-primary'],
        'manual_unsupported' => ['Serviço sem Verificar agora', 'bg-secondary'],
        'manual_no_response' => ['Sem resposta ao comando', 'bg-danger'],
        'current'            => ['Atualizado', 'bg-success'],
        'downloading'        => ['Baixando', 'bg-primary'],
        'installing'         => ['Instalando', 'bg-warning text-dark'],
        InstallStatus::STATUS_RETRYING       => ['Nova tentativa', 'bg-warning text-dark'],
        InstallStatus::STATUS_INSTALL_FAILED => ['Falha na Instalação', 'bg-danger'],
        'updated'            => ['Atualizado', 'bg-success'],
        'error'              => ['Erro', 'bg-danger'],
    ];

    /** @return array{clients: array<int, array>, active: ?array, interval: int} */
    public static function load(bool $withLogs = true): array
    {
        global $DB;
        $clients = [];
        if ($DB->tableExists(self::TABLE)) {
            $columns = $withLogs ? array_merge(self::SUMMARY_COLUMNS, ['install_log', 'diagnostics_log']) : self::SUMMARY_COLUMNS;
            foreach ($DB->request(['SELECT' => $columns, 'FROM' => self::TABLE, 'ORDER' => ['last_check DESC', 'id ASC']]) as $row) {
                $clients[] = $row;
            }
        }
        return [
            'clients'  => $clients,
            'active'   => (new PluginAtivaupdaterRelease())->getActiveRelease(),
            'interval' => max(300, min(86400, ConfigService::getInt('check_interval_seconds', 3600))),
        ];
    }

    /**
     * Changes whenever the rendered section would change. Time-based states
     * (no response, stuck installation) are refreshed at least every 30 s.
     */
    public static function signature(array $data, int $now): string
    {
        $active = $data['active'];
        return sha1((string) json_encode([
            array_map(static fn (array $row): array => array_intersect_key($row, array_flip(self::SUMMARY_COLUMNS)), $data['clients']),
            $active === null ? null : [$active['id'], $active['version'], $active['allow_downgrade'] ?? 0, $active['activated_at'] ?? ''],
            $data['interval'],
            intdiv($now, 30),
        ], JSON_INVALID_UTF8_SUBSTITUTE));
    }

    public static function render(array $data, bool $canManage, int $now): string
    {
        $clients = $data['clients'];
        $activeRelease = $data['active'];
        $checkInterval = (int) $data['interval'];
        $activeVersion = $activeRelease ? (string) $activeRelease['version'] : '';
        $activeAllowsDowngrade = $activeRelease !== null && (int) ($activeRelease['allow_downgrade'] ?? 0) === 1;
        $activePublishedAt = $activeRelease
            ? ServerClock::toTimestamp((string) (($activeRelease['activated_at'] ?? '') ?: $activeRelease['created_at']))
            : 0;

        $totalClients = count($clients);
        $updatedClients = 0;
        $errorClients = 0;
        foreach ($clients as $client) {
            if ($activeVersion !== '' && ReleasePolicy::isOnTarget((string) $client['installed_version'], $activeVersion, $activeAllowsDowngrade)) {
                $updatedClients++;
            }
            $clientStatus = (string) $client['status'];
            if (($clientStatus === 'error' && !str_contains((string) ($client['message'] ?? ''), 'NO_RELEASE'))
                || $clientStatus === InstallStatus::STATUS_INSTALL_FAILED
                || InstallStatus::isStuck($clientStatus, $client['install_started_at'] ?? null, (string) $client['last_check'], $now)
            ) {
                $errorClients++;
            }
        }
        $progress = $totalClients > 0 ? (int) round(($updatedClients / $totalClients) * 100) : 0;

        $html = '';
        if ($activeVersion === '') {
            $html .= "<div class='alert alert-info mb-3'>{$totalClients} computador(es) identificado(s). Publique o primeiro instalador unificado para iniciar a distribuição automática.</div>";
        } else {
            $html .= "<div class='d-flex justify-content-between mb-1'><span>{$updatedClients} de {$totalClients} computador(es) na versão atual</span><strong>{$progress}%</strong></div>";
            $html .= "<div class='progress mb-3' style='height: 20px'><div class='progress-bar bg-success' role='progressbar' style='width: {$progress}%' aria-valuenow='{$progress}' aria-valuemin='0' aria-valuemax='100'>{$progress}%</div></div>";
        }
        if ($errorClients > 0) {
            $html .= "<div class='alert alert-danger'>{$errorClients} computador(es) informaram erro. Consulte o motivo e os logs na tabela.</div>";
        }
        if ($totalClients === 0) {
            return $html . "<p class='text-muted mb-0'>Nenhum serviço se identificou ainda. Depois da instalação, a primeira consulta acontece imediatamente.</p>";
        }

        $html .= "<div class='table-responsive'><table class='table table-striped align-middle'><thead><tr>"
            . '<th>Computador</th><th>Pacote unificado</th><th>Serviço</th><th>Wallpaper</th><th>GLPI Agent</th>'
            . '<th>Disponível</th><th>Status</th><th>Última consulta</th><th>Próxima consulta</th><th>Detalhes</th>'
            . '</tr></thead><tbody>';
        foreach ($clients as $client) {
            $html .= self::renderRow($client, $canManage, $now, $activeVersion, $activeAllowsDowngrade, $activePublishedAt, $checkInterval);
        }
        return $html . '</tbody></table></div>';
    }

    private static function renderRow(
        array $client,
        bool $canManage,
        int $now,
        string $activeVersion,
        bool $activeAllowsDowngrade,
        int $activePublishedAt,
        int $checkInterval
    ): string {
        $id = (int) $client['id'];
        $statusKey = (string) $client['status'];
        $lastCheck = (string) $client['last_check'];
        $lastCheckAt = ServerClock::toTimestamp($lastCheck);
        $serviceVersion = (string) ($client['updater_version'] ?? '');
        $versionLabel = $serviceVersion !== '' ? $serviceVersion : 'desconhecida';
        $stuck = InstallStatus::isStuck($statusKey, $client['install_started_at'] ?? null, $lastCheck, $now);
        $manualState = ManualCheck::state($client, $now);
        $pendingCommand = ManualCheck::command($client);
        $nextRegularCheckAt = $lastCheckAt + $checkInterval;
        $nextRegularCheckLabel = $nextRegularCheckAt > $now
            ? Html::convDateTime(ServerClock::format($nextRegularCheckAt))
            : 'a qualquer momento';
        $noReleaseMessage = str_contains((string) ($client['message'] ?? ''), 'NO_RELEASE');
        $clientAction = $activeVersion !== ''
            ? ReleasePolicy::clientAction((string) $client['installed_version'], $activeVersion, $activeAllowsDowngrade)
            : ReleasePolicy::ACTION_CURRENT;
        $needsActiveVersion = in_array($clientAction, [ReleasePolicy::ACTION_UPGRADE, ReleasePolicy::ACTION_DOWNGRADE], true);
        $waitingForNextCheck = $needsActiveVersion
            && ($statusKey === 'waiting_release' || $noReleaseMessage || ($activePublishedAt > 0 && $activePublishedAt > $lastCheckAt));
        $displayMessage = (string) ($client['message'] ?: '-');
        $displayAvailable = (string) $client['available_version'];

        if ($stuck) {
            // Checked before commands: a computer that stopped reporting would
            // otherwise show "Verificando agora" forever.
            $statusKey = InstallStatus::STATUS_INSTALL_FAILED;
            $startedAt = (string) ($client['install_started_at'] ?? '');
            $displayMessage = 'A instalação não foi concluída'
                . ($startedAt !== '' ? ' (iniciada em ' . Html::convDateTime($startedAt) . ')' : '')
                . ' e o computador não informa progresso desde ' . Html::convDateTime($lastCheck) . '. '
                . 'O serviço pode estar parado ou com o instalador travado; o vigia (serviço 1.5.0+) tenta recuperar a cada 15 min. '
                . 'Use "Enviar logs" ou consulte C:\\ProgramData\\AtivaLocacao\\UnifiedUpdater\\logs no computador.';
        } elseif ($manualState === ManualCheck::STATE_UNSUPPORTED) {
            $statusKey = 'manual_unsupported';
            $displayMessage = 'O serviço deste computador (versão ' . $versionLabel . ') é anterior a '
                . ManualCheck::MIN_SERVICE_VERSION . ' e não recebe comandos do dashboard. '
                . 'Ele consultará sozinho no intervalo automático (próxima consulta: ' . $nextRegularCheckLabel . ').';
        } elseif ($manualState === ManualCheck::STATE_BUSY) {
            // Keep the real progress label (Baixando/Instalando).
            $displayMessage = 'Verificação solicitada; o serviço ' . $versionLabel
                . ' fará a verificação quando a instalação em andamento terminar. ' . $displayMessage;
        } elseif ($manualState === ManualCheck::STATE_NO_RESPONSE) {
            $statusKey = 'manual_no_response';
            $displayMessage = 'O serviço não confirmou o comando enviado em '
                . Html::convDateTime((string) $client['check_requested_at'])
                . ' (último contato: ' . Html::convDateTime($lastCheck) . '). '
                . 'Ele pode estar parado ou sem acesso a chamados.ativalocacao.com.br:8443; o vigia tenta religá-lo a cada 15 min.';
        } elseif ($manualState === ManualCheck::STATE_WAITING) {
            switch ($pendingCommand) {
                case ManualCheck::COMMAND_REINSTALL:
                    $statusKey = 'command_reinstall';
                    $displayMessage = 'Reinstalação enviada; o serviço cancela o que estiver fazendo e reinstala o pacote publicado.';
                    break;
                case ManualCheck::COMMAND_RESTART_SERVICE:
                    $statusKey = 'command_restart';
                    $displayMessage = 'Reinício do serviço enviado; ele volta em alguns segundos.';
                    break;
                case ManualCheck::COMMAND_SEND_LOGS:
                    $displayMessage = 'Coleta de logs solicitada; aparece aqui em até 15 segundos. ' . $displayMessage;
                    break;
                default:
                    $statusKey = 'manual_check';
                    $displayAvailable = $activeVersion ?: $displayAvailable;
                    $displayMessage = ManualCheck::cancelsInstallations($serviceVersion)
                        ? 'Comando enviado; o serviço cancela o que estiver fazendo e recomeça a verificação em até 15 segundos.'
                        : 'Comando enviado; o serviço iniciará a consulta em até 15 segundos.';
            }
        } elseif ($waitingForNextCheck) {
            $statusKey = 'waiting_check';
            $displayAvailable = $activeVersion;
            $publishedLabel = $clientAction === ReleasePolicy::ACTION_DOWNGRADE ? 'Rollback autorizado.' : 'Versão publicada.';
            $displayMessage = $nextRegularCheckAt > $now
                ? $publishedLabel . ' Consulta automática prevista até ' . $nextRegularCheckLabel . '.'
                    . (ManualCheck::supports($serviceVersion)
                        ? ' Use "Verificar agora" para antecipar.'
                        : ' Este serviço (versão ' . $versionLabel . ') não aceita "Verificar agora".')
                : $publishedLabel . ' Aguardando o próximo contato automático do serviço.';
        } elseif ($statusKey === 'error' && $noReleaseMessage) {
            $statusKey = 'waiting_release';
            $displayMessage = 'Computador registrado; aguardando a primeira versão publicada.';
        }

        [$statusLabel, $statusClass] = self::LABELS[$statusKey] ?? [$statusKey, 'bg-secondary'];
        $offline = $lastCheckAt < $now - 7200;
        $nextCheckLabel = $manualState === ManualCheck::STATE_WAITING ? 'Em até 15 segundos' : ucfirst($nextRegularCheckLabel);
        $hostname = (string) $client['hostname'];

        $html = '<tr>';
        $html .= '<td><strong>' . htmlescape($hostname) . '</strong>'
            . ($offline ? " <span class='badge bg-secondary'>Sem contato há mais de 2 h</span>" : '')
            . ($canManage ? self::renderActions($id, $hostname, $serviceVersion) : '')
            . '</td>';
        $html .= '<td>' . htmlescape((string) $client['installed_version'])
            . ($clientAction === ReleasePolicy::ACTION_BLOCKED_DOWNGRADE
                ? " <span class='badge bg-secondary' title='Downgrade não autorizado para a versão publicada'>Acima da versão publicada</span>"
                : '')
            . ($clientAction === ReleasePolicy::ACTION_DOWNGRADE ? " <span class='badge bg-warning text-dark'>Rollback pendente</span>" : '')
            . '</td>';
        $html .= '<td>' . htmlescape($serviceVersion !== '' ? $serviceVersion : '-')
            . ($serviceVersion !== '' && !ManualCheck::supports($serviceVersion, ManualCheck::COMMAND_REINSTALL)
                ? " <span class='badge bg-secondary' title='Sem vigia e sem ações remotas (exige " . ManualCheck::REMOTE_ACTIONS_MIN_VERSION . ")'>desatualizado</span>"
                : '')
            . '</td>';
        $html .= '<td>' . htmlescape((string) ($client['wallpaper_client_version'] ?: '-')) . '</td>';
        $html .= '<td>' . htmlescape((string) ($client['glpi_agent_version'] ?: '-')) . '</td>';
        $html .= '<td>' . htmlescape($displayAvailable ?: '-') . '</td>';
        $html .= "<td><span class='badge {$statusClass}'>" . htmlescape($statusLabel) . '</span></td>';
        $html .= '<td>' . Html::convDateTime($lastCheck) . '</td>';
        $html .= '<td>' . htmlescape($nextCheckLabel) . '</td>';
        $html .= '<td>' . htmlescape($displayMessage);

        $recoveryAt = ServerClock::toTimestamp($client['recovery_at'] ?? null);
        if ($recoveryAt > $now - 86400 && trim((string) ($client['recovery_note'] ?? '')) !== '') {
            $html .= "<div class='small text-warning mt-1'><i class='fas fa-shield-alt me-1'></i>"
                . htmlescape((string) $client['recovery_note']) . '</div>';
        }
        $html .= self::renderLog("install-{$id}", 'Ver log da instalação', (string) ($client['install_log'] ?? ''), 'text-danger');
        $diagnosticsAt = (string) ($client['diagnostics_at'] ?? '');
        $html .= self::renderLog(
            "diagnostics-{$id}",
            'Ver logs enviados' . ($diagnosticsAt !== '' ? ' em ' . Html::convDateTime($diagnosticsAt) : ''),
            (string) ($client['diagnostics_log'] ?? ''),
            'text-primary'
        );
        return $html . '</td></tr>';
    }

    private static function renderActions(int $id, string $hostname, string $serviceVersion): string
    {
        $actions = [
            ManualCheck::COMMAND_SEND_LOGS => ['fas fa-file-alt', 'Logs', 'Coletar e enviar os logs deste computador', ''],
            ManualCheck::COMMAND_REINSTALL => [
                'fas fa-redo', 'Reinstalar', 'Reinstalar o pacote publicado',
                "Reinstalar o pacote publicado em {$hostname}? Uma instalação em andamento será cancelada.",
            ],
            ManualCheck::COMMAND_RESTART_SERVICE => [
                'fas fa-power-off', 'Reiniciar serviço', 'Reiniciar o serviço Ativa Unified Updater',
                "Reiniciar o serviço Ativa Unified Updater em {$hostname}?",
            ],
        ];
        $html = "<div class='btn-group btn-group-sm mt-2 d-flex flex-wrap' role='group'>";
        foreach ($actions as $command => [$icon, $label, $title, $confirm]) {
            $supported = ManualCheck::supports($serviceVersion, $command);
            $html .= "<button type='button' class='btn btn-outline-secondary' data-ativaupdater-command='" . htmlescape($command) . "'"
                . " data-client-id='{$id}'"
                . ($confirm !== '' ? " data-confirm='" . htmlescape($confirm) . "'" : '')
                . " title='" . htmlescape($supported ? $title : 'Exige o serviço ' . ManualCheck::REMOTE_ACTIONS_MIN_VERSION . ' ou superior') . "'"
                . ($supported ? '' : ' disabled')
                . "><i class='{$icon} me-1'></i>" . htmlescape($label) . '</button>';
        }
        return $html . '</div>';
    }

    private static function renderLog(string $key, string $summary, string $content, string $summaryClass): string
    {
        if ($content === '') {
            return '';
        }
        return "<details class='mt-2 ativaupdater-log' data-key='" . htmlescape($key) . "'>"
            . "<summary class='" . htmlescape($summaryClass) . "'>" . htmlescape($summary) . '</summary>'
            . "<pre class='mt-2 p-2 bg-light border small' style='max-height: 360px; overflow: auto; white-space: pre-wrap;'>"
            . htmlescape($content)
            . '</pre></details>';
    }
}
