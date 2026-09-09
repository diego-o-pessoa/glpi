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

/* components/itilobject/layout.html.twig */
class __TwigTemplate_f6b52778000f2538a8de19f8b64183b7 extends Template
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
        $context["rand"] = Twig\Extension\CoreExtension::random($this->env->getCharset());
        // line 34
        $context["main_rand"] = ($context["rand"] ?? null);
        // line 35
        $context["is_helpdesk"] = ($this->extensions['Glpi\Application\View\Extension\SessionExtension']->getCurrentInterface() == "helpdesk");
        // line 36
        $context["show_extra_fields"] = ( !($context["is_helpdesk"] ?? null) || ($context["show_tickets_properties_on_helpdesk"] ?? null));
        // line 37
        yield "
";
        // line 38
        $context["itil_layout"] = $this->extensions['Glpi\Application\View\Extension\SessionExtension']->userPref("itil_layout", true);
        // line 39
        $context["is_collapsed"] = ((((CoreExtension::getAttribute($this->env, $this->source, ($context["itil_layout"] ?? null), "collapsed", [], "array", true, true, false, 39) &&  !(null === (($_v0 = ($context["itil_layout"] ?? null)) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0["collapsed"] ?? null) : null)))) ? ((($_v1 = ($context["itil_layout"] ?? null)) && is_array($_v1) || $_v1 instanceof ArrayAccess ? ($_v1["collapsed"] ?? null) : null)) : (false)) == "true");
        // line 40
        $context["is_expanded"] = ((((CoreExtension::getAttribute($this->env, $this->source, ($context["itil_layout"] ?? null), "expanded", [], "array", true, true, false, 40) &&  !(null === (($_v2 = ($context["itil_layout"] ?? null)) && is_array($_v2) || $_v2 instanceof ArrayAccess ? ($_v2["expanded"] ?? null) : null)))) ? ((($_v3 = ($context["itil_layout"] ?? null)) && is_array($_v3) || $_v3 instanceof ArrayAccess ? ($_v3["expanded"] ?? null) : null)) : (false)) == "true");
        // line 41
        $context["collapsed_cls"] = (((($tmp = ($context["is_collapsed"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("right-collapsed") : (""));
        // line 42
        $context["expanded_cls"] = (((($context["is_expanded"] ?? null) == "true")) ? ("right-expanded") : (""));
        // line 43
        $context["template_preview"] = ((CoreExtension::getAttribute($this->env, $this->source, ($context["params"] ?? null), "template_preview", [], "any", true, true, false, 43)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["params"] ?? null), "template_preview", [], "any", false, false, false, 43), false)) : (false));
        // line 44
        yield "
";
        // line 45
        $context["left_regular_cls"] = "col-lg-8";
        // line 46
        $context["right_regular_cls"] = "col-lg-4";
        // line 47
        yield "
";
        // line 48
        $context["left_expanded_cls"] = "col-xl-5 col-lg-6";
        // line 49
        $context["right_expanded_cls"] = "col-xl-7 col-lg-6";
        // line 50
        yield "
";
        // line 51
        $context["left_side_cls"] = ($context["left_regular_cls"] ?? null);
        // line 52
        $context["right_side_cls"] = ($context["right_regular_cls"] ?? null);
        // line 53
        if ((($tmp = ($context["is_expanded"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 54
            yield "    ";
            $context["left_side_cls"] = ($context["left_expanded_cls"] ?? null);
            // line 55
            yield "    ";
            $context["right_side_cls"] = ($context["right_expanded_cls"] ?? null);
        }
        // line 57
        yield "
";
        // line 58
        $context["can_have_form"] =  !($context["template_preview"] ?? null);
        // line 59
        yield "
<div id=\"itil-object-container\" class=\"mt-n1 ";
        // line 60
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["collapsed_cls"] ?? null), "html", null, true);
        yield " ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["expanded_cls"] ?? null), "html", null, true);
        yield "\">

    ";
        // line 62
        if ((($context["can_have_form"] ?? null) && CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "isNewItem", [], "method", false, false, false, 62))) {
            // line 63
            yield "        ";
            yield Twig\Extension\CoreExtension::include($this->env, $context, "components/itilobject/mainform_open.html.twig");
            yield "
    ";
        }
        // line 65
        yield "
    <div class=\"row d-flex flex-column itil-object\">
        ";
        // line 67
        $context["is_timeline_reversed"] = ($this->extensions['Glpi\Application\View\Extension\SessionExtension']->userPref("timeline_order") == Twig\Extension\CoreExtension::constant("CommonITILObject::TIMELINE_ORDER_REVERSE"));
        // line 68
        yield "        ";
        $context["fl_direction"] = (((CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "isNewItem", [], "method", false, false, false, 68) || ($context["is_timeline_reversed"] ?? null))) ? ("flex-column") : ("flex-column-reverse"));
        // line 69
        yield "        <div class=\"itil-left-side col-12 ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["left_side_cls"] ?? null), "html", null, true);
        yield " order-last order-lg-first pt-2 pe-2 pe-lg-4 d-flex ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["fl_direction"] ?? null), "html", null, true);
        yield " border-top border-4 ";
        yield (((($tmp =  !($context["show_extra_fields"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("w-100") : (""));
        yield "\">
            ";
        // line 70
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "isNewItem", [], "method", false, false, false, 70)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 71
            yield "                ";
            yield Twig\Extension\CoreExtension::include($this->env, $context, "components/itilobject/timeline/new_form.html.twig");
            yield "
            ";
        } else {
            // line 73
            yield "                ";
            yield Twig\Extension\CoreExtension::include($this->env, $context, "components/itilobject/timeline/timeline.html.twig");
            yield "
            ";
        }
        // line 75
        yield "        </div>
        <div class=\"itil-right-side col-12 ";
        // line 76
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["right_side_cls"] ?? null), "html", null, true);
        yield " mt-0 mt-lg-n1 card-footer p-0 rounded-0 ";
        yield (((($tmp = ($context["show_extra_fields"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("") : ("d-none"));
        yield "\">
            ";
        // line 78
        yield "            ";
        if ((($context["can_have_form"] ?? null) &&  !CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "isNewItem", [], "method", false, false, false, 78))) {
            // line 79
            yield "                ";
            yield Twig\Extension\CoreExtension::include($this->env, $context, "components/itilobject/mainform_open.html.twig");
            yield "
            ";
        }
        // line 81
        yield "            ";
        if ((($tmp = ($context["show_extra_fields"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 82
            yield "                ";
            yield Twig\Extension\CoreExtension::include($this->env, $context, "components/itilobject/fields_panel.html.twig");
            yield "
            ";
        }
        // line 84
        yield "            ";
        if ((($context["can_have_form"] ?? null) &&  !CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "isNewItem", [], "method", false, false, false, 84))) {
            // line 85
            yield "                ";
            yield Twig\Extension\CoreExtension::include($this->env, $context, "components/itilobject/mainform_close.html.twig");
            yield "
            ";
        }
        // line 87
        yield "        </div>
    </div>

    ";
        // line 90
        if ((($tmp =  !($context["template_preview"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 91
            yield "        ";
            yield Twig\Extension\CoreExtension::include($this->env, $context, "components/itilobject/footer.html.twig");
            yield "
    ";
        }
        // line 93
        yield "
    ";
        // line 94
        if ((($context["can_have_form"] ?? null) && CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "isNewItem", [], "method", false, false, false, 94))) {
            // line 95
            yield "        ";
            yield Twig\Extension\CoreExtension::include($this->env, $context, "components/itilobject/mainform_close.html.twig");
            yield "
    ";
        }
        // line 97
        yield "</div>

";
        // line 100
        yield "<form method=\"POST\" action=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("CommonITILObject_CommonITILObject::getFormURL"), "html", null, true);
        yield "\" class=\"d-none\" id=\"linked_itilobjects_";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["main_rand"] ?? null), "html", null, true);
        yield "\" data-submit-once>
   <input type=\"hidden\" name=\"_glpi_csrf_token\" value=\"";
        // line 101
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Session::getNewCSRFToken(), "html", null, true);
        yield "\" />
   <input type=\"hidden\" name=\"purge\" value=\"1\" />
   <input type=\"hidden\" name=\"itemtype\" value=\"";
        // line 103
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "getType", [], "method", false, false, false, 103), "html", null, true);
        yield "\" />
   <input type=\"hidden\" name=\"items_id\" value=\"";
        // line 104
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v4 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 104)) && is_array($_v4) || $_v4 instanceof ArrayAccess ? ($_v4["id"] ?? null) : null), "html", null, true);
        yield "\" />
</form>

";
        // line 108
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(["requester", "observer", "assign"]);
        foreach ($context['_seq'] as $context["_key"] => $context["actortype"]) {
            // line 109
            yield "   <form method=\"POST\" action=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "getFormURL", [], "method", false, false, false, 109), "html", null, true);
            yield "\" class=\"d-none\" id=\"addme_as_";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["actortype"], "html", null, true);
            yield "_";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["main_rand"] ?? null), "html", null, true);
            yield "\" data-submit-once>
      <input type=\"hidden\" name=\"_glpi_csrf_token\" value=\"";
            // line 110
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Session::getNewCSRFToken(), "html", null, true);
            yield "\" />
      <input type=\"hidden\" name=\"addme_as_actor\" value=\"1\" />
      <input type=\"hidden\" name=\"actortype\" value=\"";
            // line 112
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["actortype"], "html", null, true);
            yield "\" />
   </form>
