<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaguardian;

final class PageLayout
{
    public static function header(string $active, bool $live = false): string
    {
        global $CFG_GLPI;

        $front = $CFG_GLPI['root_doc'] . '/plugins/ativaguardian/front';
        $overviewClass = $active === 'overview' ? ' is-active' : '';

        $controls = '';
        if ($live) {
            $controls .= "<span id='ag-live-indicator' class='ag-live'>"
                . "<i class='fas fa-circle'></i><span>Ao vivo</span></span>";
        }

        return self::styles()
            . "<main class='ag-shell'>"
            . "<header class='ag-hero'><div class='ag-brand'>"
            . "<span class='ag-brand-icon'><i class='fas fa-shield-alt'></i></span>"
            . "<span><h1>Ativa Guardian</h1><p>Saúde dos componentes Ativa nas máquinas Windows</p></span>"
            . "</div><div class='ag-hero-actions'>{$controls}</div></header>"
            . "<nav class='ag-tabs' aria-label='Navegação do Ativa Guardian'>"
            . "<a class='ag-tab{$overviewClass}' href='" . htmlescape($front . '/dashboard.php') . "'>"
            . "<i class='fas fa-gauge-high'></i>Visão Geral</a>"
            // A aba "Computadores" foi removida: mostrava exatamente a mesma
            // tabela da Visão Geral. machines.php segue vivo como endpoint da
            // atualização ao vivo.
            . "<a class='ag-tab" . ($active === 'history' ? ' is-active' : '') . "' href='"
            . htmlescape($front . '/history.php') . "'>"
            . "<i class='fas fa-clock-rotate-left'></i>Histórico</a>"
            . "</nav>";
    }

    public static function footer(): string
    {
        return '</main>';
    }

