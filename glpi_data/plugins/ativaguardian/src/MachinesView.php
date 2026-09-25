<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaguardian;

use Html;

final class MachinesView
{
    /** Fixed dashboard columns => label. Future components appear on their own row detail. */
    public const COLUMNS = [
        'wallpaper'  => 'Wallpaper',
        'updater'    => 'Updater',
        'remote'     => 'Remote',
        'glpi_agent' => 'GLPI Agent',
        'workspace'  => 'Workspace',
    ];

    /** @return array{machines: array<int, array>, actions: array<int, array>} */
    public static function load(): array
    {
        $machines = MachineRepository::loadAll();
        return [
            'machines' => $machines,
            // Acoes pendentes/em execucao, para a celula mostrar o andamento
            // em vez de oferecer o botao de novo.
            'actions'  => ActionQueue::activeByMachine(array_column($machines, 'id')),
        ];
    }

    /** @param array{machines: array<int, array>} $data */
    public static function metrics(array $data): array
    {
        return MachineRepository::metrics($data['machines']);
    }

    /**
     * Changes whenever the rendered table would change, so the page only swaps
     * its HTML when something actually moved. Includes a coarse clock so the
     * "last contact" relative labels and the offline flip stay fresh.
     *
     * @param array{machines: array<int, array>} $data
     */
    public static function signature(array $data): string
    {
        $shape = [];
        foreach ($data['machines'] as $machine) {
            $shape[] = [
                (int) $machine['id'],
                (string) $machine['last_contact'],
                (string) $machine['effective_status'],
                (string) $machine['guardian_version'],
                (string) ($machine['username'] ?? ''),
                (string) $machine['antivirus'],
                $machine['components'],
                $data['actions'][(int) $machine['id']] ?? [],
            ];
        }
        return sha1((string) json_encode([$shape, intdiv(time(), 30)], JSON_INVALID_UTF8_SUBSTITUTE));
    }

    public static function renderKpis(array $metrics): string
    {
        // $key feeds data-ag-metric (matched by the live-refresh JS); $label is shown.
        $card = static fn(string $modifier, string $icon, string $key, string $label, int $value, string $note): string =>
            "<article class='ag-kpi {$modifier}'><span class='ag-kpi-icon'><i class='fas {$icon}'></i></span>"
            . "<div><div class='ag-kpi-label'>" . htmlescape($label) . "</div>"
            . "<span class='ag-kpi-value' data-ag-metric='" . htmlescape($key) . "'>{$value}</span>"
            . "<span class='ag-kpi-note'>" . htmlescape($note) . "</span></div></article>";

        return "<section class='ag-kpis' aria-label='Resumo'>"
            . $card('', 'fa-desktop', 'total', 'Máquinas', $metrics['total'], 'cadastradas')
            . $card('is-green', 'fa-shield-heart', 'healthy', 'Saudáveis', $metrics['healthy'], 'sem problemas')
            . $card('is-red', 'fa-triangle-exclamation', 'problem', 'Com problema', $metrics['problem'], 'requer atenção')
            . $card('is-slate', 'fa-plug-circle-xmark', 'offline', 'Offline', $metrics['offline'], 'sem contato')
            . '</section>';
    }

