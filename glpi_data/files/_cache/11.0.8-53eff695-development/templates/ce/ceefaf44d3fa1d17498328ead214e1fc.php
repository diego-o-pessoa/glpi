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

/* pages/setup/marketplace/card.html.twig */
class __TwigTemplate_4ee563d277280dbd3a13db701f30af36 extends Template
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
        // line 55
        yield "
";
        // line 85
        yield "
";
        // line 86
        if (((isset($context["tab"]) || array_key_exists("tab", $context) ? $context["tab"] : (function () { throw new RuntimeError('Variable "tab" does not exist.', 86, $this->source); })()) == "discover")) {
            // line 87
            yield "    <li class=\"plugin\" data-key=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["plugin"]) || array_key_exists("plugin", $context) ? $context["plugin"] : (function () { throw new RuntimeError('Variable "plugin" does not exist.', 87, $this->source); })()), "key", [], "array", false, false, false, 87), "html", null, true);
            yield "\" data-state=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["plugin"]) || array_key_exists("plugin", $context) ? $context["plugin"] : (function () { throw new RuntimeError('Variable "plugin" does not exist.', 87, $this->source); })()), "state", [], "array", false, false, false, 87), "html", null, true);
            yield "\">
        <div class=\"main ";
            // line 88
            if ((CoreExtension::getAttribute($this->env, $this->source, (isset($context["plugin"]) || array_key_exists("plugin", $context) ? $context["plugin"] : (function () { throw new RuntimeError('Variable "plugin" does not exist.', 88, $this->source); })()), "updatable", [], "array", false, false, false, 88) == true)) {
                yield " bg-warning-subtle";
            }
            yield "\">
            <span class=\"icon\">";
            // line 89
            yield CoreExtension::getAttribute($this->env, $this->source, (isset($context["plugin"]) || array_key_exists("plugin", $context) ? $context["plugin"] : (function () { throw new RuntimeError('Variable "plugin" does not exist.', 89, $this->source); })()), "icon", [], "array", false, false, false, 89);
            yield "</span>
            <span class=\"details\">
                <span class=\"title fs-3 fw-bold\">";
            // line 91
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["plugin"]) || array_key_exists("plugin", $context) ? $context["plugin"] : (function () { throw new RuntimeError('Variable "plugin" does not exist.', 91, $this->source); })()), "name", [], "array", false, false, false, 91), "html", null, true);
            yield "</span>
                ";
            // line 92
            yield CoreExtension::getAttribute($this->env, $this->source, (isset($context["plugin"]) || array_key_exists("plugin", $context) ? $context["plugin"] : (function () { throw new RuntimeError('Variable "plugin" does not exist.', 92, $this->source); })()), "network_info", [], "array", false, false, false, 92);
            yield "
                <p class=\"description\">";
            // line 93
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getTextFromHtml(CoreExtension::getAttribute($this->env, $this->source, (isset($context["plugin"]) || array_key_exists("plugin", $context) ? $context["plugin"] : (function () { throw new RuntimeError('Variable "plugin" does not exist.', 93, $this->source); })()), "description", [], "array", false, false, false, 93)), "html", null, true);
            yield "</p>
            </span>
            <span class=\"buttons\">";
            // line 95
            yield CoreExtension::getAttribute($this->env, $this->source, (isset($context["plugin"]) || array_key_exists("plugin", $context) ? $context["plugin"] : (function () { throw new RuntimeError('Variable "plugin" does not exist.', 95, $this->source); })()), "buttons", [], "array", false, false, false, 95);
            yield "</span>
        </div>
        <div class=\"footer\">
            ";
            // line 98
            yield $this->getTemplateForMacro("macro_misc_left", $context, 98, $this->getSourceContext())->macro_misc_left(...[(isset($context["plugin"]) || array_key_exists("plugin", $context) ? $context["plugin"] : (function () { throw new RuntimeError('Variable "plugin" does not exist.', 98, $this->source); })()), (isset($context["tab"]) || array_key_exists("tab", $context) ? $context["tab"] : (function () { throw new RuntimeError('Variable "tab" does not exist.', 98, $this->source); })())]);
            yield "
            ";
            // line 99
            yield $this->getTemplateForMacro("macro_misc_right", $context, 99, $this->getSourceContext())->macro_misc_right(...[(isset($context["plugin"]) || array_key_exists("plugin", $context) ? $context["plugin"] : (function () { throw new RuntimeError('Variable "plugin" does not exist.', 99, $this->source); })())]);
            yield "
        </div>
    </li>
