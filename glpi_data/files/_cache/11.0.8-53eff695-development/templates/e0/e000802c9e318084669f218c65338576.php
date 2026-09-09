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

/* layout/parts/impersonate_banner.html.twig */
class __TwigTemplate_ff683832b37b5f42260aa8b9aaf61c68 extends Template
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
        if ((($tmp = (isset($context["is_impersonate_active"]) || array_key_exists("is_impersonate_active", $context) ? $context["is_impersonate_active"] : (function () { throw new RuntimeError('Variable "is_impersonate_active" does not exist.', 33, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 34
            yield "<div class=\"banner-impersonate\">
   <form name=\"form\" method=\"post\" action=\"";
            // line 35
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeFormPath("User"), "html", null, true);
            yield "\" data-submit-once>
      ";
            // line 36
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::sprintf(__("You are impersonating %s."), $this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemName((isset($context["user"]) || array_key_exists("user", $context) ? $context["user"] : (function () { throw new RuntimeError('Variable "user" does not exist.', 36, $this->source); })()))), "html", null, true);
            yield "
      <input type=\"hidden\" name=\"_glpi_csrf_token\" value=\"";
            // line 37
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Session::getNewCSRFToken(), "html", null, true);
            yield "\" />
      <button type=\"submit\" name=\"impersonate\" class=\"btn btn-outline-danger\" value=\"0\">
         ";
            // line 39
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Stop impersonating"), "html", null, true);
            yield "
      </button>
   </form>
</div>
";
        }
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "layout/parts/impersonate_banner.html.twig";
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
        return array (  63 => 39,  58 => 37,  54 => 36,  50 => 35,  47 => 34,  45 => 33,  42 => 32,);
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

{% if is_impersonate_active %}
<div class=\"banner-impersonate\">
   <form name=\"form\" method=\"post\" action=\"{{ \x27User\x27|itemtype_form_path }}\" data-submit-once>
      {{ __(\x27You are impersonating %s.\x27)|format(get_item_name(user)) }}
      <input type=\"hidden\" name=\"_glpi_csrf_token\" value=\"{{ csrf_token() }}\" />
      <button type=\"submit\" name=\"impersonate\" class=\"btn btn-outline-danger\" value=\"0\">
         {{ __(\x27Stop impersonating\x27) }}
      </button>
   </form>
</div>
{% endif %}
", "layout/parts/impersonate_banner.html.twig", "/var/www/html/glpi/templates/layout/parts/impersonate_banner.html.twig");
    }
}
