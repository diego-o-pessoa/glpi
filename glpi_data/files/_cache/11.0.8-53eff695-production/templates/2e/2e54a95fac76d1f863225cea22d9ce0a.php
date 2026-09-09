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

/* pages/admin/inventory/agent.html.twig */
class __TwigTemplate_d77f08f36f64d6986fcdd3518d1ace16 extends Template
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
        $macros["fields"] = $this->macros["fields"] = $this->load("components/form/fields_macros.html.twig", 33)->unwrap();
        // line 34
        yield "
";
        // line 35
        $context["no_header"] = ((array_key_exists("no_header", $context)) ? (Twig\Extension\CoreExtension::default(($context["no_header"] ?? null), ( !CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "isNewItem", [], "method", false, false, false, 35) &&  !((CoreExtension::getAttribute($this->env, $this->source, ($context["_get"] ?? null), "_in_modal", [], "any", true, true, false, 35)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["_get"] ?? null), "_in_modal", [], "any", false, false, false, 35), false)) : (false))))) : (( !CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "isNewItem", [], "method", false, false, false, 35) &&  !((CoreExtension::getAttribute($this->env, $this->source, ($context["_get"] ?? null), "_in_modal", [], "any", true, true, false, 35)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["_get"] ?? null), "_in_modal", [], "any", false, false, false, 35), false)) : (false)))));
        // line 36
        $context["bg"] = "";
        // line 37
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "isDeleted", [], "method", false, false, false, 37)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 38
            yield "   ";
            $context["bg"] = "asset-deleted";
        }
        // line 40
        yield "
