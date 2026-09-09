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

/* components/kanban/item_panels/default_panel.html.twig */
class __TwigTemplate_082edaf0a8368c595523e03cc859dbc5 extends Template
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
<div class=\"item-details-panel\" data-itemtype=\"";
        // line 33
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["itemtype"] ?? null), "html", null, true);
        yield "\" data-items_id=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["item_fields"] ?? null), "id", [], "any", false, false, false, 33), "html", null, true);
        yield "\">
   <div class=\"card d-flex flex-column h-100\">
      <div class=\"card-header d-block\">
         <h5 class=\"card-title d-flex justify-content-between w-100\">
            <a href=\"";
        // line 37
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeFormPath(($context["itemtype"] ?? null), CoreExtension::getAttribute($this->env, $this->source, ($context["item_fields"] ?? null), "id", [], "any", false, false, false, 37)), "html", null, true);
        yield "\">
               <i class=\"";
        // line 38
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeIcon(($context["itemtype"] ?? null)), "html", null, true);
        yield "\" title=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName(($context["itemtype"] ?? null)), "html", null, true);
        yield "\"></i>
               ";
        // line 39
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["item_fields"] ?? null), "name", [], "any", false, false, false, 39), "html", null, true);
        yield "
            </a>
            <button type=\"button\" class=\"btn-link\"><i class=\"ti ti-x\"></i></button>
         </h5>
         <h6 class=\"card-subtitle\">
            ";
        // line 44
        if ((($tmp = (((CoreExtension::getAttribute($this->env, $this->source, ($context["item_fields"] ?? null), "is_milestone", [], "any", true, true, false, 44) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, ($context["item_fields"] ?? null), "is_milestone", [], "any", false, false, false, 44)))) ? (CoreExtension::getAttribute($this->env, $this->source, ($context["item_fields"] ?? null), "is_milestone", [], "any", false, false, false, 44)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 45
            yield "               <i class=\"ti ti-directions-filled me-2\"></i>";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Milestone"), "html", null, true);
            yield "
            ";
        }
        // line 47
        yield "         </h6>
      </div>
      <div class=\"card-body overflow-auto\">
         ";
        // line 60
        yield "         ";
        yield $this->getTemplateForMacro("macro_print_hook_section", $context, 60, $this->getSourceContext())->macro_print_hook_section(...[Twig\Extension\CoreExtension::constant("Glpi\\Plugin\\Hooks::PRE_KANBAN_PANEL_CONTENT"), ($context["itemtype"] ?? null), CoreExtension::getAttribute($this->env, $this->source, ($context["item_fields"] ?? null), "id", [], "any", false, false, false, 60)]);
        yield "
         ";
        // line 61
        yield $this->getTemplateForMacro("macro_print_hook_section", $context, 61, $this->getSourceContext())->macro_print_hook_section(...[Twig\Extension\CoreExtension::constant("Glpi\\Plugin\\Hooks::PRE_KANBAN_PANEL_MAIN_CONTENT"), ($context["itemtype"] ?? null), CoreExtension::getAttribute($this->env, $this->source, ($context["item_fields"] ?? null), "id", [], "any", false, false, false, 61)]);
        yield "

         ";
        // line 63
        $context["content"] = $this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getSafeHtml(CoreExtension::getAttribute($this->env, $this->source, ($context["item_fields"] ?? null), "content", [], "any", false, false, false, 63));
        // line 64
        yield "         ";
        $context["preview"] = (((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["content"] ?? null)) > 1000)) ? ((Twig\Extension\CoreExtension::slice($this->env->getCharset(), ($context["content"] ?? null), 0, 1000) . " (...)")) : (($context["content"] ?? null)));
        // line 65
        yield "         <div class=\"col-12 mb-3 rich_text_container\">";
        yield $this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getSafeHtml(($context["preview"] ?? null));
        yield "</div>

         ";
        // line 67
        yield $this->getTemplateForMacro("macro_print_hook_section", $context, 67, $this->getSourceContext())->macro_print_hook_section(...[Twig\Extension\CoreExtension::constant("Glpi\\Plugin\\Hooks::POST_KANBAN_PANEL_MAIN_CONTENT"), ($context["itemtype"] ?? null), CoreExtension::getAttribute($this->env, $this->source, ($context["item_fields"] ?? null), "id", [], "any", false, false, false, 67)]);
        yield "

         <h5 class=\"d-flex justify-content-between\">
            <span>";
        // line 70
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Team"), "html", null, true);
        yield "</span>
            <button type=\"button\" class=\"btn-link kanban-item-edit-team\"><i class=\"ti ti-plus\"></i></button>
         </h5>
         ";
        // line 73
        if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(($context["team"] ?? null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 74
            yield "            ";
            $context["item"] = $this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeClass(($context["itemtype"] ?? null));
            // line 75
            yield "            ";
            if ($this->extensions['Glpi\Application\View\Extension\PhpExtension']->isInstanceOf(($context["item"] ?? null), "Glpi\\Features\\TeamworkInterface")) {
                // line 76
                yield "               ";
                $context['_parent'] = $context;
                $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "getTeamRoles", [], "method", false, false, false, 76));
                foreach ($context['_seq'] as $context["_key"] => $context["team_role"]) {
                    // line 77
                    yield "                  ";
                    $context["role_members"] = Twig\Extension\CoreExtension::filter($this->env, $this->env->hasExtension(\Twig\Extension\SandboxExtension::class) && $this->env->getExtension(\Twig\Extension\SandboxExtension::class)->isSandboxed($this->source), ($context["team"] ?? null), function ($__m__) use ($context, $macros) { $context["m"] = $__m__; return (CoreExtension::getAttribute($this->env, $this->source, ($context["m"] ?? null), "role", [], "any", false, false, false, 77) == $context["team_role"]); });
                    // line 78
                    yield "                  ";
                    if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["role_members"] ?? null)) > 0)) {
                        // line 79
                        yield "                     <h5>";
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "getTeamRoleName", [$context["team_role"], Session::getPluralNumber()], "method", false, false, false, 79), "html", null, true);
                        yield "</h5>
                     <ul class=\"list-group team-list\" data-role=\"";
                        // line 80
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["team_role"], "html", null, true);
                        yield "\">
                        ";
                        // line 81
                        $context['_parent'] = $context;
                        $context['_seq'] = CoreExtension::ensureTraversable(($context["role_members"] ?? null));
                        foreach ($context['_seq'] as $context["_key"] => $context["team_member"]) {
                            // line 82
                            yield "                           <li class=\"list-group-item d-flex justify-content-between p-2\" data-itemtype=\"";
                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["team_member"], "itemtype", [], "any", false, false, false, 82), "html", null, true);
                            yield "\"
                               data-items_id=\"";
                            // line 83
                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["team_member"], "items_id", [], "any", false, false, false, 83), "html", null, true);
                            yield "\"
                               data-name=\"";
                            // line 84
                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((CoreExtension::getAttribute($this->env, $this->source, $context["team_member"], "display_name", [], "any", true, true, false, 84)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, $context["team_member"], "display_name", [], "any", false, false, false, 84), CoreExtension::getAttribute($this->env, $this->source, $context["team_member"], "name", [], "any", false, false, false, 84))) : (CoreExtension::getAttribute($this->env, $this->source, $context["team_member"], "name", [], "any", false, false, false, 84))), "html", null, true);
                            yield "\"
                               data-firstname=\"";
                            // line 85
                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["team_member"], "firstname", [], "any", false, false, false, 85), "html", null, true);
                            yield "\"
                               data-realname=\"";
                            // line 86
                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["team_member"], "realname", [], "any", false, false, false, 86), "html", null, true);
                            yield "\">
                              ";
                            // line 88
                            yield "                           </li>
                        ";
                        }
                        $_parent = $context['_parent'];
                        unset($context['_seq'], $context['_key'], $context['team_member'], $context['_parent']);
                        $context = array_intersect_key($context, $_parent) + $_parent;
                        // line 90
                        yield "                     </ul>
                  ";
                    }
                    // line 92
                    yield "               ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_key'], $context['team_role'], $context['_parent']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 93
                yield "            ";
            }
            // line 94
            yield "         ";
        } else {
            // line 95
            yield "            ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("No team members"), "html", null, true);
            yield "
         ";
        }
        // line 97
        yield "         <hr>

         ";
        // line 99
        yield $this->getTemplateForMacro("macro_print_hook_section", $context, 99, $this->getSourceContext())->macro_print_hook_section(...[Twig\Extension\CoreExtension::constant("Glpi\\Plugin\\Hooks::POST_KANBAN_PANEL_CONTENT"), ($context["itemtype"] ?? null), CoreExtension::getAttribute($this->env, $this->source, ($context["item_fields"] ?? null), "id", [], "any", false, false, false, 99)]);
        yield "
      </div>
      <div class=\"card-footer border-top flex-shrink-0 text-center p-3\">
         <a class=\"btn btn-outline w-100\" target=\"_blank\" rel=\"noopener\" href=\"";
        // line 102
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeFormPath(($context["itemtype"] ?? null), CoreExtension::getAttribute($this->env, $this->source, ($context["item_fields"] ?? null), "id", [], "any", false, false, false, 102)), "html", null, true);
        yield "\">
            <span class=\"pr-1\">";
        // line 103
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Open full form"), "html", null, true);
        yield "</span>
         </a>
      </div>
   </div>
