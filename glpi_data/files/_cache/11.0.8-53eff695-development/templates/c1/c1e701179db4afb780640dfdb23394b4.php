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

/* __string_template__0bc8cc32591c9958681bfd8ee6d1cc15 */
class __TwigTemplate_e86af308b19d82cd1c450f0a101cd456 extends Template
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
        yield "            ";
        if ((($tmp = (isset($context["mini"]) || array_key_exists("mini", $context) ? $context["mini"] : (function () { throw new RuntimeError('Variable "mini" does not exist.', 1, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 2
            yield "                <div class=\x27card mb-4 d-none d-md-block dashboard-card\x27>
                    <div class=\x27card-body p-2\x27>
            ";
        }
        // line 5
        yield "            <div class=\"dashboard ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["embed_class"]) || array_key_exists("embed_class", $context) ? $context["embed_class"] : (function () { throw new RuntimeError('Variable "embed_class" does not exist.', 5, $this->source); })()), "html", null, true);
        yield " ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["mini_class"]) || array_key_exists("mini_class", $context) ? $context["mini_class"] : (function () { throw new RuntimeError('Variable "mini_class" does not exist.', 5, $this->source); })()), "html", null, true);
        yield "\" id=\"dashboard-";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["rand"]) || array_key_exists("rand", $context) ? $context["rand"] : (function () { throw new RuntimeError('Variable "rand" does not exist.', 5, $this->source); })()), "html", null, true);
        yield "\">
                <span class=\x27glpi_logo\x27></span>
                ";
        // line 7
        yield (isset($context["toolbars"]) || array_key_exists("toolbars", $context) ? $context["toolbars"] : (function () { throw new RuntimeError('Variable "toolbars" does not exist.', 7, $this->source); })());
        yield "
                ";
        // line 8
        yield (isset($context["filters"]) || array_key_exists("filters", $context) ? $context["filters"] : (function () { throw new RuntimeError('Variable "filters" does not exist.', 8, $this->source); })());
        yield "
                <div class=\"grid-stack grid-stack-";
        // line 9
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["grid_cols"]) || array_key_exists("grid_cols", $context) ? $context["grid_cols"] : (function () { throw new RuntimeError('Variable "grid_cols" does not exist.', 9, $this->source); })()), "html", null, true);
        yield "\"
                id=\"grid-stack-";
        // line 10
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["rand"]) || array_key_exists("rand", $context) ? $context["rand"] : (function () { throw new RuntimeError('Variable "rand" does not exist.', 10, $this->source); })()), "html", null, true);
        yield "\"
                gs-column=\"";
        // line 11
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["grid_cols"]) || array_key_exists("grid_cols", $context) ? $context["grid_cols"] : (function () { throw new RuntimeError('Variable "grid_cols" does not exist.', 11, $this->source); })()), "html", null, true);
        yield "\"
                gs-min-row=\"";
        // line 12
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["grid_rows"]) || array_key_exists("grid_rows", $context) ? $context["grid_rows"] : (function () { throw new RuntimeError('Variable "grid_rows" does not exist.', 12, $this->source); })()), "html", null, true);
        yield "\"
                style=\"width: 100%; --gs-col-count: ";
        // line 13
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["grid_cols"]) || array_key_exists("grid_cols", $context) ? $context["grid_cols"] : (function () { throw new RuntimeError('Variable "grid_cols" does not exist.', 13, $this->source); })()), "html", null, true);
        yield "; --gs-row-count: ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["grid_rows"]) || array_key_exists("grid_rows", $context) ? $context["grid_rows"] : (function () { throw new RuntimeError('Variable "grid_rows" does not exist.', 13, $this->source); })()), "html", null, true);
        yield "; --gs-cell-margin: ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["cell_margin"]) || array_key_exists("cell_margin", $context) ? $context["cell_margin"] : (function () { throw new RuntimeError('Variable "cell_margin" does not exist.', 13, $this->source); })()), "html", null, true);
        yield "px;\">
                    ";
        // line 14
        yield (isset($context["grid_guide"]) || array_key_exists("grid_guide", $context) ? $context["grid_guide"] : (function () { throw new RuntimeError('Variable "grid_guide" does not exist.', 14, $this->source); })());
        yield "
                    ";
        // line 15
        yield (isset($context["gridstack_items"]) || array_key_exists("gridstack_items", $context) ? $context["gridstack_items"] : (function () { throw new RuntimeError('Variable "gridstack_items" does not exist.', 15, $this->source); })());
        yield "
                </div>
            </div>
            ";
        // line 18
        if ((($tmp = (isset($context["mini"]) || array_key_exists("mini", $context) ? $context["mini"] : (function () { throw new RuntimeError('Variable "mini" does not exist.', 18, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 19
            yield "                    </div>
                </div>
            ";
        }
        // line 22
        yield "            <script type=\"module\">
                import(\x27/js/modules/Dashboard/Dashboard.js\x27).then((m) => {
                    new m.GLPIDashboard(";
        // line 24
        yield json_encode((isset($context["js_params"]) || array_key_exists("js_params", $context) ? $context["js_params"] : (function () { throw new RuntimeError('Variable "js_params" does not exist.', 24, $this->source); })()));
        yield ");
                });
            </script>";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "__string_template__0bc8cc32591c9958681bfd8ee6d1cc15";
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
        return array (  113 => 24,  109 => 22,  104 => 19,  102 => 18,  96 => 15,  92 => 14,  84 => 13,  80 => 12,  76 => 11,  72 => 10,  68 => 9,  64 => 8,  60 => 7,  50 => 5,  45 => 2,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("            {% if mini %}
                <div class=\x27card mb-4 d-none d-md-block dashboard-card\x27>
                    <div class=\x27card-body p-2\x27>
            {% endif %}
            <div class=\"dashboard {{ embed_class }} {{ mini_class }}\" id=\"dashboard-{{ rand }}\">
                <span class=\x27glpi_logo\x27></span>
                {{ toolbars|raw }}
                {{ filters|raw }}
                <div class=\"grid-stack grid-stack-{{ grid_cols }}\"
                id=\"grid-stack-{{ rand }}\"
                gs-column=\"{{ grid_cols }}\"
                gs-min-row=\"{{ grid_rows }}\"
                style=\"width: 100%; --gs-col-count: {{ grid_cols }}; --gs-row-count: {{ grid_rows }}; --gs-cell-margin: {{ cell_margin }}px;\">
                    {{ grid_guide|raw }}
                    {{ gridstack_items|raw }}
                </div>
            </div>
            {% if mini %}
                    </div>
                </div>
            {% endif %}
            <script type=\"module\">
                import(\x27/js/modules/Dashboard/Dashboard.js\x27).then((m) => {
                    new m.GLPIDashboard({{ js_params|json_encode|raw }});
                });
            </script>", "__string_template__0bc8cc32591c9958681bfd8ee6d1cc15", "");
    }
}
