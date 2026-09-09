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

/* pages/admin/profile/tools.html.twig */
class __TwigTemplate_cc30c83e09461902bba3e53be6259ff1 extends Template
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
        yield $this->getTemplateForMacro("macro_displayRightsMatrix", $context, 36, $this->getSourceContext())->macro_displayRightsMatrix(...[($context["item"] ?? null), "central", "tools", "general", __("Tools")]);
        yield "
    ";
        // line 37
        yield $this->getTemplateForMacro("macro_displayRightsMatrix", $context, 37, $this->getSourceContext())->macro_displayRightsMatrix(...[($context["item"] ?? null), "central", "tools", "projects", $this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("Project", Session::getPluralNumber())]);
        yield "
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "pages/admin/profile/tools.html.twig";
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
        return array (  63 => 37,  58 => 36,  51 => 35,  40 => 33,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "pages/admin/profile/tools.html.twig", "/var/www/html/glpi/templates/pages/admin/profile/tools.html.twig");
    }
}