";
        } else {
            // line 103
            yield "    <li class=\"plugin\" data-key=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["plugin"]) || array_key_exists("plugin", $context) ? $context["plugin"] : (function () { throw new RuntimeError('Variable "plugin" does not exist.', 103, $this->source); })()), "key", [], "array", false, false, false, 103), "html", null, true);
            yield "\" data-state=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["plugin"]) || array_key_exists("plugin", $context) ? $context["plugin"] : (function () { throw new RuntimeError('Variable "plugin" does not exist.', 103, $this->source); })()), "state", [], "array", false, false, false, 103), "html", null, true);
            yield "\">
        <div class=\"main ";
            // line 104
            if ((CoreExtension::getAttribute($this->env, $this->source, (isset($context["plugin"]) || array_key_exists("plugin", $context) ? $context["plugin"] : (function () { throw new RuntimeError('Variable "plugin" does not exist.', 104, $this->source); })()), "updatable", [], "array", false, false, false, 104) == true)) {
                yield " bg-warning-subtle";
            }
            yield "\">
            <span class=\"icon\">";
            // line 105
            yield CoreExtension::getAttribute($this->env, $this->source, (isset($context["plugin"]) || array_key_exists("plugin", $context) ? $context["plugin"] : (function () { throw new RuntimeError('Variable "plugin" does not exist.', 105, $this->source); })()), "icon", [], "array", false, false, false, 105);
            yield "</span>
            <span class=\"details\">
                <span class=\"title fs-3 fw-bold\">";
            // line 107
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["plugin"]) || array_key_exists("plugin", $context) ? $context["plugin"] : (function () { throw new RuntimeError('Variable "plugin" does not exist.', 107, $this->source); })()), "name", [], "array", false, false, false, 107), "html", null, true);
            yield "</span>
                ";
            // line 108
            yield $this->getTemplateForMacro("macro_misc_right", $context, 108, $this->getSourceContext())->macro_misc_right(...[(isset($context["plugin"]) || array_key_exists("plugin", $context) ? $context["plugin"] : (function () { throw new RuntimeError('Variable "plugin" does not exist.', 108, $this->source); })())]);
            yield "
            </span>
            <span class=\"buttons\">";
            // line 110
            yield CoreExtension::getAttribute($this->env, $this->source, (isset($context["plugin"]) || array_key_exists("plugin", $context) ? $context["plugin"] : (function () { throw new RuntimeError('Variable "plugin" does not exist.', 110, $this->source); })()), "buttons", [], "array", false, false, false, 110);
            yield "</span>
        </div>
        <div class=\"footer\">
            ";
            // line 113
            yield $this->getTemplateForMacro("macro_misc_left", $context, 113, $this->getSourceContext())->macro_misc_left(...[(isset($context["plugin"]) || array_key_exists("plugin", $context) ? $context["plugin"] : (function () { throw new RuntimeError('Variable "plugin" does not exist.', 113, $this->source); })()), (isset($context["tab"]) || array_key_exists("tab", $context) ? $context["tab"] : (function () { throw new RuntimeError('Variable "tab" does not exist.', 113, $this->source); })())]);
            yield "
        </div>
    </li>
