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

/* components/messages_after_redirect_toasts.html.twig */
class __TwigTemplate_e39964ee1f79f13b7432caf23a50d363 extends Template
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
        $context["messages"] = $this->extensions['Glpi\Application\View\Extension\SessionExtension']->pullMessages();
        // line 34
        yield "
";
        // line 35
        $context["toasts_html"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 36
            yield "    ";
            if ((($tmp = Twig\Extension\CoreExtension::length($this->env->getCharset(), (isset($context["messages"]) || array_key_exists("messages", $context) ? $context["messages"] : (function () { throw new RuntimeError('Variable "messages" does not exist.', 36, $this->source); })()))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 37
                yield "        ";
                $context['_parent'] = $context;
                $context['_seq'] = CoreExtension::ensureTraversable((isset($context["messages"]) || array_key_exists("messages", $context) ? $context["messages"] : (function () { throw new RuntimeError('Variable "messages" does not exist.', 37, $this->source); })()));
                foreach ($context['_seq'] as $context["type"] => $context["message"]) {
                    // line 38
                    yield "            ";
                    $context["message"] = Twig\Extension\CoreExtension::join($context["message"], "<br />");
                    // line 39
                    yield "            ";
                    $context["class"] = "";
                    // line 40
                    yield "            ";
                    $context["title"] = "";
                    // line 41
                    yield "            ";
                    if (($context["type"] == Twig\Extension\CoreExtension::constant("ERROR"))) {
                        // line 42
                        yield "                ";
                        $context["class"] = "bg-danger text-white";
                        // line 43
                        yield "                ";
                        $context["title"] = _n("Error", "Errors", 1);
                        // line 44
                        yield "            ";
                    } elseif (($context["type"] == Twig\Extension\CoreExtension::constant("WARNING"))) {
                        // line 45
                        yield "                ";
                        $context["class"] = "bg-warning text-white";
                        // line 46
                        yield "                ";
                        $context["title"] = __("Warning");
                        // line 47
                        yield "            ";
                    } else {
                        // line 48
                        yield "                ";
                        $context["class"] = "bg-info text-white";
                        // line 49
                        yield "                ";
                        $context["title"] = _n("Information", "Information", 1);
                        // line 50
                        yield "            ";
                    }
                    // line 51
                    yield "
            <div class=\"toast animate__animated animate__tada animate__delay-2s animate__slow\" role=\"alert\" aria-live=\"assertive\" aria-atomic=\"true\">
                <div class=\"toast-header ";
                    // line 53
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["class"]) || array_key_exists("class", $context) ? $context["class"] : (function () { throw new RuntimeError('Variable "class" does not exist.', 53, $this->source); })()), "html", null, true);
                    yield " \">
                    <strong class=\"me-auto\">";
                    // line 54
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["title"]) || array_key_exists("title", $context) ? $context["title"] : (function () { throw new RuntimeError('Variable "title" does not exist.', 54, $this->source); })()), "html", null, true);
                    yield "</strong>
                    <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"toast\" aria-label=\"Close\"></button>
                </div>
                <div class=\"toast-body\">
                    ";
                    // line 58
                    yield $context["message"];
                    yield "
                </div>
            </div>
        ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['type'], $context['message'], $context['_parent']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 62
                yield "    ";
            }
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
        // line 64
        yield "