    /** @param array{machines: array<int, array>} $data */
    public static function renderTable(array $data, bool $canManage = false): string
    {
        if ($data['machines'] === []) {
            return "<div class='ag-empty'><i class='fas fa-inbox me-2'></i>Nenhuma máquina enviou heartbeat ainda.</div>";
        }

        // Colunas ordenáveis no cliente: a tabela é pequena (uma linha por
        // máquina), então ordenar e filtrar no navegador evita ida ao servidor.
        $sortable = static fn(string $label): string =>
            "<th><button type='button' class='ag-sort'>" . htmlescape($label)
            . "<i class='fas fa-sort'></i></button></th>";

        $head = '<thead><tr>' . $sortable('Máquina') . $sortable('Guardian');
        foreach (self::COLUMNS as $label) {
            $head .= $sortable($label);
        }
        $head .= $sortable('Último contato') . "<th class='ag-col-actions'>Ações</th></tr></thead>";

        $rows = '';
        foreach ($data['machines'] as $machine) {
            $rows .= self::renderRow(
                $machine,
                $data['actions'][(int) $machine['id']] ?? [],
                $canManage
            );
        }

        $total = count($data['machines']);
        $footer = "<div class='ag-table-foot'>"
            . "<span data-ag-count>Mostrando {$total} de {$total} " . ($total === 1 ? 'máquina' : 'máquinas') . '</span>'
            . "<span class='ag-updated'><i class='fas fa-rotate'></i>Última atualização: "
            . "<span data-ag-updated>" . htmlescape(date('d-m-Y H:i:s')) . '</span></span></div>';

        return "<table class='ag-table'>{$head}<tbody>{$rows}</tbody></table>{$footer}";
    }

    private static function renderRow(array $machine, array $actions, bool $canManage): string
    {
        $offline = (bool) $machine['offline'];
        $name = (string) $machine['hostname'] !== '' ? (string) $machine['hostname'] : (string) $machine['machine_id'];
        [$overallLabel, $overallClass] = HealthStatus::badge((string) $machine['effective_status']);

        $antivirus = trim((string) $machine['antivirus']);
        $username = trim((string) ($machine['username'] ?? ''));
        $machineCell = "<td><div class='ag-machine'><i class='fas fa-desktop'></i><div>"
            . '<strong>' . htmlescape($name) . '</strong>'
            // Quem está usando a máquina, como no Ativa Updater. Vazio quando
            // ninguém está logado (tela de bloqueio ou máquina recém-ligada).
            . ($username !== ''
                ? "<small class='ag-user'><i class='fas fa-user'></i> " . htmlescape($username) . '</small>'
                : '')
            . "<small><span class='ag-badge {$overallClass}'>" . htmlescape($overallLabel) . '</span>'
            . ($antivirus !== '' ? ' · ' . htmlescape($antivirus) : '') . '</small>'
            . '</div></div></td>';

        // Guardian itself: a machine we just heard from is, by definition, running it.
        $guardianStatus = $offline ? HealthStatus::OFFLINE : HealthStatus::HEALTHY;
        $guardianCell = self::componentCell($guardianStatus, (string) $machine['guardian_version']);

        $componentCells = '';
        foreach (array_keys(self::COLUMNS) as $key) {
            $component = $machine['components'][$key] ?? null;
            $componentCells .= self::componentCell(
                $component === null ? '' : (string) $component['status'],
                $component === null ? '' : (string) $component['version'],
                $actions[$key] ?? null,
                $component !== null
            );
        }

        $age = self::relativeTime((string) $machine['last_contact']);
        $lastCell = "<td class='ag-last'>" . htmlescape($age['label'])
            . "<small>" . htmlescape($age['absolute']) . '</small></td>';

        $actionsCell = self::actionsMenu($machine, $actions, $canManage && !$offline);

        // data-ag-name alimenta a busca do cabeçalho sem precisar ler o DOM
        // interno. Inclui o usuário: procurar por quem usa a máquina é tão útil
        // quanto procurar pelo nome dela.
        $searchable = mb_strtolower(trim($name . ' ' . $username));
        return "<tr data-ag-name='" . htmlescape($searchable) . "'>"
            . "{$machineCell}{$guardianCell}{$componentCells}{$lastCell}{$actionsCell}</tr>";
    }

