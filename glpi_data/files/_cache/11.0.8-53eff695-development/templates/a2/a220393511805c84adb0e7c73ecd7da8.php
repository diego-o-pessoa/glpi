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

/* layout/page_skeleton.html.twig */
class __TwigTemplate_6b985b9210cfb0c0083e4a8c7367eb96 extends Template
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
            'content' => [$this, 'block_content'],
        ];
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 32
        yield "
";
        // line 33
        $context["interface"] = $this->extensions['Glpi\Application\View\Extension\SessionExtension']->getCurrentInterface();
        // line 34
        $context["header_method"] = ((((isset($context["interface"]) || array_key_exists("interface", $context) ? $context["interface"] : (function () { throw new RuntimeError('Variable "interface" does not exist.', 34, $this->source); })()) == "central")) ? ("header") : ("helpHeader"));
        // line 35
        yield "
";
        // line 36
        $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call(["Html", (isset($context["header_method"]) || array_key_exists("header_method", $context) ? $context["header_method"] : (function () { throw new RuntimeError('Variable "header_method" does not exist.', 36, $this->source); })())], ["title" =>         // line 37
(isset($context["title"]) || array_key_exists("title", $context) ? $context["title"] : (function () { throw new RuntimeError('Variable "title" does not exist.', 37, $this->source); })()), "sector" => (((CoreExtension::getAttribute($this->env, $this->source,         // line 38
($context["menu"] ?? null), 0, [], "array", true, true, false, 38) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, (isset($context["menu"]) || array_key_exists("menu", $context) ? $context["menu"] : (function () { throw new RuntimeError('Variable "menu" does not exist.', 38, $this->source); })()), 0, [], "array", false, false, false, 38)))) ? (CoreExtension::getAttribute($this->env, $this->source, (isset($context["menu"]) || array_key_exists("menu", $context) ? $context["menu"] : (function () { throw new RuntimeError('Variable "menu" does not exist.', 38, $this->source); })()), 0, [], "array", false, false, false, 38)) : ("none")), "item" => (((CoreExtension::getAttribute($this->env, $this->source,         // line 39
($context["menu"] ?? null), 1, [], "array", true, true, false, 39) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, (isset($context["menu"]) || array_key_exists("menu", $context) ? $context["menu"] : (function () { throw new RuntimeError('Variable "menu" does not exist.', 39, $this->source); })()), 1, [], "array", false, false, false, 39)))) ? (CoreExtension::getAttribute($this->env, $this->source, (isset($context["menu"]) || array_key_exists("menu", $context) ? $context["menu"] : (function () { throw new RuntimeError('Variable "menu" does not exist.', 39, $this->source); })()), 1, [], "array", false, false, false, 39)) : ("none")), "option" => (((CoreExtension::getAttribute($this->env, $this->source,         // line 40
($context["menu"] ?? null), 2, [], "array", true, true, false, 40) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, (isset($context["menu"]) || array_key_exists("menu", $context) ? $context["menu"] : (function () { throw new RuntimeError('Variable "menu" does not exist.', 40, $this->source); })()), 2, [], "array", false, false, false, 40)))) ? (CoreExtension::getAttribute($this->env, $this->source, (isset($context["menu"]) || array_key_exists("menu", $context) ? $context["menu"] : (function () { throw new RuntimeError('Variable "menu" does not exist.', 40, $this->source); })()), 2, [], "array", false, false, false, 40)) : (""))]);
        // line 42
        yield "
";
        // line 43
        yield from $this->unwrap()->yieldBlock('content', $context, $blocks);
        // line 45
        yield "
";
        // line 46
        $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call(["Html", "footer"]);
        yield from [];
    }

    // line 43
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_content(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "layout/page_skeleton.html.twig";
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
        return array (  72 => 43,  67 => 46,  64 => 45,  62 => 43,  59 => 42,  57 => 40,  56 => 39,  55 => 38,  54 => 37,  53 => 36,  50 => 35,  48 => 34,  46 => 33,  43 => 32,);
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

{% set interface = get_current_interface() %}
{% set header_method = interface == \x27central\x27 ? \x27header\x27 : \x27helpHeader\x27 %}

{% do call([\x27Html\x27, header_method], {
    title: title,
    sector: menu[0] ?? \x27none\x27,
    item  : menu[1] ?? \x27none\x27,
    option: menu[2] ?? \x27\x27,
}) %}

{% block content %}
{% endblock content %}

{% do call([\x27Html\x27, \x27footer\x27]) %}
", "layout/page_skeleton.html.twig", "/var/www/html/glpi/templates/layout/page_skeleton.html.twig");
    }
}
