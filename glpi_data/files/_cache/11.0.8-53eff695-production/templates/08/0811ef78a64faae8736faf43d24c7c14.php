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

/* components/itilobject/add_items.html.twig */
class __TwigTemplate_43607a0e66d1da7660d90e26b70f8373 extends Template
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
<div id=\"itemAddForm";
        // line 33
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
        yield "\">
   ";
        // line 34
        if ((($tmp = ($context["can_edit"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 35
            yield "      ";
            yield ($context["my_items_dropdown"] ?? null);
            yield "
      ";
            // line 36
            yield ($context["all_items_dropdown"] ?? null);
            yield "
      <a href=\"javascript:itemAction";
            // line 37
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
            yield "(\x27add\x27);\" class=\"btn btn-sm btn-outline-secondary\">
         <i class=\"ti ti-plus\"></i>
         <span>";
            // line 39
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(_x("button", "Add"), "html", null, true);
            yield "</span>
      </a>
   ";
        }
        // line 42
        yield "
   ";
        // line 43
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["items_to_add"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["item_to_add"]) {
            // line 44
            yield "      ";
            yield $context["item_to_add"];
            yield "
   ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['item_to_add'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 46
        yield "
   ";
        // line 47
        if ((($context["count"] ?? null) == 0)) {
            // line 48
            yield "      <input type=\"hidden\" value=\"0\" name=\"items_id\">
   ";
        }
        // line 50
        yield "
   ";
        // line 51
        if (((CoreExtension::getAttribute($this->env, $this->source, ($context["params"] ?? null), "id", [], "any", false, false, false, 51) > 0) && (($context["usedcount"] ?? null) != ($context["count"] ?? null)))) {
            // line 52
            yield "      <i>";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::sprintf(_n("%1\$s item not saved", "%1\$s items not saved", (($context["count"] ?? null) - ($context["usedcount"] ?? null))), (($context["count"] ?? null) - ($context["usedcount"] ?? null))), "html", null, true);
            yield "</i>
   ";
        }
        // line 54
        yield "   ";
        if (((CoreExtension::getAttribute($this->env, $this->source, ($context["params"] ?? null), "id", [], "any", false, false, false, 54) > 0) && (($context["usedcount"] ?? null) > 5))) {
            // line 55
            yield "      <i><a href=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeFormPath(($context["itil_class"] ?? null), CoreExtension::getAttribute($this->env, $this->source, ($context["params"] ?? null), "id", [], "any", false, false, false, 55)), "html", null, true);
            yield "&amp;forcetab=";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["item_class"] ?? null), "html", null, true);
            yield "\$1\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((((__("Display all items") . "(") . ($context["usedcount"] ?? null)) . ")"), "html", null, true);
            yield "</a></i>
   ";
        }
        // line 57
        yield "   <script>
      function refreshItemCounter";
        // line 58
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
        yield "() {
         const item_form = \$(\"#itemAddForm";
        // line 59
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
        yield "\");
         let item_count = item_form.find(\x27input[type=\"hidden\"][name^=\"items_id[\"]\x27).length;
         item_form.closest(\x27.accordion-item\x27).find(\x27.item-counter\x27).text(item_count);
      }

      function itemAction";
        // line 64
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
        yield "(action, itemtype, items_id) {
          if (itemtype === undefined && items_id === undefined) {
              const my_items_dropdown = \$(\x27#dropdown_my_items";
        // line 66
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
        yield "\x27);
              if (my_items_dropdown.length > 0) {
                 const val = my_items_dropdown.val();
                 if (val && val.includes(\x27_\x27)) {
                    itemtype = val.split(\x27_\x27)[0];
                    items_id = val.split(\x27_\x27)[1];
                 }
              }
              if (itemtype === undefined && items_id === undefined) {
                  const dropdown_itemtype = \$(\x27#dropdown_itemtype";
        // line 75
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
        yield "\x27);
                  const dropdown_items_id = \$(\x27#dropdown_add_items_id";
        // line 76
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
        yield "\x27);
                  if (dropdown_itemtype.length > 0 && dropdown_items_id.length > 0) {
                      itemtype = dropdown_itemtype.val();
                      items_id = dropdown_items_id.val();
                  }
              }
          }
         if (!itemtype || !items_id) {
            glpi_toast_error(";
        // line 84
        yield json_encode(__("Please select an item to add"));
        yield ");
            return;
         }
         \$.ajax({
            method: \x27POST\x27,
            url: CFG_GLPI.root_doc + \x27/ajax/";
        // line 89
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::lower($this->env->getCharset(), ($context["item_class"] ?? null)), "html", null, true);
        yield ".php\x27,
            dataType: \x27html\x27,
            data: {
               \x27action\x27: action,
               \x27rand\x27: ";
        // line 93
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
        yield ",
               \x27params\x27: ";
        // line 94
        yield json_encode(($context["opt"] ?? null));
        yield ",
               \x27my_items\x27: \$(\x27#dropdown_my_items";
        // line 95
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
        yield "\x27).val(),
               \x27itemtype\x27: itemtype,
               \x27items_id\x27: items_id,
            },
            success: function(response) {
               \$(\"#itemAddForm";
        // line 100
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
        yield "\").replaceWith(response);
            }
         });
      }
      refreshItemCounter";
        // line 104
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
        yield "();
   </script>
</div>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "components/itilobject/add_items.html.twig";
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
        return array (  205 => 104,  198 => 100,  190 => 95,  186 => 94,  182 => 93,  175 => 89,  167 => 84,  156 => 76,  152 => 75,  140 => 66,  135 => 64,  127 => 59,  123 => 58,  120 => 57,  110 => 55,  107 => 54,  101 => 52,  99 => 51,  96 => 50,  92 => 48,  90 => 47,  87 => 46,  78 => 44,  74 => 43,  71 => 42,  65 => 39,  60 => 37,  56 => 36,  51 => 35,  49 => 34,  45 => 33,  42 => 32,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "components/itilobject/add_items.html.twig", "/var/www/html/glpi/templates/components/itilobject/add_items.html.twig");
    }
}
