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

/* layout/parts/global_search_form.html.twig */
class __TwigTemplate_6490798e57b9943baf1dd326c8092353 extends Template
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
        if ((($this->extensions['Glpi\Application\View\Extension\SessionExtension']->getCurrentInterface() == "central") && $this->extensions['Glpi\Application\View\Extension\ConfigExtension']->config("allow_search_global"))) {
            // line 34
            yield "<form action=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path("front/search.php"), "html", null, true);
            yield "\" role=\"search\" method=\"get\" data-submit-once>
   <label for=\"global-search\" class=\"visually-hidden\">";
            // line 35
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Search…"), "html", null, true);
            yield "</label>
   <div class=\"input-group input-group-flat\">
      <input type=\"text\" id=\"global-search\" class=\"form-control\" name=\"globalsearch\" placeholder=\"";
            // line 37
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Search…"), "html", null, true);
            yield "\" />
      <span class=\"input-group-text p-0\">
         <button type=\"submit\" class=\"btn btn-link p-0 m-0\" title=\"";
            // line 39
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Search…"), "html", null, true);
            yield "\">
            <span class=\"ti ti-search\" aria-hidden=\"true\"></span>
         </button>
      </span>
   </div>
</form>
";
        }
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "layout/parts/global_search_form.html.twig";
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
        return array (  62 => 39,  57 => 37,  52 => 35,  47 => 34,  45 => 33,  42 => 32,);
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

{% if get_current_interface() == \x27central\x27 and config(\x27allow_search_global\x27) %}
<form action=\"{{ path(\x27front/search.php\x27) }}\" role=\"search\" method=\"get\" data-submit-once>
   <label for=\"global-search\" class=\"visually-hidden\">{{ __(\x27Search…\x27) }}</label>
   <div class=\"input-group input-group-flat\">
      <input type=\"text\" id=\"global-search\" class=\"form-control\" name=\"globalsearch\" placeholder=\"{{ __(\x27Search…\x27) }}\" />
      <span class=\"input-group-text p-0\">
         <button type=\"submit\" class=\"btn btn-link p-0 m-0\" title=\"{{ __(\x27Search…\x27) }}\">
            <span class=\"ti ti-search\" aria-hidden=\"true\"></span>
         </button>
      </span>
   </div>
</form>
{% endif %}
", "layout/parts/global_search_form.html.twig", "/var/www/html/glpi/templates/layout/parts/global_search_form.html.twig");
    }
}
