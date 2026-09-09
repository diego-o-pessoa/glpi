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

/* pages/assistance/planning/single_filter.html.twig */
class __TwigTemplate_73c6fe86ae9282861c8e3d2119811678 extends Template
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
        $macros["inputs"] = $this->macros["inputs"] = $this->load("components/form/basic_inputs_macros.html.twig", 33)->unwrap();
        // line 34
        yield "<li event_type=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["filter_data"] ?? null), "type", [], "any", false, false, false, 34), "html", null, true);
        yield "\" event_name=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["filter_key"] ?? null), "html", null, true);
        yield "\" class=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["filter_data"] ?? null), "type", [], "any", false, false, false, 34), "html", null, true);
        yield " ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["expanded"] ?? null), "html", null, true);
        yield "\">
    <input type=\"checkbox\" id=\"";
        // line 35
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["filter_key"] ?? null), "html", null, true);
        yield "\" name=\"filters[]\" class=\"form-check-input\" value=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["filter_key"] ?? null), "html", null, true);
        yield "\" ";
        yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["filter_data"] ?? null), "display", [], "any", false, false, false, 35)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("checked") : (""));
        yield "/>
    ";
        // line 36
        if ((CoreExtension::getAttribute($this->env, $this->source, ($context["filter_data"] ?? null), "type", [], "any", false, false, false, 36) != "event_filter")) {
            // line 37
            yield "        <i class=\"ms-1 pb-1 actor_icon ti ti-";
            yield ((((($_v0 = Twig\Extension\CoreExtension::split($this->env->getCharset(), CoreExtension::getAttribute($this->env, $this->source, ($context["filter_data"] ?? null), "type", [], "any", false, false, false, 37), "_")) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0[0] ?? null) : null) == "group")) ? ("users") : ("user"));
            yield "\"></i>
    ";
        }
        // line 39
        yield "    ";
        $context["label_title"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 40
            yield "        ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["title"] ?? null), "html", null, true);
            yield "
        ";
            // line 41
            if (((CoreExtension::getAttribute($this->env, $this->source, ($context["filter_data"] ?? null), "type", [], "any", false, false, false, 41) == "external") && (!Toolbox::isUrlSafe(((CoreExtension::getAttribute($this->env, $this->source, ($context["filter_data"] ?? null), "url", [], "any", true, true, false, 41)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["filter_data"] ?? null), "url", [], "any", false, false, false, 41), "")) : ("")))))) {
                // line 42
                yield "            <i class=\"ti ti-alert-triangle\" title=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::sprintf(__("URL \"%s\" is not allowed by your administrator."), CoreExtension::getAttribute($this->env, $this->source, ($context["filter_data"] ?? null), "url", [], "any", false, false, false, 42)), "html", null, true);
                yield "\"></i>
        ";
            }
            // line 44
            yield "    ";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
        // line 45
        yield "    ";
        yield $macros["inputs"]->getTemplateForMacro("macro_label", $context, 45, $this->getSourceContext())->macro_label(...[($context["label_title"] ?? null), ($context["filter_key"] ?? null)]);
        yield "

    <span class=\"ms-auto d-flex align-items-center\">
        ";
        // line 49
        yield "        ";
        if ((((CoreExtension::getAttribute($this->env, $this->source, ($context["filter_data"] ?? null), "type", [], "any", false, false, false, 49) != "group_users") && (($context["filter_key"] ?? null) != "OnlyBgEvents")) && (($context["filter_key"] ?? null) != "StateDone"))) {
            // line 50
            yield "            <span class=\"color_input\">
                <input type=\"color\" name=\"";
            // line 51
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["filter_key"] ?? null), "html", null, true);
            yield "_color\" aria-label=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::sprintf(__("%s color"), ($context["title"] ?? null)), "html", null, true);
            yield "\" value=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["color"] ?? null), "html", null, true);
            yield "\">
            </span>
        ";
        }
        // line 54
        yield "        ";
        if ((CoreExtension::getAttribute($this->env, $this->source, ($context["filter_data"] ?? null), "type", [], "any", false, false, false, 54) == "group_users")) {
            // line 55
            yield "            <span class=\"toggle cursor-pointer\"></span>
        ";
        }
        // line 57
        yield "        ";
        if ((CoreExtension::getAttribute($this->env, $this->source, ($context["filter_data"] ?? null), "type", [], "any", false, false, false, 57) != "event_filter")) {
            // line 58
            yield "            <div class=\"filter_option dropstart\">
                <i class=\"ti ti-dots cursor-pointer\"></i>
                <ul class=\"dropdown-menu\">
                    ";
            // line 61
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["params"] ?? null), "show_delete", [], "any", false, false, false, 61)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 62
                yield "                        <li class=\"dropdown-item\">
                            <a target=\"_blank\" href=\"#\" onclick=\"GLPIPlanning.deletePlanning(this)\" value=\"";
                // line 63
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["filter_key"] ?? null), "html", null, true);
                yield "\">";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Delete"), "html", null, true);
                yield "</a>
                        </li>
                    ";
            }
            // line 66
            yield "                    ";
            if ((($tmp = ($context["show_export_buttons"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 67
                yield "                        <li class=\"dropdown-item\">
                            ";
                // line 68
                $context["export_url"] = $this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path(((((((((("front/planning.php?genical=1&uID=" .                 // line 69
($context["uID"] ?? null)) . "&gID=") . ($context["gID"] ?? null)) . "&entities_id=") . $this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpiactive_entity")) . "&is_recursive=") . $this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpiactive_entity_recursive")) . "&token=") . CoreExtension::getAttribute($this->env, $this->source, ($context["login_user"] ?? null), "getAuthToken", [], "method", false, false, false, 69)));
                // line 71
                yield "                            <a target=\"_blank\" href=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["export_url"] ?? null), "html", null, true);
                yield "\">
                                ";
                // line 72
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(_x("button", "Export"), "html", null, true);
                yield " - ";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Ical"), "html", null, true);
                yield "
                            </a>
                        </li>
                        <li class=\"dropdown-item\">
                            <a target=\"_blank\" href=\"";
                // line 76
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["webcal_base_url"] ?? null), "html", null, true);
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["export_url"] ?? null), "html", null, true);
                yield "\">
                                ";
                // line 77
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(_x("button", "Export"), "html", null, true);
                yield " - ";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Webcal"), "html", null, true);
                yield "
                            </a>
                        </li>
                        <li class=\"dropdown-item\">
                            <a target=\"_blank\" href=\"";
                // line 81
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((((($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path("front/planningcsv.php") . "?uID=") . ($context["uID"] ?? null)) . "&gID=") . ($context["gID"] ?? null)), "html", null, true);
                yield "\">
                                ";
                // line 82
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(_x("button", "Export"), "html", null, true);
                yield " - ";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("CSV"), "html", null, true);
                yield "
                            </a>
                        </li>
                        ";
                // line 85
                if ((($tmp =  !(null === ($context["caldav_url"] ?? null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 86
                    yield "                        <li class=\"dropdown-item\">
                            <a target=\"_blank\" href=\"#\" onclick=\"copyTextToClipboard(\x27";
                    // line 87
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["caldav_url"] ?? null), "js"), "html", null, true);
                    yield "\x27); alert(\x27";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("CalDAV URL has been copied to clipboard"), "js"), "html", null, true);
                    yield "\x27); return false;\">
                                ";
                    // line 88
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Copy CalDAV URL to clipboard"), "html", null, true);
                    yield "
                            </a>
                        </li>
                        ";
                }
                // line 92
                yield "                    ";
            }
            // line 93
            yield "                </ul>
            </div>
        ";
        }
        // line 96
        yield "    </span>
    ";
        // line 97
        if (( !Twig\Extension\CoreExtension::testEmpty((((array_key_exists("caldav_url", $context) &&  !(null === $context["caldav_url"]))) ? ($context["caldav_url"]) : (""))) && (CoreExtension::getAttribute($this->env, $this->source, ($context["filter_data"] ?? null), "type", [], "any", false, false, false, 97) == "group_users"))) {
            // line 98
            yield "        <ul class=\"group_listofusers filters\">
            ";
            // line 99
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, ($context["filter_data"] ?? null), "users", [], "any", false, false, false, 99));
            foreach ($context['_seq'] as $context["user_key"] => $context["user_data"]) {
                // line 100
                yield "                ";
                $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Planning::showSingleLinePlanningFilter", [$context["user_key"], $context["user_data"], ["show_delete" => false, "filter_color_index" => CoreExtension::getAttribute($this->env, $this->source,                 // line 102
($context["params"] ?? null), "filter_color_index", [], "any", false, false, false, 102)]]);
                // line 104
                yield "            ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['user_key'], $context['user_data'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 105
            yield "        </ul>
    ";
        }
        // line 107
        yield "</li>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "pages/assistance/planning/single_filter.html.twig";
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
        return array (  246 => 107,  242 => 105,  236 => 104,  234 => 102,  232 => 100,  228 => 99,  225 => 98,  223 => 97,  220 => 96,  215 => 93,  212 => 92,  205 => 88,  199 => 87,  196 => 86,  194 => 85,  186 => 82,  182 => 81,  173 => 77,  168 => 76,  159 => 72,  154 => 71,  152 => 69,  151 => 68,  148 => 67,  145 => 66,  137 => 63,  134 => 62,  132 => 61,  127 => 58,  124 => 57,  120 => 55,  117 => 54,  107 => 51,  104 => 50,  101 => 49,  94 => 45,  90 => 44,  84 => 42,  82 => 41,  77 => 40,  74 => 39,  68 => 37,  66 => 36,  58 => 35,  47 => 34,  45 => 33,  42 => 32,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "pages/assistance/planning/single_filter.html.twig", "/var/www/html/glpi/templates/pages/assistance/planning/single_filter.html.twig");
    }
}
