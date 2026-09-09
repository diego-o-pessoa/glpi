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

/* layout/parts/context_links.html.twig */
class __TwigTemplate_28ec0a78f57980eea90c4276d3761ac7 extends Template
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
        $context["links"] = (((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["menu"] ?? null), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 33, $this->source); })()), [], "array", false, true, false, 33), "content", [], "array", false, true, false, 33), (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 33, $this->source); })()), [], "array", false, true, false, 33), "options", [], "array", false, true, false, 33), (isset($context["option"]) || array_key_exists("option", $context) ? $context["option"] : (function () { throw new RuntimeError('Variable "option" does not exist.', 33, $this->source); })()), [], "array", false, true, false, 33), "links", [], "array", true, true, false, 33) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["menu"]) || array_key_exists("menu", $context) ? $context["menu"] : (function () { throw new RuntimeError('Variable "menu" does not exist.', 33, $this->source); })()), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 33, $this->source); })()), [], "array", false, false, false, 33), "content", [], "array", false, false, false, 33), (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 33, $this->source); })()), [], "array", false, false, false, 33), "options", [], "array", false, false, false, 33), (isset($context["option"]) || array_key_exists("option", $context) ? $context["option"] : (function () { throw new RuntimeError('Variable "option" does not exist.', 33, $this->source); })()), [], "array", false, false, false, 33), "links", [], "array", false, false, false, 33)))) ? (CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["menu"]) || array_key_exists("menu", $context) ? $context["menu"] : (function () { throw new RuntimeError('Variable "menu" does not exist.', 33, $this->source); })()), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 33, $this->source); })()), [], "array", false, false, false, 33), "content", [], "array", false, false, false, 33), (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 33, $this->source); })()), [], "array", false, false, false, 33), "options", [], "array", false, false, false, 33), (isset($context["option"]) || array_key_exists("option", $context) ? $context["option"] : (function () { throw new RuntimeError('Variable "option" does not exist.', 33, $this->source); })()), [], "array", false, false, false, 33), "links", [], "array", false, false, false, 33)) : ((((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["menu"] ?? null), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 33, $this->source); })()), [], "array", false, true, false, 33), "content", [], "array", false, true, false, 33), (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 33, $this->source); })()), [], "array", false, true, false, 33), "links", [], "array", true, true, false, 33) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["menu"]) || array_key_exists("menu", $context) ? $context["menu"] : (function () { throw new RuntimeError('Variable "menu" does not exist.', 33, $this->source); })()), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 33, $this->source); })()), [], "array", false, false, false, 33), "content", [], "array", false, false, false, 33), (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 33, $this->source); })()), [], "array", false, false, false, 33), "links", [], "array", false, false, false, 33)))) ? (CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["menu"]) || array_key_exists("menu", $context) ? $context["menu"] : (function () { throw new RuntimeError('Variable "menu" does not exist.', 33, $this->source); })()), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 33, $this->source); })()), [], "array", false, false, false, 33), "content", [], "array", false, false, false, 33), (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 33, $this->source); })()), [], "array", false, false, false, 33), "links", [], "array", false, false, false, 33)) : (""))));
        // line 34
        $context["lists_itemtype"] = (((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["menu"] ?? null), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 34, $this->source); })()), [], "array", false, true, false, 34), "content", [], "array", false, true, false, 34), (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 34, $this->source); })()), [], "array", false, true, false, 34), "options", [], "array", false, true, false, 34), (isset($context["option"]) || array_key_exists("option", $context) ? $context["option"] : (function () { throw new RuntimeError('Variable "option" does not exist.', 34, $this->source); })()), [], "array", false, true, false, 34), "lists_itemtype", [], "array", true, true, false, 34) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["menu"]) || array_key_exists("menu", $context) ? $context["menu"] : (function () { throw new RuntimeError('Variable "menu" does not exist.', 34, $this->source); })()), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 34, $this->source); })()), [], "array", false, false, false, 34), "content", [], "array", false, false, false, 34), (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 34, $this->source); })()), [], "array", false, false, false, 34), "options", [], "array", false, false, false, 34), (isset($context["option"]) || array_key_exists("option", $context) ? $context["option"] : (function () { throw new RuntimeError('Variable "option" does not exist.', 34, $this->source); })()), [], "array", false, false, false, 34), "lists_itemtype", [], "array", false, false, false, 34)))) ? (CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["menu"]) || array_key_exists("menu", $context) ? $context["menu"] : (function () { throw new RuntimeError('Variable "menu" does not exist.', 34, $this->source); })()), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 34, $this->source); })()), [], "array", false, false, false, 34), "content", [], "array", false, false, false, 34), (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 34, $this->source); })()), [], "array", false, false, false, 34), "options", [], "array", false, false, false, 34), (isset($context["option"]) || array_key_exists("option", $context) ? $context["option"] : (function () { throw new RuntimeError('Variable "option" does not exist.', 34, $this->source); })()), [], "array", false, false, false, 34), "lists_itemtype", [], "array", false, false, false, 34)) : ((((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["menu"] ?? null), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 34, $this->source); })()), [], "array", false, true, false, 34), "content", [], "array", false, true, false, 34), (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 34, $this->source); })()), [], "array", false, true, false, 34), "lists_itemtype", [], "array", true, true, false, 34) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["menu"]) || array_key_exists("menu", $context) ? $context["menu"] : (function () { throw new RuntimeError('Variable "menu" does not exist.', 34, $this->source); })()), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 34, $this->source); })()), [], "array", false, false, false, 34), "content", [], "array", false, false, false, 34), (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 34, $this->source); })()), [], "array", false, false, false, 34), "lists_itemtype", [], "array", false, false, false, 34)))) ? (CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["menu"]) || array_key_exists("menu", $context) ? $context["menu"] : (function () { throw new RuntimeError('Variable "menu" does not exist.', 34, $this->source); })()), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 34, $this->source); })()), [], "array", false, false, false, 34), "content", [], "array", false, false, false, 34), (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 34, $this->source); })()), [], "array", false, false, false, 34), "lists_itemtype", [], "array", false, false, false, 34)) : (""))));
        // line 35
        if (Twig\Extension\CoreExtension::testEmpty((isset($context["lists_itemtype"]) || array_key_exists("lists_itemtype", $context) ? $context["lists_itemtype"] : (function () { throw new RuntimeError('Variable "lists_itemtype" does not exist.', 35, $this->source); })()))) {
            // line 36
            yield "    ";
            $context["lists_itemtype"] = (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 36, $this->source); })());
        }
        // line 38
        yield "
