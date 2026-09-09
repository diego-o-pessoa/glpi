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

/* pages/admin/profile/base_tab.html.twig */
class __TwigTemplate_c3116151bd5864cd4ad5275accd6fff1 extends Template
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
            'content' => [$this, 'block_content'],
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
        $macros["fields"] = $this->macros["fields"] = $this->load("components/form/fields_macros.html.twig", 34)->unwrap();
        // line 35
        yield "
";
        // line 36
        $context["show_legend"] = ((array_key_exists("show_legend", $context)) ? (Twig\Extension\CoreExtension::default(($context["show_legend"] ?? null), false)) : (false));
        // line 37
        $context["can_edit"] = ((Session::haveRight("profile", Twig\Extension\CoreExtension::constant("CREATE")) || Session::haveRight("profile", Twig\Extension\CoreExtension::constant("UPDATE"))) || Session::haveRight("profile", Twig\Extension\CoreExtension::constant("PURGE")));
        // line 38
        yield "
";
        // line 48
        yield "
";
        // line 58
        yield "
<div class=\"asset\">
    ";
        // line 60
        if ((($tmp = ($context["can_edit"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 61
            yield "        <form method=\"post\" action=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeFormPath("Profile"), "html", null, true);
            yield "\" data-track-changes=\"true\">
            <input type=\"hidden\" name=\"id\" value=\"";
            // line 62
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "getID", [], "method", false, false, false, 62), "html", null, true);
            yield "\">
            ";
            // line 63
            yield $macros["fields"]->getTemplateForMacro("macro_csrfField", $context, 63, $this->getSourceContext())->macro_csrfField(...[]);
            yield "
    ";
        }
        // line 65
        yield "            <div>
                <div class=\"card-body p-0\">
                    ";
        // line 67
        yield from $this->unwrap()->yieldBlock('content', $context, $blocks);
        // line 68
        yield "                    ";
        if ((($tmp = ($context["show_legend"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 69
            yield "                        ";
            yield Twig\Extension\CoreExtension::include($this->env, $context, "pages/admin/profile/legend.html.twig");
            yield "
                    ";
        }
        // line 71
        yield "                </div>
    ";
        // line 72
        if ((($tmp = ($context["can_edit"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 73
            yield "                <div class=\"card-body mx-n2 border-top d-flex flex-row-reverse align-items-start flex-wrap\">
                    ";
            // line 74
            yield $macros["inputs"]->getTemplateForMacro("macro_submit", $context, 74, $this->getSourceContext())->macro_submit(...["update", _x("button", "Save"), 1, ["class" => "btn btn-primary", "icon" => "ti ti-device-floppy"]]);
            // line 77
            yield "
                </div>
    ";
        }
        // line 80
        yield "            </div>
    ";
        // line 81
        if ((($tmp = ($context["can_edit"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 82
            yield "        </form>
    ";
        }
        // line 84
        yield "</div>
";
        yield from [];
    }

    // line 67
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_content(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        yield from [];
    }

    // line 39
    public function macro_displayRightsMatrix($item = null, $interface = null, $form = null, $group = null, $title = null, ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "item" => $item,
            "interface" => $interface,
            "form" => $form,
            "group" => $group,
            "title" => $title,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 40
            yield "    ";
            CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "displayRightsChoiceMatrix", [$this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Profile::getRightsForForm", [            // line 41
($context["interface"] ?? null), ($context["form"] ?? null), ($context["group"] ?? null)]), ["canedit" => ((Session::haveRight("profile", Twig\Extension\CoreExtension::constant("CREATE")) || Session::haveRight("profile", Twig\Extension\CoreExtension::constant("UPDATE"))) || Session::haveRight("profile", Twig\Extension\CoreExtension::constant("PURGE"))), "title" =>             // line 44
($context["title"] ?? null)]], "method", false, false, false, 40);
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 49
    public function macro_displayLifecycleMatrix($item = null, $title = null, $html_field = null, $db_field = null, $statuses = null, ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "item" => $item,
            "title" => $title,
            "html_field" => $html_field,
            "db_field" => $db_field,
            "statuses" => $statuses,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 50
            yield "    ";
            CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "displayLifeCycleMatrix", [            // line 51
($context["title"] ?? null),             // line 52
($context["html_field"] ?? null),             // line 53
($context["db_field"] ?? null),             // line 54
($context["statuses"] ?? null), ((Session::haveRight("profile", Twig\Extension\CoreExtension::constant("CREATE")) || Session::haveRight("profile", Twig\Extension\CoreExtension::constant("UPDATE"))) || Session::haveRight("profile", Twig\Extension\CoreExtension::constant("PURGE")))], "method", false, false, false, 50);
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "pages/admin/profile/base_tab.html.twig";
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
        return array (  184 => 54,  183 => 53,  182 => 52,  181 => 51,  179 => 50,  163 => 49,  157 => 44,  156 => 41,  154 => 40,  138 => 39,  128 => 67,  122 => 84,  118 => 82,  116 => 81,  113 => 80,  108 => 77,  106 => 74,  103 => 73,  101 => 72,  98 => 71,  92 => 69,  89 => 68,  87 => 67,  83 => 65,  78 => 63,  74 => 62,  69 => 61,  67 => 60,  63 => 58,  60 => 48,  57 => 38,  55 => 37,  53 => 36,  50 => 35,  48 => 34,  46 => 33,  43 => 32,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "pages/admin/profile/base_tab.html.twig", "/var/www/html/glpi/templates/pages/admin/profile/base_tab.html.twig");
    }
}
