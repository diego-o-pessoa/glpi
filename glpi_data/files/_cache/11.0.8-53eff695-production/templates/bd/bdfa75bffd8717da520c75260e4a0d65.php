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

/* pages/admin/plugins/list_suspend_banner.html.twig */
class __TwigTemplate_4de3719272586b75a2736ab741c4c517 extends Template
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
        $macros["fields"] = $this->macros["fields"] = $this->load("components/form/fields_macros.html.twig", 33)->unwrap();
        // line 34
        yield "
";
        // line 35
        if ((($tmp = ($context["execution_suspended"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 36
            yield "    <div class=\"alert alert-warning\" role=\"alert\">
        ";
            // line 37
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("The execution of the plugins is suspended."), "html", null, true);
            yield "
        <form class=\"d-inline\" action=\"";
            // line 38
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeFormPath("Plugin"), "html", null, true);
            yield "\" method=\"POST\">
            ";
            // line 39
            yield $macros["fields"]->getTemplateForMacro("macro_csrfField", $context, 39, $this->getSourceContext())->macro_csrfField(...[]);
            yield "
            <button type=\"submit\" name=\"action\" value=\"resume_all_execution\" class=\"btn btn-sm btn-primary me-1\">
                <i class=\"ti ti-player-play-filled\"></i>
                <span>";
            // line 42
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Resume execution of all active plugins"), "html", null, true);
            yield "</span>
            </button>
        </form>
    </div>
";
        } else {
            // line 47
            yield "    <div class=\"text-end mb-3\">
        <form class=\"d-inline\" action=\"";
            // line 48
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeFormPath("Plugin"), "html", null, true);
            yield "\" method=\"POST\">
            ";
            // line 49
            yield $macros["fields"]->getTemplateForMacro("macro_csrfField", $context, 49, $this->getSourceContext())->macro_csrfField(...[]);
            yield "
            <button type=\"submit\" name=\"action\" value=\"suspend_all_execution\" class=\"btn btn-primary me-1\">
                <i class=\"ti ti-player-pause-filled\"></i>
                <span>";
            // line 52
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Suspend execution of all plugins"), "html", null, true);
            yield "</span>
            </button>
        </form>
    </div>
";
        }
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "pages/admin/plugins/list_suspend_banner.html.twig";
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
        return array (  90 => 52,  84 => 49,  80 => 48,  77 => 47,  69 => 42,  63 => 39,  59 => 38,  55 => 37,  52 => 36,  50 => 35,  47 => 34,  45 => 33,  42 => 32,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "pages/admin/plugins/list_suspend_banner.html.twig", "/var/www/html/glpi/templates/pages/admin/plugins/list_suspend_banner.html.twig");
    }
}