";
        // line 39
        $context["display_divider"] = (CoreExtension::getAttribute($this->env, $this->source, ($context["links"] ?? null), "add", [], "array", true, true, false, 39) || (Twig\Extension\CoreExtension::length($this->env->getCharset(), (isset($context["links"]) || array_key_exists("links", $context) ? $context["links"] : (function () { throw new RuntimeError('Variable "links" does not exist.', 39, $this->source); })())) > 0));
        // line 40
        yield "
";
        // line 41
        if ((($tmp = (isset($context["display_divider"]) || array_key_exists("display_divider", $context) ? $context["display_divider"] : (function () { throw new RuntimeError('Variable "display_divider" does not exist.', 41, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 42
            yield "   ";
            // line 43
            yield "   <ul class=\"nav navbar-nav border-start border-left ps-1 ps-sm-2 flex-row\">
";
        }
        // line 45
        yield "
";
        // line 46
        if (CoreExtension::getAttribute($this->env, $this->source, ($context["links"] ?? null), "add", [], "array", true, true, false, 46)) {
            // line 47
            yield "<li class=\"nav-item\">
   <a href=\"";
            // line 48
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path(CoreExtension::getAttribute($this->env, $this->source, (isset($context["links"]) || array_key_exists("links", $context) ? $context["links"] : (function () { throw new RuntimeError('Variable "links" does not exist.', 48, $this->source); })()), "add", [], "array", false, false, false, 48)), "html", null, true);
            yield "\" class=\"btn btn-sm btn-primary me-1 pe-2\" data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\" title=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Add"), "html", null, true);
            yield "\">
      <i class=\"ti ti-plus\"></i>
      <span class=\"d-none d-xxl-block\">";
            // line 50
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Add"), "html", null, true);
            yield "</span>
   </a>
</li>
";
        }
        // line 54
        yield "
";
        // line 55
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable((isset($context["links"]) || array_key_exists("links", $context) ? $context["links"] : (function () { throw new RuntimeError('Variable "links" does not exist.', 55, $this->source); })()));
        foreach ($context['_seq'] as $context["type"] => $context["link"]) {
            // line 56
            yield "   ";
            if (((($context["type"] == "add") || ($context["type"] == "search")) || ($context["type"] == "lists"))) {
                // line 57
                yield "   ";
            } elseif (($context["type"] == "template")) {
                // line 58
                yield "      <li class=\"nav-item\">
         <a href=\"";
                // line 59
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path($context["link"]), "html", null, true);
                yield "\" class=\"btn btn-sm btn-ghost-secondary me-1 pe-2\" data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\" title=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Manage templates..."), "html", null, true);
                yield "\">
            <i class=\"ti ti-template\"></i>
            <span class=\"d-none d-xxl-block\">";
                // line 61
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Templates"), "html", null, true);
                yield "</span>
         </a>
      </li>
   ";
            } elseif ((            // line 64
$context["type"] == "showall")) {
                // line 65
                yield "      <li class=\"nav-item\">
         <a href=\"";
                // line 66
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path($context["link"]), "html", null, true);
                yield "\" class=\"btn btn-sm btn-ghost-secondary me-1 pe-2\" data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\" title=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Show all"), "html", null, true);
                yield "\">
            <i class=\"ti ti-eye-check\"></i>
            <span class=\"d-none d-xxl-block\">";
                // line 68
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Show all"), "html", null, true);
                yield "</span>
         </a>
      </li>
   ";
            } elseif ((            // line 71
$context["type"] == "summary")) {
                // line 72
                yield "      <li class=\"nav-item\">
         <a href=\"";
                // line 73
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path($context["link"]), "html", null, true);
                yield "\" class=\"btn btn-sm btn-ghost-secondary me-1 pe-2\" data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\" title=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Summary"), "html", null, true);
                yield "\">
            <i class=\"ti ti-notes\"></i>
            <span class=\"d-none d-xxl-block\">";
                // line 75
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Summary"), "html", null, true);
                yield "</span>
         </a>
      </li>
   ";
            } elseif ((            // line 78
$context["type"] == "summary_kanban")) {
                // line 79
                yield "      <li class=\"nav-item\">
         <a href=\"";
                // line 80
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path($context["link"]), "html", null, true);
                yield "\" class=\"btn btn-sm btn-ghost-secondary me-1 pe-2\" data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\" title=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Global Kanban"), "html", null, true);
                yield "\">
            <i class=\"ti ti-layout-columns\"></i>
            <span class=\"d-none d-xxl-block\">";
                // line 82
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Global Kanban"), "html", null, true);
                yield "</span>
         </a>
      </li>
   ";
            } elseif ((            // line 85
$context["type"] == "transfer_list")) {
                // line 86
                yield "      <li class=\"nav-item\">
         <a href=\"";
                // line 87
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path($context["link"]), "html", null, true);
                yield "\" class=\"btn btn-sm btn-ghost-secondary me-1 pe-2\" data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\" title=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Transfer list"), "html", null, true);
                yield "\">
            <i class=\"ti ti-list-check\"></i>
            <span class=\"d-none d-xxl-block\">";
                // line 89
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Transfer list"), "html", null, true);
                yield "</span>
         </a>
      </li>
   ";
            } elseif ((            // line 92
$context["type"] == "config")) {
                // line 93
                yield "      <li class=\"nav-item\">
         <a href=\"";
                // line 94
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path($context["link"]), "html", null, true);
                yield "\" class=\"btn btn-sm btn-ghost-secondary me-1 pe-2\" data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\" title=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Setup"), "html", null, true);
                yield "\">
            <i class=\"ti ti-tool\"></i>
            <span class=\"d-none d-xxl-block\">";
                // line 96
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Setup"), "html", null, true);
                yield "</span>
         </a>
      </li>
   ";
            } elseif ((            // line 99
$context["type"] == "view_form_categories")) {
                // line 100
                yield "      <li class=\"nav-item\">
         <a href=\"";
                // line 101
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path($context["link"]), "html", null, true);
                yield "\" role=\"button\" class=\"btn btn-sm btn-ghost-secondary me-1 pe-2\" data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\" title=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("Glpi\\Form\\Category", Session::getPluralNumber()), "html", null, true);
                yield "\">
            <i class=\"ti ti-folder\"></i>
            <span class=\"d-none d-xxl-block\">";
                // line 103
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("Glpi\\Form\\Category", Session::getPluralNumber()), "html", null, true);
                yield "</span>
         </a>
      </li>
   ";
            } elseif ((            // line 106
$context["type"] == "import_forms")) {
                // line 107
                yield "      <li class=\"nav-item\">
         <a href=\"";
                // line 108
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path($context["link"]), "html", null, true);
                yield "\" role=\"button\" class=\"btn btn-sm btn-ghost-secondary me-1 pe-2\" data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\" title=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Import forms"), "html", null, true);
                yield "\">
            <i class=\"ti ti-file-arrow-left\"></i>
            <span class=\"d-none d-xxl-block\">";
                // line 110
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Import forms"), "html", null, true);
                yield "</span>
         </a>
      </li>
   ";
            } elseif ((            // line 113
$context["type"] == "Glpi\\Dropdown\\DropdownDefinition")) {
                // line 114
                yield "      <li class=\"nav-item\">
         <a href=\"";
                // line 115
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path($context["link"]), "html", null, true);
                yield "\" role=\"button\" class=\"btn btn-sm btn-ghost-secondary me-1 pe-2\" data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\" title=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("Glpi\\Dropdown\\DropdownDefinition"), "html", null, true);
                yield "\">
            <i class=\"";
                // line 116
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeIcon("Glpi\\Dropdown\\DropdownDefinition"), "html", null, true);
                yield "\"></i>
            <span class=\"d-none d-xxl-block\">";
                // line 117
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("Glpi\\Dropdown\\DropdownDefinition"), "html", null, true);
                yield "</span>
         </a>
      </li>
   ";
            } else {
                // line 121
                yield "      <li class=\"nav-item\">
         <a href=\"";
                // line 122
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path($context["link"]), "html", null, true);
                yield "\" class=\"btn btn-sm btn-ghost-secondary me-1 pe-2\">
            ";
                // line 123
                yield $context["type"];
                yield "
         </a>
      </li>
   ";
            }
            // line 127
            yield "
