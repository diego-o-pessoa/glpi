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

/* components/datatable.html.twig */
class __TwigTemplate_16820aa972586e4b5bd967e5864762a6 extends Template
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
        // line 86
        yield "
";
        // line 87
        $macros["alerts"] = $this->macros["alerts"] = $this->load("components/alerts_macros.html.twig", 87)->unwrap();
        // line 88
        yield "
";
        // line 89
        $context["datatable_id"] = ((array_key_exists("datatable_id", $context)) ? (Twig\Extension\CoreExtension::default(($context["datatable_id"] ?? null), ("datatable" . Twig\Extension\CoreExtension::random($this->env->getCharset())))) : (("datatable" . Twig\Extension\CoreExtension::random($this->env->getCharset()))));
        // line 90
        $context["filters"] = ((array_key_exists("filters", $context)) ? (Twig\Extension\CoreExtension::default(($context["filters"] ?? null), [])) : ([]));
        // line 91
        $context["additional_params"] = ((array_key_exists("additional_params", $context)) ? (Twig\Extension\CoreExtension::default(($context["additional_params"] ?? null), "")) : (""));
        // line 92
        $context["sort"] = ((array_key_exists("sort", $context)) ? (Twig\Extension\CoreExtension::default(($context["sort"] ?? null), null)) : (null));
        // line 93
        $context["nosort"] = ((array_key_exists("nosort", $context)) ? (Twig\Extension\CoreExtension::default(($context["nosort"] ?? null), false)) : (false));
        // line 94
        $context["order"] = ((array_key_exists("order", $context)) ? (Twig\Extension\CoreExtension::default(($context["order"] ?? null), "ASC")) : ("ASC"));
        // line 95
        $context["csv_url"] = ((array_key_exists("csv_url", $context)) ? (Twig\Extension\CoreExtension::default(($context["csv_url"] ?? null), "")) : (""));
        // line 96
        $context["footers"] = ((array_key_exists("footers", $context)) ? (Twig\Extension\CoreExtension::default(($context["footers"] ?? null), [])) : ([]));
        // line 97
        $context["showmassiveactions"] = ((array_key_exists("showmassiveactions", $context)) ? (Twig\Extension\CoreExtension::default(($context["showmassiveactions"] ?? null), false)) : (false));
        // line 98
        yield "
";
        // line 99
        $context["use_pager"] = ((array_key_exists("use_pager", $context)) ? (Twig\Extension\CoreExtension::default(($context["use_pager"] ?? null), ((array_key_exists("start", $context) && array_key_exists("limit", $context)) && array_key_exists("filtered_number", $context)))) : (((array_key_exists("start", $context) && array_key_exists("limit", $context)) && array_key_exists("filtered_number", $context))));
        // line 100
        yield "
";
        // line 102
        $context["use_pager"] = ((array_key_exists("nopager", $context)) ? ( !($context["nopager"] ?? null)) : (($context["use_pager"] ?? null)));
        // line 103
        yield "
