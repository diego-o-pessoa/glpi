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

/* error_block.html.twig */
class __TwigTemplate_6f9230663e5557ad92a0db6d68be793a extends Template
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
<div class=\"card\">
    <div class=\"card-body\">
        <div role=\"alert\" class=\"alert alert-danger alert-important\">
            <div class=\"d-flex\">
                <div class=\"me-2\">
                    <i class=\"ti ti-alert-triangle fs-2x\"></i>
                </div>
                <div>
                    ";
        // line 41
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["message"]) || array_key_exists("message", $context) ? $context["message"] : (function () { throw new RuntimeError('Variable "message" does not exist.', 41, $this->source); })()), "html", null, true);
        yield "
                    ";
        // line 42
        if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty((isset($context["link_url"]) || array_key_exists("link_url", $context) ? $context["link_url"] : (function () { throw new RuntimeError('Variable "link_url" does not exist.', 42, $this->source); })()))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 43
            yield "                        <br>
                        <a href=\"";
            // line 44
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["link_url"]) || array_key_exists("link_url", $context) ? $context["link_url"] : (function () { throw new RuntimeError('Variable "link_url" does not exist.', 44, $this->source); })()), "html", null, true);
            yield "\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["link_text"]) || array_key_exists("link_text", $context) ? $context["link_text"] : (function () { throw new RuntimeError('Variable "link_text" does not exist.', 44, $this->source); })()), "html", null, true);
            yield "</a>
                    ";
        }
        // line 46
        yield "                </div>
            </div>
        </div>

        ";
        // line 50
        if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty((isset($context["trace"]) || array_key_exists("trace", $context) ? $context["trace"] : (function () { throw new RuntimeError('Variable "trace" does not exist.', 50, $this->source); })()))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 51
            yield "            <pre data-testid=\"stack-trace\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["trace"]) || array_key_exists("trace", $context) ? $context["trace"] : (function () { throw new RuntimeError('Variable "trace" does not exist.', 51, $this->source); })()), "html", null, true);
            yield "</pre>
        ";
        }
        // line 53
        yield "    </div>
</div>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "error_block.html.twig";
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
        return array (  83 => 53,  77 => 51,  75 => 50,  69 => 46,  62 => 44,  59 => 43,  57 => 42,  53 => 41,  42 => 32,);
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

<div class=\"card\">
    <div class=\"card-body\">
        <div role=\"alert\" class=\"alert alert-danger alert-important\">
            <div class=\"d-flex\">
                <div class=\"me-2\">
                    <i class=\"ti ti-alert-triangle fs-2x\"></i>
                </div>
                <div>
                    {{ message }}
                    {% if link_url is not empty %}
                        <br>
                        <a href=\"{{ link_url }}\">{{ link_text }}</a>
                    {% endif %}
                </div>
            </div>
        </div>

        {% if trace is not empty %}
            <pre data-testid=\"stack-trace\">{{ trace }}</pre>
        {% endif %}
    </div>
</div>
", "error_block.html.twig", "/var/www/html/glpi/templates/error_block.html.twig");
    }
}
