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

/* @glpiinventory/forms/agentmodule.html.twig */
class __TwigTemplate_84eb7bfd9000b346437efc0fd5400b24 extends Template
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
        // line 28
        yield "
";
        // line 29
        $macros["fields"] = $this->macros["fields"] = $this->load("components/form/fields_macros.html.twig", 29)->unwrap();
        // line 30
        yield "
";
        // line 31
        if ((($tmp = ($context["canedit"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 32
            yield "<form method=\"POST\" action=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["form_url"] ?? null), "html", null, true);
            yield "\">
";
        }
        // line 34
        yield "    <table class=\x27tab_cadre_fixe\x27>
        <tr>
            <th style=\"width: 20%;\">";
        // line 36
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Module", "glpiinventory"), "html", null, true);
        yield "</th>
            <th style=\"width: 10%;\">
    ";
        // line 38
        if (array_key_exists("agents_id", $context)) {
            // line 39
            yield "                ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Activation", "glpiinventory"), "html", null, true);
            yield "
    ";
        } else {
            // line 41
            yield "                ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Activation (by default)", "glpiinventory"), "html", null, true);
            yield "
    ";
        }
        // line 43
        yield "            </th>
    ";
        // line 44
        if ((($tmp =  !array_key_exists("agents_id", $context)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 45
            yield "            <th>";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Exceptions", "glpiinventory"), "html", null, true);
            yield "</th>
    ";
        }
        // line 47
        yield "        </tr>
";
        // line 48
        $context["rand"] = Twig\Extension\CoreExtension::random($this->env->getCharset());
        // line 49
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["modules"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["module"]) {
            // line 50
            yield "        <tr>
            <td><label for=\"";
            // line 51
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((CoreExtension::getAttribute($this->env, $this->source, $context["module"], "id", [], "any", false, false, false, 51) . "_is_active_") . ($context["rand"] ?? null)), "html", null, true);
            yield "\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["module"], "displayname", [], "any", false, false, false, 51), "html", null, true);
            yield "</label></td>
            <td>
                ";
            // line 53
            yield $macros["fields"]->getTemplateForMacro("macro_checkboxField", $context, 53, $this->getSourceContext())->macro_checkboxField(...[(CoreExtension::getAttribute($this->env, $this->source,             // line 54
$context["module"], "id", [], "any", false, false, false, 54) . "_is_active"), CoreExtension::getAttribute($this->env, $this->source,             // line 55
$context["module"], "is_active", [], "any", false, false, false, 55), "", ["rand" =>             // line 57
($context["rand"] ?? null)]]);
            // line 59
            yield "
            </td>
    ";
            // line 61
            if ((($tmp =  !array_key_exists("agents_id", $context)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 62
                yield "            <td>
                ";
                // line 63
                yield $macros["fields"]->getTemplateForMacro("macro_dropdownField", $context, 63, $this->getSourceContext())->macro_dropdownField(...["Agent", (CoreExtension::getAttribute($this->env, $this->source,                 // line 65
$context["module"], "id", [], "any", false, false, false, 65) . "_exceptions"), CoreExtension::getAttribute($this->env, $this->source,                 // line 66
$context["module"], "exceptions", [], "any", false, false, false, 66), "", ["multiple" => true, "no_label" => true, "full_width" => true]]);
                // line 73
                yield "
            </td>
    ";
            }
            // line 76
            yield "        </tr>
";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['module'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 78
        yield "    </table>
";
        // line 79
        if ((($tmp = ($context["canedit"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 80
            yield "    <div class=\"card-footer mx-n2 d-flex\">
        <button type=\"submit\" name=\"update";
            // line 81
            yield ((array_key_exists("agents_id", $context)) ? ("") : ("_exceptions"));
            yield "\" value=\"1\" class=\"ms-auto btn btn-primary\">
            <i class=\"ti ti-device-floppy\"></i>
            <span>";
            // line 83
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(_x("button", "Update"), "html", null, true);
            yield "</span>
        </button>
    ";
            // line 85
            if (array_key_exists("agents_id", $context)) {
                // line 86
                yield "        ";
                yield $macros["fields"]->getTemplateForMacro("macro_hiddenField", $context, 86, $this->getSourceContext())->macro_hiddenField(...["agents_id", ($context["agents_id"] ?? null)]);
                yield "
    ";
            }
            // line 88
            yield "        ";
            yield $macros["fields"]->getTemplateForMacro("macro_hiddenField", $context, 88, $this->getSourceContext())->macro_hiddenField(...["_glpi_csrf_token", Session::getNewCSRFToken()]);
            yield "
    </div>
</form>
";
        }
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "@glpiinventory/forms/agentmodule.html.twig";
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
        return array (  167 => 88,  161 => 86,  159 => 85,  154 => 83,  149 => 81,  146 => 80,  144 => 79,  141 => 78,  134 => 76,  129 => 73,  127 => 66,  126 => 65,  125 => 63,  122 => 62,  120 => 61,  116 => 59,  114 => 57,  113 => 55,  112 => 54,  111 => 53,  104 => 51,  101 => 50,  97 => 49,  95 => 48,  92 => 47,  86 => 45,  84 => 44,  81 => 43,  75 => 41,  69 => 39,  67 => 38,  62 => 36,  58 => 34,  52 => 32,  50 => 31,  47 => 30,  45 => 29,  42 => 28,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "@glpiinventory/forms/agentmodule.html.twig", "/var/www/html/glpi/marketplace/glpiinventory/templates/forms/agentmodule.html.twig");
    }
}