<div class=\"asset ";
        // line 41
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["bg"] ?? null), "html", null, true);
        yield "\">
   ";
        // line 42
        yield Twig\Extension\CoreExtension::include($this->env, $context, "components/form/header.html.twig", ["in_twig" => true]);
        yield "

   ";
        // line 44
        $context["rand"] = Twig\Extension\CoreExtension::random($this->env->getCharset());
        // line 45
        yield "   ";
        $context["params"] = (((array_key_exists("params", $context) &&  !(null === $context["params"]))) ? ($context["params"]) : ([]));
        // line 46
        yield "   ";
        $context["target"] = (((CoreExtension::getAttribute($this->env, $this->source, ($context["params"] ?? null), "target", [], "array", true, true, false, 46) &&  !(null === (($_v0 = ($context["params"] ?? null)) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0["target"] ?? null) : null)))) ? ((($_v1 = ($context["params"] ?? null)) && is_array($_v1) || $_v1 instanceof ArrayAccess ? ($_v1["target"] ?? null) : null)) : (CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "getFormURL", [], "method", false, false, false, 46)));
        // line 47
        yield "   ";
        $context["withtemplate"] = (((CoreExtension::getAttribute($this->env, $this->source, ($context["params"] ?? null), "withtemplate", [], "array", true, true, false, 47) &&  !(null === (($_v2 = ($context["params"] ?? null)) && is_array($_v2) || $_v2 instanceof ArrayAccess ? ($_v2["withtemplate"] ?? null) : null)))) ? ((($_v3 = ($context["params"] ?? null)) && is_array($_v3) || $_v3 instanceof ArrayAccess ? ($_v3["withtemplate"] ?? null) : null)) : (""));
        // line 48
        yield "   ";
        $context["item_type"] = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "getType", [], "method", false, false, false, 48);
        // line 49
        yield "   ";
        $context["field_options"] = ["locked_fields" => CoreExtension::getAttribute($this->env, $this->source,         // line 50
($context["item"] ?? null), "getLockedFields", [], "method", false, false, false, 50), "rand" =>         // line 51
($context["rand"] ?? null)];
        // line 53
        yield "
   <div class=\"card-body d-flex flex-wrap\">
      <div class=\"col-12 flex-column\">
         <div class=\"d-flex flex-row flex-wrap flex-xl-nowrap\">
            <div class=\"row flex-row align-items-start flex-grow-1\">
               <div class=\"row flex-row\">

                    ";
        // line 60
        yield $macros["fields"]->getTemplateForMacro("macro_htmlField", $context, 60, $this->getSourceContext())->macro_htmlField(...["name", $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v4 = CoreExtension::getAttribute($this->env, $this->source,         // line 62
($context["item"] ?? null), "fields", [], "any", false, false, false, 62)) && is_array($_v4) || $_v4 instanceof ArrayAccess ? ($_v4["name"] ?? null) : null)), __("Name"),         // line 64
($context["field_options"] ?? null)]);
        // line 65
        yield "

                    ";
        // line 67
        yield $macros["fields"]->getTemplateForMacro("macro_htmlField", $context, 67, $this->getSourceContext())->macro_htmlField(...["agenttypes_id", $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemName("AgentType", (($_v5 = CoreExtension::getAttribute($this->env, $this->source,         // line 69
($context["item"] ?? null), "fields", [], "any", false, false, false, 69)) && is_array($_v5) || $_v5 instanceof ArrayAccess ? ($_v5["agenttypes_id"] ?? null) : null))), _n("Agent type", "Agents types", 1),         // line 71
($context["field_options"] ?? null)]);
        // line 72
        yield "

                    ";
        // line 74
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownYesNo", $context, 74, $this->getSourceContext())->macro_dropdownYesNo(...["locked", (($_v6 = CoreExtension::getAttribute($this->env, $this->source,         // line 76
($context["item"] ?? null), "fields", [], "any", false, false, false, 76)) && is_array($_v6) || $_v6 instanceof ArrayAccess ? ($_v6["locked"] ?? null) : null), __("Locked"),         // line 78
($context["field_options"] ?? null)]);
        // line 79
        yield "

                    ";
        // line 81
        yield $macros["fields"]->getTemplateForMacro("macro_htmlField", $context, 81, $this->getSourceContext())->macro_htmlField(...["deviceid", $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v7 = CoreExtension::getAttribute($this->env, $this->source,         // line 83
($context["item"] ?? null), "fields", [], "any", false, false, false, 83)) && is_array($_v7) || $_v7 instanceof ArrayAccess ? ($_v7["deviceid"] ?? null) : null)), __("Device ID"),         // line 85
($context["field_options"] ?? null)]);
        // line 86
        yield "

                    ";
        // line 88
        yield $macros["fields"]->getTemplateForMacro("macro_numberField", $context, 88, $this->getSourceContext())->macro_numberField(...["port", (($_v8 = CoreExtension::getAttribute($this->env, $this->source,         // line 90
($context["item"] ?? null), "fields", [], "any", false, false, false, 90)) && is_array($_v8) || $_v8 instanceof ArrayAccess ? ($_v8["port"] ?? null) : null), _n("Port", "Ports", 1),         // line 92
($context["field_options"] ?? null)]);
        // line 93
        yield "

                    ";
        // line 95
        yield $macros["fields"]->getTemplateForMacro("macro_htmlField", $context, 95, $this->getSourceContext())->macro_htmlField(...["Agent", $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v9 = CoreExtension::getAttribute($this->env, $this->source,         // line 97
($context["item"] ?? null), "fields", [], "any", false, false, false, 97)) && is_array($_v9) || $_v9 instanceof ArrayAccess ? ($_v9["remote_addr"] ?? null) : null)), __("Public contact address"),         // line 99
($context["field_options"] ?? null)]);
        // line 100
        yield "

                    ";
        // line 102
        yield $macros["fields"]->getTemplateForMacro("macro_htmlField", $context, 102, $this->getSourceContext())->macro_htmlField(...["itemtype", $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName((($_v10 = CoreExtension::getAttribute($this->env, $this->source,         // line 104
($context["item"] ?? null), "fields", [], "any", false, false, false, 104)) && is_array($_v10) || $_v10 instanceof ArrayAccess ? ($_v10["itemtype"] ?? null) : null))), __("Item type"),         // line 106
($context["field_options"] ?? null)]);
        // line 107
        yield "

                    ";
        // line 109
        if ((($tmp =  !CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "isNewItem", [], "method", false, false, false, 109)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 110
            yield "                        ";
            $context["asset_item"] = $this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItem((($_v11 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 110)) && is_array($_v11) || $_v11 instanceof ArrayAccess ? ($_v11["itemtype"] ?? null) : null), (($_v12 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 110)) && is_array($_v12) || $_v12 instanceof ArrayAccess ? ($_v12["items_id"] ?? null) : null));
            // line 111
            yield "                        ";
            yield $macros["fields"]->getTemplateForMacro("macro_htmlField", $context, 111, $this->getSourceContext())->macro_htmlField(...["items_id", CoreExtension::getAttribute($this->env, $this->source,             // line 113
($context["asset_item"] ?? null), "getLink", [], "method", false, false, false, 113), __("Item link"),             // line 115
($context["field_options"] ?? null)]);
            // line 116
            yield "
                        <input type=\"hidden\" name=\"items_id\" value=\"";
            // line 117
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v13 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 117)) && is_array($_v13) || $_v13 instanceof ArrayAccess ? ($_v13["items_id"] ?? null) : null), "html", null, true);
            yield "\">

                        ";
            // line 119
            $context["versions"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 120
                yield "                            ";
                $context["versions_array"] = $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("importArrayFromDB", [($context["versions_field"] ?? null)]);
                // line 121
                yield "                            ";
                if ((($tmp = Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["versions_array"] ?? null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 122
                    yield "                                <ul>
                                ";
                    // line 123
                    $context['_parent'] = $context;
                    $context['_seq'] = CoreExtension::ensureTraversable(($context["versions_array"] ?? null));
                    foreach ($context['_seq'] as $context["module"] => $context["version"]) {
                        // line 124
                        yield "                                    <li><strong>";
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["module"], "html", null, true);
                        yield "</strong>: ";
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["version"], "html", null, true);
                        yield "</li>
                                ";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['module'], $context['version'], $context['_parent']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 126
                    yield "                                </ul>
                            ";
                } elseif ((($tmp = Twig\Extension\CoreExtension::length($this->env->getCharset(),                 // line 127
($context["versions_field"] ?? null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 128
                    yield "                                ";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["versions_field"] ?? null), "html", null, true);
                    yield "
                            ";
                }
                // line 130
                yield "                        ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 131
            yield "                        ";
            yield $macros["fields"]->getTemplateForMacro("macro_htmlField", $context, 131, $this->getSourceContext())->macro_htmlField(...["versions",             // line 133
($context["versions"] ?? null), _n("Version", "Versions", 1),             // line 135
($context["field_options"] ?? null)]);
            // line 136
            yield "

                        ";
            // line 138
            yield $macros["fields"]->getTemplateForMacro("macro_htmlField", $context, 138, $this->getSourceContext())->macro_htmlField(...["tag", $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v14 = CoreExtension::getAttribute($this->env, $this->source,             // line 140
($context["item"] ?? null), "fields", [], "any", false, false, false, 140)) && is_array($_v14) || $_v14 instanceof ArrayAccess ? ($_v14["tag"] ?? null) : null)), __("Tag"),             // line 142
($context["field_options"] ?? null)]);
            // line 143
            yield "

                        ";
            // line 145
            yield $macros["fields"]->getTemplateForMacro("macro_htmlField", $context, 145, $this->getSourceContext())->macro_htmlField(...["useragent", $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v15 = CoreExtension::getAttribute($this->env, $this->source,             // line 147
($context["item"] ?? null), "fields", [], "any", false, false, false, 147)) && is_array($_v15) || $_v15 instanceof ArrayAccess ? ($_v15["useragent"] ?? null) : null)), __("Useragent"),             // line 149
($context["field_options"] ?? null)]);
            // line 150
            yield "

                        ";
            // line 152
            yield $macros["fields"]->getTemplateForMacro("macro_htmlField", $context, 152, $this->getSourceContext())->macro_htmlField(...["last_contact", $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getFormattedDatetime((($_v16 = CoreExtension::getAttribute($this->env, $this->source,             // line 154
($context["item"] ?? null), "fields", [], "any", false, false, false, 154)) && is_array($_v16) || $_v16 instanceof ArrayAccess ? ($_v16["last_contact"] ?? null) : null))), __("Last contact"),             // line 156
($context["field_options"] ?? null)]);
            // line 157
            yield "

                        <div class=\"hr-text\">
                            <i class=\"ti ti-adjustments\"></i>
                            <span>";
            // line 161
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Agent configuration"), "html", null, true);
            yield "</span>
                        </div>

                        ";
            // line 164
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(["threads_networkdiscovery" => __("Network discovery threads"), "timeout_networkdiscovery" => __("Network discovery timeout"), "threads_networkinventory" => __("Network inventory threads"), "timeout_networkinventory" => __("Network inventory timeout")]);
            foreach ($context['_seq'] as $context["netfield"] => $context["netlabel"]) {
                // line 170
                yield "                            ";
                $context["general"] = __("General setup");
                // line 171
                yield "                            ";
                if (($this->extensions['Glpi\Application\View\Extension\ConfigExtension']->config($context["netfield"]) != null)) {
                    // line 172
                    yield "                                ";
                    $context["general"] = Twig\Extension\CoreExtension::sprintf("%1\$s (%2\$s)", ($context["general"] ?? null), $this->extensions['Glpi\Application\View\Extension\ConfigExtension']->config($context["netfield"]));
                    // line 173
                    yield "                            ";
                }
                // line 174
                yield "
                            ";
                // line 175
                yield $macros["fields"]->getTemplateForMacro("macro_dropdownNumberField", $context, 175, $this->getSourceContext())->macro_dropdownNumberField(...[                // line 176
$context["netfield"], (($_v17 = CoreExtension::getAttribute($this->env, $this->source,                 // line 177
($context["item"] ?? null), "fields", [], "any", false, false, false, 177)) && is_array($_v17) || $_v17 instanceof ArrayAccess ? ($_v17[(($_v18 = $context["netfield"]) instanceof \Stringable ? (string) $_v18 : $_v18)] ?? null) : null),                 // line 178
$context["netlabel"], Twig\Extension\CoreExtension::merge(                // line 179
($context["field_options"] ?? null), ["min" => 1, "toadd" => [                // line 182
($context["general"] ?? null)]])]);
                // line 185
                yield "
                        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['netfield'], $context['netlabel'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 187
            yield "
                        <div class=\"hr-text\">
                            <i class=\"ti ti-list\"></i>
                            <span>";
            // line 190
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Supported tasks"), "html", null, true);
            yield " </span>
                            <span class=\"form-help\" data-bs-toggle=\"tooltip\" data-bs-placement=\"top\" data-bs-html=\"true\"
                                  data-bs-title=\"";
            // line 192
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("When reported by GLPI agent via native inventory"), "html", null, true);
            yield "\">?</span>
                        </div>

                        ";
            // line 195
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(["use_module_wake_on_lan" => __("Wake on LAN"), "use_module_computer_inventory" => __("Computer inventory"), "use_module_esx_remote_inventory" => __("ESX remote inventory"), "use_module_network_inventory" => __("Network inventory (SNMP)"), "use_module_network_discovery" => __("Network discovery (SNMP)"), "use_module_package_deployment" => __("Package Deployment"), "use_module_collect_data" => __("Collect data"), "use_module_remote_inventory" => __("Remote inventory")]);
            foreach ($context['_seq'] as $context["modulefield"] => $context["modulelabel"]) {
                // line 205
                yield "
                        ";
                // line 206
                if ((($tmp = (($_v19 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 206)) && is_array($_v19) || $_v19 instanceof ArrayAccess ? ($_v19[(($_v20 = $context["modulefield"]) instanceof \Stringable ? (string) $_v20 : $_v20)] ?? null) : null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 207
                    yield "                            ";
                    $context["html"] = "<i class=\"ti ti-check green\"></i>";
                    // line 208
                    yield "                        ";
                } else {
                    // line 209
                    yield "                            ";
                    $context["html"] = "<i class=\"ti ti-x red\"></i>";
                    // line 210
                    yield "                        ";
                }
                // line 211
                yield "
                        ";
                // line 212
                yield $macros["fields"]->getTemplateForMacro("macro_htmlField", $context, 212, $this->getSourceContext())->macro_htmlField(...["", ($context["html"] ?? null), $context["modulelabel"]]);
                yield "
                        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['modulefield'], $context['modulelabel'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 214
            yield "                    ";
        }
        // line 215
        yield "                </div> ";
        // line 216
        yield "            </div> ";
        // line 217
        yield "         </div> ";
        // line 218
        yield "      </div>
   </div> ";
        // line 220
        yield "
   ";
        // line 221
        if (( !array_key_exists("no_form_buttons", $context) || (($context["no_form_buttons"] ?? null) == false))) {
            // line 222
            yield "      ";
            yield Twig\Extension\CoreExtension::include($this->env, $context, "components/form/buttons.html.twig");
            yield "
   ";
        }
        // line 224
        yield "
   ";
        // line 225
        if ((($tmp = (((CoreExtension::getAttribute($this->env, $this->source, ($context["params"] ?? null), "formfooter", [], "array", true, true, false, 225) &&  !(null === (($_v21 = ($context["params"] ?? null)) && is_array($_v21) || $_v21 instanceof ArrayAccess ? ($_v21["formfooter"] ?? null) : null)))) ? ((($_v22 = ($context["params"] ?? null)) && is_array($_v22) || $_v22 instanceof ArrayAccess ? ($_v22["formfooter"] ?? null) : null)) : (true))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 226
            yield "      <div class=\"card-footer mx-n2 mb-n2 mt-4\">
         ";
            // line 227
            yield Twig\Extension\CoreExtension::include($this->env, $context, "components/form/dates.html.twig");
            yield "
      </div>
   ";
        }
        // line 230
        yield "</div>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "pages/admin/inventory/agent.html.twig";
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
        return array (  374 => 230,  368 => 227,  365 => 226,  363 => 225,  360 => 224,  354 => 222,  352 => 221,  349 => 220,  346 => 218,  344 => 217,  342 => 216,  340 => 215,  337 => 214,  329 => 212,  326 => 211,  323 => 210,  320 => 209,  317 => 208,  314 => 207,  312 => 206,  309 => 205,  305 => 195,  299 => 192,  294 => 190,  289 => 187,  282 => 185,  280 => 182,  279 => 179,  278 => 178,  277 => 177,  276 => 176,  275 => 175,  272 => 174,  269 => 173,  266 => 172,  263 => 171,  260 => 170,  256 => 164,  250 => 161,  244 => 157,  242 => 156,  241 => 154,  240 => 152,  236 => 150,  234 => 149,  233 => 147,  232 => 145,  228 => 143,  226 => 142,  225 => 140,  224 => 138,  220 => 136,  218 => 135,  217 => 133,  215 => 131,  211 => 130,  205 => 128,  203 => 127,  200 => 126,  189 => 124,  185 => 123,  182 => 122,  179 => 121,  176 => 120,  174 => 119,  169 => 117,  166 => 116,  164 => 115,  163 => 113,  161 => 111,  158 => 110,  156 => 109,  152 => 107,  150 => 106,  149 => 104,  148 => 102,  144 => 100,  142 => 99,  141 => 97,  140 => 95,  136 => 93,  134 => 92,  133 => 90,  132 => 88,  128 => 86,  126 => 85,  125 => 83,  124 => 81,  120 => 79,  118 => 78,  117 => 76,  116 => 74,  112 => 72,  110 => 71,  109 => 69,  108 => 67,  104 => 65,  102 => 64,  101 => 62,  100 => 60,  91 => 53,  89 => 51,  88 => 50,  86 => 49,  83 => 48,  80 => 47,  77 => 46,  74 => 45,  72 => 44,  67 => 42,  63 => 41,  60 => 40,  56 => 38,  54 => 37,  52 => 36,  50 => 35,  47 => 34,  45 => 33,  42 => 32,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "pages/admin/inventory/agent.html.twig", "/var/www/html/glpi/templates/pages/admin/inventory/agent.html.twig");
    }
}