";
        // line 65
        if ((($tmp = (isset($context["display_container"]) || array_key_exists("display_container", $context) ? $context["display_container"] : (function () { throw new RuntimeError('Variable "display_container" does not exist.', 65, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 66
            yield "    ";
            $context["toast_location"] = $this->extensions['Glpi\Application\View\Extension\SessionExtension']->userPref("toast_location");
            // line 67
            yield "    ";
            if (!CoreExtension::inFilter((isset($context["toast_location"]) || array_key_exists("toast_location", $context) ? $context["toast_location"] : (function () { throw new RuntimeError('Variable "toast_location" does not exist.', 67, $this->source); })()), ["top-left", "top-right", "bottom-left", "bottom-right"])) {
                // line 68
                yield "        ";
                $context["toast_location"] = "bottom-right";
                // line 69
                yield "    ";
            }
            // line 70
            yield "    <div class=\"toast-container ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["toast_location"]) || array_key_exists("toast_location", $context) ? $context["toast_location"] : (function () { throw new RuntimeError('Variable "toast_location" does not exist.', 70, $this->source); })()), "html", null, true);
            yield " p-3 messages_after_redirect\" id=\"messages_after_redirect\">
        ";
            // line 71
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["toasts_html"]) || array_key_exists("toasts_html", $context) ? $context["toasts_html"] : (function () { throw new RuntimeError('Variable "toasts_html" does not exist.', 71, $this->source); })()), "html", null, true);
            yield "

        <script type=\"text/javascript\">
        var initMessagesAfterRedirectToasts = function() {
            var toastElList = [].slice.call(document.querySelectorAll(\x27#messages_after_redirect .toast:not(.show)\x27));
            var toastList = toastElList.map(function (toastEl) {
                var toast = new bootstrap.Toast(toastEl, {
                    delay: 10000,
                })
                toast.show()

                \$(toastEl).on(\x27hidden.bs.toast\x27, function () {
                    \$(toastEl).remove();
                });

                return toast;
            });
        }
        \$(function() {
            initMessagesAfterRedirectToasts();
        });
        </script>
    </div>
";
        } else {
            // line 95
            yield "    ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["toasts_html"]) || array_key_exists("toasts_html", $context) ? $context["toasts_html"] : (function () { throw new RuntimeError('Variable "toasts_html" does not exist.', 95, $this->source); })()), "html", null, true);
            yield "
";
        }
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "components/messages_after_redirect_toasts.html.twig";
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
        return array (  178 => 95,  151 => 71,  146 => 70,  143 => 69,  140 => 68,  137 => 67,  134 => 66,  132 => 65,  129 => 64,  124 => 62,  114 => 58,  107 => 54,  103 => 53,  99 => 51,  96 => 50,  93 => 49,  90 => 48,  87 => 47,  84 => 46,  81 => 45,  78 => 44,  75 => 43,  72 => 42,  69 => 41,  66 => 40,  63 => 39,  60 => 38,  55 => 37,  52 => 36,  50 => 35,  47 => 34,  45 => 33,  42 => 32,);
    }

    public function getSourceContext(): Source
    {
        return new Source("{#
 # ---------------------------------------------------------------------
 #
 # GLPI - Gestionnaire Libre de Parc Informatique
 #
 # http://glpi-project.org
 #
 # @copyright 2015-2026 Teclib\x27 and contributors.
 # @licence   https://www.gnu.org/licenses/gpl-3.0.html
 #
 # ---------------------------------------------------------------------
 #
 # LICENSE
 #
 # This file is part of GLPI.
 #
 # This program is free software: you can redistribute it and/or modify
 # it under the terms of the GNU General Public License as published by
 # the Free Software Foundation, either version 3 of the License, or
 # (at your option) any later version.
 #
 # This program is distributed in the hope that it will be useful,
 # but WITHOUT ANY WARRANTY; without even the implied warranty of
 # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 # GNU General Public License for more details.
 #
 # You should have received a copy of the GNU General Public License
 # along with this program.  If not, see <https://www.gnu.org/licenses/>.
 #
 # ---------------------------------------------------------------------
 #}

{% set messages = pull_messages() %}

{% set toasts_html %}
    {% if messages|length %}
        {% for type, message in messages %}
            {% set message = message|join(\x27<br />\x27) %}
            {% set class = \x27\x27 %}
            {% set title = \x27\x27 %}
            {% if type == constant(\x27ERROR\x27) %}
                {% set class = \x27bg-danger text-white\x27 %}
                {% set title = _n(\x27Error\x27, \x27Errors\x27, 1) %}
            {% elseif type == constant(\x27WARNING\x27) %}
                {% set class = \x27bg-warning text-white\x27 %}
                {% set title = __(\x27Warning\x27) %}
            {% else %}
                {% set class = \x27bg-info text-white\x27 %}
                {% set title = _n(\x27Information\x27, \x27Information\x27, 1) %}
            {% endif %}

            <div class=\"toast animate__animated animate__tada animate__delay-2s animate__slow\" role=\"alert\" aria-live=\"assertive\" aria-atomic=\"true\">
                <div class=\"toast-header {{ class }} \">
                    <strong class=\"me-auto\">{{ title }}</strong>
                    <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"toast\" aria-label=\"Close\"></button>
                </div>
                <div class=\"toast-body\">
                    {{ message|raw }}
                </div>
            </div>
        {% endfor %}
    {% endif %}
{% endset %}

{% if display_container %}
    {% set toast_location = user_pref(\x27toast_location\x27) %}
    {% if toast_location not in [\x27top-left\x27, \x27top-right\x27, \x27bottom-left\x27, \x27bottom-right\x27] %}
        {% set toast_location = \x27bottom-right\x27 %}
    {% endif %}
    <div class=\"toast-container {{ toast_location }} p-3 messages_after_redirect\" id=\"messages_after_redirect\">
        {{ toasts_html }}

        <script type=\"text/javascript\">
        var initMessagesAfterRedirectToasts = function() {
            var toastElList = [].slice.call(document.querySelectorAll(\x27#messages_after_redirect .toast:not(.show)\x27));
            var toastList = toastElList.map(function (toastEl) {
                var toast = new bootstrap.Toast(toastEl, {
                    delay: 10000,
                })
                toast.show()

                \$(toastEl).on(\x27hidden.bs.toast\x27, function () {
                    \$(toastEl).remove();
                });

                return toast;
            });
        }
        \$(function() {
            initMessagesAfterRedirectToasts();
        });
        </script>
    </div>
{% else %}
    {{ toasts_html }}
{% endif %}
", "components/messages_after_redirect_toasts.html.twig", "/var/www/html/glpi/templates/components/messages_after_redirect_toasts.html.twig");
    }
}
