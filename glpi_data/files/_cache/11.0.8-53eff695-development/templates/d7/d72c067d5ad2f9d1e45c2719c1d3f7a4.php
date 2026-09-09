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

/* error_page.html.twig */
class __TwigTemplate_3fa9543ab0566531cebb5a2d2c0f5429 extends Template
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
        $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call(["Html", (isset($context["header_method"]) || array_key_exists("header_method", $context) ? $context["header_method"] : (function () { throw new RuntimeError('Variable "header_method" does not exist.', 33, $this->source); })())], [(isset($context["page_title"]) || array_key_exists("page_title", $context) ? $context["page_title"] : (function () { throw new RuntimeError('Variable "page_title" does not exist.', 33, $this->source); })())]);
        // line 34
        yield "
";
        // line 35
        yield Twig\Extension\CoreExtension::include($this->env, $context, "error_block.html.twig", ["message" =>         // line 36
(isset($context["message"]) || array_key_exists("message", $context) ? $context["message"] : (function () { throw new RuntimeError('Variable "message" does not exist.', 36, $this->source); })()), "trace" =>         // line 37
(isset($context["trace"]) || array_key_exists("trace", $context) ? $context["trace"] : (function () { throw new RuntimeError('Variable "trace" does not exist.', 37, $this->source); })()), "link_url" =>         // line 38
(isset($context["link_url"]) || array_key_exists("link_url", $context) ? $context["link_url"] : (function () { throw new RuntimeError('Variable "link_url" does not exist.', 38, $this->source); })()), "link_text" =>         // line 39
(isset($context["link_text"]) || array_key_exists("link_text", $context) ? $context["link_text"] : (function () { throw new RuntimeError('Variable "link_text" does not exist.', 39, $this->source); })())], false);
        // line 40
        yield "

";
        // line 42
        $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call(["Html", "footer"]);
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "error_page.html.twig";
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
        return array (  60 => 42,  56 => 40,  54 => 39,  53 => 38,  52 => 37,  51 => 36,  50 => 35,  47 => 34,  45 => 33,  42 => 32,);
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

{% do call([\x27Html\x27, header_method], [page_title]) %}

{{ include(\x27error_block.html.twig\x27, {
    message: message,
    trace: trace,
    link_url: link_url,
    link_text: link_text,
}, with_context = false) }}

{% do call([\x27Html\x27, \x27footer\x27]) %}
", "error_page.html.twig", "/var/www/html/glpi/templates/error_page.html.twig");
    }
}
