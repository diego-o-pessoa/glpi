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

/* __string_template__c628b5ae1a029e9fea56f619a14d9735 */
class __TwigTemplate_42875994fe15b4c0a5ffbb0a7f1bb202 extends Template
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
        $context["bg_color"] = (((($tmp =  !Twig\Extension\CoreExtension::testEmpty(($context["picture"] ?? null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("inherit") : (($context["initials_bg"] ?? null)));
        // line 2
        yield "                <span class=\"avatar avatar-md me-2\"
                    style=\"";
        // line 3
        if ((($tmp =  !(null === ($context["picture"] ?? null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield " background-image: url(";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["picture"] ?? null), "html", null, true);
            yield "); ";
        }
        yield " background-color: ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["bg_color"] ?? null), "html", null, true);
        yield "\">
                    ";
        // line 4
        if (Twig\Extension\CoreExtension::testEmpty(($context["picture"] ?? null))) {
            // line 5
            yield "                        ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["initials"] ?? null), "html", null, true);
            yield "
                    ";
        }
        // line 7
        yield "                </span>";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "__string_template__c628b5ae1a029e9fea56f619a14d9735";
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
        return array (  66 => 7,  60 => 5,  58 => 4,  48 => 3,  45 => 2,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "__string_template__c628b5ae1a029e9fea56f619a14d9735", "");
    }
}
