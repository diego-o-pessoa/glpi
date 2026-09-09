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

/* layout/parts/breadcrumbs.html.twig */
class __TwigTemplate_29f0e9db51115c415fccd16e97f9b351 extends Template
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
<nav aria-label=\"";
        // line 33
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Breadcrumbs"), "html", null, true);
        yield "\">
   <ol class=\"breadcrumb breadcrumb-alternate pe-1 pe-sm-3\">
      <li class=\"breadcrumb-item text-truncate\">
      <a href=\"";
        // line 36
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->indexPath(), "html", null, true);
        yield "\" class=\"d-inline-flex align-items-center gap-1\" data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\" title=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Home"), "html", null, true);
        yield "\">
            <i class=\"ti ti-home-2\"></i>
            ";
        // line 38
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Home"), "html", null, true);
        yield "
         </a>
      </li>

      ";
        // line 42
        if (CoreExtension::getAttribute($this->env, $this->source, ($context["menu"] ?? null), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 42, $this->source); })()), [], "array", true, true, false, 42)) {
            // line 43
            yield "      <li class=\"breadcrumb-item text-truncate\">
      <a href=\"";
            // line 44
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path((((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["menu"] ?? null), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 44, $this->source); })()), [], "array", false, true, false, 44), "default", [], "array", true, true, false, 44) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["menu"]) || array_key_exists("menu", $context) ? $context["menu"] : (function () { throw new RuntimeError('Variable "menu" does not exist.', 44, $this->source); })()), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 44, $this->source); })()), [], "array", false, false, false, 44), "default", [], "array", false, false, false, 44)))) ? (CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["menu"]) || array_key_exists("menu", $context) ? $context["menu"] : (function () { throw new RuntimeError('Variable "menu" does not exist.', 44, $this->source); })()), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 44, $this->source); })()), [], "array", false, false, false, 44), "default", [], "array", false, false, false, 44)) : ("/front/central.php"))), "html", null, true);
            yield "\" class=\"d-inline-flex align-items-center gap-1\" data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\" title=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["menu"]) || array_key_exists("menu", $context) ? $context["menu"] : (function () { throw new RuntimeError('Variable "menu" does not exist.', 44, $this->source); })()), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 44, $this->source); })()), [], "array", false, false, false, 44), "title", [], "array", false, false, false, 44), "html", null, true);
            yield "\">
            <i class=\"";
            // line 45
            yield (((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["menu"] ?? null), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 45, $this->source); })()), [], "array", false, true, false, 45), "icon", [], "array", true, true, false, 45) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["menu"]) || array_key_exists("menu", $context) ? $context["menu"] : (function () { throw new RuntimeError('Variable "menu" does not exist.', 45, $this->source); })()), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 45, $this->source); })()), [], "array", false, false, false, 45), "icon", [], "array", false, false, false, 45)))) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["menu"]) || array_key_exists("menu", $context) ? $context["menu"] : (function () { throw new RuntimeError('Variable "menu" does not exist.', 45, $this->source); })()), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 45, $this->source); })()), [], "array", false, false, false, 45), "icon", [], "array", false, false, false, 45), "html", null, true)) : (""));
            yield "\"></i>
            ";
            // line 46
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["menu"]) || array_key_exists("menu", $context) ? $context["menu"] : (function () { throw new RuntimeError('Variable "menu" does not exist.', 46, $this->source); })()), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 46, $this->source); })()), [], "array", false, false, false, 46), "title", [], "array", false, false, false, 46), "html", null, true);
            yield "
         </a>
      </li>
      ";
        }
        // line 50
        yield "
      ";
        // line 51
        $context["with_option"] = false;
        // line 52
        yield "      ";
        if (CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["menu"] ?? null), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 52, $this->source); })()), [], "array", false, true, false, 52), "content", [], "array", false, true, false, 52), (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 52, $this->source); })()), [], "array", true, true, false, 52)) {
            // line 53
            yield "         ";
            if (CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["menu"] ?? null), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 53, $this->source); })()), [], "array", false, true, false, 53), "content", [], "array", false, true, false, 53), (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 53, $this->source); })()), [], "array", false, true, false, 53), "page", [], "array", true, true, false, 53)) {
                // line 54
                yield "         ";
                $context["with_option"] = (( !Twig\Extension\CoreExtension::testEmpty((isset($context["option"]) || array_key_exists("option", $context) ? $context["option"] : (function () { throw new RuntimeError('Variable "option" does not exist.', 54, $this->source); })())) && CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["menu"] ?? null), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 54, $this->source); })()), [], "array", false, true, false, 54), "content", [], "array", false, true, false, 54), (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 54, $this->source); })()), [], "array", false, true, false, 54), "options", [], "array", false, true, false, 54), (isset($context["option"]) || array_key_exists("option", $context) ? $context["option"] : (function () { throw new RuntimeError('Variable "option" does not exist.', 54, $this->source); })()), [], "array", false, true, false, 54), "title", [], "array", true, true, false, 54)) && CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["menu"] ?? null), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 54, $this->source); })()), [], "array", false, true, false, 54), "content", [], "array", false, true, false, 54), (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 54, $this->source); })()), [], "array", false, true, false, 54), "options", [], "array", false, true, false, 54), (isset($context["option"]) || array_key_exists("option", $context) ? $context["option"] : (function () { throw new RuntimeError('Variable "option" does not exist.', 54, $this->source); })()), [], "array", false, true, false, 54), "page", [], "array", true, true, false, 54));
                // line 55
                yield "         <li class=\"breadcrumb-item text-truncate\">
            <a href=\"";
                // line 56
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["menu"]) || array_key_exists("menu", $context) ? $context["menu"] : (function () { throw new RuntimeError('Variable "menu" does not exist.', 56, $this->source); })()), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 56, $this->source); })()), [], "array", false, false, false, 56), "content", [], "array", false, false, false, 56), (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 56, $this->source); })()), [], "array", false, false, false, 56), "page", [], "array", false, false, false, 56)), "html", null, true);
                yield "\"
            class=\"d-inline-flex align-items-center gap-1 ";
                // line 57
                yield (((($tmp = (isset($context["with_option"]) || array_key_exists("with_option", $context) ? $context["with_option"] : (function () { throw new RuntimeError('Variable "with_option" does not exist.', 57, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("") : ("here"));
                yield "\"
               data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\"
               title=\"";
                // line 59
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["menu"]) || array_key_exists("menu", $context) ? $context["menu"] : (function () { throw new RuntimeError('Variable "menu" does not exist.', 59, $this->source); })()), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 59, $this->source); })()), [], "array", false, false, false, 59), "content", [], "array", false, false, false, 59), (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 59, $this->source); })()), [], "array", false, false, false, 59), "title", [], "array", false, false, false, 59), "html", null, true);
                yield "\" >
               <i class=\"";
                // line 60
                yield (((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["menu"] ?? null), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 60, $this->source); })()), [], "array", false, true, false, 60), "content", [], "array", false, true, false, 60), (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 60, $this->source); })()), [], "array", false, true, false, 60), "icon", [], "array", true, true, false, 60) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["menu"]) || array_key_exists("menu", $context) ? $context["menu"] : (function () { throw new RuntimeError('Variable "menu" does not exist.', 60, $this->source); })()), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 60, $this->source); })()), [], "array", false, false, false, 60), "content", [], "array", false, false, false, 60), (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 60, $this->source); })()), [], "array", false, false, false, 60), "icon", [], "array", false, false, false, 60)))) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["menu"]) || array_key_exists("menu", $context) ? $context["menu"] : (function () { throw new RuntimeError('Variable "menu" does not exist.', 60, $this->source); })()), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 60, $this->source); })()), [], "array", false, false, false, 60), "content", [], "array", false, false, false, 60), (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 60, $this->source); })()), [], "array", false, false, false, 60), "icon", [], "array", false, false, false, 60), "html", null, true)) : (""));
                yield "\"></i>
               ";
                // line 61
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["menu"]) || array_key_exists("menu", $context) ? $context["menu"] : (function () { throw new RuntimeError('Variable "menu" does not exist.', 61, $this->source); })()), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 61, $this->source); })()), [], "array", false, false, false, 61), "content", [], "array", false, false, false, 61), (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 61, $this->source); })()), [], "array", false, false, false, 61), "title", [], "array", false, false, false, 61), "html", null, true);
                yield "
            </a>
         </li>
         ";
            }
            // line 65
            yield "
         ";
            // line 66
            if ((($tmp = (isset($context["with_option"]) || array_key_exists("with_option", $context) ? $context["with_option"] : (function () { throw new RuntimeError('Variable "with_option" does not exist.', 66, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 67
                yield "         <li class=\"breadcrumb-item text-truncate\">
            <a href=\"";
                // line 68
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["menu"]) || array_key_exists("menu", $context) ? $context["menu"] : (function () { throw new RuntimeError('Variable "menu" does not exist.', 68, $this->source); })()), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 68, $this->source); })()), [], "array", false, false, false, 68), "content", [], "array", false, false, false, 68), (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 68, $this->source); })()), [], "array", false, false, false, 68), "options", [], "array", false, false, false, 68), (isset($context["option"]) || array_key_exists("option", $context) ? $context["option"] : (function () { throw new RuntimeError('Variable "option" does not exist.', 68, $this->source); })()), [], "array", false, false, false, 68), "page", [], "array", false, false, false, 68)), "html", null, true);
                yield "\"
               class=\"d-inline-flex align-items-center gap-1 here\"
               data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\"
               title=\"";
                // line 71
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["menu"]) || array_key_exists("menu", $context) ? $context["menu"] : (function () { throw new RuntimeError('Variable "menu" does not exist.', 71, $this->source); })()), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 71, $this->source); })()), [], "array", false, false, false, 71), "content", [], "array", false, false, false, 71), (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 71, $this->source); })()), [], "array", false, false, false, 71), "options", [], "array", false, false, false, 71), (isset($context["option"]) || array_key_exists("option", $context) ? $context["option"] : (function () { throw new RuntimeError('Variable "option" does not exist.', 71, $this->source); })()), [], "array", false, false, false, 71), "title", [], "array", false, false, false, 71), "html", null, true);
                yield "\" >
               <i class=\"";
                // line 72
                yield (((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["menu"] ?? null), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 72, $this->source); })()), [], "array", false, true, false, 72), "content", [], "array", false, true, false, 72), (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 72, $this->source); })()), [], "array", false, true, false, 72), "options", [], "array", false, true, false, 72), (isset($context["option"]) || array_key_exists("option", $context) ? $context["option"] : (function () { throw new RuntimeError('Variable "option" does not exist.', 72, $this->source); })()), [], "array", false, true, false, 72), "icon", [], "array", true, true, false, 72) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["menu"]) || array_key_exists("menu", $context) ? $context["menu"] : (function () { throw new RuntimeError('Variable "menu" does not exist.', 72, $this->source); })()), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 72, $this->source); })()), [], "array", false, false, false, 72), "content", [], "array", false, false, false, 72), (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 72, $this->source); })()), [], "array", false, false, false, 72), "options", [], "array", false, false, false, 72), (isset($context["option"]) || array_key_exists("option", $context) ? $context["option"] : (function () { throw new RuntimeError('Variable "option" does not exist.', 72, $this->source); })()), [], "array", false, false, false, 72), "icon", [], "array", false, false, false, 72)))) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["menu"]) || array_key_exists("menu", $context) ? $context["menu"] : (function () { throw new RuntimeError('Variable "menu" does not exist.', 72, $this->source); })()), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 72, $this->source); })()), [], "array", false, false, false, 72), "content", [], "array", false, false, false, 72), (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 72, $this->source); })()), [], "array", false, false, false, 72), "options", [], "array", false, false, false, 72), (isset($context["option"]) || array_key_exists("option", $context) ? $context["option"] : (function () { throw new RuntimeError('Variable "option" does not exist.', 72, $this->source); })()), [], "array", false, false, false, 72), "icon", [], "array", false, false, false, 72), "html", null, true)) : (""));
                yield "\"></i>
               ";
                // line 73
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $this->extensions['Twig\Extra\String\StringExtension']->createUnicodeString(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["menu"]) || array_key_exists("menu", $context) ? $context["menu"] : (function () { throw new RuntimeError('Variable "menu" does not exist.', 73, $this->source); })()), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 73, $this->source); })()), [], "array", false, false, false, 73), "content", [], "array", false, false, false, 73), (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 73, $this->source); })()), [], "array", false, false, false, 73), "options", [], "array", false, false, false, 73), (isset($context["option"]) || array_key_exists("option", $context) ? $context["option"] : (function () { throw new RuntimeError('Variable "option" does not exist.', 73, $this->source); })()), [], "array", false, false, false, 73), "title", [], "array", false, false, false, 73)), "truncate", [17, "..."], "method", false, false, false, 73), "html", null, true);
                yield "
            </a>
         </li>
         ";
            }
            // line 77
            yield "
      ";
        }
        // line 79
        yield "   </ol>
