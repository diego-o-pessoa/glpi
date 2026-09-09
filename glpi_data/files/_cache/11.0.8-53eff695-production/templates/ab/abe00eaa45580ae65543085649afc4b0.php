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

/* layout/parts/page_header_empty.html.twig */
class __TwigTemplate_63c3a9bad1f18b9f145e3728d9f2b426 extends Template
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
<body class=\"horizontal-layout\">
   <div class=\"skip-links\">
      <a class=\"visually-hidden-focusable skip-link\" href=\"#page\">";
        // line 35
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Go to main content"), "html", null, true);
        yield "</a>
   </div>
   ";
        // line 37
        if ((($this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("DBConnection::isDbAvailable") && Twig\Extension\CoreExtension::constant("GLPI_SKIP_UPDATES")) &&  !$this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Update::isDbUpToDate"))) {
            // line 38
            yield "      <div class=\"banner-need-update\">
         ";
            // line 39
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("You are bypassing a needed update"), "html", null, true);
            yield "
      </div>
   ";
        }
        // line 42
        yield "   <div class=\"page\">

      <header class=\"navbar d-print-none sticky-lg-top shadow-sm topbar\" role=\"banner\">
         <div class=\"container-fluid\">
            <a href=\"";
        // line 46
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->indexPath(), "html", null, true);
        yield "\" title=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Home"), "html", null, true);
        yield "\" class=\"navbar-brand\">
               <span class=\"glpi-logo\"></span>
            </a>
         </div>
      </header>

      <div class=\"page-wrapper mb-0\">
         <div class=\"page-body container-fluid\">
            <main role=\"main\" id=\"page\" class=\"legacy\" tabindex=\"-1\">
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "layout/parts/page_header_empty.html.twig";
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
        return array (  69 => 46,  63 => 42,  57 => 39,  54 => 38,  52 => 37,  47 => 35,  42 => 32,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "layout/parts/page_header_empty.html.twig", "/var/www/html/glpi/templates/layout/parts/page_header_empty.html.twig");
    }
}