</div>
";
        yield from [];
    }

    // line 50
    public function macro_print_hook_section($name = null, $itemtype = null, $items_id = null, ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "itemtype" => $itemtype,
            "items_id" => $items_id,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 51
            yield "            ";
            $context["content"] = $this->extensions['Glpi\Application\View\Extension\PluginExtension']->callPluginHookFunction(($context["name"] ?? null), ["itemtype" =>             // line 52
($context["itemtype"] ?? null), "items_id" =>             // line 53
($context["items_id"] ?? null)], true);
            // line 55
            yield "            ";
            if (( !Twig\Extension\CoreExtension::testEmpty(($context["content"] ?? null)) &&  !Twig\Extension\CoreExtension::testEmpty(Twig\Extension\CoreExtension::trim((((CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "content", [], "array", true, true, false, 55) &&  !(null === (($_v0 = ($context["content"] ?? null)) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0["content"] ?? null) : null)))) ? ((($_v1 = ($context["content"] ?? null)) && is_array($_v1) || $_v1 instanceof ArrayAccess ? ($_v1["content"] ?? null) : null)) : ("")))))) {
                // line 56
                yield "               ";
                yield (($_v2 = ($context["content"] ?? null)) && is_array($_v2) || $_v2 instanceof ArrayAccess ? ($_v2["content"] ?? null) : null);
                yield "
               <hr>
            ";
            }
            // line 59
            yield "         ";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "components/kanban/item_panels/default_panel.html.twig";
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
        return array (  254 => 59,  247 => 56,  244 => 55,  242 => 53,  241 => 52,  239 => 51,  225 => 50,  214 => 103,  210 => 102,  204 => 99,  200 => 97,  194 => 95,  191 => 94,  188 => 93,  182 => 92,  178 => 90,  171 => 88,  167 => 86,  163 => 85,  159 => 84,  155 => 83,  150 => 82,  146 => 81,  142 => 80,  137 => 79,  134 => 78,  131 => 77,  126 => 76,  123 => 75,  120 => 74,  118 => 73,  112 => 70,  106 => 67,  100 => 65,  97 => 64,  95 => 63,  90 => 61,  85 => 60,  80 => 47,  74 => 45,  72 => 44,  64 => 39,  58 => 38,  54 => 37,  45 => 33,  42 => 32,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "components/kanban/item_panels/default_panel.html.twig", "/var/www/html/glpi/templates/components/kanban/item_panels/default_panel.html.twig");
    }
}
