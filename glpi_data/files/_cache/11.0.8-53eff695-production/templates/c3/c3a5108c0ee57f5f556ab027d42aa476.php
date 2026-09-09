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

/* @ativawallpaper/settings.html.twig */
class __TwigTemplate_13b896868efdc947fc91323c1848afa8 extends Template
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
  <div class=\"d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4\"><div><h1 class=\"mb-1\"><i class=\"ti ti-settings me-2 text-primary\"></i>Configuracao inicial</h1><div class=\"text-muted\">Diagnostico e parametros de producao</div></div><nav class=\"btn-group\"><a class=\"btn btn-outline-secondary\" href=\"";
        // line 2
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["urls"] ?? null), "dashboard", [], "any", false, false, false, 2), "html", null, true);
        yield "\">Dashboard</a><a class=\"btn btn-outline-secondary\" href=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["urls"] ?? null), "history", [], "any", false, false, false, 2), "html", null, true);
        yield "\">Historico</a><a class=\"btn btn-primary\" href=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["urls"] ?? null), "settings", [], "any", false, false, false, 2), "html", null, true);
        yield "\">Configuracoes</a></nav></div>

  <div class=\"row g-3 mb-4\">
    <div class=\"col-md-6 col-xl-4\"><div class=\"card h-100\"><div class=\"card-body\"><div class=\"ativa-step\"><span>1</span><div><h2 class=\"h4\">GLPI Inventory</h2><span class=\"badge ";
        // line 5
        yield (((($tmp = ($context["inventory_active"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("bg-success") : ("bg-danger"));
        yield "\">";
        yield (((($tmp = ($context["inventory_active"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("Instalado e ativo") : ("Nao ativo"));
        yield "</span>";
        if ((($tmp = ($context["inventory_version"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield "<div class=\"small text-muted mt-2\">Versao ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["inventory_version"] ?? null), "html", null, true);
            yield "</div>";
        }
        yield "</div></div></div></div></div>
    <div class=\"col-md-6 col-xl-4\"><div class=\"card h-100\"><div class=\"card-body\"><div class=\"ativa-step\"><span>2</span><div><h2 class=\"h4\">Endpoint Inventory</h2><code class=\"text-wrap\">";
        // line 6
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["inventory_endpoint"] ?? null), "html", null, true);
        yield "</code><div class=\"small text-muted mt-2\">Use sempre o hostname; nunca 10.117.41.6.</div></div></div></div></div></div>
    <div class=\"col-md-6 col-xl-4\"><div class=\"card h-100\"><div class=\"card-body\"><div class=\"ativa-step\"><span>3</span><div><h2 class=\"h4\">taskscheduler</h2>";
        // line 7
        if ((($tmp = ($context["cron"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield "<span class=\"badge ";
            yield (((($context["cron_recent"] ?? null) && (CoreExtension::getAttribute($this->env, $this->source, ($context["cron"] ?? null), "mode", [], "any", false, false, false, 7) == 2))) ? ("bg-success") : ("bg-warning"));
            yield "\">";
            yield (((($context["cron_recent"] ?? null) && (CoreExtension::getAttribute($this->env, $this->source, ($context["cron"] ?? null), "mode", [], "any", false, false, false, 7) == 2))) ? ("Saudavel / CLI") : ("Verificar"));
            yield "</span><div class=\"small mt-2\">Ultima execucao: ";
            yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["cron"] ?? null), "lastrun", [], "any", false, false, false, 7)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getFormattedDatetime(CoreExtension::getAttribute($this->env, $this->source, ($context["cron"] ?? null), "lastrun", [], "any", false, false, false, 7)), "html", null, true)) : ("nunca"));
            yield "<br>Modo: ";
            yield (((CoreExtension::getAttribute($this->env, $this->source, ($context["cron"] ?? null), "mode", [], "any", false, false, false, 7) == 2)) ? ("CLI") : ("GLPI"));
            yield " | Estado: ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["cron"] ?? null), "state", [], "any", false, false, false, 7), "html", null, true);
            yield "</div>";
        } else {
            yield "<span class=\"badge bg-danger\">Nao localizado</span>";
        }
        yield "</div></div></div></div></div>
    <div class=\"col-md-6 col-xl-4\"><div class=\"card h-100\"><div class=\"card-body\"><div class=\"ativa-step\"><span>4</span><div><h2 class=\"h4\">Wallpaper Client</h2><strong>";
        // line 8
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["settings"] ?? null), "latest_client_version", [], "any", false, false, false, 8), "html", null, true);
        yield "</strong><div class=\"small text-muted\">Windows 10/11 x64; Windows Server excluido.</div></div></div></div></div></div>
    <div class=\"col-md-6 col-xl-4\"><div class=\"card h-100\"><div class=\"card-body\"><div class=\"ativa-step\"><span>5</span><div><h2 class=\"h4\">Bootstrap Deploy</h2><span class=\"badge ";
        // line 9
        yield ((((CoreExtension::getAttribute($this->env, $this->source, ($context["settings"] ?? null), "bootstrap_configured", [], "any", false, false, false, 9) == "1") && ($context["secret_ready"] ?? null))) ? ("bg-success") : ("bg-warning"));
        yield "\">";
        yield ((((CoreExtension::getAttribute($this->env, $this->source, ($context["settings"] ?? null), "bootstrap_configured", [], "any", false, false, false, 9) == "1") && ($context["secret_ready"] ?? null))) ? ("Confirmado") : ("Pendente"));
        yield "</span><div class=\"small text-muted mt-2\">Segredo: ";
        yield (((($tmp = ($context["secret_ready"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("configurado") : ("gerar abaixo"));
        yield ".<br>Primeiro restrinja ao piloto DESKTOP-R1C8ICN-2026-08-17-14-54-59.</div></div></div></div></div></div>
    <div class=\"col-md-6 col-xl-4\"><div class=\"card h-100\"><div class=\"card-body\"><div class=\"ativa-step\"><span>6</span><div><h2 class=\"h4\">Wallpaper inicial</h2><span class=\"badge ";
        // line 10
        yield (((($tmp = ($context["current_wallpaper"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("bg-success") : ("bg-warning"));
        yield "\">";
        yield (((($tmp = ($context["current_wallpaper"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["current_wallpaper"] ?? null), "version", [], "any", false, false, false, 10), "html", null, true)) : ("Pendente"));
        yield "</span><div class=\"mt-2\"><a href=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["urls"] ?? null), "dashboard", [], "any", false, false, false, 10), "html", null, true);
        yield "\">";
        yield (((($tmp = ($context["current_wallpaper"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("Ver wallpaper atual") : ("Enviar imagem inicial"));
        yield "</a></div></div></div></div></div></div>
  </div>

  ";
        // line 13
        if ((($tmp = ($context["can_configure"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 14
            yield "  <div class=\"row g-3\">
    <div class=\"col-xl-8\"><section class=\"card shadow-sm\"><div class=\"card-header\"><h2 class=\"card-title mb-0\">Parametros</h2></div><div class=\"card-body\"><form method=\"post\" action=\"";
            // line 15
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["urls"] ?? null), "action", [], "any", false, false, false, 15), "html", null, true);
            yield "\" class=\"row g-3\">
      <input type=\"hidden\" name=\"_glpi_csrf_token\" value=\"";
            // line 16
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Session::getNewCSRFToken(), "html", null, true);
            yield "\"><input type=\"hidden\" name=\"action\" value=\"settings\">
      <div class=\"col-12\"><label class=\"form-label\">URL base da API</label><input class=\"form-control\" type=\"url\" name=\"server_url\" required value=\"";
            // line 17
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["settings"] ?? null), "server_url", [], "any", false, false, false, 17), "html", null, true);
            yield "\"><div class=\"form-hint\">HTTPS e hostname sao obrigatorios.</div></div>
      <div class=\"col-md-4\"><label class=\"form-label\">Polling (segundos)</label><input class=\"form-control\" type=\"number\" min=\"60\" max=\"86400\" name=\"poll_interval_seconds\" value=\"";
            // line 18
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["settings"] ?? null), "poll_interval_seconds", [], "any", false, false, false, 18), "html", null, true);
            yield "\"></div>
      <div class=\"col-md-4\"><label class=\"form-label\">Jitter maximo</label><input class=\"form-control\" type=\"number\" min=\"0\" max=\"3600\" name=\"poll_jitter_seconds\" value=\"";
            // line 19
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["settings"] ?? null), "poll_jitter_seconds", [], "any", false, false, false, 19), "html", null, true);
            yield "\"></div>
      <div class=\"col-md-4\"><label class=\"form-label\">Offline apos</label><input class=\"form-control\" type=\"number\" min=\"120\" max=\"2592000\" name=\"offline_after_seconds\" value=\"";
            // line 20
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["settings"] ?? null), "offline_after_seconds", [], "any", false, false, false, 20), "html", null, true);
            yield "\"></div>
      <div class=\"col-md-4\"><label class=\"form-label\">Upload maximo (MB)</label><input class=\"form-control\" type=\"number\" min=\"1\" max=\"100\" name=\"max_upload_mb\" value=\"";
            // line 21
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["settings"] ?? null), "max_upload_mb", [], "any", false, false, false, 21), "html", null, true);
            yield "\"></div>
      <div class=\"col-md-4\"><label class=\"form-label\">Cliente minimo</label><input class=\"form-control\" name=\"minimum_client_version\" value=\"";
            // line 22
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["settings"] ?? null), "minimum_client_version", [], "any", false, false, false, 22), "html", null, true);
            yield "\"></div>
      <div class=\"col-md-4\"><label class=\"form-label\">Cliente mais recente</label><input class=\"form-control\" name=\"latest_client_version\" value=\"";
            // line 23
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["settings"] ?? null), "latest_client_version", [], "any", false, false, false, 23), "html", null, true);
            yield "\"></div>
      <div class=\"col-12\"><label class=\"form-check\"><input class=\"form-check-input\" type=\"checkbox\" name=\"bootstrap_configured\" value=\"1\" ";
            // line 24
            yield (((CoreExtension::getAttribute($this->env, $this->source, ($context["settings"] ?? null), "bootstrap_configured", [], "any", false, false, false, 24) == "1")) ? ("checked") : (""));
            yield "><span class=\"form-check-label\">Confirmo que o Deploy piloto foi configurado</span></label><label class=\"form-check\"><input class=\"form-check-input\" type=\"checkbox\" name=\"setup_completed\" value=\"1\" ";
            yield (((CoreExtension::getAttribute($this->env, $this->source, ($context["settings"] ?? null), "setup_completed", [], "any", false, false, false, 24) == "1")) ? ("checked") : (""));
            yield "><span class=\"form-check-label\">Configuracao inicial concluida</span></label><label class=\"form-check\"><input class=\"form-check-input\" type=\"checkbox\" name=\"preserve_data_on_uninstall\" value=\"1\" ";
            yield (((CoreExtension::getAttribute($this->env, $this->source, ($context["settings"] ?? null), "preserve_data_on_uninstall", [], "any", false, false, false, 24) == "1")) ? ("checked") : (""));
            yield "><span class=\"form-check-label\">Preservar tabelas e historico ao desinstalar o plugin</span></label></div>
      <div class=\"col-12\"><button class=\"btn btn-primary\" type=\"submit\"><i class=\"ti ti-device-floppy me-1\"></i>Salvar configuracoes</button></div>
    </form></div></section></div>
    <div class=\"col-xl-4\"><section class=\"card shadow-sm border-warning mb-3\"><div class=\"card-header\"><h2 class=\"card-title mb-0\">Configuracao de bootstrap</h2></div><div class=\"card-body\"><p>Gera um novo segredo e baixa o JSON usado somente pelo pacote inicial. O valor anterior deixa de registrar novos clientes; clientes ja registrados continuam funcionando.</p><div class=\"alert alert-warning small\"><i class=\"ti ti-alert-triangle me-1\"></i>O segredo e exibido uma unica vez no arquivo. Proteja o pacote e rotacione depois do rollout.</div><form method=\"post\" action=\"";
            // line 27
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["urls"] ?? null), "action", [], "any", false, false, false, 27), "html", null, true);
            yield "\" data-ativa-confirm=\"Gerar um novo segredo? Pacotes de bootstrap antigos deixarao de registrar clientes.\"><input type=\"hidden\" name=\"_glpi_csrf_token\" value=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Session::getNewCSRFToken(), "html", null, true);
            yield "\"><input type=\"hidden\" name=\"action\" value=\"bootstrap\"><button class=\"btn btn-warning w-100\" type=\"submit\"><i class=\"ti ti-key me-1\"></i>Rotacionar e baixar JSON</button></form>";
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["settings"] ?? null), "registration_secret_rotated_at", [], "any", false, false, false, 27)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                yield "<div class=\"small text-muted mt-2\">Ultima rotacao: ";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getFormattedDatetime(CoreExtension::getAttribute($this->env, $this->source, ($context["settings"] ?? null), "registration_secret_rotated_at", [], "any", false, false, false, 27)), "html", null, true);
                yield "</div>";
            }
            yield "</div></section><section class=\"card shadow-sm\"><div class=\"card-header\"><h2 class=\"card-title mb-0\">Versoes do cliente</h2></div><div class=\"card-body\"><div class=\"mb-2\">Mais recente: <strong>";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["settings"] ?? null), "latest_client_version", [], "any", false, false, false, 27), "html", null, true);
            yield "</strong></div><ul class=\"list-group list-group-flush\">";
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["client_versions"] ?? null));
            $context['_iterated'] = false;
            foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
                yield "<li class=\"list-group-item d-flex justify-content-between px-0\"><code>";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["item"], "version", [], "any", false, false, false, 27), "html", null, true);
                yield "</code><span class=\"badge bg-secondary\">";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["item"], "total", [], "any", false, false, false, 27), "html", null, true);
                yield "</span></li>";
                $context['_iterated'] = true;
            }
            if (!$context['_iterated']) {
                yield "<li class=\"list-group-item text-muted px-0\">Nenhum cliente registrado.</li>";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['item'], $context['_parent'], $context['_iterated']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            yield "</ul></div></section></div>
  </div>
  ";
        }
        // line 30
        yield "</div>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "@ativawallpaper/settings.html.twig";
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
        return array (  198 => 30,  164 => 27,  154 => 24,  150 => 23,  146 => 22,  142 => 21,  138 => 20,  134 => 19,  130 => 18,  126 => 17,  122 => 16,  118 => 15,  115 => 14,  113 => 13,  101 => 10,  93 => 9,  89 => 8,  71 => 7,  67 => 6,  55 => 5,  45 => 2,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "@ativawallpaper/settings.html.twig", "/var/www/html/glpi/plugins/ativawallpaper/templates/settings.html.twig");
    }
}
