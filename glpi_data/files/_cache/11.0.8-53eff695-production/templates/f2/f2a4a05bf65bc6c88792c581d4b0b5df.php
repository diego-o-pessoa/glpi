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

/* @glpisaml/loginScreen.html.twig */
class __TwigTemplate_8c3c9d056d92057074fd6a2b1cfaa4a2 extends Template
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
        yield "<!-- implemented in Loginflow->showLoginScreen(void): string -->
";
        // line 2
        if ((($context["showbuttons"] ?? null) == true)) {
            // line 3
            yield "<style>
.col-auto.px-2.text-center:has(.glpisaml-login-buttons){display:flex;flex-basis:100%;justify-content:center;max-width:100%;padding-top:12px}
.glpisaml-login-buttons{margin:0 auto;width:260px}
.glpisaml-login-buttons .card-header{display:none}
.glpisaml-provider-list,.glpisaml-provider-item{width:260px}
.glpisaml-provider-button{align-items:center;box-sizing:border-box;display:flex;gap:10px;justify-content:center;margin:0;padding:8px 12px;width:260px}
.glpisaml-microsoft-icon{flex:0 0 20px}
</style>
<div class=\"glpisaml-login-buttons\" style=\"width:260px; margin:12px auto 0;\">
    <div class=\"card-header\">
        <h2>";
            // line 13
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["header"] ?? null), "html", null, true);
            yield "</h2>
    </div>
    <div class=\"glpisaml-provider-list\" style=\"width:260px;\">
        ";
            // line 16
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["buttons"] ?? null));
            $context['_iterated'] = false;
            foreach ($context['_seq'] as $context["_key"] => $context["button"]) {
                // line 17
                yield "        <div class=\"glpisaml-provider-item\" style=\"width:260px;\">
            <button type=\"submit\" name=\"";
                // line 18
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["postfield"] ?? null), "html", null, true);
                yield "\" value=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(abs(CoreExtension::getAttribute($this->env, $this->source, $context["button"], "id", [], "any", false, false, false, 18)), "html", null, true);
                yield "\" class=\"glpisaml-provider-button\" style=\"background:#fff; border:1px solid #8c8c8c; border-radius:0; color:#242424; cursor:pointer; font-size:14px; min-height:44px; text-align:center;\">
                <span class=\"glpisaml-microsoft-icon\" aria-hidden=\"true\" style=\"display:grid; gap:2px; grid-template-columns:repeat(2,9px); grid-template-rows:repeat(2,9px); height:20px; width:20px;\"><span style=\"background:#f35325; display:block;\"></span><span style=\"background:#81bc06; display:block;\"></span><span style=\"background:#05a6f0; display:block;\"></span><span style=\"background:#ffba08; display:block;\"></span></span>
                <span>Entrar com Microsoft</span>
            </button>
        </div>
        <!-- This section should never be reached -->
        ";
                $context['_iterated'] = true;
            }
            // line 24
            if (!$context['_iterated']) {
                // line 25
                yield "            <div><p>";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["noconfig"] ?? null), "html", null, true);
                yield "</p>
        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['button'], $context['_parent'], $context['_iterated']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 27
            yield "        </div>
    </div>
</div>
";
        }
        // line 31
        yield "<!-- Add CSS to hide certain login buttons
     Will be used as configurable option in 
     version 1.2.0
";
        // line 34
        if ((($context["enforced"] ?? null) == true)) {
            // line 35
            yield "<style>
    .mb-4:has(#login_password){ display:none; }
    .mb-2:has(#login_remember){ display:none; }
    .mb-3:has(.select2){ display:none; }
</style>
";
        }
        // line 41
        yield "-->";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "@glpisaml/loginScreen.html.twig";
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
        return array (  118 => 41,  110 => 35,  108 => 34,  103 => 31,  97 => 27,  88 => 25,  86 => 24,  73 => 18,  70 => 17,  65 => 16,  59 => 13,  47 => 3,  45 => 2,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "@glpisaml/loginScreen.html.twig", "/var/www/html/glpi/plugins/glpisaml/templates/loginScreen.html.twig");
    }
}
