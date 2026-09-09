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

/* components/kanban/kanban.html.twig */
class __TwigTemplate_17ee0f0360dcbef7a6742568b755a0fb extends Template
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
        $macros["modals"] = $this->macros["modals"] = $this->load("components/form/modals_macros.html.twig", 33)->unwrap();
        // line 34
        yield "
";
        // line 35
        if ((($tmp =  !array_key_exists("rights", $context)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 36
            yield "   ";
            $context["rights"] = ["create_item" => false, "delete_item" => false, "create_column" => false, "modify_view" => false, "order_card" => false, "create_card_limited_columns" => [0]];
        }
        // line 45
        yield "
<div id=\"kanban-app\"></div>
";
        // line 47
        yield $macros["modals"]->getTemplateForMacro("macro_modal", $context, 47, $this->getSourceContext())->macro_modal(...["", "", ["id" => "kanban-modal"]]);
        // line 49
        yield "

";
        // line 51
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["supported_itemtypes"] ?? null));
        foreach ($context['_seq'] as $context["supported_itemtype"] => $context["info"]) {
            // line 52
            yield "   <template id=\"kanban-teammember-item-dropdown-";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["supported_itemtype"], "html", null, true);
            yield "\">
   </template>
";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['supported_itemtype'], $context['info'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 55
        yield "
<script>
   \$(function(){
      // Create Kanban
      window.Vue.createApp(window.Vue.components[\x27Kanban/KanbanApp\x27].component, {
          element_id: \x27";
        // line 60
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("kanban_id", $context)) ? (Twig\Extension\CoreExtension::default(($context["kanban_id"] ?? null), "kanban")) : ("kanban")), "html", null, true);
        yield "\x27,
          rights: ";
        // line 61
        yield json_encode(($context["rights"] ?? null));
        yield ",
          supported_itemtypes: ";
        // line 62
        yield json_encode(($context["supported_itemtypes"] ?? null));
        yield ",
          column_field: ";
        // line 63
        yield json_encode(($context["column_field"] ?? null));
        yield ",
          background_refresh_interval: ";
        // line 64
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("background_refresh_interval", $context)) ? (Twig\Extension\CoreExtension::default(($context["background_refresh_interval"] ?? null), $this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpirefresh_views"))) : ($this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpirefresh_views"))), "html", null, true);
        yield ",
          item: ";
        // line 65
        yield json_encode(($context["item"] ?? null));
        yield ",
          supported_filters: ";
        // line 66
        yield json_encode(($context["supported_filters"] ?? null));
        yield ",
          display_initials: ";
        // line 67
        yield (((($tmp = $this->extensions['Glpi\Application\View\Extension\ConfigExtension']->getEntityConfig("display_users_initials", $this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpiactive_entity"))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("true") : ("false"));
        yield ",
      }).mount(\x27#kanban-app\x27);
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
        return "components/kanban/kanban.html.twig";
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
        return array (  115 => 67,  111 => 66,  107 => 65,  103 => 64,  99 => 63,  95 => 62,  91 => 61,  87 => 60,  80 => 55,  70 => 52,  66 => 51,  62 => 49,  60 => 47,  56 => 45,  52 => 36,  50 => 35,  47 => 34,  45 => 33,  42 => 32,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "components/kanban/kanban.html.twig", "/var/www/html/glpi/templates/components/kanban/kanban.html.twig");
    }
}
