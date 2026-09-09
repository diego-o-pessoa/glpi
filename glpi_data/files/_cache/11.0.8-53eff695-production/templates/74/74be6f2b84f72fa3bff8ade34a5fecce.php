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

/* @glpiinventory/forms/config/netinv.html.twig */
class __TwigTemplate_a1e48b3178ae6b3ceb7696f34453f724 extends Template
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
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownNumberField", $context, 47, $this->getSourceContext())->macro_dropdownNumberField(...["threads_networkdiscovery", CoreExtension::getAttribute($this->env, $this->source,         // line 49
($context["item"] ?? null), "getValue", ["threads_networkdiscovery"], "method", false, false, false, 49), (((__("Threads number", "glpiinventory") . " (") . __("Network discovery", "glpiinventory")) . ")"), Twig\Extension\CoreExtension::merge(        // line 51
($context["field_options"] ?? null), ["min" => 1, "max" => 400])]);
        // line 55
        yield "

                        ";
        // line 57
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownNumberField", $context, 57, $this->getSourceContext())->macro_dropdownNumberField(...["threads_networkinventory", CoreExtension::getAttribute($this->env, $this->source,         // line 59
($context["item"] ?? null), "getValue", ["threads_networkinventory"], "method", false, false, false, 59), (((__("Threads number", "glpiinventory") . " (") . __("Network inventory (SNMP)", "glpiinventory")) . ")"), Twig\Extension\CoreExtension::merge(        // line 61
($context["field_options"] ?? null), ["min" => 1, "max" => 400])]);
        // line 65
        yield "

                        ";
        // line 67
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownNumberField", $context, 67, $this->getSourceContext())->macro_dropdownNumberField(...["timeout_networkdiscovery", CoreExtension::getAttribute($this->env, $this->source,         // line 69
($context["item"] ?? null), "getValue", ["timeout_networkdiscovery"], "method", false, false, false, 69), (((__("SNMP timeout", "glpiinventory") . " (") . __("Network discovery", "glpiinventory")) . ")"), Twig\Extension\CoreExtension::merge(        // line 71
($context["field_options"] ?? null), ["min" => 1, "max" => 400])]);
        // line 75
        yield "

                        ";
        // line 77
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownNumberField", $context, 77, $this->getSourceContext())->macro_dropdownNumberField(...["timeout_networkinventory", CoreExtension::getAttribute($this->env, $this->source,         // line 79
($context["item"] ?? null), "getValue", ["timeout_networkinventory"], "method", false, false, false, 79), (((__("SNMP timeout", "glpiinventory") . " (") . __("Network inventory (SNMP)", "glpiinventory")) . ")"), Twig\Extension\CoreExtension::merge(        // line 81
($context["field_options"] ?? null), ["min" => 1, "max" => 400])]);
        // line 85
        yield "
                    </div> ";
        // line 87
        yield "                </div> ";
        // line 88
        yield "            </div> ";
        // line 89
        yield "        </div>
    </div> ";
        // line 91
        yield "
    ";
        // line 92
        if (( !array_key_exists("no_form_buttons", $context) || (($context["no_form_buttons"] ?? null) == false))) {
            // line 93
            yield "        ";
            yield Twig\Extension\CoreExtension::include($this->env, $context, "components/form/buttons.html.twig");
            yield "
    ";
        }
        // line 95
        yield "
</div> ";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "@glpiinventory/forms/config/netinv.html.twig";
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
        return array (  134 => 95,  128 => 93,  126 => 92,  123 => 91,  120 => 89,  118 => 88,  116 => 87,  113 => 85,  111 => 81,  110 => 79,  109 => 77,  105 => 75,  103 => 71,  102 => 69,  101 => 67,  97 => 65,  95 => 61,  94 => 59,  93 => 57,  89 => 55,  87 => 51,  86 => 49,  85 => 47,  77 => 41,  74 => 40,  71 => 39,  68 => 38,  65 => 37,  62 => 36,  60 => 35,  55 => 33,  52 => 32,  50 => 31,  47 => 30,  45 => 29,  42 => 28,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "@glpiinventory/forms/config/netinv.html.twig", "/var/www/html/glpi/marketplace/glpiinventory/templates/forms/config/netinv.html.twig");
    }
}
