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

/* pages/admin/profile/form.html.twig */
class __TwigTemplate_06075ba096b3fb09ac63ee23d096ed9e extends Template
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
        // line 33
        $this->parent = $this->load("generic_show_form.html.twig", 33);
        yield from $this->parent->unwrap()->yield($context, array_merge($this->blocks, $blocks));
    }

    // line 36
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_form_fields(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 37
        yield "    ";
        yield $macros["fields"]->getTemplateForMacro("macro_textField", $context, 37, $this->getSourceContext())->macro_textField(...["name", (($_v0 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 37)) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0["name"] ?? null) : null), __("Name")]);
        yield "
    ";
        // line 38
        yield $macros["fields"]->getTemplateForMacro("macro_textareaField", $context, 38, $this->getSourceContext())->macro_textareaField(...["comment", (($_v1 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 38)) && is_array($_v1) || $_v1 instanceof ArrayAccess ? ($_v1["comment"] ?? null) : null), _n("Comment", "Comments", Session::getPluralNumber())]);
        yield "
    ";
        // line 39
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
        yield "        ";
        yield $macros["fields"]->getTemplateForMacro("macro_checkboxField", $context, 40, $this->getSourceContext())->macro_checkboxField(...["is_default", (($_v2 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 40)) && is_array($_v2) || $_v2 instanceof ArrayAccess ? ($_v2["is_default"] ?? null) : null), __("Default profile")]);
        yield "
        ";
        // line 41
        yield $macros["fields"]->getTemplateForMacro("macro_nullField", $context, 41, $this->getSourceContext())->macro_nullField(...[]);
        yield "
        ";
        // line 42
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownArrayField", $context, 42, $this->getSourceContext())->macro_dropdownArrayField(...["interface", (($_v3 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 42)) && is_array($_v3) || $_v3 instanceof ArrayAccess ? ($_v3["interface"] ?? null) : null), ($context["interfaces"] ?? null), __("Profile\x27s interface"), ["readonly" => (        // line 43
($context["last_super_admin_profile"] ?? null) && ((($_v4 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 43)) && is_array($_v4) || $_v4 instanceof ArrayAccess ? ($_v4["interface"] ?? null) : null) == "central"))]]);
        // line 44
        yield "
        ";
        // line 45
        yield $macros["fields"]->getTemplateForMacro("macro_nullField", $context, 45, $this->getSourceContext())->macro_nullField(...[]);
        yield "
        ";
        // line 46
        yield $macros["fields"]->getTemplateForMacro("macro_checkboxField", $context, 46, $this->getSourceContext())->macro_checkboxField(...["_password_update", (($_v5 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 46)) && is_array($_v5) || $_v5 instanceof ArrayAccess ? ($_v5["password_update"] ?? null) : null), __("Update own password")]);
        yield "
        ";
        // line 47
        yield $macros["fields"]->getTemplateForMacro("macro_nullField", $context, 47, $this->getSourceContext())->macro_nullField(...[]);
        yield "
        ";
        // line 48
        yield $macros["fields"]->getTemplateForMacro("macro_checkboxField", $context, 48, $this->getSourceContext())->macro_checkboxField(...["create_ticket_on_login", (($_v6 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 48)) && is_array($_v6) || $_v6 instanceof ArrayAccess ? ($_v6["create_ticket_on_login"] ?? null) : null), __("Ticket creation form on login")]);
        yield "
    ";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "pages/admin/profile/form.html.twig";
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
        return array (  109 => 48,  105 => 47,  101 => 46,  97 => 45,  94 => 44,  92 => 43,  91 => 42,  87 => 41,  82 => 40,  71 => 39,  67 => 38,  62 => 37,  55 => 36,  50 => 33,  48 => 34,  41 => 33,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "pages/admin/profile/form.html.twig", "/var/www/html/glpi/templates/pages/admin/profile/form.html.twig");
    }
}
