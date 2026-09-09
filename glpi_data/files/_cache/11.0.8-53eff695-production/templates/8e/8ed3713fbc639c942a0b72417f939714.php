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

/* layout/parts/page_header.html.twig */
class __TwigTemplate_82aaa8876c14f4dde900e1a393872598 extends Template
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
        $context["anonymous"] = (null === $this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpiactiveprofile"));
        // line 34
        yield "
";
        // line 35
        $context["is_vertical"] = ( !($context["anonymous"] ?? null) && ($this->extensions['Glpi\Application\View\Extension\SessionExtension']->getPageLayout() == "vertical"));
        // line 36
        $context["is_horizontal"] =  !($context["is_vertical"] ?? null);
        // line 37
        $context["is_helpdesk"] = (($context["anonymous"] ?? null) || ($this->extensions['Glpi\Application\View\Extension\SessionExtension']->getCurrentInterface() == "helpdesk"));
        // line 38
        yield "
<body class=\"";
        // line 39
        yield ((($this->extensions['Glpi\Application\View\Extension\SessionExtension']->userPref("fold_menu") && ($context["is_vertical"] ?? null))) ? ("navbar-collapsed") : (""));
        yield " ";
        yield (((($tmp = ($context["is_vertical"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("vertical-layout") : ("horizontal-layout"));
        yield " ";
        yield (((($tmp = ($context["is_debug_active"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("debug-active") : (""));
        yield " ";
        yield (((($tmp = ($context["is_helpdesk"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("helpdesk") : ("central"));
        yield "\">
   <div class=\"skip-links\">
      <a class=\"visually-hidden-focusable skip-link\" href=\"#page\">";
        // line 41
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Go to main content"), "html", null, true);
        yield "</a>
   </div>
   ";
        // line 43
        if ((($this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("DBConnection::isDbAvailable") && Twig\Extension\CoreExtension::constant("GLPI_SKIP_UPDATES", null, true)) &&  !$this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Update::isDbUpToDate"))) {
            // line 44
            yield "      <div class=\"banner-need-update\">
         ";
            // line 45
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("You are bypassing a needed update"), "html", null, true);
            yield "
      </div>
   ";
        }
        // line 48
        yield "   ";
        yield Twig\Extension\CoreExtension::include($this->env, $context, "layout/parts/impersonate_banner.html.twig");
        yield "
   ";
        // line 49
        yield Twig\Extension\CoreExtension::include($this->env, $context, "components/messages_after_redirect_toasts.html.twig", ["display_container" => true]);
        yield "

   <div class=\"page\">

      ";
        // line 53
        if ((($tmp = ($context["is_vertical"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 54
            yield "      <aside class=\"navbar navbar-vertical navbar-expand-lg sticky-lg-top sidebar\" data-testid=\"sidebar\" aria-label=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Sidebar"), "html", null, true);
            yield "\">
         <div class=\"container-fluid\">
            <button class=\"navbar-toggler\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#navbar-menu\" aria-controls=\"navbar-menu\" aria-label=\"";
            // line 56
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Toggle navigation"), "html", null, true);
            yield "\">
               <span class=\"navbar-toggler-icon\"></span>
            </button>

            <a href=\"";
            // line 60
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->indexPath(), "html", null, true);
            yield "\" accesskey=\"1\" title=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Home"), "html", null, true);
            yield "\"
               class=\"navbar-brand\">
               <span class=\"glpi-logo\"></span>
            </a>

            ";
            // line 65
            if ((($tmp =  !($context["anonymous"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 66
                yield "               <span class=\"d-none d-lg-inline-block\">
                   ";
                // line 67
                yield Twig\Extension\CoreExtension::include($this->env, $context, "layout/parts/goto_button.html.twig");
                yield "
               </span>
            ";
            }
            // line 70
            yield "
            ";
            // line 71
            if ((($tmp =  !(null === ($context["user"] ?? null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 72
                yield "               ";
                // line 73
                yield "               <div class=\"d-lg-none\">
                  ";
                // line 74
                yield Twig\Extension\CoreExtension::include($this->env, $context, "layout/parts/user_header.html.twig");
                yield "
               </div>
            ";
            }
            // line 77
            yield "
            ";
            // line 78
            if ((($tmp =  !($context["anonymous"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 79
                yield "               <nav class=\"collapse navbar-collapse\" id=\"navbar-menu\" aria-label=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Main navigation"), "html", null, true);
                yield "\">
                   <span class=\"d-inline-block d-lg-none ms-2\">
                       ";
                // line 81
                yield Twig\Extension\CoreExtension::include($this->env, $context, "layout/parts/goto_button.html.twig");
                yield "
                   </span>
                   ";
                // line 83
                yield Twig\Extension\CoreExtension::include($this->env, $context, "layout/parts/menu.html.twig");
                yield "


                  <p class=\"text-start\">
                     <button class=\"btn btn-sm btn-ghost-secondary  ";
                // line 87
                yield (((($tmp = ($context["is_debug_active"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("mb-4") : ("mb-2"));
                yield " mx-auto reduce-menu d-none d-md-block\">
                        <span class=\"menu-label\">";
                // line 88
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Collapse menu"), "html", null, true);
                yield "</span>
                     </button>
                  </p>
               </nav>
            ";
            }
            // line 93
            yield "         </div>
      </aside>
      ";
        }
        // line 96
        yield "
      <header class=\"navbar d-print-none sticky-lg-top shadow-sm ";
        // line 97
        yield (((($tmp = ($context["is_vertical"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("navbar-light navbar-expand-md") : ("navbar-dark navbar-expand-xl topbar"));
        yield "\" data-testid=\"main-header\" role=\"banner\">
         ";
        // line 99
        yield "         <div class=\"header-container container-fluid flex-xl-nowrap pe-xl-0 ";
        yield (((($tmp = ($context["is_helpdesk"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("container-xl") : (""));
        yield "\">
            ";
        // line 100
        if ((($tmp = ($context["is_vertical"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 101
            yield "               ";
            yield Twig\Extension\CoreExtension::include($this->env, $context, "layout/parts/breadcrumbs.html.twig");
            yield "

                <div class=\"ms-lg-auto d-none d-lg-block flex-grow-1 flex-lg-grow-0\">
                     ";
            // line 104
            yield Twig\Extension\CoreExtension::include($this->env, $context, "layout/parts/global_search_form.html.twig");
            yield "
                </div>

            ";
        } elseif ((($tmp =         // line 107
($context["is_horizontal"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 108
            yield "               <button class=\"navbar-toggler\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#navbar-menu\" aria-controls=\"navbar-menu\" aria-label=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Toggle navigation"), "html", null, true);
            yield "\">
                  <span class=\"navbar-toggler-icon\"></span>
               </button>

               <a href=\"";
            // line 112
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->indexPath(), "html", null, true);
            yield "\" accesskey=\"1\" title=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Home"), "html", null, true);
            yield "\"
                  class=\"navbar-brand\">
                  <span class=\"glpi-logo\"></span>
               </a>

               <div class=\"d-lg-none\">
                  ";
            // line 118
            yield Twig\Extension\CoreExtension::include($this->env, $context, "layout/parts/user_header.html.twig");
            yield "
               </div>

               ";
            // line 121
            if (( !($context["anonymous"] ?? null) || ((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["menu"] ?? null)) > 0) &&  !CoreExtension::getAttribute($this->env, $this->source, ($context["menu"] ?? null), "home", [], "any", true, true, false, 121)))) {
                // line 122
                yield "               <nav class=\"collapse navbar-collapse justify-content-center\" id=\"navbar-menu\" aria-label=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Main navigation"), "html", null, true);
                yield "\">
                  ";
                // line 123
                yield Twig\Extension\CoreExtension::include($this->env, $context, "layout/parts/menu.html.twig");
                yield "
                  ";
                // line 124
                if ((($tmp =  !($context["anonymous"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 125
                    yield "                  <span class=\"ms-xl-2 d-inline-block mt-2 mt-xl-2\">
                     ";
                    // line 126
                    yield Twig\Extension\CoreExtension::include($this->env, $context, "layout/parts/goto_button.html.twig");
                    yield "
                  </span>
                  ";
                }
                // line 129
                yield "               </nav>
               ";
            }
            // line 131
            yield "            ";
        }
        // line 132
        yield "
            <div class=\"ms-md-4 d-none d-lg-block\">
               ";
        // line 134
        yield Twig\Extension\CoreExtension::include($this->env, $context, "layout/parts/user_header.html.twig");
        yield "
            </div>
         </div>
      </header>

      ";
        // line 140
        yield "      ";
        if ((($context["is_horizontal"] ?? null) &&  !($context["is_helpdesk"] ?? null))) {
            // line 141
            yield "      <div class=\"navbar navbar-expand-md navbar-light secondary-bar sticky-md-top shadow-sm\">
         <div class=\"container-fluid justify-content-start\">
            ";
            // line 143
            yield Twig\Extension\CoreExtension::include($this->env, $context, "layout/parts/breadcrumbs.html.twig");
            yield "
            <div class=\"ms-md-auto d-none d-md-block flex-grow-1 flex-md-grow-0\">
                ";
            // line 145
            yield Twig\Extension\CoreExtension::include($this->env, $context, "layout/parts/global_search_form.html.twig");
            yield "
            </div>
         </div>
      </div>
      ";
        }
        // line 150
        yield "
      <div class=\"page-wrapper mb-0\">
         <div class=\"page-body container-fluid ";
        // line 152
        yield (((($tmp = ($context["is_helpdesk"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("container-xl") : (""));
        yield "\">
            <main role=\"main\" id=\"page\" class=\"legacy\" tabindex=\"-1\">
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "layout/parts/page_header.html.twig";
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
        return array (  309 => 152,  305 => 150,  297 => 145,  292 => 143,  288 => 141,  285 => 140,  277 => 134,  273 => 132,  270 => 131,  266 => 129,  260 => 126,  257 => 125,  255 => 124,  251 => 123,  246 => 122,  244 => 121,  238 => 118,  227 => 112,  219 => 108,  217 => 107,  211 => 104,  204 => 101,  202 => 100,  197 => 99,  193 => 97,  190 => 96,  185 => 93,  177 => 88,  173 => 87,  166 => 83,  161 => 81,  155 => 79,  153 => 78,  150 => 77,  144 => 74,  141 => 73,  139 => 72,  137 => 71,  134 => 70,  128 => 67,  125 => 66,  123 => 65,  113 => 60,  106 => 56,  100 => 54,  98 => 53,  91 => 49,  86 => 48,  80 => 45,  77 => 44,  75 => 43,  70 => 41,  59 => 39,  56 => 38,  54 => 37,  52 => 36,  50 => 35,  47 => 34,  45 => 33,  42 => 32,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "layout/parts/page_header.html.twig", "/var/www/html/glpi/templates/layout/parts/page_header.html.twig");
    }
}
