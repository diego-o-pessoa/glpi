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

/* pages/admin/profile/assistance.html.twig */
class __TwigTemplate_d1ac24c78c39d059d02c5232f24fd321 extends Template
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
            'content' => [$this, 'block_content'],
        ];
    }

    protected function doGetParent(array $context): bool|string|Template|TemplateWrapper
    {
        // line 33
        return "pages/admin/profile/base_tab.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 34
        $macros["fields"] = $this->macros["fields"] = $this->load("components/form/fields_macros.html.twig", 34)->unwrap();
        // line 36
        $context["is_root_entity_active"] = Session::haveAccessToEntity(0);
        // line 33
        $this->parent = $this->load("pages/admin/profile/base_tab.html.twig", 33);
        yield from $this->parent->unwrap()->yield($context, array_merge($this->blocks, $blocks));
    }

    // line 38
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_content(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 39
        yield "    ";
        yield $macros["fields"]->getTemplateForMacro("macro_smallTitle", $context, 39, $this->getSourceContext())->macro_smallTitle(...[__("ITIL Templates")]);
        yield "
    ";
        // line 40
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownField", $context, 40, $this->getSourceContext())->macro_dropdownField(...["TicketTemplate", "tickettemplates_id", (($_v0 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 40)) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0["tickettemplates_id"] ?? null) : null), __("Default ticket template"), ["full_width" => true, "condition" => Twig\Extension\CoreExtension::merge(["entities_id" => 0], (((($tmp = Session::isMultiEntitiesMode()) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (["is_recursive" => 1]) : ([]))), "addicon" =>         // line 43
($context["is_root_entity_active"] ?? null)]]);
        // line 44
        yield "
    ";
        // line 45
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownField", $context, 45, $this->getSourceContext())->macro_dropdownField(...["ChangeTemplate", "changetemplates_id", (($_v1 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 45)) && is_array($_v1) || $_v1 instanceof ArrayAccess ? ($_v1["changetemplates_id"] ?? null) : null), __("Default change template"), ["full_width" => true, "condition" => Twig\Extension\CoreExtension::merge(["entities_id" => 0], (((($tmp = Session::isMultiEntitiesMode()) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (["is_recursive" => 1]) : ([]))), "addicon" =>         // line 48
($context["is_root_entity_active"] ?? null)]]);
        // line 49
        yield "
    ";
        // line 50
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownField", $context, 50, $this->getSourceContext())->macro_dropdownField(...["ProblemTemplate", "problemtemplates_id", (($_v2 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 50)) && is_array($_v2) || $_v2 instanceof ArrayAccess ? ($_v2["problemtemplates_id"] ?? null) : null), __("Default problem template"), ["full_width" => true, "condition" => Twig\Extension\CoreExtension::merge(["entities_id" => 0], (((($tmp = Session::isMultiEntitiesMode()) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (["is_recursive" => 1]) : ([]))), "addicon" =>         // line 53
($context["is_root_entity_active"] ?? null)]]);
        // line 54
        yield "

    ";
        // line 56
        yield $this->getTemplateForMacro("macro_displayRightsMatrix", $context, 56, $this->getSourceContext())->macro_displayRightsMatrix(...[($context["item"] ?? null), "central", "tracking", "itilobjects", _n("ITIL object", "ITIL objects", Session::getPluralNumber())]);
        yield "
    ";
        // line 57
        yield $this->getTemplateForMacro("macro_displayRightsMatrix", $context, 57, $this->getSourceContext())->macro_displayRightsMatrix(...[($context["item"] ?? null), "central", "tracking", "tickets", $this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("Ticket", Session::getPluralNumber())]);
        yield "
    ";
        // line 58
        yield $this->getTemplateForMacro("macro_displayRightsMatrix", $context, 58, $this->getSourceContext())->macro_displayRightsMatrix(...[($context["item"] ?? null), "central", "tracking", "changes", $this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("Change", Session::getPluralNumber())]);
        yield "
    ";
        // line 59
        yield $this->getTemplateForMacro("macro_displayRightsMatrix", $context, 59, $this->getSourceContext())->macro_displayRightsMatrix(...[($context["item"] ?? null), "central", "tracking", "problems", $this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("Problem", Session::getPluralNumber())]);
        yield "
    ";
        // line 60
        yield $this->getTemplateForMacro("macro_displayRightsMatrix", $context, 60, $this->getSourceContext())->macro_displayRightsMatrix(...[($context["item"] ?? null), "central", "tracking", "followups_tasks", (($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("ITILFollowup", Session::getPluralNumber()) . " / ") . $this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("CommonITILTask", Session::getPluralNumber()))]);
        yield "

    ";
        // line 62
        if (($this->extensions['Glpi\Application\View\Extension\ConfigExtension']->config("use_notifications") == "0")) {
            // line 63
            yield "        ";
            $context["activation_msg"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 64
                yield "            <span class=\"alert alert-warning\">
                ";
                // line 65
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Notifications must be enabled to activate mentions."), "html", null, true);
                yield "
            </span>
        ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 68
            yield "        ";
            yield $macros["fields"]->getTemplateForMacro("macro_htmlField", $context, 68, $this->getSourceContext())->macro_htmlField(...["use_mentions", ($context["activation_msg"] ?? null), __("Mentions configuration"), ["full_width" => true]]);
            yield "
    ";
        } else {
            // line 70
            yield "        ";
            $context["user_mention_helper"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 71
                yield "            ";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Enables or disables the ability to mention users within the application."), "html", null, true);
                yield "
            ";
                // line 72
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((__("Disabled") . ": ") . __("User mentions are disabled for this profile.")), "html", null, true);
                yield "
            ";
                // line 73
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((__("Full") . ": ") . __("Displays all users. Mentioned users will be added as observers if they are not already actors.")), "html", null, true);
                yield "
            ";
                // line 74
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((__("Restricted") . ": ") . __("Limits the display to actors directly involved in the ticket.")), "html", null, true);
                yield "
        ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 76
            yield "        ";
            yield $macros["fields"]->getTemplateForMacro("macro_dropdownArrayField", $context, 76, $this->getSourceContext())->macro_dropdownArrayField(...["use_mentions", (($_v3 = CoreExtension::getAttribute($this->env, $this->source,             // line 78
($context["item"] ?? null), "fields", [], "any", false, false, false, 78)) && is_array($_v3) || $_v3 instanceof ArrayAccess ? ($_v3["use_mentions"] ?? null) : null), [ (string)Twig\Extension\CoreExtension::constant("Glpi\\RichText\\UserMention::USER_MENTION_DISABLED") => __("Disabled"),  (string)Twig\Extension\CoreExtension::constant("Glpi\\RichText\\UserMention::USER_MENTION_FULL") => __("Full"),  (string)Twig\Extension\CoreExtension::constant("Glpi\\RichText\\UserMention::USER_MENTION_RESTRICTED") => __("Restricted")], __("Mentions configuration"), ["helper" =>             // line 85
($context["user_mention_helper"] ?? null)]]);
            // line 86
            yield "
    ";
        }
        // line 88
        yield "
    ";
        // line 89
        yield $this->getTemplateForMacro("macro_displayRightsMatrix", $context, 89, $this->getSourceContext())->macro_displayRightsMatrix(...[($context["item"] ?? null), "central", "tracking", "validations", $this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("CommonITILValidation", Session::getPluralNumber())]);
        yield "

    ";
        // line 91
        yield $macros["fields"]->getTemplateForMacro("macro_smallTitle", $context, 91, $this->getSourceContext())->macro_smallTitle(...[__("Association")]);
        yield "

    ";
        // line 93
        yield $macros["fields"]->getTemplateForMacro("macro_checkboxField", $context, 93, $this->getSourceContext())->macro_checkboxField(...["_show_group_hardware", (($_v4 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 93)) && is_array($_v4) || $_v4 instanceof ArrayAccess ? ($_v4["show_group_hardware"] ?? null) : null), __("See hardware of my groups"), ["full_width" => true]]);
        // line 95
        yield "

    ";
        // line 97
        $context["helpdesk_hardware_field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 98
            yield "        ";
            $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Profile::getLinearRightChoice", [$this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Profile::getHelpdeskHardwareTypes", [true]), ["field" => "helpdesk_hardware", "value" => (($_v5 = CoreExtension::getAttribute($this->env, $this->source,             // line 102
($context["item"] ?? null), "fields", [], "any", false, false, false, 102)) && is_array($_v5) || $_v5 instanceof ArrayAccess ? ($_v5["helpdesk_hardware"] ?? null) : null)]]);
            // line 105
            yield "    ";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
        // line 106
        yield "    ";
        yield $macros["fields"]->getTemplateForMacro("macro_htmlField", $context, 106, $this->getSourceContext())->macro_htmlField(...["helpdesk_hardware", ($context["helpdesk_hardware_field"] ?? null), __("Link with items for the creation of tickets"), ["full_width" => true]]);
        // line 108
        yield "

    ";
        // line 110
        $context["associable_types_field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 111
            yield "        ";
            $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Profile::dropdownHelpdeskItemtypes", [["values" => (($_v6 = CoreExtension::getAttribute($this->env, $this->source,             // line 112
($context["item"] ?? null), "fields", [], "any", false, false, false, 112)) && is_array($_v6) || $_v6 instanceof ArrayAccess ? ($_v6["helpdesk_item_type"] ?? null) : null)]]);
            // line 114
            yield "    ";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
        // line 115
        yield "    ";
        yield $macros["fields"]->getTemplateForMacro("macro_htmlField", $context, 115, $this->getSourceContext())->macro_htmlField(...["helpdesk_item_type", ($context["associable_types_field"] ?? null), __("Associable items to tickets, changes and problems"), ["full_width" => true]]);
        // line 117
        yield "

    ";
        // line 119
        yield $this->getTemplateForMacro("macro_displayRightsMatrix", $context, 119, $this->getSourceContext())->macro_displayRightsMatrix(...[($context["item"] ?? null), "central", "tracking", "visibility", __("Visibility")]);
        yield "
    ";
        // line 120
        yield $this->getTemplateForMacro("macro_displayRightsMatrix", $context, 120, $this->getSourceContext())->macro_displayRightsMatrix(...[($context["item"] ?? null), "central", "tracking", "planning", __("Planning")]);
        yield "

";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "pages/admin/profile/assistance.html.twig";
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
        return array (  217 => 120,  213 => 119,  209 => 117,  206 => 115,  202 => 114,  200 => 112,  198 => 111,  196 => 110,  192 => 108,  189 => 106,  185 => 105,  183 => 102,  181 => 98,  179 => 97,  175 => 95,  173 => 93,  168 => 91,  163 => 89,  160 => 88,  156 => 86,  154 => 85,  153 => 78,  151 => 76,  145 => 74,  141 => 73,  137 => 72,  132 => 71,  129 => 70,  123 => 68,  116 => 65,  113 => 64,  110 => 63,  108 => 62,  103 => 60,  99 => 59,  95 => 58,  91 => 57,  87 => 56,  83 => 54,  81 => 53,  80 => 50,  77 => 49,  75 => 48,  74 => 45,  71 => 44,  69 => 43,  68 => 40,  63 => 39,  56 => 38,  51 => 33,  49 => 36,  47 => 34,  40 => 33,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "pages/admin/profile/assistance.html.twig", "/var/www/html/glpi/templates/pages/admin/profile/assistance.html.twig");
    }
}
