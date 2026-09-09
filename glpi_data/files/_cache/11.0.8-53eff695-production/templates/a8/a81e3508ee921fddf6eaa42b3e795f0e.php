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

/* pages/2fa/2fa_config.html.twig */
class __TwigTemplate_7fc42d8a88c1369c6730d3b01f98ef4a extends Template
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
        // line 33
        $macros["fields"] = $this->macros["fields"] = $this->load("components/form/fields_macros.html.twig", 33)->unwrap();
        // line 34
        yield "
<div class=\"spaced\">
    ";
        // line 36
        if ((($tmp = ($context["canedit"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 37
            yield "    <form method=\"post\" name=\"form\" action=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["action"] ?? null), "html", null, true);
            yield "\" data-track-changes=\"true\">
        ";
        }
        // line 39
        yield "        <table class=\"tab_cadre_fixe\">
            ";
        // line 40
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\PluginExtension']->callPluginHook(Twig\Extension\CoreExtension::constant("Glpi\\Plugin\\Hooks::PRE_ITEM_FORM"), ["item" =>         // line 41
($context["item"] ?? null), "options" => []]), "html", null, true);
        // line 43
        yield "
            <tr>
                <th colspan=\"2\">";
        // line 45
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Two-factor authentication (2FA)"), "html", null, true);
        yield "</th>
            </tr>
            <tr class=\"tab_bg_1\">
                <td colspan=\"2\">
                    <div class=\"alert alert-info\">
                        ";
        // line 50
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::sprintf(__("If 2FA is enforced, users with access to this %s will be required to use 2FA at login even if this is not their default"), CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "getTypeName", [1], "method", false, false, false, 50)), "html", null, true);
        yield "
                    </div>
                </td>
            </tr>
            <tr class=\"tab_bg_1\">
                ";
        // line 55
        if ($this->extensions['Glpi\Application\View\Extension\PhpExtension']->isInstanceOf(($context["item"] ?? null), "User")) {
            // line 56
            yield "                    <td>";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Unenforce 2FA"), "html", null, true);
            yield "</td>
                ";
        } else {
            // line 58
            yield "                    <td>";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Enforce 2FA"), "html", null, true);
            yield "</td>
                ";
        }
        // line 60
        yield "                <td>
                    ";
        // line 61
        if ((CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "getType", [], "method", false, false, false, 61) == "Entity")) {
            // line 62
            yield "                        ";
            yield $macros["fields"]->getTemplateForMacro("macro_dropdownArrayField", $context, 62, $this->getSourceContext())->macro_dropdownArrayField(...["2fa_enforcement_strategy", (($_v0 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 62)) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0["2fa_enforcement_strategy"] ?? null) : null), [ (string)Twig\Extension\CoreExtension::constant("Entity::CONFIG_PARENT") => __("Inheritance of the parent entity"), "0" => __("No"), "1" => __("Yes")], "", ["no_label" => true, "add_field_html" =>             // line 68
($context["inherited_value"] ?? null)]]);
            // line 69
            yield "
                    ";
        } elseif ($this->extensions['Glpi\Application\View\Extension\PhpExtension']->isInstanceOf(        // line 70
($context["item"] ?? null), "User")) {
            // line 71
            yield "                        ";
            yield $macros["fields"]->getTemplateForMacro("macro_dropdownYesNo", $context, 71, $this->getSourceContext())->macro_dropdownYesNo(...["2fa_unenforced", (($_v1 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 71)) && is_array($_v1) || $_v1 instanceof ArrayAccess ? ($_v1["2fa_unenforced"] ?? null) : null), "", ["no_label" => true]]);
            // line 73
            yield "
                    ";
        } else {
            // line 75
            yield "                        ";
            yield $macros["fields"]->getTemplateForMacro("macro_dropdownYesNo", $context, 75, $this->getSourceContext())->macro_dropdownYesNo(...["2fa_enforced", (($_v2 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 75)) && is_array($_v2) || $_v2 instanceof ArrayAccess ? ($_v2["2fa_enforced"] ?? null) : null), "", ["no_label" => true]]);
            // line 77
            yield "
                    ";
        }
        // line 79
        yield "                </td>
            </tr>
            ";
        // line 81
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\PluginExtension']->callPluginHook(Twig\Extension\CoreExtension::constant("Glpi\\Plugin\\Hooks::POST_ITEM_FORM"), ["item" =>         // line 82
($context["item"] ?? null), "options" => []]), "html", null, true);
        // line 84
        yield "
        </table>
        ";
        // line 86
        if ((($tmp = ($context["canedit"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 87
            yield "        <div class=\"card-body mb-n5 border-top d-flex flex-row-reverse\">
            <input type=\"hidden\" name=\"id\" value=\"";
            // line 88
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v3 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 88)) && is_array($_v3) || $_v3 instanceof ArrayAccess ? ($_v3["id"] ?? null) : null), "html", null, true);
            yield "\">
            ";
            // line 89
            yield $macros["fields"]->getTemplateForMacro("macro_csrfField", $context, 89, $this->getSourceContext())->macro_csrfField(...[]);
            yield "
            <button type=\"submit\" name=\"update\" class=\"btn btn-primary\">
                <i class=\"ti ti-device-floppy me-1\"></i>
                ";
            // line 92
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(_x("button", "Save"), "html", null, true);
            yield "
            </button>
        </div>
    </form>
    ";
        }
        // line 97
        yield "</div>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "pages/2fa/2fa_config.html.twig";
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
        return array (  161 => 97,  153 => 92,  147 => 89,  143 => 88,  140 => 87,  138 => 86,  134 => 84,  132 => 82,  131 => 81,  127 => 79,  123 => 77,  120 => 75,  116 => 73,  113 => 71,  111 => 70,  108 => 69,  106 => 68,  104 => 62,  102 => 61,  99 => 60,  93 => 58,  87 => 56,  85 => 55,  77 => 50,  69 => 45,  65 => 43,  63 => 41,  62 => 40,  59 => 39,  53 => 37,  51 => 36,  47 => 34,  45 => 33,  42 => 32,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "pages/2fa/2fa_config.html.twig", "/var/www/html/glpi/templates/pages/2fa/2fa_config.html.twig");
    }
}
