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

    /**
     * Ativa Wallpaper's client table. The Updater service runs as SYSTEM and so never sees
     * who is logged in; the Wallpaper client runs per-user (HKCU) and reports it. Both key
     * their rows by MachineGuid, so the logged-in user is read from there when available.
     * The Wallpaper dashboard already reads this table's Updater counterpart the same way.
     */
    private const WALLPAPER_TABLE = 'glpi_plugin_ativawallpaper_clients';

    /** Columns needed to detect changes; the large log columns are only loaded to render. */
    private const SUMMARY_COLUMNS = [
        'id', 'hostname', 'machine_guid', 'updater_version', 'installed_version', 'available_version',
        'wallpaper_client_version', 'glpi_agent_version', 'status', 'message', 'last_check',
        'check_requested_at', 'check_request_seq', 'check_ack_seq', 'command',
        'install_started_at', 'diagnostics_at', 'recovery_note', 'recovery_at',
    ];

    /**
     * Keys attached to each row after the query rather than selected from self::TABLE.
     * Tracked by signature() so the polled section also refreshes when they change.
     */
    private const DERIVED_COLUMNS = ['username', 'wallpaper_last_check'];

    /**
     * A computer whose Wallpaper client reported this recently is powered on and on the
     * network, so silence from the SYSTEM service means the service itself is down --
     * not the machine. Same 2 h window the Updater uses to call a client offline.
     */
    private const POWERED_ON_WINDOW_SECONDS = 7200;

    private const LABELS = [
        'checking'           => ['Consultando', 'bg-info'],
        'waiting_release'    => ['Aguardando publicação', 'bg-info'],
        'waiting_check'      => ['Aguardando próxima consulta', 'bg-info'],
        'manual_check'       => ['Verificando agora', 'bg-primary'],
        'command_reinstall'  => ['Reinstalação solicitada', 'bg-primary'],
        'command_restart'    => ['Reiniciando serviço', 'bg-primary'],
        'manual_unsupported' => ['Serviço sem Verificar agora', 'bg-secondary'],
        'manual_no_response' => ['Sem resposta ao comando', 'bg-danger'],
        'offline'            => ['Sem contato', 'bg-danger'],
        'service_down'       => ['Serviço parado', 'bg-danger'],
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
            $wallpaper = self::wallpaperClients(array_column($clients, 'machine_guid'));
            foreach ($clients as &$client) {
                $reported = $wallpaper[strtolower(trim((string) $client['machine_guid']))] ?? null;
                $client['username'] = $reported['username'] ?? null;
                $client['wallpaper_last_check'] = $reported['last_check'] ?? 0;
            }
            unset($client);
        }
        return [
            'clients'  => $clients,
            'active'   => (new PluginAtivaupdaterRelease())->getActiveRelease(),
            'interval' => max(300, min(86400, ConfigService::getInt('check_interval_seconds', 3600))),
        ];
    }

    /**
     * Logged-in user and last contact of the same computers, as reported by the Ativa
     * Wallpaper client, keyed by lowercase MachineGuid. Returns an empty map when the
     * Wallpaper plugin is not installed, so the dashboard degrades instead of breaking.
     *
     * The Wallpaper stores the GUID with its original case while this plugin lowercases
     * it on write, so rows are re-keyed here rather than trusted to match byte for byte.
     *
     * @param  array<int, mixed> $machineGuids
     * @return array<string, array{username: ?string, last_check: int}>
     */
    private static function wallpaperClients(array $machineGuids): array
    {
        global $DB;
        $guids = array_values(array_unique(array_filter(array_map(
            static fn ($guid): string => strtolower(trim((string) $guid)),
            $machineGuids
        ))));
        if ($guids === [] || !$DB->tableExists(self::WALLPAPER_TABLE)) {
            return [];
        }
        $clients = [];
        $iterator = $DB->request([
            'SELECT' => ['machine_guid', 'username', 'last_check'],
            'FROM'   => self::WALLPAPER_TABLE,
            'WHERE'  => ['machine_guid' => $guids],
        ]);
        foreach ($iterator as $row) {
            $username = trim((string) ($row['username'] ?? ''));
            $clients[strtolower(trim((string) $row['machine_guid']))] = [
                'username'   => $username !== '' ? $username : null,
                'last_check' => ServerClock::toTimestamp($row['last_check'] ?? null),
            ];
        }
        return $clients;
    }

    /**
     * Changes whenever the rendered section would change. Time-based states
     * (no response, stuck installation) are refreshed at least every 30 s.
     */
    public static function signature(array $data, int $now): string
    {
        $active = $data['active'];
        return sha1((string) json_encode([
            array_map(
                static fn (array $row): array => array_intersect_key(
                    $row,
                    array_flip(array_merge(self::SUMMARY_COLUMNS, self::DERIVED_COLUMNS))
                ),
                $data['clients']
            ),
            $active === null ? null : [$active['id'], $active['version'], $active['allow_downgrade'] ?? 0, $active['activated_at'] ?? ''],
            $data['interval'],
            intdiv($now, 30),
        ], JSON_INVALID_UTF8_SUBSTITUTE));
    }

    /** @return array{total: int, updated: int, errors: int, progress: int} */
    public static function metrics(array $data, int $now): array
    {
        $clients = $data['clients'];
        $activeRelease = $data['active'];
        $activeVersion = $activeRelease ? (string) $activeRelease['version'] : '';
        $allowDowngrade = $activeRelease !== null && (int) ($activeRelease['allow_downgrade'] ?? 0) === 1;
        $updated = 0;
        $errors = 0;
        foreach ($clients as $client) {
            $status = (string) $client['status'];
            $hasError = ($status === 'error' && !str_contains((string) ($client['message'] ?? ''), 'NO_RELEASE'))
                || $status === InstallStatus::STATUS_INSTALL_FAILED
                || InstallStatus::isStuck($status, $client['install_started_at'] ?? null, (string) $client['last_check'], $now)
                || ManualCheck::state($client, $now) === ManualCheck::STATE_NO_RESPONSE
                || ServerClock::toTimestamp((string) $client['last_check']) < $now - 7200;
            if ($hasError) {
                $errors++;
            }
            if (!$hasError && $activeVersion !== '' && ReleasePolicy::isOnTarget((string) $client['installed_version'], $activeVersion, $allowDowngrade)) {
                $updated++;
            }
        }
        $total = count($clients);
        return [
            'total' => $total,
            'updated' => $updated,
            'errors' => $errors,
            'progress' => $total > 0 ? (int) round(($updated / $total) * 100) : 0,
        ];
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

        $metrics = self::metrics($data, $now);
        $totalClients = $metrics['total'];
        $errorClients = $metrics['errors'];

        $html = '';
        if ($activeVersion === '') {
            $html .= "<div class='aw-alert aw-alert-info'>{$totalClients} computador(es) identificado(s). Publique o primeiro instalador unificado para iniciar a distribuição automática.</div>";
        }
        if ($errorClients > 0) {
            $html .= "<div class='aw-alert aw-alert-danger'><i class='fas fa-exclamation-circle'></i> {$errorClients} computador(es) requer(em) atenção</div>";
        }
        if ($totalClients === 0) {
            return $html . "<p class='aw-empty'>Nenhum serviço se identificou ainda. Depois da instalação, a primeira consulta acontece imediatamente.</p>";
        }

        $html .= "<div class='table-responsive'><table class='aw-computers-table'><thead><tr>"
            . '<th>Computador</th><th>Versões</th><th>Status</th><th>Última consulta</th><th>Próxima consulta</th><th>Ações</th>'
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

        $offline = $lastCheckAt < $now - 7200;
        // The Wallpaper client runs per-user and reports on its own schedule. If it was
        // heard from while this SYSTEM service was silent, the computer is demonstrably
        // on, which separates "machine off" from "service down" without asking anyone.
        $poweredOn = (int) ($client['wallpaper_last_check'] ?? 0) > $now - self::POWERED_ON_WINDOW_SECONDS;
        if ($offline && !in_array($statusKey, ['error', InstallStatus::STATUS_INSTALL_FAILED], true)) {
            $statusKey = $poweredOn ? 'service_down' : 'offline';
            $displayMessage = $poweredOn
                ? 'O computador está ligado e em rede (o cliente Ativa Wallpaper reportou há pouco), mas o serviço Ativa Unified Updater não fala com o servidor há mais de 2 horas. O vigia tenta religá-lo a cada 15 minutos; se persistir, reinicie o serviço nesta máquina.'
                : 'O computador não entra em contato com o servidor há mais de 2 horas. Verifique se ele está ligado, conectado à rede e se o serviço Ativa Unified Updater está em execução.';
        }
        [$statusLabel, $statusClass] = self::LABELS[$statusKey] ?? [$statusKey, 'bg-secondary'];
        $nextCheckLabel = $manualState === ManualCheck::STATE_WAITING ? 'Em até 15 segundos' : ucfirst($nextRegularCheckLabel);
        $hostname = (string) $client['hostname'];

        $problem = in_array($statusKey, ['error', 'offline', 'service_down', 'manual_no_response', InstallStatus::STATUS_INSTALL_FAILED], true);
        $html = "<tr class='" . ($problem ? 'aw-row-error' : '') . "'>";
        $username = trim((string) ($client['username'] ?? ''));
        $html .= "<td><div class='aw-computer'><i class='fas fa-desktop'></i><div><strong>" . htmlescape($hostname) . '</strong>'
            . ($username !== ''
                ? "<small title='Usuário informado pelo Ativa Wallpaper'><i class='fas fa-user'></i> " . htmlescape($username) . '</small>'
                : '')
            . "<small class='" . ($offline ? 'text-danger' : '') . "'>"
            . ($offline
                ? ($poweredOn ? 'Ligado, mas o serviço parou há mais de 2 h' : 'Sem contato há mais de 2 h')
                : 'Gerenciado pelo Ativa Updater')
            . '</small></div></div></td>';

        $packageExtra = $clientAction === ReleasePolicy::ACTION_BLOCKED_DOWNGRADE
            ? "<span class='aw-mini-note' title='Downgrade não autorizado'>acima da publicada</span>"
            : ($clientAction === ReleasePolicy::ACTION_DOWNGRADE ? "<span class='aw-mini-note text-warning'>rollback pendente</span>" : '');
        $serviceExtra = $serviceVersion !== '' && !ManualCheck::supports($serviceVersion, ManualCheck::COMMAND_REINSTALL)
            ? "<span class='aw-mini-note'>desatualizado</span>" : '';
        $html .= "<td><div class='aw-versions'>"
            . self::versionBlock('Pacote', (string) $client['installed_version'], $packageExtra)
            . self::versionBlock('Updater', $serviceVersion ?: '-', $serviceExtra)
            . self::versionBlock('Wallpaper', (string) ($client['wallpaper_client_version'] ?: '-'))
            . self::versionBlock('Agent', (string) ($client['glpi_agent_version'] ?: '-'))
            . '</div></td>';
        $html .= "<td><span class='badge {$statusClass}'>" . htmlescape($statusLabel) . '</span>'
            . "<small class='aw-status-message'>" . htmlescape($displayMessage) . '</small></td>';
        $html .= '<td>' . Html::convDateTime($lastCheck) . '</td>';
        $html .= '<td>' . htmlescape($nextCheckLabel) . '</td>';
        $html .= '<td>' . ($canManage ? self::renderActions($id, $hostname, $serviceVersion) : '-') . '</td></tr>';

        $recoveryAt = ServerClock::toTimestamp($client['recovery_at'] ?? null);
        $details = '';
        if ($recoveryAt > $now - 86400 && trim((string) ($client['recovery_note'] ?? '')) !== '') {
            $details .= "<div class='small text-warning mt-1'><i class='fas fa-shield-alt me-1'></i>"
                . htmlescape((string) $client['recovery_note']) . '</div>';
        }
        $details .= self::renderLog("install-{$id}", 'Ver log da instalação', (string) ($client['install_log'] ?? ''), 'text-danger');
        $diagnosticsAt = (string) ($client['diagnostics_at'] ?? '');
        $details .= self::renderLog(
            "diagnostics-{$id}",
            'Ver logs enviados' . ($diagnosticsAt !== '' ? ' em ' . Html::convDateTime($diagnosticsAt) : ''),
            (string) ($client['diagnostics_log'] ?? ''),
            'text-primary'
        );

        if ($problem) {
            $logPath = 'C:\\ProgramData\\AtivaLocacao\\UnifiedUpdater\\logs';
            $html .= "<tr class='aw-details-row'><td colspan='6'><div class='aw-problem'>"
                . "<div><strong><i class='fas fa-exclamation-circle'></i> Detalhes do problema</strong><p>"
                . htmlescape($displayMessage) . "</p><code>" . htmlescape($logPath) . "</code>{$details}</div>"
                . "<div><strong><i class='fas fa-wrench'></i> Ações recomendadas</strong><ol>"
                // "Reiniciar" and "Logs" travel on the same polling loop the service
                // itself runs, so a service that stopped answering cannot receive them.
                // Saying otherwise sends people clicking a button that cannot work.
                . ($statusKey === 'service_down'
                    ? '<li>Aguarde até 15 minutos: o vigia tenta religar o serviço sozinho.</li>'
                        . '<li>Se não voltar, reinicie o serviço <strong>na própria máquina</strong> — os botões abaixo dependem do serviço responder e não chegam até ele neste estado.</li>'
                        . '<li>Na máquina, consulte os logs no caminho acima para identificar o que travou.</li>'
                    : '<li>Verifique se o serviço está em execução.</li><li>Tente reiniciar o serviço.</li><li>Consulte os logs para identificar o erro.</li>')
                . '</ol>'
                . ($canManage ? self::restartButton($id, $hostname, $serviceVersion) : '')
                . '</div></div></td></tr>';
        }
        return $html;
    }

    private static function versionBlock(string $label, string $version, string $extra = ''): string
    {
        return "<span><small>" . htmlescape($label) . '</small><strong>' . htmlescape($version) . "</strong>{$extra}</span>";
    }

    private static function renderActions(int $id, string $hostname, string $serviceVersion): string
    {
        $html = "<div class='aw-actions' role='group'>";
        $logsSupported = ManualCheck::supports($serviceVersion, ManualCheck::COMMAND_SEND_LOGS);
        $html .= "<button type='button' class='aw-button aw-button-light' data-aw-open-logs data-client-id='{$id}' data-hostname='"
            . htmlescape($hostname) . "' title='"
            . htmlescape($logsSupported ? 'Abrir e atualizar os logs deste computador' : 'Abrir os últimos logs; a coleta atualizada exige o serviço ' . ManualCheck::REMOTE_ACTIONS_MIN_VERSION . ' ou superior')
            . "'><i class='fas fa-file-alt'></i>Logs</button>";
        $html .= self::restartButton($id, $hostname, $serviceVersion);
        $reinstallSupported = ManualCheck::supports($serviceVersion, ManualCheck::COMMAND_REINSTALL);
        $html .= "<button type='button' class='aw-button aw-button-light' data-ativaupdater-command='" . ManualCheck::COMMAND_REINSTALL
            . "' data-client-id='{$id}' data-confirm='Reinstalar o pacote publicado em " . htmlescape($hostname)
            . "? Uma instalação em andamento será cancelada.' title='"
            . htmlescape($reinstallSupported ? 'Reinstalar o pacote publicado' : 'Clique para consultar o requisito; exige o serviço ' . ManualCheck::REMOTE_ACTIONS_MIN_VERSION . ' ou superior')
            . "'><i class='fas fa-redo'></i>Reinstalar</button>";
        return $html . '</div>';
    }

    private static function restartButton(int $id, string $hostname, string $serviceVersion): string
    {
        $supported = ManualCheck::supports($serviceVersion, ManualCheck::COMMAND_RESTART_SERVICE);
        return "<button type='button' class='aw-button aw-button-light' data-aw-restart-service data-client-id='{$id}' data-hostname='"
            . htmlescape($hostname) . "' title='"
            . htmlescape($supported ? 'Reiniciar o serviço Ativa Unified Updater' : 'Clique para consultar o requisito; exige o serviço ' . ManualCheck::REMOTE_ACTIONS_MIN_VERSION . ' ou superior')
            . "'><i class='fas fa-sync-alt'></i>Reiniciar</button>";
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
