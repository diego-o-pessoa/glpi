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

/* pages/setup/crontask/statistics.html.twig */
class __TwigTemplate_52f7c1185890ccdb14b0d5ed34eb971a extends Template
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
        // line 32
        yield "
<div class=\"d-flex justify-content-center\">
   <div class=\"col-sm-8 col-md-6 col-xlg-4 col-12 mx-2 mw-50\">
      <div class=\"row\">
         <h1>";
        // line 36
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Statistics"), "html", null, true);
        yield "</h1>
      </div>
      <div class=\"list-group divide-y\">
         <div class=\"row list-group-item d-flex\">
            <div class=\"col\">";
        // line 40
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Run count"), "html", null, true);
        yield "</div>
            <div class=\"col text-end\">
               <ul class=\"list-unstyled\">
                  <li>";
        // line 43
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::sprintf(_n("%s start", "%s starts", CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["stats"] ?? null), "runs", [], "any", false, false, false, 43), "starts", [], "any", false, false, false, 43)), CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["stats"] ?? null), "runs", [], "any", false, false, false, 43), "starts", [], "any", false, false, false, 43)), "html", null, true);
        yield "</li>
                  <li>";
        // line 44
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::sprintf(_n("%s stop", "%s stops", CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["stats"] ?? null), "runs", [], "any", false, false, false, 44), "stops", [], "any", false, false, false, 44)), CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["stats"] ?? null), "runs", [], "any", false, false, false, 44), "stops", [], "any", false, false, false, 44)), "html", null, true);
        yield "</li>
                  <li>";
        // line 45
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::sprintf(_n("%s error", "%s errors", CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["stats"] ?? null), "runs", [], "any", false, false, false, 45), "errors", [], "any", false, false, false, 45)), CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["stats"] ?? null), "runs", [], "any", false, false, false, 45), "errors", [], "any", false, false, false, 45)), "html", null, true);
        yield "</li>
               </ul>
            </div>
         </div>
         ";
        // line 49
        if ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["stats"] ?? null), "runs", [], "any", false, false, false, 49), "stops", [], "any", false, false, false, 49) > 0)) {
            // line 50
            yield "            <div class=\"row list-group-item d-flex\">
               <div class=\"col\">";
            // line 51
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Start date"), "html", null, true);
            yield "</div>
               <div class=\"col text-end\">";
            // line 52
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getFormattedDatetime(CoreExtension::getAttribute($this->env, $this->source, ($context["stats"] ?? null), "datemin", [], "any", false, false, false, 52)), "html", null, true);
            yield "</div>
            </div>
            <div class=\"row list-group-item d-flex\">
               <div class=\"col\">";
            // line 55
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Minimal time"), "html", null, true);
            yield "</div>
               <div class=\"col text-end\">";
            // line 56
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::sprintf(_n("%s second", "%s seconds", CoreExtension::getAttribute($this->env, $this->source, ($context["stats"] ?? null), "elapsedmin", [], "any", false, false, false, 56)), $this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getFormattedNumber(CoreExtension::getAttribute($this->env, $this->source, ($context["stats"] ?? null), "elapsedmin", [], "any", false, false, false, 56))), "html", null, true);
            yield "</div>
            </div>
            <div class=\"row list-group-item d-flex\">
               <div class=\"col\">";
            // line 59
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Maximal time"), "html", null, true);
            yield "</div>
               <div class=\"col text-end\">";
            // line 60
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::sprintf(_n("%s second", "%s seconds", CoreExtension::getAttribute($this->env, $this->source, ($context["stats"] ?? null), "elapsedmax", [], "any", false, false, false, 60)), $this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getFormattedNumber(CoreExtension::getAttribute($this->env, $this->source, ($context["stats"] ?? null), "elapsedmax", [], "any", false, false, false, 60))), "html", null, true);
            yield "</div>
            </div>
            <div class=\"row list-group-item d-flex\">
               <div class=\"col\">";
            // line 63
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Average time"), "html", null, true);
            yield "</div>
               <div class=\"col text-end fw-bold\">";
            // line 64
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::sprintf(_n("%s second", "%s seconds", CoreExtension::getAttribute($this->env, $this->source, ($context["stats"] ?? null), "elapsedavg", [], "any", false, false, false, 64)), $this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getFormattedNumber(CoreExtension::getAttribute($this->env, $this->source, ($context["stats"] ?? null), "elapsedavg", [], "any", false, false, false, 64))), "html", null, true);
            yield "</div>
            </div>
            <div class=\"row list-group-item d-flex\">
               <div class=\"col\">";
            // line 67
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Total duration"), "html", null, true);
            yield "</div>
               <div class=\"col text-end\">";
            // line 68
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::sprintf(_n("%s second", "%s seconds", CoreExtension::getAttribute($this->env, $this->source, ($context["stats"] ?? null), "elapsedtot", [], "any", false, false, false, 68)), $this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getFormattedNumber(CoreExtension::getAttribute($this->env, $this->source, ($context["stats"] ?? null), "elapsedtot", [], "any", false, false, false, 68))), "html", null, true);
            yield "</div>
            </div>
            ";
            // line 70
            if ((CoreExtension::getAttribute($this->env, $this->source, ($context["stats"] ?? null), "voltot", [], "any", false, false, false, 70) > 0)) {
                // line 71
                yield "               <div class=\"row list-group-item d-flex\">
                  <div class=\"col\">";
                // line 72
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Minimal count"), "html", null, true);
                yield "</div>
                  <div class=\"col text-end\">";
                // line 73
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::sprintf(_n("%s item", "%s items", CoreExtension::getAttribute($this->env, $this->source, ($context["stats"] ?? null), "volmin", [], "any", false, false, false, 73)), CoreExtension::getAttribute($this->env, $this->source, ($context["stats"] ?? null), "volmin", [], "any", false, false, false, 73)), "html", null, true);
                yield "</div>
               </div>
               <div class=\"row list-group-item d-flex\">
                  <div class=\"col\">";
                // line 76
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Maximal count"), "html", null, true);
                yield "</div>
                  <div class=\"col text-end\">";
                // line 77
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::sprintf(_n("%s item", "%s items", CoreExtension::getAttribute($this->env, $this->source, ($context["stats"] ?? null), "volmax", [], "any", false, false, false, 77)), CoreExtension::getAttribute($this->env, $this->source, ($context["stats"] ?? null), "volmax", [], "any", false, false, false, 77)), "html", null, true);
                yield "</div>
               </div>
               <div class=\"row list-group-item d-flex\">
                  <div class=\"col\">";
                // line 80
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Average count"), "html", null, true);
                yield "</div>
                  <div class=\"col text-end fw-bold\">";
                // line 81
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::sprintf(_n("%s item", "%s items", CoreExtension::getAttribute($this->env, $this->source, ($context["stats"] ?? null), "volavg", [], "any", false, false, false, 81)), $this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getFormattedNumber(CoreExtension::getAttribute($this->env, $this->source, ($context["stats"] ?? null), "volavg", [], "any", false, false, false, 81))), "html", null, true);
                yield "</div>
               </div>
               <div class=\"row list-group-item d-flex\">
                  <div class=\"col\">";
                // line 84
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Total count"), "html", null, true);
                yield "</div>
                  <div class=\"col text-end\">";
                // line 85
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::sprintf(_n("%s item", "%s items", CoreExtension::getAttribute($this->env, $this->source, ($context["stats"] ?? null), "voltot", [], "any", false, false, false, 85)), CoreExtension::getAttribute($this->env, $this->source, ($context["stats"] ?? null), "voltot", [], "any", false, false, false, 85)), "html", null, true);
                yield "</div>
               </div>
               <div class=\"row list-group-item d-flex\">
                  <div class=\"col\">";
                // line 88
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Average speed"), "html", null, true);
                yield "</div>
                  <div class=\"col text-end\">";
                // line 89
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::sprintf(__("%s items/sec"), $this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getFormattedNumber((CoreExtension::getAttribute($this->env, $this->source, ($context["stats"] ?? null), "voltot", [], "any", false, false, false, 89) / CoreExtension::getAttribute($this->env, $this->source, ($context["stats"] ?? null), "elapsedtot", [], "any", false, false, false, 89)))), "html", null, true);
                yield "</div>
               </div>
            ";
            }
            // line 92
            yield "         ";
        }
        // line 93
        yield "      </div>
   </div>
</div>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "pages/setup/crontask/statistics.html.twig";
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
        return array (  188 => 93,  185 => 92,  179 => 89,  175 => 88,  169 => 85,  165 => 84,  159 => 81,  155 => 80,  149 => 77,  145 => 76,  139 => 73,  135 => 72,  132 => 71,  130 => 70,  125 => 68,  121 => 67,  115 => 64,  111 => 63,  105 => 60,  101 => 59,  95 => 56,  91 => 55,  85 => 52,  81 => 51,  78 => 50,  76 => 49,  69 => 45,  65 => 44,  61 => 43,  55 => 40,  48 => 36,  42 => 32,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "pages/setup/crontask/statistics.html.twig", "/var/www/html/glpi/templates/pages/setup/crontask/statistics.html.twig");
    }
}
