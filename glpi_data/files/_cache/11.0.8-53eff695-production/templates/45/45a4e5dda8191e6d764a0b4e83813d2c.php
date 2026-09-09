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

/* pages/login.html.twig */
class __TwigTemplate_6eb3539b85a8e30ed7f73ab3af359e3a extends Template
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
            'content_block' => [$this, 'block_content_block'],
            'footer_block' => [$this, 'block_footer_block'],
            'javascript_block' => [$this, 'block_javascript_block'],
        ];
    }

    protected function doGetParent(array $context): bool|string|Template|TemplateWrapper
    {
        // line 33
        return "layout/page_card_notlogged.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $this->parent = $this->load("layout/page_card_notlogged.html.twig", 33);
        yield from $this->parent->unwrap()->yield($context, array_merge($this->blocks, $blocks));
    }

    // line 35
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_content_block(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 36
        yield "    <form action=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path("front/login.php"), "html", null, true);
        yield "\" method=\"post\" autocomplete=\"off\" data-submit-once>
        <input type=\"hidden\" name=\"noAUTO\" value=\"";
        // line 37
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["noAuto"] ?? null), "html", null, true);
        yield "\"/>
        <input type=\"hidden\" name=\"redirect\" value=\"";
        // line 38
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["redirect"] ?? null), "html", null, true);
        yield "\"/>
        <input type=\"hidden\" name=\"_glpi_csrf_token\" value=\"";
        // line 39
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Session::getNewCSRFToken(), "html", null, true);
        yield "\"/>
        ";
        // line 40
        if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["text_login"] ?? null)) > 0)) {
            // line 41
            yield "            <div class=\"rich_text_container text-center\">
                ";
            // line 42
            yield $this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getSafeHtml(($context["text_login"] ?? null));
            yield "
            </div>
        ";
        }
        // line 45
        yield "        <div class=\"row justify-content-center\">
            <div class=\"col-md-5\">
                <div class=\"card-header mb-4\">
                    <h2 class=\"mx-auto\">";
        // line 48
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Login to your account"), "html", null, true);
        yield "</h2>
                </div>
                <div class=\"mb-3\">
                    <label class=\"form-label\" for=\"login_name\">";
        // line 51
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Login"), "html", null, true);
        yield "</label>
                    <input type=\"text\" class=\"form-control\" id=\"login_name\" name=\"login_name\" placeholder=\"\"/>
                </div>
                <div class=\"mb-4\">
                    <div class=\"d-flex\">
                        <label class=\"form-label\" for=\"login_password\">
                            ";
        // line 57
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Password"), "html", null, true);
        yield "
                        </label>
                    </div>
                    <input type=\"password\" class=\"form-control\" id=\"login_password\" name=\"login_password\" placeholder=\"\" autocomplete=\"off\"/>
                </div>

                ";
        // line 63
        if ((($tmp = $this->extensions['Glpi\Application\View\Extension\ConfigExtension']->config("display_login_source")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 64
            yield "                    <div class=\"mb-3\">
                        <label class=\"form-label\" for=\"dropdown_auth";
            // line 65
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
            yield "\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Login source"), "html", null, true);
            yield "</label>
                        ";
            // line 66
            yield ($context["auth_dropdown_login"] ?? null);
            yield "
                    </div>
                ";
        }
        // line 69
        yield "
                ";
        // line 70
        if ((($tmp = $this->extensions['Glpi\Application\View\Extension\ConfigExtension']->config("login_remember_time")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 71
            yield "                    <div class=\"mb-2\">
                        <label class=\"form-check\" for=\"login_remember\">
                            <input type=\"checkbox\" class=\"form-check-input\" id=\"login_remember\" name=\"login_remember\" ";
            // line 73
            yield (((($tmp = $this->extensions['Glpi\Application\View\Extension\ConfigExtension']->config("login_remember_default")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("checked") : (""));
            yield "/>
                            <span class=\"form-check-label\">";
            // line 74
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Remember me"), "html", null, true);
            yield "</span>
                        </label>
                    </div>
                ";
        }
        // line 78
        yield "
                <div class=\"form-footer\">
                    <button type=\"submit\" name=\"submit\" class=\"btn btn-primary w-100 mb-2\">
                        ";
        // line 81
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Sign in"), "html", null, true);
        yield "
                    </button>
                    ";
        // line 83
        if ((($tmp = ($context["show_lost_password"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 84
            yield "                        <div class=\"ms-auto text-center forgot_password ";
            yield (((($tmp = $this->extensions['Glpi\Application\View\Extension\ConfigExtension']->config("display_login_source")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("d-none") : (""));
            yield "\">
                            <a href=\"";
            // line 85
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path("front/lostpassword.php?lostpassword=1"), "html", null, true);
            yield "\">
                                ";
            // line 86
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Forgotten password?"), "html", null, true);
            yield "
                            </a>
                        </div>
                        ";
            // line 89
            if ((($tmp = $this->extensions['Glpi\Application\View\Extension\ConfigExtension']->config("display_login_source")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 90
                yield "                            <script>
                                \$(() => {
                                    if (\$(\x27select[name=\"auth\"]\x27).val() === \x27local\x27) {
                                        \$(\x27.forgot_password\x27).removeClass(\x27d-none\x27);
                                    }
                                    \$(\x27select[name=\"auth\"]\x27).on(\x27change\x27, function () {
                                        if (\$(this).val() === \x27local\x27) {
                                            \$(\x27.forgot_password\x27).removeClass(\x27d-none\x27);
                                        } else {
                                            \$(\x27.forgot_password\x27).addClass(\x27d-none\x27);
                                        }
                                    });
                                });
                            </script>
                        ";
            }
            // line 105
            yield "                    ";
        }
        // line 106
        yield "                </div>
            </div>

            ";
        // line 109
        if ((($tmp = ($context["right_panel"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 110
            yield "                <div class=\"col-auto px-2 text-center\">
                    ";
            // line 111
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\PluginExtension']->callPluginHook(Twig\Extension\CoreExtension::constant("Glpi\\Plugin\\Hooks::DISPLAY_LOGIN")), "html", null, true);
            yield "
                </div>
            ";
        }
        // line 114
        yield "        </div>
        ";
        // line 115
        if ((($tmp = $this->extensions['Glpi\Application\View\Extension\ConfigExtension']->config("use_public_faq")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 116
            yield "            <div class=\"text-center mt-4 border-top\">
                <a class=\"btn btn-outline-secondary mt-4\" href=\"";
            // line 117
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path("front/helpdesk.faq.php"), "html", null, true);
            yield "\">
                    <i class=\"ti ti-help\"></i>&nbsp;
                    ";
            // line 119
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("FAQ"), "html", null, true);
            yield "
                </a>
            </div>
        ";
        }
        // line 123
        yield "    </form>
";
        yield from [];
    }

    // line 126
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_footer_block(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 127
        yield "    ";
        yield ($context["copyright_message"] ?? null);
        yield "

    ";
        // line 129
        if ((($tmp = ($context["must_call_cron"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 130
            yield "        <div style=\"background-image: url(\x27";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path("/front/cron.php"), "html", null, true);
            yield "\x27);\"></div>
    ";
        }
        yield from [];
    }

    // line 134
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_javascript_block(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 135
        yield "    <script type=\"text/javascript\">
        \$(function () {
            \$(\x27#login_name\x27).focus();
        });
    </script>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "pages/login.html.twig";
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
        return array (  276 => 135,  269 => 134,  260 => 130,  258 => 129,  252 => 127,  245 => 126,  239 => 123,  232 => 119,  227 => 117,  224 => 116,  222 => 115,  219 => 114,  213 => 111,  210 => 110,  208 => 109,  203 => 106,  200 => 105,  183 => 90,  181 => 89,  175 => 86,  171 => 85,  166 => 84,  164 => 83,  159 => 81,  154 => 78,  147 => 74,  143 => 73,  139 => 71,  137 => 70,  134 => 69,  128 => 66,  122 => 65,  119 => 64,  117 => 63,  108 => 57,  99 => 51,  93 => 48,  88 => 45,  82 => 42,  79 => 41,  77 => 40,  73 => 39,  69 => 38,  65 => 37,  60 => 36,  53 => 35,  42 => 33,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "pages/login.html.twig", "/var/www/html/glpi/templates/pages/login.html.twig");
    }
}
