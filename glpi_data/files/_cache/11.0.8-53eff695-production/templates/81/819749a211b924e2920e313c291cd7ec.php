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

/* pages/admin/entity/notifications.html.twig */
class __TwigTemplate_036ca77f3ba417034a7fb093a0471106 extends Template
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
            'more_fields' => [$this, 'block_more_fields'],
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
        // line 35
        $context["params"] = (((array_key_exists("params", $context) &&  !(null === $context["params"]))) ? ($context["params"]) : ([]));
        // line 33
        $this->parent = $this->load("generic_show_form.html.twig", 33);
        yield from $this->parent->unwrap()->yield($context, array_merge($this->blocks, $blocks));
    }

    // line 94
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_form_fields(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 95
        yield "   ";
        yield from $this->unwrap()->yieldBlock('more_fields', $context, $blocks);
        yield from [];
    }

    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_more_fields(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 96
        yield "      ";
        yield $macros["fields"]->getTemplateForMacro("macro_largeTitle", $context, 96, $this->getSourceContext())->macro_largeTitle(...[__("Notification options"), $this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeIcon("Notification")]);
        yield "
      ";
        // line 97
        yield $this->getTemplateForMacro("macro_inherited_email", $context, 97, $this->getSourceContext())->macro_inherited_email(...[($context["item"] ?? null), ($context["inheritance_labels"] ?? null), "admin_email", __("Administrator email address")]);
        yield "
      ";
        // line 98
        yield $this->getTemplateForMacro("macro_inherited_text", $context, 98, $this->getSourceContext())->macro_inherited_text(...[($context["item"] ?? null), ($context["inheritance_labels"] ?? null), "admin_email_name", __("Administrator name")]);
        yield "

      ";
        // line 100
        yield $this->getTemplateForMacro("macro_inherited_email", $context, 100, $this->getSourceContext())->macro_inherited_email(...[($context["item"] ?? null), ($context["inheritance_labels"] ?? null), "from_email", __("Email sender email address")]);
        yield "
      ";
        // line 101
        yield $this->getTemplateForMacro("macro_inherited_text", $context, 101, $this->getSourceContext())->macro_inherited_text(...[($context["item"] ?? null), ($context["inheritance_labels"] ?? null), "from_email_name", __("Email sender name")]);
        yield "

      ";
        // line 103
        yield $this->getTemplateForMacro("macro_inherited_email", $context, 103, $this->getSourceContext())->macro_inherited_email(...[($context["item"] ?? null), ($context["inheritance_labels"] ?? null), "noreply_email", __("No-Reply address")]);
        yield "
      ";
        // line 104
        yield $this->getTemplateForMacro("macro_inherited_text", $context, 104, $this->getSourceContext())->macro_inherited_text(...[($context["item"] ?? null), ($context["inheritance_labels"] ?? null), "noreply_email_name", __("No-Reply name")]);
        yield "

      ";
        // line 106
        yield $this->getTemplateForMacro("macro_inherited_email", $context, 106, $this->getSourceContext())->macro_inherited_email(...[($context["item"] ?? null), ($context["inheritance_labels"] ?? null), "replyto_email", __("Reply-To address")]);
        yield "
      ";
        // line 107
        yield $this->getTemplateForMacro("macro_inherited_text", $context, 107, $this->getSourceContext())->macro_inherited_text(...[($context["item"] ?? null), ($context["inheritance_labels"] ?? null), "replyto_email_name", __("Reply-To name")]);
        yield "

      ";
        // line 109
        yield $this->getTemplateForMacro("macro_inherited_text", $context, 109, $this->getSourceContext())->macro_inherited_text(...[($context["item"] ?? null), ($context["inheritance_labels"] ?? null), "notification_subject_tag", __("Prefix for notifications")]);
        yield "

      ";
        // line 111
        $context["to_add"] = [];
        // line 112
        yield "      ";
        if (((($_v0 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 112)) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0["id"] ?? null) : null) > 0)) {
            // line 113
            yield "         ";
            $context["to_add"] = [ (string)Twig\Extension\CoreExtension::constant("Entity::CONFIG_PARENT") => __("Inheritance of the parent entity")];
            // line 116
            yield "      ";
        }
        // line 117
        yield "      ";
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownNumberField", $context, 117, $this->getSourceContext())->macro_dropdownNumberField(...["delay_send_emails", (($_v1 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 117)) && is_array($_v1) || $_v1 instanceof ArrayAccess ? ($_v1["delay_send_emails"] ?? null) : null), __("Delay to send email notifications"), ["add_field_html" => ((CoreExtension::getAttribute($this->env, $this->source,         // line 118
($context["inheritance_labels"] ?? null), "delay_send_emails", [], "array", true, true, false, 118)) ? (Twig\Extension\CoreExtension::default((($_v2 = ($context["inheritance_labels"] ?? null)) && is_array($_v2) || $_v2 instanceof ArrayAccess ? ($_v2["delay_send_emails"] ?? null) : null), null)) : (null)), "min" => 0, "max" => 100, "unit" => "minute", "toadd" =>         // line 122
($context["to_add"] ?? null)]]);
        // line 123
        yield "
      ";
        // line 124
        yield $this->getTemplateForMacro("macro_inherited_alert_yesno", $context, 124, $this->getSourceContext())->macro_inherited_alert_yesno(...[($context["item"] ?? null), ($context["inheritance_labels"] ?? null), "is_notif_enable_default", __("Enable notifications by default")]);
        yield "

      ";
        // line 126
        yield $macros["fields"]->getTemplateForMacro("macro_textareaField", $context, 126, $this->getSourceContext())->macro_textareaField(...["mailing_signature", (($_v3 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 126)) && is_array($_v3) || $_v3 instanceof ArrayAccess ? ($_v3["mailing_signature"] ?? null) : null), __("Email signature"), ["add_field_html" => ((CoreExtension::getAttribute($this->env, $this->source,         // line 127
($context["inheritance_labels"] ?? null), "mailing_signature", [], "array", true, true, false, 127)) ? (Twig\Extension\CoreExtension::default((($_v4 = ($context["inheritance_labels"] ?? null)) && is_array($_v4) || $_v4 instanceof ArrayAccess ? ($_v4["mailing_signature"] ?? null) : null), null)) : (null))]]);
        // line 128
        yield "
      ";
        // line 129
        yield $this->getTemplateForMacro("macro_inherited_text", $context, 129, $this->getSourceContext())->macro_inherited_text(...[($context["item"] ?? null), ($context["inheritance_labels"] ?? null), "url_base", __("URL of the application")]);
        yield "

      ";
        // line 131
        yield $macros["fields"]->getTemplateForMacro("macro_largeTitle", $context, 131, $this->getSourceContext())->macro_largeTitle(...[__("Alarms options"), "ti ti-alarm"]);
        yield "

      ";
        // line 133
        yield $macros["fields"]->getTemplateForMacro("macro_smallTitle", $context, 133, $this->getSourceContext())->macro_smallTitle(...[$this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("Cartridge", Session::getPluralNumber())]);
        yield "
      ";
        // line 134
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownField", $context, 134, $this->getSourceContext())->macro_dropdownField(...["Alert", "cartridges_alert_repeat", (($_v5 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 134)) && is_array($_v5) || $_v5 instanceof ArrayAccess ? ($_v5["cartridges_alert_repeat"] ?? null) : null), __("Reminders frequency for alarms on cartridges"), ["add_field_html" => ((CoreExtension::getAttribute($this->env, $this->source,         // line 135
($context["inheritance_labels"] ?? null), "cartridges_alert_repeat", [], "array", true, true, false, 135)) ? (Twig\Extension\CoreExtension::default((($_v6 = ($context["inheritance_labels"] ?? null)) && is_array($_v6) || $_v6 instanceof ArrayAccess ? ($_v6["cartridges_alert_repeat"] ?? null) : null), null)) : (null)), "inherit_parent" => ((((($_v7 = CoreExtension::getAttribute($this->env, $this->source,         // line 136
($context["item"] ?? null), "fields", [], "any", false, false, false, 136)) && is_array($_v7) || $_v7 instanceof ArrayAccess ? ($_v7["id"] ?? null) : null) > 0)) ? (1) : (0))]]);
        // line 137
        yield "
      ";
        // line 138
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownNumberField", $context, 138, $this->getSourceContext())->macro_dropdownNumberField(...["default_cartridges_alarm_threshold", (($_v8 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 138)) && is_array($_v8) || $_v8 instanceof ArrayAccess ? ($_v8["default_cartridges_alarm_threshold"] ?? null) : null), __("Default threshold for cartridges count"), ["add_field_html" => ((CoreExtension::getAttribute($this->env, $this->source,         // line 139
($context["inheritance_labels"] ?? null), "default_cartridges_alarm_threshold", [], "array", true, true, false, 139)) ? (Twig\Extension\CoreExtension::default((($_v9 = ($context["inheritance_labels"] ?? null)) && is_array($_v9) || $_v9 instanceof ArrayAccess ? ($_v9["default_cartridges_alarm_threshold"] ?? null) : null), null)) : (null)), "min" => 0, "max" => 100, "toadd" => (        // line 142
($context["to_add"] ?? null) + [ (string)Twig\Extension\CoreExtension::constant("Entity::CONFIG_NEVER") => __("Never")])]]);
        // line 145
        yield "

      ";
        // line 147
        yield $macros["fields"]->getTemplateForMacro("macro_smallTitle", $context, 147, $this->getSourceContext())->macro_smallTitle(...[$this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("Consumable", Session::getPluralNumber())]);
        yield "
      ";
        // line 148
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownField", $context, 148, $this->getSourceContext())->macro_dropdownField(...["Alert", "consumables_alert_repeat", (($_v10 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 148)) && is_array($_v10) || $_v10 instanceof ArrayAccess ? ($_v10["consumables_alert_repeat"] ?? null) : null), __("Reminders frequency for alarms on consumables"), ["add_field_html" => ((CoreExtension::getAttribute($this->env, $this->source,         // line 149
($context["inheritance_labels"] ?? null), "consumables_alert_repeat", [], "array", true, true, false, 149)) ? (Twig\Extension\CoreExtension::default((($_v11 = ($context["inheritance_labels"] ?? null)) && is_array($_v11) || $_v11 instanceof ArrayAccess ? ($_v11["consumables_alert_repeat"] ?? null) : null), null)) : (null)), "inherit_parent" => ((((($_v12 = CoreExtension::getAttribute($this->env, $this->source,         // line 150
($context["item"] ?? null), "fields", [], "any", false, false, false, 150)) && is_array($_v12) || $_v12 instanceof ArrayAccess ? ($_v12["id"] ?? null) : null) > 0)) ? (1) : (0))]]);
        // line 151
        yield "
      ";
        // line 152
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownNumberField", $context, 152, $this->getSourceContext())->macro_dropdownNumberField(...["default_consumables_alarm_threshold", (($_v13 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 152)) && is_array($_v13) || $_v13 instanceof ArrayAccess ? ($_v13["default_consumables_alarm_threshold"] ?? null) : null), __("Default threshold for consumables count"), ["add_field_html" => ((CoreExtension::getAttribute($this->env, $this->source,         // line 153
($context["inheritance_labels"] ?? null), "default_consumables_alarm_threshold", [], "array", true, true, false, 153)) ? (Twig\Extension\CoreExtension::default((($_v14 = ($context["inheritance_labels"] ?? null)) && is_array($_v14) || $_v14 instanceof ArrayAccess ? ($_v14["default_consumables_alarm_threshold"] ?? null) : null), null)) : (null)), "min" => 0, "max" => 100, "toadd" => (        // line 156
($context["to_add"] ?? null) + [ (string)Twig\Extension\CoreExtension::constant("Entity::CONFIG_NEVER") => __("Never")])]]);
        // line 159
        yield "

      ";
        // line 161
        yield $macros["fields"]->getTemplateForMacro("macro_smallTitle", $context, 161, $this->getSourceContext())->macro_smallTitle(...[$this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("Contract", Session::getPluralNumber())]);
        yield "
      ";
        // line 162
        yield $this->getTemplateForMacro("macro_inherited_alert_yesno", $context, 162, $this->getSourceContext())->macro_inherited_alert_yesno(...[($context["item"] ?? null), ($context["inheritance_labels"] ?? null), "use_contracts_alert", __("Alarms on contracts")]);
        yield "
      ";
        // line 163
        yield $this->getTemplateForMacro("macro_inherited_alert_dropdown", $context, 163, $this->getSourceContext())->macro_inherited_alert_dropdown(...[($context["item"] ?? null), ($context["inheritance_labels"] ?? null), "default_contract_alert", __("Default value"), ($context["contract_alert_choices"] ?? null)]);
        yield "
      ";
        // line 164
        yield $this->getTemplateForMacro("macro_inherited_alert_int_never", $context, 164, $this->getSourceContext())->macro_inherited_alert_int_never(...[($context["item"] ?? null), ($context["inheritance_labels"] ?? null), "send_contracts_alert_before_delay", __("Send contract alarms before")]);
        yield "

      ";
        // line 166
        yield $macros["fields"]->getTemplateForMacro("macro_smallTitle", $context, 166, $this->getSourceContext())->macro_smallTitle(...[$this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("Infocom", Session::getPluralNumber())]);
        yield "
      ";
        // line 167
        yield $this->getTemplateForMacro("macro_inherited_alert_yesno", $context, 167, $this->getSourceContext())->macro_inherited_alert_yesno(...[($context["item"] ?? null), ($context["inheritance_labels"] ?? null), "use_infocoms_alert", __("Alarms on financial and administrative information")]);
        yield "
      ";
        // line 168
        yield $this->getTemplateForMacro("macro_inherited_alert_dropdown", $context, 168, $this->getSourceContext())->macro_inherited_alert_dropdown(...[($context["item"] ?? null), ($context["inheritance_labels"] ?? null), "default_infocom_alert", __("Default value"), ($context["infocom_alert_choices"] ?? null)]);
        yield "
      ";
        // line 169
        yield $this->getTemplateForMacro("macro_inherited_alert_int_never", $context, 169, $this->getSourceContext())->macro_inherited_alert_int_never(...[($context["item"] ?? null), ($context["inheritance_labels"] ?? null), "send_infocoms_alert_before_delay", __("Send financial and administrative information alarms before")]);
        yield "

      ";
        // line 171
        yield $macros["fields"]->getTemplateForMacro("macro_smallTitle", $context, 171, $this->getSourceContext())->macro_smallTitle(...[$this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("SoftwareLicense", Session::getPluralNumber())]);
        yield "
      ";
        // line 172
        yield $this->getTemplateForMacro("macro_inherited_alert_yesno", $context, 172, $this->getSourceContext())->macro_inherited_alert_yesno(...[($context["item"] ?? null), ($context["inheritance_labels"] ?? null), "use_licenses_alert", __("Alarms on expired licenses")]);
        yield "
      ";
        // line 173
        yield $this->getTemplateForMacro("macro_inherited_alert_int_never", $context, 173, $this->getSourceContext())->macro_inherited_alert_int_never(...[($context["item"] ?? null), ($context["inheritance_labels"] ?? null), "send_licenses_alert_before_delay", __("Send license alarms before")]);
        yield "

      ";
        // line 175
        yield $macros["fields"]->getTemplateForMacro("macro_smallTitle", $context, 175, $this->getSourceContext())->macro_smallTitle(...[$this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("Certificate", Session::getPluralNumber())]);
        yield "
      ";
        // line 176
        yield $this->getTemplateForMacro("macro_inherited_alert_yesno", $context, 176, $this->getSourceContext())->macro_inherited_alert_yesno(...[($context["item"] ?? null), ($context["inheritance_labels"] ?? null), "use_certificates_alert", __("Alarms on expired certificates")]);
        yield "
      ";
        // line 177
        yield $this->getTemplateForMacro("macro_inherited_alert_int_never", $context, 177, $this->getSourceContext())->macro_inherited_alert_int_never(...[($context["item"] ?? null), ($context["inheritance_labels"] ?? null), "send_certificates_alert_before_delay", __("Send certificates alarms before")]);
        yield "
      ";
        // line 178
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownField", $context, 178, $this->getSourceContext())->macro_dropdownField(...["Alert", "certificates_alert_repeat_interval", (($_v15 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 178)) && is_array($_v15) || $_v15 instanceof ArrayAccess ? ($_v15["certificates_alert_repeat_interval"] ?? null) : null), __("Reminders frequency for alarms on certificates"), ["add_field_html" => ((CoreExtension::getAttribute($this->env, $this->source,         // line 179
($context["inheritance_labels"] ?? null), "certificates_alert_repeat_interval", [], "array", true, true, false, 179)) ? (Twig\Extension\CoreExtension::default((($_v16 = ($context["inheritance_labels"] ?? null)) && is_array($_v16) || $_v16 instanceof ArrayAccess ? ($_v16["certificates_alert_repeat_interval"] ?? null) : null), null)) : (null)), "inherit_parent" => ((((($_v17 = CoreExtension::getAttribute($this->env, $this->source,         // line 180
($context["item"] ?? null), "fields", [], "any", false, false, false, 180)) && is_array($_v17) || $_v17 instanceof ArrayAccess ? ($_v17["id"] ?? null) : null) > 0)) ? (1) : (0))]]);
        // line 181
        yield "

      ";
        // line 183
        yield $macros["fields"]->getTemplateForMacro("macro_smallTitle", $context, 183, $this->getSourceContext())->macro_smallTitle(...[$this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("Reservation", Session::getPluralNumber())]);
        yield "
      ";
        // line 184
        yield $this->getTemplateForMacro("macro_inherited_alert_int_never", $context, 184, $this->getSourceContext())->macro_inherited_alert_int_never(...[($context["item"] ?? null), ($context["inheritance_labels"] ?? null), "use_reservations_alert", __("Alerts on reservations"), "hour"]);
        yield "

      ";
        // line 186
        yield $macros["fields"]->getTemplateForMacro("macro_smallTitle", $context, 186, $this->getSourceContext())->macro_smallTitle(...[$this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("Ticket", Session::getPluralNumber())]);
        yield "
      ";
        // line 187
        yield $this->getTemplateForMacro("macro_inherited_alert_int_never", $context, 187, $this->getSourceContext())->macro_inherited_alert_int_never(...[($context["item"] ?? null), ($context["inheritance_labels"] ?? null), "notclosed_delay", __("Alerts on tickets which are not solved since")]);
        yield "

      ";
        // line 189
        yield $macros["fields"]->getTemplateForMacro("macro_smallTitle", $context, 189, $this->getSourceContext())->macro_smallTitle(...[Twig\Extension\CoreExtension::sprintf("%s / %s", $this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("Ticket", Session::getPluralNumber()), $this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("Change", Session::getPluralNumber()))]);
        yield "
      ";
        // line 190
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownField", $context, 190, $this->getSourceContext())->macro_dropdownField(...["Alert", "approval_reminder_repeat_interval", (($_v18 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 190)) && is_array($_v18) || $_v18 instanceof ArrayAccess ? ($_v18["approval_reminder_repeat_interval"] ?? null) : null), __("Approval reminder frequency"), ["add_field_html" => ((CoreExtension::getAttribute($this->env, $this->source,         // line 191
($context["inheritance_labels"] ?? null), "approval_reminder_repeat_interval", [], "array", true, true, false, 191)) ? (Twig\Extension\CoreExtension::default((($_v19 = ($context["inheritance_labels"] ?? null)) && is_array($_v19) || $_v19 instanceof ArrayAccess ? ($_v19["approval_reminder_repeat_interval"] ?? null) : null), null)) : (null)), "inherit_parent" => ((((($_v20 = CoreExtension::getAttribute($this->env, $this->source,         // line 192
($context["item"] ?? null), "fields", [], "any", false, false, false, 192)) && is_array($_v20) || $_v20 instanceof ArrayAccess ? ($_v20["id"] ?? null) : null) > 0)) ? (1) : (0))]]);
        // line 193
        yield "

      ";
        // line 195
        yield $macros["fields"]->getTemplateForMacro("macro_smallTitle", $context, 195, $this->getSourceContext())->macro_smallTitle(...[$this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("Domain", Session::getPluralNumber())]);
        yield "
      ";
        // line 196
        yield $this->getTemplateForMacro("macro_inherited_alert_yesno", $context, 196, $this->getSourceContext())->macro_inherited_alert_yesno(...[($context["item"] ?? null), ($context["inheritance_labels"] ?? null), "use_domains_alert", __("Alarms on domains expiries")]);
        yield "
      ";
        // line 197
        yield $this->getTemplateForMacro("macro_inherited_alert_int_never", $context, 197, $this->getSourceContext())->macro_inherited_alert_int_never(...[($context["item"] ?? null), ($context["inheritance_labels"] ?? null), "send_domains_alert_close_expiries_delay", __("Domains closes expiries")]);
        yield "
      ";
        // line 198
        yield $this->getTemplateForMacro("macro_inherited_alert_int_never", $context, 198, $this->getSourceContext())->macro_inherited_alert_int_never(...[($context["item"] ?? null), ($context["inheritance_labels"] ?? null), "send_domains_alert_expired_delay", __("Domains expired")]);
        yield "

   ";
        yield from [];
    }

    // line 37
    public function macro_inherited_email($item = null, $inheritance_labels = null, $field = null, $label = null, ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "item" => $item,
            "inheritance_labels" => $inheritance_labels,
            "field" => $field,
            "label" => $label,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 38
            yield "   ";
            yield $macros["fields"]->getTemplateForMacro("macro_emailField", $context, 38, $this->getSourceContext())->macro_emailField(...[($context["field"] ?? null), (($_v21 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 38)) && is_array($_v21) || $_v21 instanceof ArrayAccess ? ($_v21[(($_v22 = ($context["field"] ?? null)) instanceof \Stringable ? (string) $_v22 : $_v22)] ?? null) : null), ($context["label"] ?? null), ["add_field_html" => ((CoreExtension::getAttribute($this->env, $this->source,             // line 39
($context["inheritance_labels"] ?? null), ($context["field"] ?? null), [], "array", true, true, false, 39)) ? (Twig\Extension\CoreExtension::default((($_v23 = ($context["inheritance_labels"] ?? null)) && is_array($_v23) || $_v23 instanceof ArrayAccess ? ($_v23[(($_v24 = ($context["field"] ?? null)) instanceof \Stringable ? (string) $_v24 : $_v24)] ?? null) : null), null)) : (null))]]);
            // line 40
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 43
    public function macro_inherited_text($item = null, $inheritance_labels = null, $field = null, $label = null, ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "item" => $item,
            "inheritance_labels" => $inheritance_labels,
            "field" => $field,
            "label" => $label,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 44
            yield "   ";
            yield $macros["fields"]->getTemplateForMacro("macro_textField", $context, 44, $this->getSourceContext())->macro_textField(...[($context["field"] ?? null), (($_v25 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 44)) && is_array($_v25) || $_v25 instanceof ArrayAccess ? ($_v25[(($_v26 = ($context["field"] ?? null)) instanceof \Stringable ? (string) $_v26 : $_v26)] ?? null) : null), ($context["label"] ?? null), ["add_field_html" => ((CoreExtension::getAttribute($this->env, $this->source,             // line 45
($context["inheritance_labels"] ?? null), ($context["field"] ?? null), [], "array", true, true, false, 45)) ? (Twig\Extension\CoreExtension::default((($_v27 = ($context["inheritance_labels"] ?? null)) && is_array($_v27) || $_v27 instanceof ArrayAccess ? ($_v27[(($_v28 = ($context["field"] ?? null)) instanceof \Stringable ? (string) $_v28 : $_v28)] ?? null) : null), null)) : (null))]]);
            // line 46
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 49
    public function macro_inherited_alert_yesno($item = null, $inheritance_labels = null, $field = null, $label = null, ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "item" => $item,
            "inheritance_labels" => $inheritance_labels,
            "field" => $field,
            "label" => $label,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 50
            yield "   ";
            $context["to_add"] = [];
            // line 51
            yield "   ";
            if (((($_v29 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 51)) && is_array($_v29) || $_v29 instanceof ArrayAccess ? ($_v29["id"] ?? null) : null) > 0)) {
                // line 52
                yield "      ";
                $context["to_add"] = [ (string)Twig\Extension\CoreExtension::constant("Entity::CONFIG_PARENT") => __("Inheritance of the parent entity")];
                // line 55
                yield "   ";
            }
            // line 56
            yield "   ";
            yield $macros["fields"]->getTemplateForMacro("macro_dropdownArrayField", $context, 56, $this->getSourceContext())->macro_dropdownArrayField(...[($context["field"] ?? null), (($_v30 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 56)) && is_array($_v30) || $_v30 instanceof ArrayAccess ? ($_v30[(($_v31 = ($context["field"] ?? null)) instanceof \Stringable ? (string) $_v31 : $_v31)] ?? null) : null), (($context["to_add"] ?? null) + [__("No"), __("Yes")]),             // line 59
($context["label"] ?? null), ["add_field_html" => ((CoreExtension::getAttribute($this->env, $this->source,             // line 60
($context["inheritance_labels"] ?? null), ($context["field"] ?? null), [], "array", true, true, false, 60)) ? (Twig\Extension\CoreExtension::default((($_v32 = ($context["inheritance_labels"] ?? null)) && is_array($_v32) || $_v32 instanceof ArrayAccess ? ($_v32[(($_v33 = ($context["field"] ?? null)) instanceof \Stringable ? (string) $_v33 : $_v33)] ?? null) : null), null)) : (null))]]);
            // line 61
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 64
    public function macro_inherited_alert_int_never($item = null, $inheritance_labels = null, $field = null, $label = null, $unit = "day", ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "item" => $item,
            "inheritance_labels" => $inheritance_labels,
            "field" => $field,
            "label" => $label,
            "unit" => $unit,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 65
            yield "   ";
            $context["to_add"] = [];
            // line 66
            yield "   ";
            if (((($_v34 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 66)) && is_array($_v34) || $_v34 instanceof ArrayAccess ? ($_v34["id"] ?? null) : null) > 0)) {
                // line 67
                yield "      ";
                $context["to_add"] = [ (string)Twig\Extension\CoreExtension::constant("Entity::CONFIG_PARENT") => __("Inheritance of the parent entity")];
                // line 70
                yield "   ";
            }
            // line 71
            yield "   ";
            yield $macros["fields"]->getTemplateForMacro("macro_dropdownNumberField", $context, 71, $this->getSourceContext())->macro_dropdownNumberField(...[($context["field"] ?? null), (($_v35 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 71)) && is_array($_v35) || $_v35 instanceof ArrayAccess ? ($_v35[(($_v36 = ($context["field"] ?? null)) instanceof \Stringable ? (string) $_v36 : $_v36)] ?? null) : null), ($context["label"] ?? null), ["min" => 1, "max" => 365, "unit" =>             // line 74
($context["unit"] ?? null), "toadd" => (            // line 75
($context["to_add"] ?? null) + [__("No")]), "add_field_html" => ((CoreExtension::getAttribute($this->env, $this->source,             // line 78
($context["inheritance_labels"] ?? null), ($context["field"] ?? null), [], "array", true, true, false, 78)) ? (Twig\Extension\CoreExtension::default((($_v37 = ($context["inheritance_labels"] ?? null)) && is_array($_v37) || $_v37 instanceof ArrayAccess ? ($_v37[(($_v38 = ($context["field"] ?? null)) instanceof \Stringable ? (string) $_v38 : $_v38)] ?? null) : null), null)) : (null))]]);
            // line 79
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 82
    public function macro_inherited_alert_dropdown($item = null, $inheritance_labels = null, $field = null, $label = null, $values = null, ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "item" => $item,
            "inheritance_labels" => $inheritance_labels,
            "field" => $field,
            "label" => $label,
            "values" => $values,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 83
            yield "   ";
            $context["to_add"] = [];
            // line 84
            yield "   ";
            if (((($_v39 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 84)) && is_array($_v39) || $_v39 instanceof ArrayAccess ? ($_v39["id"] ?? null) : null) > 0)) {
                // line 85
                yield "      ";
                $context["to_add"] = [ (string)Twig\Extension\CoreExtension::constant("Entity::CONFIG_PARENT") => __("Inheritance of the parent entity")];
                // line 88
                yield "   ";
            }
            // line 89
            yield "   ";
            yield $macros["fields"]->getTemplateForMacro("macro_dropdownArrayField", $context, 89, $this->getSourceContext())->macro_dropdownArrayField(...[($context["field"] ?? null), (($_v40 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 89)) && is_array($_v40) || $_v40 instanceof ArrayAccess ? ($_v40[(($_v41 = ($context["field"] ?? null)) instanceof \Stringable ? (string) $_v41 : $_v41)] ?? null) : null), (($context["to_add"] ?? null) + ($context["values"] ?? null)), ($context["label"] ?? null), ["add_field_html" => ((CoreExtension::getAttribute($this->env, $this->source,             // line 90
($context["inheritance_labels"] ?? null), ($context["field"] ?? null), [], "array", true, true, false, 90)) ? (Twig\Extension\CoreExtension::default((($_v42 = ($context["inheritance_labels"] ?? null)) && is_array($_v42) || $_v42 instanceof ArrayAccess ? ($_v42[(($_v43 = ($context["field"] ?? null)) instanceof \Stringable ? (string) $_v43 : $_v43)] ?? null) : null), null)) : (null))]]);
            // line 91
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "pages/admin/entity/notifications.html.twig";
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
        return array (  481 => 91,  479 => 90,  477 => 89,  474 => 88,  471 => 85,  468 => 84,  465 => 83,  449 => 82,  442 => 79,  440 => 78,  439 => 75,  438 => 74,  436 => 71,  433 => 70,  430 => 67,  427 => 66,  424 => 65,  408 => 64,  401 => 61,  399 => 60,  398 => 59,  396 => 56,  393 => 55,  390 => 52,  387 => 51,  384 => 50,  369 => 49,  362 => 46,  360 => 45,  358 => 44,  343 => 43,  336 => 40,  334 => 39,  332 => 38,  317 => 37,  309 => 198,  305 => 197,  301 => 196,  297 => 195,  293 => 193,  291 => 192,  290 => 191,  289 => 190,  285 => 189,  280 => 187,  276 => 186,  271 => 184,  267 => 183,  263 => 181,  261 => 180,  260 => 179,  259 => 178,  255 => 177,  251 => 176,  247 => 175,  242 => 173,  238 => 172,  234 => 171,  229 => 169,  225 => 168,  221 => 167,  217 => 166,  212 => 164,  208 => 163,  204 => 162,  200 => 161,  196 => 159,  194 => 156,  193 => 153,  192 => 152,  189 => 151,  187 => 150,  186 => 149,  185 => 148,  181 => 147,  177 => 145,  175 => 142,  174 => 139,  173 => 138,  170 => 137,  168 => 136,  167 => 135,  166 => 134,  162 => 133,  157 => 131,  152 => 129,  149 => 128,  147 => 127,  146 => 126,  141 => 124,  138 => 123,  136 => 122,  135 => 118,  133 => 117,  130 => 116,  127 => 113,  124 => 112,  122 => 111,  117 => 109,  112 => 107,  108 => 106,  103 => 104,  99 => 103,  94 => 101,  90 => 100,  85 => 98,  81 => 97,  76 => 96,  64 => 95,  57 => 94,  52 => 33,  50 => 35,  48 => 34,  41 => 33,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "pages/admin/entity/notifications.html.twig", "/var/www/html/glpi/templates/pages/admin/entity/notifications.html.twig");
    }
}
