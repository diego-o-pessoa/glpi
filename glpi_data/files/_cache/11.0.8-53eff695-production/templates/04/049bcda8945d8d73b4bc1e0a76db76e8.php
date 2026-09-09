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

/* pages/admin/profile/management.html.twig */
class __TwigTemplate_1757085a7f4962a8443c8c4661023317 extends Template
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
        // line 34
        $macros["fields"] = $this->macros["fields"] = $this->load("components/form/fields_macros.html.twig", 34)->unwrap();
        // line 33
        $this->parent = $this->load("pages/admin/profile/base_tab.html.twig", 33);
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
        yield $this->getTemplateForMacro("macro_displayRightsMatrix", $context, 37, $this->getSourceContext())->macro_displayRightsMatrix(...[($context["item"] ?? null), "central", "management", "general", __("Management")]);
        yield "
    ";
        // line 38
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownArrayField", $context, 38, $this->getSourceContext())->macro_dropdownArrayField(...["managed_domainrecordtypes", "", (["-1" => __("All")] + CoreExtension::getAttribute($this->env, $this->source,         // line 41
($context["item"] ?? null), "getDomainRecordTypes", [], "method", false, false, false, 41)), __("Manageable domain records"), ["multiple" => true, "values" => (($_v0 = CoreExtension::getAttribute($this->env, $this->source,         // line 45
($context["item"] ?? null), "fields", [], "any", false, false, false, 45)) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0["managed_domainrecordtypes"] ?? null) : null)]]);
        // line 47
        yield "
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "pages/admin/profile/management.html.twig";
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
        return array (  70 => 47,  68 => 45,  67 => 41,  66 => 38,  61 => 37,  54 => 36,  49 => 33,  47 => 34,  40 => 33,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "pages/admin/profile/management.html.twig", "/var/www/html/glpi/templates/pages/admin/profile/management.html.twig");
    }
}
