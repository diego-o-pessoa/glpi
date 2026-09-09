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

/* components/user/info_card.html.twig */
class __TwigTemplate_0ae007be3b10ce317718a4221ce8b9f0 extends Template
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
        $context["enable_anonymization"] = (((array_key_exists("enable_anonymization", $context) &&  !(null === $context["enable_anonymization"]))) ? ($context["enable_anonymization"]) : (false));
        // line 34
        yield "
<div class=\"p-0 user-info-card\">
   <div class=\"row\">
      <div class=\"col-auto pt-1\">
         ";
        // line 38
        if ((($tmp = (($_v0 = ($context["user"] ?? null)) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0["id"] ?? null) : null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 39
            yield "            ";
            yield Twig\Extension\CoreExtension::include($this->env, $context, "components/user/picture.html.twig", ["users_id" => (($_v1 =             // line 40
($context["user"] ?? null)) && is_array($_v1) || $_v1 instanceof ArrayAccess ? ($_v1["id"] ?? null) : null), "user_object" => (((            // line 41
array_key_exists("user_object", $context) &&  !(null === $context["user_object"]))) ? ($context["user_object"]) : (null)), "enable_anonymization" =>             // line 42
($context["enable_anonymization"] ?? null)]);
            // line 43
            yield "
         ";
        }
        // line 45
        yield "      </div>
      <div class=\"col ms-2\">
         <h4 class=\"card-title mb-1\">
            ";
        // line 48
        if ((($tmp = (($_v2 = ($context["user"] ?? null)) && is_array($_v2) || $_v2 instanceof ArrayAccess ? ($_v2["id"] ?? null) : null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 49
            yield "               <a href=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeFormPath("User", (($_v3 = ($context["user"] ?? null)) && is_array($_v3) || $_v3 instanceof ArrayAccess ? ($_v3["id"] ?? null) : null)), "html", null, true);
            yield "\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v4 = ($context["user"] ?? null)) && is_array($_v4) || $_v4 instanceof ArrayAccess ? ($_v4["user_name"] ?? null) : null), "html", null, true);
            yield "</a>
            ";
        } else {
            // line 51
            yield "               ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v5 = ($context["user"] ?? null)) && is_array($_v5) || $_v5 instanceof ArrayAccess ? ($_v5["user_name"] ?? null) : null), "html", null, true);
            yield "
            ";
        }
        // line 53
        yield "         </h4>

         <div class=\"text-muted d-grid gap-y-1\" style=\"grid-template-columns: auto 1fr; column-gap: 0.25rem; align-items: center;\">
            ";
        // line 56
        if ((CoreExtension::getAttribute($this->env, $this->source, ($context["user"] ?? null), "login", [], "any", true, true, false, 56) &&  !Twig\Extension\CoreExtension::testEmpty((($_v6 = ($context["user"] ?? null)) && is_array($_v6) || $_v6 instanceof ArrayAccess ? ($_v6["login"] ?? null) : null)))) {
            // line 57
            yield "                <i class=\"ti ti-id-badge\"></i>
                <span>";
            // line 58
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v7 = ($context["user"] ?? null)) && is_array($_v7) || $_v7 instanceof ArrayAccess ? ($_v7["login"] ?? null) : null), "html", null, true);
            yield "</span>
            ";
        }
        // line 60
        yield "            ";
        if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), (($_v8 = ($context["user"] ?? null)) && is_array($_v8) || $_v8 instanceof ArrayAccess ? ($_v8["email"] ?? null) : null)) > 0)) {
            // line 61
            yield "               <i class=\"ti ti-mail\"></i>
               <a href=\"mailto:";
            // line 62
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v9 = ($context["user"] ?? null)) && is_array($_v9) || $_v9 instanceof ArrayAccess ? ($_v9["email"] ?? null) : null), "html", null, true);
            yield "\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v10 = ($context["user"] ?? null)) && is_array($_v10) || $_v10 instanceof ArrayAccess ? ($_v10["email"] ?? null) : null), "html", null, true);
            yield "</a>
            ";
        }
        // line 64
        yield "            ";
        if (( !Twig\Extension\CoreExtension::testEmpty((($_v11 = ($context["user"] ?? null)) && is_array($_v11) || $_v11 instanceof ArrayAccess ? ($_v11["phone"] ?? null) : null)) ||  !Twig\Extension\CoreExtension::testEmpty((($_v12 = ($context["user"] ?? null)) && is_array($_v12) || $_v12 instanceof ArrayAccess ? ($_v12["phone2"] ?? null) : null)))) {
            // line 65
            yield "               <i class=\"ti ti-phone\"></i>
               <span class=\"d-flex align-items-center flex-wrap gap-1\">
                  ";
            // line 67
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty((($_v13 = ($context["user"] ?? null)) && is_array($_v13) || $_v13 instanceof ArrayAccess ? ($_v13["phone"] ?? null) : null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 68
                yield "                     <a href=\"tel:";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v14 = ($context["user"] ?? null)) && is_array($_v14) || $_v14 instanceof ArrayAccess ? ($_v14["phone"] ?? null) : null), "html", null, true);
                yield "\">";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v15 = ($context["user"] ?? null)) && is_array($_v15) || $_v15 instanceof ArrayAccess ? ($_v15["phone"] ?? null) : null), "html", null, true);
                yield "</a>
                  ";
            }
            // line 70
            yield "                  ";
            if (( !Twig\Extension\CoreExtension::testEmpty((($_v16 = ($context["user"] ?? null)) && is_array($_v16) || $_v16 instanceof ArrayAccess ? ($_v16["phone"] ?? null) : null)) &&  !Twig\Extension\CoreExtension::testEmpty((($_v17 = ($context["user"] ?? null)) && is_array($_v17) || $_v17 instanceof ArrayAccess ? ($_v17["phone2"] ?? null) : null)))) {
                // line 71
                yield "                     <span> - </span>
                  ";
            }
            // line 73
            yield "                  ";
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty((($_v18 = ($context["user"] ?? null)) && is_array($_v18) || $_v18 instanceof ArrayAccess ? ($_v18["phone2"] ?? null) : null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 74
                yield "                     <a href=\"tel:";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v19 = ($context["user"] ?? null)) && is_array($_v19) || $_v19 instanceof ArrayAccess ? ($_v19["phone2"] ?? null) : null), "html", null, true);
                yield "\">";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v20 = ($context["user"] ?? null)) && is_array($_v20) || $_v20 instanceof ArrayAccess ? ($_v20["phone2"] ?? null) : null), "html", null, true);
                yield "</a>
                  ";
            }
            // line 76
            yield "               </span>
            ";
        }
        // line 78
        yield "            ";
        if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), (($_v21 = ($context["user"] ?? null)) && is_array($_v21) || $_v21 instanceof ArrayAccess ? ($_v21["mobile"] ?? null) : null)) > 0)) {
            // line 79
            yield "               <i class=\"ti ti-device-mobile\"></i>
               <a href=\"tel:";
            // line 80
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v22 = ($context["user"] ?? null)) && is_array($_v22) || $_v22 instanceof ArrayAccess ? ($_v22["mobile"] ?? null) : null), "html", null, true);
            yield "\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v23 = ($context["user"] ?? null)) && is_array($_v23) || $_v23 instanceof ArrayAccess ? ($_v23["mobile"] ?? null) : null), "html", null, true);
            yield "</a>
            ";
        }
        // line 82
        yield "            ";
        if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), (($_v24 = ($context["user"] ?? null)) && is_array($_v24) || $_v24 instanceof ArrayAccess ? ($_v24["registration_number"] ?? null) : null)) > 0)) {
            // line 83
            yield "               <i class=\"ti ti-id-badge-2\"></i>
               <span>";
            // line 84
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v25 = ($context["user"] ?? null)) && is_array($_v25) || $_v25 instanceof ArrayAccess ? ($_v25["registration_number"] ?? null) : null), "html", null, true);
            yield "</span>
            ";
        }
        // line 86
        yield "            ";
        if (((($_v26 = ($context["user"] ?? null)) && is_array($_v26) || $_v26 instanceof ArrayAccess ? ($_v26["locations_id"] ?? null) : null) > 0)) {
            // line 87
            yield "               <i class=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeIcon("Location"), "html", null, true);
            yield "\"></i>
               <span title=\"";
            // line 88
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("Location"), "html", null, true);
            yield "\" data-bs-toggle=\"tooltip\" data-bs-placement=\"top\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemName("Location", (($_v27 = ($context["user"] ?? null)) && is_array($_v27) || $_v27 instanceof ArrayAccess ? ($_v27["locations_id"] ?? null) : null)), "html", null, true);
            yield "</span>
            ";
        }
        // line 90
        yield "            ";
        if (((($_v28 = ($context["user"] ?? null)) && is_array($_v28) || $_v28 instanceof ArrayAccess ? ($_v28["usertitles_id"] ?? null) : null) > 0)) {
            // line 91
            yield "               <i class=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeIcon("UserTitle"), "html", null, true);
            yield "\"></i>
               <span title=\"";
            // line 92
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(_x("person", "Title"), "html", null, true);
            yield "\" data-bs-toggle=\"tooltip\" data-bs-placement=\"top\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemName("UserTitle", (($_v29 = ($context["user"] ?? null)) && is_array($_v29) || $_v29 instanceof ArrayAccess ? ($_v29["usertitles_id"] ?? null) : null)), "html", null, true);
            yield "</span>
            ";
        }
        // line 94
        yield "            ";
        if (((($_v30 = ($context["user"] ?? null)) && is_array($_v30) || $_v30 instanceof ArrayAccess ? ($_v30["usercategories_id"] ?? null) : null) > 0)) {
            // line 95
            yield "               <i class=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeIcon("UserCategory"), "html", null, true);
            yield "\"></i>
               <span title=\"";
            // line 96
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(_n("Category", "Categories", 1), "html", null, true);
            yield "\" data-bs-toggle=\"tooltip\" data-bs-placement=\"top\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemName("UserCategory", (($_v31 = ($context["user"] ?? null)) && is_array($_v31) || $_v31 instanceof ArrayAccess ? ($_v31["usercategories_id"] ?? null) : null)), "html", null, true);
            yield "</span>
            ";
        }
        // line 98
        yield "            ";
        if (((($_v32 = ($context["user"] ?? null)) && is_array($_v32) || $_v32 instanceof ArrayAccess ? ($_v32["groups_id"] ?? null) : null) > 0)) {
            // line 99
            yield "               <i class=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeIcon("Group"), "html", null, true);
            yield "\"></i>
               <span title=\"";
            // line 100
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Default group"), "html", null, true);
            yield "\" data-bs-toggle=\"tooltip\" data-bs-placement=\"top\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemName("Group", (($_v33 = ($context["user"] ?? null)) && is_array($_v33) || $_v33 instanceof ArrayAccess ? ($_v33["groups_id"] ?? null) : null)), "html", null, true);
            yield "</span>
            ";
        }
        // line 102
        yield "         </div>
      </div>

      ";
        // line 105
        if ((($tmp = (((array_key_exists("can_edit", $context) &&  !(null === $context["can_edit"]))) ? ($context["can_edit"]) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 106
            yield "         <div class=\"col\">
            <a class=\"btn btn-icon btn-sm btn-outline-secondary\" href=\"";
            // line 107
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path("front/preference.php"), "html", null, true);
            yield "\"
               title=\"";
            // line 108
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Edit"), "html", null, true);
            yield "\" data-bs-toggle=\"tooltip\" data-bs-placement=\"right\">
               <i class=\"ti ti-user-edit\"></i>
            </a>
         </div>
      ";
        }
        // line 113
        yield "   </div>
</div>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "components/user/info_card.html.twig";
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
        return array (  259 => 113,  251 => 108,  247 => 107,  244 => 106,  242 => 105,  237 => 102,  230 => 100,  225 => 99,  222 => 98,  215 => 96,  210 => 95,  207 => 94,  200 => 92,  195 => 91,  192 => 90,  185 => 88,  180 => 87,  177 => 86,  172 => 84,  169 => 83,  166 => 82,  159 => 80,  156 => 79,  153 => 78,  149 => 76,  141 => 74,  138 => 73,  134 => 71,  131 => 70,  123 => 68,  121 => 67,  117 => 65,  114 => 64,  107 => 62,  104 => 61,  101 => 60,  96 => 58,  93 => 57,  91 => 56,  86 => 53,  80 => 51,  72 => 49,  70 => 48,  65 => 45,  61 => 43,  59 => 42,  58 => 41,  57 => 40,  55 => 39,  53 => 38,  47 => 34,  45 => 33,  42 => 32,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "components/user/info_card.html.twig", "/var/www/html/glpi/templates/components/user/info_card.html.twig");
    }
}
