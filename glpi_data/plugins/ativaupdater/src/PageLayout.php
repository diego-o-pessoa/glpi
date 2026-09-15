<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaupdater;

final class PageLayout
{
    public static function header(string $active, bool $canManage = false, bool $live = false): string
    {
        global $CFG_GLPI;

        $front = $CFG_GLPI['root_doc'] . '/plugins/ativaupdater/front';
        $overviewClass = $active === 'overview' ? ' is-active' : '';
        $settingsClass = $active === 'settings' ? ' is-active' : '';

        $controls = '';
        if ($live) {
            $controls .= "<span id='ativaupdater-live-indicator' class='aw-live'>"
                . "<i class='fas fa-circle'></i><span>Ao vivo</span></span>";
        }
        if ($live && $canManage) {
            $controls .= "<button type='button' class='btn aw-primary-btn' data-ativaupdater-command='check_now'"
                . " data-confirm='Verificar atualizações agora em todos os computadores disponíveis?'>"
                . "<i class='fas fa-sync-alt'></i>Verificar agora</button>";
        }

        return self::styles()
            . "<main class='aw-shell'>"
            . "<header class='aw-hero'><div class='aw-brand'>"
            . "<span class='aw-brand-icon'><i class='fas fa-cloud-upload-alt'></i></span>"
            . "<span><h1>Ativa Updater</h1><p>Gerenciamento de GLPI Agent, Updater e Wallpaper</p></span>"
            . "</div><div class='aw-hero-actions'>{$controls}</div></header>"
            . "<nav class='aw-tabs' aria-label='Navegação do Ativa Updater'>"
            . "<a class='aw-tab{$overviewClass}' href='" . htmlescape($front . '/dashboard.php') . "'>"
            . "<i class='fas fa-home'></i>Visão Geral</a>"
            . "<a class='aw-tab{$settingsClass}' href='" . htmlescape($front . '/settings.php') . "'>"
            . "<i class='fas fa-cog'></i>Configurações</a>"
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
.aw-shell{--aw-ink:#142a5b;--aw-muted:#6d7da7;--aw-border:#dfe5f1;--aw-blue:#356fd1;--aw-yellow:#ffc94f;--aw-green:#35bf62;--aw-red:#dd3f4a;color:var(--aw-ink);padding:18px 22px 34px;background:linear-gradient(180deg,#f5f8fd 0,#f8faff 230px,#f4f7fb 100%);min-height:calc(100vh - 115px)}
.aw-hero{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:13px}.aw-brand{display:flex;align-items:center;gap:16px}.aw-brand-icon{width:58px;height:58px;border-radius:13px;background:#e1ecff;color:#3673dc;display:grid;place-items:center;font-size:28px}.aw-brand h1{font-size:27px;line-height:1.05;margin:0;font-weight:750;letter-spacing:-.4px}.aw-brand p{margin:4px 0 0;color:var(--aw-muted);font-size:14px}.aw-hero-actions{display:flex;align-items:center;gap:14px}.aw-live{height:39px;padding:0 15px;border:1px solid var(--aw-border);background:#fff;border-radius:8px;display:flex;align-items:center;gap:7px;color:var(--aw-muted);box-shadow:0 1px 3px #21345d0a}.aw-live i{font-size:10px;color:var(--aw-green)}.aw-primary-btn{height:41px;background:linear-gradient(180deg,#ffd665,#ffc442);border:1px solid #f1b928;color:#22335c;font-weight:650;padding:0 18px;box-shadow:0 3px 9px #d79d2029}.aw-primary-btn:hover{background:#ffbf36;color:#17264a}.aw-primary-btn i{margin-right:8px}
.aw-tabs{display:flex;align-items:flex-end;border-bottom:1px solid var(--aw-border);margin-bottom:16px}.aw-tab{display:flex;align-items:center;gap:9px;padding:11px 24px;color:#405582;text-decoration:none;border:1px solid transparent;border-radius:7px 7px 0 0;font-size:15px}.aw-tab:hover{color:#19366f;background:#fff}.aw-tab.is-active{background:linear-gradient(180deg,#ffd568,#ffc84e);border-color:#f2c14a;color:#1e315a;font-weight:650}.aw-tab i{font-size:14px}
.aw-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:15px}.aw-kpi,.aw-card{background:#fff;border:1px solid var(--aw-border);border-radius:8px;box-shadow:0 2px 7px #20335c0a}.aw-kpi{min-height:88px;display:flex;align-items:center;padding:14px}.aw-kpi-icon{width:56px;height:56px;border-radius:10px;display:grid;place-items:center;font-size:27px;margin-right:17px;background:#e8f0ff;color:#3568c5}.aw-kpi.is-green .aw-kpi-icon{background:#e2f8e9;color:#28b656}.aw-kpi.is-red .aw-kpi-icon{background:#ffeaec;color:#d93c47}.aw-kpi-label{font-size:13px;font-weight:650;margin-bottom:5px}.aw-kpi-value{font-size:25px;font-weight:750;line-height:1}.aw-kpi-note{font-size:12px;color:var(--aw-muted);margin-left:9px}.aw-current-badge{font-size:11px;background:#3dcc65;color:#fff;border-radius:5px;padding:4px 10px;margin-left:9px;vertical-align:4px}
.aw-grid{display:grid;grid-template-columns:1fr 1.08fr;gap:15px;margin-bottom:15px}.aw-card{overflow:hidden;margin-bottom:15px}.aw-card-header{min-height:49px;border-bottom:1px solid var(--aw-border);display:flex;align-items:center;justify-content:space-between;padding:0 17px}.aw-card-title{display:flex;align-items:center;gap:11px;font-size:18px;font-weight:700;margin:0}.aw-card-title i{color:#2772e0}.aw-card-body{padding:15px 17px}.aw-package-data{display:grid;grid-template-columns:135px 1fr;gap:7px 10px;margin:0}.aw-package-data dt{font-weight:700}.aw-package-data dd{margin:0;color:#334d80;min-width:0}.aw-hash{display:flex;align-items:center;justify-content:space-between;gap:10px;background:#f1f5fb;border:1px solid #dce5f3;border-radius:6px;padding:7px 10px;font-family:monospace;overflow-wrap:anywhere}.aw-form-grid{display:grid;grid-template-columns:135px 1fr;gap:12px 14px;align-items:start}.aw-form-grid label{font-weight:700;padding-top:9px}.aw-form-grid .form-text{color:var(--aw-muted)}.aw-submit-row{grid-column:2}.aw-submit-btn{background:#ffc84f;border-color:#f2bd3d;color:#21345d;font-weight:650}.aw-submit-btn:hover{background:#ffbd31;color:#17264a}
.aw-status-head{display:flex;align-items:center;justify-content:space-between;gap:18px;width:100%}.aw-progress-summary{display:flex;align-items:center;gap:14px;font-size:12px;font-weight:600}.aw-progress{height:18px;flex:1;background:#e5e9f1;border-radius:8px;overflow:hidden;min-width:260px}.aw-progress span{display:flex;align-items:center;justify-content:flex-end;height:100%;min-width:0;background:linear-gradient(90deg,#35c15f,#41ba45);color:#fff;padding-right:8px;font-size:11px;transition:width .45s ease}.aw-alert{border:1px solid transparent;border-radius:6px;padding:9px 13px;margin-bottom:12px}.aw-alert-danger{background:#fff4f4;border-color:#f0c6ca;color:#cf303b}.aw-alert-info{background:#eff6ff;border-color:#cfe0f5;color:#335b8e}.aw-empty{color:var(--aw-muted);margin:4px 0}.aw-computers-table{width:100%;border-collapse:collapse;color:#243c70;table-layout:fixed}.aw-computers-table th:nth-child(1){width:16%}.aw-computers-table th:nth-child(2){width:25%}.aw-computers-table th:nth-child(3){width:23%}.aw-computers-table th:nth-child(4),.aw-computers-table th:nth-child(5){width:10%}.aw-computers-table th:nth-child(6){width:16%}.aw-computers-table thead th{background:#f5f7fb;color:#3c5687;font-size:11px;font-weight:650;border-bottom:1px solid var(--aw-border);padding:10px;text-align:left}.aw-computers-table td{padding:11px 10px;vertical-align:middle;border-bottom:1px solid #edf0f5;font-size:12px}.aw-computer{display:flex;gap:11px;align-items:center}.aw-computer>i{font-size:18px;color:#3566ba}.aw-computer strong{display:block;color:#17336b;white-space:nowrap}.aw-computer small{display:block;color:var(--aw-muted);font-size:10px;margin-top:2px}.aw-versions{display:flex;gap:0}.aw-versions>span{min-width:77px;padding:0 10px;border-left:1px solid #e4e9f2}.aw-versions>span:first-child{border-left:0;padding-left:0}.aw-versions small{display:block;color:var(--aw-muted);font-size:10px}.aw-versions strong{display:block;font-size:12px;color:#193467;margin-top:2px}.aw-mini-note{display:block;font-size:9px;color:var(--aw-muted)}.aw-status-message{display:block;font-size:11px;color:var(--aw-muted);margin-top:5px;max-width:330px;overflow-wrap:anywhere}.aw-row-error td{background:#fff8f8;border-top-color:#f4c5c9;border-bottom-color:#f4c5c9}.aw-button{display:inline-flex;align-items:center;gap:6px;border:1px solid #d3dceb;border-radius:6px;background:#fff;color:#2f4a7a;padding:6px 9px;font-size:11px;white-space:nowrap}.aw-button:hover{border-color:#96acd0;background:#f5f8fd}.aw-button:disabled{opacity:.5}.aw-actions{display:flex;gap:7px;flex-wrap:wrap}.aw-details-row td{background:#fff6f6!important;padding:0 14px 13px!important}.aw-problem{border:1px solid #f1c3c7;border-radius:7px;padding:11px 14px;display:grid;grid-template-columns:1fr 1fr;gap:18px}.aw-problem strong{font-size:12px}.aw-problem p{font-size:11px;margin:5px 0;color:#304c80}.aw-problem code{display:block;margin-top:7px;background:#edf2f8;border:1px solid #dbe4ef;border-radius:5px;padding:5px 8px;color:#63769a;font-size:10px}.aw-problem ol{font-size:11px;margin:5px 0 8px;padding-left:22px}.aw-history summary{cursor:pointer;padding:13px 17px;font-weight:650}.aw-history .table{margin:0}.aw-settings-grid{display:grid;grid-template-columns:1.2fr .8fr;gap:15px}.aw-help{background:#edf5ff;border:1px solid #cfe0f8;color:#35527d;border-radius:7px;padding:11px 14px;margin-bottom:15px}
.aw-modal-layer{position:fixed;inset:0;z-index:11000;background:#142346b8;display:grid;place-items:center;padding:24px}.aw-modal-layer[hidden]{display:none}.aw-modal{width:min(980px,96vw);max-height:90vh;background:#fff;border-radius:11px;box-shadow:0 24px 70px #07132c73;display:flex;flex-direction:column;overflow:hidden}.aw-modal-header{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:16px 19px;border-bottom:1px solid var(--aw-border)}.aw-modal-heading{display:flex;align-items:center;gap:12px}.aw-modal-heading>i{width:38px;height:38px;border-radius:9px;background:#e8f0ff;color:#356fd1;display:grid;place-items:center;font-size:18px}.aw-modal-heading h2{font-size:18px;margin:0}.aw-modal-heading p{font-size:11px;color:var(--aw-muted);margin:2px 0 0}.aw-modal-close{border:0;background:transparent;color:#6d7da7;font-size:21px}.aw-modal-body{padding:18px;overflow:auto;min-height:230px}.aw-log-layout{display:grid;grid-template-columns:210px 1fr;gap:14px;min-height:420px}.aw-log-menu{display:flex;flex-direction:column;gap:6px}.aw-log-menu button{border:1px solid #dce4f0;background:#f7f9fc;color:#35507f;border-radius:6px;text-align:left;padding:9px 10px;font-size:11px}.aw-log-menu button.is-active{background:#e5efff;border-color:#aac5ef;color:#153a75;font-weight:700}.aw-log-content{margin:0;background:#111a2d;color:#dfe9ff;border-radius:7px;padding:14px;min-height:420px;max-height:58vh;overflow:auto;white-space:pre-wrap;font:11px/1.5 Consolas,monospace}.aw-operation{text-align:center;padding:12px 18px 22px}.aw-operation-icon{width:68px;height:68px;margin:0 auto 16px;border-radius:50%;display:grid;place-items:center;background:#e8f0ff;color:#356fd1;font-size:27px}.aw-operation-icon.is-running i{animation:aw-spin 1.4s linear infinite}.aw-operation-icon.is-done{background:#e2f8e9;color:#28b656}.aw-operation-icon.is-error{background:#ffeaec;color:#d93c47}.aw-operation h3{font-size:18px;margin:0 0 6px}.aw-operation p{color:var(--aw-muted);margin:0 0 20px}.aw-operation-progress{height:24px;border-radius:12px;background:#e5e9f1;overflow:hidden}.aw-operation-progress span{height:100%;display:flex;align-items:center;justify-content:flex-end;background:linear-gradient(90deg,#3776df,#35bd62);color:#fff;padding-right:10px;font-size:11px;font-weight:700;transition:width .7s ease}.aw-operation-note{font-size:11px;color:var(--aw-muted);margin-top:10px}.aw-modal-footer{display:flex;justify-content:flex-end;padding:12px 18px;border-top:1px solid var(--aw-border)}body.aw-modal-open{overflow:hidden}@keyframes aw-spin{to{transform:rotate(360deg)}}
@media(max-width:1100px){.aw-kpis{grid-template-columns:repeat(2,1fr)}.aw-grid,.aw-settings-grid{grid-template-columns:1fr}.aw-progress-summary{width:100%}.aw-status-head{align-items:flex-start;flex-direction:column}.aw-computers-table{min-width:1020px}}
@media(max-width:620px){.aw-shell{padding:14px 10px}.aw-hero{align-items:flex-start;flex-direction:column}.aw-hero-actions{width:100%;justify-content:space-between}.aw-kpis{grid-template-columns:1fr}.aw-tab{padding:10px 14px}.aw-form-grid{grid-template-columns:1fr}.aw-form-grid label{padding:0}.aw-submit-row{grid-column:1}.aw-log-layout{grid-template-columns:1fr}.aw-log-menu{flex-direction:row;overflow:auto}.aw-log-menu button{white-space:nowrap}.aw-modal-layer{padding:8px}}
</style>
HTML;
    }
}
