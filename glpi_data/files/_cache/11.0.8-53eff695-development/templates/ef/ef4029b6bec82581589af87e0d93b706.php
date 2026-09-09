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

/* layout/parts/goto_button.html.twig */
class __TwigTemplate_0379f3c47e6cea5c90ad7980899956cc extends Template
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
        if (($this->extensions['Glpi\Application\View\Extension\SessionExtension']->getCurrentInterface() == "central")) {
            // line 34
            yield "   ";
            $context["rand"] = Twig\Extension\CoreExtension::random($this->env->getCharset());
            // line 35
            yield "   ";
            $context["shortcut"] = __("Ctrl+Alt+G");
            // line 36
            yield "   ";
            if (((isset($context["platform"]) || array_key_exists("platform", $context) ? $context["platform"] : (function () { throw new RuntimeError('Variable "platform" does not exist.', 36, $this->source); })()) == Twig\Extension\CoreExtension::constant("donatj\\UserAgent\\Platforms::MACINTOSH"))) {
                // line 37
                yield "      ";
                $context["shortcut"] = __("Option+Command+G");
                // line 38
                yield "   ";
            }
            // line 39
            yield "
   <button class=\"btn btn-sm btn-ghost-secondary trigger-fuzzy justify-content-start mb-md-2\"
           title=\"";
            // line 41
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["shortcut"]) || array_key_exists("shortcut", $context) ? $context["shortcut"] : (function () { throw new RuntimeError('Variable "shortcut" does not exist.', 41, $this->source); })()), "html", null, true);
            yield "\"
           data-bs-toggle=\"tooltip\"
           data-bs-placement=\"right\">
      <i class=\"ti ti-arrow-big-right me-1\"></i>
      <span class=\"menu-label ";
            // line 45
            yield (((($tmp =  !(isset($context["is_vertical"]) || array_key_exists("is_vertical", $context) ? $context["is_vertical"] : (function () { throw new RuntimeError('Variable "is_vertical" does not exist.', 45, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("d-block d-xl-none d-xxl-block") : (""));
            yield "\">
         ";
            // line 46
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Find menu"), "html", null, true);
            yield "
      </span>
   </button>
   <div id=\"fuzzy-search-modal\"></div>
   <script type=\"module\">
       if (document.querySelector(\x27#fuzzy-search-modal\x27).__vue_app__ === undefined) {
           window.Vue.createApp(window.Vue.components[\x27FuzzySearch/Modal\x27].component).mount(\x27#fuzzy-search-modal\x27);
       }
   </script>
";
        }
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "layout/parts/goto_button.html.twig";
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
        return array (  77 => 46,  73 => 45,  66 => 41,  62 => 39,  59 => 38,  56 => 37,  53 => 36,  50 => 35,  47 => 34,  45 => 33,  42 => 32,);
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

{% if get_current_interface() == \x27central\x27 %}
   {% set rand = random() %}
   {% set shortcut = __(\x27Ctrl+Alt+G\x27) %}
   {% if platform == constant(\"donatj\\\\UserAgent\\\\Platforms::MACINTOSH\") %}
      {% set shortcut = __(\x27Option+Command+G\x27) %}
   {% endif %}

   <button class=\"btn btn-sm btn-ghost-secondary trigger-fuzzy justify-content-start mb-md-2\"
           title=\"{{ shortcut }}\"
           data-bs-toggle=\"tooltip\"
           data-bs-placement=\"right\">
      <i class=\"ti ti-arrow-big-right me-1\"></i>
      <span class=\"menu-label {{ not is_vertical ? \"d-block d-xl-none d-xxl-block\" : \"\" }}\">
         {{ __(\x27Find menu\x27) }}
      </span>
   </button>
   <div id=\"fuzzy-search-modal\"></div>
   <script type=\"module\">
       if (document.querySelector(\x27#fuzzy-search-modal\x27).__vue_app__ === undefined) {
           window.Vue.createApp(window.Vue.components[\x27FuzzySearch/Modal\x27].component).mount(\x27#fuzzy-search-modal\x27);
       }
   </script>
{% endif %}
", "layout/parts/goto_button.html.twig", "/var/www/html/glpi/templates/layout/parts/goto_button.html.twig");
    }
}
