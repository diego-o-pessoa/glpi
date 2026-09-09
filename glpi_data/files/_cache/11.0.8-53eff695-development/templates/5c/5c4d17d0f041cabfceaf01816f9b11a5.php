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

/* layout/parts/page_footer.html.twig */
class __TwigTemplate_b79e285ba7437bb0bcb70656e21f282d extends Template
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
        // line 34
        $context["js_files"] = Twig\Extension\CoreExtension::merge((isset($context["js_files"]) || array_key_exists("js_files", $context) ? $context["js_files"] : (function () { throw new RuntimeError('Variable "js_files" does not exist.', 34, $this->source); })()), $this->extensions['Glpi\Application\View\Extension\PluginExtension']->getPluginsJsScriptsFiles(false));
        // line 35
        $context["js_modules"] = Twig\Extension\CoreExtension::merge((isset($context["js_modules"]) || array_key_exists("js_modules", $context) ? $context["js_modules"] : (function () { throw new RuntimeError('Variable "js_modules" does not exist.', 35, $this->source); })()), $this->extensions['Glpi\Application\View\Extension\PluginExtension']->getPluginsJsModulesFiles(false));
        // line 36
        yield "
            </main> ";
        // line 38
        yield "         </div> ";
        // line 39
        yield "      </div> ";
        // line 40
        yield "   </div> ";
        // line 41
        yield "
   ";
        // line 42
        if ((($tmp = $this->extensions['Glpi\Application\View\Extension\ConfigExtension']->config("maintenance_mode")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 43
            yield "      <div id=\"maintenance-float\">
         <a href=\"";
            // line 44
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path("front/config.form.php?forcetab=Config\$5"), "html", null, true);
            yield "\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("MAINTENANCE MODE"), "html", null, true);
            yield "</a>
      </div>
   ";
        }
        // line 47
        yield "
   ";
        // line 48
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable((isset($context["js_files"]) || array_key_exists("js_files", $context) ? $context["js_files"] : (function () { throw new RuntimeError('Variable "js_files" does not exist.', 48, $this->source); })()));
        foreach ($context['_seq'] as $context["_key"] => $context["js_file"]) {
            // line 49
            yield "      <script type=\"text/javascript\" src=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\FrontEndAssetsExtension']->jsPath(CoreExtension::getAttribute($this->env, $this->source, $context["js_file"], "path", [], "any", false, false, false, 49), (((CoreExtension::getAttribute($this->env, $this->source, $context["js_file"], "options", [], "any", true, true, false, 49) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, $context["js_file"], "options", [], "any", false, false, false, 49)))) ? (CoreExtension::getAttribute($this->env, $this->source, $context["js_file"], "options", [], "any", false, false, false, 49)) : ([]))), "html", null, true);
            yield "\"></script>
   ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['js_file'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 51
        yield "
   ";
        // line 52
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable((isset($context["js_modules"]) || array_key_exists("js_modules", $context) ? $context["js_modules"] : (function () { throw new RuntimeError('Variable "js_modules" does not exist.', 52, $this->source); })()));
        foreach ($context['_seq'] as $context["_key"] => $context["js_file"]) {
            // line 53
            yield "      <script type=\"module\" src=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\FrontEndAssetsExtension']->jsPath(CoreExtension::getAttribute($this->env, $this->source, $context["js_file"], "path", [], "any", false, false, false, 53), (((CoreExtension::getAttribute($this->env, $this->source, $context["js_file"], "options", [], "any", true, true, false, 53) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, $context["js_file"], "options", [], "any", false, false, false, 53)))) ? (CoreExtension::getAttribute($this->env, $this->source, $context["js_file"], "options", [], "any", false, false, false, 53)) : ([]))), "html", null, true);
            yield "\"></script>
   ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['js_file'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 55
        yield "
    ";
        // line 56
        if ((($tmp =  !(null === (isset($context["debug_info"]) || array_key_exists("debug_info", $context) ? $context["debug_info"] : (function () { throw new RuntimeError('Variable "debug_info" does not exist.', 56, $this->source); })()))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 57
            yield "        ";
            yield Twig\Extension\CoreExtension::include($this->env, $context, "components/debug/debug_toolbar.html.twig", ["debug_info" =>             // line 58
(isset($context["debug_info"]) || array_key_exists("debug_info", $context) ? $context["debug_info"] : (function () { throw new RuntimeError('Variable "debug_info" does not exist.', 58, $this->source); })())], false);
            // line 59
            yield "
    ";
        }
        // line 61
        yield "</body>
</html>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "layout/parts/page_footer.html.twig";
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
        return array (  119 => 61,  115 => 59,  113 => 58,  111 => 57,  109 => 56,  106 => 55,  97 => 53,  93 => 52,  90 => 51,  81 => 49,  77 => 48,  74 => 47,  66 => 44,  63 => 43,  61 => 42,  58 => 41,  56 => 40,  54 => 39,  52 => 38,  49 => 36,  47 => 35,  45 => 34,  42 => 32,);
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

{# JS scripts / modules are loaded in the footer in non-anonymous pages #}
{% set js_files = js_files|merge(get_plugins_js_scripts_files(false)) %}
{% set js_modules = js_modules|merge(get_plugins_js_modules_files(false)) %}

            </main> {# #page #}
         </div> {# .page-body #}
      </div> {# .page-wrapper #}
   </div> {# .page #}

   {% if config(\x27maintenance_mode\x27) %}
      <div id=\"maintenance-float\">
         <a href=\"{{ path(\x27front/config.form.php?forcetab=Config\$5\x27) }}\">{{ __(\x27MAINTENANCE MODE\x27) }}</a>
      </div>
   {% endif %}

   {% for js_file in js_files %}
      <script type=\"text/javascript\" src=\"{{ js_path(js_file.path, js_file.options ?? []) }}\"></script>
   {% endfor %}

   {% for js_file in js_modules %}
      <script type=\"module\" src=\"{{ js_path(js_file.path, js_file.options ?? []) }}\"></script>
   {% endfor %}

    {% if debug_info is not null %}
        {{ include(\x27components/debug/debug_toolbar.html.twig\x27, {
            debug_info: debug_info,
        }, with_context = false) }}
    {% endif %}
</body>
</html>
", "layout/parts/page_footer.html.twig", "/var/www/html/glpi/templates/layout/parts/page_footer.html.twig");
    }
}
