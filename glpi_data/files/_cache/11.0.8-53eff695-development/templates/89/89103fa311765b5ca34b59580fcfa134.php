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

/* components/danger_modal.html.twig */
class __TwigTemplate_8e066433423d8ca1d5b33208b4f9cd39 extends Template
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
<!-- Modal -->
<div class=\"modal fade\" id=\"";
        // line 34
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["modal_id"]) || array_key_exists("modal_id", $context) ? $context["modal_id"] : (function () { throw new RuntimeError('Variable "modal_id" does not exist.', 34, $this->source); })()), "html", null, true);
        yield "\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">
    <div class=\"modal-dialog\" role=\"document\">
        <div class=\"modal-content\">
            <a type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></a>
            <div class=\"modal-status bg-danger\"></div>
            <div
                class=\"modal-body text-center py-4\">
                <svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon mb-2 text-danger icon-lg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"></path><path d=\"M12 9v4\"></path><path d=\"M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z\"></path><path d=\"M12 16h.01\"></path></svg>
                <h3>";
        // line 42
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("title", $context)) ? (Twig\Extension\CoreExtension::default((isset($context["title"]) || array_key_exists("title", $context) ? $context["title"] : (function () { throw new RuntimeError('Variable "title" does not exist.', 42, $this->source); })()), _x("button", "Are you sure?"))) : (_x("button", "Are you sure?"))), "html", null, true);
        yield "</h3>
                <div class=\"text-muted\">
                    ";
        // line 44
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["content"]) || array_key_exists("content", $context) ? $context["content"] : (function () { throw new RuntimeError('Variable "content" does not exist.', 44, $this->source); })()), "html", null, true);
        yield "
                </div>
            </div>
            <div class=\"modal-footer\">
                <div class=\"w-100\">
                    <div class=\"row\">
                        <div class=\"col\">
                            <a class=\"btn w-100\" data-bs-dismiss=\"modal\">
                                ";
        // line 52
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Cancel"), "html", null, true);
        yield "
                            </a>
                        </div>
                        <div class=\"col\">
                            ";
        // line 56
        yield (isset($context["confirm_btn"]) || array_key_exists("confirm_btn", $context) ? $context["confirm_btn"] : (function () { throw new RuntimeError('Variable "confirm_btn" does not exist.', 56, $this->source); })());
        yield "
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "components/danger_modal.html.twig";
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
        return array (  80 => 56,  73 => 52,  62 => 44,  57 => 42,  46 => 34,  42 => 32,);
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

<!-- Modal -->
<div class=\"modal fade\" id=\"{{ modal_id }}\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">
    <div class=\"modal-dialog\" role=\"document\">
        <div class=\"modal-content\">
            <a type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></a>
            <div class=\"modal-status bg-danger\"></div>
            <div
                class=\"modal-body text-center py-4\">
                <svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon mb-2 text-danger icon-lg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"></path><path d=\"M12 9v4\"></path><path d=\"M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z\"></path><path d=\"M12 16h.01\"></path></svg>
                <h3>{{ title|default(_x(\x27button\x27, \x27Are you sure?\x27)) }}</h3>
                <div class=\"text-muted\">
                    {{ content }}
                </div>
            </div>
            <div class=\"modal-footer\">
                <div class=\"w-100\">
                    <div class=\"row\">
                        <div class=\"col\">
                            <a class=\"btn w-100\" data-bs-dismiss=\"modal\">
                                {{ __(\x27Cancel\x27) }}
                            </a>
                        </div>
                        <div class=\"col\">
                            {{ confirm_btn|raw }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
", "components/danger_modal.html.twig", "/var/www/html/glpi/templates/components/danger_modal.html.twig");
    }
}