    /**
     * Menu "⋮" da linha: reúne as ações disponíveis de todos os componentes.
     *
     * Substituiu os botões soltos em cada célula — eles poluíam a tabela e, no
     * caso do "Verificar novamente", ofereciam manualmente algo que o Guardian
     * já faz sozinho a cada 30 s.
     */
    private static function actionsMenu(array $machine, array $actions, bool $canManage): string
    {
        if (!$canManage) {
            return "<td class='ag-col-actions'><span class='ag-comp-ver'>—</span></td>";
        }

        $machinesId = (int) $machine['id'];
        $items = '';
        foreach (self::COLUMNS as $key => $label) {
            $component = $machine['components'][$key] ?? null;
            if ($component === null || isset($actions[$key])) {
                continue; // não reportado, ou já tem ação na fila
            }
            foreach (self::offeredActions($key, (string) $component['status']) as $action => [$text, $icon]) {
                $items .= "<button type='button' class='ag-menu-item' data-ag-action='" . htmlescape($action)
                    . "' data-ag-component='" . htmlescape($key) . "' data-ag-machine='{$machinesId}'>"
                    . "<i class='fas {$icon}'></i>" . htmlescape($label . ' — ' . $text) . '</button>';
            }
        }

        if ($items === '') {
            return "<td class='ag-col-actions'><button type='button' class='ag-kebab' disabled"
                . " title='Nenhuma ação disponível'><i class='fas fa-ellipsis-vertical'></i></button></td>";
        }

        return "<td class='ag-col-actions'><div class='ag-menu'>"
            . "<button type='button' class='ag-kebab' data-ag-menu title='Ações'>"
            . "<i class='fas fa-ellipsis-vertical'></i></button>"
            . "<div class='ag-menu-list' hidden>{$items}</div></div></td>";
    }

    /**
     * Ação de "Corrigir" para um componente no estado atual.
     *
     * Um botão só, em vez de Iniciar/Reiniciar/Reparar separados: quem opera
     * quer que o componente volte a funcionar, não escolher o procedimento. O
     * estado decide o que é feito de fato — serviço parado é iniciado,
     * executável ausente é reinstalado — e o modal mostra qual foi.
     *
     * A ação enviada continua sendo uma das já conhecidas pelo Guardian; nada
     * de verbo novo no contrato.
     */
    private static function offeredActions(string $component, string $status): array
    {
        $supported = ActionQueue::SUPPORTED[$component] ?? [];
        if (!in_array(ActionQueue::FIX, $supported, true)) {
            return [];
        }

        // O status aqui serve só para decidir se vale oferecer o botão; quem
        // escolhe o procedimento é a máquina, com uma verificação feita na hora.
        return [ActionQueue::FIX => ['Corrigir', 'fa-wrench']];
    }

    /**
     * Badge + versão do componente, e o andamento quando há ação na fila.
     *
     * Não desenha botões: as ações vivem no menu "⋮" da linha.
     */
    private static function componentCell(
        string $status,
        string $version,
        ?array $action = null,
        bool $reported = true
    ): string {
        if (!$reported) {
            return "<td><span class='ag-comp-ver'>—</span></td>";
        }

        [$label, $class] = HealthStatus::badge($status);
        $versionLine = $version !== ''
            ? "<span class='ag-comp-ver'>" . htmlescape($version) . '</span>'
            : "<span class='ag-comp-ver'>—</span>";

        // Sem texto de andamento aqui: o progresso da ação aparece no modal
        // bloqueante aberto no clique, como no Ativa Updater.
        return "<td><div class='ag-comp'><span class='ag-badge {$class}'>" . htmlescape($label)
            . "</span>{$versionLine}</div></td>";
    }

    /** @return array{label:string, absolute:string} */
    private static function relativeTime(string $datetime): array
    {
        $timestamp = ServerClock::toTimestamp($datetime);
        if ($timestamp <= 0) {
            return ['label' => 'nunca', 'absolute' => '-'];
        }
        $seconds = max(0, time() - $timestamp);
        if ($seconds < 60) {
            $label = 'agora há pouco';
        } elseif ($seconds < 3600) {
            $label = 'há ' . intdiv($seconds, 60) . ' min';
        } elseif ($seconds < 86400) {
            $label = 'há ' . intdiv($seconds, 3600) . ' h';
        } else {
            $label = 'há ' . intdiv($seconds, 86400) . ' d';
        }
        return ['label' => $label, 'absolute' => Html::convDateTime($datetime)];
    }
}
