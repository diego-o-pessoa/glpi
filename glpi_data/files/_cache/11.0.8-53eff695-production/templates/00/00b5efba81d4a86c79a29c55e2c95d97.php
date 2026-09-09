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

/* pages/setup/crontask/crontask.html.twig */
class __TwigTemplate_03db81f6ab839ba6217d63a015b67379 extends Template
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
            'form_fields' => [$this, 'block_form_fields'],
        ];
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 32
        yield "
";
        // line 33
        $macros["fields"] = $this->macros["fields"] = $this->load("components/form/fields_macros.html.twig", 33)->unwrap();
        // line 34
        yield "
<div class=\"asset\">
   ";
        // line 36
        yield Twig\Extension\CoreExtension::include($this->env, $context, "components/form/header.html.twig");
        yield "

   ";
        // line 38
        $context["rand"] = Twig\Extension\CoreExtension::random($this->env->getCharset());
        // line 39
        yield "   ";
        $context["params"] = (((array_key_exists("params", $context) &&  !(null === $context["params"]))) ? ($context["params"]) : ([]));
        // line 40
        yield "   ";
        $context["target"] = (((CoreExtension::getAttribute($this->env, $this->source, ($context["params"] ?? null), "target", [], "array", true, true, false, 40) &&  !(null === (($_v0 = ($context["params"] ?? null)) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0["target"] ?? null) : null)))) ? ((($_v1 = ($context["params"] ?? null)) && is_array($_v1) || $_v1 instanceof ArrayAccess ? ($_v1["target"] ?? null) : null)) : (CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "getFormURL", [], "method", false, false, false, 40)));
        // line 41
        yield "   ";
        $context["withtemplate"] = (((CoreExtension::getAttribute($this->env, $this->source, ($context["params"] ?? null), "withtemplate", [], "array", true, true, false, 41) &&  !(null === (($_v2 = ($context["params"] ?? null)) && is_array($_v2) || $_v2 instanceof ArrayAccess ? ($_v2["withtemplate"] ?? null) : null)))) ? ((($_v3 = ($context["params"] ?? null)) && is_array($_v3) || $_v3 instanceof ArrayAccess ? ($_v3["withtemplate"] ?? null) : null)) : (""));
        // line 42
        yield "   ";
        $context["item_type"] = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "getType", [], "method", false, false, false, 42);
        // line 43
        yield "   ";
        $context["field_options"] = [];
        // line 44
        yield "   ";
        $context["can_execute"] = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "canEdit", [(($_v4 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 44)) && is_array($_v4) || $_v4 instanceof ArrayAccess ? ($_v4["id"] ?? null) : null)], "method", false, false, false, 44);
        // line 45
        yield "
   <div class=\"card-body d-flex flex-wrap\">
      <div class=\"col-12 col-xxl-12 flex-column\">
         <div class=\"d-flex flex-row flex-wrap flex-xl-nowrap\">
            <div class=\"row flex-row align-items-start flex-grow-1\">
               <div class=\"row flex-row\">
                  ";
        // line 51
        yield from $this->unwrap()->yieldBlock('form_fields', $context, $blocks);
        // line 170
        yield "               </div> ";
        // line 171
        yield "            </div> ";
        // line 172
        yield "         </div> ";
        // line 173
        yield "      </div>
   </div> ";
        // line 175
        yield "
   ";
        // line 176
        yield Twig\Extension\CoreExtension::include($this->env, $context, "components/form/buttons.html.twig");
        yield "
