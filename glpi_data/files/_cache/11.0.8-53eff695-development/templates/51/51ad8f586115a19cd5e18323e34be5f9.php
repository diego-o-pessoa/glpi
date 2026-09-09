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

/* layout/parts/profile_selector.html.twig */
class __TwigTemplate_65bf4fe812a4677efa10a61f0a5e7620 extends Template
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
        $context["rand"] = Twig\Extension\CoreExtension::random($this->env->getCharset());
        // line 34
        yield "
<div class=\"dropdown dropstart\">
    <button
        class=\"dropdown-item dropdown-toggle\"
        type=\"button\"
        data-bs-toggle=\"dropdown\"
        aria-haspopup=\"true\"
        aria-expanded=\"false\"
        aria-label=\"";
        // line 42
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Change profile"), "html", null, true);
        yield "\"
    >
        <i class=\"ti ti-user-check\"></i>
        ";
        // line 45
        yield (((CoreExtension::getAttribute($this->env, $this->source, $this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpiactiveprofile"), "name", [], "array", true, true, false, 45) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, $this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpiactiveprofile"), "name", [], "array", false, false, false, 45)))) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpiactiveprofile"), "name", [], "array", false, false, false, 45), "html", null, true)) : (""));
        yield "
    </button>
    <div class=\"dropdown-menu\" data-bs-popper=\"none\">
        <span class=\"dropdown-header\">";
        // line 48
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Profiles"), "html", null, true);
        yield "</span>
        ";
        // line 49
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable($this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpiprofiles"));
        foreach ($context['_seq'] as $context["profile_id"] => $context["profile"]) {
            // line 50
            yield "            ";
            $context["is_active"] = ($context["profile_id"] == (((CoreExtension::getAttribute($this->env, $this->source, $this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpiactiveprofile"), "id", [], "array", true, true, false, 50) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, $this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpiactiveprofile"), "id", [], "array", false, false, false, 50)))) ? (CoreExtension::getAttribute($this->env, $this->source, $this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpiactiveprofile"), "id", [], "array", false, false, false, 50)) : (0)));
            // line 51
            yield "            <form action=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path("/Session/ChangeProfile"), "html", null, true);
            yield "\" method=\"POST\">
                <button
                    class=\"dropdown-item ";
            // line 53
            yield (((($tmp = (isset($context["is_active"]) || array_key_exists("is_active", $context) ? $context["is_active"] : (function () { throw new RuntimeError('Variable "is_active" does not exist.', 53, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("active") : (""));
            yield "\"
                    type=\"submit\"
                >
                    <i class=\"ti ti-user-check\"></i>
                    ";
            // line 57
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["profile"], "name", [], "array", false, false, false, 57), "html", null, true);
            yield "
                </button>
                <input type=\"hidden\" name=\"id\" value=\"";
            // line 59
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["profile_id"], "html", null, true);
            yield "\" />
                <input type=\"hidden\" name=\"_glpi_csrf_token\" value=\"";
            // line 60
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Session::getNewCSRFToken(), "html", null, true);
            yield "\" />
            </form>
        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['profile_id'], $context['profile'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 63
        yield "    </div>
</div>

";
        // line 66
        $context["current_entity"] = $this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpiactive_entity_name");
        // line 67
        $context["current_entity_short"] = $this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpiactive_entity_shortname");
        // line 68
        if (((isset($context["current_entity"]) || array_key_exists("current_entity", $context) ? $context["current_entity"] : (function () { throw new RuntimeError('Variable "current_entity" does not exist.', 68, $this->source); })()) != (isset($context["current_entity_short"]) || array_key_exists("current_entity_short", $context) ? $context["current_entity_short"] : (function () { throw new RuntimeError('Variable "current_entity_short" does not exist.', 68, $this->source); })()))) {
            // line 69
            yield "    ";
            $context["current_entity_short"] = ("... > " . (isset($context["current_entity_short"]) || array_key_exists("current_entity_short", $context) ? $context["current_entity_short"] : (function () { throw new RuntimeError('Variable "current_entity_short" does not exist.', 69, $this->source); })()));
        }
        // line 71
        if ((($tmp =  !Session::isMultiEntitiesMode()) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 72
            yield "    <span class=\"dropdown-item dropdown-item-text\" title=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["current_entity"]) || array_key_exists("current_entity", $context) ? $context["current_entity"] : (function () { throw new RuntimeError('Variable "current_entity" does not exist.', 72, $this->source); })()), "html", null, true);
            yield "\">
        <i class=\"ti ti-stack\"></i>
        ";
            // line 74
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $this->extensions['Twig\Extra\String\StringExtension']->createUnicodeString((isset($context["current_entity_short"]) || array_key_exists("current_entity_short", $context) ? $context["current_entity_short"] : (function () { throw new RuntimeError('Variable "current_entity_short" does not exist.', 74, $this->source); })())), "truncate", [35, "..."], "method", false, false, false, 74), "html", null, true);
            yield "
    </span>
";
        } else {
            // line 77
            yield "    <div class=\"dropdown dropstart\" id=\"entity-tree-dropdown-";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["rand"]) || array_key_exists("rand", $context) ? $context["rand"] : (function () { throw new RuntimeError('Variable "rand" does not exist.', 77, $this->source); })()), "html", null, true);
            yield "\">
        <a href=\"#\" class=\"dropdown-item dropdown-toggle entity-dropdown-toggle\" data-bs-toggle=\"dropdown\" data-bs-auto-close=\"outside\" title=\"";
            // line 78
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["current_entity"]) || array_key_exists("current_entity", $context) ? $context["current_entity"] : (function () { throw new RuntimeError('Variable "current_entity" does not exist.', 78, $this->source); })()), "html", null, true);
            yield "\" aria-label=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Select the desired entity"), "html", null, true);
            yield "\">
            <i class=\"ti ti-stack\"></i>
            ";
            // line 80
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $this->extensions['Twig\Extra\String\StringExtension']->createUnicodeString((isset($context["current_entity_short"]) || array_key_exists("current_entity_short", $context) ? $context["current_entity_short"] : (function () { throw new RuntimeError('Variable "current_entity_short" does not exist.', 80, $this->source); })())), "truncate", [35, "..."], "method", false, false, false, 80), "html", null, true);
            yield "
        </a>
        <div class=\"dropdown-menu p-3 overflow-y-auto\" style=\"max-height: calc(100vh - 8rem)\" data-testid=\"entity-menu-dropdown\">
            <h3>";
            // line 83
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Select the desired entity"), "html", null, true);
            yield "</h3>

            <div class=\"alert alert-info d-block\" role=\"alert\">
                ";
            // line 86
            $context["shortcut"] = __("Ctrl + Alt + E");
            // line 87
            yield "                ";
            if (((isset($context["platform"]) || array_key_exists("platform", $context) ? $context["platform"] : (function () { throw new RuntimeError('Variable "platform" does not exist.', 87, $this->source); })()) == Twig\Extension\CoreExtension::constant("donatj\\UserAgent\\Platforms::MACINTOSH"))) {
                // line 88
                yield "                    ";
                $context["shortcut"] = __("⌥ (option) + ⌘ (command) + E");
                // line 89
                yield "                ";
            }
            // line 90
            yield "                ";
            yield Twig\Extension\CoreExtension::sprintf(__("Tip: You can call this modal with %s keys combination"), (("<kbd>" . (isset($context["shortcut"]) || array_key_exists("shortcut", $context) ? $context["shortcut"] : (function () { throw new RuntimeError('Variable "shortcut" does not exist.', 90, $this->source); })())) . "</kbd>"));
            yield "
            </div>

            ";
            // line 93
            $context["switch_to_full_structure_id"] = ("switch_to_full_structure_" . (isset($context["rand"]) || array_key_exists("rand", $context) ? $context["rand"] : (function () { throw new RuntimeError('Variable "rand" does not exist.', 93, $this->source); })()));
            // line 94
            yield "            <form
                id=\"";
            // line 95
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["switch_to_full_structure_id"]) || array_key_exists("switch_to_full_structure_id", $context) ? $context["switch_to_full_structure_id"] : (function () { throw new RuntimeError('Variable "switch_to_full_structure_id" does not exist.', 95, $this->source); })()), "html", null, true);
            yield "\"
                method=\"POST\"
                action=\"";
            // line 97
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path("/Session/ChangeEntity"), "html", null, true);
            yield "\"
            >
                <input type=\"hidden\" name=\"full_structure\" value=\"true\">
                <input type=\"hidden\" name=\"_glpi_csrf_token\" value=\"";
            // line 100
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Session::getNewCSRFToken(), "html", null, true);
            yield "\" />
            </form>

            <form
                id=\"ch_ent_o_";
            // line 104
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["rand"]) || array_key_exists("rand", $context) ? $context["rand"] : (function () { throw new RuntimeError('Variable "rand" does not exist.', 104, $this->source); })()), "html", null, true);
            yield "\"
                method=\"POST\"
                action=\"";
            // line 106
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path("/Session/ChangeEntity"), "html", null, true);
            yield "\"
            >
                <input type=\"hidden\" name=\"is_recursive\" value=\"0\">
                <input type=\"hidden\" name=\"_glpi_csrf_token\" value=\"";
            // line 109
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Session::getNewCSRFToken(), "html", null, true);
            yield "\" />
            </form>

            <form
                id=\"ch_ent_r_";
            // line 113
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["rand"]) || array_key_exists("rand", $context) ? $context["rand"] : (function () { throw new RuntimeError('Variable "rand" does not exist.', 113, $this->source); })()), "html", null, true);
            yield "\"
                method=\"POST\"
                action=\"";
            // line 115
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path("/Session/ChangeEntity"), "html", null, true);
            yield "\"
            >
                <input type=\"hidden\" name=\"is_recursive\" value=\"1\">
                <input type=\"hidden\" name=\"_glpi_csrf_token\" value=\"";
            // line 118
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Session::getNewCSRFToken(), "html", null, true);
            yield "\" />
            </form>

            <div class=\"input-group\">
                <input type=\"text\" class=\"form-control\" name=\"entsearchtext\" id=\"entsearchtext";
            // line 122
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["rand"]) || array_key_exists("rand", $context) ? $context["rand"] : (function () { throw new RuntimeError('Variable "rand" does not exist.', 122, $this->source); })()), "html", null, true);
            yield "\"
                        placeholder=\"";
            // line 123
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Search entity"), "html", null, true);
            yield "\" autocomplete=\"off\">
                <button id=\"entsearchsubmit";
            // line 124
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["rand"]) || array_key_exists("rand", $context) ? $context["rand"] : (function () { throw new RuntimeError('Variable "rand" does not exist.', 124, $this->source); })()), "html", null, true);
            yield "\" type=\"submit\" class=\"btn btn-icon btn-primary\" title=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Search"), "html", null, true);
            yield "\"
                        data-bs-toggle=\"tooltip\" data-bs-placement=\"top\">
                    <i class=\"ti ti-search\"></i>
                </button>
                <a class=\"btn btn-icon btn-outline-secondary\" href=\"#\" id=\"entsearchtext";
            // line 128
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["rand"]) || array_key_exists("rand", $context) ? $context["rand"] : (function () { throw new RuntimeError('Variable "rand" does not exist.', 128, $this->source); })()), "html", null, true);
            yield "_clear\"
                    title=\"";
            // line 129
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Clear search"), "html", null, true);
            yield "\" data-bs-toggle=\"tooltip\" data-bs-placement=\"top\">
                        <i class=\"ti ti-x\"></i>
                </a>
                <button
                    class=\"btn btn-secondary\"
                    title=\"";
            // line 134
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Select all"), "html", null, true);
            yield "\"
                    data-bs-toggle=\"tooltip\"
                    data-bs-placement=\"top\"
                    form=\"";
            // line 137
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["switch_to_full_structure_id"]) || array_key_exists("switch_to_full_structure_id", $context) ? $context["switch_to_full_structure_id"] : (function () { throw new RuntimeError('Variable "switch_to_full_structure_id" does not exist.', 137, $this->source); })()), "html", null, true);
            yield "\"
                    type=\"submit\"
                >
                    <i class=\"ti ti-eye\"></i>
                </button>
            </div>

            <div class=\"fancytree-grid-container flexbox-item-grow entity_tree overflow-x-auto\">
                <table id=\"tree_entity";
            // line 145
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["rand"]) || array_key_exists("rand", $context) ? $context["rand"] : (function () { throw new RuntimeError('Variable "rand" does not exist.', 145, $this->source); })()), "html", null, true);
            yield "\" aria-label=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Entity tree"), "html", null, true);
            yield "\">
                <colgroup>
                    <col></col>
                </colgroup>
                <thead>
                    <tr>
                        <th class=\"parent-path\"></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                </table>
                <div id=\"verticalScrollbar-";
            // line 157
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["rand"]) || array_key_exists("rand", $context) ? $context["rand"] : (function () { throw new RuntimeError('Variable "rand" does not exist.', 157, $this->source); })()), "html", null, true);
            yield "\" class=\"position-absolute overflow-auto\" style=\"height:100%; width: 16px; top: 0; right: 0;\">
                    <div class=\"fake-scrollbar-inner\" style=\"height: 100%; width: 100%;\">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type=\"text/javascript\">
    \$(function() {
        let block_fake_scrollbar_event = false;
        var initTree";
            // line 168
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["rand"]) || array_key_exists("rand", $context) ? $context["rand"] : (function () { throw new RuntimeError('Variable "rand" does not exist.', 168, $this->source); })()), "html", null, true);
            yield " = function() {
            if (\$.ui.fancytree.getTree(\"#tree_entity";
            // line 169
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["rand"]) || array_key_exists("rand", $context) ? $context["rand"] : (function () { throw new RuntimeError('Variable "rand" does not exist.', 169, $this->source); })()), "html", null, true);
            yield "\") !== null) {
                return;
            }

            \$(\x27#tree_entity";
            // line 173
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["rand"]) || array_key_exists("rand", $context) ? $context["rand"] : (function () { throw new RuntimeError('Variable "rand" does not exist.', 173, $this->source); })()), "html", null, true);
            yield "\x27).fancytree({
                // load plugins
                extensions: [\x27filter\x27, \x27glyph\x27, \x27grid\x27],

                // Scroll node into visible area, when focused by keyboard
                autoScroll: true,

                // enable font-awesome icons
                glyph: {
                    preset: \"awesome5\",
                    map: {}
                },

                // enable virtual dom, it requires the grid (table extension) plugin
                table: {
                    indentation: 20,       // indent 20px per node level
                    nodeColumnIdx: 0,      // render the node title into the 1st column
                    mergeStatusColumns: false,
                },
                grid: {
                    mergeStatusColumns: false,
                },
                viewport: {
                    enabled: true,
                    count: 15, // number of items to display at once
                },

                // translate strings
                strings: {
                    loading: __(\"Loading...\"),
                    loadError: __(\"Unexpected error.\"),
                    moreData: __(\"More...\"),
                    noData: __(\"No entity found\")
                },

                // load data by ajax
                source: {
                    url:  \x27";
            // line 210
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\RoutingExtension']->path(("/ajax/entitytreesons.php?rand=" . (isset($context["rand"]) || array_key_exists("rand", $context) ? $context["rand"] : (function () { throw new RuntimeError('Variable "rand" does not exist.', 210, $this->source); })()))), "html", null, true);
            yield "\x27,
                    cache: false
                },

                // filter plugin options
                filter: {
                    mode: \"hide\", // remove unmatched nodes
                    autoExpand: true, // if results found in children, auto-expand parent
                    nodata: \x27";
            // line 218
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("No entity found"), "html", null, true);
            yield "\x27, // message when no data found
                    highlight: false, // do not highlight matches by wrapping inside tags (when true, this strip the a tag)
                    counter: false, // do not show counters when filtering entity tree
                },

                // update scrollbar when viewport is updated
                updateViewport: function(event, data) {
                    const fake_scrollbar = \$(\"#verticalScrollbar-";
            // line 225
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["rand"]) || array_key_exists("rand", $context) ? $context["rand"] : (function () { throw new RuntimeError('Variable "rand" does not exist.', 225, $this->source); })()), "html", null, true);
            yield "\");
                    const item_height = \$(data.tree.tbody).find(\x27tr\x27).height();
                    block_fake_scrollbar_event = true;
                    fake_scrollbar.find(\x27.fake-scrollbar-inner\x27).height(item_height * data.tree.visibleNodeList.length);
                    fake_scrollbar.scrollTop(item_height * data.tree.viewport.start);

                    initTooltips();
                },

                // update toolips on node expand
                expand: function(event, data) {
                    initTooltips();
                },
            });
        };

        //Sync viewport with fake scrollbar
        \$(\"#verticalScrollbar-";
            // line 242
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["rand"]) || array_key_exists("rand", $context) ? $context["rand"] : (function () { throw new RuntimeError('Variable "rand" does not exist.', 242, $this->source); })()), "html", null, true);
            yield "\").on(\x27scroll\x27, function(e) {
            if (block_fake_scrollbar_event) {
                block_fake_scrollbar_event = false;
                return;
            }
            const fake_scrollbar = \$(e.target);
            const item_height = fake_scrollbar.find(\x27.fake-scrollbar-inner\x27).height() / \$.ui.fancytree.getTree(\"#tree_entity";
            // line 248
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["rand"]) || array_key_exists("rand", $context) ? $context["rand"] : (function () { throw new RuntimeError('Variable "rand" does not exist.', 248, $this->source); })()), "html", null, true);
            yield "\").visibleNodeList.length;
            const new_start = Math.round(fake_scrollbar.scrollTop() / item_height);
            \$.ui.fancytree.getTree(\"#tree_entity";
            // line 250
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["rand"]) || array_key_exists("rand", $context) ? $context["rand"] : (function () { throw new RuntimeError('Variable "rand" does not exist.', 250, $this->source); })()), "html", null, true);
            yield "\").setViewport({
                start: new_start,
            });
        });

      // init Entities tree only when user ask for it (when dropdown is opened)
        document.getElementById(\x27entity-tree-dropdown-";
            // line 256
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["rand"]) || array_key_exists("rand", $context) ? $context["rand"] : (function () { throw new RuntimeError('Variable "rand" does not exist.', 256, $this->source); })()), "html", null, true);
            yield "\x27)
            .addEventListener(\x27show.bs.dropdown\x27, function (event) {
                initTree";
            // line 258
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["rand"]) || array_key_exists("rand", $context) ? $context["rand"] : (function () { throw new RuntimeError('Variable "rand" does not exist.', 258, $this->source); })()), "html", null, true);
            yield "();
            });

        var searchTree = function() {
            var search_text = \$(\"#entsearchtext";
            // line 262
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["rand"]) || array_key_exists("rand", $context) ? $context["rand"] : (function () { throw new RuntimeError('Variable "rand" does not exist.', 262, $this->source); })()), "html", null, true);
            yield "\").val();
            \$.ui.fancytree.getTree(\"#tree_entity";
            // line 263
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["rand"]) || array_key_exists("rand", $context) ? $context["rand"] : (function () { throw new RuntimeError('Variable "rand" does not exist.', 263, $this->source); })()), "html", null, true);
            yield "\").filterBranches(search_text);
        }

        \$(\x27#entsearchsubmit";
            // line 266
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["rand"]) || array_key_exists("rand", $context) ? $context["rand"] : (function () { throw new RuntimeError('Variable "rand" does not exist.', 266, $this->source); })()), "html", null, true);
            yield "\x27).click(function(event) {
            // cancel submit of entity search form
            event.preventDefault();

            searchTree();
        });

        \$(\x27#entsearchtext";
            // line 273
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["rand"]) || array_key_exists("rand", $context) ? $context["rand"] : (function () { throw new RuntimeError('Variable "rand" does not exist.', 273, $this->source); })()), "html", null, true);
            yield "\x27).keyup(function () {
            var inputsearch = \$(this);
            typewatch(function () {
                if (inputsearch.val().length >= 3) {
                searchTree();
                }
            }, 500);
        }).focus();

        \$(\x27#entsearchtext";
            // line 282
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["rand"]) || array_key_exists("rand", $context) ? $context["rand"] : (function () { throw new RuntimeError('Variable "rand" does not exist.', 282, $this->source); })()), "html", null, true);
            yield "_clear\x27).click(function () {
            \$(\x27#entsearchtext";
            // line 283
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["rand"]) || array_key_exists("rand", $context) ? $context["rand"] : (function () { throw new RuntimeError('Variable "rand" does not exist.', 283, $this->source); })()), "html", null, true);
            yield "\x27).val(\x27\x27);
            searchTree();
        });

        // when the shortcut for entity form is called
        hotkeys(\x27ctrl+alt+e, option+command+e\x27, async function(e) {
            e.stopPropagation();
            e.preventDefault();
            \$(\x27.user-menu-dropdown-toggle:visible\x27).dropdown(\x27show\x27);
            await new Promise(r => setTimeout(r, 100));
            \$(\x27.user-menu-dropdown-toggle:visible\x27).parent().find(\x27.entity-dropdown-toggle\x27).dropdown(\x27show\x27);
            \$(\x27input[name=entsearchtext]\x27).filter(\":visible\")[0].focus();
        });
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
        return "layout/parts/profile_selector.html.twig";
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
        return array (  481 => 283,  477 => 282,  465 => 273,  455 => 266,  449 => 263,  445 => 262,  438 => 258,  433 => 256,  424 => 250,  419 => 248,  410 => 242,  390 => 225,  380 => 218,  369 => 210,  329 => 173,  322 => 169,  318 => 168,  304 => 157,  287 => 145,  276 => 137,  270 => 134,  262 => 129,  258 => 128,  249 => 124,  245 => 123,  241 => 122,  234 => 118,  228 => 115,  223 => 113,  216 => 109,  210 => 106,  205 => 104,  198 => 100,  192 => 97,  187 => 95,  184 => 94,  182 => 93,  175 => 90,  172 => 89,  169 => 88,  166 => 87,  164 => 86,  158 => 83,  152 => 80,  145 => 78,  140 => 77,  134 => 74,  128 => 72,  126 => 71,  122 => 69,  120 => 68,  118 => 67,  116 => 66,  111 => 63,  102 => 60,  98 => 59,  93 => 57,  86 => 53,  80 => 51,  77 => 50,  73 => 49,  69 => 48,  63 => 45,  57 => 42,  47 => 34,  45 => 33,  42 => 32,);
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

{% set rand = random() %}

<div class=\"dropdown dropstart\">
    <button
        class=\"dropdown-item dropdown-toggle\"
        type=\"button\"
        data-bs-toggle=\"dropdown\"
        aria-haspopup=\"true\"
        aria-expanded=\"false\"
        aria-label=\"{{ __(\"Change profile\") }}\"
    >
        <i class=\"ti ti-user-check\"></i>
        {{ (session(\x27glpiactiveprofile\x27)[\x27name\x27] ?? \x27\x27) }}
    </button>
    <div class=\"dropdown-menu\" data-bs-popper=\"none\">
        <span class=\"dropdown-header\">{{ __(\x27Profiles\x27) }}</span>
        {% for profile_id, profile in session(\x27glpiprofiles\x27) %}
            {% set is_active = profile_id == (session(\x27glpiactiveprofile\x27)[\x27id\x27] ?? 0) %}
            <form action=\"{{ path(\x27/Session/ChangeProfile\x27) }}\" method=\"POST\">
                <button
                    class=\"dropdown-item {{ is_active ? \x27active\x27 : \x27\x27 }}\"
                    type=\"submit\"
                >
                    <i class=\"ti ti-user-check\"></i>
                    {{ profile[\x27name\x27] }}
                </button>
                <input type=\"hidden\" name=\"id\" value=\"{{ profile_id }}\" />
                <input type=\"hidden\" name=\"_glpi_csrf_token\" value=\"{{ csrf_token() }}\" />
            </form>
        {% endfor %}
    </div>
</div>

{% set current_entity = session(\x27glpiactive_entity_name\x27) %}
{% set current_entity_short = session(\x27glpiactive_entity_shortname\x27) %}
{% if current_entity != current_entity_short %}
    {% set current_entity_short = \x27... > \x27 ~ current_entity_short %}
{% endif %}
{% if not is_multi_entities_mode() %}
    <span class=\"dropdown-item dropdown-item-text\" title=\"{{ current_entity }}\">
        <i class=\"ti ti-stack\"></i>
        {{ current_entity_short|u.truncate(35, \x27...\x27) }}
    </span>
{% else %}
    <div class=\"dropdown dropstart\" id=\"entity-tree-dropdown-{{ rand }}\">
        <a href=\"#\" class=\"dropdown-item dropdown-toggle entity-dropdown-toggle\" data-bs-toggle=\"dropdown\" data-bs-auto-close=\"outside\" title=\"{{ current_entity }}\" aria-label=\"{{ __(\x27Select the desired entity\x27) }}\">
            <i class=\"ti ti-stack\"></i>
            {{ current_entity_short|u.truncate(35, \x27...\x27) }}
        </a>
        <div class=\"dropdown-menu p-3 overflow-y-auto\" style=\"max-height: calc(100vh - 8rem)\" data-testid=\"entity-menu-dropdown\">
            <h3>{{ __(\x27Select the desired entity\x27) }}</h3>

            <div class=\"alert alert-info d-block\" role=\"alert\">
                {% set shortcut = __(\x27Ctrl + Alt + E\x27) %}
                {% if platform == constant(\"donatj\\\\UserAgent\\\\Platforms::MACINTOSH\") %}
                    {% set shortcut = __(\x27⌥ (option) + ⌘ (command) + E\x27) %}
                {% endif %}
                {{ __(\"Tip: You can call this modal with %s keys combination\")|format(\x27<kbd>\x27 ~ shortcut ~ \x27</kbd>\x27)|raw }}
            </div>

            {% set switch_to_full_structure_id = \x27switch_to_full_structure_\x27 ~ rand %}
            <form
                id=\"{{ switch_to_full_structure_id }}\"
                method=\"POST\"
                action=\"{{ path(\x27/Session/ChangeEntity\x27) }}\"
            >
                <input type=\"hidden\" name=\"full_structure\" value=\"true\">
                <input type=\"hidden\" name=\"_glpi_csrf_token\" value=\"{{ csrf_token() }}\" />
            </form>

            <form
                id=\"ch_ent_o_{{ rand }}\"
                method=\"POST\"
                action=\"{{ path(\x27/Session/ChangeEntity\x27) }}\"
            >
                <input type=\"hidden\" name=\"is_recursive\" value=\"0\">
                <input type=\"hidden\" name=\"_glpi_csrf_token\" value=\"{{ csrf_token() }}\" />
            </form>

            <form
                id=\"ch_ent_r_{{ rand }}\"
                method=\"POST\"
                action=\"{{ path(\x27/Session/ChangeEntity\x27) }}\"
            >
                <input type=\"hidden\" name=\"is_recursive\" value=\"1\">
                <input type=\"hidden\" name=\"_glpi_csrf_token\" value=\"{{ csrf_token() }}\" />
            </form>

            <div class=\"input-group\">
                <input type=\"text\" class=\"form-control\" name=\"entsearchtext\" id=\"entsearchtext{{ rand }}\"
                        placeholder=\"{{ __(\x27Search entity\x27) }}\" autocomplete=\"off\">
                <button id=\"entsearchsubmit{{ rand }}\" type=\"submit\" class=\"btn btn-icon btn-primary\" title=\"{{ __(\x27Search\x27) }}\"
                        data-bs-toggle=\"tooltip\" data-bs-placement=\"top\">
                    <i class=\"ti ti-search\"></i>
                </button>
                <a class=\"btn btn-icon btn-outline-secondary\" href=\"#\" id=\"entsearchtext{{ rand }}_clear\"
                    title=\"{{ __(\"Clear search\") }}\" data-bs-toggle=\"tooltip\" data-bs-placement=\"top\">
                        <i class=\"ti ti-x\"></i>
                </a>
                <button
                    class=\"btn btn-secondary\"
                    title=\"{{ __(\x27Select all\x27) }}\"
                    data-bs-toggle=\"tooltip\"
                    data-bs-placement=\"top\"
                    form=\"{{ switch_to_full_structure_id }}\"
                    type=\"submit\"
                >
                    <i class=\"ti ti-eye\"></i>
                </button>
            </div>

            <div class=\"fancytree-grid-container flexbox-item-grow entity_tree overflow-x-auto\">
                <table id=\"tree_entity{{ rand }}\" aria-label=\"{{ __(\x27Entity tree\x27) }}\">
                <colgroup>
                    <col></col>
                </colgroup>
                <thead>
                    <tr>
                        <th class=\"parent-path\"></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                </table>
                <div id=\"verticalScrollbar-{{ rand }}\" class=\"position-absolute overflow-auto\" style=\"height:100%; width: 16px; top: 0; right: 0;\">
                    <div class=\"fake-scrollbar-inner\" style=\"height: 100%; width: 100%;\">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type=\"text/javascript\">
    \$(function() {
        let block_fake_scrollbar_event = false;
        var initTree{{ rand }} = function() {
            if (\$.ui.fancytree.getTree(\"#tree_entity{{ rand }}\") !== null) {
                return;
            }

            \$(\x27#tree_entity{{ rand }}\x27).fancytree({
                // load plugins
                extensions: [\x27filter\x27, \x27glyph\x27, \x27grid\x27],

                // Scroll node into visible area, when focused by keyboard
                autoScroll: true,

                // enable font-awesome icons
                glyph: {
                    preset: \"awesome5\",
                    map: {}
                },

                // enable virtual dom, it requires the grid (table extension) plugin
                table: {
                    indentation: 20,       // indent 20px per node level
                    nodeColumnIdx: 0,      // render the node title into the 1st column
                    mergeStatusColumns: false,
                },
                grid: {
                    mergeStatusColumns: false,
                },
                viewport: {
                    enabled: true,
                    count: 15, // number of items to display at once
                },

                // translate strings
                strings: {
                    loading: __(\"Loading...\"),
                    loadError: __(\"Unexpected error.\"),
                    moreData: __(\"More...\"),
                    noData: __(\"No entity found\")
                },

                // load data by ajax
                source: {
                    url:  \x27{{ path(\"/ajax/entitytreesons.php?rand=\" ~ rand) }}\x27,
                    cache: false
                },

                // filter plugin options
                filter: {
                    mode: \"hide\", // remove unmatched nodes
                    autoExpand: true, // if results found in children, auto-expand parent
                    nodata: \x27{{ __(\"No entity found\") }}\x27, // message when no data found
                    highlight: false, // do not highlight matches by wrapping inside tags (when true, this strip the a tag)
                    counter: false, // do not show counters when filtering entity tree
                },

                // update scrollbar when viewport is updated
                updateViewport: function(event, data) {
                    const fake_scrollbar = \$(\"#verticalScrollbar-{{ rand }}\");
                    const item_height = \$(data.tree.tbody).find(\x27tr\x27).height();
                    block_fake_scrollbar_event = true;
                    fake_scrollbar.find(\x27.fake-scrollbar-inner\x27).height(item_height * data.tree.visibleNodeList.length);
                    fake_scrollbar.scrollTop(item_height * data.tree.viewport.start);

                    initTooltips();
                },

                // update toolips on node expand
                expand: function(event, data) {
                    initTooltips();
                },
            });
        };

        //Sync viewport with fake scrollbar
        \$(\"#verticalScrollbar-{{ rand }}\").on(\x27scroll\x27, function(e) {
            if (block_fake_scrollbar_event) {
                block_fake_scrollbar_event = false;
                return;
            }
            const fake_scrollbar = \$(e.target);
            const item_height = fake_scrollbar.find(\x27.fake-scrollbar-inner\x27).height() / \$.ui.fancytree.getTree(\"#tree_entity{{ rand }}\").visibleNodeList.length;
            const new_start = Math.round(fake_scrollbar.scrollTop() / item_height);
            \$.ui.fancytree.getTree(\"#tree_entity{{ rand }}\").setViewport({
                start: new_start,
            });
        });

      // init Entities tree only when user ask for it (when dropdown is opened)
        document.getElementById(\x27entity-tree-dropdown-{{ rand }}\x27)
            .addEventListener(\x27show.bs.dropdown\x27, function (event) {
                initTree{{ rand }}();
            });

        var searchTree = function() {
            var search_text = \$(\"#entsearchtext{{ rand }}\").val();
            \$.ui.fancytree.getTree(\"#tree_entity{{ rand }}\").filterBranches(search_text);
        }

        \$(\x27#entsearchsubmit{{ rand }}\x27).click(function(event) {
            // cancel submit of entity search form
            event.preventDefault();

            searchTree();
        });

        \$(\x27#entsearchtext{{ rand }}\x27).keyup(function () {
            var inputsearch = \$(this);
            typewatch(function () {
                if (inputsearch.val().length >= 3) {
                searchTree();
                }
            }, 500);
        }).focus();

        \$(\x27#entsearchtext{{ rand }}_clear\x27).click(function () {
            \$(\x27#entsearchtext{{ rand }}\x27).val(\x27\x27);
            searchTree();
        });

        // when the shortcut for entity form is called
        hotkeys(\x27ctrl+alt+e, option+command+e\x27, async function(e) {
            e.stopPropagation();
            e.preventDefault();
            \$(\x27.user-menu-dropdown-toggle:visible\x27).dropdown(\x27show\x27);
            await new Promise(r => setTimeout(r, 100));
            \$(\x27.user-menu-dropdown-toggle:visible\x27).parent().find(\x27.entity-dropdown-toggle\x27).dropdown(\x27show\x27);
            \$(\x27input[name=entsearchtext]\x27).filter(\":visible\")[0].focus();
        });
    });
    </script>
{% endif %}
", "layout/parts/profile_selector.html.twig", "/var/www/html/glpi/templates/layout/parts/profile_selector.html.twig");
    }
}
