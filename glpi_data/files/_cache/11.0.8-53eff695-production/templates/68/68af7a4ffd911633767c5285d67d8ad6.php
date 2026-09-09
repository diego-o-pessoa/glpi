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

/* pages/assets/template_list.html.twig */
class __TwigTemplate_8e02db8db0f661d641cc44e640d511eb extends Template
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
";
        // line 59
        yield "
<div class=\"d-flex mx-auto justify-content-center\">
    <div class=\"card col-10 col-sm-6\">
        <div class=\"card-body p-0\">
            <div class=\"table-responsive\">
                <table class=\"table\">
                    ";
        // line 65
        if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["templates"] ?? null)) == 0)) {
            // line 66
            yield "                        <tr>
                            <td>
                                <div class=\"alert alert-info\">";
            // line 68
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("No results found"), "html", null, true);
            yield "</div>
                            </td>
                        </tr>
                    ";
        } else {
            // line 72
            yield "                        <thead>
                            <tr>
                                <th>";
            // line 74
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(_n("Template", "Templates", 1), "html", null, true);
            yield "</th>
                                ";
            // line 75
            if (( !($context["add_mode"] ?? null) && Twig\Extension\CoreExtension::default($this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpi_multientitiesmode"), false))) {
                // line 76
                yield "                                    <th>";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("Entity", 1), "html", null, true);
                yield "</th>
                                ";
            }
            // line 78
            yield "                                ";
            if ((($tmp =  !($context["add_mode"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 79
                yield "                                    <th>";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(_n("Action", "Actions", Session::getPluralNumber()), "html", null, true);
                yield "</th>
                                ";
            }
            // line 81
            yield "                            </tr>
                        </thead>
                        <tbody>
                            ";
            // line 84
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["templates"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["template"]) {
                // line 85
                yield "                                ";
                yield $this->getTemplateForMacro("macro_template_row", $context, 85, $this->getSourceContext())->macro_template_row(...[$context["template"], ($context["target"] ?? null), ($context["add_mode"] ?? null)]);
                yield "
                            ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['template'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 87
            yield "                        </tbody>
                    ";
        }
        // line 89
        yield "                </table>
            </div>
        </div>
        ";
        // line 92
        if ((($tmp = ($context["add_template"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 93
            yield "            <div class=\"card-footer text-center py-2\">
                <a href=\"";
            // line 94
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["target_create"] ?? null), "html", null, true);
            yield "\" class=\"mt-3\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Add a template"), "html", null, true);
            yield "</a>
            </div>
        ";
        }
        // line 97
        yield "    </div>
</div>
";
        yield from [];
    }

    // line 33
    public function macro_template_row($template = null, $target = null, $add_mode = null, ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "template" => $template,
            "target" => $target,
            "add_mode" => $add_mode,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 34
            yield "    ";
            $macros["fields"] = $this->load("components/form/fields_macros.html.twig", 34)->unwrap();
            // line 35
            yield "    <tr>
        <td>
            ";
            // line 37
            yield (($_v0 = ($context["template"] ?? null)) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0["name"] ?? null) : null);
            yield "
        </td>
        ";
            // line 39
            if (( !($context["add_mode"] ?? null) && Twig\Extension\CoreExtension::default($this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpi_multientitiesmode"), false))) {
                // line 40
                yield "            <td>";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v1 = ($context["template"] ?? null)) && is_array($_v1) || $_v1 instanceof ArrayAccess ? ($_v1["entity"] ?? null) : null), "html", null, true);
                yield "</td>
        ";
            }
            // line 42
            yield "        ";
            if ((($tmp =  !($context["add_mode"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 43
                yield "            <td>
                <form method=\"post\" action=\"";
                // line 44
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["target"] ?? null), "html", null, true);
                yield "\" data-submit-once>
                    ";
                // line 45
                if ((($tmp = (($_v2 = ($context["template"] ?? null)) && is_array($_v2) || $_v2 instanceof ArrayAccess ? ($_v2["can_delete"] ?? null) : null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 46
                    yield "                        <input type=\"hidden\" name=\"id\" value=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v3 = ($context["template"] ?? null)) && is_array($_v3) || $_v3 instanceof ArrayAccess ? ($_v3["id"] ?? null) : null), "html", null, true);
                    yield "\">
                        <button class=\"btn btn-danger me-2\" type=\"submit\" name=\"purge\" value=\"1\"
                                onclick=\"return confirm(\x27";
                    // line 48
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Confirm the final deletion?"), "js"), "html", null, true);
                    yield "\x27);\">
                            <i class=\"ti ti-trash\"></i>
                            <span>";
                    // line 50
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(_x("button", "Delete permanently"), "html", null, true);
                    yield "</span>
                        </button>
                        ";
                    // line 52
                    yield $macros["fields"]->getTemplateForMacro("macro_csrfField", $context, 52, $this->getSourceContext())->macro_csrfField(...[]);
                    yield "
                    ";
                }
                // line 54
                yield "                </form>
            </td>
        ";
            }
            // line 57
            yield "    </tr>
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "pages/assets/template_list.html.twig";
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
        return array (  210 => 57,  205 => 54,  200 => 52,  195 => 50,  190 => 48,  184 => 46,  182 => 45,  178 => 44,  175 => 43,  172 => 42,  166 => 40,  164 => 39,  159 => 37,  155 => 35,  152 => 34,  138 => 33,  131 => 97,  123 => 94,  120 => 93,  118 => 92,  113 => 89,  109 => 87,  100 => 85,  96 => 84,  91 => 81,  85 => 79,  82 => 78,  76 => 76,  74 => 75,  70 => 74,  66 => 72,  59 => 68,  55 => 66,  53 => 65,  45 => 59,  42 => 32,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "pages/assets/template_list.html.twig", "/var/www/html/glpi/templates/pages/assets/template_list.html.twig");
    }
}
