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
class __TwigTemplate_a974f12eaaf5bda0adca8cb2d884c585 extends Template
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
        if (CoreExtension::getAttribute($this->env, $this->source, ($context["menu"] ?? null), ($context["sector"] ?? null), [], "array", true, true, false, 42)) {
            // line 43
            yield "      <li class=\"breadcrumb-item text-truncate\">
      <a href=\"";
            // line 44
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path((((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["menu"] ?? null), ($context["sector"] ?? null), [], "array", false, true, false, 44), "default", [], "array", true, true, false, 44) &&  !(null === (($_v0 = (($_v1 = ($context["menu"] ?? null)) && is_array($_v1) || $_v1 instanceof ArrayAccess ? ($_v1[(($_v2 = ($context["sector"] ?? null)) instanceof \Stringable ? (string) $_v2 : $_v2)] ?? null) : null)) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0["default"] ?? null) : null)))) ? ((($_v3 = (($_v4 = ($context["menu"] ?? null)) && is_array($_v4) || $_v4 instanceof ArrayAccess ? ($_v4[(($_v5 = ($context["sector"] ?? null)) instanceof \Stringable ? (string) $_v5 : $_v5)] ?? null) : null)) && is_array($_v3) || $_v3 instanceof ArrayAccess ? ($_v3["default"] ?? null) : null)) : ("/front/central.php"))), "html", null, true);
            yield "\" class=\"d-inline-flex align-items-center gap-1\" data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\" title=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v6 = (($_v7 = ($context["menu"] ?? null)) && is_array($_v7) || $_v7 instanceof ArrayAccess ? ($_v7[(($_v8 = ($context["sector"] ?? null)) instanceof \Stringable ? (string) $_v8 : $_v8)] ?? null) : null)) && is_array($_v6) || $_v6 instanceof ArrayAccess ? ($_v6["title"] ?? null) : null), "html", null, true);
            yield "\">
            <i class=\"";
            // line 45
            yield (((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["menu"] ?? null), ($context["sector"] ?? null), [], "array", false, true, false, 45), "icon", [], "array", true, true, false, 45) &&  !(null === (($_v9 = (($_v10 = ($context["menu"] ?? null)) && is_array($_v10) || $_v10 instanceof ArrayAccess ? ($_v10[(($_v11 = ($context["sector"] ?? null)) instanceof \Stringable ? (string) $_v11 : $_v11)] ?? null) : null)) && is_array($_v9) || $_v9 instanceof ArrayAccess ? ($_v9["icon"] ?? null) : null)))) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v12 = (($_v13 = ($context["menu"] ?? null)) && is_array($_v13) || $_v13 instanceof ArrayAccess ? ($_v13[(($_v14 = ($context["sector"] ?? null)) instanceof \Stringable ? (string) $_v14 : $_v14)] ?? null) : null)) && is_array($_v12) || $_v12 instanceof ArrayAccess ? ($_v12["icon"] ?? null) : null), "html", null, true)) : (""));
            yield "\"></i>
            ";
            // line 46
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v15 = (($_v16 = ($context["menu"] ?? null)) && is_array($_v16) || $_v16 instanceof ArrayAccess ? ($_v16[(($_v17 = ($context["sector"] ?? null)) instanceof \Stringable ? (string) $_v17 : $_v17)] ?? null) : null)) && is_array($_v15) || $_v15 instanceof ArrayAccess ? ($_v15["title"] ?? null) : null), "html", null, true);
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
        if (CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["menu"] ?? null), ($context["sector"] ?? null), [], "array", false, true, false, 52), "content", [], "array", false, true, false, 52), ($context["item"] ?? null), [], "array", true, true, false, 52)) {
            // line 53
            yield "         ";
            if (CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["menu"] ?? null), ($context["sector"] ?? null), [], "array", false, true, false, 53), "content", [], "array", false, true, false, 53), ($context["item"] ?? null), [], "array", false, true, false, 53), "page", [], "array", true, true, false, 53)) {
                // line 54
                yield "         ";
                $context["with_option"] = (( !Twig\Extension\CoreExtension::testEmpty(($context["option"] ?? null)) && CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["menu"] ?? null), ($context["sector"] ?? null), [], "array", false, true, false, 54), "content", [], "array", false, true, false, 54), ($context["item"] ?? null), [], "array", false, true, false, 54), "options", [], "array", false, true, false, 54), ($context["option"] ?? null), [], "array", false, true, false, 54), "title", [], "array", true, true, false, 54)) && CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["menu"] ?? null), ($context["sector"] ?? null), [], "array", false, true, false, 54), "content", [], "array", false, true, false, 54), ($context["item"] ?? null), [], "array", false, true, false, 54), "options", [], "array", false, true, false, 54), ($context["option"] ?? null), [], "array", false, true, false, 54), "page", [], "array", true, true, false, 54));
                // line 55
                yield "         <li class=\"breadcrumb-item text-truncate\">
            <a href=\"";
                // line 56
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path((($_v18 = (($_v19 = (($_v20 = (($_v21 = ($context["menu"] ?? null)) && is_array($_v21) || $_v21 instanceof ArrayAccess ? ($_v21[(($_v22 = ($context["sector"] ?? null)) instanceof \Stringable ? (string) $_v22 : $_v22)] ?? null) : null)) && is_array($_v20) || $_v20 instanceof ArrayAccess ? ($_v20["content"] ?? null) : null)) && is_array($_v19) || $_v19 instanceof ArrayAccess ? ($_v19[(($_v23 = ($context["item"] ?? null)) instanceof \Stringable ? (string) $_v23 : $_v23)] ?? null) : null)) && is_array($_v18) || $_v18 instanceof ArrayAccess ? ($_v18["page"] ?? null) : null)), "html", null, true);
                yield "\"
            class=\"d-inline-flex align-items-center gap-1 ";
                // line 57
                yield (((($tmp = ($context["with_option"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("") : ("here"));
                yield "\"
               data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\"
               title=\"";
                // line 59
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v24 = (($_v25 = (($_v26 = (($_v27 = ($context["menu"] ?? null)) && is_array($_v27) || $_v27 instanceof ArrayAccess ? ($_v27[(($_v28 = ($context["sector"] ?? null)) instanceof \Stringable ? (string) $_v28 : $_v28)] ?? null) : null)) && is_array($_v26) || $_v26 instanceof ArrayAccess ? ($_v26["content"] ?? null) : null)) && is_array($_v25) || $_v25 instanceof ArrayAccess ? ($_v25[(($_v29 = ($context["item"] ?? null)) instanceof \Stringable ? (string) $_v29 : $_v29)] ?? null) : null)) && is_array($_v24) || $_v24 instanceof ArrayAccess ? ($_v24["title"] ?? null) : null), "html", null, true);
                yield "\" >
               <i class=\"";
                // line 60
                yield (((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["menu"] ?? null), ($context["sector"] ?? null), [], "array", false, true, false, 60), "content", [], "array", false, true, false, 60), ($context["item"] ?? null), [], "array", false, true, false, 60), "icon", [], "array", true, true, false, 60) &&  !(null === (($_v30 = (($_v31 = (($_v32 = (($_v33 = ($context["menu"] ?? null)) && is_array($_v33) || $_v33 instanceof ArrayAccess ? ($_v33[(($_v34 = ($context["sector"] ?? null)) instanceof \Stringable ? (string) $_v34 : $_v34)] ?? null) : null)) && is_array($_v32) || $_v32 instanceof ArrayAccess ? ($_v32["content"] ?? null) : null)) && is_array($_v31) || $_v31 instanceof ArrayAccess ? ($_v31[(($_v35 = ($context["item"] ?? null)) instanceof \Stringable ? (string) $_v35 : $_v35)] ?? null) : null)) && is_array($_v30) || $_v30 instanceof ArrayAccess ? ($_v30["icon"] ?? null) : null)))) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v36 = (($_v37 = (($_v38 = (($_v39 = ($context["menu"] ?? null)) && is_array($_v39) || $_v39 instanceof ArrayAccess ? ($_v39[(($_v40 = ($context["sector"] ?? null)) instanceof \Stringable ? (string) $_v40 : $_v40)] ?? null) : null)) && is_array($_v38) || $_v38 instanceof ArrayAccess ? ($_v38["content"] ?? null) : null)) && is_array($_v37) || $_v37 instanceof ArrayAccess ? ($_v37[(($_v41 = ($context["item"] ?? null)) instanceof \Stringable ? (string) $_v41 : $_v41)] ?? null) : null)) && is_array($_v36) || $_v36 instanceof ArrayAccess ? ($_v36["icon"] ?? null) : null), "html", null, true)) : (""));
                yield "\"></i>
               ";
                // line 61
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v42 = (($_v43 = (($_v44 = (($_v45 = ($context["menu"] ?? null)) && is_array($_v45) || $_v45 instanceof ArrayAccess ? ($_v45[(($_v46 = ($context["sector"] ?? null)) instanceof \Stringable ? (string) $_v46 : $_v46)] ?? null) : null)) && is_array($_v44) || $_v44 instanceof ArrayAccess ? ($_v44["content"] ?? null) : null)) && is_array($_v43) || $_v43 instanceof ArrayAccess ? ($_v43[(($_v47 = ($context["item"] ?? null)) instanceof \Stringable ? (string) $_v47 : $_v47)] ?? null) : null)) && is_array($_v42) || $_v42 instanceof ArrayAccess ? ($_v42["title"] ?? null) : null), "html", null, true);
                yield "
            </a>
         </li>
         ";
            }
            // line 65
            yield "
         ";
            // line 66
            if ((($tmp = ($context["with_option"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 67
                yield "         <li class=\"breadcrumb-item text-truncate\">
            <a href=\"";
                // line 68
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path((($_v48 = (($_v49 = (($_v50 = (($_v51 = (($_v52 = (($_v53 = ($context["menu"] ?? null)) && is_array($_v53) || $_v53 instanceof ArrayAccess ? ($_v53[(($_v54 = ($context["sector"] ?? null)) instanceof \Stringable ? (string) $_v54 : $_v54)] ?? null) : null)) && is_array($_v52) || $_v52 instanceof ArrayAccess ? ($_v52["content"] ?? null) : null)) && is_array($_v51) || $_v51 instanceof ArrayAccess ? ($_v51[(($_v55 = ($context["item"] ?? null)) instanceof \Stringable ? (string) $_v55 : $_v55)] ?? null) : null)) && is_array($_v50) || $_v50 instanceof ArrayAccess ? ($_v50["options"] ?? null) : null)) && is_array($_v49) || $_v49 instanceof ArrayAccess ? ($_v49[(($_v56 = ($context["option"] ?? null)) instanceof \Stringable ? (string) $_v56 : $_v56)] ?? null) : null)) && is_array($_v48) || $_v48 instanceof ArrayAccess ? ($_v48["page"] ?? null) : null)), "html", null, true);
                yield "\"
               class=\"d-inline-flex align-items-center gap-1 here\"
               data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\"
               title=\"";
                // line 71
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v57 = (($_v58 = (($_v59 = (($_v60 = (($_v61 = (($_v62 = ($context["menu"] ?? null)) && is_array($_v62) || $_v62 instanceof ArrayAccess ? ($_v62[(($_v63 = ($context["sector"] ?? null)) instanceof \Stringable ? (string) $_v63 : $_v63)] ?? null) : null)) && is_array($_v61) || $_v61 instanceof ArrayAccess ? ($_v61["content"] ?? null) : null)) && is_array($_v60) || $_v60 instanceof ArrayAccess ? ($_v60[(($_v64 = ($context["item"] ?? null)) instanceof \Stringable ? (string) $_v64 : $_v64)] ?? null) : null)) && is_array($_v59) || $_v59 instanceof ArrayAccess ? ($_v59["options"] ?? null) : null)) && is_array($_v58) || $_v58 instanceof ArrayAccess ? ($_v58[(($_v65 = ($context["option"] ?? null)) instanceof \Stringable ? (string) $_v65 : $_v65)] ?? null) : null)) && is_array($_v57) || $_v57 instanceof ArrayAccess ? ($_v57["title"] ?? null) : null), "html", null, true);
                yield "\" >
               <i class=\"";
                // line 72
                yield (((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["menu"] ?? null), ($context["sector"] ?? null), [], "array", false, true, false, 72), "content", [], "array", false, true, false, 72), ($context["item"] ?? null), [], "array", false, true, false, 72), "options", [], "array", false, true, false, 72), ($context["option"] ?? null), [], "array", false, true, false, 72), "icon", [], "array", true, true, false, 72) &&  !(null === (($_v66 = (($_v67 = (($_v68 = (($_v69 = (($_v70 = (($_v71 = ($context["menu"] ?? null)) && is_array($_v71) || $_v71 instanceof ArrayAccess ? ($_v71[(($_v72 = ($context["sector"] ?? null)) instanceof \Stringable ? (string) $_v72 : $_v72)] ?? null) : null)) && is_array($_v70) || $_v70 instanceof ArrayAccess ? ($_v70["content"] ?? null) : null)) && is_array($_v69) || $_v69 instanceof ArrayAccess ? ($_v69[(($_v73 = ($context["item"] ?? null)) instanceof \Stringable ? (string) $_v73 : $_v73)] ?? null) : null)) && is_array($_v68) || $_v68 instanceof ArrayAccess ? ($_v68["options"] ?? null) : null)) && is_array($_v67) || $_v67 instanceof ArrayAccess ? ($_v67[(($_v74 = ($context["option"] ?? null)) instanceof \Stringable ? (string) $_v74 : $_v74)] ?? null) : null)) && is_array($_v66) || $_v66 instanceof ArrayAccess ? ($_v66["icon"] ?? null) : null)))) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v75 = (($_v76 = (($_v77 = (($_v78 = (($_v79 = (($_v80 = ($context["menu"] ?? null)) && is_array($_v80) || $_v80 instanceof ArrayAccess ? ($_v80[(($_v81 = ($context["sector"] ?? null)) instanceof \Stringable ? (string) $_v81 : $_v81)] ?? null) : null)) && is_array($_v79) || $_v79 instanceof ArrayAccess ? ($_v79["content"] ?? null) : null)) && is_array($_v78) || $_v78 instanceof ArrayAccess ? ($_v78[(($_v82 = ($context["item"] ?? null)) instanceof \Stringable ? (string) $_v82 : $_v82)] ?? null) : null)) && is_array($_v77) || $_v77 instanceof ArrayAccess ? ($_v77["options"] ?? null) : null)) && is_array($_v76) || $_v76 instanceof ArrayAccess ? ($_v76[(($_v83 = ($context["option"] ?? null)) instanceof \Stringable ? (string) $_v83 : $_v83)] ?? null) : null)) && is_array($_v75) || $_v75 instanceof ArrayAccess ? ($_v75["icon"] ?? null) : null), "html", null, true)) : (""));
                yield "\"></i>
               ";
                // line 73
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $this->extensions['Twig\Extra\String\StringExtension']->createUnicodeString((($_v84 = (($_v85 = (($_v86 = (($_v87 = (($_v88 = (($_v89 = ($context["menu"] ?? null)) && is_array($_v89) || $_v89 instanceof ArrayAccess ? ($_v89[(($_v90 = ($context["sector"] ?? null)) instanceof \Stringable ? (string) $_v90 : $_v90)] ?? null) : null)) && is_array($_v88) || $_v88 instanceof ArrayAccess ? ($_v88["content"] ?? null) : null)) && is_array($_v87) || $_v87 instanceof ArrayAccess ? ($_v87[(($_v91 = ($context["item"] ?? null)) instanceof \Stringable ? (string) $_v91 : $_v91)] ?? null) : null)) && is_array($_v86) || $_v86 instanceof ArrayAccess ? ($_v86["options"] ?? null) : null)) && is_array($_v85) || $_v85 instanceof ArrayAccess ? ($_v85[(($_v92 = ($context["option"] ?? null)) instanceof \Stringable ? (string) $_v92 : $_v92)] ?? null) : null)) && is_array($_v84) || $_v84 instanceof ArrayAccess ? ($_v84["title"] ?? null) : null)), "truncate", [17, "..."], "method", false, false, false, 73), "html", null, true);
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
        if (CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["menu"] ?? null), ($context["sector"] ?? null), [], "array", false, true, false, 82), "content", [], "array", false, true, false, 82), ($context["item"] ?? null), [], "array", true, true, false, 82)) {
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
        return new Source("", "layout/parts/breadcrumbs.html.twig", "/var/www/html/glpi/templates/layout/parts/breadcrumbs.html.twig");
    }
}
