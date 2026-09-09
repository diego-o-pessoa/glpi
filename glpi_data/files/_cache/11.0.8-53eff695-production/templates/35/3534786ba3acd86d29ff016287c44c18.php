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

/* @glpiinventory/submenu.html.twig */
class __TwigTemplate_60f687fd578cdfe6b6893ec62c8b5872 extends Template
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
   <nav class=\"rounded navbar justify-content-center navbar-expand-lg mt-n8 mb-3 rounded\" id=\"fusinv_navbar\">
      <ul class=\"navbar-nav\">
         ";
        // line 31
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["menu"] ?? null));
        foreach ($context['_seq'] as $context["first_lvl_key"] => $context["first_lvl"]) {
            // line 32
            yield "            <li class=\"nav-item dropdown mx-3\">
               <a class=\"nav-link dropdown-toggle\" href=\"#\" id=\"";
            // line 33
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["first_lvl_key"], "html", null, true);
            yield "_menu\"
                  role=\"button\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\" title=\"";
            // line 34
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v0 = $context["first_lvl"]) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0["name"] ?? null) : null), "html", null, true);
            yield "\">
                  ";
            // line 35
            if ((($tmp = Twig\Extension\CoreExtension::length($this->env->getCharset(), (($_v1 = $context["first_lvl"]) && is_array($_v1) || $_v1 instanceof ArrayAccess ? ($_v1["pic"] ?? null) : null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 36
                yield "                     <i class=\"p-1 ";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v2 = $context["first_lvl"]) && is_array($_v2) || $_v2 instanceof ArrayAccess ? ($_v2["pic"] ?? null) : null), "html", null, true);
                yield "\"></i>
                  ";
            }
            // line 38
            yield "                  <span class=\"menu-label\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v3 = $context["first_lvl"]) && is_array($_v3) || $_v3 instanceof ArrayAccess ? ($_v3["name"] ?? null) : null), "html", null, true);
            yield "</span>
               </a>
               <ul class=\"dropdown-menu\" aria-labelledby=\"";
            // line 40
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["first_lvl_key"], "html", null, true);
            yield "_menu\">
                  ";
            // line 41
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable((($_v4 = $context["first_lvl"]) && is_array($_v4) || $_v4 instanceof ArrayAccess ? ($_v4["children"] ?? null) : null));
            foreach ($context['_seq'] as $context["_key"] => $context["second_lvl"]) {
                // line 42
                yield "                     <li>
                        <a class=\"dropdown-item text-wrap\" href=\"";
                // line 43
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v5 = $context["second_lvl"]) && is_array($_v5) || $_v5 instanceof ArrayAccess ? ($_v5["link"] ?? null) : null), "html", null, true);
                yield "\">
                           ";
                // line 44
                if ((($tmp = Twig\Extension\CoreExtension::length($this->env->getCharset(), (($_v6 = $context["second_lvl"]) && is_array($_v6) || $_v6 instanceof ArrayAccess ? ($_v6["pic"] ?? null) : null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 45
                    yield "                              <i class=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v7 = $context["second_lvl"]) && is_array($_v7) || $_v7 instanceof ArrayAccess ? ($_v7["pic"] ?? null) : null), "html", null, true);
                    yield "\"></i>
                           ";
                }
                // line 47
                yield "                           <span>";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v8 = $context["second_lvl"]) && is_array($_v8) || $_v8 instanceof ArrayAccess ? ($_v8["name"] ?? null) : null), "html", null, true);
                yield "</span>
                        </a>
                     </li>
                  ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['second_lvl'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 51
            yield "               </ul>
            </li>
         ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['first_lvl_key'], $context['first_lvl'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 54
        yield "      </ul>
   </nav>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "@glpiinventory/submenu.html.twig";
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
        return array (  118 => 54,  110 => 51,  99 => 47,  93 => 45,  91 => 44,  87 => 43,  84 => 42,  80 => 41,  76 => 40,  70 => 38,  64 => 36,  62 => 35,  58 => 34,  54 => 33,  51 => 32,  47 => 31,  42 => 28,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "@glpiinventory/submenu.html.twig", "/var/www/html/glpi/marketplace/glpiinventory/templates/submenu.html.twig");
    }
}