";
        }
        yield from [];
    }

    // line 33
    public function macro_misc_right($plugin = null, ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "plugin" => $plugin,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 34
            yield "    <span class=\"misc-right\">
        <span class=\"license\">
            ";
            // line 36
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, (isset($context["plugin"]) || array_key_exists("plugin", $context) ? $context["plugin"] : (function () { throw new RuntimeError('Variable "plugin" does not exist.', 36, $this->source); })()), "license", [], "array", false, false, false, 36))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 37
                yield "                <i class=\"ti ti-license\"></i>
                ";
                // line 38
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getTextFromHtml(CoreExtension::getAttribute($this->env, $this->source, (isset($context["plugin"]) || array_key_exists("plugin", $context) ? $context["plugin"] : (function () { throw new RuntimeError('Variable "plugin" does not exist.', 38, $this->source); })()), "license", [], "array", false, false, false, 38)), "html", null, true);
                yield "
            ";
            }
            // line 40
            yield "        </span>
        <span class=\"authors\">
            ";
            // line 42
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, (isset($context["plugin"]) || array_key_exists("plugin", $context) ? $context["plugin"] : (function () { throw new RuntimeError('Variable "plugin" does not exist.', 42, $this->source); })()), "authors", [], "array", false, false, false, 42))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 43
                yield "                <i class=\"ti ti-users\"></i>
                ";
                // line 44
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getTextFromHtml(Twig\Extension\CoreExtension::join(CoreExtension::getAttribute($this->env, $this->source, (isset($context["plugin"]) || array_key_exists("plugin", $context) ? $context["plugin"] : (function () { throw new RuntimeError('Variable "plugin" does not exist.', 44, $this->source); })()), "authors", [], "array", false, false, false, 44), ", ")), "html", null, true);
                yield "
            ";
            }
            // line 46
            yield "        </span>
        <span class=\"version\">
            ";
            // line 48
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, (isset($context["plugin"]) || array_key_exists("plugin", $context) ? $context["plugin"] : (function () { throw new RuntimeError('Variable "plugin" does not exist.', 48, $this->source); })()), "version", [], "array", false, false, false, 48))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 49
                yield "                <i class=\"ti ti-git-branch\"></i>
                ";
                // line 50
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getTextFromHtml(CoreExtension::getAttribute($this->env, $this->source, (isset($context["plugin"]) || array_key_exists("plugin", $context) ? $context["plugin"] : (function () { throw new RuntimeError('Variable "plugin" does not exist.', 50, $this->source); })()), "version", [], "array", false, false, false, 50)), "html", null, true);
                yield "
            ";
            }
            // line 52
            yield "        </span>
    </span>
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 56
    public function macro_misc_left($plugin = null, $tab = null, ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "plugin" => $plugin,
            "tab" => $tab,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 57
            yield "    <span class=\"misc-left\">
        ";
            // line 58
            if (((isset($context["tab"]) || array_key_exists("tab", $context) ? $context["tab"] : (function () { throw new RuntimeError('Variable "tab" does not exist.', 58, $this->source); })()) == "discover")) {
                // line 59
                yield "            <div class=\"note\">";
                yield CoreExtension::getAttribute($this->env, $this->source, (isset($context["plugin"]) || array_key_exists("plugin", $context) ? $context["plugin"] : (function () { throw new RuntimeError('Variable "plugin" does not exist.', 59, $this->source); })()), "stars", [], "array", false, false, false, 59);
                yield "</div>
        ";
            }
            // line 61
            yield "        <span class=\"links\">
            ";
            // line 62
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, (isset($context["plugin"]) || array_key_exists("plugin", $context) ? $context["plugin"] : (function () { throw new RuntimeError('Variable "plugin" does not exist.', 62, $this->source); })()), "homepage_url", [], "array", false, false, false, 62))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 63
                yield "                <a href=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["plugin"]) || array_key_exists("plugin", $context) ? $context["plugin"] : (function () { throw new RuntimeError('Variable "plugin" does not exist.', 63, $this->source); })()), "homepage_url", [], "array", false, false, false, 63), "html", null, true);
                yield "\" target=\"_blank\">
                    <i class=\"ti ti-home-2 add_tooltip\" title=\"";
                // line 64
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Homepage"), "html", null, true);
                yield "\"></i>
                </a>
            ";
            }
            // line 67
            yield "            ";
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, (isset($context["plugin"]) || array_key_exists("plugin", $context) ? $context["plugin"] : (function () { throw new RuntimeError('Variable "plugin" does not exist.', 67, $this->source); })()), "issues_url", [], "array", false, false, false, 67))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 68
                yield "                <a href=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["plugin"]) || array_key_exists("plugin", $context) ? $context["plugin"] : (function () { throw new RuntimeError('Variable "plugin" does not exist.', 68, $this->source); })()), "issues_url", [], "array", false, false, false, 68), "html", null, true);
                yield "\" target=\"_blank\">
                    <i class=\"ti ti-bug add_tooltip\" title=\"";
                // line 69
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Get help"), "html", null, true);
                yield "\"></i>
                </a>
            ";
            }
            // line 72
            yield "            ";
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, (isset($context["plugin"]) || array_key_exists("plugin", $context) ? $context["plugin"] : (function () { throw new RuntimeError('Variable "plugin" does not exist.', 72, $this->source); })()), "readme_url", [], "array", false, false, false, 72))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 73
                yield "                <a href=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["plugin"]) || array_key_exists("plugin", $context) ? $context["plugin"] : (function () { throw new RuntimeError('Variable "plugin" does not exist.', 73, $this->source); })()), "readme_url", [], "array", false, false, false, 73), "html", null, true);
                yield "\" target=\"_blank\">
                    <i class=\"ti ti-book add_tooltip\" title=\"";
                // line 74
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Readme"), "html", null, true);
                yield "\"></i>
                </a>
            ";
            }
            // line 77
            yield "            ";
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, (isset($context["plugin"]) || array_key_exists("plugin", $context) ? $context["plugin"] : (function () { throw new RuntimeError('Variable "plugin" does not exist.', 77, $this->source); })()), "changelog_url", [], "array", false, false, false, 77))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 78
                yield "                <a href=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["plugin"]) || array_key_exists("plugin", $context) ? $context["plugin"] : (function () { throw new RuntimeError('Variable "plugin" does not exist.', 78, $this->source); })()), "changelog_url", [], "array", false, false, false, 78), "html", null, true);
                yield "\" target=\"_blank\">
                    <i class=\"ti ti-news add_tooltip\" title=\"";
                // line 79
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Changelog"), "html", null, true);
                yield "\"></i>
                </a>
            ";
            }
            // line 82
            yield "        </span>
    </span>
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "pages/setup/marketplace/card.html.twig";
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
        return array (  288 => 82,  282 => 79,  277 => 78,  274 => 77,  268 => 74,  263 => 73,  260 => 72,  254 => 69,  249 => 68,  246 => 67,  240 => 64,  235 => 63,  233 => 62,  230 => 61,  224 => 59,  222 => 58,  219 => 57,  206 => 56,  198 => 52,  193 => 50,  190 => 49,  188 => 48,  184 => 46,  179 => 44,  176 => 43,  174 => 42,  170 => 40,  165 => 38,  162 => 37,  160 => 36,  156 => 34,  144 => 33,  134 => 113,  128 => 110,  123 => 108,  119 => 107,  114 => 105,  108 => 104,  101 => 103,  94 => 99,  90 => 98,  84 => 95,  79 => 93,  75 => 92,  71 => 91,  66 => 89,  60 => 88,  53 => 87,  51 => 86,  48 => 85,  45 => 55,  42 => 32,);
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