";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['type'], $context['link'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 129
        yield "
";
        // line 130
        if ((($tmp = (isset($context["display_divider"]) || array_key_exists("display_divider", $context) ? $context["display_divider"] : (function () { throw new RuntimeError('Variable "display_divider" does not exist.', 130, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 131
            yield "   </ul>
";
        }
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "layout/parts/context_links.html.twig";
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
        return array (  296 => 131,  294 => 130,  291 => 129,  284 => 127,  277 => 123,  273 => 122,  270 => 121,  263 => 117,  259 => 116,  253 => 115,  250 => 114,  248 => 113,  242 => 110,  235 => 108,  232 => 107,  230 => 106,  224 => 103,  217 => 101,  214 => 100,  212 => 99,  206 => 96,  199 => 94,  196 => 93,  194 => 92,  188 => 89,  181 => 87,  178 => 86,  176 => 85,  170 => 82,  163 => 80,  160 => 79,  158 => 78,  152 => 75,  145 => 73,  142 => 72,  140 => 71,  134 => 68,  127 => 66,  124 => 65,  122 => 64,  116 => 61,  109 => 59,  106 => 58,  103 => 57,  100 => 56,  96 => 55,  93 => 54,  86 => 50,  79 => 48,  76 => 47,  74 => 46,  71 => 45,  67 => 43,  65 => 42,  63 => 41,  60 => 40,  58 => 39,  55 => 38,  51 => 36,  49 => 35,  47 => 34,  45 => 33,  42 => 32,);
    }

    public function getSourceContext(): Source
    {
        return new Source("{#
 # ---------------------------------------------------------------------
 #
 # GLPI - Gestionnaire Libre de Parc Informatique
 #
 # http://glpi-project.org
 #
 # @copyright 2015-2026 Teclib\x27 and contributors.
 # @licence   https://www.gnu.org/licenses/gpl-3.0.html
 #
 # ---------------------------------------------------------------------
 #
 # LICENSE
 #
 # This file is part of GLPI.
 #
 # This program is free software: you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation, either version 3 of the License, or
 # (at your option) any later version.
 #
 # This program is distributed in the hope that it will be useful,
 # but WITHOUT ANY WARRANTY; without even the implied warranty of
 # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 # GNU General Public License for more details.
 #
 # You should have received a copy of the GNU General Public License
 # along with this program.  If not, see <https://www.gnu.org/licenses/>.
 #
 # ---------------------------------------------------------------------
 #}

{% set links = menu[sector][\x27content\x27][item][\x27options\x27][option][\x27links\x27] ?? menu[sector][\x27content\x27][item][\x27links\x27] ?? \x27\x27 %}
{% set lists_itemtype = menu[sector][\x27content\x27][item][\x27options\x27][option][\x27lists_itemtype\x27] ?? menu[sector][\x27content\x27][item][\x27lists_itemtype\x27] ?? \x27\x27 %}
{% if lists_itemtype is empty %}
    {% set lists_itemtype = item %}
{% endif %}

{% set display_divider = (links[\x27add\x27] is defined or links|length > 0) %}

{% if display_divider %}
   {# @TODO  border-start is not implemented in current boostrap beta (remove border-left when done)  #}
   <ul class=\"nav navbar-nav border-start border-left ps-1 ps-sm-2 flex-row\">
{% endif %}

{% if links[\x27add\x27] is defined %}
<li class=\"nav-item\">
   <a href=\"{{ path(links[\x27add\x27]) }}\" class=\"btn btn-sm btn-primary me-1 pe-2\" data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\" title=\"{{ __(\x27Add\x27) }}\">
      <i class=\"ti ti-plus\"></i>
      <span class=\"d-none d-xxl-block\">{{ __(\x27Add\x27) }}</span>
   </a>
</li>
{% endif %}

{% for type, link in links %}
   {% if type == \x27add\x27 or type == \x27search\x27 or type == \x27lists\x27 %}
   {% elseif type == \x27template\x27 %}
      <li class=\"nav-item\">
         <a href=\"{{ path(link) }}\" class=\"btn btn-sm btn-ghost-secondary me-1 pe-2\" data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\" title=\"{{ __(\x27Manage templates...\x27) }}\">
            <i class=\"ti ti-template\"></i>
            <span class=\"d-none d-xxl-block\">{{ __(\x27Templates\x27) }}</span>
         </a>
      </li>
   {% elseif type == \x27showall\x27 %}
      <li class=\"nav-item\">
         <a href=\"{{ path(link) }}\" class=\"btn btn-sm btn-ghost-secondary me-1 pe-2\" data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\" title=\"{{ __(\x27Show all\x27) }}\">
            <i class=\"ti ti-eye-check\"></i>
            <span class=\"d-none d-xxl-block\">{{ __(\x27Show all\x27) }}</span>
         </a>
      </li>
   {% elseif type == \x27summary\x27 %}
      <li class=\"nav-item\">
         <a href=\"{{ path(link) }}\" class=\"btn btn-sm btn-ghost-secondary me-1 pe-2\" data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\" title=\"{{ __(\x27Summary\x27) }}\">
            <i class=\"ti ti-notes\"></i>
            <span class=\"d-none d-xxl-block\">{{ __(\x27Summary\x27) }}</span>
         </a>
      </li>
   {% elseif type == \x27summary_kanban\x27 %}
      <li class=\"nav-item\">
         <a href=\"{{ path(link) }}\" class=\"btn btn-sm btn-ghost-secondary me-1 pe-2\" data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\" title=\"{{ __(\x27Global Kanban\x27) }}\">
            <i class=\"ti ti-layout-columns\"></i>
            <span class=\"d-none d-xxl-block\">{{ __(\x27Global Kanban\x27) }}</span>
         </a>
      </li>
   {% elseif type == \x27transfer_list\x27 %}
      <li class=\"nav-item\">
         <a href=\"{{ path(link) }}\" class=\"btn btn-sm btn-ghost-secondary me-1 pe-2\" data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\" title=\"{{ __(\x27Transfer list\x27) }}\">
            <i class=\"ti ti-list-check\"></i>
            <span class=\"d-none d-xxl-block\">{{ __(\x27Transfer list\x27) }}</span>
         </a>
      </li>
   {% elseif type == \x27config\x27 %}
      <li class=\"nav-item\">
         <a href=\"{{ path(link) }}\" class=\"btn btn-sm btn-ghost-secondary me-1 pe-2\" data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\" title=\"{{ __(\x27Setup\x27) }}\">
            <i class=\"ti ti-tool\"></i>
            <span class=\"d-none d-xxl-block\">{{ __(\x27Setup\x27) }}</span>
         </a>
      </li>
   {% elseif type == \x27view_form_categories\x27 %}
      <li class=\"nav-item\">
         <a href=\"{{ path(link) }}\" role=\"button\" class=\"btn btn-sm btn-ghost-secondary me-1 pe-2\" data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\" title=\"{{ \x27Glpi\\\\Form\\\\Category\x27|itemtype_name(get_plural_number()) }}\">
            <i class=\"ti ti-folder\"></i>
            <span class=\"d-none d-xxl-block\">{{ \x27Glpi\\\\Form\\\\Category\x27|itemtype_name(get_plural_number()) }}</span>
         </a>
      </li>
   {% elseif type == \x27import_forms\x27 %}
      <li class=\"nav-item\">
         <a href=\"{{ path(link) }}\" role=\"button\" class=\"btn btn-sm btn-ghost-secondary me-1 pe-2\" data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\" title=\"{{ __(\x27Import forms\x27) }}\">
            <i class=\"ti ti-file-arrow-left\"></i>
            <span class=\"d-none d-xxl-block\">{{ __(\x27Import forms\x27) }}</span>
         </a>
      </li>
   {% elseif type == \x27Glpi\\\\Dropdown\\\\DropdownDefinition\x27 %}
      <li class=\"nav-item\">
         <a href=\"{{ path(link) }}\" role=\"button\" class=\"btn btn-sm btn-ghost-secondary me-1 pe-2\" data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\" title=\"{{ \x27Glpi\\\\Dropdown\\\\DropdownDefinition\x27|itemtype_name }}\">
            <i class=\"{{ \x27Glpi\\\\Dropdown\\\\DropdownDefinition\x27|itemtype_icon }}\"></i>
            <span class=\"d-none d-xxl-block\">{{ \x27Glpi\\\\Dropdown\\\\DropdownDefinition\x27|itemtype_name }}</span>
         </a>
      </li>
   {% else %}
      <li class=\"nav-item\">
         <a href=\"{{ path(link) }}\" class=\"btn btn-sm btn-ghost-secondary me-1 pe-2\">
            {{ type|raw }}
         </a>
      </li>
   {% endif %}

{% endfor %}

{% if display_divider %}
   </ul>
{% endif %}
", "layout/parts/context_links.html.twig", "/var/www/html/glpi/templates/layout/parts/context_links.html.twig");
    }
}
