<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* @ativawallpaper/dashboard.html.twig */
class __TwigTemplate_9f149f75f4abd5b460576b1c2e63d209 extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 1
        yield "<div class=\"container-fluid ativa-wallpaper\">
  <div class=\"d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4\">
    <div>
      <h1 class=\"mb-1\"><i class=\"ti ti-photo me-2 text-primary\"></i>Ativa Wallpaper</h1>
      <div class=\"text-muted\">Gerenciamento centralizado dos wallpapers corporativos</div>
    </div>
    <nav class=\"btn-group\" aria-label=\"Navegacao Ativa Wallpaper\">
      <a class=\"btn btn-primary\" href=\"";
        // line 8
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["urls"] ?? null), "dashboard", [], "any", false, false, false, 8), "html", null, true);
        yield "\"><i class=\"ti ti-dashboard me-1\"></i>Dashboard</a>
      <a class=\"btn btn-outline-secondary\" href=\"";
        // line 9
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["urls"] ?? null), "history", [], "any", false, false, false, 9), "html", null, true);
        yield "\"><i class=\"ti ti-history me-1\"></i>Historico</a>
      ";
        // line 10
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["rights"] ?? null), "config", [], "any", false, false, false, 10)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield "<a class=\"btn btn-outline-secondary\" href=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["urls"] ?? null), "settings", [], "any", false, false, false, 10), "html", null, true);
            yield "\"><i class=\"ti ti-settings me-1\"></i>Configuracoes</a>";
        }
        // line 11
        yield "    </nav>
  </div>

  ";
        // line 14
        if ((($tmp =  !($context["enabled"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 15
            yield "    <div class=\"alert alert-warning d-flex align-items-center\" role=\"alert\">
      <i class=\"ti ti-player-pause me-2 fs-2\"></i>
      <div>A distribuicao esta desativada. Os clientes manterao o wallpaper atual.</div>
    </div>
  ";
        }
        // line 20
        yield "
  <div class=\"row g-3 mb-4\">
    <div class=\"col-xl-7\">
      <section class=\"card h-100 shadow-sm\">
        <div class=\"card-header d-flex align-items-center justify-content-between\">
          <h2 class=\"card-title mb-0\">Wallpaper atual</h2>
          <span class=\"badge ";
        // line 26
        yield (((($tmp = ($context["enabled"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("bg-success") : ("bg-secondary"));
        yield "\">";
        yield (((($tmp = ($context["enabled"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("Ativo") : ("Inativo"));
        yield "</span>
        </div>
        <div class=\"card-body\">
          ";
        // line 29
        if ((($tmp = ($context["current"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 30
            yield "            <div class=\"row g-4 align-items-center\">
              <div class=\"col-lg-7\">
                <img class=\"img-fluid rounded border ativa-current-preview\" src=\"";
            // line 32
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["urls"] ?? null), "image", [], "any", false, false, false, 32), "html", null, true);
            yield "?id=";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["current"] ?? null), "id", [], "any", false, false, false, 32), "html", null, true);
            yield "&thumbnail=1\" alt=\"Preview do wallpaper ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["current"] ?? null), "version", [], "any", false, false, false, 32), "html", null, true);
            yield "\">
              </div>
              <div class=\"col-lg-5\">
                <dl class=\"row mb-0 ativa-details\">
                  <dt class=\"col-5\">Versao</dt><dd class=\"col-7\"><code>";
            // line 36
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["current"] ?? null), "version", [], "any", false, false, false, 36), "html", null, true);
            yield "</code></dd>
                  <dt class=\"col-5\">Publicado</dt><dd class=\"col-7\">";
            // line 37
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getFormattedDatetime(CoreExtension::getAttribute($this->env, $this->source, ($context["current"] ?? null), "published_at", [], "any", false, false, false, 37)), "html", null, true);
            yield "</dd>
                  <dt class=\"col-5\">Por</dt><dd class=\"col-7\">";
            // line 38
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["current"] ?? null), "created_by_name", [], "any", false, false, false, 38), "html", null, true);
            yield "</dd>
                  <dt class=\"col-5\">Resolucao</dt><dd class=\"col-7\">";
            // line 39
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["current"] ?? null), "width", [], "any", false, false, false, 39), "html", null, true);
            yield "x";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["current"] ?? null), "height", [], "any", false, false, false, 39), "html", null, true);
            yield "</dd>
                  <dt class=\"col-5\">Tamanho</dt><dd class=\"col-7\">";
            // line 40
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Twig\Extension\CoreExtension']->formatNumber((CoreExtension::getAttribute($this->env, $this->source, ($context["current"] ?? null), "filesize", [], "any", false, false, false, 40) / 1048576), 2, ",", "."), "html", null, true);
            yield " MB</dd>
                  <dt class=\"col-5\">SHA-256</dt><dd class=\"col-7\"><code class=\"ativa-hash\" title=\"";
            // line 41
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["current"] ?? null), "sha256", [], "any", false, false, false, 41), "html", null, true);
            yield "\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::slice($this->env->getCharset(), CoreExtension::getAttribute($this->env, $this->source, ($context["current"] ?? null), "sha256", [], "any", false, false, false, 41), 0, 16), "html", null, true);
            yield "...</code></dd>
                  <dt class=\"col-5\">Modo</dt><dd class=\"col-7\">";
            // line 42
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::capitalize($this->env->getCharset(), CoreExtension::getAttribute($this->env, $this->source, ($context["current"] ?? null), "style", [], "any", false, false, false, 42)), "html", null, true);
            yield "</dd>
                  <dt class=\"col-5\">Bloqueio</dt><dd class=\"col-7\">";
            // line 43
            yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["current"] ?? null), "lock_change", [], "any", false, false, false, 43)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("Sim") : ("Nao"));
            yield "</dd>
                </dl>
              </div>
            </div>
          ";
        } else {
            // line 48
            yield "            <div class=\"empty py-5 text-center\">
              <i class=\"ti ti-photo-off fs-1 text-muted\"></i>
              <p class=\"mt-3 mb-0\">Nenhum wallpaper foi publicado.</p>
            </div>
          ";
        }
        // line 53
        yield "        </div>
        ";
        // line 54
        if ((CoreExtension::getAttribute($this->env, $this->source, ($context["rights"] ?? null), "publish", [], "any", false, false, false, 54) || CoreExtension::getAttribute($this->env, $this->source, ($context["rights"] ?? null), "config", [], "any", false, false, false, 54))) {
            // line 55
            yield "          <div class=\"card-footer d-flex flex-wrap gap-2\">
            ";
            // line 56
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["rights"] ?? null), "publish", [], "any", false, false, false, 56)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 57
                yield "              <button class=\"btn btn-primary\" type=\"button\" data-bs-toggle=\"modal\" data-bs-target=\"#ativa-upload-modal\">
                <i class=\"ti ti-upload me-1\"></i>Alterar wallpaper
              </button>
            ";
            }
            // line 61
            yield "            ";
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["rights"] ?? null), "config", [], "any", false, false, false, 61)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 62
                yield "              <form method=\"post\" action=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["urls"] ?? null), "action", [], "any", false, false, false, 62), "html", null, true);
                yield "\">
                <input type=\"hidden\" name=\"_glpi_csrf_token\" value=\"";
                // line 63
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Session::getNewCSRFToken(), "html", null, true);
                yield "\">
                <input type=\"hidden\" name=\"action\" value=\"toggle\">
                <input type=\"hidden\" name=\"enabled\" value=\"";
                // line 65
                yield (((($tmp = ($context["enabled"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("0") : ("1"));
                yield "\">
                <button class=\"btn btn-outline-";
                // line 66
                yield (((($tmp = ($context["enabled"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("warning") : ("success"));
                yield "\" type=\"submit\">
                  <i class=\"ti ti-power me-1\"></i>";
                // line 67
                yield (((($tmp = ($context["enabled"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("Desativar") : ("Ativar"));
                yield "
                </button>
              </form>
            ";
            }
            // line 71
            yield "          </div>
        ";
        }
        // line 73
        yield "      </section>
    </div>

    <div class=\"col-xl-5\">
      <section class=\"card h-100 shadow-sm\">
        <div class=\"card-header\"><h2 class=\"card-title mb-0\">Computadores gerenciados</h2></div>
        <div class=\"card-body\">
          <div class=\"display-5 fw-bold mb-3\">";
        // line 80
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["summary"] ?? null), "managed", [], "any", false, false, false, 80), "html", null, true);
        yield "</div>
          <div class=\"row g-2 mb-4\">
            <div class=\"col-6\"><div class=\"ativa-stat border-success\"><span>Atualizados</span><strong>";
        // line 82
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["summary"] ?? null), "updated", [], "any", false, false, false, 82), "html", null, true);
        yield "</strong></div></div>
            <div class=\"col-6\"><div class=\"ativa-stat border-warning\"><span>Pendentes</span><strong>";
        // line 83
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["summary"] ?? null), "pending", [], "any", false, false, false, 83), "html", null, true);
        yield "</strong></div></div>
            <div class=\"col-6\"><div class=\"ativa-stat border-secondary\"><span>Offline</span><strong>";
        // line 84
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["summary"] ?? null), "offline", [], "any", false, false, false, 84), "html", null, true);
        yield "</strong></div></div>
            <div class=\"col-6\"><div class=\"ativa-stat border-danger\"><span>Erro</span><strong>";
        // line 85
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["summary"] ?? null), "errors", [], "any", false, false, false, 85), "html", null, true);
        yield "</strong></div></div>
          </div>
          <div class=\"d-flex justify-content-between mb-1\"><span>Conformidade</span><strong>";
        // line 87
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Twig\Extension\CoreExtension']->formatNumber(CoreExtension::getAttribute($this->env, $this->source, ($context["summary"] ?? null), "percentage", [], "any", false, false, false, 87), 1, ",", "."), "html", null, true);
        yield "%</strong></div>
          <div class=\"progress\" role=\"progressbar\" aria-valuenow=\"";
        // line 88
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["summary"] ?? null), "percentage", [], "any", false, false, false, 88), "html", null, true);
        yield "\" aria-valuemin=\"0\" aria-valuemax=\"100\">
            <div class=\"progress-bar bg-success\" style=\"width: ";
        // line 89
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["summary"] ?? null), "percentage", [], "any", false, false, false, 89), "html", null, true);
        yield "%\"></div>
          </div>
        </div>
      </section>
    </div>
  </div>

  <section class=\"card shadow-sm\">
    <div class=\"card-header\"><h2 class=\"card-title mb-0\">Status dos computadores</h2></div>
    <div class=\"card-body border-bottom\">
      <form method=\"get\" action=\"";
        // line 99
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["urls"] ?? null), "dashboard", [], "any", false, false, false, 99), "html", null, true);
        yield "\" class=\"row g-2 align-items-end\">
        <div class=\"col-md-5\">
          <label class=\"form-label\" for=\"ativa-search\">Pesquisar</label>
          <input class=\"form-control\" id=\"ativa-search\" name=\"q\" value=\"";
        // line 102
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["filters"] ?? null), "q", [], "any", false, false, false, 102), "html", null, true);
        yield "\" placeholder=\"Hostname, usuario ou versao\">
        </div>
        <div class=\"col-md-3\">
          <label class=\"form-label\" for=\"ativa-status\">Status</label>
          <select class=\"form-select\" id=\"ativa-status\" name=\"status\">
            ";
        // line 107
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(["all" => "Todos", "updated" => "Atualizados", "pending" => "Pendentes", "offline" => "Offline", "error" => "Erro"]);
        foreach ($context['_seq'] as $context["value"] => $context["label"]) {
            // line 108
            yield "              <option value=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["value"], "html", null, true);
            yield "\" ";
            yield (((CoreExtension::getAttribute($this->env, $this->source, ($context["filters"] ?? null), "status", [], "any", false, false, false, 108) == $context["value"])) ? ("selected") : (""));
            yield ">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["label"], "html", null, true);
            yield "</option>
            ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['value'], $context['label'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 110
        yield "          </select>
        </div>
        <div class=\"col-md-2\">
          <label class=\"form-label\" for=\"ativa-sort\">Ordenar</label>
          <select class=\"form-select\" id=\"ativa-sort\" name=\"sort\">
            ";
        // line 115
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(["last_check" => "Ultimo contato", "hostname" => "Computador", "username" => "Usuario", "wallpaper_version" => "Wallpaper", "client_version" => "Cliente", "status" => "Status"]);
        foreach ($context['_seq'] as $context["value"] => $context["label"]) {
            // line 116
            yield "              <option value=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["value"], "html", null, true);
            yield "\" ";
            yield (((CoreExtension::getAttribute($this->env, $this->source, ($context["filters"] ?? null), "sort", [], "any", false, false, false, 116) == $context["value"])) ? ("selected") : (""));
            yield ">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["label"], "html", null, true);
            yield "</option>
            ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['value'], $context['label'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 118
        yield "          </select>
        </div>
        <div class=\"col-md-1\">
          <label class=\"form-label\" for=\"ativa-direction\">Direcao</label>
          <select class=\"form-select\" id=\"ativa-direction\" name=\"direction\">
            <option value=\"desc\" ";
        // line 123
        yield (((CoreExtension::getAttribute($this->env, $this->source, ($context["filters"] ?? null), "direction", [], "any", false, false, false, 123) == "desc")) ? ("selected") : (""));
        yield ">Desc</option>
            <option value=\"asc\" ";
        // line 124
        yield (((CoreExtension::getAttribute($this->env, $this->source, ($context["filters"] ?? null), "direction", [], "any", false, false, false, 124) == "asc")) ? ("selected") : (""));
        yield ">Asc</option>
          </select>
        </div>
        <div class=\"col-md-1\"><button class=\"btn btn-primary w-100\" type=\"submit\"><i class=\"ti ti-search\"></i><span class=\"visually-hidden\">Filtrar</span></button></div>
      </form>
    </div>
    <div class=\"table-responsive\">
      <table class=\"table table-hover table-vcenter mb-0\">
        <thead><tr>
          <th>Computador</th><th>Usuario</th><th>Wallpaper</th><th>Cliente</th><th>Ultimo contato</th><th>Ultima aplicacao</th><th>Status</th><th>Erro</th>";
        // line 133
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["rights"] ?? null), "clients", [], "any", false, false, false, 133)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield "<th class=\"text-end\">Acoes</th>";
        }
        // line 134
        yield "        </tr></thead>
        <tbody>
        ";
        // line 136
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, ($context["clients"] ?? null), "rows", [], "any", false, false, false, 136));
        $context['_iterated'] = false;
        foreach ($context['_seq'] as $context["_key"] => $context["client"]) {
            // line 137
            yield "          ";
            $context["badge"] = (($_v0 = ["updated" => "success", "pending" => "warning", "offline" => "secondary", "error" => "danger"]) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0[(($_v1 = CoreExtension::getAttribute($this->env, $this->source, $context["client"], "computed_status", [], "any", false, false, false, 137)) instanceof \Stringable ? (string) $_v1 : $_v1)] ?? null) : null);
            // line 138
            yield "          ";
            $context["label"] = (($_v2 = ["updated" => "Atualizado", "pending" => "Pendente", "offline" => "Offline", "error" => "Erro"]) && is_array($_v2) || $_v2 instanceof ArrayAccess ? ($_v2[(($_v3 = CoreExtension::getAttribute($this->env, $this->source, $context["client"], "computed_status", [], "any", false, false, false, 138)) instanceof \Stringable ? (string) $_v3 : $_v3)] ?? null) : null);
            // line 139
            yield "          <tr>
            <td><strong>";
            // line 140
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["client"], "hostname", [], "any", false, false, false, 140), "html", null, true);
            yield "</strong>";
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["client"], "computers_id", [], "any", false, false, false, 140)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                yield "<div><a class=\"small\" href=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ConfigExtension']->config("root_doc"), "html", null, true);
                yield "/front/computer.form.php?id=";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["client"], "computers_id", [], "any", false, false, false, 140), "html", null, true);
                yield "\">Abrir computador #";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["client"], "computers_id", [], "any", false, false, false, 140), "html", null, true);
                yield "</a></div>";
            } else {
                yield "<div class=\"small text-muted\">Aguardando reconciliacao</div>";
            }
            yield "</td>
            <td>";
            // line 141
            yield ((CoreExtension::getAttribute($this->env, $this->source, $context["client"], "username", [], "any", false, false, false, 141)) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["client"], "username", [], "any", false, false, false, 141), "html", null, true)) : ("-"));
            yield "</td>
            <td><code>";
            // line 142
            yield ((CoreExtension::getAttribute($this->env, $this->source, $context["client"], "wallpaper_version", [], "any", false, false, false, 142)) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["client"], "wallpaper_version", [], "any", false, false, false, 142), "html", null, true)) : ("-"));
            yield "</code></td>
            <td>";
            // line 143
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["client"], "client_version", [], "any", false, false, false, 143), "html", null, true);
            yield "</td>
            <td>";
            // line 144
            yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["client"], "last_check", [], "any", false, false, false, 144)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getFormattedDatetime(CoreExtension::getAttribute($this->env, $this->source, $context["client"], "last_check", [], "any", false, false, false, 144)), "html", null, true)) : ("-"));
            yield "</td>
            <td>";
            // line 145
            yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["client"], "last_apply", [], "any", false, false, false, 145)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getFormattedDatetime(CoreExtension::getAttribute($this->env, $this->source, $context["client"], "last_apply", [], "any", false, false, false, 145)), "html", null, true)) : ("-"));
            yield "</td>
            <td><span class=\"badge bg-";
            // line 146
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["badge"] ?? null), "html", null, true);
            yield "\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["label"] ?? null), "html", null, true);
            yield "</span>";
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["client"], "force_reapply", [], "any", false, false, false, 146)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                yield "<div class=\"small text-primary mt-1\">Reaplicacao solicitada</div>";
            }
            yield "</td>
            <td class=\"ativa-error\" title=\"";
            // line 147
            yield ((CoreExtension::getAttribute($this->env, $this->source, $context["client"], "last_error", [], "any", false, false, false, 147)) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["client"], "last_error", [], "any", false, false, false, 147), "html", null, true)) : (""));
            yield "\">";
            yield ((CoreExtension::getAttribute($this->env, $this->source, $context["client"], "last_error", [], "any", false, false, false, 147)) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["client"], "last_error", [], "any", false, false, false, 147), "html", null, true)) : ("-"));
            yield "</td>
            ";
            // line 148
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["rights"] ?? null), "clients", [], "any", false, false, false, 148)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 149
                yield "              <td class=\"text-end text-nowrap\">
                <form class=\"d-inline\" method=\"post\" action=\"";
                // line 150
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["urls"] ?? null), "action", [], "any", false, false, false, 150), "html", null, true);
                yield "\">
                  <input type=\"hidden\" name=\"_glpi_csrf_token\" value=\"";
                // line 151
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Session::getNewCSRFToken(), "html", null, true);
                yield "\"><input type=\"hidden\" name=\"action\" value=\"force\"><input type=\"hidden\" name=\"id\" value=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["client"], "id", [], "any", false, false, false, 151), "html", null, true);
                yield "\">
                  <button class=\"btn btn-sm btn-outline-primary\" title=\"Forcar nova sincronizacao\"><i class=\"ti ti-refresh\"></i></button>
                </form>
                <form class=\"d-inline\" method=\"post\" action=\"";
                // line 154
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["urls"] ?? null), "action", [], "any", false, false, false, 154), "html", null, true);
                yield "\" data-ativa-confirm=\"Revogar este cliente? Ele precisara ser registrado novamente.\">
                  <input type=\"hidden\" name=\"_glpi_csrf_token\" value=\"";
                // line 155
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Session::getNewCSRFToken(), "html", null, true);
                yield "\"><input type=\"hidden\" name=\"action\" value=\"revoke\"><input type=\"hidden\" name=\"id\" value=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["client"], "id", [], "any", false, false, false, 155), "html", null, true);
                yield "\">
                  <button class=\"btn btn-sm btn-outline-danger\" title=\"Revogar cliente\"><i class=\"ti ti-ban\"></i></button>
                </form>
              </td>
            ";
            }
            // line 160
            yield "          </tr>
        ";
            $context['_iterated'] = true;
        }
        // line 161
        if (!$context['_iterated']) {
            // line 162
            yield "          <tr><td colspan=\"9\" class=\"text-center text-muted py-5\">Nenhum cliente encontrado.</td></tr>
        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['client'], $context['_parent'], $context['_iterated']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 164
        yield "        </tbody>
      </table>
    </div>
    <div class=\"card-footer d-flex justify-content-between align-items-center\">
      <span class=\"text-muted\">";
        // line 168
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["clients"] ?? null), "total", [], "any", false, false, false, 168), "html", null, true);
        yield " registro(s)</span>
      <nav><ul class=\"pagination mb-0\">
        <li class=\"page-item ";
        // line 170
        yield (((CoreExtension::getAttribute($this->env, $this->source, ($context["clients"] ?? null), "page", [], "any", false, false, false, 170) <= 1)) ? ("disabled") : (""));
        yield "\"><a class=\"page-link\" href=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["urls"] ?? null), "dashboard", [], "any", false, false, false, 170), "html", null, true);
        yield "?page=";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((CoreExtension::getAttribute($this->env, $this->source, ($context["clients"] ?? null), "page", [], "any", false, false, false, 170) - 1), "html", null, true);
        yield "&q=";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::urlencode(CoreExtension::getAttribute($this->env, $this->source, ($context["filters"] ?? null), "q", [], "any", false, false, false, 170)), "html", null, true);
        yield "&status=";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["filters"] ?? null), "status", [], "any", false, false, false, 170), "html", null, true);
        yield "&sort=";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["filters"] ?? null), "sort", [], "any", false, false, false, 170), "html", null, true);
        yield "&direction=";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["filters"] ?? null), "direction", [], "any", false, false, false, 170), "html", null, true);
        yield "\">Anterior</a></li>
        <li class=\"page-item disabled\"><span class=\"page-link\">";
        // line 171
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["clients"] ?? null), "page", [], "any", false, false, false, 171), "html", null, true);
        yield " / ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["clients"] ?? null), "pages", [], "any", false, false, false, 171), "html", null, true);
        yield "</span></li>
        <li class=\"page-item ";
        // line 172
        yield (((CoreExtension::getAttribute($this->env, $this->source, ($context["clients"] ?? null), "page", [], "any", false, false, false, 172) >= CoreExtension::getAttribute($this->env, $this->source, ($context["clients"] ?? null), "pages", [], "any", false, false, false, 172))) ? ("disabled") : (""));
        yield "\"><a class=\"page-link\" href=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["urls"] ?? null), "dashboard", [], "any", false, false, false, 172), "html", null, true);
        yield "?page=";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((CoreExtension::getAttribute($this->env, $this->source, ($context["clients"] ?? null), "page", [], "any", false, false, false, 172) + 1), "html", null, true);
        yield "&q=";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::urlencode(CoreExtension::getAttribute($this->env, $this->source, ($context["filters"] ?? null), "q", [], "any", false, false, false, 172)), "html", null, true);
        yield "&status=";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["filters"] ?? null), "status", [], "any", false, false, false, 172), "html", null, true);
        yield "&sort=";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["filters"] ?? null), "sort", [], "any", false, false, false, 172), "html", null, true);
        yield "&direction=";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["filters"] ?? null), "direction", [], "any", false, false, false, 172), "html", null, true);
        yield "\">Proxima</a></li>
      </ul></nav>
    </div>
  </section>