{% macro misc_right(plugin) %}
    <span class=\"misc-right\">
        <span class=\"license\">
            {% if plugin[\x27license\x27] is not empty %}
                <i class=\"ti ti-license\"></i>
                {{ plugin[\x27license\x27]|html_to_text }}
            {% endif %}
        </span>
        <span class=\"authors\">
            {% if plugin[\x27authors\x27] is not empty %}
                <i class=\"ti ti-users\"></i>
                {{ plugin[\x27authors\x27]|join(\x27, \x27)|html_to_text }}
            {% endif %}
        </span>
        <span class=\"version\">
            {% if plugin[\x27version\x27] is not empty %}
                <i class=\"ti ti-git-branch\"></i>
                {{ plugin[\x27version\x27]|html_to_text }}
            {% endif %}
        </span>
    </span>
{% endmacro %}

{% macro misc_left(plugin, tab) %}
    <span class=\"misc-left\">
        {% if tab == \x27discover\x27 %}
            <div class=\"note\">{{ plugin[\x27stars\x27]|raw }}</div>
        {% endif %}
        <span class=\"links\">
            {% if plugin[\x27homepage_url\x27] is not empty %}
                <a href=\"{{ plugin[\x27homepage_url\x27] }}\" target=\"_blank\">
                    <i class=\"ti ti-home-2 add_tooltip\" title=\"{{ __(\x27Homepage\x27) }}\"></i>
                </a>
            {% endif %}
            {% if plugin[\x27issues_url\x27] is not empty %}
                <a href=\"{{ plugin[\x27issues_url\x27] }}\" target=\"_blank\">
                    <i class=\"ti ti-bug add_tooltip\" title=\"{{ __(\x27Get help\x27) }}\"></i>
                </a>
            {% endif %}
            {% if plugin[\x27readme_url\x27] is not empty %}
                <a href=\"{{ plugin[\x27readme_url\x27] }}\" target=\"_blank\">
                    <i class=\"ti ti-book add_tooltip\" title=\"{{ __(\x27Readme\x27) }}\"></i>
                </a>
            {% endif %}
            {% if plugin[\x27changelog_url\x27] is not empty %}
                <a href=\"{{ plugin[\x27changelog_url\x27] }}\" target=\"_blank\">
                    <i class=\"ti ti-news add_tooltip\" title=\"{{ __(\x27Changelog\x27) }}\"></i>
                </a>
            {% endif %}
        </span>
    </span>
{% endmacro %}

