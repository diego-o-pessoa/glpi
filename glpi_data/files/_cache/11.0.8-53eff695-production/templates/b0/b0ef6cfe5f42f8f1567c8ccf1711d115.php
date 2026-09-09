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

/* pages/admin/profile/admin.html.twig */
class __TwigTemplate_cd5a0e697bbf5bfd10123306906ed573 extends Template
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
        // line 34
        return "pages/admin/profile/base_tab.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 33
        $context["show_legend"] = true;
        // line 34
        $this->parent = $this->load("pages/admin/profile/base_tab.html.twig", 34);
        yield from $this->parent->unwrap()->yield($context, array_merge($this->blocks, $blocks));
    }

    // line 36
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_content(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 37
        yield "    ";
        yield $this->getTemplateForMacro("macro_displayRightsMatrix", $context, 37, $this->getSourceContext())->macro_displayRightsMatrix(...[($context["item"] ?? null), "central", "admin", "general", __("Administration")]);
        yield "
    ";
        // line 38
        yield $this->getTemplateForMacro("macro_displayRightsMatrix", $context, 38, $this->getSourceContext())->macro_displayRightsMatrix(...[($context["item"] ?? null), "central", "admin", "inventory", __("Inventory")]);
        yield "
    ";
        // line 39
        yield $this->getTemplateForMacro("macro_displayRightsMatrix", $context, 39, $this->getSourceContext())->macro_displayRightsMatrix(...[($context["item"] ?? null), "central", "admin", "rules", _n("Rule", "Rules", Session::getPluralNumber())]);
        yield "
    ";
        // line 40
        yield $this->getTemplateForMacro("macro_displayRightsMatrix", $context, 40, $this->getSourceContext())->macro_displayRightsMatrix(...[($context["item"] ?? null), "central", "admin", "dictionaries", __("Dropdowns dictionary")]);
        yield "
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "pages/admin/profile/admin.html.twig";
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
        return array (  74 => 40,  70 => 39,  66 => 38,  61 => 37,  54 => 36,  49 => 34,  47 => 33,  40 => 34,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "pages/admin/profile/admin.html.twig", "/var/www/html/glpi/templates/pages/admin/profile/admin.html.twig");
    }
}
