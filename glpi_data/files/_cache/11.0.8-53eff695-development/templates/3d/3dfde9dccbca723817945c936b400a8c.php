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

/* layout/parts/head.html.twig */
class __TwigTemplate_31e6bc93cc7917c9d8217c8585ed2f17 extends Template
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
<!DOCTYPE html>
<html lang=\"";
        // line 34
        yield (((array_key_exists("lang", $context) &&  !(null === $context["lang"]))) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["lang"], "html", null, true)) : ("en"));
        yield "\"
    ";
        // line 35
        if ((($tmp = $this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpiisrtl")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 36
            yield "        dir=\"rtl\"
    ";
        }
        // line 38
        yield "    ";
        if ((($tmp = $this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpihighcontrast_css")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 39
            yield "        data-high-contrast=\"1\"
    ";
        }
        // line 41
        yield "    ";
        if (array_key_exists("theme", $context)) {
            // line 42
            yield "        data-glpi-theme=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["theme"]) || array_key_exists("theme", $context) ? $context["theme"] : (function () { throw new RuntimeError('Variable "theme" does not exist.', 42, $this->source); })()), "getKey", [], "method", false, false, false, 42), "html", null, true);
            yield "\"
        data-glpi-theme-dark=\"";
            // line 43
            yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["theme"]) || array_key_exists("theme", $context) ? $context["theme"] : (function () { throw new RuntimeError('Variable "theme" does not exist.', 43, $this->source); })()), "isDarkTheme", [], "method", false, false, false, 43)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("1") : ("0"));
            yield "\"
    ";
        }
        // line 45
        yield "    ";
        if (array_key_exists("glpi_request_id", $context)) {
            // line 46
            yield "        data-glpi-request-id=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["glpi_request_id"]) || array_key_exists("glpi_request_id", $context) ? $context["glpi_request_id"] : (function () { throw new RuntimeError('Variable "glpi_request_id" does not exist.', 46, $this->source); })()), "html", null, true);
            yield "\"
    ";
        }
        // line 48
        yield ">