</div>

";
        // line 178
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["rights"] ?? null), "publish", [], "any", false, false, false, 178)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 179
            yield "<div class=\"modal fade\" id=\"ativa-upload-modal\" tabindex=\"-1\" aria-labelledby=\"ativa-upload-title\" aria-hidden=\"true\">
  <div class=\"modal-dialog modal-lg modal-dialog-centered\"><div class=\"modal-content\">
    <form method=\"post\" action=\"";
            // line 181
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["urls"] ?? null), "action", [], "any", false, false, false, 181), "html", null, true);
            yield "\" enctype=\"multipart/form-data\" data-ativa-upload-form data-max-bytes=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($context["max_upload_mb"] ?? null) * 1048576), "html", null, true);
            yield "\">
      <input type=\"hidden\" name=\"_glpi_csrf_token\" value=\"";
            // line 182
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Session::getNewCSRFToken(), "html", null, true);
            yield "\"><input type=\"hidden\" name=\"action\" value=\"publish\">
      <div class=\"modal-header\"><h2 class=\"modal-title\" id=\"ativa-upload-title\">Publicar wallpaper</h2><button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Fechar\"></button></div>
      <div class=\"modal-body\">
        <div class=\"row g-4\">
          <div class=\"col-md-7\">
            <label class=\"form-label\" for=\"ativa-wallpaper-file\">Imagem JPG ou PNG (maximo ";
            // line 187
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["max_upload_mb"] ?? null), "html", null, true);
            yield " MB)</label>
            <input class=\"form-control\" id=\"ativa-wallpaper-file\" name=\"wallpaper\" type=\"file\" accept=\"image/jpeg,image/png,.jpg,.jpeg,.png\" required data-ativa-image-input>
            <div class=\"invalid-feedback\" data-ativa-upload-error></div>
            <div class=\"ativa-preview mt-3\"><img class=\"img-fluid rounded border d-none\" alt=\"Preview da imagem selecionada\" data-ativa-image-preview><div class=\"text-muted text-center py-5\" data-ativa-preview-empty>Selecione uma imagem para visualizar.</div></div>
          </div>
          <div class=\"col-md-5\">
            <label class=\"form-label\" for=\"ativa-style\">Modo de ajuste</label>
            <select class=\"form-select mb-3\" id=\"ativa-style\" name=\"style\">
              <option value=\"fill\">Preencher / Fill</option><option value=\"fit\">Ajustar / Fit</option><option value=\"stretch\">Esticar / Stretch</option><option value=\"center\">Centralizar / Center</option><option value=\"tile\">Lado a lado / Tile</option><option value=\"span\">Span</option>
            </select>
            <label class=\"form-check\"><input class=\"form-check-input\" type=\"checkbox\" name=\"lock_change\" value=\"1\"><span class=\"form-check-label\">Impedir usuario de alterar wallpaper</span></label>
            <p class=\"small text-muted mt-2\">Desativado por padrao. O cliente nao sobrescreve politicas preexistentes.</p>
          </div>
        </div>
      </div>
      <div class=\"modal-footer\"><button type=\"button\" class=\"btn btn-outline-secondary\" data-bs-dismiss=\"modal\">Cancelar</button><button type=\"submit\" class=\"btn btn-primary\"><i class=\"ti ti-send me-1\"></i>Publicar wallpaper</button></div>
    </form>
  </div></div>
