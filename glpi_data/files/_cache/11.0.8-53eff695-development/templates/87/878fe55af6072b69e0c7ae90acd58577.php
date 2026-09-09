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

/* layout/page_card_notlogged.html.twig */
class __TwigTemplate_900f365328fd551f91d77f1cc2296788 extends Template
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
            'header_block' => [$this, 'block_header_block'],
            'content_block' => [$this, 'block_content_block'],
            'footer_block' => [$this, 'block_footer_block'],
            'javascript_block' => [$this, 'block_javascript_block'],
        ];
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 32
        yield "
";
        // line 33
        $context["theme"] = $this->extensions['Glpi\Application\View\Extension\FrontEndAssetsExtension']->currentTheme();
        // line 34
        if ((($tmp =  !array_key_exists("css_files", $context)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 35
            yield "   ";
            $context["css_files"] = [["path" => "lib/base.css"], ["path" => "lib/tabler.css"], ["path" => "css/glpi.scss"], ["path" => "css/core_palettes.scss"]];
            // line 41
            yield "
   ";
            // line 42
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable($this->extensions['Glpi\Application\View\Extension\FrontEndAssetsExtension']->getCustomThemesPaths());
            foreach ($context['_seq'] as $context["_key"] => $context["theme_path"]) {
                // line 43
                yield "      ";
                $context["css_files"] = Twig\Extension\CoreExtension::merge((isset($context["css_files"]) || array_key_exists("css_files", $context) ? $context["css_files"] : (function () { throw new RuntimeError('Variable "css_files" does not exist.', 43, $this->source); })()), [["path" => $context["theme_path"]]]);
                // line 44
                yield "   ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['theme_path'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 45
            yield "
   ";
        }
        // line 48
        if ((($tmp =  !array_key_exists("js_files", $context)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 49
            yield "   ";
            $context["js_files"] = [["path" => "lib/base.js"], ["path" => "js/common.js"], ["path" => "lib/fuzzy.js"]];
        }
        // line 55
        if ((($tmp =  !array_key_exists("js_modules", $context)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 56
            yield "   ";
            $context["js_modules"] = [];
        }
        // line 58
        if ((($tmp =  !array_key_exists("custom_header_tags", $context)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 59
            yield "   ";
            $context["custom_header_tags"] = [];
        }
        // line 61
        yield "
";
        // line 63
        $context["js_files"] = Twig\Extension\CoreExtension::merge((isset($context["js_files"]) || array_key_exists("js_files", $context) ? $context["js_files"] : (function () { throw new RuntimeError('Variable "js_files" does not exist.', 63, $this->source); })()), $this->extensions['Glpi\Application\View\Extension\PluginExtension']->getPluginsJsScriptsFiles(true));
        // line 64
        $context["js_modules"] = Twig\Extension\CoreExtension::merge((isset($context["js_modules"]) || array_key_exists("js_modules", $context) ? $context["js_modules"] : (function () { throw new RuntimeError('Variable "js_modules" does not exist.', 64, $this->source); })()), $this->extensions['Glpi\Application\View\Extension\PluginExtension']->getPluginsJsModulesFiles(true));
        // line 65
        yield "
";
        // line 66
        $context["is_anonymous_page"] = true;
        // line 67
        yield "
";
        // line 68
        yield Twig\Extension\CoreExtension::include($this->env, $context, "layout/parts/head.html.twig");
        yield "
<body class=\"welcome-anonymous\">
   <div class=\"skip-links\">
      <a class=\"visually-hidden-focusable skip-link\" href=\"#page\">";
        // line 71
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Go to main content"), "html", null, true);
        yield "</a>
   </div>
   <main id=\"page\" class=\"page-anonymous\" role=\"main\" tabindex=\"-1\">
      <div class=\"flex-fill d-flex flex-column justify-content-center py-4 mt-4\">
         ";
        // line 75
        $context["style"] = null;
        // line 76
        yield "         ";
        if (array_key_exists("card_md_width", $context)) {
            // line 77
            yield "            ";
            $context["style"] = "max-width: 40rem";
            // line 78
            yield "         ";
        }
        // line 79
        yield "         ";
        if (array_key_exists("card_bg_width", $context)) {
            // line 80
            yield "            ";
            $context["style"] = "max-width: 60rem";
            // line 81
            yield "         ";
        }
        // line 82
        yield "
         <div class=\"container-tight py-6\" ";
        // line 83
        if ((($tmp =  !(null === (isset($context["style"]) || array_key_exists("style", $context) ? $context["style"] : (function () { throw new RuntimeError('Variable "style" does not exist.', 83, $this->source); })()))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield "style=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["style"]) || array_key_exists("style", $context) ? $context["style"] : (function () { throw new RuntimeError('Variable "style" does not exist.', 83, $this->source); })()), "html", null, true);
            yield "\"";
        }
        yield ">
            <div class=\"text-center\">
               <div class=\"col-md\">
                  <span class=\"glpi-logo mb-4\" title=\"GLPI\"></span>
               </div>
            </div>
            <div class=\"card card-md main-content-card\">
               ";
        // line 91
        yield "               <div class=\"card-header\">";
        yield from $this->unwrap()->yieldBlock('header_block', $context, $blocks);
        yield "</div>
               <div class=\"card-body\">
                  ";
        // line 93
        yield from $this->unwrap()->yieldBlock('content_block', $context, $blocks);
        // line 94
        yield "               </div>
            </div>

            <div class=\"text-center text-muted mt-3\">
               ";
        // line 98
        yield from $this->unwrap()->yieldBlock('footer_block', $context, $blocks);
        // line 99
        yield "            </div>
         </div>
      </div>
   </main>

   ";
        // line 104
        yield from $this->unwrap()->yieldBlock('javascript_block', $context, $blocks);
        // line 105
        yield "</body>
</html>
";
        yield from [];
    }

    // line 91
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_header_block(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        yield from [];
    }

    // line 93
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_content_block(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        yield from [];
    }

    // line 98
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_footer_block(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        yield from [];
    }

    // line 104
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_javascript_block(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "layout/page_card_notlogged.html.twig";
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
        return array (  221 => 104,  211 => 98,  201 => 93,  191 => 91,  184 => 105,  182 => 104,  175 => 99,  173 => 98,  167 => 94,  165 => 93,  159 => 91,  145 => 83,  142 => 82,  139 => 81,  136 => 80,  133 => 79,  130 => 78,  127 => 77,  124 => 76,  122 => 75,  115 => 71,  109 => 68,  106 => 67,  104 => 66,  101 => 65,  99 => 64,  97 => 63,  94 => 61,  90 => 59,  88 => 58,  84 => 56,  82 => 55,  78 => 49,  76 => 48,  72 => 45,  66 => 44,  63 => 43,  59 => 42,  56 => 41,  53 => 35,  51 => 34,  49 => 33,  46 => 32,);
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

{% set theme = current_theme() %}
{% if css_files is not defined %}
   {% set css_files = [
       {\x27path\x27: \x27lib/base.css\x27},
       {\x27path\x27: \x27lib/tabler.css\x27},
       {\x27path\x27: \x27css/glpi.scss\x27},
       {\x27path\x27: \x27css/core_palettes.scss\x27}
   ] %}

   {% for theme_path in get_custom_themes_paths() %}
      {% set css_files = css_files|merge([{\x27path\x27: theme_path}]) %}
   {% endfor %}

   {# TODO : load hight contrast css #}
{% endif %}
{% if js_files is not defined %}
   {% set js_files = [
       {\x27path\x27: \x27lib/base.js\x27},
       {\x27path\x27: \x27js/common.js\x27},
       {\x27path\x27: \x27lib/fuzzy.js\x27}
   ] %}
{% endif %}
{% if js_modules is not defined %}
   {% set js_modules = [] %}
{% endif %}
{% if custom_header_tags is not defined %}
   {% set custom_header_tags = [] %}
{% endif %}

{# JS scripts / modules are loaded in the header in anonymous pages #}
{% set js_files = js_files|merge(get_plugins_js_scripts_files(true)) %}
{% set js_modules = js_modules|merge(get_plugins_js_modules_files(true)) %}

{% set is_anonymous_page = true %}

{{ include(\x27layout/parts/head.html.twig\x27) }}
<body class=\"welcome-anonymous\">
   <div class=\"skip-links\">
      <a class=\"visually-hidden-focusable skip-link\" href=\"#page\">{{ __(\x27Go to main content\x27) }}</a>
   </div>
   <main id=\"page\" class=\"page-anonymous\" role=\"main\" tabindex=\"-1\">
      <div class=\"flex-fill d-flex flex-column justify-content-center py-4 mt-4\">
         {% set style = null %}
         {% if card_md_width is defined %}
            {% set style = \x27max-width: 40rem\x27 %}
         {% endif %}
         {% if card_bg_width is defined %}
            {% set style = \x27max-width: 60rem\x27 %}
         {% endif %}

         <div class=\"container-tight py-6\" {% if style is not null %}style=\"{{ style }}\"{% endif %}>
            <div class=\"text-center\">
               <div class=\"col-md\">
                  <span class=\"glpi-logo mb-4\" title=\"GLPI\"></span>
               </div>
            </div>
            <div class=\"card card-md main-content-card\">
               {# Keep the header on one line so that the :empty CSS selector will work #}
               <div class=\"card-header\">{% block header_block %}{% endblock %}</div>
               <div class=\"card-body\">
                  {% block content_block %}{% endblock %}
               </div>
            </div>

            <div class=\"text-center text-muted mt-3\">
               {% block footer_block %}{% endblock %}
            </div>
         </div>
      </div>
   </main>

   {% block javascript_block %}{% endblock %}
</body>
</html>
", "layout/page_card_notlogged.html.twig", "/var/www/html/glpi/templates/layout/page_card_notlogged.html.twig");
    }
}
