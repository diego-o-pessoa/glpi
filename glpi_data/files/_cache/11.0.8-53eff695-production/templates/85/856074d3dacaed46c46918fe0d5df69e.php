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

/* @glpiinventory/forms/config/main.html.twig */
class __TwigTemplate_d7fedac0148117becbcc3eb56a545f06 extends Template
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
        $context["no_header"] = true;
        // line 32
        yield "<div class=\"asset\">
    ";
        // line 33
        yield Twig\Extension\CoreExtension::include($this->env, $context, "components/form/header.html.twig", ["in_twig" => true]);
        yield "

    ";
        // line 35
        $context["rand"] = Twig\Extension\CoreExtension::random($this->env->getCharset());
        // line 36
        yield "    ";
        $context["params"] = (((array_key_exists("params", $context) &&  !(null === $context["params"]))) ? ($context["params"]) : ([]));
        // line 37
        yield "    ";
        $context["target"] = (((CoreExtension::getAttribute($this->env, $this->source, ($context["params"] ?? null), "target", [], "array", true, true, false, 37) &&  !(null === (($_v0 = ($context["params"] ?? null)) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0["target"] ?? null) : null)))) ? ((($_v1 = ($context["params"] ?? null)) && is_array($_v1) || $_v1 instanceof ArrayAccess ? ($_v1["target"] ?? null) : null)) : (CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "getFormURL", [], "method", false, false, false, 37)));
        // line 38
        yield "    ";
        $context["withtemplate"] = (((CoreExtension::getAttribute($this->env, $this->source, ($context["params"] ?? null), "withtemplate", [], "array", true, true, false, 38) &&  !(null === (($_v2 = ($context["params"] ?? null)) && is_array($_v2) || $_v2 instanceof ArrayAccess ? ($_v2["withtemplate"] ?? null) : null)))) ? ((($_v3 = ($context["params"] ?? null)) && is_array($_v3) || $_v3 instanceof ArrayAccess ? ($_v3["withtemplate"] ?? null) : null)) : (""));
        // line 39
        yield "    ";
        $context["item_type"] = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "getType", [], "method", false, false, false, 39);
        // line 40
        yield "    ";
        $context["field_options"] = ["rand" => ($context["rand"] ?? null)];
        // line 41
        yield "
    <div class=\"card-body d-flex flex-wrap\">
        <div class=\"col-12 flex-column\">
            <div class=\"d-flex flex-row flex-wrap flex-xl-nowrap\">
                <div class=\"row flex-row align-items-start flex-grow-1\">
                    <div class=\"row flex-row\">
                        ";
        // line 47
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownYesNo", $context, 47, $this->getSourceContext())->macro_dropdownYesNo(...["ssl_only", CoreExtension::getAttribute($this->env, $this->source,         // line 49
($context["item"] ?? null), "isFieldActive", ["ssl_only"], "method", false, false, false, 49), __("SSL-only for agent", "glpiinventory"),         // line 51
($context["field_options"] ?? null)]);
        // line 52
        yield "

                        ";
        // line 54
        yield $macros["fields"]->getTemplateForMacro("macro_textField", $context, 54, $this->getSourceContext())->macro_textField(...["agent_port", CoreExtension::getAttribute($this->env, $this->source,         // line 56
($context["item"] ?? null), "getValue", ["agent_port"], "method", false, false, false, 56), __("Agent port", "glpiinventory"),         // line 58
($context["field_options"] ?? null)]);
        // line 59
        yield "

                        ";
        // line 61
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownNumberField", $context, 61, $this->getSourceContext())->macro_dropdownNumberField(...["delete_task", CoreExtension::getAttribute($this->env, $this->source,         // line 63
($context["item"] ?? null), "getValue", ["delete_task"], "method", false, false, false, 63), __("Delete tasks logs after", "glpiinventory"), Twig\Extension\CoreExtension::merge(        // line 65
($context["field_options"] ?? null), ["min" => 1, "max" => 240, "unit" => "day"])]);
        // line 70
        yield "

                        ";
        // line 72
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownYesNo", $context, 72, $this->getSourceContext())->macro_dropdownYesNo(...["reprepare_job", CoreExtension::getAttribute($this->env, $this->source,         // line 74
($context["item"] ?? null), "isFieldActive", ["reprepare_job"], "method", false, false, false, 74), __("Re-prepare successful jobs", "glpiinventory"),         // line 76
($context["field_options"] ?? null)]);
        // line 77
        yield "

                        ";
        // line 79
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownNumberField", $context, 79, $this->getSourceContext())->macro_dropdownNumberField(...["wakeup_agent_max", CoreExtension::getAttribute($this->env, $this->source,         // line 81
($context["item"] ?? null), "getValue", ["wakeup_agent_max"], "method", false, false, false, 81), __("Maximum number of agents to wake up in a task", "glpiinventory"), Twig\Extension\CoreExtension::merge(        // line 83
($context["field_options"] ?? null), ["min" => 1, "max" => 100])]);
        // line 87
        yield "

                        ";
        // line 89
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownYesNo", $context, 89, $this->getSourceContext())->macro_dropdownYesNo(...["extradebug", CoreExtension::getAttribute($this->env, $this->source,         // line 91
($context["item"] ?? null), "isFieldActive", ["extradebug"], "method", false, false, false, 91), __("Extra-debug", "glpiinventory"),         // line 93
($context["field_options"] ?? null)]);
        // line 94
        yield "
                    </div> ";
        // line 96
        yield "                </div> ";
        // line 97
        yield "            </div> ";
        // line 98
        yield "        </div>
    </div> ";
        // line 100
        yield "
    ";
        // line 101
        if (( !array_key_exists("no_form_buttons", $context) || (($context["no_form_buttons"] ?? null) == false))) {
            // line 102
            yield "        ";
            yield Twig\Extension\CoreExtension::include($this->env, $context, "components/form/buttons.html.twig");
            yield "
    ";
        }
        // line 104
        yield "
</div> ";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "@glpiinventory/forms/config/main.html.twig";
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
        return array (  150 => 104,  144 => 102,  142 => 101,  139 => 100,  136 => 98,  134 => 97,  132 => 96,  129 => 94,  127 => 93,  126 => 91,  125 => 89,  121 => 87,  119 => 83,  118 => 81,  117 => 79,  113 => 77,  111 => 76,  110 => 74,  109 => 72,  105 => 70,  103 => 65,  102 => 63,  101 => 61,  97 => 59,  95 => 58,  94 => 56,  93 => 54,  89 => 52,  87 => 51,  86 => 49,  85 => 47,  77 => 41,  74 => 40,  71 => 39,  68 => 38,  65 => 37,  62 => 36,  60 => 35,  55 => 33,  52 => 32,  50 => 31,  47 => 30,  45 => 29,  42 => 28,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "@glpiinventory/forms/config/main.html.twig", "/var/www/html/glpi/marketplace/glpiinventory/templates/forms/config/main.html.twig");
    }
}
