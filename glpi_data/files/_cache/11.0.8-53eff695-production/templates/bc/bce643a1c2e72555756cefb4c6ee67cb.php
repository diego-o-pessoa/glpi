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

/* components/notepad/form.html.twig */
class __TwigTemplate_3b2f3508aab885123c8daa263b26d226 extends Template
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
        $context["fields_options"] = ["full_width" => true, "is_horizontal" => false];
        // line 39
        yield "
<div class=\"d-flex mb-3 justify-content-between align-items-center\">
    ";
        // line 41
        if ((($tmp = ($context["canedit"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 42
            yield "        <button type=\"button\" class=\"btn btn-primary\" data-bs-toggle=\"modal\" data-bs-target=\"#new-note-form\" aria-label=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(_x("button", "Add a note"), "html", null, true);
            yield "\">
            <i class=\"ti ti-link\"></i>
            <span>";
            // line 44
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(_x("button", "Add a note"), "html", null, true);
            yield "</span>
        </button>
    ";
        } else {
            // line 47
            yield "        <span></span>
    ";
        }
        // line 49
        yield "
    ";
        // line 50
        if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["notes"] ?? null)) > 0)) {
            // line 51
            yield "        <small class=\"me-2\">
            <a href=\"#\" class=\"text-decoration-none text-secondary toggle-all-notes d-flex align-items-center gap-1\"
               style=\"cursor: pointer; transition: color 0.2s ease;\"
               onmouseover=\"this.style.color=\x27#0d6efd\x27\"
               onmouseout=\"this.style.color=\x27\x27\"
               data-expand-text=\"";
            // line 56
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Expand all"), "html", null, true);
            yield "\"
               data-collapse-text=\"";
            // line 57
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Collapse all"), "html", null, true);
            yield "\"
               data-expand-title=\"";
            // line 58
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Expand all"), "html", null, true);
            yield "\"
               data-collapse-title=\"";
            // line 59
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Collapse all"), "html", null, true);
            yield "\">
                <i class=\"ti ti-eye\"></i>
                <span>";
            // line 61
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Collapse all"), "html", null, true);
            yield "</span>
            </a>
        </small>
    ";
        }
        // line 65
        yield "</div>

";
        // line 67
        if ((($tmp = ($context["canedit"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 68
            yield "    <div class=\"my-3\">
        <div id=\"new-note-form\" class=\"modal fade\">
            <div class=\"modal-dialog modal-xl\">
                <div class=\"modal-content\">
                    <div class=\"modal-header\">
                        <h3 class=\"modal-title\">
                            <i class=\"ti ti-notes\"></i>
                            ";
            // line 75
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Add a note"), "html", null, true);
            yield "
                        </h3>
                        <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>
                    </div>

                    <form action=\"";
            // line 80
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["url"] ?? null), "html", null, true);
            yield "\" method=\"post\" autocomplete=\"off\" data-submit-once>
                        <div class=\"modal-body\">
                            <input type=\"hidden\" name=\"_glpi_csrf_token\" value=\"";
            // line 82
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Session::getNewCSRFToken(), "html", null, true);
            yield "\" />
                            <input type=\"hidden\" name=\"itemtype\" value=\"";
            // line 83
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["itemtype"] ?? null), "html", null, true);
            yield "\" />
                            <input type=\"hidden\" name=\"items_id\" value=\"";
            // line 84
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["items_id"] ?? null), "html", null, true);
            yield "\" />
                            ";
            // line 85
            yield $macros["fields"]->getTemplateForMacro("macro_textareaField", $context, 85, $this->getSourceContext())->macro_textareaField(...["content", "", __("Content"), Twig\Extension\CoreExtension::merge(            // line 89
($context["fields_options"] ?? null), ["enable_richtext" => true, "enable_fileupload" => true])]);
            // line 93
            yield "

                            ";
            // line 95
            if ((($context["itemtype"] ?? null) == "Entity")) {
                // line 96
                yield "                                ";
                yield $macros["fields"]->getTemplateForMacro("macro_sliderField", $context, 96, $this->getSourceContext())->macro_sliderField(...["visible_from_ticket", false, __("Visible on tickets"),                 // line 100
($context["fields_options"] ?? null)]);
                // line 101
                yield "
                            ";
            }
            // line 103
            yield "                        </div>

                        <div class=\"modal-footer\">
                            <button type=\"submit\" value=\"Add\" name=\"add\" class=\"btn btn-primary\">
                                <span>";
            // line 107
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Add"), "html", null, true);
            yield "</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
";
        }
        // line 116
        yield "
