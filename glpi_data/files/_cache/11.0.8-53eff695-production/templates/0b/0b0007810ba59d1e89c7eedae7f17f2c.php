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

/* pages/assistance/planning/filters.html.twig */
class __TwigTemplate_e71bcf35f0cf954666bc8c74a60ba769 extends Template
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
<div id=\"planning_filter\">
    <div id=\"planning_filter_toggle\">
        <a class=\"toggle cursor-pointer\" title=\"";
        // line 35
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Toggle filters"), "html", null, true);
        yield "\"></a>
    </div>
    <div id=\"planning_filter_content\">
        ";
        // line 38
        $context["headings"] = ["filters" => __("Filters"), "plannings" => __("Plannings")];
        // line 42
        yield "        ";
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable($this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpi_plannings"));
        foreach ($context['_seq'] as $context["filter_heading"] => $context["filters"]) {
            // line 43
            yield "            ";
            if (CoreExtension::inFilter($context["filter_heading"], Twig\Extension\CoreExtension::keys(($context["headings"] ?? null)))) {
                // line 44
                yield "                <div>
                    <h3>
                        ";
                // line 46
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v0 = ($context["headings"] ?? null)) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0[(($_v1 = $context["filter_heading"]) instanceof \Stringable ? (string) $_v1 : $_v1)] ?? null) : null), "html", null, true);
                yield "
                        ";
                // line 47
                if (($context["filter_heading"] == "plannings")) {
                    // line 48
                    yield "                            <a class=\"planning_link planning_add_filter\" href=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path("/ajax/planning.php?action=add_planning_form"), "html", null, true);
                    yield "\" title=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Add a calendar"), "html", null, true);
                    yield "\">
                                <i class=\"ti ti-circle-plus\"></i>
                            </a>
                        ";
                }
                // line 52
                yield "                    </h3>
                    <ul class=\"filters\">
                        ";
                // line 54
                $context['_parent'] = $context;
                $context['_seq'] = CoreExtension::ensureTraversable($context["filters"]);
                foreach ($context['_seq'] as $context["filter_key"] => $context["filter_data"]) {
                    // line 55
                    yield "                            ";
                    $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Planning::showSingleLinePlanningFilter", [$context["filter_key"], $context["filter_data"], ["filter_color_index" => 0]]);
                    // line 56
                    yield "                        ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['filter_key'], $context['filter_data'], $context['_parent']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 57
                yield "                    </ul>
                </div>
            ";
            }
            // line 60
            yield "        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['filter_heading'], $context['filters'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 61
        yield "    </div>
</div>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "pages/assistance/planning/filters.html.twig";
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
        return array (  111 => 61,  105 => 60,  100 => 57,  94 => 56,  91 => 55,  87 => 54,  83 => 52,  73 => 48,  71 => 47,  67 => 46,  63 => 44,  60 => 43,  55 => 42,  53 => 38,  47 => 35,  42 => 32,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "pages/assistance/planning/filters.html.twig", "/var/www/html/glpi/templates/pages/assistance/planning/filters.html.twig");
    }
}
