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

/* __string_template__f646f760a698dfcf5ab82c9b48df316b */
class __TwigTemplate_154e3d2326f2844f6ee315444f464c44 extends Template
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
        // line 1
        yield "                ";
        $macros["fields"] = $this->macros["fields"] = $this->load("components/form/fields_macros.html.twig", 1)->unwrap();
        // line 2
        yield "                <div class=\"mb-3\">
                    <form action=\"";
        // line 3
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["form_url"] ?? null), "html", null, true);
        yield "\" method=\"post\">
                        ";
        // line 4
        yield $macros["fields"]->getTemplateForMacro("macro_largeTitle", $context, 4, $this->getSourceContext())->macro_largeTitle(...[($context["header"] ?? null)]);
        yield "
                        <input type=\"hidden\" name=\"";
        // line 5
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["fk"] ?? null), "html", null, true);
        yield "\" value=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["id"] ?? null), "html", null, true);
        yield "\">
                        <input type=\"hidden\" name=\"_glpi_csrf_token\" value=\"";
        // line 6
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Session::getNewCSRFToken(), "html", null, true);
        yield "\">
                        <div>
                            <div>
                                ";
        // line 9
        yield $macros["fields"]->getTemplateForMacro("macro_textField", $context, 9, $this->getSourceContext())->macro_textField(...["name", "", ($context["name_label"] ?? null), ["full_width" => true]]);
        // line 11
        yield "
                                ";
        // line 12
        if ((($tmp =  !(null === ($context["entity"] ?? null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 13
            yield "                                    <input type=\"hidden\" name=\"entities_id\" value=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["entity"] ?? null), "html", null, true);
            yield "\">
                                ";
        }
        // line 15
        yield "                                ";
        if ((($tmp = ($context["is_recursive"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 16
            yield "                                    <input type=\"hidden\" name=\"is_recursive\" value=\"1\">
                                ";
        }
        // line 18
        yield "                            </div>
                            <div class=\"d-flex flex-row-reverse pe-2\">
                                <button type=\"submit\" name=\"add\" class=\"btn btn-primary\"><i class=\"ti ti-plus\"></i><span>";
        // line 20
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["btn_label"] ?? null), "html", null, true);
        yield "</span></button>
                            </div>
                        </div>
                    </form>
                </div>";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "__string_template__f646f760a698dfcf5ab82c9b48df316b";
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
        return array (  92 => 20,  88 => 18,  84 => 16,  81 => 15,  75 => 13,  73 => 12,  70 => 11,  68 => 9,  62 => 6,  56 => 5,  52 => 4,  48 => 3,  45 => 2,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "__string_template__f646f760a698dfcf5ab82c9b48df316b", "");
    }
}
