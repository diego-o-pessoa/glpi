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

/* __string_template__035f9fc5d5f514ca83e06e8e85ee1e80 */
class __TwigTemplate_7f1e6bce2533f709ce477347ecb7011e extends Template
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
        yield "                <div class=\x27filters_toolbar m-2 ";
        yield (((($tmp = (isset($context["is_placeholder"]) || array_key_exists("is_placeholder", $context) ? $context["is_placeholder"] : (function () { throw new RuntimeError('Variable "is_placeholder" does not exist.', 1, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("d-none") : (""));
        yield "\x27>
                    <span class=\x27filters\x27></span>
                    <span class=\x27filters-control\x27>
                        <i class=\"btn btn-sm btn-ghost-secondary ti ti-plus plus-sign add-filter\">
                            <span class=\x27add-filter-lbl\x27>";
        // line 5
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["messages"]) || array_key_exists("messages", $context) ? $context["messages"] : (function () { throw new RuntimeError('Variable "messages" does not exist.', 5, $this->source); })()), "add_filter", [], "array", false, false, false, 5), "html", null, true);
        yield "</span>
                        </i>
                    </span>
                </div>
                <div class=\x27placeholder_info ";
        // line 9
        yield (((($tmp = (isset($context["is_placeholder"]) || array_key_exists("is_placeholder", $context) ? $context["is_placeholder"] : (function () { throw new RuntimeError('Variable "is_placeholder" does not exist.', 9, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("") : ("d-none"));
        yield "\x27 style=\"background-color: transparent; color: var(--tblr-body-color); font-size: var(--tblr-body-font-size)\">
                    <div class=\"alert alert-info\">
                        <div class=\"d-flex\">
                            <i class=\"ti ti-info-circle fs-2x me-3\"></i>
                            <div>
                                <h4 class=\"alert-title\">";
        // line 14
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["messages"]) || array_key_exists("messages", $context) ? $context["messages"] : (function () { throw new RuntimeError('Variable "messages" does not exist.', 14, $this->source); })()), "placeholder_main", [], "array", false, false, false, 14), "html", null, true);
        yield "</h4>
                                <div class=\"mt-2\">
                                    <button class=\"btn btn-info btn-sm disable-dashboard-demo me-2 ";
        // line 16
        yield (((($tmp = (isset($context["can_disable_demo"]) || array_key_exists("can_disable_demo", $context) ? $context["can_disable_demo"] : (function () { throw new RuntimeError('Variable "can_disable_demo" does not exist.', 16, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("") : ("d-none"));
        yield "\" type=\"button\">
                                        <i class=\"ti ti-presentation-off\"></i>
                                        <span>";
        // line 18
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["messages"]) || array_key_exists("messages", $context) ? $context["messages"] : (function () { throw new RuntimeError('Variable "messages" does not exist.', 18, $this->source); })()), "disable_demo_msg", [], "array", false, false, false, 18), "html", null, true);
        yield "</span>
                                    </button>
                                    <script>
                                        \$(\x27button.disable-dashboard-demo\x27).on(\x27click\x27, function() {
                                            \$.post(CFG_GLPI.root_doc + \x27/ajax/dashboard.php\x27, {
                                                action: \x27disable_placeholders\x27
                                            }).then(() => {
                                                window.location.reload();
                                            });
                                        });
                                    </script>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "__string_template__035f9fc5d5f514ca83e06e8e85ee1e80";
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
        return array (  75 => 18,  70 => 16,  65 => 14,  57 => 9,  50 => 5,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("                <div class=\x27filters_toolbar m-2 {{ is_placeholder ? \"d-none\" : \"\" }}\x27>
                    <span class=\x27filters\x27></span>
                    <span class=\x27filters-control\x27>
                        <i class=\"btn btn-sm btn-ghost-secondary ti ti-plus plus-sign add-filter\">
                            <span class=\x27add-filter-lbl\x27>{{ messages[\x27add_filter\x27] }}</span>
                        </i>
                    </span>
                </div>
                <div class=\x27placeholder_info {{ is_placeholder ? \"\" : \"d-none\" }}\x27 style=\"background-color: transparent; color: var(--tblr-body-color); font-size: var(--tblr-body-font-size)\">
                    <div class=\"alert alert-info\">
                        <div class=\"d-flex\">
                            <i class=\"ti ti-info-circle fs-2x me-3\"></i>
                            <div>
                                <h4 class=\"alert-title\">{{ messages[\x27placeholder_main\x27] }}</h4>
                                <div class=\"mt-2\">
                                    <button class=\"btn btn-info btn-sm disable-dashboard-demo me-2 {{ can_disable_demo ? \x27\x27 : \x27d-none\x27 }}\" type=\"button\">
                                        <i class=\"ti ti-presentation-off\"></i>
                                        <span>{{ messages[\x27disable_demo_msg\x27] }}</span>
                                    </button>
                                    <script>
                                        \$(\x27button.disable-dashboard-demo\x27).on(\x27click\x27, function() {
                                            \$.post(CFG_GLPI.root_doc + \x27/ajax/dashboard.php\x27, {
                                                action: \x27disable_placeholders\x27
                                            }).then(() => {
                                                window.location.reload();
                                            });
                                        });
                                    </script>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>", "__string_template__035f9fc5d5f514ca83e06e8e85ee1e80", "");
    }
}
