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

/* layout/parts/profile_selector_form.html.twig */
class __TwigTemplate_7bbf0a24b1a1a5cb634a6c91801cbcf3 extends Template
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
<div class=\"d-flex align-items-center\">
    <button
        type=\"submit\"
        form=\"ch_ent_o_";
        // line 36
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
        yield "\"
        name=\"id\"
        value=\"";
        // line 38
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["id"] ?? null), "html", null, true);
        yield "\"
        class=\"btn btn-link p-0 bg-transparent ";
        // line 39
        yield (((($tmp = ($context["is_recursive"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("fw-bold") : (""));
        yield "\">
        ";
        // line 40
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["name"] ?? null), "html", null, true);
        yield "
    </button>

    ";
        // line 43
        if ((($tmp = ($context["is_recursive"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 44
            yield "        <button
            type=\"submit\"
            form=\"ch_ent_r_";
            // line 46
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
            yield "\"
            name=\"id\"
            value=\"";
            // line 48
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["id"] ?? null), "html", null, true);
            yield "\"
            class=\"btn btn-outline-secondary p-0 ms-1\"
            aria-label=\"";
            // line 50
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::sprintf(__("Select %1s entity with all its sub entities"), ($context["name"] ?? null)), "html", null, true);
            yield "\"
        >
            <i
                class=\"ti ti-chevrons-down\"
                data-bs-toggle=\"tooltip\"
                data-bs-placement=\"right\"
                title=\"";
            // line 56
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::sprintf(__("Select %1s entity with all its sub entities"), ($context["name"] ?? null)), "html", null, true);
            yield "\"
            ></i>
        </button>
    ";
        }
        // line 60
        yield "</div>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "layout/parts/profile_selector_form.html.twig";
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
        return array (  99 => 60,  92 => 56,  83 => 50,  78 => 48,  73 => 46,  69 => 44,  67 => 43,  61 => 40,  57 => 39,  53 => 38,  48 => 36,  42 => 32,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "layout/parts/profile_selector_form.html.twig", "/var/www/html/glpi/templates/layout/parts/profile_selector_form.html.twig");
    }
}
