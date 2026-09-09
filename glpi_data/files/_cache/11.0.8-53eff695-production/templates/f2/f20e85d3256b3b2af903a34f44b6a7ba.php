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

/* @ativawallpaper/history.html.twig */
class __TwigTemplate_0bd89d4693b4cfcee466bc11dcbe4fa7 extends Template
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
    <div><h1 class=\"mb-1\"><i class=\"ti ti-history me-2 text-primary\"></i>Historico</h1><div class=\"text-muted\">Versoes preservadas e trilha administrativa</div></div>
    <nav class=\"btn-group\"><a class=\"btn btn-outline-secondary\" href=\"";
        // line 4
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["urls"] ?? null), "dashboard", [], "any", false, false, false, 4), "html", null, true);
        yield "\">Dashboard</a><a class=\"btn btn-primary\" href=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["urls"] ?? null), "history", [], "any", false, false, false, 4), "html", null, true);
        yield "\">Historico</a>";
        if ((($tmp = ($context["can_config"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield "<a class=\"btn btn-outline-secondary\" href=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["urls"] ?? null), "settings", [], "any", false, false, false, 4), "html", null, true);
            yield "\">Configuracoes</a>";
        }
        yield "</nav>
  </div>

  <div class=\"row g-3 mb-4\">
    ";
        // line 8
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["wallpapers"] ?? null));
        $context['_iterated'] = false;
        foreach ($context['_seq'] as $context["_key"] => $context["wallpaper"]) {
            // line 9
            yield "      <div class=\"col-md-6 col-xl-4\"><article class=\"card h-100 shadow-sm ";
            yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["wallpaper"], "is_current", [], "any", false, false, false, 9)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("border-success") : (""));
            yield "\">
        <img class=\"card-img-top ativa-history-preview\" src=\"";
            // line 10
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["urls"] ?? null), "image", [], "any", false, false, false, 10), "html", null, true);
            yield "?id=";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["wallpaper"], "id", [], "any", false, false, false, 10), "html", null, true);
            yield "&thumbnail=1\" alt=\"Preview ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["wallpaper"], "version", [], "any", false, false, false, 10), "html", null, true);
            yield "\">
        <div class=\"card-body\">
          <div class=\"d-flex justify-content-between align-items-center mb-2\"><h2 class=\"h4 mb-0\"><code>";
            // line 12
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["wallpaper"], "version", [], "any", false, false, false, 12), "html", null, true);
            yield "</code></h2><span class=\"badge ";
            yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["wallpaper"], "is_current", [], "any", false, false, false, 12)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("bg-success") : ("bg-secondary"));
            yield "\">";
            yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["wallpaper"], "is_current", [], "any", false, false, false, 12)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("Atual") : ("Anterior"));
            yield "</span></div>
          <dl class=\"row small mb-0\"><dt class=\"col-4\">Publicado</dt><dd class=\"col-8\">";
            // line 13
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getFormattedDatetime(CoreExtension::getAttribute($this->env, $this->source, $context["wallpaper"], "published_at", [], "any", false, false, false, 13)), "html", null, true);
            yield "</dd><dt class=\"col-4\">Usuario</dt><dd class=\"col-8\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["wallpaper"], "created_by_name", [], "any", false, false, false, 13), "html", null, true);
            yield "</dd><dt class=\"col-4\">Resolucao</dt><dd class=\"col-8\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["wallpaper"], "width", [], "any", false, false, false, 13), "html", null, true);
            yield "x";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["wallpaper"], "height", [], "any", false, false, false, 13), "html", null, true);
            yield "</dd><dt class=\"col-4\">Estilo</dt><dd class=\"col-8\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["wallpaper"], "style", [], "any", false, false, false, 13), "html", null, true);
            yield "</dd><dt class=\"col-4\">SHA-256</dt><dd class=\"col-8\"><code title=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["wallpaper"], "sha256", [], "any", false, false, false, 13), "html", null, true);
            yield "\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::slice($this->env->getCharset(), CoreExtension::getAttribute($this->env, $this->source, $context["wallpaper"], "sha256", [], "any", false, false, false, 13), 0, 14), "html", null, true);
            yield "...</code></dd></dl>
        </div>
        ";
            // line 15
            if ((($context["can_publish"] ?? null) &&  !CoreExtension::getAttribute($this->env, $this->source, $context["wallpaper"], "is_current", [], "any", false, false, false, 15))) {
                yield "<div class=\"card-footer\"><form method=\"post\" action=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["urls"] ?? null), "action", [], "any", false, false, false, 15), "html", null, true);
                yield "\" data-ativa-confirm=\"Tornar esta versao atual? Os clientes detectarao uma nova revisao.\"><input type=\"hidden\" name=\"_glpi_csrf_token\" value=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Session::getNewCSRFToken(), "html", null, true);
                yield "\"><input type=\"hidden\" name=\"action\" value=\"rollback\"><input type=\"hidden\" name=\"id\" value=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["wallpaper"], "id", [], "any", false, false, false, 15), "html", null, true);
                yield "\"><button class=\"btn btn-outline-primary w-100\" type=\"submit\"><i class=\"ti ti-restore me-1\"></i>Tornar esta versao atual</button></form></div>";
            }
            // line 16
            yield "      </article></div>
    ";
            $context['_iterated'] = true;
        }
        // line 17
        if (!$context['_iterated']) {
            // line 18
            yield "      <div class=\"col-12\"><div class=\"card\"><div class=\"card-body text-center text-muted py-5\">Nenhuma versao publicada.</div></div></div>
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['wallpaper'], $context['_parent'], $context['_iterated']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 20
        yield "  </div>
  <nav class=\"mb-4\"><ul class=\"pagination justify-content-center\"><li class=\"page-item ";
        // line 21
        yield (((($context["page"] ?? null) <= 1)) ? ("disabled") : (""));
        yield "\"><a class=\"page-link\" href=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["urls"] ?? null), "history", [], "any", false, false, false, 21), "html", null, true);
        yield "?page=";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($context["page"] ?? null) - 1), "html", null, true);
        yield "\">Anterior</a></li><li class=\"page-item disabled\"><span class=\"page-link\">";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["page"] ?? null), "html", null, true);
        yield " / ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["pages"] ?? null), "html", null, true);
        yield "</span></li><li class=\"page-item ";
        yield (((($context["page"] ?? null) >= ($context["pages"] ?? null))) ? ("disabled") : (""));
        yield "\"><a class=\"page-link\" href=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["urls"] ?? null), "history", [], "any", false, false, false, 21), "html", null, true);
        yield "?page=";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($context["page"] ?? null) + 1), "html", null, true);
        yield "\">Proxima</a></li></ul></nav>

  <section class=\"card shadow-sm\"><div class=\"card-header\"><h2 class=\"card-title mb-0\">Auditoria administrativa</h2></div><div class=\"table-responsive\"><table class=\"table table-striped table-vcenter mb-0\"><thead><tr><th>Data</th><th>Usuario</th><th>Acao</th><th>Alvo</th><th>IP</th><th>Antes</th><th>Depois</th></tr></thead><tbody>
    ";
        // line 24
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["audits"] ?? null));
        $context['_iterated'] = false;
        foreach ($context['_seq'] as $context["_key"] => $context["audit"]) {
            yield "<tr><td>";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getFormattedDatetime(CoreExtension::getAttribute($this->env, $this->source, $context["audit"], "created_at", [], "any", false, false, false, 24)), "html", null, true);
            yield "</td><td>";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["audit"], "user_name", [], "any", false, false, false, 24), "html", null, true);
            yield "</td><td><code>";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["audit"], "action", [], "any", false, false, false, 24), "html", null, true);
            yield "</code></td><td>";
            yield ((CoreExtension::getAttribute($this->env, $this->source, $context["audit"], "target_type", [], "any", false, false, false, 24)) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["audit"], "target_type", [], "any", false, false, false, 24), "html", null, true)) : ("-"));
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["audit"], "target_id", [], "any", false, false, false, 24)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                yield " #";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["audit"], "target_id", [], "any", false, false, false, 24), "html", null, true);
            }
            yield "</td><td>";
            yield ((CoreExtension::getAttribute($this->env, $this->source, $context["audit"], "ip_address", [], "any", false, false, false, 24)) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["audit"], "ip_address", [], "any", false, false, false, 24), "html", null, true)) : ("-"));
            yield "</td><td class=\"ativa-json\">";
            yield ((CoreExtension::getAttribute($this->env, $this->source, $context["audit"], "old_value", [], "any", false, false, false, 24)) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["audit"], "old_value", [], "any", false, false, false, 24), "html", null, true)) : ("-"));
            yield "</td><td class=\"ativa-json\">";
            yield ((CoreExtension::getAttribute($this->env, $this->source, $context["audit"], "new_value", [], "any", false, false, false, 24)) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["audit"], "new_value", [], "any", false, false, false, 24), "html", null, true)) : ("-"));
            yield "</td></tr>";
            $context['_iterated'] = true;
        }
        if (!$context['_iterated']) {
            yield "<tr><td colspan=\"7\" class=\"text-center text-muted py-4\">Nenhum evento registrado.</td></tr>";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['audit'], $context['_parent'], $context['_iterated']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 25
        yield "  </tbody></table></div></section>
</div>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "@ativawallpaper/history.html.twig";
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
        return array (  185 => 25,  153 => 24,  133 => 21,  130 => 20,  123 => 18,  121 => 17,  116 => 16,  106 => 15,  89 => 13,  81 => 12,  72 => 10,  67 => 9,  62 => 8,  47 => 4,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "@ativawallpaper/history.html.twig", "/var/www/html/glpi/plugins/ativawallpaper/templates/history.html.twig");
    }
}