<head>
   <title>";
        // line 50
        if (array_key_exists("title", $context)) {
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["title"]) || array_key_exists("title", $context) ? $context["title"] : (function () { throw new RuntimeError('Variable "title" does not exist.', 50, $this->source); })()), "html", null, true);
            yield " - ";
        }
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ConfigExtension']->config("app_name"), "html", null, true);
        yield "</title>

   <meta charset=\"utf-8\" />

   ";
        // line 55
        yield "   <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\" />

   ";
        // line 58
        yield "   <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\" />

   ";
        // line 61
        yield "   <meta name=\"robots\" content=\"noindex, nofollow\" />

    ";
        // line 64
        yield "    ";
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(Twig\Extension\CoreExtension::merge((isset($context["custom_header_tags"]) || array_key_exists("custom_header_tags", $context) ? $context["custom_header_tags"] : (function () { throw new RuntimeError('Variable "custom_header_tags" does not exist.', 64, $this->source); })()), $this->extensions['Glpi\Application\View\Extension\PluginExtension']->getPluginsHeaderTags(((array_key_exists("is_anonymous_page", $context)) ? (Twig\Extension\CoreExtension::default((isset($context["is_anonymous_page"]) || array_key_exists("is_anonymous_page", $context) ? $context["is_anonymous_page"] : (function () { throw new RuntimeError('Variable "is_anonymous_page" does not exist.', 64, $this->source); })()), false)) : (false)))));
        foreach ($context['_seq'] as $context["_key"] => $context["header_tag"]) {
            // line 65
            yield "        <";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["header_tag"], "tag", [], "any", false, false, false, 65), "html", null, true);
            yield " ";
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, $context["header_tag"], "properties", [], "any", false, false, false, 65));
            foreach ($context['_seq'] as $context["key"] => $context["value"]) {
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["key"], "html", null, true);
                yield "=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["value"], "html", null, true);
                yield "\" ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['key'], $context['value'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            yield "/>
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['header_tag'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 67
        yield "
   <meta property=\"glpi:csrf_token\" content=\"";
        // line 68
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Session::getNewCSRFToken(true), "html", null, true);
        yield "\" />

   ";
        // line 70
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(Twig\Extension\CoreExtension::merge((isset($context["css_files"]) || array_key_exists("css_files", $context) ? $context["css_files"] : (function () { throw new RuntimeError('Variable "css_files" does not exist.', 70, $this->source); })()), $this->extensions['Glpi\Application\View\Extension\PluginExtension']->getPluginsCssFiles(((array_key_exists("is_anonymous_page", $context)) ? (Twig\Extension\CoreExtension::default((isset($context["is_anonymous_page"]) || array_key_exists("is_anonymous_page", $context) ? $context["is_anonymous_page"] : (function () { throw new RuntimeError('Variable "is_anonymous_page" does not exist.', 70, $this->source); })()), false)) : (false)))));
        foreach ($context['_seq'] as $context["_key"] => $context["css_file"]) {
            // line 71
            yield "      <link rel=\"stylesheet\" type=\"text/css\" href=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\FrontEndAssetsExtension']->cssPath(CoreExtension::getAttribute($this->env, $this->source, $context["css_file"], "path", [], "any", false, false, false, 71), (((CoreExtension::getAttribute($this->env, $this->source, $context["css_file"], "options", [], "any", true, true, false, 71) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, $context["css_file"], "options", [], "any", false, false, false, 71)))) ? (CoreExtension::getAttribute($this->env, $this->source, $context["css_file"], "options", [], "any", false, false, false, 71)) : ([]))), "html", null, true);
            yield "\" />
   ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['css_file'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 73
        yield "
   ";
        // line 74
        yield $this->extensions['Glpi\Application\View\Extension\FrontEndAssetsExtension']->customCss();
        yield "

   <link rel=\"shortcut icon\" type=\"images/x-icon\" href=\"";
        // line 76
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\FrontEndAssetsExtension']->assetPath("/pics/favicon.ico"), "html", null, true);
        yield "\" />

   ";
        // line 78
        yield $this->extensions['Glpi\Application\View\Extension\FrontEndAssetsExtension']->importmap();
        yield "

   ";
        // line 80
        yield $this->extensions['Glpi\Application\View\Extension\FrontEndAssetsExtension']->configJs();
        yield "

   ";
        // line 82
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable((isset($context["js_files"]) || array_key_exists("js_files", $context) ? $context["js_files"] : (function () { throw new RuntimeError('Variable "js_files" does not exist.', 82, $this->source); })()));
        foreach ($context['_seq'] as $context["_key"] => $context["js_file"]) {
            // line 83
            yield "      <script type=\"text/javascript\" src=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\FrontEndAssetsExtension']->jsPath(CoreExtension::getAttribute($this->env, $this->source, $context["js_file"], "path", [], "any", false, false, false, 83), (((CoreExtension::getAttribute($this->env, $this->source, $context["js_file"], "options", [], "any", true, true, false, 83) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, $context["js_file"], "options", [], "any", false, false, false, 83)))) ? (CoreExtension::getAttribute($this->env, $this->source, $context["js_file"], "options", [], "any", false, false, false, 83)) : ([]))), "html", null, true);
            yield "\"></script>
   ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['js_file'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 85
        yield "
   ";
        // line 86
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable((isset($context["js_modules"]) || array_key_exists("js_modules", $context) ? $context["js_modules"] : (function () { throw new RuntimeError('Variable "js_modules" does not exist.', 86, $this->source); })()));
        foreach ($context['_seq'] as $context["_key"] => $context["js_file"]) {
            // line 87
            yield "      <script type=\"module\" src=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\FrontEndAssetsExtension']->jsPath(CoreExtension::getAttribute($this->env, $this->source, $context["js_file"], "path", [], "any", false, false, false, 87), (((CoreExtension::getAttribute($this->env, $this->source, $context["js_file"], "options", [], "any", true, true, false, 87) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, $context["js_file"], "options", [], "any", false, false, false, 87)))) ? (CoreExtension::getAttribute($this->env, $this->source, $context["js_file"], "options", [], "any", false, false, false, 87)) : ([]))), "html", null, true);
            yield "\"></script>
   ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['js_file'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 89
        yield "
   ";
        // line 90
        yield $this->extensions['Glpi\Application\View\Extension\FrontEndAssetsExtension']->localesJs();
        yield "
</head>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "layout/parts/head.html.twig";
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
        return array (  214 => 90,  211 => 89,  202 => 87,  198 => 86,  195 => 85,  186 => 83,  182 => 82,  177 => 80,  172 => 78,  167 => 76,  162 => 74,  159 => 73,  150 => 71,  146 => 70,  141 => 68,  138 => 67,  117 => 65,  112 => 64,  108 => 61,  104 => 58,  100 => 55,  89 => 50,  85 => 48,  79 => 46,  76 => 45,  71 => 43,  66 => 42,  63 => 41,  59 => 39,  56 => 38,  52 => 36,  50 => 35,  46 => 34,  42 => 32,);
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

<!DOCTYPE html>
<html lang=\"{{ lang ?? \x27en\x27 }}\"
    {% if session(\x27glpiisrtl\x27) %}
        dir=\"rtl\"
    {% endif %}
    {% if session(\x27glpihighcontrast_css\x27) %}
        data-high-contrast=\"1\"
    {% endif %}
    {% if theme is defined %}
        data-glpi-theme=\"{{ theme.getKey() }}\"
        data-glpi-theme-dark=\"{{ theme.isDarkTheme() ? \x271\x27 : \x270\x27 }}\"
    {% endif %}
    {% if glpi_request_id is defined %}
        data-glpi-request-id=\"{{ glpi_request_id }}\"
    {% endif %}
>
<head>
   <title>{% if title is defined %}{{ title }} - {% endif %}{{ config(\x27app_name\x27) }}</title>

   <meta charset=\"utf-8\" />

   {# prevent IE to turn into compatible mode... #}
   <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\" />

   {# auto desktop / mobile viewport #}
   <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\" />

   {# prevent robots to index GLPI instances #}
   <meta name=\"robots\" content=\"noindex, nofollow\" />

    {# Custom header tags #}
    {% for header_tag in custom_header_tags|merge(get_plugins_header_tags(is_anonymous_page|default(false))) %}
        <{{ header_tag.tag }} {% for key, value in header_tag.properties %}{{ key }}=\"{{ value }}\" {% endfor %}/>
    {% endfor %}

   <meta property=\"glpi:csrf_token\" content=\"{{ csrf_token(true) }}\" />

   {% for css_file in css_files|merge(get_plugins_css_files(is_anonymous_page|default(false))) %}
      <link rel=\"stylesheet\" type=\"text/css\" href=\"{{ css_path(css_file.path, css_file.options ?? []) }}\" />
   {% endfor %}

   {{ custom_css() }}

   <link rel=\"shortcut icon\" type=\"images/x-icon\" href=\"{{ asset_path(\x27/pics/favicon.ico\x27) }}\" />

   {{ importmap() }}

   {{ config_js() }}

   {% for js_file in js_files %}
      <script type=\"text/javascript\" src=\"{{ js_path(js_file.path, js_file.options ?? []) }}\"></script>
   {% endfor %}

   {% for js_file in js_modules %}
      <script type=\"module\" src=\"{{ js_path(js_file.path, js_file.options ?? []) }}\"></script>
   {% endfor %}

   {{ locales_js() }}
</head>
", "layout/parts/head.html.twig", "/var/www/html/glpi/templates/layout/parts/head.html.twig");
    }
}