</nav>

";
        // line 82
        if (CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["menu"] ?? null), (isset($context["sector"]) || array_key_exists("sector", $context) ? $context["sector"] : (function () { throw new RuntimeError('Variable "sector" does not exist.', 82, $this->source); })()), [], "array", false, true, false, 82), "content", [], "array", false, true, false, 82), (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 82, $this->source); })()), [], "array", true, true, false, 82)) {
            // line 83
            yield "    ";
            yield Twig\Extension\CoreExtension::include($this->env, $context, "layout/parts/context_links.html.twig");
            yield "
";
        }
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "layout/parts/breadcrumbs.html.twig";
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
        return array (  168 => 83,  166 => 82,  161 => 79,  157 => 77,  150 => 73,  146 => 72,  142 => 71,  136 => 68,  133 => 67,  131 => 66,  128 => 65,  121 => 61,  117 => 60,  113 => 59,  108 => 57,  104 => 56,  101 => 55,  98 => 54,  95 => 53,  92 => 52,  90 => 51,  87 => 50,  80 => 46,  76 => 45,  70 => 44,  67 => 43,  65 => 42,  58 => 38,  51 => 36,  45 => 33,  42 => 32,);
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

<nav aria-label=\"{{ __(\x27Breadcrumbs\x27) }}\">
   <ol class=\"breadcrumb breadcrumb-alternate pe-1 pe-sm-3\">
      <li class=\"breadcrumb-item text-truncate\">
      <a href=\"{{ index_path() }}\" class=\"d-inline-flex align-items-center gap-1\" data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\" title=\"{{ __(\x27Home\x27) }}\">
            <i class=\"ti ti-home-2\"></i>
            {{ __(\x27Home\x27) }}
         </a>
      </li>

      {% if menu[sector] is defined %}
      <li class=\"breadcrumb-item text-truncate\">
      <a href=\"{{ path(menu[sector][\x27default\x27] ?? \x27/front/central.php\x27) }}\" class=\"d-inline-flex align-items-center gap-1\" data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\" title=\"{{ menu[sector][\x27title\x27] }}\">
            <i class=\"{{ menu[sector][\x27icon\x27] ?? \x27\x27 }}\"></i>
            {{ menu[sector][\x27title\x27] }}
         </a>
      </li>
      {% endif %}

      {% set with_option = false %}
      {% if menu[sector][\x27content\x27][item] is defined %}
         {% if menu[sector][\x27content\x27][item][\x27page\x27] is defined %}
         {% set with_option = option is not empty and menu[sector][\x27content\x27][item][\x27options\x27][option][\x27title\x27] is defined and menu[sector][\x27content\x27][item][\x27options\x27][option][\x27page\x27] is defined %}
         <li class=\"breadcrumb-item text-truncate\">
            <a href=\"{{ path(menu[sector][\x27content\x27][item][\x27page\x27]) }}\"
            class=\"d-inline-flex align-items-center gap-1 {{ with_option ? \x27\x27 : \x27here\x27 }}\"
               data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\"
               title=\"{{ menu[sector][\x27content\x27][item][\x27title\x27] }}\" >
               <i class=\"{{ menu[sector][\x27content\x27][item][\x27icon\x27] ?? \x27\x27 }}\"></i>
               {{ menu[sector][\x27content\x27][item][\x27title\x27] }}
            </a>
         </li>
         {% endif %}

         {% if with_option %}
         <li class=\"breadcrumb-item text-truncate\">
            <a href=\"{{ path(menu[sector][\x27content\x27][item][\x27options\x27][option][\x27page\x27]) }}\"
               class=\"d-inline-flex align-items-center gap-1 here\"
               data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\"
               title=\"{{ menu[sector][\x27content\x27][item][\x27options\x27][option][\x27title\x27] }}\" >
               <i class=\"{{ menu[sector][\x27content\x27][item][\x27options\x27][option][\x27icon\x27] ?? \x27\x27 }}\"></i>
               {{ menu[sector][\x27content\x27][item][\x27options\x27][option][\x27title\x27]|u.truncate(17, \x27...\x27) }}
            </a>
         </li>
         {% endif %}

      {% endif %}
   </ol>
</nav>

{% if menu[sector][\x27content\x27][item] is defined %}
    {{ include(\x27layout/parts/context_links.html.twig\x27) }}
{% endif %}
", "layout/parts/breadcrumbs.html.twig", "/var/www/html/glpi/templates/layout/parts/breadcrumbs.html.twig");
    }
}
