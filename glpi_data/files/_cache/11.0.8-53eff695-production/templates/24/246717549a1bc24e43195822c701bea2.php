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

/* components/itilobject/footer.html.twig */
class __TwigTemplate_2ae0406edef75ee3248789146f45ed7e extends Template
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
        $context["is_helpdesk"] = ($this->extensions['Glpi\Application\View\Extension\SessionExtension']->getCurrentInterface() == "helpdesk");
        // line 34
        $context["timeline_btns_cls"] = ($context["left_regular_cls"] ?? null);
        // line 35
        $context["form_btns_cls"] = (((($tmp = ($context["is_expanded"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (($context["right_expanded_cls"] ?? null)) : ("col-lg"));
        // line 36
        $context["timeline_btn_layout"] = $this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpitimeline_action_btn_layout");
        // line 37
        $context["switch_btn_cls"] = "ti ti-caret-left-filled";
        // line 38
        if ((($tmp = ($context["is_expanded"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 39
            yield "    ";
            $context["timeline_btns_cls"] = ($context["left_expanded_cls"] ?? null);
            // line 40
            yield "    ";
            $context["form_btns_cls"] = ($context["right_expanded_cls"] ?? null);
            // line 41
            yield "    ";
            $context["switch_btn_cls"] = "ti ti-caret-right filled";
        }
        // line 43
        yield "
<div class=\"mx-n2 mb-n2 itil-footer itil-footer p-0 border-top\" id=\"itil-footer\">
   <div class=\"buttons-bar d-flex py-2\">
      <div class=\"col ";
        // line 46
        yield (((($tmp =  !($context["is_helpdesk"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["timeline_btns_cls"] ?? null), "html", null, true)) : (""));
        yield " ps-3 timeline-buttons d-flex\">
         ";
        // line 47
        if ((($tmp =  !CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "isNewItem", [], "method", false, false, false, 47)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 48
            yield "            ";
            $context["main_actions_itemtypes"] = Twig\Extension\CoreExtension::filter($this->env, $this->env->hasExtension(\Twig\Extension\SandboxExtension::class) && $this->env->getExtension(\Twig\Extension\SandboxExtension::class)->isSandboxed($this->source), ($context["timeline_itemtypes"] ?? null), function ($__v__, $__k__) use ($context, $macros) { $context["v"] = $__v__; $context["k"] = $__k__; return ( !CoreExtension::getAttribute($this->env, $this->source, ($context["v"] ?? null), "hide_in_menu", [], "any", true, true, false, 48) || (CoreExtension::getAttribute($this->env, $this->source, ($context["v"] ?? null), "hide_in_menu", [], "any", false, false, false, 48) != true)); });
            // line 49
            yield "
            ";
            // line 50
            $context["default_action_data"] = Twig\Extension\CoreExtension::first($this->env->getCharset(), ($context["main_actions_itemtypes"] ?? null));
            // line 51
            yield "            ";
            $context["default_action"] = Twig\Extension\CoreExtension::first($this->env->getCharset(), Twig\Extension\CoreExtension::keys(($context["main_actions_itemtypes"] ?? null)));
            // line 52
            yield "            ";
            if ((CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "isNotSolved", [], "method", false, false, false, 52) && (($context["default_action_data"] ?? null) != false))) {
                // line 53
                yield "               ";
                if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["main_actions_itemtypes"] ?? null)) > 1)) {
                    // line 54
                    yield "                  ";
                    $context["btn_class"] = (((($context["timeline_btn_layout"] ?? null) == Twig\Extension\CoreExtension::constant("Config::TIMELINE_ACTION_BTN_SPLITTED"))) ? ("btn-splitted") : ("btn-group"));
                    // line 55
                    yield "                  <div class=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["btn_class"] ?? null), "html", null, true);
                    yield " me-2 main-actions\">
               ";
                } else {
                    // line 57
                    yield "                  ";
                    // line 58
                    yield "                  <div class=\"main-actions\" style=\"display:inline-flex\">
               ";
                }
                // line 60
                yield "                  <button
                     class=\"btn btn-primary answer-action ";
                // line 61
                yield (((($context["default_action"] ?? null) != "answer")) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(("action-" . ($context["default_action"] ?? null)), "html", null, true)) : (""));
                yield "\"
                     data-bs-toggle=\"collapse\"
                     data-bs-target=\"#new-";
                // line 63
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["default_action_data"] ?? null), "class", [], "any", false, false, false, 63), "html", null, true);
                yield "-block\"
                     type=\"button\"
                     aria-label=\"";
                // line 65
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["default_action_data"] ?? null), "label", [], "any", false, false, false, 65), "html", null, true);
                yield "\"
                  >
                     <i class=\"";
                // line 67
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["default_action_data"] ?? null), "icon", [], "any", false, false, false, 67), "html", null, true);
                yield "\"></i>
                     <span>";
                // line 68
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["default_action_data"] ?? null), "label", [], "any", false, false, false, 68), "html", null, true);
                yield "</span>
                  </button>

                  ";
                // line 71
                if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["main_actions_itemtypes"] ?? null)) > 1)) {
                    // line 72
                    yield "                     ";
                    if ((($context["timeline_btn_layout"] ?? null) == Twig\Extension\CoreExtension::constant("Config::TIMELINE_ACTION_BTN_SPLITTED"))) {
                        // line 73
                        yield "                        ";
                        $context['_parent'] = $context;
                        $context['_seq'] = CoreExtension::ensureTraversable(($context["main_actions_itemtypes"] ?? null));
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
                        foreach ($context['_seq'] as $context["action"] => $context["timeline_itemtype"]) {
                            // line 74
                            yield "                        ";
                            if ((CoreExtension::getAttribute($this->env, $this->source, $context["loop"], "index0", [], "any", false, false, false, 74) > 0)) {
                                // line 75
                                yield "                              <button class=\"btn btn-primary answer-action action-";
                                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["action"], "html", null, true);
                                yield "\" data-bs-toggle=\"collapse\" data-bs-target=\"#new-";
                                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["timeline_itemtype"], "class", [], "any", false, false, false, 75), "html", null, true);
                                yield "-block\">
                                 <i class=\"";
                                // line 76
                                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["timeline_itemtype"], "icon", [], "any", false, false, false, 76), "html", null, true);
                                yield "\"></i>
                                 <span>";
                                // line 77
                                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["timeline_itemtype"], "short_label", [], "any", false, false, false, 77), "html", null, true);
                                yield "</span>
                              </button>
                              ";
                            }
                            // line 80
                            yield "                        ";
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
                        unset($context['_seq'], $context['action'], $context['timeline_itemtype'], $context['_parent'], $context['loop']);
                        $context = array_intersect_key($context, $_parent) + $_parent;
                        // line 81
                        yield "                     ";
                    } else {
                        // line 82
                        yield "                        <button
                           type=\"button\"
                           class=\"btn btn-primary dropdown-toggle dropdown-toggle-split ";
                        // line 84
                        yield (((($context["default_action"] ?? null) != "answer")) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(("action-" . ($context["default_action"] ?? null)), "html", null, true)) : (""));
                        yield "\"
                           data-bs-toggle=\"dropdown\"
                           aria-expanded=\"false\"
                        >
                           <span class=\"visually-hidden\">";
                        // line 88
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("View other actions"), "html", null, true);
                        yield "</span>
                        </button>
                        <ul class=\"dropdown-menu\">
                              ";
                        // line 91
                        $context['_parent'] = $context;
                        $context['_seq'] = CoreExtension::ensureTraversable(($context["main_actions_itemtypes"] ?? null));
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
                        foreach ($context['_seq'] as $context["action"] => $context["timeline_itemtype"]) {
                            // line 92
                            yield "                                 ";
                            if ((CoreExtension::getAttribute($this->env, $this->source, $context["loop"], "index0", [], "any", false, false, false, 92) > 0)) {
                                // line 93
                                yield "                                 <li aria-label=\"";
                                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["timeline_itemtype"], "label", [], "any", false, false, false, 93), "html", null, true);
                                yield "\"><a class=\"dropdown-item action-";
                                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["action"], "html", null, true);
                                yield " answer-action\" href=\"#\"
                                    data-bs-toggle=\"collapse\" data-bs-target=\"#new-";
                                // line 94
                                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["timeline_itemtype"], "class", [], "any", false, false, false, 94), "html", null, true);
                                yield "-block\">
                                    <i class=\"";
                                // line 95
                                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["timeline_itemtype"], "icon", [], "any", false, false, false, 95), "html", null, true);
                                yield "\"></i>
                                    <span>";
                                // line 96
                                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["timeline_itemtype"], "label", [], "any", false, false, false, 96), "html", null, true);
                                yield "</span>
                                 </a></li>
                                 ";
                            }
                            // line 99
                            yield "                              ";
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
                        unset($context['_seq'], $context['action'], $context['timeline_itemtype'], $context['_parent'], $context['loop']);
                        $context = array_intersect_key($context, $_parent) + $_parent;
                        // line 100
                        yield "                        </ul>
                     ";
                    }
                    // line 102
                    yield "                  ";
                }
                // line 103
                yield "               </div>
            ";
            }
            // line 105
            yield "
            <ul class=\"legacy-timeline-actions\">
               ";
            // line 107
            yield ((array_key_exists("legacy_timeline_actions", $context)) ? (Twig\Extension\CoreExtension::default(($context["legacy_timeline_actions"] ?? null), "")) : (""));
            yield "
            </ul>

            ";
            // line 110
            if ((CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "canSolve", [], "method", false, false, false, 110) &&  !CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "checkRequiredFieldsFilled", [], "method", false, false, false, 110))) {
                // line 111
                yield "               <i class=\"ti ti-alert-triangle text-warning me-2 d-flex align-items-center\"
                  data-bs-toggle=\"tooltip\" data-bs-placement=\"top\"
                  title=\"";
                // line 113
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::sprintf(__("Solving this %s is not possible as one or more mandatory field is not filled"), CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "getTypeName", [1], "method", false, false, false, 113)), "html", null, true);
                yield "\"></i>
            ";
            }
            // line 115
            yield "
            <div class=\"ms-auto\"></div>

            ";
            // line 118
            if ((CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "canDeleteItem", [], "method", false, false, false, 118) && ($context["is_helpdesk"] ?? null))) {
                // line 119
                yield "               <button class=\"btn btn-ghost-danger me-2\" type=\"submit\" name=\"delete\" form=\"itil-form\">
                  <i class=\"ti ti-trash me-1\"></i>
                  <span>";
                // line 121
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Cancel ticket"), "html", null, true);
                yield "</span>
               </button>
            ";
            }
            // line 124
            yield "
            ";
            // line 125
            if ((($tmp =  !($context["is_helpdesk"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 126
                yield "               ";
                yield Twig\Extension\CoreExtension::include($this->env, $context, "components/itilobject/timeline/filter_timeline.html.twig");
                yield "
            ";
            }
            // line 128
            yield "         ";
        }
        // line 129
        yield "     </div>

      ";
        // line 131
        if ((($tmp =  !($context["is_helpdesk"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 132
            yield "         <div class=\"form-buttons ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["form_btns_cls"] ?? null), "html", null, true);
            yield " d-flex justify-content-between ms-auto ms-lg-0 my-n2 py-2 pe-3 card-footer border-top-0 position-relative\">
            <span class=\"d-none d-lg-block ms-n3\"
                  data-bs-toggle=\"tooltip\" data-bs-placement=\"top\" title=\"";
            // line 134
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Toggle panels width"), "html", null, true);
            yield "\">
               <button type=\"button\" class=\"switch-panel-width btn btn-icon btn-ghost-secondary px-0\">
                  <i class=\"";
            // line 136
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["switch_btn_cls"] ?? null), "html", null, true);
            yield "\"></i>
               </button>
               <button type=\"button\" class=\"collapse-panel btn btn-icon btn-ghost-secondary px-0 mr-1\">
                  <i class=\"ti ti-caret-right-filled\"></i>
               </button>
            </span>

            <span>
            ";
            // line 144
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "isNewItem", [], "method", false, false, false, 144)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 145
                yield "               <button
                  class=\"btn btn-primary\"
                  type=\"submit\"
                  name=\"add\"
                  form=\"itil-form\"
                  title=\"";
                // line 150
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(_x("button", "Add"), "html", null, true);
                yield "\"
                  aria-label=\"";
                // line 151
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(_x("button", "Add"), "html", null, true);
                yield "\"
               >
                  <i class=\"ti ti-plus\"></i>
                  <span class=\"d-none d-lg-block\">";
                // line 154
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(_x("button", "Add"), "html", null, true);
                yield "</span>
               </button>
            ";
            } else {
                // line 157
                yield "               ";
                // line 158
                yield "               <div class=\"btn-group flex-row-reverse\" role=\"group\" id=\"right-actions\" style=\"display:flex\">
                  ";
                // line 159
                $context["is_locked"] = (CoreExtension::getAttribute($this->env, $this->source, ($context["params"] ?? null), "locked", [], "array", true, true, false, 159) && (($_v0 = ($context["params"] ?? null)) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0["locked"] ?? null) : null));
                // line 160
                yield "                  ";
                $context["display_save_btn"] = ( !($context["is_locked"] ?? null) && ((((($context["canupdate"] ?? null) || ($context["can_requester"] ?? null)) || ($context["canpriority"] ?? null)) || ($context["canassign"] ?? null)) || ($context["canassigntome"] ?? null)));
                // line 161
                yield "
                  ";
                // line 162
                if ((($tmp = ($context["display_save_btn"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 163
                    yield "                     <button
                        class=\"btn btn-primary btn-square\"
                        type=\"submit\"
                        name=\"update\"
                        form=\"itil-form\"
                        data-bs-toggle=\"tooltip\"
                        data-bs-placement=\"top\"
                        title=\"";
                    // line 170
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(_x("button", "Save"), "html", null, true);
                    yield "\"
                        aria-label=\"";
                    // line 171
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(_x("button", "Save"), "html", null, true);
                    yield "\"
                     >
                        <i class=\"ti ti-device-floppy\"></i>
                        <span class=\"d-none d-xl-block\">";
                    // line 174
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(_x("button", "Save"), "html", null, true);
                    yield "</span>
                     </button>
                  ";
                }
                // line 177
                yield "
                   ";
                // line 178
                if ((($tmp = ($context["canupdate"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 179
                    yield "                       ";
                    yield Twig\Extension\CoreExtension::include($this->env, $context, "components/form/single-action.html.twig", ["onlyicon" => true]);
                    // line 181
                    yield "
                   ";
                }
                // line 183
                yield "
                  ";
                // line 184
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "canDeleteItem", [], "method", false, false, false, 184)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 185
                    yield "                     ";
                    if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "isDeleted", [], "method", false, false, false, 185)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                        // line 186
                        yield "                        <button class=\"btn btn-outline-secondary btn-square\" type=\"submit\" name=\"restore\" form=\"itil-form\"
                              title=\"";
                        // line 187
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(_x("button", "Restore"), "html", null, true);
                        yield "\">
                           <i class=\"ti ti-trash-off\"></i>
                           <span class=\"d-none d-lg-block\">";
                        // line 189
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(_x("button", "Restore"), "html", null, true);
                        yield "</span>
                        </button>

                        <button class=\"btn btn-outline-danger btn-square\" type=\"submit\" name=\"purge\" form=\"itil-form\"
                              title=\"";
                        // line 193
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(_x("button", "Delete permanently"), "html", null, true);
                        yield "\"
                              onclick=\"return confirm(\x27";
                        // line 194
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Confirm the final deletion?"), "js"), "html", null, true);
                        yield "\x27);\">
                           <i class=\"ti ti-trash\"></i>
                           <span class=\"d-none d-lg-block\">";
                        // line 196
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(_x("button", "Delete permanently"), "html", null, true);
                        yield "</span>
                        </button>
                     ";
                    } else {
                        // line 199
                        yield "                        <button class=\"btn btn-outline-danger btn-square\" type=\"submit\" name=\"delete\" form=\"itil-form\"
                              title=\"";
                        // line 200
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(_x("button", "Put in trashbin"), "html", null, true);
                        yield "\"
                                data-bs-toggle=\"tooltip\" data-bs-placement=\"top\">
                           <i class=\"ti ti-trash\"></i>
                        </button>
                     ";
                    }
                    // line 205
                    yield "                  ";
                }
                // line 206
                yield "               </div>
            ";
            }
            // line 208
            yield "            </span>
         </div>
      ";
        }
        // line 211
        yield "
   </div>
</div>

";
        // line 215
        $context["openfollowup"] = (((CoreExtension::getAttribute($this->env, $this->source, ($context["_get"] ?? null), "_openfollowup", [], "array", true, true, false, 215) &&  !(null === (($_v1 = ($context["_get"] ?? null)) && is_array($_v1) || $_v1 instanceof ArrayAccess ? ($_v1["_openfollowup"] ?? null) : null)))) ? ((($_v2 = ($context["_get"] ?? null)) && is_array($_v2) || $_v2 instanceof ArrayAccess ? ($_v2["_openfollowup"] ?? null) : null)) : (false));
        // line 216
        $context["is_timeline_reversed"] = ($this->extensions['Glpi\Application\View\Extension\SessionExtension']->userPref("timeline_order") == Twig\Extension\CoreExtension::constant("CommonITILObject::TIMELINE_ORDER_REVERSE"));
        // line 217
        yield "
<script type=\"text/javascript\">

(function(){
    ";
        // line 226
        yield "   var scrollToTimelineStart = function() {
        // scroll body to ensure we are at bottom (useful for responsive display)
        \$(\x27html, body\x27).animate({
        scrollTop: ";
        // line 229
        yield (((($tmp = ($context["is_timeline_reversed"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("-") : (""));
        yield "\$(document).height()
        }, 0, function(){
            // scroll timeline with animation
            var timeline = \$(\"#itil-object-container .itil-left-side\");
            timeline.animate({
                scrollTop: ";
        // line 234
        yield (((($tmp = ($context["is_timeline_reversed"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("-") : (""));
        yield "timeline.prop(\x27scrollHeight\x27)
            }, \x27slow\x27);
        });
   };

   \$(document).on(\"click\", \"#itil-footer .answer-action\", function() {
      scrollToTimelineStart();
   });

   // Hide primary actions whenever a top-level timeline answer form opens (direct children only, to ignore nested collapses inside forms).
   \$(document).on(\"show.bs.collapse\", \"#new-itilobject-form > .collapse\", function() {
      \$(\"#itil-footer .main-actions\").hide();
      \$(\"#right-actions\").hide();
      setHasUnsavedChanges(true);
   });

   // Restore primary actions once all top-level forms are fully hidden (direct children only, to avoid false positives from nested .collapse.show inside forms such as pending-reasons).
   \$(document).on(\"hidden.bs.collapse\", \"#new-itilobject-form > .collapse\", function() {
      if (\$(\"#new-itilobject-form > .collapse.show\").length === 0) {
         \$(\"#itil-footer .main-actions\").show();
         \$(\"#right-actions\").show();
         setHasUnsavedChanges(false);
      }
   });

   \$(\x27#itil-footer .form-buttons button[name=\"update\"]\x27).on(\x27click\x27, () => {
       const has_opened_new_form = \$(\x27#new-itilobject-form > .collapse.show\x27).length > 0
           || \$(\x27.timeline-item .edit-content\x27).filter((i, c) => c.textContent.trim().length > 0).length > 0;
       if (has_opened_new_form) {
          return confirm(\x27";
        // line 263
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("You have unsaved changes in the timeline. Are you sure you want to continue?"), "js"), "html", null, true);
        yield "\x27);
       }
   });

   \$(function() {
      ";
        // line 268
        if ((($tmp = ($context["openfollowup"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 269
            yield "         // trigger for reopen, show followup form in timeline
         var myCollapse = document.getElementById(\x27new-ITILFollowup-block\x27)
         var bsCollapse = new bootstrap.Collapse(myCollapse);
         bsCollapse.show();

         scrollToTimelineStart();
      ";
        }
        // line 276
        yield "   });
})();

</script>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "components/itilobject/footer.html.twig";
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
        return array (  579 => 276,  570 => 269,  568 => 268,  560 => 263,  528 => 234,  520 => 229,  515 => 226,  509 => 217,  507 => 216,  505 => 215,  499 => 211,  494 => 208,  490 => 206,  487 => 205,  479 => 200,  476 => 199,  470 => 196,  465 => 194,  461 => 193,  454 => 189,  449 => 187,  446 => 186,  443 => 185,  441 => 184,  438 => 183,  434 => 181,  431 => 179,  429 => 178,  426 => 177,  420 => 174,  414 => 171,  410 => 170,  401 => 163,  399 => 162,  396 => 161,  393 => 160,  391 => 159,  388 => 158,  386 => 157,  380 => 154,  374 => 151,  370 => 150,  363 => 145,  361 => 144,  350 => 136,  345 => 134,  339 => 132,  337 => 131,  333 => 129,  330 => 128,  324 => 126,  322 => 125,  319 => 124,  313 => 121,  309 => 119,  307 => 118,  302 => 115,  297 => 113,  293 => 111,  291 => 110,  285 => 107,  281 => 105,  277 => 103,  274 => 102,  270 => 100,  256 => 99,  250 => 96,  246 => 95,  242 => 94,  235 => 93,  232 => 92,  215 => 91,  209 => 88,  202 => 84,  198 => 82,  195 => 81,  181 => 80,  175 => 77,  171 => 76,  164 => 75,  161 => 74,  143 => 73,  140 => 72,  138 => 71,  132 => 68,  128 => 67,  123 => 65,  118 => 63,  113 => 61,  110 => 60,  106 => 58,  104 => 57,  98 => 55,  95 => 54,  92 => 53,  89 => 52,  86 => 51,  84 => 50,  81 => 49,  78 => 48,  76 => 47,  72 => 46,  67 => 43,  63 => 41,  60 => 40,  57 => 39,  55 => 38,  53 => 37,  51 => 36,  49 => 35,  47 => 34,  45 => 33,  42 => 32,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "components/itilobject/footer.html.twig", "/var/www/html/glpi/templates/components/itilobject/footer.html.twig");
    }
}
