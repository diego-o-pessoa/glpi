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

/* pages/admin/inventory/upload_form.html.twig */
class __TwigTemplate_7a8e8af7a642609ea9100c0ef93e415b extends Template
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
<form method=\x27POST\x27 enctype=\x27multipart/form-data\x27 action=\"";
        // line 35
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path("Inventory/ImportFiles"), "html", null, true);
        yield "\">
    <h2>
        ";
        // line 37
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Import inventory files"), "html", null, true);
        yield "
    </h2>
    <div class=\"alert alert-info\" role=\"alert\">
        <p>
            ";
        // line 41
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::sprintf(__("You can use this menu to upload one or several inventory files. Files must have a known extension (%1\$s)."), Twig\Extension\CoreExtension::join(($context["inventory_extensions"] ?? null), ", ")), "html", null, true);
        yield "<br>
            ";
        // line 42
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("It is also possible to upload a compressed archive directly with a collection of inventory files."), "html", null, true);
        yield "
        </p>
    </div>
    ";
        // line 45
        yield $macros["fields"]->getTemplateForMacro("macro_fileField", $context, 45, $this->getSourceContext())->macro_fileField(...["inventory_files[]", "", "", ["is_horizontal" => true, "simple" => true, "no_label" => true, "multiple" => true, "required" => true]]);
        // line 56
        yield "

    <button class=\"btn btn-primary me-2\" type=\"submit\"
            name=\"upload_inventory\" value=\"1\">
        <i class=\"ti ti-upload\"></i>
        <span>";
        // line 61
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(_x("button", "Upload"), "html", null, true);
        yield "</span>
    </button>

    <input type=\"hidden\" name=\"_glpi_csrf_token\" value=\"";
        // line 64
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Session::getNewCSRFToken(), "html", null, true);
        yield "\" />
</form>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "pages/admin/inventory/upload_form.html.twig";
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
        return array (  87 => 64,  81 => 61,  74 => 56,  72 => 45,  66 => 42,  62 => 41,  55 => 37,  50 => 35,  47 => 34,  45 => 33,  42 => 32,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "pages/admin/inventory/upload_form.html.twig", "/var/www/html/glpi/templates/pages/admin/inventory/upload_form.html.twig");
    }
}
