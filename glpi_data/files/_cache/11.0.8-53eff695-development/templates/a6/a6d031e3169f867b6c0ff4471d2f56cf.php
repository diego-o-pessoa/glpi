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

/* layout/parts/user_header.html.twig */
class __TwigTemplate_d144641c23b33e44b1d24e63c3c97884 extends Template
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
        $context["rand_header"] = Twig\Extension\CoreExtension::random($this->env->getCharset());
        // line 34
        yield "
<div class=\"btn-group\">
   ";
        // line 36
        if ((($tmp =  !(null === (isset($context["user"]) || array_key_exists("user", $context) ? $context["user"] : (function () { throw new RuntimeError('Variable "user" does not exist.', 36, $this->source); })()))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 37
            yield "      <div class=\"navbar-nav flex-row order-md-last user-menu\">
         <div class=\"nav-item dropdown\">
            <a href=\"#\" class=\"nav-link d-flex lh-1 text-reset p-1 dropdown-toggle user-menu-dropdown-toggle ";
            // line 39
            if ((($tmp = (isset($context["is_debug_active"]) || array_key_exists("is_debug_active", $context) ? $context["is_debug_active"] : (function () { throw new RuntimeError('Variable "is_debug_active" does not exist.', 39, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                yield "bg-red-lt";
            }
            yield "\"
               data-bs-toggle=\"dropdown\" data-bs-auto-close=\"outside\"
               aria-label=\"";
            // line 41
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("User menu"), "html", null, true);
            yield "\">
               ";
            // line 42
            if ((($tmp =  !(isset($context["anonymous"]) || array_key_exists("anonymous", $context) ? $context["anonymous"] : (function () { throw new RuntimeError('Variable "anonymous" does not exist.', 42, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 43
                yield "                  <div class=\"pe-2 d-none d-xl-block\">
                     <div>";
                // line 44
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $this->extensions['Twig\Extra\String\StringExtension']->createUnicodeString((((CoreExtension::getAttribute($this->env, $this->source, $this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpiactiveprofile"), "name", [], "array", true, true, false, 44) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, $this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpiactiveprofile"), "name", [], "array", false, false, false, 44)))) ? (CoreExtension::getAttribute($this->env, $this->source, $this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpiactiveprofile"), "name", [], "array", false, false, false, 44)) : (""))), "truncate", [35, "..."], "method", false, false, false, 44), "html", null, true);
                yield "</div>
                     ";
                // line 45
                $context["entity_completename"] = $this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpiactive_entity_name");
                // line 46
                yield "                     <div class=\"mt-1 small text-muted-menu\" title=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["entity_completename"]) || array_key_exists("entity_completename", $context) ? $context["entity_completename"] : (function () { throw new RuntimeError('Variable "entity_completename" does not exist.', 46, $this->source); })()), "html", null, true);
                yield "\"
                          data-testid=\"current-entity\"
                          data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\">
                        ";
                // line 49
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->truncateLeft((isset($context["entity_completename"]) || array_key_exists("entity_completename", $context) ? $context["entity_completename"] : (function () { throw new RuntimeError('Variable "entity_completename" does not exist.', 49, $this->source); })())), "html", null, true);
                yield "
                     </div>
                  </div>

                  ";
                // line 53
                yield Twig\Extension\CoreExtension::include($this->env, $context, "components/user/picture.html.twig", ["users_id" => CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source,                 // line 54
(isset($context["user"]) || array_key_exists("user", $context) ? $context["user"] : (function () { throw new RuntimeError('Variable "user" does not exist.', 54, $this->source); })()), "fields", [], "any", false, false, false, 54), "id", [], "array", false, false, false, 54), "with_link" => false, "avatar_size" => ""]);
                // line 57
                yield "
               ";
            }
            // line 59
            yield "            </a>
            <div class=\"dropdown-menu dropdown-menu-end mt-1 dropdown-menu-arrow animate__animated animate__fadeInRight\" data-testid=\"user-menu-dropdown\">
               <h6 class=\"dropdown-header\">";
            // line 61
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemName((isset($context["user"]) || array_key_exists("user", $context) ? $context["user"] : (function () { throw new RuntimeError('Variable "user" does not exist.', 61, $this->source); })())), "html", null, true);
            yield "</h6>

               ";
            // line 63
            if ((($tmp =  !(isset($context["anonymous"]) || array_key_exists("anonymous", $context) ? $context["anonymous"] : (function () { throw new RuntimeError('Variable "anonymous" does not exist.', 63, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 64
                yield "                  ";
                yield Twig\Extension\CoreExtension::include($this->env, $context, "layout/parts/profile_selector.html.twig");
                yield "

                  <div class=\"dropdown-divider\"></div>

                  ";
                // line 68
                if ((($tmp = $this->extensions['Glpi\Application\View\Extension\SessionExtension']->hasItemtypeRight("Config", Twig\Extension\CoreExtension::constant("UPDATE"))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 69
                    yield "                     <a href=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path("/ajax/switchdebug.php"), "html", null, true);
                    yield "\"
                        class=\"dropdown-item ";
                    // line 70
                    if ((($tmp = (isset($context["is_debug_active"]) || array_key_exists("is_debug_active", $context) ? $context["is_debug_active"] : (function () { throw new RuntimeError('Variable "is_debug_active" does not exist.', 70, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                        yield "bg-red-lt";
                    }
                    yield "\"
                        title=\"";
                    // line 71
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Change mode"), "html", null, true);
                    yield "\">
                        <i class=\"ti ti-bug debug\"></i>
                        ";
                    // line 73
                    yield (((($tmp = (isset($context["is_debug_active"]) || array_key_exists("is_debug_active", $context) ? $context["is_debug_active"] : (function () { throw new RuntimeError('Variable "is_debug_active" does not exist.', 73, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Debug mode enabled"), "html", null, true)) : ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Debug mode disabled"), "html", null, true)));
                    yield "
                     </a>
                  ";
                }
                // line 76
                yield "               ";
            }
            // line 77
            yield "
               ";
            // line 79
            yield "
               <div class=\"dropdown-item\">
                  <i class=\"ti ti-language\"></i>
                  ";
            // line 82
            yield $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("User::showSwitchLangForm");
            yield "
               </div>

               <div class=\"dropdown-divider\"></div>

               <a href=\"";
            // line 87
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["help_url"]) || array_key_exists("help_url", $context) ? $context["help_url"] : (function () { throw new RuntimeError('Variable "help_url" does not exist.', 87, $this->source); })()), "html", null, true);
            yield "\" class=\"dropdown-item\" title=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Help"), "html", null, true);
            yield "\">
                  <i class=\"ti ti-help\"></i>
                  ";
            // line 89
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Help"), "html", null, true);
            yield "
               </a>

      ";
            // line 92
            if ((($tmp = Session::haveRight("config", Twig\Extension\CoreExtension::constant("READ"))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 93
                yield "               <a href=\"#\" class=\"dropdown-item\" title=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("About"), "html", null, true);
                yield "\"
                  id=\"show_about_modal_";
                // line 94
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["rand_header"]) || array_key_exists("rand_header", $context) ? $context["rand_header"] : (function () { throw new RuntimeError('Variable "rand_header" does not exist.', 94, $this->source); })()), "html", null, true);
                yield "\"
                  data-testid=\"about-link\">
                  <i class=\"ti ti-info-circle\"></i>
                  ";
                // line 97
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("About"), "html", null, true);
                yield "
                  ";
                // line 98
                if ((($tmp =  !(null === (isset($context["found_new_version"]) || array_key_exists("found_new_version", $context) ? $context["found_new_version"] : (function () { throw new RuntimeError('Variable "found_new_version" does not exist.', 98, $this->source); })()))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 99
                    yield "                     <span class=\"badge bg-info text-dark ms-2\">
                        1
                     </span>
                  ";
                }
                // line 103
                yield "               </a>
      ";
            }
            // line 105
            yield "
               <div class=\"dropdown-divider\"></div>

               <a href=\"";
            // line 108
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path("/front/preference.php"), "html", null, true);
            yield "\" class=\"dropdown-item\" title=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("My settings"), "html", null, true);
            yield "\">
                  <i class=\"ti ti-user-cog\"></i>
                  ";
            // line 110
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("My settings"), "html", null, true);
            yield "
               </a>
               <a href=\"";
            // line 112
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path(("/front/logout.php" . (((($tmp = (($this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpiextauth")) ? ($this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpiextauth")) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("?noAUTO=1") : ("")))), "html", null, true);
            yield "\" class=\"dropdown-item\" title=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Logout"), "html", null, true);
            yield "\">
                  <i class=\"ti ti-logout\"></i>
                  ";
            // line 114
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Logout"), "html", null, true);
            yield "
               </a>
            </div>
         </div>
      </div>

      ";
            // line 120
            if ((($tmp = Session::haveRight("config", Twig\Extension\CoreExtension::constant("READ"))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 121
                yield "      <div class=\"modal fade\" id=\"about_modal_";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["rand_header"]) || array_key_exists("rand_header", $context) ? $context["rand_header"] : (function () { throw new RuntimeError('Variable "rand_header" does not exist.', 121, $this->source); })()), "html", null, true);
                yield "\" role=\"dialog\">
         <div class=\"modal-dialog\">
            <div class=\"modal-content\">
               <div class=\"modal-header\">
                  <h4 class=\"modal-title\">";
                // line 125
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("About"), "html", null, true);
                yield "</h4>
                  <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"";
                // line 126
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Close"), "html", null, true);
                yield "\" title=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Close"), "html", null, true);
                yield "\" data-bs-toggle=\"tooltip\" data-bs-placement=\"left\"></button>
               </div>
               <div class=\"modal-body text-center\">
                  <p>
                     <img src=\"";
                // line 130
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path("pics/logos/logo-GLPI-100-grey.png"), "html", null, true);
                yield "\" title=\"GLPI Logo\" style=\"max-width:100px; height:auto;\" />
                  </p>
                  ";
                // line 132
                if ((($tmp =  !$this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("\\Glpi\\Toolbox\\VersionParser::isStableRelease", [Twig\Extension\CoreExtension::constant("GLPI_VERSION")])) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 133
                    yield "                      <div class=\x27alert alert-important alert-warning d-flex\x27>
                        <strong>⚠️  ";
                    // line 134
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("This version is UNSTABLE and some SECURITY FIXES may not be included."), "html", null, true);
                    yield " ⚠️</strong>
                      </div>
                  ";
                }
                // line 137
                yield "                  <p><a href=\"https://glpi-project.org/\" title=\"Powered by Teclib and contributors\" class=\"copyright\">
                     <p>GLPI ";
                // line 138
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::constant("GLPI_VERSION"), "html", null, true);
                yield "</p>
                     Copyright (C) 2015-";
                // line 139
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::constant("GLPI_YEAR"), "html", null, true);
                yield " Teclib\x27 and contributors
                  </a></p>
                  ";
                // line 141
                if ((($tmp =  !(null === (isset($context["found_new_version"]) || array_key_exists("found_new_version", $context) ? $context["found_new_version"] : (function () { throw new RuntimeError('Variable "found_new_version" does not exist.', 141, $this->source); })()))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 142
                    yield "                     <p>
                        <a href=\"https://glpi-project.org\" target=\"_blank\"
                           title=\"";
                    // line 144
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("You will find it on the GLPI-PROJECT.org site."), "html", null, true);
                    yield "\">
                           ";
                    // line 145
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::sprintf(__("A new version is available: %s."), (isset($context["found_new_version"]) || array_key_exists("found_new_version", $context) ? $context["found_new_version"] : (function () { throw new RuntimeError('Variable "found_new_version" does not exist.', 145, $this->source); })())), "html", null, true);
                    yield "
                           <span class=\"badge bg-info text-dark\">
                              1
                           </span>
                        </a>
                     </p>
                  ";
                }
                // line 152
                yield "               </div>
            </div>
         </div>
      </div>
      ";
            }
            // line 157
            yield "   ";
        }
        // line 158
        yield "</div>

<script type=\"text/javascript\">
\$(function() {
   \$(\"#show_about_modal_";
        // line 162
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["rand_header"]) || array_key_exists("rand_header", $context) ? $context["rand_header"] : (function () { throw new RuntimeError('Variable "rand_header" does not exist.', 162, $this->source); })()), "html", null, true);
        yield "\").click(function(e) {
      e.preventDefault();
      \$(\"#about_modal_";
        // line 164
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["rand_header"]) || array_key_exists("rand_header", $context) ? $context["rand_header"] : (function () { throw new RuntimeError('Variable "rand_header" does not exist.', 164, $this->source); })()), "html", null, true);
        yield "\").remove().modal(\"show\");
   });
});
</script>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "layout/parts/user_header.html.twig";
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
        return array (  329 => 164,  324 => 162,  318 => 158,  315 => 157,  308 => 152,  298 => 145,  294 => 144,  290 => 142,  288 => 141,  283 => 139,  279 => 138,  276 => 137,  270 => 134,  267 => 133,  265 => 132,  260 => 130,  251 => 126,  247 => 125,  239 => 121,  237 => 120,  228 => 114,  221 => 112,  216 => 110,  209 => 108,  204 => 105,  200 => 103,  194 => 99,  192 => 98,  188 => 97,  182 => 94,  177 => 93,  175 => 92,  169 => 89,  162 => 87,  154 => 82,  149 => 79,  146 => 77,  143 => 76,  137 => 73,  132 => 71,  126 => 70,  121 => 69,  119 => 68,  111 => 64,  109 => 63,  104 => 61,  100 => 59,  96 => 57,  94 => 54,  93 => 53,  86 => 49,  79 => 46,  77 => 45,  73 => 44,  70 => 43,  68 => 42,  64 => 41,  57 => 39,  53 => 37,  51 => 36,  47 => 34,  45 => 33,  42 => 32,);
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

{% set rand_header = random() %}

<div class=\"btn-group\">
   {% if user is not null %}
      <div class=\"navbar-nav flex-row order-md-last user-menu\">
         <div class=\"nav-item dropdown\">
            <a href=\"#\" class=\"nav-link d-flex lh-1 text-reset p-1 dropdown-toggle user-menu-dropdown-toggle {% if is_debug_active %}bg-red-lt{% endif %}\"
               data-bs-toggle=\"dropdown\" data-bs-auto-close=\"outside\"
               aria-label=\"{{ __(\x27User menu\x27) }}\">
               {% if not anonymous %}
                  <div class=\"pe-2 d-none d-xl-block\">
                     <div>{{ (session(\x27glpiactiveprofile\x27)[\x27name\x27] ?? \x27\x27)|u.truncate(35, \x27...\x27) }}</div>
                     {% set entity_completename = session(\x27glpiactive_entity_name\x27) %}
                     <div class=\"mt-1 small text-muted-menu\" title=\"{{ entity_completename }}\"
                          data-testid=\"current-entity\"
                          data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\">
                        {{ entity_completename|truncate_left }}
                     </div>
                  </div>

                  {{ include(\x27components/user/picture.html.twig\x27, {
                     \x27users_id\x27: user.fields[\x27id\x27],
                     \x27with_link\x27: false,
                     \x27avatar_size\x27: \x27\x27,
                  }) }}
               {% endif %}
            </a>
            <div class=\"dropdown-menu dropdown-menu-end mt-1 dropdown-menu-arrow animate__animated animate__fadeInRight\" data-testid=\"user-menu-dropdown\">
               <h6 class=\"dropdown-header\">{{ get_item_name(user) }}</h6>

               {% if not anonymous %}
                  {{ include(\x27layout/parts/profile_selector.html.twig\x27) }}

                  <div class=\"dropdown-divider\"></div>

                  {% if has_itemtype_right(\x27Config\x27, constant(\x27UPDATE\x27)) %}
                     <a href=\"{{ path(\x27/ajax/switchdebug.php\x27) }}\"
                        class=\"dropdown-item {% if is_debug_active %}bg-red-lt{% endif %}\"
                        title=\"{{ __(\x27Change mode\x27) }}\">
                        <i class=\"ti ti-bug debug\"></i>
                        {{ is_debug_active ? __(\x27Debug mode enabled\x27) : __(\x27Debug mode disabled\x27) }}
                     </a>
                  {% endif %}
               {% endif %}

               {# @TODO Saved searches panel #}

               <div class=\"dropdown-item\">
                  <i class=\"ti ti-language\"></i>
                  {{ call(\x27User::showSwitchLangForm\x27)|raw }}
               </div>

               <div class=\"dropdown-divider\"></div>

               <a href=\"{{ help_url }}\" class=\"dropdown-item\" title=\"{{ __(\x27Help\x27) }}\">
                  <i class=\"ti ti-help\"></i>
                  {{ __(\x27Help\x27) }}
               </a>

      {% if has_profile_right(\x27config\x27, constant(\x27READ\x27)) %}
               <a href=\"#\" class=\"dropdown-item\" title=\"{{ __(\x27About\x27) }}\"
                  id=\"show_about_modal_{{ rand_header }}\"
                  data-testid=\"about-link\">
                  <i class=\"ti ti-info-circle\"></i>
                  {{ __(\x27About\x27) }}
                  {% if found_new_version is not null %}
                     <span class=\"badge bg-info text-dark ms-2\">
                        1
                     </span>
                  {% endif %}
               </a>
      {% endif %}

               <div class=\"dropdown-divider\"></div>

               <a href=\"{{ path(\x27/front/preference.php\x27) }}\" class=\"dropdown-item\" title=\"{{ __(\x27My settings\x27) }}\">
                  <i class=\"ti ti-user-cog\"></i>
                  {{ __(\x27My settings\x27) }}
               </a>
               <a href=\"{{ path(\x27/front/logout.php\x27 ~ ((session(\x27glpiextauth\x27) ?: false) ? \x27?noAUTO=1\x27 : \x27\x27)) }}\" class=\"dropdown-item\" title=\"{{ __(\x27Logout\x27) }}\">
                  <i class=\"ti ti-logout\"></i>
                  {{ __(\x27Logout\x27) }}
               </a>
            </div>
         </div>
      </div>

      {% if has_profile_right(\x27config\x27, constant(\x27READ\x27)) %}
      <div class=\"modal fade\" id=\"about_modal_{{ rand_header }}\" role=\"dialog\">
         <div class=\"modal-dialog\">
            <div class=\"modal-content\">
               <div class=\"modal-header\">
                  <h4 class=\"modal-title\">{{ __(\x27About\x27) }}</h4>
                  <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"{{ __(\x27Close\x27) }}\" title=\"{{ __(\x27Close\x27) }}\" data-bs-toggle=\"tooltip\" data-bs-placement=\"left\"></button>
               </div>
               <div class=\"modal-body text-center\">
                  <p>
                     <img src=\"{{ path(\x27pics/logos/logo-GLPI-100-grey.png\x27) }}\" title=\"GLPI Logo\" style=\"max-width:100px; height:auto;\" />
                  </p>
                  {% if not call(\x27\\\\Glpi\\\\Toolbox\\\\VersionParser::isStableRelease\x27, [constant(\x27GLPI_VERSION\x27)]) %}
                      <div class=\x27alert alert-important alert-warning d-flex\x27>
                        <strong>⚠️  {{ __(\"This version is UNSTABLE and some SECURITY FIXES may not be included.\") }} ⚠️</strong>
                      </div>
                  {% endif %}
                  <p><a href=\"https://glpi-project.org/\" title=\"Powered by Teclib and contributors\" class=\"copyright\">
                     <p>GLPI {{ constant(\x27GLPI_VERSION\x27) }}</p>
                     Copyright (C) 2015-{{ constant(\x27GLPI_YEAR\x27) }} Teclib\x27 and contributors
                  </a></p>
                  {% if found_new_version is not null %}
                     <p>
                        <a href=\"https://glpi-project.org\" target=\"_blank\"
                           title=\"{{ __(\x27You will find it on the GLPI-PROJECT.org site.\x27) }}\">
                           {{ __(\x27A new version is available: %s.\x27)|format(found_new_version) }}
                           <span class=\"badge bg-info text-dark\">
                              1
                           </span>
                        </a>
                     </p>
                  {% endif %}
               </div>
            </div>
         </div>
      </div>
      {% endif %}
   {% endif %}
</div>

<script type=\"text/javascript\">
\$(function() {
   \$(\"#show_about_modal_{{ rand_header }}\").click(function(e) {
      e.preventDefault();
      \$(\"#about_modal_{{ rand_header }}\").remove().modal(\"show\");
   });
});
</script>
", "layout/parts/user_header.html.twig", "/var/www/html/glpi/templates/layout/parts/user_header.html.twig");
    }
}
