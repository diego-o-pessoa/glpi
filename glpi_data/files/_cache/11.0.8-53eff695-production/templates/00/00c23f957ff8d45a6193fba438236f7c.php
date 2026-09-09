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

/* pages/admin/entity/address.html.twig */
class __TwigTemplate_97191454cc14e77d32831bfdee514980 extends Template
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

        $this->blocks = [
            'form_fields' => [$this, 'block_form_fields'],
            'more_fields' => [$this, 'block_more_fields'],
        ];
    }

    protected function doGetParent(array $context): bool|string|Template|TemplateWrapper
    {
        // line 33
        return "generic_show_form.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 34
        $macros["fields"] = $this->macros["fields"] = $this->load("components/form/fields_macros.html.twig", 34)->unwrap();
        // line 35
        $context["params"] = (((array_key_exists("params", $context) &&  !(null === $context["params"]))) ? ($context["params"]) : ([]));
        // line 33
        $this->parent = $this->load("generic_show_form.html.twig", 33);
        yield from $this->parent->unwrap()->yield($context, array_merge($this->blocks, $blocks));
    }

    // line 38
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_form_fields(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 39
        yield "   ";
        yield from $this->unwrap()->yieldBlock('more_fields', $context, $blocks);
        yield from [];
    }

    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_more_fields(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 40
        yield "      ";
        yield $macros["fields"]->getTemplateForMacro("macro_autoNameField", $context, 40, $this->getSourceContext())->macro_autoNameField(...["phonenumber",         // line 42
($context["item"] ?? null), $this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("Phone")]);
        // line 44
        yield "

      ";
        // line 46
        yield $macros["fields"]->getTemplateForMacro("macro_autoNameField", $context, 46, $this->getSourceContext())->macro_autoNameField(...["registration_number",         // line 48
($context["item"] ?? null), _x("infocom", "Administrative number")]);
        // line 50
        yield "

      ";
        // line 52
        yield $macros["fields"]->getTemplateForMacro("macro_autoNameField", $context, 52, $this->getSourceContext())->macro_autoNameField(...["fax",         // line 54
($context["item"] ?? null), __("Fax")]);
        // line 56
        yield "
      ";
        // line 57
        yield $macros["fields"]->getTemplateForMacro("macro_nullField", $context, 57, $this->getSourceContext())->macro_nullField(...[]);
        yield "

      ";
        // line 59
        yield $macros["fields"]->getTemplateForMacro("macro_autoNameField", $context, 59, $this->getSourceContext())->macro_autoNameField(...["website",         // line 61
($context["item"] ?? null), __("Website")]);
        // line 63
        yield "
      ";
        // line 64
        yield $macros["fields"]->getTemplateForMacro("macro_nullField", $context, 64, $this->getSourceContext())->macro_nullField(...[]);
        yield "

      ";
        // line 66
        yield $macros["fields"]->getTemplateForMacro("macro_autoNameField", $context, 66, $this->getSourceContext())->macro_autoNameField(...["email",         // line 68
($context["item"] ?? null), _n("Email", "Emails", 1)]);
        // line 70
        yield "
      ";
        // line 71
        yield $macros["fields"]->getTemplateForMacro("macro_nullField", $context, 71, $this->getSourceContext())->macro_nullField(...[]);
        yield "

      ";
        // line 73
        yield $macros["fields"]->getTemplateForMacro("macro_autoNameField", $context, 73, $this->getSourceContext())->macro_autoNameField(...["postcode",         // line 75
($context["item"] ?? null), __("Postal code")]);
        // line 77
        yield "

      ";
        // line 79
        yield $macros["fields"]->getTemplateForMacro("macro_textareaField", $context, 79, $this->getSourceContext())->macro_textareaField(...["address", (($_v0 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 79)) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0["address"] ?? null) : null), __("Address")]);
        yield "

      ";
        // line 81
        yield $macros["fields"]->getTemplateForMacro("macro_autoNameField", $context, 81, $this->getSourceContext())->macro_autoNameField(...["town",         // line 83
($context["item"] ?? null), __("City")]);
        // line 85
        yield "
      ";
        // line 86
        yield $macros["fields"]->getTemplateForMacro("macro_nullField", $context, 86, $this->getSourceContext())->macro_nullField(...[]);
        yield "

      ";
        // line 88
        yield $macros["fields"]->getTemplateForMacro("macro_autoNameField", $context, 88, $this->getSourceContext())->macro_autoNameField(...["state",         // line 90
($context["item"] ?? null), _x("location", "State")]);
        // line 92
        yield "
      ";
        // line 93
        yield $macros["fields"]->getTemplateForMacro("macro_nullField", $context, 93, $this->getSourceContext())->macro_nullField(...[]);
        yield "

      ";
        // line 95
        yield $macros["fields"]->getTemplateForMacro("macro_autoNameField", $context, 95, $this->getSourceContext())->macro_autoNameField(...["country",         // line 97
($context["item"] ?? null), __("Country")]);
        // line 99
        yield "
      ";
        // line 100
        yield $macros["fields"]->getTemplateForMacro("macro_nullField", $context, 100, $this->getSourceContext())->macro_nullField(...[]);
        yield "

      ";
        // line 102
        $context["setlocation"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 103
            yield "         ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "displaySpecificTypeField", [(($_v1 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 103)) && is_array($_v1) || $_v1 instanceof ArrayAccess ? ($_v1["id"] ?? null) : null), ["name" => "setlocation", "type" => "setlocation", "label" => "", "list" => false]], "method", false, false, false, 103), "html", null, true);
            // line 108
            yield "
      ";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
        // line 110
        yield "      ";
        yield $macros["fields"]->getTemplateForMacro("macro_htmlField", $context, 110, $this->getSourceContext())->macro_htmlField(...["setlocation", ($context["setlocation"] ?? null), __("Location on map")]);
        yield "
      ";
        // line 111
        yield $macros["fields"]->getTemplateForMacro("macro_nullField", $context, 111, $this->getSourceContext())->macro_nullField(...[]);
        yield "

      ";
        // line 113
        yield $macros["fields"]->getTemplateForMacro("macro_textField", $context, 113, $this->getSourceContext())->macro_textField(...["longitude", (($_v2 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 113)) && is_array($_v2) || $_v2 instanceof ArrayAccess ? ($_v2["longitude"] ?? null) : null), _x("location", "Longitude")]);
        yield "
      ";
        // line 114
        yield $macros["fields"]->getTemplateForMacro("macro_nullField", $context, 114, $this->getSourceContext())->macro_nullField(...[]);
        yield "
      ";
        // line 115
        yield $macros["fields"]->getTemplateForMacro("macro_textField", $context, 115, $this->getSourceContext())->macro_textField(...["latitude", (($_v3 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 115)) && is_array($_v3) || $_v3 instanceof ArrayAccess ? ($_v3["latitude"] ?? null) : null), _x("location", "Latitude")]);
        yield "
      ";
        // line 116
        yield $macros["fields"]->getTemplateForMacro("macro_nullField", $context, 116, $this->getSourceContext())->macro_nullField(...[]);
        yield "
      ";
        // line 117
        yield $macros["fields"]->getTemplateForMacro("macro_textField", $context, 117, $this->getSourceContext())->macro_textField(...["altitude", (($_v4 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 117)) && is_array($_v4) || $_v4 instanceof ArrayAccess ? ($_v4["altitude"] ?? null) : null), _x("location", "Altitude")]);
        yield "
      ";
        // line 118
        yield $macros["fields"]->getTemplateForMacro("macro_nullField", $context, 118, $this->getSourceContext())->macro_nullField(...[]);
        yield "
   ";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "pages/admin/entity/address.html.twig";
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
        return array (  209 => 118,  205 => 117,  201 => 116,  197 => 115,  193 => 114,  189 => 113,  184 => 111,  179 => 110,  174 => 108,  171 => 103,  169 => 102,  164 => 100,  161 => 99,  159 => 97,  158 => 95,  153 => 93,  150 => 92,  148 => 90,  147 => 88,  142 => 86,  139 => 85,  137 => 83,  136 => 81,  131 => 79,  127 => 77,  125 => 75,  124 => 73,  119 => 71,  116 => 70,  114 => 68,  113 => 66,  108 => 64,  105 => 63,  103 => 61,  102 => 59,  97 => 57,  94 => 56,  92 => 54,  91 => 52,  87 => 50,  85 => 48,  84 => 46,  80 => 44,  78 => 42,  76 => 40,  64 => 39,  57 => 38,  52 => 33,  50 => 35,  48 => 34,  41 => 33,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "pages/admin/entity/address.html.twig", "/var/www/html/glpi/templates/pages/admin/entity/address.html.twig");
    }
}
