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

/* pages/admin/profile/lifecycle.html.twig */
class __TwigTemplate_bf59d7d7221ec97df7a52892400ab734 extends Template
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

        $this->blocks = [
            'content' => [$this, 'block_content'],
        ];
    }

    protected function doGetParent(array $context): bool|string|Template|TemplateWrapper
    {
        // line 33
        return "pages/admin/profile/base_tab.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $this->parent = $this->load("pages/admin/profile/base_tab.html.twig", 33);
        yield from $this->parent->unwrap()->yield($context, array_merge($this->blocks, $blocks));
    }

    // line 35
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_content(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 36
        yield "    ";
        yield $this->getTemplateForMacro("macro_displayLifecycleMatrix", $context, 36, $this->getSourceContext())->macro_displayLifecycleMatrix(...[        // line 37
($context["item"] ?? null), __("Life cycle of tickets"), "_cycle_ticket", "ticket_status", $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Ticket::getAllStatusArray")]);
        // line 42
        yield "
    ";
        // line 43
        yield $this->getTemplateForMacro("macro_displayLifecycleMatrix", $context, 43, $this->getSourceContext())->macro_displayLifecycleMatrix(...[        // line 44
($context["item"] ?? null), __("Life cycle of problems"), "_cycle_problem", "problem_status", $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Problem::getAllStatusArray")]);
        // line 49
        yield "
    ";
        // line 50
        yield $this->getTemplateForMacro("macro_displayLifecycleMatrix", $context, 50, $this->getSourceContext())->macro_displayLifecycleMatrix(...[        // line 51
($context["item"] ?? null), __("Life cycle of changes"), "_cycle_change", "change_status", $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Change::getAllStatusArray")]);
        // line 56
        yield "
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "pages/admin/profile/lifecycle.html.twig";
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
        return array (  74 => 56,  72 => 51,  71 => 50,  68 => 49,  66 => 44,  65 => 43,  62 => 42,  60 => 37,  58 => 36,  51 => 35,  40 => 33,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "pages/admin/profile/lifecycle.html.twig", "/var/www/html/glpi/templates/pages/admin/profile/lifecycle.html.twig");
    }
}