    private static function styles(): string
    {
        return <<<'HTML'
<style>
.ag-shell{--ag-ink:#132a29;--ag-muted:#5f7c78;--ag-border:#dbe8e5;--ag-green:#1f9d70;--ag-red:#dd3f4a;--ag-amber:#e6a417;--ag-slate:#64748b;color:var(--ag-ink);padding:18px 22px 34px;background:linear-gradient(180deg,#f4faf8 0,#f8fdfb 230px,#f4f9f7 100%);min-height:calc(100vh - 115px)}
.ag-hero{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:13px}.ag-brand{display:flex;align-items:center;gap:16px}.ag-brand-icon{width:58px;height:58px;border-radius:13px;background:#dcf3ea;color:#1f9d70;display:grid;place-items:center;font-size:28px}.ag-brand h1{font-size:27px;line-height:1.05;margin:0;font-weight:750;letter-spacing:-.4px}.ag-brand p{margin:4px 0 0;color:var(--ag-muted);font-size:14px}.ag-hero-actions{display:flex;align-items:center;gap:14px}.ag-live{height:39px;padding:0 15px;border:1px solid var(--ag-border);background:#fff;border-radius:8px;display:flex;align-items:center;gap:7px;color:var(--ag-muted)}.ag-live i{font-size:10px;color:var(--ag-green)}
.ag-tabs{display:flex;align-items:flex-end;border-bottom:1px solid var(--ag-border);margin-bottom:16px}.ag-tab{display:flex;align-items:center;gap:9px;padding:11px 24px;color:#3c5a55;text-decoration:none;border:1px solid transparent;border-radius:7px 7px 0 0;font-size:15px}.ag-tab:hover{color:#12433a;background:#fff}.ag-tab.is-active{background:linear-gradient(180deg,#3fbf8d,#1f9d70);border-color:#1f9d70;color:#fff;font-weight:650}.ag-tab i{font-size:14px}
.ag-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:15px}.ag-kpi,.ag-card{background:#fff;border:1px solid var(--ag-border);border-radius:8px;box-shadow:0 2px 7px #1d40380a}.ag-kpi{min-height:88px;display:flex;align-items:center;padding:14px}.ag-kpi-icon{width:56px;height:56px;border-radius:10px;display:grid;place-items:center;font-size:27px;margin-right:17px;background:#e6f1fb;color:#3568c5}.ag-kpi.is-green .ag-kpi-icon{background:#e2f8ec;color:#1f9d70}.ag-kpi.is-red .ag-kpi-icon{background:#ffeaec;color:#d93c47}.ag-kpi.is-slate .ag-kpi-icon{background:#eef1f6;color:#64748b}.ag-kpi-label{font-size:13px;font-weight:650;margin-bottom:5px}.ag-kpi-value{font-size:25px;font-weight:750;line-height:1}.ag-kpi-note{font-size:12px;color:var(--ag-muted);margin-left:9px}
.ag-card{overflow:hidden;margin-bottom:15px}.ag-card-header{min-height:49px;border-bottom:1px solid var(--ag-border);display:flex;align-items:center;justify-content:space-between;padding:0 17px}.ag-card-title{display:flex;align-items:center;gap:11px;font-size:18px;font-weight:700;margin:0}.ag-card-title i{color:#1f9d70}.ag-card-body{padding:0}
.ag-table{width:100%;border-collapse:collapse;color:#1d3a36;font-size:12px}.ag-table thead th{background:#f2f8f6;color:#365b54;font-size:11px;font-weight:650;border-bottom:1px solid var(--ag-border);padding:10px 12px;text-align:left}.ag-table td{padding:11px 12px;vertical-align:middle;border-bottom:1px solid #eef3f1}.ag-table tbody tr:hover{background:#f7fbfa}.ag-machine{display:flex;gap:11px;align-items:center}.ag-machine>i{font-size:18px;color:#2f9a72}.ag-machine strong{display:block;color:#143f36;white-space:nowrap}.ag-machine small{display:block;color:var(--ag-muted);font-size:10px;margin-top:2px}
.ag-badge{display:inline-block;font-size:11px;font-weight:600;border-radius:5px;padding:3px 8px;color:#fff;white-space:nowrap}.ag-badge.bg-success{background:#1f9d70}.ag-badge.bg-danger{background:#dd3f4a}.ag-badge.bg-warning{background:#e6a417;color:#3a2c05}.ag-badge.bg-secondary{background:#7c8b96}.ag-card-head{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:18px 20px 14px;flex-wrap:wrap}.ag-card-head-text h2{display:flex;align-items:center;gap:10px;font-size:20px;font-weight:700;margin:0;color:#143f36}.ag-card-head-text h2 i{color:#1f9d70}.ag-card-head-text p{margin:4px 0 0 30px;color:var(--ag-muted);font-size:13px}
.ag-search{position:relative;min-width:270px}.ag-search i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#9bb3ac;font-size:12px}.ag-search input{width:100%;height:38px;border:1px solid var(--ag-border);border-radius:8px;padding:0 12px 0 34px;font-size:13px;color:var(--ag-ink);background:#fff}.ag-search input:focus{outline:0;border-color:#1f9d70;box-shadow:0 0 0 3px #1f9d7022}
.ag-sort{display:inline-flex;align-items:center;gap:6px;border:0;background:transparent;padding:0;font:inherit;color:inherit;cursor:pointer}.ag-sort i{font-size:9px;opacity:.45}.ag-sort:hover i{opacity:.9}.ag-col-actions{width:70px;text-align:center}
.ag-menu{position:relative;display:inline-block}.ag-kebab{border:1px solid var(--ag-border);background:#fff;color:#4a6b63;border-radius:7px;width:30px;height:30px;display:grid;place-items:center;cursor:pointer}.ag-kebab:hover:not(:disabled){border-color:#1f9d70;background:#f2fbf7;color:#1f9d70}.ag-kebab:disabled{opacity:.35;cursor:default}
.ag-menu-list{position:absolute;right:0;top:34px;z-index:30;background:#fff;border:1px solid var(--ag-border);border-radius:9px;box-shadow:0 10px 28px #1d403822;min-width:215px;padding:6px;display:flex;flex-direction:column;gap:2px}.ag-menu-list[hidden]{display:none}.ag-menu-item{display:flex;align-items:center;gap:9px;border:0;background:transparent;border-radius:6px;padding:8px 10px;font-size:12px;color:#26463f;text-align:left;cursor:pointer;white-space:nowrap}.ag-menu-item:hover{background:#eef8f4;color:#12433a}.ag-menu-item i{width:13px;font-size:11px;color:#1f9d70}
.ag-modal-layer{position:fixed;inset:0;z-index:12000;background:#0d2a2299;display:grid;place-items:center;padding:24px}.ag-modal-layer[hidden]{display:none}.ag-modal{width:min(560px,96vw);background:#fff;border-radius:12px;box-shadow:0 24px 70px #07231c73;overflow:hidden}.ag-modal-head{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:16px 20px;border-bottom:1px solid var(--ag-border)}.ag-modal-head-text h3{margin:0;font-size:17px;color:#143f36}.ag-modal-head-text p{margin:2px 0 0;font-size:12px;color:var(--ag-muted)}.ag-modal-close{border:0;background:transparent;color:#6d8a83;font-size:19px;cursor:pointer}.ag-modal-close[hidden]{display:none}
.ag-op{text-align:center;padding:26px 24px 24px}.ag-op-icon{width:66px;height:66px;margin:0 auto 15px;border-radius:50%;display:grid;place-items:center;background:#e3f4ed;color:#1f9d70;font-size:26px}.ag-op-icon.is-run i{animation:ag-spin 1.3s linear infinite}.ag-op-icon.is-done{background:#e2f8ec;color:#1f9d70}.ag-op-icon.is-err{background:#ffeaec;color:#d93c47}.ag-op h4{margin:0 0 6px;font-size:17px;color:#143f36}.ag-op p{margin:0 0 18px;color:var(--ag-muted);font-size:13px}.ag-op-bar{height:22px;border-radius:11px;background:#e7efec;overflow:hidden}.ag-op-bar span{display:flex;align-items:center;justify-content:flex-end;height:100%;background:linear-gradient(90deg,#3fbf8d,#1f9d70);color:#fff;padding-right:9px;font-size:11px;font-weight:700;transition:width .6s ease}.ag-op-note{margin-top:11px;font-size:11px;color:var(--ag-muted)}.ag-modal-foot{display:flex;justify-content:flex-end;padding:12px 20px;border-top:1px solid var(--ag-border)}.ag-modal-foot[hidden]{display:none}body.ag-modal-open{overflow:hidden}@keyframes ag-spin{to{transform:rotate(360deg)}}
.ag-table-foot{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:13px 20px;border-top:1px solid #eef3f1;color:var(--ag-muted);font-size:12px;flex-wrap:wrap}.ag-updated{display:inline-flex;align-items:center;gap:7px}.ag-updated i{font-size:11px}
.ag-comp{display:flex;flex-direction:column;gap:2px;min-width:0}.ag-comp-ver{color:var(--ag-muted);font-size:10px}.ag-empty{color:var(--ag-muted);padding:22px 17px;text-align:center}.ag-last{color:#365b54}.ag-last small{display:block;color:var(--ag-muted);font-size:10px}
@media(max-width:1100px){.ag-kpis{grid-template-columns:repeat(2,1fr)}.ag-table{min-width:900px}.ag-card-body{overflow:auto}}
@media(max-width:620px){.ag-shell{padding:14px 10px}.ag-hero{align-items:flex-start;flex-direction:column}.ag-kpis{grid-template-columns:1fr}.ag-tab{padding:10px 14px}}
</style>
HTML;
    }
}