</div>
";
        yield from [];
    }

    // line 51
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_form_fields(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 52
        yield "                     ";
        $context["name"] = (($_v5 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 52)) && is_array($_v5) || $_v5 instanceof ArrayAccess ? ($_v5["name"] ?? null) : null);
        // line 53
        yield "                     ";
        if ((($tmp = ($context["plugin_info"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 54
            yield "                        ";
            $context["name"] = Twig\Extension\CoreExtension::sprintf(__("%1\$s - %2\$s"), (($_v6 = ($context["plugin_info"] ?? null)) && is_array($_v6) || $_v6 instanceof ArrayAccess ? ($_v6["plugin"] ?? null) : null), ($context["name"] ?? null));
            // line 55
            yield "                     ";
        }
        // line 56
        yield "                     ";
        $context["name_field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 57
            yield "                        <span class=\"fw-bold\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["name"] ?? null), "html", null, true);
            yield "</span>
                     ";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
        // line 59
        yield "                     ";
        yield $macros["fields"]->getTemplateForMacro("macro_htmlField", $context, 59, $this->getSourceContext())->macro_htmlField(...["name", ($context["name_field"] ?? null), __("Name")]);
        yield "

                     ";
        // line 61
        yield $macros["fields"]->getTemplateForMacro("macro_textareaField", $context, 61, $this->getSourceContext())->macro_textareaField(...["comment", (($_v7 = CoreExtension::getAttribute($this->env, $this->source,         // line 63
($context["item"] ?? null), "fields", [], "any", false, false, false, 63)) && is_array($_v7) || $_v7 instanceof ArrayAccess ? ($_v7["comment"] ?? null) : null), _n("Comment", "Comments", Session::getPluralNumber()),         // line 65
($context["field_options"] ?? null)]);
        // line 66
        yield "

                     ";
        // line 68
        yield $macros["fields"]->getTemplateForMacro("macro_htmlField", $context, 68, $this->getSourceContext())->macro_htmlField(...["description", $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "getDescription", [CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "getID", [], "any", false, false, false, 68)], "method", false, false, false, 68)), __("Description")]);
        yield "

                     ";
        // line 70
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownFrequency", $context, 70, $this->getSourceContext())->macro_dropdownFrequency(...["frequency", (($_v8 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 70)) && is_array($_v8) || $_v8 instanceof ArrayAccess ? ($_v8["frequency"] ?? null) : null), __("Run frequency")]);
        yield "

                     ";
        // line 73
        yield "                     ";
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownArrayField", $context, 73, $this->getSourceContext())->macro_dropdownArrayField(...["state", (($_v9 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 73)) && is_array($_v9) || $_v9 instanceof ArrayAccess ? ($_v9["state"] ?? null) : null), [ (string)Twig\Extension\CoreExtension::constant("CronTask::STATE_DISABLE") => __("Disabled"),  (string)Twig\Extension\CoreExtension::constant("CronTask::STATE_WAITING") => __("Scheduled")], __("Status")]);
        // line 76
        yield "

                     ";
        // line 79
        yield "                     ";
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownArrayField", $context, 79, $this->getSourceContext())->macro_dropdownArrayField(...["mode", (($_v10 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 79)) && is_array($_v10) || $_v10 instanceof ArrayAccess ? ($_v10["mode"] ?? null) : null), [ (string)Twig\Extension\CoreExtension::constant("CronTask::MODE_INTERNAL") => __("GLPI"),  (string)Twig\Extension\CoreExtension::constant("CronTask::MODE_EXTERNAL") => __("CLI")], __("Run mode")]);
        // line 82
        yield "

                     ";
        // line 85
        yield "                     ";
        $context["run_period_field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 86
            yield "                        <div class=\"d-flex align-items-center\">
                           ";
            // line 87
            yield $macros["fields"]->getTemplateForMacro("macro_dropdownNumberField", $context, 87, $this->getSourceContext())->macro_dropdownNumberField(...["hourmin", (($_v11 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 87)) && is_array($_v11) || $_v11 instanceof ArrayAccess ? ($_v11["hourmin"] ?? null) : null), null, ["min" => 0, "max" => 24, "no_label" => true, "field_class" => ""]]);
            // line 92
            yield "
                           <i class=\"ti ti-arrow-right mb-3\"></i>
                           ";
            // line 94
            yield $macros["fields"]->getTemplateForMacro("macro_dropdownNumberField", $context, 94, $this->getSourceContext())->macro_dropdownNumberField(...["hourmax", (($_v12 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 94)) && is_array($_v12) || $_v12 instanceof ArrayAccess ? ($_v12["hourmax"] ?? null) : null), null, ["min" => 0, "max" => 24, "no_label" => true, "field_class" => ""]]);
            // line 99
            yield "
                        </div>
                     ";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
        // line 102
        yield "                     ";
        yield $macros["fields"]->getTemplateForMacro("macro_htmlField", $context, 102, $this->getSourceContext())->macro_htmlField(...["run_period", ($context["run_period_field"] ?? null), __("Run period")]);
        yield "

                     ";
        // line 105
        yield "                     ";
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownNumberField", $context, 105, $this->getSourceContext())->macro_dropdownNumberField(...["logs_lifetime", (($_v13 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 105)) && is_array($_v13) || $_v13 instanceof ArrayAccess ? ($_v13["logs_lifetime"] ?? null) : null), __("Number of days this action logs are stored"), ["min" => 10, "max" => 360, "step" => 10, "toadd" => [__("Infinite")]]]);
        // line 112
        yield "

                     ";
        // line 115
        yield "                     ";
        if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, ($context["item_meta"] ?? null), "param_description", [], "any", false, false, false, 115))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 116
            yield "                        ";
            yield $macros["fields"]->getTemplateForMacro("macro_dropdownNumberField", $context, 116, $this->getSourceContext())->macro_dropdownNumberField(...["param", (($_v14 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 116)) && is_array($_v14) || $_v14 instanceof ArrayAccess ? ($_v14["param"] ?? null) : null), CoreExtension::getAttribute($this->env, $this->source, ($context["item_meta"] ?? null), "param_description", [], "any", false, false, false, 116), ["min" => 0, "max" => 10000]]);
            // line 119
            yield "
                     ";
        }
        // line 121
        yield "
                     <div class=\"row flex-row\">
                        ";
        // line 124
        yield "                        ";
        $context["last_run_field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 125
            yield "                           ";
            if (Twig\Extension\CoreExtension::testEmpty((($_v15 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 125)) && is_array($_v15) || $_v15 instanceof ArrayAccess ? ($_v15["lastrun"] ?? null) : null))) {
                // line 126
                yield "                              ";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Never"), "html", null, true);
                yield "
                           ";
            } else {
                // line 128
                yield "                              ";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getFormattedDatetime((($_v16 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 128)) && is_array($_v16) || $_v16 instanceof ArrayAccess ? ($_v16["lastrun"] ?? null) : null)), "html", null, true);
                yield "
                              ";
                // line 129
                if ((($tmp = ($context["can_execute"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 130
                    yield "                                 ";
                    // line 131
                    yield "                                 <button class=\"btn btn-icon border-0\" type=\"submit\" name=\"resetdate\" title=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Blank"), "html", null, true);
                    yield "\">
                                    <i class=\"ti ti-circle-x me-1\"></i>
                                 </button>
                              ";
                }
                // line 135
                yield "                           ";
            }
            // line 136
            yield "                        ";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
        // line 137
        yield "                        ";
        yield $macros["fields"]->getTemplateForMacro("macro_htmlField", $context, 137, $this->getSourceContext())->macro_htmlField(...["last_run", ($context["last_run_field"] ?? null), __("Last run")]);
        yield "

                        ";
        // line 140
        yield "                        ";
        $context["launch"] = (((($_v17 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 140)) && is_array($_v17) || $_v17 instanceof ArrayAccess ? ($_v17["state"] ?? null) : null) != Twig\Extension\CoreExtension::constant("CronTask::STATE_RUNNING")) && ((($_v18 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 140)) && is_array($_v18) || $_v18 instanceof ArrayAccess ? ($_v18["allowmode"] ?? null) : null) & Twig\Extension\CoreExtension::constant("CronTask::MODE_INTERNAL")));
        // line 141
        yield "                        ";
        $context["next_run_field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 142
            yield "                           ";
            if (((($_v19 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 142)) && is_array($_v19) || $_v19 instanceof ArrayAccess ? ($_v19["state"] ?? null) : null) != Twig\Extension\CoreExtension::constant("CronTask::STATE_WAITING"))) {
                // line 143
                yield "                              ";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "getStateName", [(($_v20 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 143)) && is_array($_v20) || $_v20 instanceof ArrayAccess ? ($_v20["state"] ?? null) : null)], "method", false, false, false, 143), "html", null, true);
                yield "
                           ";
            } else {
                // line 145
                yield "                              ";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["item_meta"] ?? null), "next_run_display", [], "any", false, false, false, 145), "html", null, true);
                yield "
                           ";
            }
            // line 147
            yield "                           ";
            if ((($tmp = $this->extensions['Glpi\Application\View\Extension\ConfigExtension']->config("maintenance_mode")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 148
                yield "                              <div class=\"alert alert-warning\">
                                 ";
                // line 149
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Maintenance mode enabled, running tasks is disabled"), "html", null, true);
                yield "
                              </div>
                           ";
            } elseif ((            // line 151
($context["can_execute"] ?? null) && ($context["launch"] ?? null))) {
                // line 152
                yield "                              ";
                // line 153
                yield "                              <button class=\"btn btn-primary\" type=\"submit\" name=\"execute\" value=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v21 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 153)) && is_array($_v21) || $_v21 instanceof ArrayAccess ? ($_v21["name"] ?? null) : null), "html", null, true);
                yield "\">
                                 <i class=\"ti ti-player-play me-1\"></i>
                                 ";
                // line 155
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Execute"), "html", null, true);
                yield "
                              </button>
                           ";
            }
            // line 158
            yield "
                           ";
            // line 159
            if ((($context["can_execute"] ?? null) && ((($_v22 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 159)) && is_array($_v22) || $_v22 instanceof ArrayAccess ? ($_v22["state"] ?? null) : null) == Twig\Extension\CoreExtension::constant("CronTask::STATE_RUNNING")))) {
                // line 160
                yield "                              ";
                // line 161
                yield "                              <button class=\"btn btn-primary\" type=\"submit\" name=\"resetstate\">
                                 <i class=\"ti ti-circle-x me-1\"></i>
                                 ";
                // line 163
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Blank"), "html", null, true);
                yield "
                              </button>
                           ";
            }
            // line 166
            yield "                        ";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
        // line 167
        yield "                        ";
        yield $macros["fields"]->getTemplateForMacro("macro_htmlField", $context, 167, $this->getSourceContext())->macro_htmlField(...["next_run", ($context["next_run_field"] ?? null), __("Next run")]);
        yield "
                     </div>
                  ";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "pages/setup/crontask/crontask.html.twig";
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
        return array (  332 => 167,  328 => 166,  322 => 163,  318 => 161,  316 => 160,  314 => 159,  311 => 158,  305 => 155,  299 => 153,  297 => 152,  295 => 151,  290 => 149,  287 => 148,  284 => 147,  278 => 145,  272 => 143,  269 => 142,  266 => 141,  263 => 140,  257 => 137,  253 => 136,  250 => 135,  242 => 131,  240 => 130,  238 => 129,  233 => 128,  227 => 126,  224 => 125,  221 => 124,  217 => 121,  213 => 119,  210 => 116,  207 => 115,  203 => 112,  200 => 105,  194 => 102,  188 => 99,  186 => 94,  182 => 92,  180 => 87,  177 => 86,  174 => 85,  170 => 82,  167 => 79,  163 => 76,  160 => 73,  155 => 70,  150 => 68,  146 => 66,  144 => 65,  143 => 63,  142 => 61,  136 => 59,  129 => 57,  126 => 56,  123 => 55,  120 => 54,  117 => 53,  114 => 52,  107 => 51,  99 => 176,  96 => 175,  93 => 173,  91 => 172,  89 => 171,  87 => 170,  85 => 51,  77 => 45,  74 => 44,  71 => 43,  68 => 42,  65 => 41,  62 => 40,  59 => 39,  57 => 38,  52 => 36,  48 => 34,  46 => 33,  43 => 32,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "pages/setup/crontask/crontask.html.twig", "/var/www/html/glpi/templates/pages/setup/crontask/crontask.html.twig");
    }
}