<div class=\"accordion\">
    ";
        // line 118
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["notes"] ?? null));
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
        foreach ($context['_seq'] as $context["_key"] => $context["note"]) {
            // line 119
            yield "        ";
            $context["id"] = (("note" . (($_v0 = $context["note"]) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0["id"] ?? null) : null)) . ($context["rand"] ?? null));
            // line 120
            yield "        <div class=\"accordion-item\">
            <div class=\"accordion-header\">
                <button
                    class=\"accordion-button d-flex align-self-center\"
                    type=\"button\"
                    data-bs-toggle=\"collapse\"
                    data-bs-target=\"#";
            // line 126
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["id"] ?? null), "html", null, true);
            yield "\"
                    aria-expanded=\"true\"
                    aria-controls=\"";
            // line 128
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["id"] ?? null), "html", null, true);
            yield "\"
                    data-testid=\"note-container\"
                >
                    <i class=\"ti ti-notes\"></i>
                    ";
            // line 132
            $context["title"] = ((("#" . (($_v1 = $context["note"]) && is_array($_v1) || $_v1 instanceof ArrayAccess ? ($_v1["id"] ?? null) : null)) . " - ") . $this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getFormattedDatetime((($_v2 = $context["note"]) && is_array($_v2) || $_v2 instanceof ArrayAccess ? ($_v2["date_creation"] ?? null) : null)));
            // line 133
            yield "                    ";
            if (((($_v3 = $context["note"]) && is_array($_v3) || $_v3 instanceof ArrayAccess ? ($_v3["date_mod"] ?? null) : null) != (($_v4 = $context["note"]) && is_array($_v4) || $_v4 instanceof ArrayAccess ? ($_v4["date_creation"] ?? null) : null))) {
                // line 134
                yield "                        ";
                $context["title"] = (((($context["title"] ?? null) . " (") . Twig\Extension\CoreExtension::sprintf(__("Last update on %s"), $this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getFormattedDatetime((($_v5 = $context["note"]) && is_array($_v5) || $_v5 instanceof ArrayAccess ? ($_v5["date_mod"] ?? null) : null)))) . ")");
                // line 135
                yield "                    ";
            }
            // line 136
            yield "                    <span>";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["title"] ?? null), "html", null, true);
            yield "</span>
                </button>
            </div>

            <div id=\"";
            // line 140
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["id"] ?? null), "html", null, true);
            yield "\" class=\"accordion-collapse collapse show\">
                <div class=\"accordion-body pt-0\">
                    <form action=\"";
            // line 142
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["url"] ?? null), "html", null, true);
            yield "\" method=\"post\" autocomplete=\"off\" data-submit-once>
                        <input type=\"hidden\" name=\"_glpi_csrf_token\" value=\"";
            // line 143
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Session::getNewCSRFToken(), "html", null, true);
            yield "\" />
                        <input type=\"hidden\" name=\"id\" value=\"";
            // line 144
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v6 = $context["note"]) && is_array($_v6) || $_v6 instanceof ArrayAccess ? ($_v6["id"] ?? null) : null), "html", null, true);
            yield "\" />
                        <div class=\"d-flex justify-content-between mb-2\">
                            <span class=\"d-inline-flex creator\">
                                ";
            // line 147
            yield Twig\Extension\CoreExtension::include($this->env, $context, "components/itilobject/timeline/timeline_item_header_badges.html.twig", ["users_id" => (($_v7 =             // line 148
$context["note"]) && is_array($_v7) || $_v7 instanceof ArrayAccess ? ($_v7["users_id"] ?? null) : null), "date_creation" => (($_v8 =             // line 149
$context["note"]) && is_array($_v8) || $_v8 instanceof ArrayAccess ? ($_v8["date_creation"] ?? null) : null), "date_mod" => (($_v9 =             // line 150
$context["note"]) && is_array($_v9) || $_v9 instanceof ArrayAccess ? ($_v9["date_mod"] ?? null) : null), "users_id_editor" => (($_v10 =             // line 151
$context["note"]) && is_array($_v10) || $_v10 instanceof ArrayAccess ? ($_v10["users_id_lastupdater"] ?? null) : null), "anchor" => (("Notepad" . "_") . (($_v11 =             // line 152
$context["note"]) && is_array($_v11) || $_v11 instanceof ArrayAccess ? ($_v11["id"] ?? null) : null)), "user_object" => null], false);
            // line 154
            yield "
                            </span>

                            ";
            // line 157
            if ((($tmp = ($context["canedit"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 158
                yield "                                <span>
                                    <button
                                        class=\"btn btn-sm btn-ghost-danger delete-note\"
                                        name=\"purge\"
                                        type=\"submit\"
                                        value=\"1\"
                                        onclick=\"return confirm(\x27";
                // line 164
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Confirm the final deletion?"), "js"), "html", null, true);
                yield "\x27);\"
                                        data-id=\"";
                // line 165
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["id"] ?? null), "html", null, true);
                yield "\"
                                        aria-label=\"";
                // line 166
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Delete"), "html", null, true);
                yield "\"
                                    >
                                        <i class=\"ti ti-trash\"></i>
                                        <span>";
                // line 169
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Delete"), "html", null, true);
                yield "</span>
                                    </button>

                                    <button
                                        class=\"btn btn-sm btn-ghost-secondary edit-note\"
                                        type=\"button\"
                                        data-id=\"";
                // line 175
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["id"] ?? null), "html", null, true);
                yield "\"
                                        data-bs-toggle=\"modal\"
                                        data-bs-target=\"#edit-";
                // line 177
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["id"] ?? null), "html", null, true);
                yield "\"
                                        aria-label=\"";
                // line 178
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Edit"), "html", null, true);
                yield "\"
                                    >
                                        <i class=\"ti ti-edit\"></i>
                                        <span>";
                // line 181
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Edit"), "html", null, true);
                yield "</span>
                                    </button>
                                </span>
                            ";
            }
            // line 185
            yield "                        </div>

                        <div
                            class=\"rich_text_container\"
                            id=\"contentread";
            // line 189
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["id"] ?? null), "html", null, true);
            yield "\"
                            data-testid=\"note-content\"
                        >
                            ";
            // line 192
            yield $this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getSafeHtml((($_v12 = $context["note"]) && is_array($_v12) || $_v12 instanceof ArrayAccess ? ($_v12["content"] ?? null) : null));
            yield "
                        </div>

                        ";
            // line 195
            yield Twig\Extension\CoreExtension::include($this->env, $context, "components/itilobject/timeline/sub_documents.html.twig", ["item" => $this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItem((($_v13 =             // line 196
$context["note"]) && is_array($_v13) || $_v13 instanceof ArrayAccess ? ($_v13["itemtype"] ?? null) : null), (($_v14 = $context["note"]) && is_array($_v14) || $_v14 instanceof ArrayAccess ? ($_v14["items_id"] ?? null) : null)), "entry" =>             // line 197
$context["note"]]);
            // line 198
            yield "

                    </form>

                    <div id=\"edit-";
            // line 202
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["id"] ?? null), "html", null, true);
            yield "\" class=\"modal fade\">
                        <div class=\"modal-dialog modal-xl\">
                            <div class=\"modal-content\">
                                <form action=\"";
            // line 205
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["url"] ?? null), "html", null, true);
            yield "\" method=\"post\" autocomplete=\"off\" data-submit-once>
                                    <input type=\"hidden\" name=\"_glpi_csrf_token\" value=\"";
            // line 206
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Session::getNewCSRFToken(), "html", null, true);
            yield "\" />
                                    <input type=\"hidden\" name=\"id\" value=\"";
            // line 207
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v15 = $context["note"]) && is_array($_v15) || $_v15 instanceof ArrayAccess ? ($_v15["id"] ?? null) : null), "html", null, true);
            yield "\" />

                                    <div class=\"modal-header\">
                                        <h3 class=\"modal-title\">
                                            <i class=\"ti ti-notes\"></i>
                                            ";
            // line 212
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Edit a note"), "html", null, true);
            yield "
                                        </h3>
                                        <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>
                                    </div>

                                    <div class=\"modal-body\">
                                        ";
            // line 218
            yield $macros["fields"]->getTemplateForMacro("macro_textareaField", $context, 218, $this->getSourceContext())->macro_textareaField(...["content", (($_v16 =             // line 220
$context["note"]) && is_array($_v16) || $_v16 instanceof ArrayAccess ? ($_v16["content"] ?? null) : null), __("Content"), Twig\Extension\CoreExtension::merge(            // line 222
($context["fields_options"] ?? null), ["enable_richtext" => true, "enable_fileupload" => true])]);
            // line 226
            yield "
                                        <br>

                                        ";
            // line 229
            if ((($context["itemtype"] ?? null) == "Entity")) {
                // line 230
                yield "                                            ";
                yield $macros["fields"]->getTemplateForMacro("macro_sliderField", $context, 230, $this->getSourceContext())->macro_sliderField(...["visible_from_ticket", (($_v17 =                 // line 232
$context["note"]) && is_array($_v17) || $_v17 instanceof ArrayAccess ? ($_v17["visible_from_ticket"] ?? null) : null), __("Visible on tickets"),                 // line 234
($context["fields_options"] ?? null)]);
                // line 235
                yield "
                                        ";
            }
            // line 237
            yield "                                    </div>

                                    <div class=\"modal-footer\">
                                        <button class=\"btn btn-outline-danger\" type=\"submit\" name=\"purge\" value=\"1\"
                                                onclick=\"return confirm(\x27";
            // line 241
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Confirm the final deletion?"), "js"), "html", null, true);
            yield "\x27);\"
                                                data-bs-toggle=\"tooltip\" data-bs-position=\"top\"
                                                title=\"";
            // line 243
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(_x("button", "Delete permanently"), "html", null, true);
            yield "\">
                                            <i class=\"ti ti-trash\"></i>
                                        </button>

                                        <button
                                            type=\"submit\"
                                            value=\"Update\"
                                            name=\"update\"
                                            class=\"btn btn-primary\"
                                            aria-label=\"";
            // line 252
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Update"), "html", null, true);
            yield "\"
                                        >
                                            <i class=\"ti ti-device-floppy\"></i>
                                            <span>";
            // line 255
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Update"), "html", null, true);
            yield "</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
        unset($context['_seq'], $context['_key'], $context['note'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 266
        yield "</div>

<script type=\"module\">
    /**
     * Toggle all accordions in a notepad container with Bootstrap transitions
     */
    \$(\x27.toggle-all-notes\x27).on(\x27click\x27, function(e) {
        e.preventDefault();

        var \$toggle = \$(this);
        var \$accordion = \$toggle.closest(\x27.tab-pane\x27).find(\x27.accordion\x27);

        if (!\$accordion.length) {
            console.warn(\x27Accordion container not found\x27);
            return;
        }

        var \$text = \$toggle.find(\x27span\x27);
        var \$icon = \$toggle.find(\x27i\x27);
        var expand_text = \$toggle.data(\x27expand-text\x27);
        var collapse_text = \$toggle.data(\x27collapse-text\x27);
        var expand_title = \$toggle.data(\x27expand-title\x27);
        var collapse_title = \$toggle.data(\x27collapse-title\x27);
        var is_expanding = \$text.text().includes(expand_text);

        \$accordion.find(\x27.accordion-collapse\x27).each(function() {
            var collapse = bootstrap.Collapse.getOrCreateInstance(this, { toggle: false });

            if (is_expanding) {
                collapse.show();
            } else {
                collapse.hide();
            }
        });

        if (is_expanding) {
            \$text.text(collapse_text);
            \$toggle.attr(\x27title\x27, collapse_title);
            \$icon.attr(\x27class\x27, \x27ti ti-eye\x27);
        } else {
            \$text.text(expand_text);
            \$toggle.attr(\x27title\x27, expand_title);
            \$icon.attr(\x27class\x27, \x27ti ti-eye-off\x27);
        }
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
        return "components/notepad/form.html.twig";
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
        return array (  467 => 266,  442 => 255,  436 => 252,  424 => 243,  419 => 241,  413 => 237,  409 => 235,  407 => 234,  406 => 232,  404 => 230,  402 => 229,  397 => 226,  395 => 222,  394 => 220,  393 => 218,  384 => 212,  376 => 207,  372 => 206,  368 => 205,  362 => 202,  356 => 198,  354 => 197,  353 => 196,  352 => 195,  346 => 192,  340 => 189,  334 => 185,  327 => 181,  321 => 178,  317 => 177,  312 => 175,  303 => 169,  297 => 166,  293 => 165,  289 => 164,  281 => 158,  279 => 157,  274 => 154,  272 => 152,  271 => 151,  270 => 150,  269 => 149,  268 => 148,  267 => 147,  261 => 144,  257 => 143,  253 => 142,  248 => 140,  240 => 136,  237 => 135,  234 => 134,  231 => 133,  229 => 132,  222 => 128,  217 => 126,  209 => 120,  206 => 119,  189 => 118,  185 => 116,  173 => 107,  167 => 103,  163 => 101,  161 => 100,  159 => 96,  157 => 95,  153 => 93,  151 => 89,  150 => 85,  146 => 84,  142 => 83,  138 => 82,  133 => 80,  125 => 75,  116 => 68,  114 => 67,  110 => 65,  103 => 61,  98 => 59,  94 => 58,  90 => 57,  86 => 56,  79 => 51,  77 => 50,  74 => 49,  70 => 47,  64 => 44,  58 => 42,  56 => 41,  52 => 39,  50 => 35,  47 => 34,  45 => 33,  42 => 32,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "components/notepad/form.html.twig", "/var/www/html/glpi/templates/components/notepad/form.html.twig");
    }
}
