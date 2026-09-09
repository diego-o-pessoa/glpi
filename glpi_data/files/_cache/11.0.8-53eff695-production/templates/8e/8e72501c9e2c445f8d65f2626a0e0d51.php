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

/* @glpiinventory/forms/config/deploy.html.twig */
class __TwigTemplate_e26004ce40676e7a2d7311790786ba45 extends Template
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
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownYesNo", $context, 47, $this->getSourceContext())->macro_dropdownYesNo(...["server_as_mirror", CoreExtension::getAttribute($this->env, $this->source,         // line 49
($context["item"] ?? null), "isFieldActive", ["server_as_mirror"], "method", false, false, false, 49), __("Use this GLPI server as a mirror server", "glpiinventory"),         // line 51
($context["field_options"] ?? null)]);
        // line 52
        yield "

                        ";
        // line 54
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownArrayField", $context, 54, $this->getSourceContext())->macro_dropdownArrayField(...["mirror_match", CoreExtension::getAttribute($this->env, $this->source,         // line 56
($context["item"] ?? null), "getValue", ["mirror_match"], "method", false, false, false, 56),         // line 57
($context["mirror_values"] ?? null), __("Match mirrors to agents", "glpiinventory"),         // line 59
($context["field_options"] ?? null)]);
        // line 60
        yield "

                        ";
        // line 62
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownNumberField", $context, 62, $this->getSourceContext())->macro_dropdownNumberField(...["clean_on_demand_tasks", CoreExtension::getAttribute($this->env, $this->source,         // line 64
($context["item"] ?? null), "getValue", ["clean_on_demand_tasks"], "method", false, false, false, 64), __("Delete successful on demand tasks after (in days)", "glpiinventory"), Twig\Extension\CoreExtension::merge(        // line 66
($context["field_options"] ?? null), ["min" => 1, "max" => 1000, "toadd" => ["-1" => __("Never")]])]);
        // line 71
        yield "
                    </div> ";
        // line 73
        yield "                </div> ";
        // line 74
        yield "            </div> ";
        // line 75
        yield "        </div>
    </div> ";
        // line 77
        yield "
    ";
        // line 78
        if (( !array_key_exists("no_form_buttons", $context) || (($context["no_form_buttons"] ?? null) == false))) {
            // line 79
            yield "        ";
            yield Twig\Extension\CoreExtension::include($this->env, $context, "components/form/buttons.html.twig");
            yield "
    ";
        }
        // line 81
        yield "
</div> ";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "@glpiinventory/forms/config/deploy.html.twig";
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
        return array (  127 => 81,  121 => 79,  119 => 78,  116 => 77,  113 => 75,  111 => 74,  109 => 73,  106 => 71,  104 => 66,  103 => 64,  102 => 62,  98 => 60,  96 => 59,  95 => 57,  94 => 56,  93 => 54,  89 => 52,  87 => 51,  86 => 49,  85 => 47,  77 => 41,  74 => 40,  71 => 39,  68 => 38,  65 => 37,  62 => 36,  60 => 35,  55 => 33,  52 => 32,  50 => 31,  47 => 30,  45 => 29,  42 => 28,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "@glpiinventory/forms/config/deploy.html.twig", "/var/www/html/glpi/marketplace/glpiinventory/templates/forms/config/deploy.html.twig");
    }
}
