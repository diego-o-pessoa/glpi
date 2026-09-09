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

/* layout/parts/menu.html.twig */
class __TwigTemplate_da46ca852def4d1962566618641fd3ce extends Template
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
        $context["is_vertical"] = ($this->extensions['Glpi\Application\View\Extension\SessionExtension']->getPageLayout() == "vertical");
        // line 34
        $context["is_horizontal"] =  !($context["is_vertical"] ?? null);
        // line 35
        $context["is_menu_folded"] = ($this->extensions['Glpi\Application\View\Extension\SessionExtension']->userPref("fold_menu") == "1");
        // line 36
        $context["rand"] = Twig\Extension\CoreExtension::random($this->env->getCharset());
        // line 37
        yield "
<ul class=\"navbar-nav\" id=\"menu_";
        // line 38
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
        yield "\">
";
        // line 39
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["menu"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["firstlevel"]) {
            // line 40
            yield "   ";
            $context["firstlevel_active"] = ((array_key_exists("sector", $context) && CoreExtension::getAttribute($this->env, $this->source, ($context["menu"] ?? null), ($context["sector"] ?? null), [], "array", true, true, false, 40)) && (((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["menu"] ?? null), ($context["sector"] ?? null), [], "array", false, true, false, 40), "title", [], "array", true, true, false, 40)) ? (Twig\Extension\CoreExtension::default((($_v0 = (($_v1 = ($context["menu"] ?? null)) && is_array($_v1) || $_v1 instanceof ArrayAccess ? ($_v1[(($_v2 = ($context["sector"] ?? null)) instanceof \Stringable ? (string) $_v2 : $_v2)] ?? null) : null)) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0["title"] ?? null) : null), "")) : ("")) == (($_v3 = $context["firstlevel"]) && is_array($_v3) || $_v3 instanceof ArrayAccess ? ($_v3["title"] ?? null) : null)));
            // line 41
            yield "   ";
            $context["firstlevel_shown"] = ((($context["firstlevel_active"] ?? null) && ($context["is_vertical"] ?? null)) && (($context["is_menu_folded"] ?? null) == false));
            // line 42
            yield "   ";
            $context["has_subitems"] = false;
            // line 43
            yield "   ";
            if (CoreExtension::getAttribute($this->env, $this->source, $context["firstlevel"], "content", [], "array", true, true, false, 43)) {
                // line 44
                yield "      ";
                // line 45
                yield "      ";
                $context['_parent'] = $context;
                $context['_seq'] = CoreExtension::ensureTraversable((($_v4 = $context["firstlevel"]) && is_array($_v4) || $_v4 instanceof ArrayAccess ? ($_v4["content"] ?? null) : null));
                foreach ($context['_seq'] as $context["_key"] => $context["secondlevel"]) {
                    // line 46
                    yield "         ";
                    if (CoreExtension::getAttribute($this->env, $this->source, $context["secondlevel"], "page", [], "array", true, true, false, 46)) {
                        // line 47
                        yield "            ";
                        $context["has_subitems"] = true;
                        // line 48
                        yield "         ";
                    }
                    // line 49
                    yield "      ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_key'], $context['secondlevel'], $context['_parent']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 50
                yield "   ";
            }
            // line 51
            yield "   ";
            if ((($tmp = ($context["has_subitems"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 52
                yield "   <li class=\"nav-item dropdown ";
                yield (((($tmp = ($context["firstlevel_active"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("active") : (""));
                yield "\" aria-label=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v5 = $context["firstlevel"]) && is_array($_v5) || $_v5 instanceof ArrayAccess ? ($_v5["title"] ?? null) : null), "html", null, true);
                yield "\">
      <button type=\"button\"
         class=\"nav-link dropdown-toggle ";
                // line 54
                yield (((($tmp = ($context["firstlevel_active"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("active") : (""));
                yield " ";
                yield (((($tmp = ($context["firstlevel_shown"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("show") : (""));
                yield "\"
         data-bs-toggle=\"dropdown\"
         data-testid=\"sidebar-menu-toggle\"
         aria-expanded=\"";
                // line 57
                yield (((($tmp = ($context["firstlevel_shown"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("true") : ("false"));
                yield "\">
         <i class=\"";
                // line 58
                yield (((CoreExtension::getAttribute($this->env, $this->source, $context["firstlevel"], "icon", [], "array", true, true, false, 58) &&  !(null === (($_v6 = $context["firstlevel"]) && is_array($_v6) || $_v6 instanceof ArrayAccess ? ($_v6["icon"] ?? null) : null)))) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v7 = $context["firstlevel"]) && is_array($_v7) || $_v7 instanceof ArrayAccess ? ($_v7["icon"] ?? null) : null), "html", null, true)) : (""));
                yield "\"></i>
         <span class=\"menu-label\">";
                // line 59
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v8 = $context["firstlevel"]) && is_array($_v8) || $_v8 instanceof ArrayAccess ? ($_v8["title"] ?? null) : null), "html", null, true);
                yield "</span>
      </button>
      <div class=\"dropdown-menu ";
                // line 61
                yield (((($context["firstlevel_active"] ?? null) && (($context["is_vertical"] ?? null) != false))) ? ("") : ("animate__animated"));
                yield " ";
                yield (((($tmp = ($context["is_vertical"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("animate__fadeInLeft") : ("animate__zoomIn"));
                yield " ";
                yield (((($tmp = ($context["firstlevel_shown"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("show") : (""));
                yield "\">
         <h6 class=\"dropdown-header\">";
                // line 62
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v9 = $context["firstlevel"]) && is_array($_v9) || $_v9 instanceof ArrayAccess ? ($_v9["title"] ?? null) : null), "html", null, true);
                yield "</h6>
         <div class=\"dropdown-menu-columns\">
            <div class=\"dropdown-menu-column\">
            ";
                // line 65
                $context["has_dashboard"] = CoreExtension::getAttribute($this->env, $this->source, $context["firstlevel"], "default_dashboard", [], "array", true, true, false, 65);
                // line 66
                yield "            ";
                if ((($tmp = ($context["has_dashboard"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 67
                    yield "               <a class=\"dropdown-item\"
                  href=\"";
                    // line 68
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path((($_v10 = $context["firstlevel"]) && is_array($_v10) || $_v10 instanceof ArrayAccess ? ($_v10["default_dashboard"] ?? null) : null)), "html", null, true);
                    yield "\">
                  <i class=\"";
                    // line 69
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Glpi\\Dashboard\\Dashboard::getIcon"), "html", null, true);
                    yield "\"></i>
                  ";
                    // line 70
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Dashboard"), "html", null, true);
                    yield "
               </a>
            ";
                }
                // line 73
                yield "            ";
                $context['_parent'] = $context;
                $context['_seq'] = CoreExtension::ensureTraversable((($_v11 = $context["firstlevel"]) && is_array($_v11) || $_v11 instanceof ArrayAccess ? ($_v11["content"] ?? null) : null));
                $context['loop'] = [
                  'parent' => $context['_parent'],
                  'index0' => 0,
                  'index'  => 1,
                  'first'  => true,
                ];
                if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof \Countable)) {
                    $length = count($context['_seq']);
                    $context['loop']['revindex0'] = $length - 1;
                    $context['loop']['revindex'] = $length;
                    $context['loop']['length'] = $length;
                    $context['loop']['last'] = 1 === $length;
                }
                foreach ($context['_seq'] as $context["_key"] => $context["sublevel"]) {
                    // line 74
                    yield "               ";
                    if (CoreExtension::getAttribute($this->env, $this->source, $context["sublevel"], "page", [], "array", true, true, false, 74)) {
                        // line 75
                        yield "               <a class=\"dropdown-item ";
                        yield (((($context["menu_active"] ?? null) == (($_v12 = $context["sublevel"]) && is_array($_v12) || $_v12 instanceof ArrayAccess ? ($_v12["title"] ?? null) : null))) ? ("active") : (""));
                        yield "\"
                  href=\"";
                        // line 76
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path((($_v13 = $context["sublevel"]) && is_array($_v13) || $_v13 instanceof ArrayAccess ? ($_v13["page"] ?? null) : null)), "html", null, true);
                        yield "\"
                  aria-label=\"";
                        // line 77
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v14 = $context["sublevel"]) && is_array($_v14) || $_v14 instanceof ArrayAccess ? ($_v14["title"] ?? null) : null), "html", null, true);
                        yield "\"
                  accesskey=\"";
                        // line 78
                        yield (((CoreExtension::getAttribute($this->env, $this->source, $context["sublevel"], "shortcut", [], "array", true, true, false, 78) &&  !(null === (($_v15 = $context["sublevel"]) && is_array($_v15) || $_v15 instanceof ArrayAccess ? ($_v15["shortcut"] ?? null) : null)))) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v16 = $context["sublevel"]) && is_array($_v16) || $_v16 instanceof ArrayAccess ? ($_v16["shortcut"] ?? null) : null), "html", null, true)) : (""));
                        yield "\">
                  <i class=\"";
                        // line 79
                        yield (((CoreExtension::getAttribute($this->env, $this->source, $context["sublevel"], "icon", [], "array", true, true, false, 79) &&  !(null === (($_v17 = $context["sublevel"]) && is_array($_v17) || $_v17 instanceof ArrayAccess ? ($_v17["icon"] ?? null) : null)))) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v18 = $context["sublevel"]) && is_array($_v18) || $_v18 instanceof ArrayAccess ? ($_v18["icon"] ?? null) : null), "html", null, true)) : (""));
                        yield "\"></i>
                  <span class=\x27text-wrap\x27>
                     ";
                        // line 81
                        yield $this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->underlineShortcutLetter((($_v19 = $context["sublevel"]) && is_array($_v19) || $_v19 instanceof ArrayAccess ? ($_v19["title"] ?? null) : null), (((CoreExtension::getAttribute($this->env, $this->source, $context["sublevel"], "shortcut", [], "array", true, true, false, 81) &&  !(null === (($_v20 = $context["sublevel"]) && is_array($_v20) || $_v20 instanceof ArrayAccess ? ($_v20["shortcut"] ?? null) : null)))) ? ((($_v21 = $context["sublevel"]) && is_array($_v21) || $_v21 instanceof ArrayAccess ? ($_v21["shortcut"] ?? null) : null)) : ("")));
                        yield "
                  </span>
               </a>
               ";
                    }
                    // line 85
                    yield "
               ";
                    // line 86
                    $context["count_per_column"] = 6;
                    // line 87
                    yield "               ";
                    if ((((CoreExtension::getAttribute($this->env, $this->source, $context["loop"], "index", [], "any", false, false, false, 87) % ($context["count_per_column"] ?? null)) == (((($tmp = ($context["has_dashboard"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ((($context["count_per_column"] ?? null) - 1)) : (0))) &&  !CoreExtension::getAttribute($this->env, $this->source, $context["loop"], "last", [], "any", false, false, false, 87))) {
                        // line 88
                        yield "                  </div>
                  <div class=\"dropdown-menu-column\">
               ";
                    }
                    // line 91
                    yield "            ";
                    ++$context['loop']['index0'];
                    ++$context['loop']['index'];
                    $context['loop']['first'] = false;
                    if (isset($context['loop']['revindex0'], $context['loop']['revindex'])) {
                        --$context['loop']['revindex0'];
                        --$context['loop']['revindex'];
                        $context['loop']['last'] = 0 === $context['loop']['revindex0'];
                    }
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_key'], $context['sublevel'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 92
                yield "            </div>
         </div>
      </div>
   </li>
   ";
            } elseif ((CoreExtension::getAttribute($this->env, $this->source,             // line 96
$context["firstlevel"], "default", [], "array", true, true, false, 96) && ((((CoreExtension::getAttribute($this->env, $this->source, $context["firstlevel"], "display", [], "array", true, true, false, 96) &&  !(null === (($_v22 = $context["firstlevel"]) && is_array($_v22) || $_v22 instanceof ArrayAccess ? ($_v22["display"] ?? null) : null)))) ? ((($_v23 = $context["firstlevel"]) && is_array($_v23) || $_v23 instanceof ArrayAccess ? ($_v23["display"] ?? null) : null)) : (true)) != false))) {
                // line 97
                yield "      <li class=\"nav-item dropdown ";
                yield (((($tmp = ($context["firstlevel_active"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("active") : (""));
                yield "\">
         <a class=\"nav-link\" href=\"";
                // line 98
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path((($_v24 = $context["firstlevel"]) && is_array($_v24) || $_v24 instanceof ArrayAccess ? ($_v24["default"] ?? null) : null)), "html", null, true);
                yield "\">
            <i class=\"";
                // line 99
                yield (((CoreExtension::getAttribute($this->env, $this->source, $context["firstlevel"], "icon", [], "array", true, true, false, 99) &&  !(null === (($_v25 = $context["firstlevel"]) && is_array($_v25) || $_v25 instanceof ArrayAccess ? ($_v25["icon"] ?? null) : null)))) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v26 = $context["firstlevel"]) && is_array($_v26) || $_v26 instanceof ArrayAccess ? ($_v26["icon"] ?? null) : null), "html", null, true)) : (""));
                yield "\"></i>
            <span class=\"menu-label\">";
                // line 100
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v27 = $context["firstlevel"]) && is_array($_v27) || $_v27 instanceof ArrayAccess ? ($_v27["title"] ?? null) : null), "html", null, true);
                yield "</span>
         </a>
      <li>
   ";
            }
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['firstlevel'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 105
        yield "</ul>

";
        // line 107
        if ((($tmp = ($context["is_vertical"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 108
            yield "<script type=\"text/javascript\">
\$(function() {
   // below, some modifications of dropdowns menu behavior
   document.querySelectorAll(\x27#menu_";
            // line 111
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
            yield " > .dropdown\x27).forEach(function(menuDropdown) {
      // prevent menu closes
      menuDropdown.addEventListener(\x27hide.bs.dropdown\x27, function (event) {
         var orig_event = event.clickEvent;
         if (typeof orig_event != \"undefined\"
             && typeof orig_event.target != \"undefined\") {
            // prevent body clicking to hide menu
            if (!document.getElementById(\x27menu_";
            // line 118
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
            yield "\x27).contains(orig_event.target)) {
               event.preventDefault();
               return;
            }

            // prevent menu links to close menu (waiting the page redirection)
            if (orig_event.target.className.indexOf(\x27dropdown-item\x27) !== false) {
               for (var item of document.querySelectorAll(\x27#menu_";
            // line 125
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
            yield " .dropdown-item\x27)) {
                  item.classList.remove(\x27active\x27);
               }
               orig_event.target.classList.add(\x27active\x27);
               event.preventDefault();
            }
         }
      });

      // opening a sub menu close others
      menuDropdown.addEventListener(\x27show.bs.dropdown\x27, function (event) {
          if (\$(\x27body\x27).hasClass(\x27navbar-collapsed\x27)) {
              // Dropdown submenus will be shown with CSS, and shouldn\x27t be handled by Bootstrap
              event.preventDefault();
              event.stopPropagation();
          }
         \$(\x27#menu_";
            // line 141
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
            yield " .nav-link\x27).removeClass(\x27show active\x27);
         \$(\x27#menu_";
            // line 142
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
            yield " .nav-item\x27).removeClass(\x27active\x27);
         \$(\x27#menu_";
            // line 143
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
            yield " .dropdown-menu\x27).removeClass(\x27show\x27);
      })
   });
});
</script>
";
        }
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "layout/parts/menu.html.twig";
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
        return array (  336 => 143,  332 => 142,  328 => 141,  309 => 125,  299 => 118,  289 => 111,  284 => 108,  282 => 107,  278 => 105,  267 => 100,  263 => 99,  259 => 98,  254 => 97,  252 => 96,  246 => 92,  232 => 91,  227 => 88,  224 => 87,  222 => 86,  219 => 85,  212 => 81,  207 => 79,  203 => 78,  199 => 77,  195 => 76,  190 => 75,  187 => 74,  169 => 73,  163 => 70,  159 => 69,  155 => 68,  152 => 67,  149 => 66,  147 => 65,  141 => 62,  133 => 61,  128 => 59,  124 => 58,  120 => 57,  112 => 54,  104 => 52,  101 => 51,  98 => 50,  92 => 49,  89 => 48,  86 => 47,  83 => 46,  78 => 45,  76 => 44,  73 => 43,  70 => 42,  67 => 41,  64 => 40,  60 => 39,  56 => 38,  53 => 37,  51 => 36,  49 => 35,  47 => 34,  45 => 33,  42 => 32,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "layout/parts/menu.html.twig", "/var/www/html/glpi/templates/layout/parts/menu.html.twig");
    }
}
