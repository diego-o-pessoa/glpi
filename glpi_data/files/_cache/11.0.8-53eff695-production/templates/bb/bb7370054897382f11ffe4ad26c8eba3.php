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

/* layout/parts/saved_searches.html.twig */
class __TwigTemplate_e8d1686e15ee4eb22c5c95c6c6b6bc63 extends Template
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
        $context["global_pinned"] = $this->extensions['Glpi\Application\View\Extension\SessionExtension']->userPref("savedsearches_pinned", true);
        // line 34
        $context["pinned"] = ((((CoreExtension::getAttribute($this->env, $this->source, ($context["global_pinned"] ?? null), ($context["itemtype"] ?? null), [], "array", true, true, false, 34) &&  !(null === (($_v0 = ($context["global_pinned"] ?? null)) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0[(($_v1 = ($context["itemtype"] ?? null)) instanceof \Stringable ? (string) $_v1 : $_v1)] ?? null) : null)))) ? ((($_v2 = ($context["global_pinned"] ?? null)) && is_array($_v2) || $_v2 instanceof ArrayAccess ? ($_v2[(($_v3 = ($context["itemtype"] ?? null)) instanceof \Stringable ? (string) $_v3 : $_v3)] ?? null) : null)) : ("0")) == "1");
        // line 35
        $context["clean_itemtype"] = CoreExtension::getAttribute($this->env, $this->source, $this->extensions['Twig\Extra\String\StringExtension']->createUnicodeString(Twig\Extension\CoreExtension::lower($this->env->getCharset(), ($context["itemtype"] ?? null))), "replace", ["\\", "_"], "method", false, false, false, 35);
        // line 36
        $context["rand"] = Twig\Extension\CoreExtension::random($this->env->getCharset());
        // line 37
        yield "