</div>
";
        }
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "@ativawallpaper/dashboard.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  530 => 187,  522 => 182,  516 => 181,  512 => 179,  510 => 178,  489 => 172,  483 => 171,  467 => 170,  462 => 168,  456 => 164,  449 => 162,  447 => 161,  442 => 160,  432 => 155,  428 => 154,  420 => 151,  416 => 150,  413 => 149,  411 => 148,  405 => 147,  395 => 146,  391 => 145,  387 => 144,  383 => 143,  379 => 142,  375 => 141,  359 => 140,  356 => 139,  353 => 138,  350 => 137,  345 => 136,  341 => 134,  337 => 133,  325 => 124,  321 => 123,  314 => 118,  301 => 116,  297 => 115,  290 => 110,  277 => 108,  273 => 107,  265 => 102,  259 => 99,  246 => 89,  242 => 88,  238 => 87,  233 => 85,  229 => 84,  225 => 83,  221 => 82,  216 => 80,  207 => 73,  203 => 71,  196 => 67,  192 => 66,  188 => 65,  183 => 63,  178 => 62,  175 => 61,  169 => 57,  167 => 56,  164 => 55,  162 => 54,  159 => 53,  152 => 48,  144 => 43,  140 => 42,  134 => 41,  130 => 40,  124 => 39,  120 => 38,  116 => 37,  112 => 36,  101 => 32,  97 => 30,  95 => 29,  87 => 26,  79 => 20,  72 => 15,  70 => 14,  65 => 11,  59 => 10,  55 => 9,  51 => 8,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "@ativawallpaper/dashboard.html.twig", "/var/www/html/glpi/plugins/ativawallpaper/templates/dashboard.html.twig");
    }
}
