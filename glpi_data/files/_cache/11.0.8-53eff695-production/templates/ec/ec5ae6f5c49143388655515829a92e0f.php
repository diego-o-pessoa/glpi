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

/* pages/tools/project.html.twig */
class __TwigTemplate_9f07f9d158e05c0d386920c24ab920f4 extends Template
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

        $this->blocks = [
            'form_fields' => [$this, 'block_form_fields'],
        ];
    }

    protected function doGetParent(array $context): bool|string|Template|TemplateWrapper
    {
        // line 33
        return "generic_show_form.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 34
        $macros["fields"] = $this->macros["fields"] = $this->load("components/form/fields_macros.html.twig", 34)->unwrap();
        // line 36
        $context["no_header"] = ((array_key_exists("no_header", $context)) ? (Twig\Extension\CoreExtension::default(($context["no_header"] ?? null), ( !CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "isNewItem", [], "method", false, false, false, 36) &&  !((CoreExtension::getAttribute($this->env, $this->source, ($context["_get"] ?? null), "_in_modal", [], "any", true, true, false, 36)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["_get"] ?? null), "_in_modal", [], "any", false, false, false, 36), false)) : (false))))) : (( !CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "isNewItem", [], "method", false, false, false, 36) &&  !((CoreExtension::getAttribute($this->env, $this->source, ($context["_get"] ?? null), "_in_modal", [], "any", true, true, false, 36)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["_get"] ?? null), "_in_modal", [], "any", false, false, false, 36), false)) : (false)))));
        // line 37
        $context["withtemplate"] = (((CoreExtension::getAttribute($this->env, $this->source, ($context["params"] ?? null), "withtemplate", [], "array", true, true, false, 37) &&  !(null === (($_v0 = ($context["params"] ?? null)) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0["withtemplate"] ?? null) : null)))) ? ((($_v1 = ($context["params"] ?? null)) && is_array($_v1) || $_v1 instanceof ArrayAccess ? ($_v1["withtemplate"] ?? null) : null)) : (""));
        // line 38
        $context["is_template"] = (($context["withtemplate"] ?? null) == 1);
        // line 39
        $context["from_template"] = (($context["withtemplate"] ?? null) == 2);
        // line 40
        $context["rand"] = Twig\Extension\CoreExtension::random($this->env->getCharset());
        // line 41
        $context["field_options"] = ["rand" =>         // line 42
($context["rand"] ?? null)];
        // line 33
        $this->parent = $this->load("generic_show_form.html.twig", 33);
        yield from $this->parent->unwrap()->yield($context, array_merge($this->blocks, $blocks));
    }

    // line 45
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_form_fields(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 46
        yield "    ";
        $context["date"] = (((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "isNewItem", [], "method", false, false, false, 46)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ($this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpi_currenttime")) : ((($_v2 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 46)) && is_array($_v2) || $_v2 instanceof ArrayAccess ? ($_v2["date"] ?? null) : null)));
        // line 47
        yield "    ";
        yield $macros["fields"]->getTemplateForMacro("macro_datetimeField", $context, 47, $this->getSourceContext())->macro_datetimeField(...["date", ($context["date"] ?? null), __("Creation date"), ($context["field_options"] ?? null)]);
        yield "
    ";
        // line 48
        if (( !CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "isNewItem", [], "method", false, false, false, 48) &&  !($context["is_template"] ?? null))) {
            // line 49
            yield "        ";
            yield $macros["fields"]->getTemplateForMacro("macro_htmlField", $context, 49, $this->getSourceContext())->macro_htmlField(...["date_mod", $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getFormattedDatetime((($_v3 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 49)) && is_array($_v3) || $_v3 instanceof ArrayAccess ? ($_v3["date_mod"] ?? null) : null))), __("Last update")]);
            yield "
    ";
        } elseif (((        // line 50
($context["is_template"] ?? null) &&  !CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "isNewItem", [], "method", false, false, false, 50)) && ($context["no_header"] ?? null))) {
            // line 51
            yield "        ";
            yield $macros["fields"]->getTemplateForMacro("macro_autoNameField", $context, 51, $this->getSourceContext())->macro_autoNameField(...["template_name", ($context["item"] ?? null), __("Template name"), 0, ($context["field_options"] ?? null)]);
            yield "
        ";
            // line 52
            yield $macros["fields"]->getTemplateForMacro("macro_nullField", $context, 52, $this->getSourceContext())->macro_nullField(...[]);
            yield "
    ";
        } elseif ((($tmp = CoreExtension::getAttribute($this->env, $this->source,         // line 53
($context["item"] ?? null), "isNewItem", [], "method", false, false, false, 53)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 54
            yield "        ";
            yield $macros["fields"]->getTemplateForMacro("macro_nullField", $context, 54, $this->getSourceContext())->macro_nullField(...[]);
            yield "
    ";
        }
        // line 56
        yield "    ";
        yield $macros["fields"]->getTemplateForMacro("macro_autoNameField", $context, 56, $this->getSourceContext())->macro_autoNameField(...["name", ($context["item"] ?? null), __("Name"), ($context["withtemplate"] ?? null), ($context["field_options"] ?? null)]);
        yield "
    ";
        // line 57
        yield $macros["fields"]->getTemplateForMacro("macro_textField", $context, 57, $this->getSourceContext())->macro_textField(...["code", (($_v4 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 57)) && is_array($_v4) || $_v4 instanceof ArrayAccess ? ($_v4["code"] ?? null) : null), __("Code"), ($context["field_options"] ?? null)]);
        yield "

    ";
        // line 59
        $context["priority_field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 60
            yield "        ";
            $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("CommonITILObject::dropdownPriority", [["value" => (($_v5 = CoreExtension::getAttribute($this->env, $this->source,             // line 61
($context["item"] ?? null), "fields", [], "any", false, false, false, 61)) && is_array($_v5) || $_v5 instanceof ArrayAccess ? ($_v5["priority"] ?? null) : null), "name" => "priority", "withmajor" => true]]);
            // line 65
            yield "    ";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
        // line 66
        yield "    ";
        yield $macros["fields"]->getTemplateForMacro("macro_htmlField", $context, 66, $this->getSourceContext())->macro_htmlField(...["", ($context["priority_field"] ?? null), __("Priority"), ($context["field_options"] ?? null)]);
        yield "

    ";
        // line 68
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownField", $context, 68, $this->getSourceContext())->macro_dropdownField(...[        // line 69
($context["item_type"] ?? null), "projects_id", (($_v6 = CoreExtension::getAttribute($this->env, $this->source,         // line 71
($context["item"] ?? null), "fields", [], "any", false, false, false, 71)) && is_array($_v6) || $_v6 instanceof ArrayAccess ? ($_v6["projects_id"] ?? null) : null), __("As child of"), Twig\Extension\CoreExtension::merge(        // line 73
($context["field_options"] ?? null), ["entity" => (($_v7 = CoreExtension::getAttribute($this->env, $this->source,         // line 74
($context["item"] ?? null), "fields", [], "any", false, false, false, 74)) && is_array($_v7) || $_v7 instanceof ArrayAccess ? ($_v7["entities_id"] ?? null) : null), "used" => [CoreExtension::getAttribute($this->env, $this->source,         // line 75
($context["item"] ?? null), "getID", [], "method", false, false, false, 75)]])]);
        // line 77
        yield "

    ";
        // line 79
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownField", $context, 79, $this->getSourceContext())->macro_dropdownField(...["ProjectState", "projectstates_id", (($_v8 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 79)) && is_array($_v8) || $_v8 instanceof ArrayAccess ? ($_v8["projectstates_id"] ?? null) : null), _x("item", "State"), ($context["field_options"] ?? null)]);
        yield "

    ";
        // line 81
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownNumberField", $context, 81, $this->getSourceContext())->macro_dropdownNumberField(...["percent_done", (($_v9 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 81)) && is_array($_v9) || $_v9 instanceof ArrayAccess ? ($_v9["percent_done"] ?? null) : null), __("Percent done"), Twig\Extension\CoreExtension::merge(($context["field_options"] ?? null), ["min" => 0, "max" => 100, "step" => 5, "unit" => "%", "add_field_html" => $macros["fields"]->getTemplateForMacro("macro_sliderField", $context, 86, $this->getSourceContext())->macro_sliderField(...["auto_percent_done", (($_v10 = CoreExtension::getAttribute($this->env, $this->source,         // line 86
($context["item"] ?? null), "fields", [], "any", false, false, false, 86)) && is_array($_v10) || $_v10 instanceof ArrayAccess ? ($_v10["auto_percent_done"] ?? null) : null), __("Automatically calculate"), Twig\Extension\CoreExtension::merge(($context["field_options"] ?? null), ["field_class" => "col-12", "label_class" => "col-xxl-10", "input_class" => "col-xxl-2", "additional_attributes" => ["onclick" => "\$(`select[name=\x27percent_done\x27]`).prop(\x27disabled\x27, \$(`input[type=\x27checkbox\x27][name=\x27auto_percent_done\x27]`).prop(\x27checked\x27));"]])]), "helper" => __("When automatic computation is active, percentage is computed based on the average of all child project and task percent done."), "disabled" => ((($_v11 = CoreExtension::getAttribute($this->env, $this->source,         // line 95
($context["item"] ?? null), "fields", [], "any", false, false, false, 95)) && is_array($_v11) || $_v11 instanceof ArrayAccess ? ($_v11["auto_percent_done"] ?? null) : null) == 1)])]);
        // line 96
        yield "

    ";
        // line 98
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownField", $context, 98, $this->getSourceContext())->macro_dropdownField(...["ProjectType", "projecttypes_id", (($_v12 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 98)) && is_array($_v12) || $_v12 instanceof ArrayAccess ? ($_v12["projecttypes_id"] ?? null) : null), _n("Type", "Types", 1), ($context["field_options"] ?? null)]);
        yield "

    ";
        // line 100
        if ((($tmp = ($context["gantt_plugin_enabled"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 101
            yield "        ";
            yield $macros["fields"]->getTemplateForMacro("macro_dropdownYesNo", $context, 101, $this->getSourceContext())->macro_dropdownYesNo(...["show_on_global_gantt", (($_v13 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 101)) && is_array($_v13) || $_v13 instanceof ArrayAccess ? ($_v13["show_on_global_gantt"] ?? null) : null), __("Show on global Gantt"), ($context["field_options"] ?? null)]);
            yield "
    ";
        } else {
            // line 103
            yield "        ";
            yield $macros["fields"]->getTemplateForMacro("macro_nullField", $context, 103, $this->getSourceContext())->macro_nullField(...[]);
            yield "
    ";
        }
        // line 105
        yield "
    ";
        // line 106
        yield $macros["fields"]->getTemplateForMacro("macro_smallTitle", $context, 106, $this->getSourceContext())->macro_smallTitle(...[_n("Manager", "Managers", 1)]);
        yield "
    ";
        // line 107
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownField", $context, 107, $this->getSourceContext())->macro_dropdownField(...["User", "users_id", (($_v14 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 107)) && is_array($_v14) || $_v14 instanceof ArrayAccess ? ($_v14["users_id"] ?? null) : null), $this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("User", 1), Twig\Extension\CoreExtension::merge(($context["field_options"] ?? null), ["right" => "see_project", "entity" => (($_v15 = CoreExtension::getAttribute($this->env, $this->source,         // line 109
($context["item"] ?? null), "fields", [], "any", false, false, false, 109)) && is_array($_v15) || $_v15 instanceof ArrayAccess ? ($_v15["entities_id"] ?? null) : null)])]);
        // line 110
        yield "
    ";
        // line 111
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownField", $context, 111, $this->getSourceContext())->macro_dropdownField(...["Group", "groups_id", (($_v16 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 111)) && is_array($_v16) || $_v16 instanceof ArrayAccess ? ($_v16["groups_id"] ?? null) : null), $this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("Group", 1), Twig\Extension\CoreExtension::merge(($context["field_options"] ?? null), ["condition" => ["is_manager" => 1], "entity" => (($_v17 = CoreExtension::getAttribute($this->env, $this->source,         // line 115
($context["item"] ?? null), "fields", [], "any", false, false, false, 115)) && is_array($_v17) || $_v17 instanceof ArrayAccess ? ($_v17["entities_id"] ?? null) : null)])]);
        // line 116
        yield "

    ";
        // line 118
        yield $macros["fields"]->getTemplateForMacro("macro_smallTitle", $context, 118, $this->getSourceContext())->macro_smallTitle(...[__("Planning")]);
        yield "
    ";
        // line 119
        yield $macros["fields"]->getTemplateForMacro("macro_datetimeField", $context, 119, $this->getSourceContext())->macro_datetimeField(...["plan_start_date", (($_v18 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 119)) && is_array($_v18) || $_v18 instanceof ArrayAccess ? ($_v18["plan_start_date"] ?? null) : null), __("Planned start date"), Twig\Extension\CoreExtension::merge(($context["field_options"] ?? null), ["clearable" => true])]);
        yield "
    ";
        // line 120
        yield $macros["fields"]->getTemplateForMacro("macro_datetimeField", $context, 120, $this->getSourceContext())->macro_datetimeField(...["real_start_date", (($_v19 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 120)) && is_array($_v19) || $_v19 instanceof ArrayAccess ? ($_v19["real_start_date"] ?? null) : null), __("Real start date"), Twig\Extension\CoreExtension::merge(($context["field_options"] ?? null), ["clearable" => true])]);
        yield "
    ";
        // line 121
        yield $macros["fields"]->getTemplateForMacro("macro_datetimeField", $context, 121, $this->getSourceContext())->macro_datetimeField(...["plan_end_date", (($_v20 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 121)) && is_array($_v20) || $_v20 instanceof ArrayAccess ? ($_v20["plan_end_date"] ?? null) : null), __("Planned end date"), Twig\Extension\CoreExtension::merge(($context["field_options"] ?? null), ["clearable" => true])]);
        yield "
    ";
        // line 122
        yield $macros["fields"]->getTemplateForMacro("macro_datetimeField", $context, 122, $this->getSourceContext())->macro_datetimeField(...["real_end_date", (($_v21 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 122)) && is_array($_v21) || $_v21 instanceof ArrayAccess ? ($_v21["real_end_date"] ?? null) : null), __("Real end date"), Twig\Extension\CoreExtension::merge(($context["field_options"] ?? null), ["clearable" => true])]);
        yield "
    ";
        // line 123
        yield $macros["fields"]->getTemplateForMacro("macro_htmlField", $context, 123, $this->getSourceContext())->macro_htmlField(...["planned_duration", $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getFormattedDuration(($context["planned_duration"] ?? null), false)), __("Planned duration"), Twig\Extension\CoreExtension::merge(($context["field_options"] ?? null), ["helper" => __("Sum of planned durations of tasks")])]);
        // line 125
        yield "
    ";
        // line 126
        yield $macros["fields"]->getTemplateForMacro("macro_htmlField", $context, 126, $this->getSourceContext())->macro_htmlField(...["effective_duration", $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getFormattedDuration(($context["effective_duration"] ?? null), false)), __("Effective duration"), Twig\Extension\CoreExtension::merge(($context["field_options"] ?? null), ["helper" => __("Sum of total effective durations of tasks")])]);
        // line 128
        yield "

    ";
        // line 130
        yield $macros["fields"]->getTemplateForMacro("macro_textareaField", $context, 130, $this->getSourceContext())->macro_textareaField(...["content", (($_v22 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 130)) && is_array($_v22) || $_v22 instanceof ArrayAccess ? ($_v22["content"] ?? null) : null), __("Description"), Twig\Extension\CoreExtension::merge(($context["field_options"] ?? null), ["full_width" => true, "enable_richtext" => true, "rows" => 6])]);
        // line 134
        yield "
    ";
        // line 135
        yield $macros["fields"]->getTemplateForMacro("macro_textareaField", $context, 135, $this->getSourceContext())->macro_textareaField(...["comment", (($_v23 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 135)) && is_array($_v23) || $_v23 instanceof ArrayAccess ? ($_v23["comment"] ?? null) : null), __("Comments"), Twig\Extension\CoreExtension::merge(($context["field_options"] ?? null), ["full_width" => true, "rows" => 6])]);
        // line 138
        yield "
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "pages/tools/project.html.twig";
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
        return array (  235 => 138,  233 => 135,  230 => 134,  228 => 130,  224 => 128,  222 => 126,  219 => 125,  217 => 123,  213 => 122,  209 => 121,  205 => 120,  201 => 119,  197 => 118,  193 => 116,  191 => 115,  190 => 111,  187 => 110,  185 => 109,  184 => 107,  180 => 106,  177 => 105,  171 => 103,  165 => 101,  163 => 100,  158 => 98,  154 => 96,  152 => 95,  151 => 86,  150 => 81,  145 => 79,  141 => 77,  139 => 75,  138 => 74,  137 => 73,  136 => 71,  135 => 69,  134 => 68,  128 => 66,  124 => 65,  122 => 61,  120 => 60,  118 => 59,  113 => 57,  108 => 56,  102 => 54,  100 => 53,  96 => 52,  91 => 51,  89 => 50,  84 => 49,  82 => 48,  77 => 47,  74 => 46,  67 => 45,  62 => 33,  60 => 42,  59 => 41,  57 => 40,  55 => 39,  53 => 38,  51 => 37,  49 => 36,  47 => 34,  40 => 33,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "pages/tools/project.html.twig", "/var/www/html/glpi/templates/pages/tools/project.html.twig");
    }
}