<div class=\"card col-2 d-flex flex-column responsive-toggle ";
        // line 38
        yield (((($tmp = ($context["pinned"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("pinned") : ("d-none"));
        yield " saved-searches-panel ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["clean_itemtype"] ?? null), "html", null, true);
        yield "\"
     id=\"saved-searches-panel-";
        // line 39
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
        yield "\">
   <div class=\"card-header d-flex flex-nowrap pe-0 align-items-center text-muted\">
      <i class=\"ti ti-star\"></i>&nbsp;
      <span class=\"text-truncate\">
         ";
        // line 43
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(_n("Saved search", "Saved searches", 2), "html", null, true);
        yield "
      </span>

      <li class=\"ms-auto btn-list me-1 flex-nowrap\">
         <a href=\"";
        // line 47
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path("front/savedsearch.php"), "html", null, true);
        yield "\" class=\"btn btn-sm btn-icon btn-ghost-secondary\"
            data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\"
            title=\"";
        // line 49
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Manage all saved searches"), "html", null, true);
        yield "\">
            <i class=\"ti ti-settings\"></i>
         </a>
         <button class=\"btn btn-sm btn-icon btn-ghost-secondary ms-1 d-none d-md-block pin-saved-searches-panel\"
                 data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\"
                 title=\"";
        // line 54
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Pin this panel for the current page"), "html", null, true);
        yield "\">
            <i class=\"ti ti-pinned\"></i>
         </button>
         <button class=\"btn btn-sm btn-icon btn-ghost-secondary ms-1 close-saved-searches-panel\"
                 data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\"
                 title=\"";
        // line 59
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Close the panel"), "html", null, true);
        yield "\">
            <i class=\"ti ti-x\"></i>
         </button>
      </li>
   </div>

   <div class=\"saved-searches-tabs\">
      <ul class=\"nav nav-tabs border-0\" data-bs-toggle=\"tabs\">
         <li class=\"nav-item\">
            <a class=\"nav-link active\" data-bs-target=\"#itemtype-filtered\" data-bs-toggle=\"tab\"
               href=\"";
        // line 69
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path(("ajax/savedsearch.php?action=display_mine&itemtype=" . ($context["itemtype"] ?? null))), "html", null, true);
        yield "\">
               ";
        // line 70
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName(($context["itemtype"] ?? null)), "html", null, true);
        yield "
            </a>
         </li>
         <li class=\"nav-item\">
            <a class=\"nav-link\" data-bs-target=\"#all-savedsearches\" data-bs-toggle=\"tab\"
               href=\"";
        // line 75
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path(("ajax/savedsearch.php?action=display_mine&inverse=true&itemtype=" . ($context["itemtype"] ?? null))), "html", null, true);
        yield "\">
               ";
        // line 76
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Others"), "html", null, true);
        yield "
            </a>
         </li>
      </ul>
      <div class=\"saved-searches-panel-content tab-content\">
         <div class=\"list-group list-group-flush list-group-hoverable saved-searches-panel-lists tab-pane show active\" id=\"itemtype-filtered\">
             <span class=\"spinner-border m-3\" role=\"status\" aria-hidden=\"true\"></span>
         </div>

         <div class=\"list-group list-group-flush list-group-hoverable saved-searches-panel-lists tab-pane\" id=\"all-savedsearches\">
             <span class=\"spinner-border m-3\" role=\"status\" aria-hidden=\"true\"></span>
         </div>
      </div>
   </div>

   <div class=\"card-footer\">
      <div class=\"input-group input-group-flat filter_savedsearch\">
         <input type=\"text\" name=\"saved_searches_filter_list_input\" class=\"form-control form-control-sm\"
                placeholder=\"";
        // line 94
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Filter list"), "html", null, true);
        yield "\" />
         <span class=\"input-group-text\">
            <a href=\"#\" class=\"link-secondary clear-text\" role=\"button\" title=\"Clear search\">
               <i class=\"ti ti-x fs-5\"></i>
            </a>
         </span>
      </div>
   </div>
</div>

<script type=\"text/javascript\">
\$(function() {
   // init tabs
   \$(\x27#saved-searches-panel-";
        // line 107
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
        yield " a[data-bs-toggle=\"tab\"]\x27).on(\x27shown.bs.tab\x27, function(e) {
      if (\$(this).is(\":hidden\")) {
         return;
      }
      e.preventDefault();

      var tablink = \$(this);
      var url     = tablink.attr(\x27href\x27);
      var target  = tablink.attr(\x27data-bs-target\x27);
      var index   = tablink.closest(\x27.nav-item\x27).index();

      \$.get(url, function(data) {
         \$(target).html(data);

         displayAjaxMessageAfterRedirect();
      });
   });
   // load initial tab
   \$(\x27#saved-searches-panel-";
        // line 125
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
        yield " a[data-bs-target]:first\x27).trigger(\x27shown.bs.tab\x27);

   // toggle panel
   \$(document).on(\x27click\x27, \x27.show-saved-searches\x27, function() {
      var clean_itemtype = \$(this).data(\x27itemtype\x27).toLowerCase().replaceAll(\x27\\\\\x27, \x27_\x27);
      \$(\".saved-searches-panel.\" + clean_itemtype)
         .toggleClass(\x27d-none\x27)
         .toggleClass(\x27responsive-toggle\x27);

      \$(\x27#saved-searches-panel-";
        // line 134
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
        yield " a[data-bs-toggle=\"tab\"]\x27).trigger(\x27shown.bs.tab\x27);
   });

   // close panel
   \$(document).on(\x27click\x27, \x27.close-saved-searches-panel\x27, function() {
      \$(this).closest(\".saved-searches-panel\")
         .addClass(\x27d-none\x27)
         .toggleClass(\x27responsive-toggle\x27);
   });

   // set default to a list
   \$(document).on(\x27click\x27, \x27.mark-default\x27, function() {
      var line = \$(this).closest(\x27.search-line\x27);
      var list = line.closest(\x27.saved-searches-panel-lists\x27);
      var id   = line.data(\x27id\x27);
      var to_set_default  = \$(this).hasClass(\x27ti-star-filled\x27) ? 0 : 1;

      // Make all mark default icons unmarked
      list.find(\".search-line .mark-default\")
          .removeClass(\x27ti-star-filled\x27)
          .addClass(\x27ti-star\x27)
          .parent()
            .addClass(\x27list-item-actions\x27);

      if (to_set_default) {
         \$(this)
            .removeClass(\x27ti-star\x27)
            .addClass(\x27ti-star-filled\x27)
            .parent()
               .removeClass(\x27list-item-actions\x27);
      }

      \$.ajax({
         url: CFG_GLPI.root_doc + \x27/ajax/savedsearch.php\x27,
         type: \x27GET\x27,
         data: {
            mark_default: to_set_default,
            id: id,
         }
      });
   });

   // pin panel on the left
   \$(document).on(\x27click\x27, \x27.pin-saved-searches-panel\x27, function(e) {
      e.preventDefault();
      var pin_button = \$(this);

      \$.ajax({
         url: CFG_GLPI.root_doc + \x27/ajax/pin_savedsearches.php\x27,
         type: \x27POST\x27,
         data: {
            itemtype: \x27";
        // line 185
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["itemtype"] ?? null), "html", null, true);
        yield "\x27
         },
         success: function(data) {
            if (data.success === true) {
               pin_button.closest(\".saved-searches-panel\").toggleClass(\x27pinned\x27);
            }
         }
      });
   });

   // filter list
   \$(document).on(\x27keyup\x27, \x27.filter_savedsearch input\x27, function(key) {
      var _this = \$(this);
      typewatch(function () {
         var searchtext = _this.val() + \x27\x27;
         var searchparts = searchtext.toLowerCase().split(/\\s+/);
         var _rows = _this
            .closest(\x27.card-footer\x27)
            .siblings(\x27.saved-searches-tabs\x27)
            .find(\x27.saved-searches-panel-content .savedsearches-item\x27);
         _rows.each(function() {
            var _row = \$(this);
            var rowtext = _row.text().toLowerCase();

            var show = true;

            for (var i = 0; i < searchparts.length; i++) {
               if (rowtext.indexOf(searchparts[i]) == -1) {
                  show = false;
                  break;
               }
            }

            if (show) {
               _row.toggleClass(\x27d-none\x27, false);
            } else {
               _row.toggleClass(\x27d-none\x27, true);
            }
         });
      }, 250);
   });

   // clear list
   \$(document).on(\x27click\x27, \x27.filter_savedsearch .clear-text\x27, function() {
      \$(this).closest(\x27.filter_savedsearch\x27).find(\x27input\x27).val(\"\").trigger(\x27keyup\x27);
   });
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
        return "layout/parts/saved_searches.html.twig";
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
        return array (  250 => 185,  196 => 134,  184 => 125,  163 => 107,  147 => 94,  126 => 76,  122 => 75,  114 => 70,  110 => 69,  97 => 59,  89 => 54,  81 => 49,  76 => 47,  69 => 43,  62 => 39,  56 => 38,  53 => 37,  51 => 36,  49 => 35,  47 => 34,  45 => 33,  42 => 32,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "layout/parts/saved_searches.html.twig", "/var/www/html/glpi/templates/layout/parts/saved_searches.html.twig");
    }
}
