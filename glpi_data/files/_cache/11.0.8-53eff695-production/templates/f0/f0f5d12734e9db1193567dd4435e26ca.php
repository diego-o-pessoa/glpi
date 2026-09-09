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

/* pages/admin/entity/assistance.html.twig */
class __TwigTemplate_6337783ec9b7115c0d149a855a342ea3 extends Template
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
        // line 33
        $this->parent = $this->load("generic_show_form.html.twig", 33);
        yield from $this->parent->unwrap()->yield($context, array_merge($this->blocks, $blocks));
    }

    // line 36
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_form_fields(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 37
        yield "    ";
        $context["inheritable_params"] = ["full_width" => true, "entity" => CoreExtension::getAttribute($this->env, $this->source,         // line 39
($context["item"] ?? null), "getID", [], "method", false, false, false, 39), "toadd" => (((CoreExtension::getAttribute($this->env, $this->source,         // line 40
($context["item"] ?? null), "getID", [], "method", false, false, false, 40) > 0)) ? ([ (string)Twig\Extension\CoreExtension::constant("Entity::CONFIG_PARENT") => __("Inheritance of the parent entity")]) : ([]))];
        // line 44
        yield "
    ";
        // line 45
        yield $macros["fields"]->getTemplateForMacro("macro_smallTitle", $context, 45, $this->getSourceContext())->macro_smallTitle(...[__("Templates configuration")]);
        yield "
    ";
        // line 46
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownField", $context, 46, $this->getSourceContext())->macro_dropdownField(...["TicketTemplate", "tickettemplates_id", (($_v0 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 46)) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0["tickettemplates_id"] ?? null) : null), $this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("TicketTemplate", 1), (["add_field_html" => ((CoreExtension::getAttribute($this->env, $this->source,         // line 47
($context["inheritance_labels"] ?? null), "tickettemplates_id", [], "array", true, true, false, 47)) ? (Twig\Extension\CoreExtension::default((($_v1 = ($context["inheritance_labels"] ?? null)) && is_array($_v1) || $_v1 instanceof ArrayAccess ? ($_v1["tickettemplates_id"] ?? null) : null), null)) : (null))] +         // line 48
($context["inheritable_params"] ?? null))]);
        yield "
    ";
        // line 49
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownField", $context, 49, $this->getSourceContext())->macro_dropdownField(...["ChangeTemplate", "changetemplates_id", (($_v2 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 49)) && is_array($_v2) || $_v2 instanceof ArrayAccess ? ($_v2["changetemplates_id"] ?? null) : null), $this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("ChangeTemplate", 1), (["add_field_html" => ((CoreExtension::getAttribute($this->env, $this->source,         // line 50
($context["inheritance_labels"] ?? null), "changetemplates_id", [], "array", true, true, false, 50)) ? (Twig\Extension\CoreExtension::default((($_v3 = ($context["inheritance_labels"] ?? null)) && is_array($_v3) || $_v3 instanceof ArrayAccess ? ($_v3["changetemplates_id"] ?? null) : null), null)) : (null))] +         // line 51
($context["inheritable_params"] ?? null))]);
        yield "
    ";
        // line 52
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownField", $context, 52, $this->getSourceContext())->macro_dropdownField(...["ProblemTemplate", "problemtemplates_id", (($_v4 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 52)) && is_array($_v4) || $_v4 instanceof ArrayAccess ? ($_v4["problemtemplates_id"] ?? null) : null), $this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("ProblemTemplate", 1), (["add_field_html" => ((CoreExtension::getAttribute($this->env, $this->source,         // line 53
($context["inheritance_labels"] ?? null), "problemtemplates_id", [], "array", true, true, false, 53)) ? (Twig\Extension\CoreExtension::default((($_v5 = ($context["inheritance_labels"] ?? null)) && is_array($_v5) || $_v5 instanceof ArrayAccess ? ($_v5["problemtemplates_id"] ?? null) : null), null)) : (null))] +         // line 54
($context["inheritable_params"] ?? null))]);
        yield "

    ";
        // line 56
        yield $macros["fields"]->getTemplateForMacro("macro_smallTitle", $context, 56, $this->getSourceContext())->macro_smallTitle(...[__("Tickets configuration")]);
        yield "
    ";
        // line 57
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownField", $context, 57, $this->getSourceContext())->macro_dropdownField(...["Calendar", "calendars_id", (($_v6 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 57)) && is_array($_v6) || $_v6 instanceof ArrayAccess ? ($_v6["calendars_id"] ?? null) : null), $this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("Calendar", 1), (["emptylabel" => __("24/7"), "add_field_html" => ((CoreExtension::getAttribute($this->env, $this->source,         // line 59
($context["inheritance_labels"] ?? null), "calendars_id", [], "array", true, true, false, 59)) ? (Twig\Extension\CoreExtension::default((($_v7 = ($context["inheritance_labels"] ?? null)) && is_array($_v7) || $_v7 instanceof ArrayAccess ? ($_v7["calendars_id"] ?? null) : null), null)) : (null))] +         // line 60
($context["inheritable_params"] ?? null))]);
        yield "
    ";
        // line 61
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownArrayField", $context, 61, $this->getSourceContext())->macro_dropdownArrayField(...["tickettype", (($_v8 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 61)) && is_array($_v8) || $_v8 instanceof ArrayAccess ? ($_v8["tickettype"] ?? null) : null), (CoreExtension::getAttribute($this->env, $this->source, ($context["inheritable_params"] ?? null), "toadd", [], "any", false, false, false, 61) + [ (string)Twig\Extension\CoreExtension::constant("Ticket::INCIDENT_TYPE") => __("Incident"),  (string)Twig\Extension\CoreExtension::constant("Ticket::DEMAND_TYPE") => __("Request")]), __("Tickets default type"), (["add_field_html" => ((CoreExtension::getAttribute($this->env, $this->source,         // line 65
($context["inheritance_labels"] ?? null), "tickettype", [], "array", true, true, false, 65)) ? (Twig\Extension\CoreExtension::default((($_v9 = ($context["inheritance_labels"] ?? null)) && is_array($_v9) || $_v9 instanceof ArrayAccess ? ($_v9["tickettype"] ?? null) : null), null)) : (null))] +         // line 66
($context["inheritable_params"] ?? null))]);
        yield "
    ";
        // line 67
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownArrayField", $context, 67, $this->getSourceContext())->macro_dropdownArrayField(...["auto_assign_mode", (($_v10 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 67)) && is_array($_v10) || $_v10 instanceof ArrayAccess ? ($_v10["auto_assign_mode"] ?? null) : null), (CoreExtension::getAttribute($this->env, $this->source, ($context["inheritable_params"] ?? null), "toadd", [], "any", false, false, false, 67) + [ (string)Twig\Extension\CoreExtension::constant("Entity::CONFIG_NEVER") => __("No"),  (string)Twig\Extension\CoreExtension::constant("Entity::AUTO_ASSIGN_HARDWARE_CATEGORY") => __("Based on the item then the category"),  (string)Twig\Extension\CoreExtension::constant("Entity::AUTO_ASSIGN_CATEGORY_HARDWARE") => __("Based on the category then the item")]), __("Automatic assignment of tickets, changes and problems"), (["add_field_html" => ((CoreExtension::getAttribute($this->env, $this->source,         // line 72
($context["inheritance_labels"] ?? null), "auto_assign_mode", [], "array", true, true, false, 72)) ? (Twig\Extension\CoreExtension::default((($_v11 = ($context["inheritance_labels"] ?? null)) && is_array($_v11) || $_v11 instanceof ArrayAccess ? ($_v11["auto_assign_mode"] ?? null) : null), null)) : (null))] +         // line 73
($context["inheritable_params"] ?? null))]);
        yield "
    ";
        // line 74
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownArrayField", $context, 74, $this->getSourceContext())->macro_dropdownArrayField(...["suppliers_as_private", (($_v12 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 74)) && is_array($_v12) || $_v12 instanceof ArrayAccess ? ($_v12["suppliers_as_private"] ?? null) : null), (CoreExtension::getAttribute($this->env, $this->source, ($context["inheritable_params"] ?? null), "toadd", [], "any", false, false, false, 74) + [__("No"), __("Yes")]), __("Mark followup added by a supplier though an email collector as private"), (["add_field_html" => ((CoreExtension::getAttribute($this->env, $this->source,         // line 78
($context["inheritance_labels"] ?? null), "suppliers_as_private", [], "array", true, true, false, 78)) ? (Twig\Extension\CoreExtension::default((($_v13 = ($context["inheritance_labels"] ?? null)) && is_array($_v13) || $_v13 instanceof ArrayAccess ? ($_v13["suppliers_as_private"] ?? null) : null), null)) : (null))] +         // line 79
($context["inheritable_params"] ?? null))]);
        yield "
    ";
        // line 80
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownArrayField", $context, 80, $this->getSourceContext())->macro_dropdownArrayField(...["anonymize_support_agents", (($_v14 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 80)) && is_array($_v14) || $_v14 instanceof ArrayAccess ? ($_v14["anonymize_support_agents"] ?? null) : null), (CoreExtension::getAttribute($this->env, $this->source, ($context["inheritable_params"] ?? null), "toadd", [], "any", false, false, false, 80) + [ (string)Twig\Extension\CoreExtension::constant("Entity::ANONYMIZE_DISABLED") => __("Disabled"),  (string)Twig\Extension\CoreExtension::constant("Entity::ANONYMIZE_USE_GENERIC") => __("Replace the agent and group name with a generic name"),  (string)Twig\Extension\CoreExtension::constant("Entity::ANONYMIZE_USE_NICKNAME") => __("Replace the agent and group name with a customisable nickname"),  (string)Twig\Extension\CoreExtension::constant("Entity::ANONYMIZE_USE_GENERIC_USER") => __("Replace the agent\x27s name with a generic name"),  (string)Twig\Extension\CoreExtension::constant("Entity::ANONYMIZE_USE_NICKNAME_USER") => __("Replace the agent\x27s name with a customisable nickname"),  (string)Twig\Extension\CoreExtension::constant("Entity::ANONYMIZE_USE_GENERIC_GROUP") => __("Replace the group\x27s name with a generic name")]), __("Anonymize support agents"), (["add_field_html" => ((CoreExtension::getAttribute($this->env, $this->source,         // line 88
($context["inheritance_labels"] ?? null), "anonymize_support_agents", [], "array", true, true, false, 88)) ? (Twig\Extension\CoreExtension::default((($_v15 = ($context["inheritance_labels"] ?? null)) && is_array($_v15) || $_v15 instanceof ArrayAccess ? ($_v15["anonymize_support_agents"] ?? null) : null), null)) : (null))] +         // line 89
($context["inheritable_params"] ?? null))]);
        yield "
    ";
        // line 90
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownArrayField", $context, 90, $this->getSourceContext())->macro_dropdownArrayField(...["display_users_initials", (($_v16 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 90)) && is_array($_v16) || $_v16 instanceof ArrayAccess ? ($_v16["display_users_initials"] ?? null) : null), (CoreExtension::getAttribute($this->env, $this->source, ($context["inheritable_params"] ?? null), "toadd", [], "any", false, false, false, 90) + [__("No"), __("Yes")]), __("Display initials for users without pictures"), (["add_field_html" => ((CoreExtension::getAttribute($this->env, $this->source,         // line 94
($context["inheritance_labels"] ?? null), "display_users_initials", [], "array", true, true, false, 94)) ? (Twig\Extension\CoreExtension::default((($_v17 = ($context["inheritance_labels"] ?? null)) && is_array($_v17) || $_v17 instanceof ArrayAccess ? ($_v17["display_users_initials"] ?? null) : null), null)) : (null))] +         // line 95
($context["inheritable_params"] ?? null))]);
        yield "
    ";
        // line 96
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownField", $context, 96, $this->getSourceContext())->macro_dropdownField(...["Contract", "contracts_id_default", (($_v18 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 96)) && is_array($_v18) || $_v18 instanceof ArrayAccess ? ($_v18["contracts_id_default"] ?? null) : null), __("Default contract"), ["full_width" => true, "entity" => CoreExtension::getAttribute($this->env, $this->source,         // line 98
($context["item"] ?? null), "getID", [], "method", false, false, false, 98), "toadd" => (CoreExtension::getAttribute($this->env, $this->source,         // line 99
($context["inheritable_params"] ?? null), "toadd", [], "any", false, false, false, 99) + [ (string)Twig\Extension\CoreExtension::constant("Entity::CONFIG_AUTO") => __("Contract in ticket entity")]), "condition" => Twig\Extension\CoreExtension::merge(["is_template" => 0, "is_deleted" => 0], $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Contract::getNotExpiredCriteria")), "add_field_html" => ((CoreExtension::getAttribute($this->env, $this->source,         // line 106
($context["inheritance_labels"] ?? null), "contracts_id_default", [], "array", true, true, false, 106)) ? (Twig\Extension\CoreExtension::default((($_v19 = ($context["inheritance_labels"] ?? null)) && is_array($_v19) || $_v19 instanceof ArrayAccess ? ($_v19["contracts_id_default"] ?? null) : null), null)) : (null))]]);
        // line 107
        yield "

    ";
        // line 109
        yield $macros["fields"]->getTemplateForMacro("macro_smallTitle", $context, 109, $this->getSourceContext())->macro_smallTitle(...[__("Automatic closing configuration")]);
        yield "
    ";
        // line 110
        if ((($context["closeticket_disabled"] ?? null) || ($context["purgeticket_disabled"] ?? null))) {
            // line 111
            yield "        <div class=\"alert alert-info\">
            <i class=\"ti ti-info-circle\"></i>
            <div>
                ";
            // line 114
            yield (((($tmp = ($context["closeticket_disabled"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Close ticket action is disabled."), "html", null, true)) : (""));
            yield "
                ";
            // line 115
            yield (((($tmp = ($context["purgeticket_disabled"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Purge ticket action is disabled."), "html", null, true)) : (""));
            yield "
            </div>
        </div>
    ";
        }
        // line 119
        yield "    ";
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownNumberField", $context, 119, $this->getSourceContext())->macro_dropdownNumberField(...["autoclose_delay", (($_v20 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 119)) && is_array($_v20) || $_v20 instanceof ArrayAccess ? ($_v20["autoclose_delay"] ?? null) : null), __("Automatic closing of solved tickets after"), ["full_width" => true, "min" => 1, "max" => 99, "unit" => "day", "toadd" => (CoreExtension::getAttribute($this->env, $this->source,         // line 124
($context["inheritable_params"] ?? null), "toadd", [], "any", false, false, false, 124) + [ (string)Twig\Extension\CoreExtension::constant("Entity::CONFIG_NEVER") => __("Never"), 0 => __("Immediately")]), "add_field_html" => ((CoreExtension::getAttribute($this->env, $this->source,         // line 128
($context["inheritance_labels"] ?? null), "autoclose_delay", [], "array", true, true, false, 128)) ? (Twig\Extension\CoreExtension::default((($_v21 = ($context["inheritance_labels"] ?? null)) && is_array($_v21) || $_v21 instanceof ArrayAccess ? ($_v21["autoclose_delay"] ?? null) : null), null)) : (null))]]);
        // line 129
        yield "
    ";
        // line 130
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownNumberField", $context, 130, $this->getSourceContext())->macro_dropdownNumberField(...["autopurge_delay", (($_v22 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 130)) && is_array($_v22) || $_v22 instanceof ArrayAccess ? ($_v22["autopurge_delay"] ?? null) : null), __("Automatic purge of closed tickets after"), ["full_width" => true, "min" => 1, "max" => 3650, "unit" => "day", "toadd" => (CoreExtension::getAttribute($this->env, $this->source,         // line 135
($context["inheritable_params"] ?? null), "toadd", [], "any", false, false, false, 135) + [ (string)Twig\Extension\CoreExtension::constant("Entity::CONFIG_NEVER") => __("Never")]), "add_field_html" => ((CoreExtension::getAttribute($this->env, $this->source,         // line 138
($context["inheritance_labels"] ?? null), "autopurge_delay", [], "array", true, true, false, 138)) ? (Twig\Extension\CoreExtension::default((($_v23 = ($context["inheritance_labels"] ?? null)) && is_array($_v23) || $_v23 instanceof ArrayAccess ? ($_v23["autopurge_delay"] ?? null) : null), null)) : (null))]]);
        // line 139
        yield "

    ";
        // line 141
        $context["inquest_types"] = ["Ticket" => "TicketSatisfaction", "Change" => "ChangeSatisfaction"];
        // line 145
        yield "    ";
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["inquest_types"] ?? null));
        foreach ($context['_seq'] as $context["itemtype"] => $context["inquest_itemtype"]) {
            // line 146
            yield "        ";
            $context["c_rand"] = Twig\Extension\CoreExtension::random($this->env->getCharset());
            // line 147
            yield "        ";
            $context["config_suffix"] = ((($context["itemtype"] == "Ticket")) ? ("") : (("_" . Twig\Extension\CoreExtension::lower($this->env->getCharset(), $context["itemtype"]))));
            // line 148
            yield "        <div role=\"group\" aria-labelledby=\"formsection";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["inquest_itemtype"], "html", null, true);
            yield "\">
            ";
            // line 149
            yield $macros["fields"]->getTemplateForMacro("macro_smallTitle", $context, 149, $this->getSourceContext())->macro_smallTitle(...[Twig\Extension\CoreExtension::sprintf(__("Configuring the satisfaction survey: %s"), $this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName($context["itemtype"], Session::getPluralNumber())), "", "", ("formsection" . $context["inquest_itemtype"])]);
            yield "
            ";
            // line 150
            yield $macros["fields"]->getTemplateForMacro("macro_dropdownArrayField", $context, 150, $this->getSourceContext())->macro_dropdownArrayField(...[("inquest_config" . ($context["config_suffix"] ?? null)), (($_v24 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 150)) && is_array($_v24) || $_v24 instanceof ArrayAccess ? ($_v24[(($_v25 = ("inquest_config" . ($context["config_suffix"] ?? null))) instanceof \Stringable ? (string) $_v25 : $_v25)] ?? null) : null), (CoreExtension::getAttribute($this->env, $this->source, ($context["inheritable_params"] ?? null), "toadd", [], "any", false, false, false, 150) + [ (string)Twig\Extension\CoreExtension::constant("CommonITILSatisfaction::TYPE_INTERNAL") => __("Internal survey"),  (string)Twig\Extension\CoreExtension::constant("CommonITILSatisfaction::TYPE_EXTERNAL") => __("External survey")]), __("Configuring the satisfaction survey"), (["add_field_html" => ((CoreExtension::getAttribute($this->env, $this->source,             // line 154
($context["inheritance_labels"] ?? null), ("inquest_config" . ($context["config_suffix"] ?? null)), [], "array", true, true, false, 154)) ? (Twig\Extension\CoreExtension::default((($_v26 = ($context["inheritance_labels"] ?? null)) && is_array($_v26) || $_v26 instanceof ArrayAccess ? ($_v26[(($_v27 = ("inquest_config" . ($context["config_suffix"] ?? null))) instanceof \Stringable ? (string) $_v27 : $_v27)] ?? null) : null), null)) : (null)), "rand" =>             // line 155
($context["c_rand"] ?? null)] +             // line 156
($context["inheritable_params"] ?? null))]);
            yield "
            <div id=\"inquest_config";
            // line 157
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["config_suffix"] ?? null), "html", null, true);
            yield "_extra\"></div>
            <script type=\"module\">
                const refresh_param_rows = () => {
                    const rate_dropdown = \$(\x27select[name=\"inquest_rate";
            // line 160
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["config_suffix"] ?? null), "html", null, true);
            yield "\"]\x27);
                    const config_dropdown = \$(\x27select[name=\"inquest_config";
            // line 161
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["config_suffix"] ?? null), "html", null, true);
            yield "\"]\x27);
                    const val = parseInt(\x27\x27 + rate_dropdown.val()) || 0;
                    const config_val = parseInt(\x27\x27 + config_dropdown.val());

                    const param_rows = [
                        \x27inquest_duration";
            // line 166
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["config_suffix"] ?? null), "html", null, true);
            yield "\x27,
                        \x27inquest_max_rate";
            // line 167
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["config_suffix"] ?? null), "html", null, true);
            yield "\x27,
                        \x27inquest_default_rate";
            // line 168
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["config_suffix"] ?? null), "html", null, true);
            yield "\x27,
                        \x27inquest_mandatory_comment";
            // line 169
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["config_suffix"] ?? null), "html", null, true);
            yield "\x27,
                        \x27max_closedate";
            // line 170
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["config_suffix"] ?? null), "html", null, true);
            yield "\x27,
                        \x27inquest_URL";
            // line 171
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["config_suffix"] ?? null), "html", null, true);
            yield "\x27
                    ];
                    if (val === 0) {
                        param_rows.forEach(row => {
                            \$(`[name=\"\${row}\"]`).closest(\x27.form-field\x27).hide();
                        });
                    } else {
                        param_rows.forEach(row => {
                            \$(`[name=\"\${row}\"]`).closest(\x27.form-field\x27).show();
                        });
                    }
                    if (val === 0 || config_val !== ";
            // line 182
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::constant("CommonITILSatisfaction::TYPE_EXTERNAL"), "html", null, true);
            yield ") {
                        \$(`#inquest_config";
            // line 183
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["config_suffix"] ?? null), "html", null, true);
            yield "_extra [name=\"inquest_URL";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["config_suffix"] ?? null), "html", null, true);
            yield "\"]`).closest(\x27.form-field\x27).hide();
                        \$(\x27#inquest_config";
            // line 184
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["config_suffix"] ?? null), "html", null, true);
            yield "_extra .form-field.valid_tags\x27).hide();
                    } else {
                        \$(`#inquest_config";
            // line 186
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["config_suffix"] ?? null), "html", null, true);
            yield "_extra [name=\"inquest_URL";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["config_suffix"] ?? null), "html", null, true);
            yield "\"]`).closest(\x27.form-field\x27).show();
                        \$(\x27#inquest_config";
            // line 187
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["config_suffix"] ?? null), "html", null, true);
            yield "_extra .form-field.valid_tags\x27).show();
                    }
                }

                const refresh = (val) => {
                    \$(\x27#inquest_config";
            // line 192
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["config_suffix"] ?? null), "html", null, true);
            yield "_extra\x27).load(\x27";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path("ajax/commonitilsatisfaction.php"), "html", null, true);
            yield "\x27, {
                        itemtype: \x27";
            // line 193
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["itemtype"], "html", null, true);
            yield "\x27,
                        entities_id: \x27";
            // line 194
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "getID", [], "method", false, false, false, 194), "html", null, true);
            yield "\x27,
                        inquest_config";
            // line 195
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["config_suffix"] ?? null), "html", null, true);
            yield ": val,
                    }, refresh_param_rows);
                }
                \$(\x27#dropdown_inquest_config";
            // line 198
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["config_suffix"] ?? null), "html", null, true);
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["c_rand"] ?? null), "html", null, true);
            yield "\x27).on(\x27change\x27, (e) => {
                    const selected = \$(e.target).val();
                    refresh(selected);
                });
                refresh(";
            // line 202
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v28 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 202)) && is_array($_v28) || $_v28 instanceof ArrayAccess ? ($_v28[(($_v29 = ("inquest_config" . ($context["config_suffix"] ?? null))) instanceof \Stringable ? (string) $_v29 : $_v29)] ?? null) : null), "html", null, true);
            yield ");


                \$(\x27select[name=\"inquest_config";
            // line 205
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["config_suffix"] ?? null), "html", null, true);
            yield "\"]\x27).on(\x27change\x27, refresh_param_rows);
                \$(\x27#inquest_config";
            // line 206
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["config_suffix"] ?? null), "html", null, true);
            yield "_extra\x27)
                    .on(\x27change\x27, \x27select[name=\"inquest_rate";
            // line 207
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["config_suffix"] ?? null), "html", null, true);
            yield "\"]\x27, refresh_param_rows);
            </script>
        </div>
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['itemtype'], $context['inquest_itemtype'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 211
        yield "
    ";
        // line 212
        yield $macros["fields"]->getTemplateForMacro("macro_smallTitle", $context, 212, $this->getSourceContext())->macro_smallTitle(...[__("Helpdesk")]);
        yield "
    ";
        // line 213
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownArrayField", $context, 213, $this->getSourceContext())->macro_dropdownArrayField(...["show_tickets_properties_on_helpdesk", (($_v30 = CoreExtension::getAttribute($this->env, $this->source,         // line 215
($context["item"] ?? null), "fields", [], "any", false, false, false, 215)) && is_array($_v30) || $_v30 instanceof ArrayAccess ? ($_v30["show_tickets_properties_on_helpdesk"] ?? null) : null), (CoreExtension::getAttribute($this->env, $this->source,         // line 216
($context["inheritable_params"] ?? null), "toadd", [], "any", false, false, false, 216) + [__("No"), __("Yes")]), __("Show tickets properties on helpdesk"), (["add_field_html" => ((CoreExtension::getAttribute($this->env, $this->source,         // line 222
($context["inheritance_labels"] ?? null), "show_tickets_properties_on_helpdesk", [], "array", true, true, false, 222)) ? (Twig\Extension\CoreExtension::default((($_v31 = ($context["inheritance_labels"] ?? null)) && is_array($_v31) || $_v31 instanceof ArrayAccess ? ($_v31["show_tickets_properties_on_helpdesk"] ?? null) : null), null)) : (null))] +         // line 223
($context["inheritable_params"] ?? null))]);
        // line 224
        yield "

