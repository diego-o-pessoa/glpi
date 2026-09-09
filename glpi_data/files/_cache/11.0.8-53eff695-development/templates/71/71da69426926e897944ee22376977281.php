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

/* components/user/picture.html.twig */
class __TwigTemplate_58f9c6cde051fe5b239e732564e75cde extends Template
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
        $context["enable_anonymization"] = (((array_key_exists("enable_anonymization", $context) &&  !(null === $context["enable_anonymization"]))) ? ($context["enable_anonymization"]) : (false));
        // line 34
        $context["avatar_size"] = (((array_key_exists("avatar_size", $context) &&  !(null === $context["avatar_size"]))) ? ($context["avatar_size"]) : ("avatar-md"));
        // line 35
        $context["anonymized"] = ((isset($context["enable_anonymization"]) || array_key_exists("enable_anonymization", $context) ? $context["enable_anonymization"] : (function () { throw new RuntimeError('Variable "enable_anonymization" does not exist.', 35, $this->source); })()) && ($this->extensions['Glpi\Application\View\Extension\ConfigExtension']->getEntityConfig("anonymize_support_agents", $this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpiactive_entity")) != Twig\Extension\CoreExtension::constant("Entity::ANONYMIZE_DISABLED")));
        // line 36
        $context["user"] = (((array_key_exists("user_object", $context) &&  !(null === $context["user_object"]))) ? ($context["user_object"]) : ($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItem("User", (isset($context["users_id"]) || array_key_exists("users_id", $context) ? $context["users_id"] : (function () { throw new RuntimeError('Variable "users_id" does not exist.', 36, $this->source); })()))));
        // line 37
        $context["with_link"] = (((array_key_exists("with_link", $context) &&  !(null === $context["with_link"]))) ? ($context["with_link"]) : (true));
        // line 38
        $context["force_initials"] = (((array_key_exists("force_initials", $context) &&  !(null === $context["force_initials"]))) ? ($context["force_initials"]) : (false));
        // line 39
        if ((($tmp =  !(isset($context["force_initials"]) || array_key_exists("force_initials", $context) ? $context["force_initials"] : (function () { throw new RuntimeError('Variable "force_initials" does not exist.', 39, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 40
            yield "    ";
            $context["user_thumbnail"] = CoreExtension::getAttribute($this->env, $this->source, (isset($context["user"]) || array_key_exists("user", $context) ? $context["user"] : (function () { throw new RuntimeError('Variable "user" does not exist.', 40, $this->source); })()), "getThumbnailPicturePath", [(isset($context["enable_anonymization"]) || array_key_exists("enable_anonymization", $context) ? $context["enable_anonymization"] : (function () { throw new RuntimeError('Variable "enable_anonymization" does not exist.', 40, $this->source); })())], "method", false, false, false, 40);
            // line 41
            yield "    ";
            if ((((isset($context["user_thumbnail"]) || array_key_exists("user_thumbnail", $context) ? $context["user_thumbnail"] : (function () { throw new RuntimeError('Variable "user_thumbnail" does not exist.', 41, $this->source); })()) == null) &&  !$this->extensions['Glpi\Application\View\Extension\ConfigExtension']->getEntityConfig("display_users_initials", $this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpiactive_entity")))) {
                // line 42
                yield "        ";
                $context["user_thumbnail"] = CoreExtension::getAttribute($this->env, $this->source, (isset($context["user"]) || array_key_exists("user", $context) ? $context["user"] : (function () { throw new RuntimeError('Variable "user" does not exist.', 42, $this->source); })()), "getPicturePath", [(isset($context["enable_anonymization"]) || array_key_exists("enable_anonymization", $context) ? $context["enable_anonymization"] : (function () { throw new RuntimeError('Variable "enable_anonymization" does not exist.', 42, $this->source); })())], "method", false, false, false, 42);
                // line 43
                yield "    ";
            }
        } else {
            // line 45
            yield "    ";
            $context["user_thumbnail"] = null;
        }
        // line 47
        yield "
";
        // line 48
        if (((isset($context["with_link"]) || array_key_exists("with_link", $context) ? $context["with_link"] : (function () { throw new RuntimeError('Variable "with_link" does not exist.', 48, $this->source); })()) &&  !(isset($context["anonymized"]) || array_key_exists("anonymized", $context) ? $context["anonymized"] : (function () { throw new RuntimeError('Variable "anonymized" does not exist.', 48, $this->source); })()))) {
            // line 49
            yield "   <a href=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["user"]) || array_key_exists("user", $context) ? $context["user"] : (function () { throw new RuntimeError('Variable "user" does not exist.', 49, $this->source); })()), "getLinkURL", [], "method", false, false, false, 49), "html", null, true);
            yield "\" class=\"d-flex align-items-center\">
";
        }
        // line 51
        yield "
";
        // line 52
        $context["bg_color"] = CoreExtension::getAttribute($this->env, $this->source, (isset($context["user"]) || array_key_exists("user", $context) ? $context["user"] : (function () { throw new RuntimeError('Variable "user" does not exist.', 52, $this->source); })()), "getUserInitialsBgColor", [], "method", false, false, false, 52);
        // line 53
        $context["fg_color"] = $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Toolbox::getFgColor", [(isset($context["bg_color"]) || array_key_exists("bg_color", $context) ? $context["bg_color"] : (function () { throw new RuntimeError('Variable "bg_color" does not exist.', 53, $this->source); })()), 60]);
        // line 54
        yield "<span class=\"avatar ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["avatar_size"]) || array_key_exists("avatar_size", $context) ? $context["avatar_size"] : (function () { throw new RuntimeError('Variable "avatar_size" does not exist.', 54, $this->source); })()), "html", null, true);
        yield " rounded\"
      style=\"";
        // line 55
        if ((($tmp =  !(null === (isset($context["user_thumbnail"]) || array_key_exists("user_thumbnail", $context) ? $context["user_thumbnail"] : (function () { throw new RuntimeError('Variable "user_thumbnail" does not exist.', 55, $this->source); })()))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield "background-image: url(";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["user_thumbnail"]) || array_key_exists("user_thumbnail", $context) ? $context["user_thumbnail"] : (function () { throw new RuntimeError('Variable "user_thumbnail" does not exist.', 55, $this->source); })()), "html", null, true);
            yield "); background-color: inherit; ";
        } else {
            yield " background-color: ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["bg_color"]) || array_key_exists("bg_color", $context) ? $context["bg_color"] : (function () { throw new RuntimeError('Variable "bg_color" does not exist.', 55, $this->source); })()), "html", null, true);
            yield ";";
        }
        yield " aspect-ratio: 1;\">
   ";
        // line 56
        if ((null === (isset($context["user_thumbnail"]) || array_key_exists("user_thumbnail", $context) ? $context["user_thumbnail"] : (function () { throw new RuntimeError('Variable "user_thumbnail" does not exist.', 56, $this->source); })()))) {
            // line 57
            yield "         ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["user"]) || array_key_exists("user", $context) ? $context["user"] : (function () { throw new RuntimeError('Variable "user" does not exist.', 57, $this->source); })()), "getUserInitials", [(isset($context["enable_anonymization"]) || array_key_exists("enable_anonymization", $context) ? $context["enable_anonymization"] : (function () { throw new RuntimeError('Variable "enable_anonymization" does not exist.', 57, $this->source); })())], "method", false, false, false, 57), "html", null, true);
            yield "
   ";
        }
        // line 59
        yield "</span>

";
        // line 61
        if (((isset($context["with_link"]) || array_key_exists("with_link", $context) ? $context["with_link"] : (function () { throw new RuntimeError('Variable "with_link" does not exist.', 61, $this->source); })()) &&  !(isset($context["anonymized"]) || array_key_exists("anonymized", $context) ? $context["anonymized"] : (function () { throw new RuntimeError('Variable "anonymized" does not exist.', 61, $this->source); })()))) {
            // line 62
            yield "   ";
            if ((($tmp = (((array_key_exists("display_login", $context) &&  !(null === $context["display_login"]))) ? ($context["display_login"]) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 63
                yield "      <span class=\"ms-2\">";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["user"]) || array_key_exists("user", $context) ? $context["user"] : (function () { throw new RuntimeError('Variable "user" does not exist.', 63, $this->source); })()), "fields", [], "any", false, false, false, 63), "name", [], "array", false, false, false, 63), "html", null, true);
                yield "</span>
   ";
            }
            // line 65
            yield "
   </a>
";
        }
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "components/user/picture.html.twig";
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
        return array (  134 => 65,  128 => 63,  125 => 62,  123 => 61,  119 => 59,  113 => 57,  111 => 56,  99 => 55,  94 => 54,  92 => 53,  90 => 52,  87 => 51,  81 => 49,  79 => 48,  76 => 47,  72 => 45,  68 => 43,  65 => 42,  62 => 41,  59 => 40,  57 => 39,  55 => 38,  53 => 37,  51 => 36,  49 => 35,  47 => 34,  45 => 33,  42 => 32,);
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

{% set enable_anonymization = enable_anonymization ?? false %}
{% set avatar_size = avatar_size ?? \"avatar-md\" %}
{% set anonymized = enable_anonymization and entity_config(\x27anonymize_support_agents\x27, session(\x27glpiactive_entity\x27)) != constant(\x27Entity::ANONYMIZE_DISABLED\x27) %}
{% set user = user_object ?? get_item(\x27User\x27, users_id) %}
{% set with_link = with_link ?? true %}
{% set force_initials = force_initials ?? false %}
{% if not force_initials %}
    {% set user_thumbnail = user.getThumbnailPicturePath(enable_anonymization) %}
    {% if user_thumbnail == null and not entity_config(\x27display_users_initials\x27, session(\x27glpiactive_entity\x27)) %}
        {% set user_thumbnail = user.getPicturePath(enable_anonymization) %}
    {% endif %}
{% else %}
    {% set user_thumbnail = null %}
{% endif %}

{% if with_link and not anonymized %}
   <a href=\"{{ user.getLinkURL() }}\" class=\"d-flex align-items-center\">
{% endif %}

{% set bg_color = user.getUserInitialsBgColor() %}
{% set fg_color = call(\x27Toolbox::getFgColor\x27, [bg_color, 60]) %}
<span class=\"avatar {{ avatar_size }} rounded\"
      style=\"{% if user_thumbnail is not null %}background-image: url({{ user_thumbnail }}); background-color: inherit; {% else %} background-color: {{ bg_color }};{% endif %} aspect-ratio: 1;\">
   {% if user_thumbnail is null %}
         {{ user.getUserInitials(enable_anonymization) }}
   {% endif %}
</span>

{% if with_link and not anonymized %}
   {% if display_login ?? false %}
      <span class=\"ms-2\">{{ user.fields[\x27name\x27] }}</span>
   {% endif %}

   </a>
{% endif %}
", "components/user/picture.html.twig", "/var/www/html/glpi/templates/components/user/picture.html.twig");
    }
}