{% if tab == \x27discover\x27 %}
    <li class=\"plugin\" data-key=\"{{ plugin[\x27key\x27] }}\" data-state=\"{{ plugin[\x27state\x27] }}\">
        <div class=\"main {% if plugin[\x27updatable\x27] == true %} bg-warning-subtle{% endif %}\">
            <span class=\"icon\">{{ plugin[\x27icon\x27]|raw }}</span>
            <span class=\"details\">
                <span class=\"title fs-3 fw-bold\">{{ plugin[\x27name\x27] }}</span>
                {{ plugin[\x27network_info\x27]|raw }}
                <p class=\"description\">{{ plugin[\x27description\x27]|html_to_text }}</p>
            </span>
            <span class=\"buttons\">{{ plugin[\x27buttons\x27]|raw }}</span>
        </div>
        <div class=\"footer\">
            {{ _self.misc_left(plugin, tab) }}
            {{ _self.misc_right(plugin) }}
        </div>
    </li>
{% else %}
    <li class=\"plugin\" data-key=\"{{ plugin[\x27key\x27] }}\" data-state=\"{{ plugin[\x27state\x27] }}\">
        <div class=\"main {% if plugin[\x27updatable\x27] == true %} bg-warning-subtle{% endif %}\">
            <span class=\"icon\">{{ plugin[\x27icon\x27]|raw }}</span>
            <span class=\"details\">
                <span class=\"title fs-3 fw-bold\">{{ plugin[\x27name\x27] }}</span>
                {{ _self.misc_right(plugin) }}
            </span>
            <span class=\"buttons\">{{ plugin[\x27buttons\x27]|raw }}</span>
        </div>
        <div class=\"footer\">
            {{ _self.misc_left(plugin, tab) }}
        </div>
    </li>
{% endif %}
", "pages/setup/marketplace/card.html.twig", "/var/www/html/glpi/templates/pages/setup/marketplace/card.html.twig");
    }
}