";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['actortype'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 115
        yield "
<form name=\"massaction_";
        // line 116
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
        yield "\" id=\"massaction_";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
        yield "\" method=\"post\"
      action=\"";
        // line 117
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path("/front/massiveaction.php"), "html", null, true);
        yield "?_single_item[itemtype]=";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "getType", [], "method", false, false, false, 117), "html", null, true);
        yield "&_single_item[id]=";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "getID", [], "method", false, false, false, 117), "html", null, true);
        yield "\" data-submit-once>
   <div id=\"massive_container_";
        // line 118
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
        yield "\"></div>
   <input type=\"hidden\" name=\"_glpi_csrf_token\" value=\"";
        // line 119
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Session::getNewCSRFToken(), "html", null, true);
        yield "\" />
</form>

<script type=\"text/javascript\">
\$(function() {
   \$(document).on(\"click\", \".switch-panel-width\", function() {
       if (\$(\x27#itil-object-container\x27).hasClass(\x27right-collapsed\x27)) {
           \$(\x27#itil-object-container\x27).removeClass(\x27right-collapsed\x27);
       } else if (\$(\x27.itil-left-side\x27).hasClass(\"";
        // line 127
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["left_regular_cls"] ?? null), "html", null, true);
        yield "\")) {
         // Expand right-side panel
         \$(\x27#itil-object-container\x27).addClass(\x27right-expanded\x27);
         // Left side
         \$(\x27.itil-left-side\x27).removeClass(\"";
        // line 131
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["left_regular_cls"] ?? null), "html", null, true);
        yield "\").addClass(\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["left_expanded_cls"] ?? null), "html", null, true);
        yield "\");
         \$(\x27.itil-footer .timeline-buttons\x27).removeClass(\"";
        // line 132
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["left_regular_cls"] ?? null), "html", null, true);
        yield "\").addClass(\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["left_expanded_cls"] ?? null), "html", null, true);
        yield "\");
         // Right side
         \$(\x27.itil-footer .form-buttons\x27).removeClass(\x27col-lg\x27).addClass(\"";
        // line 134
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["right_expanded_cls"] ?? null), "html", null, true);
        yield "\");
         \$(\x27.itil-right-side\x27).removeClass(\"";
        // line 135
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["right_regular_cls"] ?? null), "html", null, true);
        yield "\").addClass(\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["right_expanded_cls"] ?? null), "html", null, true);
        yield "\");
         // Switcher buttons
         \$(\x27.switch-panel-width i.fas\x27).removeClass(\x27fa-caret-left\x27).addClass(\x27fa-caret-right\x27);
         \$(\x27.itil-right-side .accordion-body:not(.accordion-actors).row .col-12\x27).removeClass(\x27col-12\x27).addClass(\x27col-sm-6\x27);
         \$(\x27#actors .col-12\x27).removeClass(\x27col-12\x27).addClass(\x27col-sm-4\x27);
      } else {
         // Decrease right-side panel
         \$(\x27#itil-object-container\x27).removeClass(\x27right-expanded\x27);
         // Left side
         \$(\x27.itil-left-side\x27).removeClass(\"";
        // line 144
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["left_expanded_cls"] ?? null), "html", null, true);
        yield "\").addClass(\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["left_regular_cls"] ?? null), "html", null, true);
        yield "\");
         \$(\x27.itil-footer .timeline-buttons\x27).removeClass(\"";
        // line 145
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["left_expanded_cls"] ?? null), "html", null, true);
        yield "\").addClass(\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["left_regular_cls"] ?? null), "html", null, true);
        yield "\");
         // Right side
         \$(\x27.itil-footer .form-buttons\x27).removeClass(\"";
        // line 147
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["right_expanded_cls"] ?? null), "html", null, true);
        yield "\").addClass(\x27col-lg\x27);
         \$(\x27.itil-right-side\x27).removeClass(\"";
        // line 148
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["right_expanded_cls"] ?? null), "html", null, true);
        yield "\").addClass(\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["right_regular_cls"] ?? null), "html", null, true);
        yield "\");
         // Switcher buttons
         \$(\x27.switch-panel-width i.fas\x27).removeClass(\x27fa-caret-right\x27).addClass(\x27fa-caret-left\x27);
         \$(\x27.itil-right-side .accordion-body:not(.accordion-actors).row .col-sm-6\x27).removeClass(\x27col-sm-6\x27).addClass(\x27col-12\x27);
         \$(\x27#actors .col-sm-4\x27).removeClass(\x27col-sm-4\x27).addClass(\x27col-12\x27);
      }

      saveFieldPanelState();
   });

    \$(document).on(\"click\", \".collapse-panel\", function() {
       \$(\x27#itil-object-container\x27).addClass(\x27right-collapsed\x27);

        // Hide all accordion item
        \$(\x27#itil-data .accordion-collapse\x27).removeClass(\x27show\x27);
        \$(\x27#itil-data .accordion-button\x27).addClass(\x27collapsed\x27);

       saveFieldPanelState();
    });

    \$(document).on(\"click\", \".right-collapsed .itil-right-side\", function(event) {
        \$(\x27#itil-object-container\x27).removeClass(\x27right-collapsed\x27);
        saveFieldPanelState();
    });

    var myCollapsible = document.getElementById(\x27itil-data\x27);
    if (myCollapsible) {
        myCollapsible.addEventListener(\x27shown.bs.collapse\x27, function () {
            saveFieldPanelState();
        });
        myCollapsible.addEventListener(\x27hidden.bs.collapse\x27, function () {
            saveFieldPanelState();
        });
    }

    var itil_layout = {
        collapsed: false,
        expanded: false,
        items: {}
    };

    var saveFieldPanelState = function() {
        itil_layout.collapsed = \$(\x27#itil-object-container\x27).hasClass(\x27right-collapsed\x27);
        itil_layout.expanded  = \$(\x27#itil-object-container\x27).hasClass(\x27right-expanded\x27);

        \$(\x27#itil-data .accordion-collapse\x27).each(function() {
            var item_shown = \$(this).hasClass(\x27show\x27);
            var item_id    = \$(this).attr(\x27id\x27);

            itil_layout.items[item_id] = item_shown;
        });

        \$.ajax({
            url: CFG_GLPI.root_doc + \x27/ajax/itillayout.php\x27,
            type: \x27POST\x27,
            datatype: \"json\",
            data: {
                \x27itil_layout\x27: itil_layout
            }
        });
    }

    var restoreFieldPanelState = function() {
        \$.each(field_panel_state.items, function(item_id, item_shown) {
            if (item_shown) {
                \$(\x27#\x27 + item_id).addClass(\x27show\x27);
            } else {
                \$(\x27#\x27 + item_id).removeClass(\x27show\x27);
            }
        });
    }
});
</script>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "components/itilobject/layout.html.twig";
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
        return array (  353 => 148,  349 => 147,  342 => 145,  336 => 144,  322 => 135,  318 => 134,  311 => 132,  305 => 131,  298 => 127,  287 => 119,  283 => 118,  275 => 117,  269 => 116,  266 => 115,  257 => 112,  252 => 110,  243 => 109,  239 => 108,  233 => 104,  229 => 103,  224 => 101,  217 => 100,  213 => 97,  207 => 95,  205 => 94,  202 => 93,  196 => 91,  194 => 90,  189 => 87,  183 => 85,  180 => 84,  174 => 82,  171 => 81,  165 => 79,  162 => 78,  156 => 76,  153 => 75,  147 => 73,  141 => 71,  139 => 70,  130 => 69,  127 => 68,  125 => 67,  121 => 65,  115 => 63,  113 => 62,  106 => 60,  103 => 59,  101 => 58,  98 => 57,  94 => 55,  91 => 54,  89 => 53,  87 => 52,  85 => 51,  82 => 50,  80 => 49,  78 => 48,  75 => 47,  73 => 46,  71 => 45,  68 => 44,  66 => 43,  64 => 42,  62 => 41,  60 => 40,  58 => 39,  56 => 38,  53 => 37,  51 => 36,  49 => 35,  47 => 34,  45 => 33,  42 => 32,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "components/itilobject/layout.html.twig", "/var/www/html/glpi/templates/components/itilobject/layout.html.twig");
    }
}