";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "pages/admin/entity/assistance.html.twig";
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
        return array (  354 => 224,  352 => 223,  351 => 222,  350 => 216,  349 => 215,  348 => 213,  344 => 212,  341 => 211,  331 => 207,  327 => 206,  323 => 205,  317 => 202,  309 => 198,  303 => 195,  299 => 194,  295 => 193,  289 => 192,  281 => 187,  275 => 186,  270 => 184,  264 => 183,  260 => 182,  246 => 171,  242 => 170,  238 => 169,  234 => 168,  230 => 167,  226 => 166,  218 => 161,  214 => 160,  208 => 157,  204 => 156,  203 => 155,  202 => 154,  201 => 150,  197 => 149,  192 => 148,  189 => 147,  186 => 146,  181 => 145,  179 => 141,  175 => 139,  173 => 138,  172 => 135,  171 => 130,  168 => 129,  166 => 128,  165 => 124,  163 => 119,  156 => 115,  152 => 114,  147 => 111,  145 => 110,  141 => 109,  137 => 107,  135 => 106,  134 => 99,  133 => 98,  132 => 96,  128 => 95,  127 => 94,  126 => 90,  122 => 89,  121 => 88,  120 => 80,  116 => 79,  115 => 78,  114 => 74,  110 => 73,  109 => 72,  108 => 67,  104 => 66,  103 => 65,  102 => 61,  98 => 60,  97 => 59,  96 => 57,  92 => 56,  87 => 54,  86 => 53,  85 => 52,  81 => 51,  80 => 50,  79 => 49,  75 => 48,  74 => 47,  73 => 46,  69 => 45,  66 => 44,  64 => 40,  63 => 39,  61 => 37,  54 => 36,  49 => 33,  47 => 34,  40 => 33,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "pages/admin/entity/assistance.html.twig", "/var/www/html/glpi/templates/pages/admin/entity/assistance.html.twig");
    }
}