";
        // line 104
        if (((($context["total_number"] ?? null) < 1) && (Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["filters"] ?? null)) == 0))) {
            // line 105
            yield "    <table id=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["datatable_id"] ?? null), "html", null, true);
            yield "\" class=\"table\">
        <thead>
        ";
            // line 107
            if ((array_key_exists("super_header", $context) &&  !Twig\Extension\CoreExtension::testEmpty(($context["super_header"] ?? null)))) {
                // line 108
                yield "            ";
                $context["super_header_label"] = ((is_array(($context["super_header"] ?? null))) ? ((($_v0 = ($context["super_header"] ?? null)) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0["label"] ?? null) : null)) : (($context["super_header"] ?? null)));
                // line 109
                yield "            ";
                if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(($context["super_header_label"] ?? null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 110
                    yield "                ";
                    $context["super_header_raw"] = ((is_array(($context["super_header"] ?? null))) ? ((((CoreExtension::getAttribute($this->env, $this->source, ($context["super_header"] ?? null), "is_raw", [], "array", true, true, false, 110) &&  !(null === (($_v1 = ($context["super_header"] ?? null)) && is_array($_v1) || $_v1 instanceof ArrayAccess ? ($_v1["is_raw"] ?? null) : null)))) ? ((($_v2 = ($context["super_header"] ?? null)) && is_array($_v2) || $_v2 instanceof ArrayAccess ? ($_v2["is_raw"] ?? null) : null)) : (false))) : (false));
                    // line 111
                    yield "                <tr>
                    <th colspan=\"1\">
                        ";
                    // line 113
                    yield (((($tmp = ($context["super_header_raw"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (($context["super_header_label"] ?? null)) : ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["super_header_label"] ?? null), "html", null, true)));
                    yield "
                    </th>
                </tr>
            ";
                }
                // line 117
                yield "        ";
            }
            // line 118
            yield "        </thead>
        <tbody>
            <tr>
                <td>
                    <div class=\"alert alert-info\">
                        ";
            // line 123
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("No results found"), "html", null, true);
            yield "
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
";
        } else {
            // line 130
            yield "    ";
            $context["total_cols"] = ((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["columns"] ?? null)) + (((($tmp = ($context["showmassiveactions"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (1) : (0))) + (((($tmp = (((array_key_exists("nofilter", $context) &&  !(null === $context["nofilter"]))) ? ($context["nofilter"]) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (0) : (1)));
            // line 131
            yield "    ";
            if ((($tmp = ($context["use_pager"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 132
                yield "        ";
                yield Twig\Extension\CoreExtension::include($this->env, $context, "components/pager.html.twig", ["count" =>                 // line 133
($context["filtered_number"] ?? null), "additional_params" => ((((                // line 134
($context["additional_params"] ?? null) . "&sort=") . ($context["sort"] ?? null)) . "&order=") . ($context["order"] ?? null))]);
                // line 135
                yield "
    ";
            }
            // line 137
            yield "
    <div class=\"table-responsive ";
            // line 138
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("container_class", $context)) ? (Twig\Extension\CoreExtension::default(($context["container_class"] ?? null), "")) : ("")), "html", null, true);
            yield "\" ";
            if ((($tmp = ($context["showmassiveactions"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                yield " id=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v3 = ($context["massiveactionparams"] ?? null)) && is_array($_v3) || $_v3 instanceof ArrayAccess ? ($_v3["container"] ?? null) : null), "html", null, true);
                yield "\" ";
            }
            yield ">
        ";
            // line 139
            if ((($tmp = ($context["showmassiveactions"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 140
                yield "            <div class=\"mb-2\">
                ";
                // line 141
                $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Html::showMassiveActions", [Twig\Extension\CoreExtension::merge(["action_button_classes" => "btn btn-sm btn-outline-secondary me-2"], ((array_key_exists("massiveactionparams", $context)) ? (Twig\Extension\CoreExtension::default(($context["massiveactionparams"] ?? null), [])) : ([])))]);
                // line 142
                yield "            </div>
        ";
            }
            // line 144
            yield "        <table id=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["datatable_id"] ?? null), "html", null, true);
            yield "\" class=\"table ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("table_class_style", $context)) ? (Twig\Extension\CoreExtension::default(($context["table_class_style"] ?? null), "table-hover")) : ("table-hover")), "html", null, true);
            yield "\" aria-label=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("datatable_aria_label", $context)) ? (Twig\Extension\CoreExtension::default(($context["datatable_aria_label"] ?? null), "")) : ("")), "html", null, true);
            yield "\">
            <thead>
                ";
            // line 146
            if ((array_key_exists("super_header", $context) &&  !Twig\Extension\CoreExtension::testEmpty(($context["super_header"] ?? null)))) {
                // line 147
                yield "                    ";
                $context["super_header_label"] = ((is_array(($context["super_header"] ?? null))) ? ((($_v4 = ($context["super_header"] ?? null)) && is_array($_v4) || $_v4 instanceof ArrayAccess ? ($_v4["label"] ?? null) : null)) : (($context["super_header"] ?? null)));
                // line 148
                yield "                    ";
                $context["super_header_raw"] = ((is_array(($context["super_header"] ?? null))) ? ((((CoreExtension::getAttribute($this->env, $this->source, ($context["super_header"] ?? null), "is_raw", [], "array", true, true, false, 148) &&  !(null === (($_v5 = ($context["super_header"] ?? null)) && is_array($_v5) || $_v5 instanceof ArrayAccess ? ($_v5["is_raw"] ?? null) : null)))) ? ((($_v6 = ($context["super_header"] ?? null)) && is_array($_v6) || $_v6 instanceof ArrayAccess ? ($_v6["is_raw"] ?? null) : null)) : (false))) : (false));
                // line 149
                yield "                    <tr>
                        ";
                // line 150
                if ((($tmp =  !(($context["super_header_raw"] ?? null) === "th_elements")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    yield "<th colspan=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["total_cols"] ?? null), "html", null, true);
                    yield "\">";
                }
                // line 151
                yield "                            ";
                yield (((($tmp = ($context["super_header_raw"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (($context["super_header_label"] ?? null)) : ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["super_header_label"] ?? null), "html", null, true)));
                yield "
                        ";
                // line 152
                if ((($tmp =  !(($context["super_header_raw"] ?? null) === "th_elements")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    yield "</th>";
                }
                // line 153
                yield "                    </tr>
                ";
            }
            // line 155
            yield "                ";
            if (( !array_key_exists("no_header", $context) || (($context["no_header"] ?? null) == false))) {
                // line 156
                yield "                    <tr>
                        ";
                // line 157
                if ((($tmp = ($context["showmassiveactions"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 158
                    yield "                            <th scope=\"col\" style=\"width: 30px;\">
                                <div>
                                    <input class=\"form-check-input massive_action_checkbox\" type=\"checkbox\" id=\"checkall_";
                    // line 160
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v7 = ($context["massiveactionparams"] ?? null)) && is_array($_v7) || $_v7 instanceof ArrayAccess ? ($_v7["container"] ?? null) : null), "html", null, true);
                    yield "\"
                                        value=\"\" aria-label=\"";
                    // line 161
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Check all"), "html", null, true);
                    yield "\"
                                        onclick=\"checkAsCheckboxes(this, \x27";
                    // line 162
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v8 = ($context["massiveactionparams"] ?? null)) && is_array($_v8) || $_v8 instanceof ArrayAccess ? ($_v8["container"] ?? null) : null), "js"), "html", null, true);
                    yield "\x27, \x27.massive_action_checkbox\x27);\" />
                                </div>
                            </th>
                        ";
                }
                // line 166
                yield "                        ";
                $context['_parent'] = $context;
                $context['_seq'] = CoreExtension::ensureTraversable(($context["columns"] ?? null));
                foreach ($context['_seq'] as $context["colkey"] => $context["column"]) {
                    // line 167
                    yield "                            ";
                    $context["column_label"] = ((is_array($context["column"])) ? ((($_v9 = $context["column"]) && is_array($_v9) || $_v9 instanceof ArrayAccess ? ($_v9["label"] ?? null) : null)) : ($context["column"]));
                    // line 168
                    yield "                            ";
                    $context["raw_header"] = ((is_array($context["column"])) ? (((CoreExtension::getAttribute($this->env, $this->source, $context["column"], "raw_header", [], "array", true, true, false, 168)) ? (Twig\Extension\CoreExtension::default((($_v10 = $context["column"]) && is_array($_v10) || $_v10 instanceof ArrayAccess ? ($_v10["raw_header"] ?? null) : null), false)) : (false))) : (false));
                    // line 169
                    yield "                            ";
                    $context["sort_icon"] = "";
                    // line 170
                    yield "                            ";
                    $context["new_order"] = "DESC";
                    // line 171
                    yield "                            ";
                    if ((($context["sort"] ?? null) == $context["colkey"])) {
                        // line 172
                        yield "                                ";
                        $context["sort_icon"] = (((($context["order"] ?? null) == "ASC")) ? ("ti ti-sort-ascending") : ((((($context["order"] ?? null) == "DESC")) ? ("ti ti-sort-descending") : (""))));
                        // line 173
                        yield "                                ";
                        $context["new_order"] = (((($context["order"] ?? null) == "ASC")) ? ("DESC") : ("ASC"));
                        // line 174
                        yield "                            ";
                    }
                    // line 175
                    yield "
                            ";
                    // line 176
                    $context["sort_href"] = (((((("javascript:reloadTab(\x27sort=" . $context["colkey"]) . "&order=") . ($context["new_order"] ?? null)) . "&") . ($context["additional_params"] ?? null)) . "\x27);");
                    // line 177
                    yield "
                            <th scope=\"col\">
                                ";
                    // line 179
                    if (( !($context["nosort"] ?? null) &&  !(is_array($context["column"]) && CoreExtension::getAttribute($this->env, $this->source, $context["column"], "nosort", [], "array", true, true, false, 179)))) {
                        // line 180
                        yield "                                    <a href=\"";
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["sort_href"] ?? null), "html", null, true);
                        yield "\">
                                    <i class=\"";
                        // line 181
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["sort_icon"] ?? null), "html", null, true);
                        yield "\"></i>
                                ";
                    }
                    // line 183
                    yield "                                <span>";
                    yield (((($tmp = ($context["raw_header"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (($context["column_label"] ?? null)) : ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["column_label"] ?? null), "html", null, true)));
                    yield "</span>
                                ";
                    // line 184
                    if (( !($context["nosort"] ?? null) &&  !(is_array($context["column"]) && CoreExtension::getAttribute($this->env, $this->source, $context["column"], "nosort", [], "array", true, true, false, 184)))) {
                        // line 185
                        yield "                                    </a>
                                ";
                    }
                    // line 187
                    yield "                            </th>
                        ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['colkey'], $context['column'], $context['_parent']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 189
                yield "
                       ";
                // line 190
                if (( !array_key_exists("nofilter", $context) || Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["csv_url"] ?? null)))) {
                    // line 191
                    yield "                           <th scope=\"col\">
                               <span class=\"float-end log-toolbar mb-0\">
                                   ";
                    // line 193
                    if ((($tmp =  !array_key_exists("nofilter", $context)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                        // line 194
                        yield "                                       <button class=\"btn btn-sm show_filters ";
                        yield (((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["filters"] ?? null)) > 0)) ? ("btn-secondary active") : ("btn-outline-secondary"));
                        yield "\">
                                           <i class=\"ti ti-filter\"></i>
                                           <span class=\"d-none d-xl-block\">";
                        // line 196
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Filter"), "html", null, true);
                        yield "</span>
                                       </button>
                                   ";
                    }
                    // line 199
                    yield "                                   ";
                    if ((($tmp = Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["csv_url"] ?? null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                        // line 200
                        yield "                                       <a href=\"";
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["csv_url"] ?? null), "html", null, true);
                        yield "\" class=\"btn btn-sm text-capitalize btn-outline-secondary\">
                                           <i class=\"ti ti-download\"></i>
                                           <span class=\"d-none d-xl-block\">";
                        // line 202
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Export"), "html", null, true);
                        yield "</span>
                                       </a>
                                   ";
                    }
                    // line 205
                    yield "                               </span>
                           </th>
                         ";
                }
                // line 208
                yield "                    </tr>
                ";
            }
            // line 210
            yield "                ";
            if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["filters"] ?? null)) > 0)) {
                // line 211
                yield "                    <tr class=\"filter_row\">
                        ";
                // line 212
                if ((($tmp = ($context["showmassiveactions"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 213
                    yield "                            <td></td>
                        ";
                }
                // line 215
                yield "                        <td style=\"display: none\">
                            <input type=\"hidden\" name=\"filters[active]\" value=\"1\" />
                            <input type=\"hidden\" name=\"items_id\" value=\"";
                // line 217
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["items_id"] ?? null), "html", null, true);
                yield "\" />
                        </td>
                        ";
                // line 219
                $context['_parent'] = $context;
                $context['_seq'] = CoreExtension::ensureTraversable(($context["columns"] ?? null));
                foreach ($context['_seq'] as $context["colkey"] => $context["colum"]) {
                    // line 220
                    yield "                            ";
                    $context["formatter"] = Twig\Extension\CoreExtension::default(((CoreExtension::getAttribute($this->env, $this->source, $context["colum"], "filter_formatter", [], "array", true, true, false, 220)) ? (Twig\Extension\CoreExtension::default((($_v11 = $context["colum"]) && is_array($_v11) || $_v11 instanceof ArrayAccess ? ($_v11["filter_formatter"] ?? null) : null), ((CoreExtension::getAttribute($this->env, $this->source, ($context["formatters"] ?? null), $context["colkey"], [], "array", true, true, false, 220)) ? (Twig\Extension\CoreExtension::default((($_v12 = ($context["formatters"] ?? null)) && is_array($_v12) || $_v12 instanceof ArrayAccess ? ($_v12[(($_v13 = $context["colkey"]) instanceof \Stringable ? (string) $_v13 : $_v13)] ?? null) : null), "")) : ("")))) : (((CoreExtension::getAttribute($this->env, $this->source, ($context["formatters"] ?? null), $context["colkey"], [], "array", true, true, false, 220)) ? (Twig\Extension\CoreExtension::default((($_v14 = ($context["formatters"] ?? null)) && is_array($_v14) || $_v14 instanceof ArrayAccess ? ($_v14[(($_v15 = $context["colkey"]) instanceof \Stringable ? (string) $_v15 : $_v15)] ?? null) : null), "")) : ("")))), "");
                    // line 221
                    yield "                            <td>
                                ";
                    // line 222
                    if (( !is_array($context["colum"]) || (((CoreExtension::getAttribute($this->env, $this->source, $context["colum"], "no_filter", [], "array", true, true, false, 222)) ? (Twig\Extension\CoreExtension::default((($_v16 = $context["colum"]) && is_array($_v16) || $_v16 instanceof ArrayAccess ? ($_v16["no_filter"] ?? null) : null), false)) : (false)) == false))) {
                        // line 223
                        yield "                                    ";
                        if (((($context["formatter"] ?? null) == "array") && CoreExtension::getAttribute($this->env, $this->source, ($context["columns_values"] ?? null), $context["colkey"], [], "array", true, true, false, 223))) {
                            // line 224
                            yield "                                           <select name=\"filters[";
                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["colkey"], "html", null, true);
                            yield "][]\"
                                                class=\"form-select filter-select-multiple\" multiple>
                                            ";
                            // line 226
                            $context['_parent'] = $context;
                            $context['_seq'] = CoreExtension::ensureTraversable((($_v17 = ($context["columns_values"] ?? null)) && is_array($_v17) || $_v17 instanceof ArrayAccess ? ($_v17[(($_v18 = $context["colkey"]) instanceof \Stringable ? (string) $_v18 : $_v18)] ?? null) : null));
                            foreach ($context['_seq'] as $context["field"] => $context["value"]) {
                                // line 227
                                yield "                                                <option value=\"";
                                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["field"], "html", null, true);
                                yield "\" ";
                                yield (((CoreExtension::getAttribute($this->env, $this->source, ($context["filters"] ?? null), $context["colkey"], [], "array", true, true, false, 227) && CoreExtension::inFilter($context["field"], (($_v19 = ($context["filters"] ?? null)) && is_array($_v19) || $_v19 instanceof ArrayAccess ? ($_v19[(($_v20 = $context["colkey"]) instanceof \Stringable ? (string) $_v20 : $_v20)] ?? null) : null)))) ? ("selected") : (""));
                                yield ">
                                                    ";
                                // line 228
                                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["value"], "html", null, true);
                                yield "
                                                </option>
                                            ";
                            }
                            $_parent = $context['_parent'];
                            unset($context['_seq'], $context['field'], $context['value'], $context['_parent']);
                            $context = array_intersect_key($context, $_parent) + $_parent;
                            // line 231
                            yield "                                        </select>
                                    ";
                        } elseif ((                        // line 232
($context["formatter"] ?? null) == "datetime")) {
                            // line 233
                            yield "                                        ";
                            yield $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Html::showDateTimeField", [(("filters[" .                             // line 234
$context["colkey"]) . "]"), ["value" => (((CoreExtension::getAttribute($this->env, $this->source,                             // line 236
($context["filters"] ?? null), $context["colkey"], [], "array", true, true, false, 236) &&  !(null === (($_v21 = ($context["filters"] ?? null)) && is_array($_v21) || $_v21 instanceof ArrayAccess ? ($_v21[(($_v22 = $context["colkey"]) instanceof \Stringable ? (string) $_v22 : $_v22)] ?? null) : null)))) ? ((($_v23 = ($context["filters"] ?? null)) && is_array($_v23) || $_v23 instanceof ArrayAccess ? ($_v23[(($_v24 = $context["colkey"]) instanceof \Stringable ? (string) $_v24 : $_v24)] ?? null) : null)) : ("")), "display" => false]]);
                            // line 239
                            yield "
                                    ";
                        } elseif ((                        // line 240
($context["formatter"] ?? null) == "date")) {
                            // line 241
                            yield "                                        ";
                            yield $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Html::showDateField", [(("filters[" .                             // line 242
$context["colkey"]) . "]"), ["value" => (((CoreExtension::getAttribute($this->env, $this->source,                             // line 244
($context["filters"] ?? null), $context["colkey"], [], "array", true, true, false, 244) &&  !(null === (($_v25 = ($context["filters"] ?? null)) && is_array($_v25) || $_v25 instanceof ArrayAccess ? ($_v25[(($_v26 = $context["colkey"]) instanceof \Stringable ? (string) $_v26 : $_v26)] ?? null) : null)))) ? ((($_v27 = ($context["filters"] ?? null)) && is_array($_v27) || $_v27 instanceof ArrayAccess ? ($_v27[(($_v28 = $context["colkey"]) instanceof \Stringable ? (string) $_v28 : $_v28)] ?? null) : null)) : ("")), "display" => false]]);
                            // line 247
                            yield "
                                    ";
                        } elseif ((is_string($_v29 =                         // line 248
($context["formatter"] ?? null)) && is_string($_v30 = "progress") && str_starts_with($_v29, $_v30))) {
                            // line 249
                            yield "                                        <input type=\"range\" class=\"form-range\"
                                            name=\"filters[";
                            // line 250
                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["colkey"], "html", null, true);
                            yield "]\"
                                            value=\"";
                            // line 251
                            yield (((CoreExtension::getAttribute($this->env, $this->source, ($context["filters"] ?? null), $context["colkey"], [], "array", true, true, false, 251) &&  !(null === (($_v31 = ($context["filters"] ?? null)) && is_array($_v31) || $_v31 instanceof ArrayAccess ? ($_v31[(($_v32 = $context["colkey"]) instanceof \Stringable ? (string) $_v32 : $_v32)] ?? null) : null)))) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v33 = ($context["filters"] ?? null)) && is_array($_v33) || $_v33 instanceof ArrayAccess ? ($_v33[(($_v34 = $context["colkey"]) instanceof \Stringable ? (string) $_v34 : $_v34)] ?? null) : null), "html", null, true)) : (0));
                            yield "\"
                                            min=\"0\" max=\"100\" step=\"1\">
                                    ";
                        } elseif ((                        // line 253
($context["formatter"] ?? null) == "avatar")) {
                            // line 254
                            yield "                                        ";
                            // line 255
                            yield "                                    ";
                        } elseif ((($context["formatter"] ?? null) == "yesno")) {
                            // line 256
                            yield "                                        <select name=\"filters[";
                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["colkey"], "html", null, true);
                            yield "]\" class=\"form-select\">
                                            <option value=\"\">";
                            // line 257
                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("All"), "html", null, true);
                            yield "</option>
                                            <option value=\"1\" ";
                            // line 258
                            yield ((((((CoreExtension::getAttribute($this->env, $this->source, ($context["filters"] ?? null), $context["colkey"], [], "array", true, true, false, 258) &&  !(null === (($_v35 = ($context["filters"] ?? null)) && is_array($_v35) || $_v35 instanceof ArrayAccess ? ($_v35[(($_v36 = $context["colkey"]) instanceof \Stringable ? (string) $_v36 : $_v36)] ?? null) : null)))) ? ((($_v37 = ($context["filters"] ?? null)) && is_array($_v37) || $_v37 instanceof ArrayAccess ? ($_v37[(($_v38 = $context["colkey"]) instanceof \Stringable ? (string) $_v38 : $_v38)] ?? null) : null)) : ("")) == "1")) ? ("selected") : (""));
                            yield ">
                                                ";
                            // line 259
                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Yes"), "html", null, true);
                            yield "
                                            </option>
                                            <option value=\"0\" ";
                            // line 261
                            yield ((((((CoreExtension::getAttribute($this->env, $this->source, ($context["filters"] ?? null), $context["colkey"], [], "array", true, true, false, 261) &&  !(null === (($_v39 = ($context["filters"] ?? null)) && is_array($_v39) || $_v39 instanceof ArrayAccess ? ($_v39[(($_v40 = $context["colkey"]) instanceof \Stringable ? (string) $_v40 : $_v40)] ?? null) : null)))) ? ((($_v41 = ($context["filters"] ?? null)) && is_array($_v41) || $_v41 instanceof ArrayAccess ? ($_v41[(($_v42 = $context["colkey"]) instanceof \Stringable ? (string) $_v42 : $_v42)] ?? null) : null)) : ("")) == "0")) ? ("selected") : (""));
                            yield ">
                                                ";
                            // line 262
                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("No"), "html", null, true);
                            yield "
                                            </option>
                                        </select>
                                    ";
                        } else {
                            // line 266
                            yield "                                        <input type=\"text\" class=\"form-control\"
                                            name=\"filters[";
                            // line 267
                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["colkey"], "html", null, true);
                            yield "]\"
                                            value=\"";
                            // line 268
                            yield (((CoreExtension::getAttribute($this->env, $this->source, ($context["filters"] ?? null), $context["colkey"], [], "array", true, true, false, 268) &&  !(null === (($_v43 = ($context["filters"] ?? null)) && is_array($_v43) || $_v43 instanceof ArrayAccess ? ($_v43[(($_v44 = $context["colkey"]) instanceof \Stringable ? (string) $_v44 : $_v44)] ?? null) : null)))) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v45 = ($context["filters"] ?? null)) && is_array($_v45) || $_v45 instanceof ArrayAccess ? ($_v45[(($_v46 = $context["colkey"]) instanceof \Stringable ? (string) $_v46 : $_v46)] ?? null) : null), "html", null, true)) : (""));
                            yield "\">
                                    ";
                        }
                        // line 270
                        yield "                                ";
                    }
                    // line 271
                    yield "                            </td>
                        ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['colkey'], $context['colum'], $context['_parent']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 273
                yield "                    </tr>
                ";
            }
            // line 275
            yield "            </thead>
            <tbody>
                ";
            // line 277
            if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["entries"] ?? null)) > 0)) {
                // line 278
                yield "                    ";
                $context['_parent'] = $context;
                $context['_seq'] = CoreExtension::ensureTraversable(($context["entries"] ?? null));
                $context['loop'] = [
                  'parent' => $context['_parent'],
                  'index0' => 0,
                  'index'  => 1,
                  'first'  => true,
                ];
                if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof \Countable)) {
                    $length = count($context['_seq']);
                    $context['loop']['revindex0'] = $length - 1;
                    $context['loop']['revindex'] = $length;
                    $context['loop']['length'] = $length;
                    $context['loop']['last'] = 1 === $length;
                }
                foreach ($context['_seq'] as $context["_key"] => $context["entry"]) {
                    // line 279
                    yield "                        ";
                    $context["row_massiveactions"] = ((CoreExtension::getAttribute($this->env, $this->source, $context["entry"], "showmassiveactions", [], "array", true, true, false, 279)) ? (Twig\Extension\CoreExtension::default((($_v47 = $context["entry"]) && is_array($_v47) || $_v47 instanceof ArrayAccess ? ($_v47["showmassiveactions"] ?? null) : null), ($context["showmassiveactions"] ?? null))) : (($context["showmassiveactions"] ?? null)));
                    // line 280
                    yield "                        <tr
                            class=\"";
                    // line 281
                    yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["loop"], "last", [], "any", false, false, false, 281)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("border-transparent") : (""));
                    yield " ";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("row_class", $context)) ? (Twig\Extension\CoreExtension::default(($context["row_class"] ?? null), "")) : ("")), "html", null, true);
                    yield " ";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((CoreExtension::getAttribute($this->env, $this->source, $context["entry"], "row_class", [], "array", true, true, false, 281)) ? (Twig\Extension\CoreExtension::default((($_v48 = $context["entry"]) && is_array($_v48) || $_v48 instanceof ArrayAccess ? ($_v48["row_class"] ?? null) : null), "")) : ("")), "html", null, true);
                    yield "\"
                            ";
                    // line 282
                    if (CoreExtension::getAttribute($this->env, $this->source, $context["entry"], "itemtype", [], "array", true, true, false, 282)) {
                        // line 283
                        yield "                                data-itemtype=\"";
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v49 = $context["entry"]) && is_array($_v49) || $_v49 instanceof ArrayAccess ? ($_v49["itemtype"] ?? null) : null), "html", null, true);
                        yield "\"
                            ";
                    }
                    // line 285
                    yield "                            ";
                    if (CoreExtension::getAttribute($this->env, $this->source, $context["entry"], "id", [], "array", true, true, false, 285)) {
                        // line 286
                        yield "                                data-id=\"";
                        yield (((CoreExtension::getAttribute($this->env, $this->source, $context["entry"], "id", [], "array", true, true, false, 286) &&  !(null === (($_v50 = $context["entry"]) && is_array($_v50) || $_v50 instanceof ArrayAccess ? ($_v50["id"] ?? null) : null)))) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v51 = $context["entry"]) && is_array($_v51) || $_v51 instanceof ArrayAccess ? ($_v51["id"] ?? null) : null), "html", null, true)) : (""));
                        yield "\"
                            ";
                    }
                    // line 288
                    yield "                            ";
                    if (CoreExtension::getAttribute($this->env, $this->source, $context["entry"], "row_aria_label", [], "array", true, true, false, 288)) {
                        // line 289
                        yield "                                aria-label=\"";
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v52 = $context["entry"]) && is_array($_v52) || $_v52 instanceof ArrayAccess ? ($_v52["row_aria_label"] ?? null) : null), "html", null, true);
                        yield "\"
                            ";
                    }
                    // line 291
                    yield "                        >
                            ";
                    // line 292
                    if ((($tmp = ($context["row_massiveactions"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                        // line 293
                        yield "                                <td style=\"width: 10px\">
                                    ";
                        // line 294
                        if (( !CoreExtension::getAttribute($this->env, $this->source, $context["entry"], "skip_ma", [], "array", true, true, false, 294) || ((($_v53 = $context["entry"]) && is_array($_v53) || $_v53 instanceof ArrayAccess ? ($_v53["skip_ma"] ?? null) : null) == false))) {
                            // line 295
                            yield "                                        <input class=\"form-check-input massive_action_checkbox\" type=\"checkbox\" data-glpicore-ma-tags=\"common\"
                                               value=\"1\" aria-label=\"";
                            // line 296
                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Select item"), "html", null, true);
                            yield "\"
                                               name=\"item[";
                            // line 297
                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v54 = $context["entry"]) && is_array($_v54) || $_v54 instanceof ArrayAccess ? ($_v54["itemtype"] ?? null) : null), "html", null, true);
                            yield "][";
                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v55 = $context["entry"]) && is_array($_v55) || $_v55 instanceof ArrayAccess ? ($_v55["id"] ?? null) : null), "html", null, true);
                            yield "]\" />
                                    ";
                        }
                        // line 299
                        yield "                                </td>
                            ";
                    }
                    // line 301
                    yield "                            ";
                    $context['_parent'] = $context;
                    $context['_seq'] = CoreExtension::ensureTraversable(($context["columns"] ?? null));
                    foreach ($context['_seq'] as $context["colkey"] => $context["colum"]) {
                        // line 302
                        yield "                                ";
                        if (CoreExtension::inFilter($context["colkey"], Twig\Extension\CoreExtension::keys($context["entry"]))) {
                            // line 303
                            yield "                                    ";
                            $context["colspan"] = ((CoreExtension::getAttribute($this->env, $this->source, $context["entry"], ($context["colkey"] . "_colspan"), [], "array", true, true, false, 303)) ? (Twig\Extension\CoreExtension::default((($_v56 = $context["entry"]) && is_array($_v56) || $_v56 instanceof ArrayAccess ? ($_v56[(($_v57 = ($context["colkey"] . "_colspan")) instanceof \Stringable ? (string) $_v57 : $_v57)] ?? null) : null), 1)) : (1));
                            // line 304
                            yield "                                    ";
                            $context["aria_label"] = ((CoreExtension::getAttribute($this->env, $this->source, $context["entry"], ($context["colkey"] . "_aria_label"), [], "array", true, true, false, 304)) ? (Twig\Extension\CoreExtension::default((($_v58 = $context["entry"]) && is_array($_v58) || $_v58 instanceof ArrayAccess ? ($_v58[(($_v59 = ($context["colkey"] . "_aria_label")) instanceof \Stringable ? (string) $_v59 : $_v59)] ?? null) : null), "")) : (""));
                            // line 305
                            yield "                                    <td colspan=\"";
                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["colspan"] ?? null), "html", null, true);
                            yield "\" aria-label=\"";
                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["aria_label"] ?? null), "html", null, true);
                            yield "\">

                                        ";
                            // line 307
                            $context["formatter"] = (((CoreExtension::getAttribute($this->env, $this->source, ($context["formatters"] ?? null), $context["colkey"], [], "array", true, true, false, 307) &&  !(null === (($_v60 = ($context["formatters"] ?? null)) && is_array($_v60) || $_v60 instanceof ArrayAccess ? ($_v60[(($_v61 = $context["colkey"]) instanceof \Stringable ? (string) $_v61 : $_v61)] ?? null) : null)))) ? ((($_v62 = ($context["formatters"] ?? null)) && is_array($_v62) || $_v62 instanceof ArrayAccess ? ($_v62[(($_v63 = $context["colkey"]) instanceof \Stringable ? (string) $_v63 : $_v63)] ?? null) : null)) : (""));
                            // line 308
                            yield "
                                        ";
                            // line 309
                            if ((($context["formatter"] ?? null) == "maintext")) {
                                // line 310
                                yield "                                            <span class=\"d-inline-block bg-blue-lt p-1 text-truncate\"
                                                title=\"";
                                // line 311
                                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v64 = $context["entry"]) && is_array($_v64) || $_v64 instanceof ArrayAccess ? ($_v64[(($_v65 = $context["colkey"]) instanceof \Stringable ? (string) $_v65 : $_v65)] ?? null) : null), "html", null, true);
                                yield "\"
                                                data-bs-toggle=\"tooltip\"
                                                style=\"max-width: 250px;\">
                                                ";
                                // line 314
                                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v66 = $context["entry"]) && is_array($_v66) || $_v66 instanceof ArrayAccess ? ($_v66[(($_v67 = $context["colkey"]) instanceof \Stringable ? (string) $_v67 : $_v67)] ?? null) : null), "html", null, true);
                                yield "
                                            </span>
                                        ";
                            } elseif ((                            // line 316
($context["formatter"] ?? null) == "longtext")) {
                                // line 317
                                yield "                                            <span class=\"d-inline-block text-truncate\"
                                                title=\"";
                                // line 318
                                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v68 = $context["entry"]) && is_array($_v68) || $_v68 instanceof ArrayAccess ? ($_v68[(($_v69 = $context["colkey"]) instanceof \Stringable ? (string) $_v69 : $_v69)] ?? null) : null), "html", null, true);
                                yield "\"
                                                data-bs-toggle=\"tooltip\"
                                                style=\"max-width: 250px;\">
                                                ";
                                // line 321
                                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v70 = $context["entry"]) && is_array($_v70) || $_v70 instanceof ArrayAccess ? ($_v70[(($_v71 = $context["colkey"]) instanceof \Stringable ? (string) $_v71 : $_v71)] ?? null) : null), "html", null, true);
                                yield "
                                            </span>
                                        ";
                            } elseif ((is_string($_v72 =                             // line 323
($context["formatter"] ?? null)) && is_string($_v73 = "progress") && str_starts_with($_v72, $_v73))) {
                                // line 324
                                yield "                                            ";
                                yield $this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getProgressBar((($_v74 = $context["entry"]) && is_array($_v74) || $_v74 instanceof ArrayAccess ? ($_v74[(($_v75 = $context["colkey"]) instanceof \Stringable ? (string) $_v75 : $_v75)] ?? null) : null));
                                yield "
                                        ";
                            } elseif ((                            // line 325
($context["formatter"] ?? null) == "date")) {
                                // line 326
                                yield "                                            ";
                                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getFormattedDate((($_v76 = $context["entry"]) && is_array($_v76) || $_v76 instanceof ArrayAccess ? ($_v76[(($_v77 = $context["colkey"]) instanceof \Stringable ? (string) $_v77 : $_v77)] ?? null) : null)), "html", null, true);
                                yield "
                                        ";
                            } elseif ((                            // line 327
($context["formatter"] ?? null) == "datetime")) {
                                // line 328
                                yield "                                            ";
                                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getFormattedDatetime((($_v78 = $context["entry"]) && is_array($_v78) || $_v78 instanceof ArrayAccess ? ($_v78[(($_v79 = $context["colkey"]) instanceof \Stringable ? (string) $_v79 : $_v79)] ?? null) : null)), "html", null, true);
                                yield "
                                        ";
                            } elseif ((                            // line 329
($context["formatter"] ?? null) == "duration")) {
                                // line 330
                                yield "                                            ";
                                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getFormattedDuration((($_v80 = $context["entry"]) && is_array($_v80) || $_v80 instanceof ArrayAccess ? ($_v80[(($_v81 = $context["colkey"]) instanceof \Stringable ? (string) $_v81 : $_v81)] ?? null) : null)), "html", null, true);
                                yield "
                                        ";
                            } elseif ((                            // line 331
($context["formatter"] ?? null) == "bytesize")) {
                                // line 332
                                yield "                                            ";
                                yield $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Toolbox::getSize", [(($_v82 = $context["entry"]) && is_array($_v82) || $_v82 instanceof ArrayAccess ? ($_v82[(($_v83 = $context["colkey"]) instanceof \Stringable ? (string) $_v83 : $_v83)] ?? null) : null)]);
                                yield "
                                        ";
                            } elseif ((                            // line 333
($context["formatter"] ?? null) == "number")) {
                                // line 334
                                yield "                                            ";
                                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getFormattedNumber((($_v84 = $context["entry"]) && is_array($_v84) || $_v84 instanceof ArrayAccess ? ($_v84[(($_v85 = $context["colkey"]) instanceof \Stringable ? (string) $_v85 : $_v85)] ?? null) : null)), "html", null, true);
                                yield "
                                        ";
                            } elseif ((                            // line 335
($context["formatter"] ?? null) == "integer")) {
                                // line 336
                                yield "                                            ";
                                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getFormattedInteger((($_v86 = $context["entry"]) && is_array($_v86) || $_v86 instanceof ArrayAccess ? ($_v86[(($_v87 = $context["colkey"]) instanceof \Stringable ? (string) $_v87 : $_v87)] ?? null) : null)), "html", null, true);
                                yield "
                                        ";
                            } elseif ((                            // line 337
($context["formatter"] ?? null) == "raw_html")) {
                                // line 338
                                yield "                                            ";
                                yield (($_v88 = $context["entry"]) && is_array($_v88) || $_v88 instanceof ArrayAccess ? ($_v88[(($_v89 = $context["colkey"]) instanceof \Stringable ? (string) $_v89 : $_v89)] ?? null) : null);
                                yield "
                                        ";
                            } elseif ((                            // line 339
($context["formatter"] ?? null) == "avatar")) {
                                // line 340
                                yield "                                            ";
                                // line 341
                                yield "                                            ";
                                $context["entry_data"] = (($_v90 = $context["entry"]) && is_array($_v90) || $_v90 instanceof ArrayAccess ? ($_v90[(($_v91 = $context["colkey"]) instanceof \Stringable ? (string) $_v91 : $_v91)] ?? null) : null);
                                // line 342
                                yield "                                            ";
                                $context["avatar_size"] = (((CoreExtension::getAttribute($this->env, $this->source, ($context["entry_data"] ?? null), "avatar_size", [], "array", true, true, false, 342) &&  !(null === (($_v92 = ($context["entry_data"] ?? null)) && is_array($_v92) || $_v92 instanceof ArrayAccess ? ($_v92["avatar_size"] ?? null) : null)))) ? ((($_v93 = ($context["entry_data"] ?? null)) && is_array($_v93) || $_v93 instanceof ArrayAccess ? ($_v93["avatar_size"] ?? null) : null)) : ("avatar-md"));
                                // line 343
                                yield "                                            ";
                                $context["img"] = (($_v94 = ($context["entry_data"] ?? null)) && is_array($_v94) || $_v94 instanceof ArrayAccess ? ($_v94["picture"] ?? null) : null);
                                // line 344
                                yield "                                            ";
                                $context["initials"] = (($_v95 = ($context["entry_data"] ?? null)) && is_array($_v95) || $_v95 instanceof ArrayAccess ? ($_v95["initials"] ?? null) : null);
                                // line 345
                                yield "                                            ";
                                $context["bg_color"] = (((($tmp =  !Twig\Extension\CoreExtension::testEmpty(($context["img"] ?? null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("inherit") : ((($_v96 = ($context["entry_data"] ?? null)) && is_array($_v96) || $_v96 instanceof ArrayAccess ? ($_v96["initials_bg"] ?? null) : null)));
                                // line 346
                                yield "                                            <span class=\"avatar ";
                                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["avatar_size"] ?? null), "html", null, true);
                                yield " rounded\"
                                                style=\"";
                                // line 347
                                if ((($tmp =  !(null === ($context["img"] ?? null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                                    yield " background-image: url(";
                                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["img"] ?? null), "html", null, true);
                                    yield "); ";
                                }
                                yield " background-color: ";
                                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["bg_color"] ?? null), "html", null, true);
                                yield "\">
                                                ";
                                // line 348
                                if (Twig\Extension\CoreExtension::testEmpty(($context["img"] ?? null))) {
                                    // line 349
                                    yield "                                                    ";
                                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["initials"] ?? null), "html", null, true);
                                    yield "
                                                ";
                                }
                                // line 351
                                yield "                                            </span>
                                        ";
                            } elseif (((                            // line 352
($context["formatter"] ?? null) == "badge") &&  !Twig\Extension\CoreExtension::testEmpty((($_v97 = $context["entry"]) && is_array($_v97) || $_v97 instanceof ArrayAccess ? ($_v97[(($_v98 = $context["colkey"]) instanceof \Stringable ? (string) $_v98 : $_v98)] ?? null) : null)))) {
                                // line 353
                                yield "                                            ";
                                $context["entry_data"] = (($_v99 = $context["entry"]) && is_array($_v99) || $_v99 instanceof ArrayAccess ? ($_v99[(($_v100 = $context["colkey"]) instanceof \Stringable ? (string) $_v100 : $_v100)] ?? null) : null);
                                // line 354
                                yield "                                            ";
                                $context["content"] = (($_v101 = ($context["entry_data"] ?? null)) && is_array($_v101) || $_v101 instanceof ArrayAccess ? ($_v101["content"] ?? null) : null);
                                // line 355
                                yield "                                            ";
                                $context["color"] = (((CoreExtension::getAttribute($this->env, $this->source, ($context["entry_data"] ?? null), "color", [], "array", true, true, false, 355) &&  !(null === (($_v102 = ($context["entry_data"] ?? null)) && is_array($_v102) || $_v102 instanceof ArrayAccess ? ($_v102["color"] ?? null) : null)))) ? ((($_v103 = ($context["entry_data"] ?? null)) && is_array($_v103) || $_v103 instanceof ArrayAccess ? ($_v103["color"] ?? null) : null)) : ("#BBBBBB"));
                                // line 356
                                yield "                                            ";
                                if ((($tmp =  !CoreExtension::matches("/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})\$/", ($context["color"] ?? null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                                    // line 357
                                    yield "                                                ";
                                    $context["color"] = "#BBBBBB";
                                    // line 358
                                    yield "                                            ";
                                }
                                // line 359
                                yield "                                            ";
                                if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(($context["content"] ?? null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                                    // line 360
                                    yield "                                                <div class=\"badge_block\" style=\"border-color: ";
                                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["color"] ?? null), "html", null, true);
                                    yield "\">
                                                    <span class=\"me-1\" style=\"background: ";
                                    // line 361
                                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["color"] ?? null), "html", null, true);
                                    yield "\"></span>
                                                    ";
                                    // line 362
                                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["content"] ?? null), "html", null, true);
                                    yield "
                                                </div>
                                            ";
                                }
                                // line 365
                                yield "                                        ";
                            } elseif ((($context["formatter"] ?? null) == "yesno")) {
                                // line 366
                                yield "                                            ";
                                if (((($_v104 = $context["entry"]) && is_array($_v104) || $_v104 instanceof ArrayAccess ? ($_v104[(($_v105 = $context["colkey"]) instanceof \Stringable ? (string) $_v105 : $_v105)] ?? null) : null) == 1)) {
                                    // line 367
                                    yield "                                                <div aria-label=\"";
                                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Yes"), "html", null, true);
                                    yield "\"><i class=\"ti ti-circle-check\"></i></div>
                                            ";
                                } else {
                                    // line 369
                                    yield "                                                <div aria-label=\"";
                                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("No"), "html", null, true);
                                    yield "\"></div>
                                            ";
                                }
                                // line 371
                                yield "                                        ";
                            } else {
                                // line 372
                                yield "                                            ";
                                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v106 = $context["entry"]) && is_array($_v106) || $_v106 instanceof ArrayAccess ? ($_v106[(($_v107 = $context["colkey"]) instanceof \Stringable ? (string) $_v107 : $_v107)] ?? null) : null), "html", null, true);
                                yield "
                                        ";
                            }
                            // line 374
                            yield "                                    </td>
                                ";
                        }
                        // line 376
                        yield "                            ";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['colkey'], $context['colum'], $context['_parent']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 377
                    yield "                            ";
                    if ((($tmp =  !(((array_key_exists("nofilter", $context) &&  !(null === $context["nofilter"]))) ? ($context["nofilter"]) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                        // line 378
                        yield "                                <td></td>
                            ";
                    }
                    // line 380
                    yield "                        </tr>
                    ";
                    ++$context['loop']['index0'];
                    ++$context['loop']['index'];
                    $context['loop']['first'] = false;
                    if (isset($context['loop']['revindex0'], $context['loop']['revindex'])) {
                        --$context['loop']['revindex0'];
                        --$context['loop']['revindex'];
                        $context['loop']['last'] = 0 === $context['loop']['revindex0'];
                    }
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_key'], $context['entry'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 382
                yield "                ";
            } else {
                // line 383
                yield "                    <tr>
                        <td colspan=\"";
                // line 384
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["total_cols"] ?? null), "html", null, true);
                yield "\">
                            ";
                // line 385
                yield $macros["alerts"]->getTemplateForMacro("macro_alert_info", $context, 385, $this->getSourceContext())->macro_alert_info(...[__("No results found")]);
                yield "
                        </td>
                    </tr>
                ";
            }
            // line 389
            yield "            </tbody>
            ";
            // line 390
            if ((($tmp = ($context["footers"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 391
                yield "                <tfoot class=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("footer_class", $context)) ? (Twig\Extension\CoreExtension::default(($context["footer_class"] ?? null), "")) : ("")), "html", null, true);
                yield "\">
                    ";
                // line 392
                $context['_parent'] = $context;
                $context['_seq'] = CoreExtension::ensureTraversable(($context["footers"] ?? null));
                foreach ($context['_seq'] as $context["_key"] => $context["footer"]) {
                    // line 393
                    yield "                        <tr>
                            ";
                    // line 394
                    if ((($tmp = ($context["showmassiveactions"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                        // line 395
                        yield "                                <td></td>
                            ";
                    }
                    // line 397
                    yield "                            ";
                    $context['_parent'] = $context;
                    $context['_seq'] = CoreExtension::ensureTraversable($context["footer"]);
                    foreach ($context['_seq'] as $context["footer_col"] => $context["footerval"]) {
                        // line 398
                        yield "                                <td>";
                        yield Twig\Extension\CoreExtension::nl2br($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["footerval"], "html", null, true));
                        yield "</td>
                            ";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['footer_col'], $context['footerval'], $context['_parent']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 400
                    yield "                            ";
                    if ((($tmp =  !array_key_exists("nofilter", $context)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                        // line 401
                        yield "                                <td></td>
                            ";
                    }
                    // line 403
                    yield "                        </tr>
                    ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_key'], $context['footer'], $context['_parent']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 405
                yield "                </tfoot>
            ";
            }
            // line 407
            yield "        </table>
    </div>

    ";
            // line 410
            if ((($tmp = ($context["use_pager"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 411
                yield "        <div class=\"ms-auto d-inline-flex align-items-center d-none d-md-block my-2\">
            ";
                // line 412
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Entries to show:"), "html", null, true);
                yield "
            ";
                // line 413
                yield from $this->load("components/dropdown/limit.html.twig", 413)->unwrap()->yield($context);
                // line 414
                yield "        </div>
    ";
            }
            // line 416
            yield "
    <script type=\"text/javascript\">
    \$(function() {
        \$(\x27.filter-select-multiple\x27).select2();
    });
    </script>
";
        }
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "components/datatable.html.twig";
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
        return array (  957 => 416,  953 => 414,  951 => 413,  947 => 412,  944 => 411,  942 => 410,  937 => 407,  933 => 405,  926 => 403,  922 => 401,  919 => 400,  910 => 398,  905 => 397,  901 => 395,  899 => 394,  896 => 393,  892 => 392,  887 => 391,  885 => 390,  882 => 389,  875 => 385,  871 => 384,  868 => 383,  865 => 382,  850 => 380,  846 => 378,  843 => 377,  837 => 376,  833 => 374,  827 => 372,  824 => 371,  818 => 369,  812 => 367,  809 => 366,  806 => 365,  800 => 362,  796 => 361,  791 => 360,  788 => 359,  785 => 358,  782 => 357,  779 => 356,  776 => 355,  773 => 354,  770 => 353,  768 => 352,  765 => 351,  759 => 349,  757 => 348,  747 => 347,  742 => 346,  739 => 345,  736 => 344,  733 => 343,  730 => 342,  727 => 341,  725 => 340,  723 => 339,  718 => 338,  716 => 337,  711 => 336,  709 => 335,  704 => 334,  702 => 333,  697 => 332,  695 => 331,  690 => 330,  688 => 329,  683 => 328,  681 => 327,  676 => 326,  674 => 325,  669 => 324,  667 => 323,  662 => 321,  656 => 318,  653 => 317,  651 => 316,  646 => 314,  640 => 311,  637 => 310,  635 => 309,  632 => 308,  630 => 307,  622 => 305,  619 => 304,  616 => 303,  613 => 302,  608 => 301,  604 => 299,  597 => 297,  593 => 296,  590 => 295,  588 => 294,  585 => 293,  583 => 292,  580 => 291,  574 => 289,  571 => 288,  565 => 286,  562 => 285,  556 => 283,  554 => 282,  546 => 281,  543 => 280,  540 => 279,  522 => 278,  520 => 277,  516 => 275,  512 => 273,  505 => 271,  502 => 270,  497 => 268,  493 => 267,  490 => 266,  483 => 262,  479 => 261,  474 => 259,  470 => 258,  466 => 257,  461 => 256,  458 => 255,  456 => 254,  454 => 253,  449 => 251,  445 => 250,  442 => 249,  440 => 248,  437 => 247,  435 => 244,  434 => 242,  432 => 241,  430 => 240,  427 => 239,  425 => 236,  424 => 234,  422 => 233,  420 => 232,  417 => 231,  408 => 228,  401 => 227,  397 => 226,  391 => 224,  388 => 223,  386 => 222,  383 => 221,  380 => 220,  376 => 219,  371 => 217,  367 => 215,  363 => 213,  361 => 212,  358 => 211,  355 => 210,  351 => 208,  346 => 205,  340 => 202,  334 => 200,  331 => 199,  325 => 196,  319 => 194,  317 => 193,  313 => 191,  311 => 190,  308 => 189,  301 => 187,  297 => 185,  295 => 184,  290 => 183,  285 => 181,  280 => 180,  278 => 179,  274 => 177,  272 => 176,  269 => 175,  266 => 174,  263 => 173,  260 => 172,  257 => 171,  254 => 170,  251 => 169,  248 => 168,  245 => 167,  240 => 166,  233 => 162,  229 => 161,  225 => 160,  221 => 158,  219 => 157,  216 => 156,  213 => 155,  209 => 153,  205 => 152,  200 => 151,  194 => 150,  191 => 149,  188 => 148,  185 => 147,  183 => 146,  173 => 144,  169 => 142,  167 => 141,  164 => 140,  162 => 139,  152 => 138,  149 => 137,  145 => 135,  143 => 134,  142 => 133,  140 => 132,  137 => 131,  134 => 130,  124 => 123,  117 => 118,  114 => 117,  107 => 113,  103 => 111,  100 => 110,  97 => 109,  94 => 108,  92 => 107,  86 => 105,  84 => 104,  81 => 103,  79 => 102,  76 => 100,  74 => 99,  71 => 98,  69 => 97,  67 => 96,  65 => 95,  63 => 94,  61 => 93,  59 => 92,  57 => 91,  55 => 90,  53 => 89,  50 => 88,  48 => 87,  45 => 86,  42 => 32,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "components/datatable.html.twig", "/var/www/html/glpi/templates/components/datatable.html.twig");
    }
}
