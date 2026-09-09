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

/* components/search/controls.html.twig */
class __TwigTemplate_8f658f0ddf293e294ac87377a1e5d5db extends Template
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
        if ((($context["showmassiveactions"] ?? null) && (($context["count"] ?? null) > 0))) {
            // line 36
            yield "    <div class=\"massiveactions-control card-header search-header ps-3 animate__animated animate__faster d-none\">
        ";
            // line 37
            $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Html::showMassiveActions", [($context["massiveactionparams"] ?? null)]);
            // line 38
            yield "    </div>
";
        }
        // line 40
        yield "
<div class=\"card-header search-header px-1 px-xl-3\">
    ";
        // line 42
        if ((($tmp =  !(((CoreExtension::getAttribute($this->env, $this->source, ($context["original_params"] ?? null), "hide_controls", [], "any", true, true, false, 42) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, ($context["original_params"] ?? null), "hide_controls", [], "any", false, false, false, 42)))) ? (CoreExtension::getAttribute($this->env, $this->source, ($context["original_params"] ?? null), "hide_controls", [], "any", false, false, false, 42)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 43
            yield "        <div class=\"search-controls d-flex justify-content-between align-items-center\">

            ";
            // line 45
            $context["mainform"] = (((array_key_exists("mainform", $context) &&  !(null === $context["mainform"]))) ? ($context["mainform"]) : (true));
            // line 46
            yield "            ";
            $context["showaction"] = (((array_key_exists("showaction", $context) &&  !(null === $context["showaction"]))) ? ($context["showaction"]) : (true));
            // line 47
            yield "            ";
            if ((($context["mainform"] ?? null) && ($context["showaction"] ?? null))) {
                // line 48
                yield "                <form
                    name=\"searchform";
                // line 49
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["normalized_itemtype"] ?? null), "html", null, true);
                yield "\"
                    class=\"search-form-container needs-validation\"
                    novalidate
                    method=\"get\"
                    action=\"";
                // line 53
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["href"] ?? null), "html", null, true);
                yield "\"
                    ";
                // line 54
                if ((($tmp =  !$this->extensions['Glpi\Application\View\Extension\SessionExtension']->userPref("show_search_form")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 55
                    yield "                        ";
                    // line 60
                    yield "                        data-glpi-search-form
                    ";
                }
                // line 62
                yield "                >
            ";
            }
            // line 64
            yield "            ";
            if ((($tmp =  !(((CoreExtension::getAttribute($this->env, $this->source, ($context["original_params"] ?? null), "hide_criteria", [], "any", true, true, false, 64) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, ($context["original_params"] ?? null), "hide_criteria", [], "any", false, false, false, 64)))) ? (CoreExtension::getAttribute($this->env, $this->source, ($context["original_params"] ?? null), "hide_criteria", [], "any", false, false, false, 64)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 65
                yield "                <div class=\"primary-controls\">
                <div class=\"btn-group me-1 me-xl-2\">
                    ";
                // line 67
                $context["is_filter_active"] = false;
                // line 68
                yield "                    ";
                if ((($tmp = Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["active_search_name"] ?? null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 69
                    yield "                        ";
                    $context["is_filter_active"] = true;
                    // line 70
                    yield "                    ";
                }
                // line 71
                yield "                    ";
                $context["animation_cls"] = "animate__animated animate__zoomIn";
                // line 72
                yield "
                    ";
                // line 73
                $context["active_savedsearch_class"] = (((($tmp = ($context["active_savedsearch"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("btn-active-search") : (""));
                // line 74
                yield "                    <button type=\"button\" class=\"btn btn-icon btn-sm p-1 ";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["active_savedsearch_class"] ?? null), "html", null, true);
                yield " btn-ghost-secondary show-saved-searches\"
                        data-itemtype=\"";
                // line 75
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["itemtype"] ?? null), "html", null, true);
                yield "\"
                        title=\"";
                // line 76
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Show saved searches"), "html", null, true);
                yield "\"  data-bs-toggle=\"tooltip\" data-bs-placement=\"top\">
                        <i class=\"ti ti-bookmarks\"></i>
                    </button>
                    ";
                // line 79
                if ((($tmp =  !$this->extensions['Glpi\Application\View\Extension\SessionExtension']->userPref("show_search_form")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 80
                    yield "                        ";
                    $context["active_filter_class"] = (((($tmp = ($context["is_filter_active"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("btn-active-search") : ("btn-ghost-secondary"));
                    // line 81
                    yield "                        <button class=\"btn btn-sm py-1 show-search-filters ";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["active_filter_class"] ?? null), "html", null, true);
                    yield " btn-ghost-secondary dropdown-toggle\" type=\"button\"
                                data-bs-toggle=\"dropdown\" data-bs-auto-close=\"outside\"
                                data-testid=\"search-filters-button\">
                            <i class=\"ti ti-list-search\"></i>
                            ";
                    // line 85
                    if ((($tmp = Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["active_search_name"] ?? null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                        // line 86
                        yield "                                <span class=\"d-none d-xl-inline-block text-truncate\" style=\"max-width: 250px\" title=\"";
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["active_search_name"] ?? null), "html", null, true);
                        yield "\" data-bs-toggle=\"tooltip\" data-bs-placement=\"top\">
                                    ";
                        // line 87
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["active_search_name"] ?? null), "html", null, true);
                        yield "
                                </span>
                            ";
                    } else {
                        // line 90
                        yield "                                <span class=\"d-none d-xl-inline-block\">";
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Search"), "html", null, true);
                        yield "</span>
                            ";
                    }
                    // line 92
                    yield "                        </button>
                        <div class=\"dropdown-menu dropdown-menu-card ";
                    // line 93
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["animation_cls"] ?? null), "html", null, true);
                    yield "\" style=\"width: max-content; max-width: 100vw;\"
                             data-testid=\"search-filters-panel\">
                            ";
                    // line 95
                    $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Glpi\\Search\\Input\\QueryBuilder::showGenericSearch", [($context["itemtype"] ?? null), ($context["original_params"] ?? null)]);
                    // line 96
                    yield "                        </div>
                    ";
                }
                // line 98
                yield "                </div>

                ";
                // line 100
                if ((((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["data"] ?? null), "search", [], "array", false, true, false, 100), "as_map", [], "array", true, true, false, 100)) ? (Twig\Extension\CoreExtension::default((($_v0 = (($_v1 = ($context["data"] ?? null)) && is_array($_v1) || $_v1 instanceof ArrayAccess ? ($_v1["search"] ?? null) : null)) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0["as_map"] ?? null) : null), false)) : (false)) != 1)) {
                    // line 101
                    yield "                    ";
                    $context["active_sort_class"] = (((($tmp = ($context["active_sort"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("btn-active-sort") : (""));
                    // line 102
                    yield "                    <button class=\"btn btn-sm py-1 show-search-sorts ";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["active_sort_class"] ?? null), "html", null, true);
                    yield " btn-ghost-secondary dropdown-toggle me-1 me-xl-2\" type=\"button\"
                                data-bs-toggle=\"dropdown\" data-bs-auto-close=\"outside\"
                                data-testid=\"search-sorts-button\">
                        <i class=\"ti ti-arrows-sort\"></i>
                        ";
                    // line 106
                    $context["sort_names"] = (((($tmp = Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["active_sort_name"] ?? null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (($context["active_sort_name"] ?? null)) : (__("Sort")));
                    // line 107
                    yield "                        <span class=\"d-none d-xl-inline-block text-truncate\" style=\"max-width: 250px\" title=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["sort_names"] ?? null), "html", null, true);
                    yield "\" data-bs-toggle=\"tooltip\" data-bs-placement=\"top\">
                            ";
                    // line 108
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["sort_names"] ?? null), "html", null, true);
                    yield "
                        </span>
                    </button>
                    <div class=\"dropdown-menu dropdown-menu-card ";
                    // line 111
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["animation_cls"] ?? null), "html", null, true);
                    yield "\" style=\"width: max-content; max-width: 100vw;\"
                         data-testid=\"search-sorts-panel\">
                        ";
                    // line 113
                    $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Glpi\\Search\\Input\\QueryBuilder::showGenericSort", [($context["itemtype"] ?? null), ($context["original_params"] ?? null)]);
                    // line 114
                    yield "                    </div>
                ";
                }
                // line 116
                yield "            </div>
            ";
            }
            // line 118
            yield "
            ";
            // line 119
            if ((($context["mainform"] ?? null) && ($context["showaction"] ?? null))) {
                // line 120
                yield "                ";
                yield $macros["fields"]->getTemplateForMacro("macro_csrfField", $context, 120, $this->getSourceContext())->macro_csrfField(...[]);
                yield "
                </form>
            ";
            }
            // line 123
            yield "
            <div class=\"middle-controls d-flex\">
                ";
            // line 125
            if ((($tmp = $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("method_exists", [($context["itemtype"] ?? null), "showSearchStatusArea"])) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 126
                yield "                    ";
                $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call([($context["itemtype"] ?? null), "showSearchStatusArea"]);
                // line 127
                yield "                ";
            }
            // line 128
            yield "
                ";
            // line 129
            if ((($tmp = $this->extensions['Glpi\Application\View\Extension\SessionExtension']->userPref("search_pagination_on_top")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 130
                yield "                    ";
                if ((((($_v2 = ($context["data"] ?? null)) && is_array($_v2) || $_v2 instanceof ArrayAccess ? ($_v2["display_type"] ?? null) : null) != Twig\Extension\CoreExtension::constant("Search::GLOBAL_SEARCH")) && ((($_v3 = (($_v4 = ($context["data"] ?? null)) && is_array($_v4) || $_v4 instanceof ArrayAccess ? ($_v4["search"] ?? null) : null)) && is_array($_v3) || $_v3 instanceof ArrayAccess ? ($_v3["as_map"] ?? null) : null) == 0))) {
                    // line 131
                    yield "                        <div class=\"search-footer flex-grow-1 mx-1 d-none d-xxl-block mb-n2\" style=\"max-width: 650px;\">
                            ";
                    // line 132
                    yield from $this->load("components/pager.html.twig", 132)->unwrap()->yield(CoreExtension::merge($context, ["short_display" => true]));
                    // line 135
                    yield "                        </div>
                    ";
                }
                // line 137
                yield "                ";
            }
            // line 138
            yield "            </div>

            ";
            // line 140
            $context["submit_search_form"] = "this.closest(\x27[data-glpi-search-container]\x27).querySelector(\x27[data-glpi-search-form]\x27).submit()";
            // line 141
            yield "            <div class=\"secondary-controls\">
                ";
            // line 142
            if ((($tmp = ($context["may_be_located"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 143
                yield "                    <div class=\"btn-group me-1 me-xl-2 shadow-none\" role=\"group\">
                        ";
                // line 144
                $context["table_class"] = ((((($_v5 = (($_v6 = ($context["data"] ?? null)) && is_array($_v6) || $_v6 instanceof ArrayAccess ? ($_v6["search"] ?? null) : null)) && is_array($_v5) || $_v5 instanceof ArrayAccess ? ($_v5["as_map"] ?? null) : null) == 0)) ? ("btn-ghost-info") : (""));
                // line 145
                yield "                        <input
                            type=\"radio\"
                            class=\"btn-check\"
                            name=\"as_map\"
                            value=\"1\"
                            autocomplete=\"off\"
                            id=\"show_as_table\"
                            onclick=\"toogle(\x27as_map\x27,\x27\x27,\x27\x27,\x27\x27); ";
                // line 152
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["submit_search_form"] ?? null), "html", null, true);
                yield ";\"
                            ";
                // line 153
                yield ((((($_v7 = (($_v8 = ($context["data"] ?? null)) && is_array($_v8) || $_v8 instanceof ArrayAccess ? ($_v8["search"] ?? null) : null)) && is_array($_v7) || $_v7 instanceof ArrayAccess ? ($_v7["as_map"] ?? null) : null) == 0)) ? ("checked") : (""));
                yield "
                        >
                            <label class=\"btn btn-icon btn-sm btn-pill px-2 py-1 ";
                // line 155
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["table_class"] ?? null), "html", null, true);
                yield "\" title=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Show as table"), "html", null, true);
                yield "\"
                                for=\"show_as_table\"
                                data-bs-toggle=\"tooltip\" data-bs-placement=\"top\">
                                <i class=\"ti ti-table\"></i>
                            </label >

                        ";
                // line 161
                $context["located_class"] = ((((($_v9 = (($_v10 = ($context["data"] ?? null)) && is_array($_v10) || $_v10 instanceof ArrayAccess ? ($_v10["search"] ?? null) : null)) && is_array($_v9) || $_v9 instanceof ArrayAccess ? ($_v9["as_map"] ?? null) : null) == 1)) ? ("btn-ghost-info") : (""));
                // line 162
                yield "                        <input
                            type=\"radio\"
                            class=\"btn-check\"
                            name=\"as_map\"
                            value=\"1\"
                            autocomplete=\"off\"
                            id=\"show_as_map\"
                            onclick=\"toogle(\x27as_map\x27,\x27\x27,\x27\x27,\x27\x27); ";
                // line 169
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["submit_search_form"] ?? null), "html", null, true);
                yield ";\"
                            ";
                // line 170
                yield ((((($_v11 = (($_v12 = ($context["data"] ?? null)) && is_array($_v12) || $_v12 instanceof ArrayAccess ? ($_v12["search"] ?? null) : null)) && is_array($_v11) || $_v11 instanceof ArrayAccess ? ($_v11["as_map"] ?? null) : null) == 1)) ? ("checked") : (""));
                yield "
                        >
                        <label class=\"btn btn-icon btn-sm btn-pill px-2 py-1 ";
                // line 172
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["located_class"] ?? null), "html", null, true);
                yield "\" title=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Show as map"), "html", null, true);
                yield "\"
                            for=\"show_as_map\"
                            data-bs-toggle=\"tooltip\" data-bs-placement=\"top\">
                            <i class=\"ti ti-map-2\"></i>
                        </label >
                    </div>
                ";
            }
            // line 179
            yield "
                ";
            // line 180
            if ((($tmp = ($context["may_be_browsed"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 181
                yield "                    <button
                        class=\"btn btn-icon btn-sm ";
                // line 182
                yield (((((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["data"] ?? null), "search", [], "array", false, true, false, 182), "browse", [], "array", true, true, false, 182)) ? (Twig\Extension\CoreExtension::default((($_v13 = (($_v14 = ($context["data"] ?? null)) && is_array($_v14) || $_v14 instanceof ArrayAccess ? ($_v14["search"] ?? null) : null)) && is_array($_v13) || $_v13 instanceof ArrayAccess ? ($_v13["browse"] ?? null) : null), false)) : (false)) == 1)) ? ("btn-secondary") : ("btn-ghost-secondary"));
                yield " me-1 me-xl-2 px-1\"
                        type=\"button\"
                        title=\"";
                // line 184
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Toggle browse"), "html", null, true);
                yield "\"
                        data-bs-toggle=\"tooltip\"
                        data-bs-placement=\"top\"
                        onclick=\"toogle(\x27browse\x27,\x27\x27,\x27\x27,\x27\x27); ";
                // line 187
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["submit_search_form"] ?? null), "html", null, true);
                yield ";\"
                    >
                        <i class=\"ti ";
                // line 189
                yield (((((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["data"] ?? null), "search", [], "array", false, true, false, 189), "browse", [], "array", true, true, false, 189)) ? (Twig\Extension\CoreExtension::default((($_v15 = (($_v16 = ($context["data"] ?? null)) && is_array($_v16) || $_v16 instanceof ArrayAccess ? ($_v16["search"] ?? null) : null)) && is_array($_v15) || $_v15 instanceof ArrayAccess ? ($_v15["browse"] ?? null) : null), false)) : (false)) == 1)) ? ("ti-checkbox") : ("ti-square"));
                yield "\"></i>
                        <span class=\"d-flex align-bottom\">
                            <i class=\"ti ti-subtask\"></i>
                        </span>
                    </button>
                ";
            }
            // line 195
            yield "
                ";
            // line 196
            if ((($tmp = ($context["may_be_deleted"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 197
                yield "                    <button
                        class=\"btn btn-icon btn-sm ";
                // line 198
                yield ((((($_v17 = (($_v18 = ($context["data"] ?? null)) && is_array($_v18) || $_v18 instanceof ArrayAccess ? ($_v18["search"] ?? null) : null)) && is_array($_v17) || $_v17 instanceof ArrayAccess ? ($_v17["is_deleted"] ?? null) : null) == 1)) ? ("btn-danger") : ("btn-ghost-danger"));
                yield " me-1 me-xl-2 px-1\"
                        type=\"button\"
                        title=\"";
                // line 200
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Show the trashbin"), "html", null, true);
                yield "\"
                        data-bs-toggle=\"tooltip\"
                        data-bs-placement=\"top\"
                        onclick=\"toogle(\x27is_deleted\x27,\x27\x27,\x27\x27,\x27\x27); ";
                // line 203
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["submit_search_form"] ?? null), "html", null, true);
                yield ";\"
                    >
                        <i class=\"ti ";
                // line 205
                yield ((((($_v19 = (($_v20 = ($context["data"] ?? null)) && is_array($_v20) || $_v20 instanceof ArrayAccess ? ($_v20["search"] ?? null) : null)) && is_array($_v19) || $_v19 instanceof ArrayAccess ? ($_v19["is_deleted"] ?? null) : null) == 1)) ? ("ti-checkbox") : ("ti-square"));
                yield "\"></i>
                        <span class=\"d-flex align-bottom\">
                            <i class=\"ti ti-trash\"></i>
                        </span>
                    </button>
                ";
            }
            // line 211
            yield "
                ";
            // line 212
            if ((($tmp = ($context["may_be_unpublished"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 213
                yield "                    ";
                $context["show_unpublished"] = (((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["data"] ?? null), "search", [], "array", false, true, false, 213), "unpublished", [], "array", true, true, false, 213)) ? (Twig\Extension\CoreExtension::default((($_v21 = (($_v22 = ($context["data"] ?? null)) && is_array($_v22) || $_v22 instanceof ArrayAccess ? ($_v22["search"] ?? null) : null)) && is_array($_v21) || $_v21 instanceof ArrayAccess ? ($_v21["unpublished"] ?? null) : null), false)) : (false)) == 1);
                // line 214
                yield "                    <button
                        class=\"btn btn-icon btn-sm ";
                // line 215
                yield (((($tmp = ($context["show_unpublished"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("btn-warning") : ("btn-ghost-warning"));
                yield " me-1 me-xl-2 px-1\"
                        type=\"button\"
                        title=\"";
                // line 217
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Show unpublished"), "html", null, true);
                yield "\"
                        data-bs-toggle=\"tooltip\"
                        data-bs-placement=\"top\"
                        onclick=\"toogle(\x27unpublished\x27,\x27\x27,\x27\x27,\x27\x27); ";
                // line 220
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["submit_search_form"] ?? null), "html", null, true);
                yield ";\"
                    >
                        <i
                            class=\"ti ";
                // line 223
                yield (((($tmp = ($context["show_unpublished"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("ti-checkbox") : ("ti-square"));
                yield "\"
                            data-testid=\"unpublished-";
                // line 224
                yield (((($tmp = ($context["show_unpublished"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("on") : ("off"));
                yield "\"
                        ></i>
                        <span class=\"d-flex align-bottom\">
                            <i class=\"ti ti-eye-off\"></i>
                        </span>
                    </button>
                ";
            }
            // line 231
            yield "

                ";
            // line 233
            if ((($tmp = ($context["can_config"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 234
                yield "                    <button class=\"show_displaypreference_modal  btn btn-sm btn-ghost-secondary\"
                        type=\"button\"
                        title=\"";
                // line 236
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Select default items to show"), "html", null, true);
                yield "\"
                        data-bs-toggle=\"tooltip\" data-bs-placement=\"top\">
                        <i class=\"ti ti-table-row\"></i>
                    </button>
                    <template id=\"displaypreference_modal_template";
                // line 240
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
                yield "\">
                        ";
                // line 241
                yield Twig\Extension\CoreExtension::include($this->env, $context, "components/search/displaypreference_modal.html.twig", ["rand" =>                 // line 242
($context["rand"] ?? null), "itemtype" =>                 // line 243
($context["itemtype"] ?? null)]);
                // line 244
                yield "
                    </template>
                ";
            }
            // line 247
            yield "
                ";
            // line 248
            if ((($tmp =  !$this->extensions['Glpi\Application\View\Extension\SessionExtension']->userPref("show_search_form")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 249
                yield "                    <button type=\"button\" class=\"btn btn-icon btn-sm p-1 btn-ghost-secondary refresh-search\"
                        title=\"";
                // line 250
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Refresh"), "html", null, true);
                yield "\"  data-bs-toggle=\"tooltip\" data-bs-placement=\"top\" data-bs-trigger=\"hover\">
                        <i class=\"ti ti-refresh\"></i>
                    </button>
                ";
            }
            // line 254
            yield "
                ";
            // line 255
            if ((($context["count"] ?? null) > 0)) {
                // line 256
                yield "                    <button class=\"dropdown-toggle btn btn-sm btn-ghost-secondary\" type=\"button\" name=\"export\" id=\"dropdown-export-";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
                yield "\"
                            data-bs-toggle=\"dropdown\" aria-expanded=\"false\" data-bs-popper-config=\x27{\"strategy\":\"fixed\"}\x27>
                        <span title=\"";
                // line 258
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Export"), "html", null, true);
                yield "\" data-bs-toggle=\"tooltip\" data-bs-placement=\"top\">
                            <i id=\"export_dropdown_icon\" class=\"ti ti-download\"></i>
                        </span>
                    </button>
                    ";
                // line 262
                $context["exporthref"] = (((($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path("/front/report.dynamic.php") . "?") . Twig\Extension\CoreExtension::urlencode(["item_type" =>                 // line 263
($context["itemtype"] ?? null), "sort" =>                 // line 264
($context["sort"] ?? null), "order" =>                 // line 265
($context["order"] ?? null), "start" =>                 // line 266
($context["start"] ?? null)])) . "&") .                 // line 267
($context["posthref"] ?? null));
                // line 268
                yield "                    <div class=\"dropdown-menu\" style=\"z-index: 10001\" aria-labelledby=\"dropdown-export-";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
                yield "\" role=\"menu\">
                        <div role=\"separator\"><h6 class=\"dropdown-header\">";
                // line 269
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Current page"), "html", null, true);
                yield "</h6></div>
                        <a class=\"dropdown-item\" href=\"";
                // line 270
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((($context["exporthref"] ?? null) . "&display_type=") . Twig\Extension\CoreExtension::constant("Search::PDF_OUTPUT_LANDSCAPE")), "html", null, true);
                yield "\" role=\"menuitem\">
                            <i class=\"ti ti-file-type-pdf\"></i>";
                // line 272
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Landscape PDF"), "html", null, true);
                // line 273
                yield "</a>
                        <a class=\"dropdown-item\" href=\"";
                // line 274
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((($context["exporthref"] ?? null) . "&display_type=") . Twig\Extension\CoreExtension::constant("Search::PDF_OUTPUT_PORTRAIT")), "html", null, true);
                yield "\" role=\"menuitem\">
                            <i class=\"ti ti-file-type-pdf\"></i>
                            ";
                // line 276
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Portrait PDF"), "html", null, true);
                yield "
                        </a>
                        <a class=\"dropdown-item\" href=\"";
                // line 278
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((($context["exporthref"] ?? null) . "&display_type=") . Twig\Extension\CoreExtension::constant("Search::CSV_OUTPUT")), "html", null, true);
                yield "\" role=\"menuitem\">
                            <i class=\"ti ti-file-type-csv\"></i>
                            ";
                // line 280
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("CSV"), "html", null, true);
                yield "
                        </a>
                        <a class=\"dropdown-item\" href=\"";
                // line 282
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((($context["exporthref"] ?? null) . "&display_type=") . Twig\Extension\CoreExtension::constant("Search::ODS_OUTPUT")), "html", null, true);
                yield "\" role=\"menuitem\">
                            <i class=\"ti ti-file-spreadsheet\"></i>
                            ";
                // line 284
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::sprintf(__("Spreadsheet (%s)"), "ODS"), "html", null, true);
                yield "
                        </a>
                        <a class=\"dropdown-item\" href=\"";
                // line 286
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((($context["exporthref"] ?? null) . "&display_type=") . Twig\Extension\CoreExtension::constant("Search::XLSX_OUTPUT")), "html", null, true);
                yield "\" role=\"menuitem\">
                            <i class=\"ti ti-file-spreadsheet\"></i>
                            ";
                // line 288
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::sprintf(__("Spreadsheet (%s)"), "XLSX"), "html", null, true);
                yield "
                        </a>
                        <hr class=\"dropdown-divider\">
                        <div role=\"separator\"><h6 class=\"dropdown-header\">";
                // line 291
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("All pages"), "html", null, true);
                yield "</h6></div>
                        <a class=\"dropdown-item\" href=\"";
                // line 292
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((($context["exporthref"] ?? null) . "&display_type=-") . Twig\Extension\CoreExtension::constant("Search::PDF_OUTPUT_LANDSCAPE")), "html", null, true);
                yield "\" role=\"menuitem\">
                            <i class=\"ti ti-file-type-pdf\"></i>
                            ";
                // line 294
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Landscape PDF"), "html", null, true);
                yield "
                        </a>
                        <a class=\"dropdown-item\" href=\"";
                // line 296
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((($context["exporthref"] ?? null) . "&display_type=-") . Twig\Extension\CoreExtension::constant("Search::PDF_OUTPUT_PORTRAIT")), "html", null, true);
                yield "\" role=\"menuitem\">
                            <i class=\"ti ti-file-type-pdf\"></i>
                            ";
                // line 298
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Portrait PDF"), "html", null, true);
                yield "
                        </a>
                        <a class=\"dropdown-item\" href=\"";
                // line 300
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((($context["exporthref"] ?? null) . "&display_type=-") . Twig\Extension\CoreExtension::constant("Search::CSV_OUTPUT")), "html", null, true);
                yield "\" role=\"menuitem\">
                            <i class=\"ti ti-file-type-csv\"></i>
                            ";
                // line 302
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("CSV"), "html", null, true);
                yield "
                        </a>
                        <a class=\"dropdown-item\" href=\"";
                // line 304
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((($context["exporthref"] ?? null) . "&display_type=-") . Twig\Extension\CoreExtension::constant("Search::ODS_OUTPUT")), "html", null, true);
                yield "\" role=\"menuitem\">
                            <i class=\"ti ti-file-spreadsheet\"></i>
                            ";
                // line 306
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::sprintf(__("Spreadsheet (%s)"), "ODS"), "html", null, true);
                yield "
                        </a>
                        <a class=\"dropdown-item\" href=\"";
                // line 308
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((($context["exporthref"] ?? null) . "&display_type=-") . Twig\Extension\CoreExtension::constant("Search::XLSX_OUTPUT")), "html", null, true);
                yield "\" role=\"menuitem\">
                            <i class=\"ti ti-file-spreadsheet\"></i>
                            ";
                // line 310
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::sprintf(__("Spreadsheet (%s)"), "XLSX"), "html", null, true);
                yield "
                        </a>
                        ";
                // line 312
                if ((($context["itemtype"] ?? null) != "Stat")) {
                    // line 313
                    yield "                        <hr class=\"dropdown-divider\">
                        <a class=\"dropdown-item\" href=\"";
                    // line 314
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((($context["exporthref"] ?? null) . "&display_type=-") . Twig\Extension\CoreExtension::constant("Search::NAMES_OUTPUT")), "html", null, true);
                    yield "\"
                           id=\"copy_names_to_clipboard\" role=\"menuitem\">
                            <i class=\"ti ti-copy\"></i>
                            ";
                    // line 317
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Copy names to clipboard"), "html", null, true);
                    yield "
                        </a>
                        ";
                }
                // line 320
                yield "                    </div>
                    <script>
                        // Ugly trick to fix z-index context issue by placing our dropdown at the end of the body
                        \$(\"#dropdown-export-";
                // line 323
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
                yield "\").on(\"show.bs.dropdown\", function() {
                            \$(document.body).append(\$(\"ul[aria-labelledby=dropdown-export-";
                // line 324
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
                yield "]\").detach());
                        });
                    </script>
                ";
            }
            // line 328
            yield "            </div>
        </div>
    ";
        }
        // line 331
        yield "</div>

<script type=\"text/javascript\">
\$(document).ready(function() {
   const refreshSearchResults = function() {
      const search_display = \$(\x27.ajax-container.search-display-data\x27);
      try {
         if (search_display.length === 1 && search_display.data(\x27js_class\x27) !== undefined) {
            search_display.data(\x27js_class\x27).view.refreshResults();
         } else {
            window.location.reload();
         }
      } catch (err) {
         window.location.reload();
      }
   };

   \$(\x27.show_displaypreference_modal\x27).click(function(e) {
      e.preventDefault();

      // remove old modal
      \$(\x27#displayprefence_modal";
        // line 352
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
        yield "\x27).remove();

      // create new one
      \$(\x27body\x27).append(\$(\x27#displaypreference_modal_template";
        // line 355
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
        yield "\x27).html());
      const modal_el = \$(\x27#displaypreference_modal";
        // line 356
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
        yield "\x27);
      modal_el.modal(\x27show\x27);
   });

   \$(\"body\").on(\x27hide.bs.modal\x27, \x27#displaypreference_modal";
        // line 360
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
        yield "\x27, function() {
      refreshSearchResults();
   });

   \$(\x27.default-filter\x27).change(function(event) {
      const search_params = new URLSearchParams(window.location.search);
      if (!\$(this).is(\":checked\")) {
          search_params.set(\x27nodefault\x27, \x271\x27);
      } else {
          search_params.delete(\x27nodefault\x27);
      }
      window.location.replace(\x27?\x27 + search_params.toString());
   });

   \$(\x27.refresh-search\x27).on(\x27click\x27, function(e) {
      e.preventDefault();
      const tooltip = bootstrap.Tooltip.getInstance(\$(this)[0]);
      if (tooltip !== null) {
         tooltip.hide();
      }
      refreshSearchResults();
   });

   // Callbacks for copy success/failure
   function copy_success() {
      glpi_toast_info(__(\x27Results copied to clipboard\x27));
      \$(\x27#export_dropdown_icon\x27).removeClass(\x27spinner-border spinner-border-sm\x27);
      \$(\x27#export_dropdown_icon\x27).addClass(\x27ti-file-download\x27);
   }
   function copy_error() {
      glpi_toast_error(__(\x27Unexpected error\x27));
      \$(\x27#export_dropdown_icon\x27).removeClass(\x27spinner-border spinner-border-sm\x27);
      \$(\x27#export_dropdown_icon\x27).addClass(\x27ti-file-download\x27);
   }

   \$(\x27#copy_names_to_clipboard\x27).click(function(e) {
      // Get target link
      const link = \$(e.currentTarget).prop(\x27href\x27);

      // Show loading indicator
      \$(\x27#export_dropdown_icon\x27).removeClass(\x27ti-file-download\x27);
      \$(\x27#export_dropdown_icon\x27).addClass(\x27spinner-border spinner-border-sm\x27);

      // Prevent link from working
      e.preventDefault();

      // Get data using ajax
      \$.get(link, function (data) {
         navigator.clipboard.writeText(data).then(copy_success, copy_error);
      }).fail(copy_error);
   });

    // show massive actions control when at least one checkbox is checked
    \$(\x27.massive_action_checkbox\x27).change(function() {
        const nb_ma_checked = \$(\x27.massive_action_checkbox:checked\x27).length;

        if (nb_ma_checked === 0) {
            \$(\x27.massiveactions-control\x27)
                .removeClass(\x27animate__slideInLeft\x27)
                .addClass(\x27animate__slideOutLeft\x27)
        } else {
            \$(\x27.massiveactions-control\x27)
                .removeClass(\x27d-none\x27)
                .removeClass(\x27animate__slideOutLeft\x27)
                .addClass(\x27animate__slideInLeft\x27);
        }
    });
    // also call it on page load
    \$(\x27.massive_action_checkbox\x27).trigger(\x27change\x27);
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
        return "components/search/controls.html.twig";
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
        return array (  727 => 360,  720 => 356,  716 => 355,  710 => 352,  687 => 331,  682 => 328,  675 => 324,  671 => 323,  666 => 320,  660 => 317,  654 => 314,  651 => 313,  649 => 312,  644 => 310,  639 => 308,  634 => 306,  629 => 304,  624 => 302,  619 => 300,  614 => 298,  609 => 296,  604 => 294,  599 => 292,  595 => 291,  589 => 288,  584 => 286,  579 => 284,  574 => 282,  569 => 280,  564 => 278,  559 => 276,  554 => 274,  551 => 273,  549 => 272,  545 => 270,  541 => 269,  536 => 268,  534 => 267,  533 => 266,  532 => 265,  531 => 264,  530 => 263,  529 => 262,  522 => 258,  516 => 256,  514 => 255,  511 => 254,  504 => 250,  501 => 249,  499 => 248,  496 => 247,  491 => 244,  489 => 243,  488 => 242,  487 => 241,  483 => 240,  476 => 236,  472 => 234,  470 => 233,  466 => 231,  456 => 224,  452 => 223,  446 => 220,  440 => 217,  435 => 215,  432 => 214,  429 => 213,  427 => 212,  424 => 211,  415 => 205,  410 => 203,  404 => 200,  399 => 198,  396 => 197,  394 => 196,  391 => 195,  382 => 189,  377 => 187,  371 => 184,  366 => 182,  363 => 181,  361 => 180,  358 => 179,  346 => 172,  341 => 170,  337 => 169,  328 => 162,  326 => 161,  315 => 155,  310 => 153,  306 => 152,  297 => 145,  295 => 144,  292 => 143,  290 => 142,  287 => 141,  285 => 140,  281 => 138,  278 => 137,  274 => 135,  272 => 132,  269 => 131,  266 => 130,  264 => 129,  261 => 128,  258 => 127,  255 => 126,  253 => 125,  249 => 123,  242 => 120,  240 => 119,  237 => 118,  233 => 116,  229 => 114,  227 => 113,  222 => 111,  216 => 108,  211 => 107,  209 => 106,  201 => 102,  198 => 101,  196 => 100,  192 => 98,  188 => 96,  186 => 95,  181 => 93,  178 => 92,  172 => 90,  166 => 87,  161 => 86,  159 => 85,  151 => 81,  148 => 80,  146 => 79,  140 => 76,  136 => 75,  131 => 74,  129 => 73,  126 => 72,  123 => 71,  120 => 70,  117 => 69,  114 => 68,  112 => 67,  108 => 65,  105 => 64,  101 => 62,  97 => 60,  95 => 55,  93 => 54,  89 => 53,  82 => 49,  79 => 48,  76 => 47,  73 => 46,  71 => 45,  67 => 43,  65 => 42,  61 => 40,  57 => 38,  55 => 37,  52 => 36,  50 => 35,  47 => 34,  45 => 33,  42 => 32,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "components/search/controls.html.twig", "/var/www/html/glpi/templates/components/search/controls.html.twig");
    }
}
