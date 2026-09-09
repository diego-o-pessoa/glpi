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

/* components/form/rulematchedlogs.html.twig */
class __TwigTemplate_548a9c08b44595f62c8745578d31cb4c extends Template
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
            'more_fields' => [$this, 'block_more_fields'],
        ];
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 32
        yield "
";
        // line 33
        yield from $this->unwrap()->yieldBlock('more_fields', $context, $blocks);
        yield from [];
    }

    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_more_fields(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 34
        yield "    ";
        $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Html::printAjaxPager", [$this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("RuleMatchedLog"), ($context["start"] ?? null), ($context["count"] ?? null)]);
        // line 35
        yield "
    <table class=\"tab_cadre_fixe\">
        <tr>
            <th colspan=\"5\"> ";
        // line 38
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Rule import logs"), "html", null, true);
        yield " </th>
        </tr>
        <tr>
            <th> ";
        // line 41
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(_n("Date", "Dates", 1), "html", null, true);
        yield "</th>
            <th>";
        // line 42
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Rule name"), "html", null, true);
        yield "</th>
            <th>
                ";
        // line 44
        if ($this->extensions['Glpi\Application\View\Extension\PhpExtension']->isInstanceOf(($context["item"] ?? null), "Agent")) {
            // line 45
            yield "                    ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(_n("Item", "Items", 1), "html", null, true);
            yield "
                ";
        } else {
            // line 47
            yield "                    ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("Agent"), "html", null, true);
            yield "
                ";
        }
        // line 49
        yield "            </th>
            <th>";
        // line 50
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Module"), "html", null, true);
        yield "</th>
            <th style=\"width: 30%\">";
        // line 51
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Input"), "html", null, true);
        yield "</th>
        </tr>
        ";
        // line 53
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["rows"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["data"]) {
            // line 54
            yield "            <tr>
                <td>";
            // line 55
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getFormattedDatetime((($_v0 = $context["data"]) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0["date"] ?? null) : null)), "html", null, true);
            yield "</td>
                <td>";
            // line 56
            yield $this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemLink("RuleImportAsset", (($_v1 = $context["data"]) && is_array($_v1) || $_v1 instanceof ArrayAccess ? ($_v1["rules_id"] ?? null) : null));
            yield "</td>
                <td>
                    ";
            // line 58
            if ($this->extensions['Glpi\Application\View\Extension\PhpExtension']->isInstanceOf(($context["item"] ?? null), "Agent")) {
                // line 59
                yield "                        ";
                yield $this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemLink((($_v2 = $context["data"]) && is_array($_v2) || $_v2 instanceof ArrayAccess ? ($_v2["itemtype"] ?? null) : null), (($_v3 = $context["data"]) && is_array($_v3) || $_v3 instanceof ArrayAccess ? ($_v3["items_id"] ?? null) : null));
                yield "
                    ";
            } else {
                // line 61
                yield "                        ";
                yield $this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemLink("Agent", (($_v4 = $context["data"]) && is_array($_v4) || $_v4 instanceof ArrayAccess ? ($_v4["agents_id"] ?? null) : null));
                yield "
                    ";
            }
            // line 63
            yield "                </td>
                <td>";
            // line 64
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v5 = $context["data"]) && is_array($_v5) || $_v5 instanceof ArrayAccess ? ($_v5["modulename"] ?? null) : null), "html", null, true);
            yield "</td>
                <td>
                    ";
            // line 66
            if ((is_iterable((($_v6 = $context["data"]) && is_array($_v6) || $_v6 instanceof ArrayAccess ? ($_v6["input"] ?? null) : null)) &&  !Twig\Extension\CoreExtension::testEmpty((($_v7 = $context["data"]) && is_array($_v7) || $_v7 instanceof ArrayAccess ? ($_v7["input"] ?? null) : null)))) {
                // line 67
                yield "                        ";
                $context["rand"] = Twig\Extension\CoreExtension::random($this->env->getCharset());
                // line 68
                yield "                        <div class=\"accordion\" id=\"inputAccordion";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
                yield "\">
                            <div id=\"item";
                // line 69
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
                yield "\">
                                <a class=\"btn btn-ghost-secondary btn-sm\" data-bs-toggle=\"collapse\" href=\"#collapse";
                // line 70
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
                yield "\">";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("See input"), "html", null, true);
                yield "</a>
                            </div>
                            <div id=\"collapse";
                // line 72
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
                yield "\" class=\"accordion-collapse collapse\" data-bs-parent=\"#inputAccordion";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
                yield "\">
                                <div class=\"nested list-group list-group-flush card\">
                                    <div class=\"list-group-item \">
                                        ";
                // line 75
                $context['_parent'] = $context;
                $context['_seq'] = CoreExtension::ensureTraversable((($_v8 = $context["data"]) && is_array($_v8) || $_v8 instanceof ArrayAccess ? ($_v8["input"] ?? null) : null));
                foreach ($context['_seq'] as $context["name"] => $context["value"]) {
                    // line 76
                    yield "                                            ";
                    if (is_iterable($context["value"])) {
                        // line 77
                        yield "                                                <b>";
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["name"], "html", null, true);
                        yield "</b> :
                                                ";
                        // line 78
                        $context['_parent'] = $context;
                        $context['_seq'] = CoreExtension::ensureTraversable($context["value"]);
                        foreach ($context['_seq'] as $context["_key"] => $context["subvalue"]) {
                            // line 79
                            yield "                                                    <br>&emsp;&bull;";
                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["subvalue"], "html", null, true);
                            yield "
                                                ";
                        }
                        $_parent = $context['_parent'];
                        unset($context['_seq'], $context['_key'], $context['subvalue'], $context['_parent']);
                        $context = array_intersect_key($context, $_parent) + $_parent;
                        // line 81
                        yield "                                                <br>
                                            ";
                    } else {
                        // line 83
                        yield "                                                <b>";
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["name"], "html", null, true);
                        yield "</b> : ";
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["value"], "html", null, true);
                        yield "<br>
                                            ";
                    }
                    // line 85
                    yield "                                        ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['name'], $context['value'], $context['_parent']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 86
                yield "                                    </div>
                                </div>
                            </div>
                        </div>
                    ";
            } else {
                // line 91
                yield "                        ";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("No input"), "html", null, true);
                yield "
                    ";
            }
            // line 93
            yield "                </td>
            </tr>
        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['data'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 96
        yield "    </table>

    ";
        // line 98
        $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Html::printAjaxPager", [$this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("RuleMatchedLog"), ($context["start"] ?? null), ($context["count"] ?? null)]);
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "components/form/rulematchedlogs.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  241 => 98,  237 => 96,  229 => 93,  223 => 91,  216 => 86,  210 => 85,  202 => 83,  198 => 81,  189 => 79,  185 => 78,  180 => 77,  177 => 76,  173 => 75,  165 => 72,  158 => 70,  154 => 69,  149 => 68,  146 => 67,  144 => 66,  139 => 64,  136 => 63,  130 => 61,  124 => 59,  122 => 58,  117 => 56,  113 => 55,  110 => 54,  106 => 53,  101 => 51,  97 => 50,  94 => 49,  88 => 47,  82 => 45,  80 => 44,  75 => 42,  71 => 41,  65 => 38,  60 => 35,  57 => 34,  46 => 33,  43 => 32,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "components/form/rulematchedlogs.html.twig", "/var/www/html/glpi/templates/components/form/rulematchedlogs.html.twig");
    }
}
