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

/* pages/admin/entity/advanced.html.twig */
class __TwigTemplate_06e29b475b511543461e6f9ce32d1927 extends Template
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
        yield $macros["fields"]->getTemplateForMacro("macro_largeTitle", $context, 40, $this->getSourceContext())->macro_largeTitle(...[__("Values for the generic rules for assignment to entities"), $this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeIcon("Entity"), false, __("These parameters are used as actions in generic rules for assignment to entities")]);
        // line 45
        yield "

      ";
        // line 47
        yield $macros["fields"]->getTemplateForMacro("macro_textField", $context, 47, $this->getSourceContext())->macro_textField(...["tag", (($_v0 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 47)) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0["tag"] ?? null) : null), __("Information in inventory tool (TAG) representing the entity"), ["full_width" => true]]);
        // line 49
        yield "
      ";
        // line 50
        if ((($tmp = ($context["can_use_ldap"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 51
            yield "         ";
            yield $macros["fields"]->getTemplateForMacro("macro_textField", $context, 51, $this->getSourceContext())->macro_textField(...["ldap_dn", (($_v1 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 51)) && is_array($_v1) || $_v1 instanceof ArrayAccess ? ($_v1["ldap_dn"] ?? null) : null), __("LDAP directory information attribute representing the entity"), ["full_width" => true]]);
            // line 53
            yield "
      ";
        }
        // line 55
        yield "      ";
        yield $macros["fields"]->getTemplateForMacro("macro_textField", $context, 55, $this->getSourceContext())->macro_textField(...["mail_domain", (($_v2 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 55)) && is_array($_v2) || $_v2 instanceof ArrayAccess ? ($_v2["mail_domain"] ?? null) : null), __("Mail domain surrogates entity"), ["full_width" => true]]);
        // line 57
        yield "

      ";
        // line 59
        if ((($tmp = ($context["can_use_ldap"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 60
            yield "         ";
            yield $macros["fields"]->getTemplateForMacro("macro_largeTitle", $context, 60, $this->getSourceContext())->macro_largeTitle(...[__("Values used in the interface to search users from a LDAP directory"), $this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeIcon("AuthLDAP")]);
            yield "
         ";
            // line 61
            yield $macros["fields"]->getTemplateForMacro("macro_dropdownField", $context, 61, $this->getSourceContext())->macro_dropdownField(...["AuthLDAP", "authldaps_id", (($_v3 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 61)) && is_array($_v3) || $_v3 instanceof ArrayAccess ? ($_v3["authldaps_id"] ?? null) : null), $this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("AuthLDAP"), ["full_width" => true, "emptylabel" => __("Default server"), "condition" => ["is_active" => 1]]]);
            // line 67
            yield "
         ";
            // line 68
            yield $macros["fields"]->getTemplateForMacro("macro_nullField", $context, 68, $this->getSourceContext())->macro_nullField(...[]);
            yield "
         ";
            // line 69
            yield $macros["fields"]->getTemplateForMacro("macro_textField", $context, 69, $this->getSourceContext())->macro_textField(...["entity_ldapfilter", (($_v4 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 69)) && is_array($_v4) || $_v4 instanceof ArrayAccess ? ($_v4["entity_ldapfilter"] ?? null) : null), __("LDAP filter associated to the entity (if necessary)"), ["full_width" => true]]);
            // line 71
            yield "
      ";
        }
        // line 73
        yield "   ";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "pages/admin/entity/advanced.html.twig";
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
        return array (  126 => 73,  122 => 71,  120 => 69,  116 => 68,  113 => 67,  111 => 61,  106 => 60,  104 => 59,  100 => 57,  97 => 55,  93 => 53,  90 => 51,  88 => 50,  85 => 49,  83 => 47,  79 => 45,  76 => 40,  64 => 39,  57 => 38,  52 => 33,  50 => 35,  48 => 34,  41 => 33,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "pages/admin/entity/advanced.html.twig", "/var/www/html/glpi/templates/pages/admin/entity/advanced.html.twig");
    }
}
