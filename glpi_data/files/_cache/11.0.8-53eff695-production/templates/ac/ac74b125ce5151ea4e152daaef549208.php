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

/* components/form/fields_macros.html.twig */
class __TwigTemplate_68412790b50bdd67fe5d0d47439a4861 extends Template
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
        // line 56
        yield "
";
        // line 78
        yield "
";
        // line 94
        yield "

";
        // line 120
        yield "
";
        // line 135
        yield "
";
        // line 149
        yield "

";
        // line 189
        yield "

";
        // line 203
        yield "

";
        // line 218
        yield "

";
        // line 279
        yield "

";
        // line 289
        yield "

";
        // line 299
        yield "

";
        // line 313
        yield "

";
        // line 340
        yield "

";
        // line 354
        yield "
";
        // line 367
        yield "
";
        // line 405
        yield "
";
        // line 441
        yield "
";
        // line 455
        yield "
";
        // line 459
        yield "
";
        // line 487
        yield "
";
        // line 516
        yield "
";
        // line 543
        yield "
";
        // line 568
        yield "
";
        // line 598
        yield "
";
        // line 613
        yield "
";
        // line 638
        yield "
";
        // line 657
        yield "
";
        // line 684
        yield "
";
        // line 711
        yield "
";
        // line 749
        yield "
";
        // line 787
        yield "
";
        // line 805
        yield "
";
        // line 851
        yield "
";
        // line 862
        yield "
";
        // line 872
        yield "

";
        // line 900
        yield "

";
        // line 965
        yield "

";
        // line 1003
        yield "
";
        // line 1008
        yield "
";
        // line 1047
        yield "
";
        yield from [];
    }

    // line 33
    public function macro_largeTitle($label = null, $icon = "", $first = false, $helper = "", ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "label" => $label,
            "icon" => $icon,
            "first" => $first,
            "helper" => $helper,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 34
            yield "   ";
            $context["margins"] = "mt-3";
            // line 35
            yield "   ";
            if ((($tmp = ($context["first"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 36
                yield "      ";
                $context["margins"] = "mt-n2";
                // line 37
                yield "   ";
            }
            // line 38
            yield "
   <div class=\"card border-0 shadow-none p-0 m-0 ";
            // line 39
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["margins"] ?? null), "html", null, true);
            yield "\">
      <div class=\"card-header mb-3 pt-2 border-top rounded-0\">
         <h4 class=\"card-title ";
            // line 41
            yield (((($tmp = Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["icon"] ?? null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("ms-5") : (""));
            yield "\">
            ";
            // line 42
            if ((($tmp = Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["icon"] ?? null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 43
                yield "               <div class=\"ribbon ribbon-bookmark ribbon-top ribbon-start bg-blue s-1\">
                  <i class=\"fs-2x ";
                // line 44
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["icon"] ?? null), "html", null, true);
                yield "\"></i>
               </div>
            ";
            }
            // line 47
            yield "            ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["label"] ?? null), "html", null, true);
            yield "
            ";
            // line 48
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(($context["helper"] ?? null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 49
                yield "               <span class=\"form-help\" data-bs-toggle=\"tooltip\" data-bs-placement=\"top\" data-bs-html=\"true\"
                     data-bs-title=\"";
                // line 50
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["helper"] ?? null), "html", null, true);
                yield "\">?</span>
            ";
            }
            // line 52
            yield "         </h4>
      </div>
   </div>
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 57
    public function macro_smallTitle($label = null, $icon = "", $helper = "", $id = "", ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "label" => $label,
            "icon" => $icon,
            "helper" => $helper,
            "id" => $id,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 58
            yield "   ";
            $context["margins"] = "mt-2 mb-2";
            // line 59
            yield "   ";
            $context["id"] = (((($context["id"] ?? null) != "")) ? (($context["id"] ?? null)) : (("formsection" . Twig\Extension\CoreExtension::random($this->env->getCharset()))));
            // line 60
            yield "
   <div class=\"card border-0 shadow-none p-0 m-0 ";
            // line 61
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["margins"] ?? null), "html", null, true);
            yield "\">
      <div id=\"";
            // line 62
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["id"] ?? null), "html", null, true);
            yield "\" class=\"card-header mb-1 p-0 ps-3 py-1\">
         <h4 class=\"card-subtitle ";
            // line 63
            yield (((($tmp = Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["icon"] ?? null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("ms-4") : (""));
            yield "\">
            ";
            // line 64
            if ((($tmp = Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["icon"] ?? null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 65
                yield "               <div class=\"ribbon ribbon-bookmark ribbon-top ribbon-start bg-blue s-1\">
                  <i class=\"fs-2x ";
                // line 66
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["icon"] ?? null), "html", null, true);
                yield "\"></i>
               </div>
            ";
            }
            // line 69
            yield "             <span class=\"ms-2\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["label"] ?? null), "html", null, true);
            yield "</span>
            ";
            // line 70
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(($context["helper"] ?? null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 71
                yield "               <span class=\"form-help\" data-bs-toggle=\"tooltip\" data-bs-placement=\"top\" data-bs-html=\"true\"
                     data-bs-title=\"";
                // line 72
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["helper"] ?? null), "html", null, true);
                yield "\">?</span>
            ";
            }
            // line 74
            yield "         </h4>
      </div>
   </div>
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 79
    public function macro_autoNameField($name = null, $item = null, $label = "", $withtemplate = 0, $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "item" => $item,
            "label" => $label,
            "withtemplate" => $withtemplate,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 80
            yield "   ";
            $context["tpl_value"] = (((Twig\Extension\CoreExtension::length($this->env->getCharset(), (((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "value", [], "any", true, true, false, 80) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "value", [], "any", false, false, false, 80)))) ? (CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "value", [], "any", false, false, false, 80)) : (""))) > 0)) ? (CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "value", [], "any", false, false, false, 80)) : ((($_v0 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 80)) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0[(($_v1 = ($context["name"] ?? null)) instanceof \Stringable ? (string) $_v1 : $_v1)] ?? null) : null)));
            // line 81
            yield "   ";
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "isTemplate", [], "method", false, false, false, 81)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                yield " ";
                // line 82
                yield "       ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["tpl_mark" => CoreExtension::getAttribute($this->env, $this->source,                 // line 83
($context["item"] ?? null), "getAutofillMark", [($context["name"] ?? null), ["withtemplate" => ($context["withtemplate"] ?? null)], ($context["tpl_value"] ?? null)], "method", false, false, false, 83)]);
                // line 85
                yield "   ";
            }
            // line 86
            yield "   ";
            if ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, true, false, 86), ($context["name"] ?? null), [], "array", true, true, false, 86) &&  !(null === (($_v2 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 86)) && is_array($_v2) || $_v2 instanceof ArrayAccess ? ($_v2[(($_v3 = ($context["name"] ?? null)) instanceof \Stringable ? (string) $_v3 : $_v3)] ?? null) : null)))) {
                // line 87
                yield "      ";
                $context["value"] = $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("autoName", [(($_v4 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 87)) && is_array($_v4) || $_v4 instanceof ArrayAccess ? ($_v4[(($_v5 = ($context["name"] ?? null)) instanceof \Stringable ? (string) $_v5 : $_v5)] ?? null) : null), ($context["name"] ?? null), (($context["withtemplate"] ?? null) == 2), CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "getType", [], "method", false, false, false, 87), (((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, true, false, 87), "entities_id", [], "array", true, true, false, 87) &&  !(null === (($_v6 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 87)) && is_array($_v6) || $_v6 instanceof ArrayAccess ? ($_v6["entities_id"] ?? null) : null)))) ? ((($_v7 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 87)) && is_array($_v7) || $_v7 instanceof ArrayAccess ? ($_v7["entities_id"] ?? null) : null)) : (null))]);
                // line 88
                yield "   ";
            } else {
                // line 89
                yield "      ";
                $context["value"] = null;
                // line 90
                yield "   ";
            }
            // line 91
            yield "
   ";
            // line 92
            yield $this->getTemplateForMacro("macro_textField", $context, 92, $this->getSourceContext())->macro_textField(...[($context["name"] ?? null), ($context["value"] ?? null), ($context["label"] ?? null), ($context["options"] ?? null)]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 96
    public function macro_textField($name = null, $value = null, $label = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "value" => $value,
            "label" => $label,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 97
            yield "   ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["id" => "%id%"],             // line 99
($context["options"] ?? null));
            // line 100
            yield "
   ";
            // line 101
            if (CoreExtension::inFilter(($context["name"] ?? null), ["name"])) {
                // line 102
                yield "        ";
                $context["current_attrs"] = ((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "additional_attributes", [], "any", true, true, false, 102)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "additional_attributes", [], "any", false, false, false, 102), [])) : ([]));
                // line 103
                yield "
         ";
                // line 104
                if ((($tmp =  !CoreExtension::getAttribute($this->env, $this->source, ($context["current_attrs"] ?? null), "autocomplete", [], "any", true, true, false, 104)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 105
                    yield "            ";
                    $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["additional_attributes" => Twig\Extension\CoreExtension::merge(                    // line 106
($context["current_attrs"] ?? null), ["autocomplete" => "off"])]);
                    // line 110
                    yield "         ";
                }
                // line 111
                yield "   ";
            }
            // line 112
            yield "
   ";
            // line 113
            $context["field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 114
                yield "        ";
                $macros["_inputs"] = $this->load("components/form/basic_inputs_macros.html.twig", 114)->unwrap();
                // line 115
                yield "        ";
                yield $macros["_inputs"]->getTemplateForMacro("macro_text", $context, 115, $this->getSourceContext())->macro_text(...[($context["name"] ?? null), ($context["value"] ?? null), ($context["options"] ?? null)]);
                yield "
   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 117
            yield "
   ";
            // line 118
            yield $this->getTemplateForMacro("macro_field", $context, 118, $this->getSourceContext())->macro_field(...[($context["name"] ?? null), ($context["field"] ?? null), ($context["label"] ?? null), ($context["options"] ?? null)]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 121
    public function macro_urlField($name = null, $value = null, $label = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "value" => $value,
            "label" => $label,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 122
            yield "    ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["id" => "%id%"],             // line 124
($context["options"] ?? null));
            // line 125
            yield "
    ";
            // line 126
            $context["field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 127
                yield "        ";
                $macros["_inputs"] = $this->load("components/form/basic_inputs_macros.html.twig", 127)->unwrap();
                // line 128
                yield "        ";
                yield $macros["_inputs"]->getTemplateForMacro("macro_input", $context, 128, $this->getSourceContext())->macro_input(...[($context["name"] ?? null), ($context["value"] ?? null), Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["type" => "url"])]);
                // line 130
                yield "
    ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 132
            yield "
    ";
            // line 133
            yield $this->getTemplateForMacro("macro_field", $context, 133, $this->getSourceContext())->macro_field(...[($context["name"] ?? null), ($context["field"] ?? null), ($context["label"] ?? null), ($context["options"] ?? null)]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 136
    public function macro_checkboxField($name = null, $value = null, $label = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "value" => $value,
            "label" => $label,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 137
            yield "   ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["id" => "%id%", "center" => true],             // line 140
($context["options"] ?? null));
            // line 141
            yield "
    ";
            // line 142
            $context["field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 143
                yield "        ";
                $macros["_inputs"] = $this->load("components/form/basic_inputs_macros.html.twig", 143)->unwrap();
                // line 144
                yield "        ";
                yield $macros["_inputs"]->getTemplateForMacro("macro_checkbox", $context, 144, $this->getSourceContext())->macro_checkbox(...[($context["name"] ?? null), ($context["value"] ?? null), ($context["options"] ?? null)]);
                yield "
    ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 146
            yield "
    ";
            // line 147
            yield $this->getTemplateForMacro("macro_field", $context, 147, $this->getSourceContext())->macro_field(...[($context["name"] ?? null), ($context["field"] ?? null), ($context["label"] ?? null), ($context["options"] ?? null)]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 151
    public function macro_sliderField($name = null, $value = null, $label = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "value" => $value,
            "label" => $label,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 152
            yield "   ";
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 152), "isMandatoryField", [($context["name"] ?? null)], "method", true, true, false, 152)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, false, false, 152), "isMandatoryField", [($context["name"] ?? null)], "method", false, false, false, 152), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 153
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(["required" => true],                 // line 155
($context["options"] ?? null));
                // line 156
                yield "   ";
            }
            // line 157
            yield "   ";
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 157), "isReadonlyField", [($context["name"] ?? null)], "method", true, true, false, 157)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, false, false, 157), "isReadonlyField", [($context["name"] ?? null)], "method", false, false, false, 157), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 158
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["readonly" => true]);
                // line 159
                yield "   ";
            }
            // line 160
            yield "   ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["no_value" => 0, "yes_value" => 1, "readonly" => false, "required" => false, "disabled" => false, "additional_attributes" => [], "label2" => ""],             // line 168
($context["options"] ?? null));
            // line 169
            yield "
   ";
            // line 170
            $context["field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 171
                yield "      <label class=\"form-check form-switch mt-2\">
         <input type=\"hidden\"   name=\"";
                // line 172
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["name"] ?? null), "html", null, true);
                yield "\" value=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "no_value", [], "any", false, false, false, 172), "html", null, true);
                yield "\" />
         <input type=\"checkbox\" name=\"";
                // line 173
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["name"] ?? null), "html", null, true);
                yield "\" value=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "yes_value", [], "any", false, false, false, 173), "html", null, true);
                yield "\" class=\"form-check-input\" id=\"%id%\"
                ";
                // line 174
                yield (((($context["value"] ?? null) == 1)) ? ("checked") : (""));
                yield "
                ";
                // line 175
                yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "readonly", [], "any", false, false, false, 175)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("readonly") : (""));
                yield "
                ";
                // line 176
                yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "required", [], "any", false, false, false, 176)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("required") : (""));
                yield "
                ";
                // line 177
                yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "disabled", [], "any", false, false, false, 177)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("disabled") : (""));
                yield "
                ";
                // line 178
                $context['_parent'] = $context;
                $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "additional_attributes", [], "any", false, false, false, 178));
                foreach ($context['_seq'] as $context["attr"] => $context["value"]) {
                    // line 179
                    yield "                    ";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["attr"], "html", null, true);
                    yield "=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["value"], "html", null, true);
                    yield "\"
                ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['attr'], $context['value'], $context['_parent']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 180
                yield " />
         ";
                // line 181
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "label2", [], "any", false, false, false, 181)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 182
                    yield "            <span class=\"form-check-label\">";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "label2", [], "any", false, false, false, 182), "html", null, true);
                    yield "</span>
         ";
                }
                // line 184
                yield "      </label>
   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 186
            yield "
   ";
            // line 187
            yield $this->getTemplateForMacro("macro_field", $context, 187, $this->getSourceContext())->macro_field(...[($context["name"] ?? null), ($context["field"] ?? null), ($context["label"] ?? null), ($context["options"] ?? null)]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 191
    public function macro_numberField($name = null, $value = null, $label = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "value" => $value,
            "label" => $label,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 192
            yield "    ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["id" => "%id%"],             // line 194
($context["options"] ?? null));
            // line 195
            yield "
    ";
            // line 196
            $context["field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 197
                yield "        ";
                $macros["_inputs"] = $this->load("components/form/basic_inputs_macros.html.twig", 197)->unwrap();
                // line 198
                yield "        ";
                yield $macros["_inputs"]->getTemplateForMacro("macro_number", $context, 198, $this->getSourceContext())->macro_number(...[($context["name"] ?? null), ($context["value"] ?? null), ($context["options"] ?? null)]);
                yield "
    ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 200
            yield "
    ";
            // line 201
            yield $this->getTemplateForMacro("macro_field", $context, 201, $this->getSourceContext())->macro_field(...[($context["name"] ?? null), ($context["field"] ?? null), ($context["label"] ?? null), ($context["options"] ?? null)]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 205
    public function macro_readOnlyField($name = null, $value = null, $label = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "value" => $value,
            "label" => $label,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 206
            yield "   ";
            $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["readonly" => true]);
            // line 207
            yield "   ";
            $context["value"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 208
                yield "      <span class=\"form-control ";
                yield (((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "input_addclass", [], "any", true, true, false, 208) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "input_addclass", [], "any", false, false, false, 208)))) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "input_addclass", [], "any", false, false, false, 208), "html", null, true)) : (""));
                yield "\" readonly>
         ";
                // line 209
                if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["value"] ?? null)) == 0)) {
                    // line 210
                    yield "            &nbsp;
         ";
                } else {
                    // line 212
                    yield "            ";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["value"] ?? null), "html", null, true);
                    yield "
         ";
                }
                // line 214
                yield "      </span>
   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 216
            yield "   ";
            yield $this->getTemplateForMacro("macro_field", $context, 216, $this->getSourceContext())->macro_field(...[($context["name"] ?? null), ($context["value"] ?? null), ($context["label"] ?? null), ($context["options"] ?? null)]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 220
    public function macro_textareaField($name = null, $value = null, $label = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "value" => $value,
            "label" => $label,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 221
            yield "   ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["rand" => Twig\Extension\CoreExtension::random($this->env->getCharset()), "enable_richtext" => false, "enable_images" => true, "enable_fileupload" => false, "mention_options" => ["enabled" => (CoreExtension::getAttribute($this->env, $this->source,             // line 227
($context["options"] ?? null), "enable_mentions", [], "any", true, true, false, 227) && CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "enable_mentions", [], "any", false, false, false, 227)), "full" => true, "users" => []], "entities_id" => $this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpiactive_entity"), "uploads" => [], "rows" => 3, "readonly" => false],             // line 235
($context["options"] ?? null));
            // line 236
            yield "
   ";
            // line 237
            if ((($tmp =  !CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "id", [], "any", true, true, false, 237)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 238
                yield "       ";
                // line 239
                yield "       ";
                $context["options"] = Twig\Extension\CoreExtension::merge(["id" => ((Html::sanitizeDomId(($context["name"] ?? null)) . "_") . CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "rand", [], "any", false, false, false, 239))], ($context["options"] ?? null));
                // line 240
                yield "   ";
            }
            // line 241
            yield "
   ";
            // line 242
            $context["field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 243
                yield "        ";
                $macros["_inputs"] = $this->load("components/form/basic_inputs_macros.html.twig", 243)->unwrap();
                // line 244
                yield "        ";
                yield $macros["_inputs"]->getTemplateForMacro("macro_textarea", $context, 244, $this->getSourceContext())->macro_textarea(...[($context["name"] ?? null), ($context["value"] ?? null), ($context["options"] ?? null)]);
                yield "
   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 246
            yield "
   ";
            // line 247
            $context["add_html"] = "";
            // line 248
            yield "   ";
            if (( !CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "readonly", [], "any", false, false, false, 248) && CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "enable_fileupload", [], "any", false, false, false, 248))) {
                // line 249
                yield "      ";
                $context["add_html"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                    // line 250
                    yield "         ";
                    $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Html::file", [["editor_id" => CoreExtension::getAttribute($this->env, $this->source,                     // line 251
($context["options"] ?? null), "id", [], "any", false, false, false, 251), "multiple" => true, "uploads" => CoreExtension::getAttribute($this->env, $this->source,                     // line 253
($context["options"] ?? null), "uploads", [], "any", false, false, false, 253), "required" => ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source,                     // line 254
($context["options"] ?? null), "fields_template", [], "any", false, true, false, 254), "isMandatoryField", ["_documents_id"], "method", true, true, false, 254)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, false, false, 254), "isMandatoryField", ["_documents_id"], "method", false, false, false, 254), false)) : (false))]]);
                    // line 256
                    yield "      ";
                    yield from [];
                })())) ? '' : new Markup($tmp, $this->env->getCharset());
                // line 257
                yield "   ";
            } elseif (((( !CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "readonly", [], "any", false, false, false, 257) &&  !CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "enable_fileupload", [], "any", false, false, false, 257)) && CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "enable_richtext", [], "any", false, false, false, 257)) && CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "enable_images", [], "any", false, false, false, 257))) {
                // line 258
                yield "      ";
                $context["add_html"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                    // line 259
                    yield "         ";
                    $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Html::file", [["editor_id" => CoreExtension::getAttribute($this->env, $this->source,                     // line 260
($context["options"] ?? null), "id", [], "any", false, false, false, 260), "name" =>                     // line 261
($context["name"] ?? null), "only_uploaded_files" => true, "uploads" => CoreExtension::getAttribute($this->env, $this->source,                     // line 263
($context["options"] ?? null), "uploads", [], "any", false, false, false, 263), "required" => ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source,                     // line 264
($context["options"] ?? null), "fields_template", [], "any", false, true, false, 264), "isMandatoryField", ["_documents_id"], "method", true, true, false, 264)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, false, false, 264), "isMandatoryField", ["_documents_id"], "method", false, false, false, 264), false)) : (false)), "init" => (((CoreExtension::getAttribute($this->env, $this->source,                     // line 265
($context["options"] ?? null), "init_fileupload", [], "any", true, true, false, 265) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "init_fileupload", [], "any", false, false, false, 265)))) ? (CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "init_fileupload", [], "any", false, false, false, 265)) : ((((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "init", [], "any", true, true, false, 265) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "init", [], "any", false, false, false, 265)))) ? (CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "init", [], "any", false, false, false, 265)) : (true))))]]);
                    // line 267
                    yield "      ";
                    yield from [];
                })())) ? '' : new Markup($tmp, $this->env->getCharset());
                // line 268
                yield "   ";
            }
            // line 269
            yield "
   ";
            // line 270
            if ((($context["add_html"] ?? null) != "")) {
                // line 271
                yield "      ";
                if (CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "add_field_html", [], "any", true, true, false, 271)) {
                    // line 272
                    yield "         ";
                    $context["add_html"] = (($context["add_html"] ?? null) . CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "add_field_html", [], "any", false, false, false, 272));
                    // line 273
                    yield "      ";
                }
                // line 274
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["add_field_html" => ($context["add_html"] ?? null)]);
                // line 275
                yield "   ";
            }
            // line 276
            yield "
   ";
            // line 277
            yield $this->getTemplateForMacro("macro_field", $context, 277, $this->getSourceContext())->macro_field(...[($context["name"] ?? null), ($context["field"] ?? null), ($context["label"] ?? null), ($context["options"] ?? null)]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 281
    public function macro_dateField($name = null, $value = null, $label = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "value" => $value,
            "label" => $label,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 282
            yield "   ";
            $context["field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 283
                yield "        ";
                $macros["_inputs"] = $this->load("components/form/basic_inputs_macros.html.twig", 283)->unwrap();
                // line 284
                yield "        ";
                yield $macros["_inputs"]->getTemplateForMacro("macro_date", $context, 284, $this->getSourceContext())->macro_date(...[($context["name"] ?? null), ($context["value"] ?? null), ($context["options"] ?? null)]);
                yield "
   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 286
            yield "
   ";
            // line 287
            yield $this->getTemplateForMacro("macro_field", $context, 287, $this->getSourceContext())->macro_field(...[($context["name"] ?? null), ($context["field"] ?? null), ($context["label"] ?? null), ($context["options"] ?? null)]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 291
    public function macro_datetimeField($name = null, $value = null, $label = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "value" => $value,
            "label" => $label,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 292
            yield "   ";
            $context["field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 293
                yield "        ";
                $macros["_inputs"] = $this->load("components/form/basic_inputs_macros.html.twig", 293)->unwrap();
                // line 294
                yield "        ";
                yield $macros["_inputs"]->getTemplateForMacro("macro_datetime", $context, 294, $this->getSourceContext())->macro_datetime(...[($context["name"] ?? null), ($context["value"] ?? null), ($context["options"] ?? null)]);
                yield "
   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 296
            yield "
   ";
            // line 297
            yield $this->getTemplateForMacro("macro_field", $context, 297, $this->getSourceContext())->macro_field(...[($context["name"] ?? null), ($context["field"] ?? null), ($context["label"] ?? null), ($context["options"] ?? null)]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 301
    public function macro_colorField($name = null, $value = null, $label = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "value" => $value,
            "label" => $label,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 302
            yield "    ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["id" => "%id%"],             // line 304
($context["options"] ?? null));
            // line 305
            yield "
    ";
            // line 306
            $context["field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 307
                yield "        ";
                $macros["_inputs"] = $this->load("components/form/basic_inputs_macros.html.twig", 307)->unwrap();
                // line 308
                yield "        ";
                yield $macros["_inputs"]->getTemplateForMacro("macro_color", $context, 308, $this->getSourceContext())->macro_color(...[($context["name"] ?? null), ($context["value"] ?? null), ($context["options"] ?? null)]);
                yield "
    ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 310
            yield "
    ";
            // line 311
            yield $this->getTemplateForMacro("macro_field", $context, 311, $this->getSourceContext())->macro_field(...[($context["name"] ?? null), ($context["field"] ?? null), ($context["label"] ?? null), ($context["options"] ?? null)]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 315
    public function macro_passwordField($name = null, $value = null, $label = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "value" => $value,
            "label" => $label,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 316
            yield "   ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["id" => "%id%", "can_regenerate" => (((CoreExtension::getAttribute($this->env, $this->source,             // line 318
($context["options"] ?? null), "can_regenerate", [], "any", true, true, false, 318) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "can_regenerate", [], "any", false, false, false, 318)))) ? (CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "can_regenerate", [], "any", false, false, false, 318)) : (false)), "clearable" => ((CoreExtension::getAttribute($this->env, $this->source,             // line 319
($context["options"] ?? null), "clearable", [], "any", true, true, false, 319)) ? (CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "clearable", [], "any", false, false, false, 319)) : ( !(((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "is_disclosable", [], "any", true, true, false, 319) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "is_disclosable", [], "any", false, false, false, 319)))) ? (CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "is_disclosable", [], "any", false, false, false, 319)) : (false)))), "is_copyable" => (((CoreExtension::getAttribute($this->env, $this->source,             // line 320
($context["options"] ?? null), "is_disclosable", [], "any", true, true, false, 320) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "is_disclosable", [], "any", false, false, false, 320)))) ? (CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "is_disclosable", [], "any", false, false, false, 320)) : (false))],             // line 321
($context["options"] ?? null));
            // line 322
            yield "
    ";
            // line 323
            $context["field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 324
                yield "        ";
                $macros["_inputs"] = $this->load("components/form/basic_inputs_macros.html.twig", 324)->unwrap();
                // line 325
                yield "        ";
                yield $macros["_inputs"]->getTemplateForMacro("macro_password", $context, 325, $this->getSourceContext())->macro_password(...[($context["name"] ?? null), ($context["value"] ?? null), ($context["options"] ?? null)]);
                yield "
    ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 327
            yield "
   ";
            // line 329
            yield "   ";
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "can_regenerate", [], "any", false, false, false, 329)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 330
                yield "      ";
                $context["regenerate_chk"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                    // line 331
                    yield "         <div class=\"d-flex align-items-center gap-1 mt-1\">
             <input class=\"form-check-input\" type=\"checkbox\" name=\"_regenerate_";
                    // line 332
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["name"] ?? null), "html", null, true);
                    yield "\" id=\"_regenerate_";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["name"] ?? null), "html", null, true);
                    yield "\"><label class=\"form-check-label\" for=\"_regenerate_";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["name"] ?? null), "html", null, true);
                    yield "\">";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Regenerate"), "html", null, true);
                    yield "</label>
         </div>
      ";
                    yield from [];
                })())) ? '' : new Markup($tmp, $this->env->getCharset());
                // line 335
                yield "      ";
                $context["field"] = (($context["field"] ?? null) . ($context["regenerate_chk"] ?? null));
                // line 336
                yield "   ";
            }
            // line 337
            yield "
   ";
            // line 338
            yield $this->getTemplateForMacro("macro_field", $context, 338, $this->getSourceContext())->macro_field(...[($context["name"] ?? null), ($context["field"] ?? null), ($context["label"] ?? null), ($context["options"] ?? null)]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 342
    public function macro_emailField($name = null, $value = null, $label = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "value" => $value,
            "label" => $label,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 343
            yield "    ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["id" => "%id%"],             // line 345
($context["options"] ?? null));
            // line 346
            yield "
    ";
            // line 347
            $context["field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 348
                yield "        ";
                $macros["_inputs"] = $this->load("components/form/basic_inputs_macros.html.twig", 348)->unwrap();
                // line 349
                yield "        ";
                yield $macros["_inputs"]->getTemplateForMacro("macro_email", $context, 349, $this->getSourceContext())->macro_email(...[($context["name"] ?? null), ($context["value"] ?? null), ($context["options"] ?? null)]);
                yield "
    ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 351
            yield "
   ";
            // line 352
            yield $this->getTemplateForMacro("macro_field", $context, 352, $this->getSourceContext())->macro_field(...[($context["name"] ?? null), ($context["field"] ?? null), ($context["label"] ?? null), ($context["options"] ?? null)]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 355
    public function macro_fileField($name = null, $value = null, $label = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "value" => $value,
            "label" => $label,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 356
            yield "   ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["id" => "%id%", "rand" => Twig\Extension\CoreExtension::random($this->env->getCharset()), "simple" => false],             // line 360
($context["options"] ?? null));
            // line 361
            yield "   ";
            $context["field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 362
                yield "        ";
                $macros["_inputs"] = $this->load("components/form/basic_inputs_macros.html.twig", 362)->unwrap();
                // line 363
                yield "        ";
                yield $macros["_inputs"]->getTemplateForMacro("macro_file", $context, 363, $this->getSourceContext())->macro_file(...[($context["name"] ?? null), ($context["value"] ?? null), ($context["options"] ?? null)]);
                yield "
   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 365
            yield "   ";
            yield $this->getTemplateForMacro("macro_field", $context, 365, $this->getSourceContext())->macro_field(...[($context["name"] ?? null), ($context["field"] ?? null), ($context["label"] ?? null), ($context["options"] ?? null)]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 368
    public function macro_imageField($name = null, $value = null, $label = "", $options = [], $link_options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "value" => $value,
            "label" => $label,
            "options" => $options,
            "link_options" => $link_options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 369
            yield "   ";
            $context["field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 370
                yield "      <div class=\"img-overlay-wrapper position-relative\">
         ";
                // line 371
                $context["clearable"] = (($_v8 = ($context["options"] ?? null)) && is_array($_v8) || $_v8 instanceof ArrayAccess ? ($_v8["clearable"] ?? null) : null);
                // line 372
                yield "         ";
                $context["url"] = (((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "url", [], "array", true, true, false, 372) &&  !(null === (($_v9 = ($context["options"] ?? null)) && is_array($_v9) || $_v9 instanceof ArrayAccess ? ($_v9["url"] ?? null) : null)))) ? ((($_v10 = ($context["options"] ?? null)) && is_array($_v10) || $_v10 instanceof ArrayAccess ? ($_v10["url"] ?? null) : null)) : (null));
                // line 373
                yield "         ";
                $context["options"] = Twig\Extension\CoreExtension::filter($this->env, $this->env->hasExtension(\Twig\Extension\SandboxExtension::class) && $this->env->getExtension(\Twig\Extension\SandboxExtension::class)->isSandboxed($this->source), ($context["options"] ?? null), function ($__v__, $__k__) use ($context, $macros) { $context["v"] = $__v__; $context["k"] = $__k__; return ((($context["k"] ?? null) != "url") && (($context["k"] ?? null) != "clearable")); });
                // line 374
                yield "         ";
                if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(($context["url"] ?? null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 375
                    yield "            <a href=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["url"] ?? null), "html", null, true);
                    yield "\" ";
                    yield $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Html::parseAttributes", [($context["link_options"] ?? null)]);
                    yield ">
         ";
                }
                // line 377
                yield "               <img src=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["value"] ?? null), "html", null, true);
                yield "\" ";
                yield $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Html::parseAttributes", [($context["options"] ?? null)]);
                yield " />
         ";
                // line 378
                if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(($context["url"] ?? null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 379
                    yield "            </a>
         ";
                }
                // line 381
                yield "         ";
                if ((($tmp = ($context["clearable"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 382
                    yield "            <input type=\"hidden\" name=\"_blank_";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["name"] ?? null), "html", null, true);
                    yield "\" />";
                    // line 383
                    $context["clear_js"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                        // line 384
                        yield "const blank_input = \$(\x27input[name=\\\x27_blank_";
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["name"] ?? null), "css"), "js"), "html", null, true);
                        yield "\\\x27]\x27);
                 blank_input.val(blank_input.val() ? \x27\x27 : true);
                 if (\$(this).closest(\x27.picture_gallery_item\x27).length) {
                    \$(this).closest(\x27.picture_gallery_item\x27).hide();
                    \$(this).closest(\x27.picture_gallery\x27).siblings(\x27.deletion_pending\x27).removeClass(\x27d-none\x27);
                 } else {
                    \$(this).closest(\x27.img-overlay-wrapper\x27).hide();
                 }";
                        yield from [];
                    })())) ? '' : new Markup($tmp, $this->env->getCharset());
                    // line 393
                    yield "<button type=\"button\" class=\"btn p-2 position-absolute top-0 start-0\" title=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Delete"), "html", null, true);
                    yield "\"
                    onclick=\"";
                    // line 394
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["clear_js"] ?? null), "html", null, true);
                    yield "\">
               <i class=\"ti ti-x\"></i>
            </button>
         ";
                }
                // line 398
                yield "      </div>
   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 400
            yield "   ";
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 400), "isReadonlyField", [($context["name"] ?? null)], "method", true, true, false, 400)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, false, false, 400), "isReadonlyField", [($context["name"] ?? null)], "method", false, false, false, 400), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 401
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["readonly" => true]);
                // line 402
                yield "   ";
            }
            // line 403
            yield "   ";
            yield $this->getTemplateForMacro("macro_field", $context, 403, $this->getSourceContext())->macro_field(...[($context["name"] ?? null), ($context["field"] ?? null), ($context["label"] ?? null), ($context["options"] ?? null)]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 406
    public function macro_imageGalleryField($name = null, $value = null, $label = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "value" => $value,
            "label" => $label,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 407
            yield "   ";
            $context["field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 408
                yield "       <div class=\"text-warning deletion_pending d-none\">
           ";
                // line 409
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("The deletion will only take effect after saving the form"), "html", null, true);
                yield "
       </div>
      <div class=\"picture_gallery d-flex flex-wrap overflow-auto p-3\">
         ";
                // line 412
                $context['_parent'] = $context;
                $context['_seq'] = CoreExtension::ensureTraversable(($context["value"] ?? null));
                foreach ($context['_seq'] as $context["i"] => $context["picture"]) {
                    // line 413
                    yield "            <div class=\"picture_gallery_item\" style=\"position: relative; width: fit-content\">
               ";
                    // line 414
                    yield $this->getTemplateForMacro("macro_imageField", $context, 414, $this->getSourceContext())->macro_imageField(...[((($context["name"] ?? null) . "_") . $context["i"]), $context["picture"], "", ["style" => "max-width: 300px; max-height: 150px", "class" => "picture_square", "clearable" => (($_v11 =                     // line 417
($context["options"] ?? null)) && is_array($_v11) || $_v11 instanceof ArrayAccess ? ($_v11["clearable"] ?? null) : null), "no_label" => true]]);
                    // line 419
                    yield "
            </div>
         ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['i'], $context['picture'], $context['_parent']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 422
                yield "      </div>
      ";
                // line 423
                yield $this->getTemplateForMacro("macro_fileField", $context, 423, $this->getSourceContext())->macro_fileField(...[($context["name"] ?? null), null, "", ["onlyimages" => true, "multiple" => true]]);
                // line 426
                yield "
   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 428
            yield "
   ";
            // line 429
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 429), "isReadonlyField", [($context["name"] ?? null)], "method", true, true, false, 429)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, false, false, 429), "isReadonlyField", [($context["name"] ?? null)], "method", false, false, false, 429), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 430
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["readonly" => true]);
                // line 431
                yield "   ";
            }
            // line 432
            yield "
   ";
            // line 433
            $context["id"] = (((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "id", [], "any", true, true, false, 433) && (Twig\Extension\CoreExtension::length($this->env->getCharset(), CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "id", [], "any", false, false, false, 433)) > 0))) ? (CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "id", [], "any", false, false, false, 433)) : (((Html::sanitizeDomId(($context["name"] ?? null)) . "_") . (((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "rand", [], "any", true, true, false, 433) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "rand", [], "any", false, false, false, 433)))) ? (CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "rand", [], "any", false, false, false, 433)) : ("")))));
            // line 434
            yield "   ";
            $macros["_inputs"] = $this->load("components/form/basic_inputs_macros.html.twig", 434)->unwrap();
            // line 435
            yield "   ";
            yield $macros["_inputs"]->getTemplateForMacro("macro_label", $context, 435, $this->getSourceContext())->macro_label(...[($context["label"] ?? null), ($context["id"] ?? null), ($context["options"] ?? null)]);
            yield "
   ";
            // line 436
            yield $this->getTemplateForMacro("macro_field", $context, 436, $this->getSourceContext())->macro_field(...[($context["name"] ?? null), ($context["field"] ?? null), ($context["label"] ?? null), Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["full_width" => true, "no_label" => true])]);
            // line 439
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 442
    public function macro_hiddenField($name = null, $value = null, $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "value" => $value,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 443
            yield "    ";
            if ((($tmp =  !is_iterable(($context["options"] ?? null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 444
                yield "        ";
                // line 450
                yield "        ";
                $context["options"] = ((CoreExtension::getAttribute($this->env, $this->source, ($context["varargs"] ?? null), 0, [], "array", true, true, false, 450)) ? (Twig\Extension\CoreExtension::default((($_v12 = ($context["varargs"] ?? null)) && is_array($_v12) || $_v12 instanceof ArrayAccess ? ($_v12[0] ?? null) : null), [])) : ([]));
                // line 451
                yield "    ";
            }
            // line 452
            yield "    ";
            $macros["_inputs"] = $this->load("components/form/basic_inputs_macros.html.twig", 452)->unwrap();
            // line 453
            yield "    ";
            yield $macros["_inputs"]->getTemplateForMacro("macro_hidden", $context, 453, $this->getSourceContext())->macro_hidden(...[($context["name"] ?? null), ($context["value"] ?? null), ($context["options"] ?? null)]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 456
    public function macro_csrfField(...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 457
            yield "    ";
            yield $this->getTemplateForMacro("macro_hiddenField", $context, 457, $this->getSourceContext())->macro_hiddenField(...["_glpi_csrf_token", Session::getNewCSRFToken()]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 460
    public function macro_dropdownNumberField($name = null, $value = null, $label = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "value" => $value,
            "label" => $label,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 461
            yield "    ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["rand" => Twig\Extension\CoreExtension::random($this->env->getCharset()), "width" => "100%", "disabled" => false],             // line 465
($context["options"] ?? null));
            // line 466
            yield "
   ";
            // line 467
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 467), "isReadonlyField", [($context["name"] ?? null)], "method", true, true, false, 467)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, false, false, 467), "isReadonlyField", [($context["name"] ?? null)], "method", false, false, false, 467), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 468
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["readonly" => true]);
                // line 469
                yield "   ";
            }
            // line 470
            yield "
   ";
            // line 471
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "disabled", [], "any", false, false, false, 471)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 472
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["specific_tags" => ["disabled" => "disabled"]]);
                // line 473
                yield "   ";
            }
            // line 474
            yield "   ";
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 474), "isMandatoryField", [($context["name"] ?? null)], "method", true, true, false, 474)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, false, false, 474), "isMandatoryField", [($context["name"] ?? null)], "method", false, false, false, 474), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 475
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(["specific_tags" => ["required" => true]], ($context["options"] ?? null));
                // line 476
                yield "   ";
            }
            // line 477
            yield "
   ";
            // line 478
            $context["field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 479
                yield "      ";
                $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Dropdown::showNumber", [($context["name"] ?? null), Twig\Extension\CoreExtension::merge(["value" =>                 // line 480
($context["value"] ?? null), "rand" => CoreExtension::getAttribute($this->env, $this->source,                 // line 481
($context["options"] ?? null), "rand", [], "any", false, false, false, 481)],                 // line 482
($context["options"] ?? null))]);
                // line 483
                yield "   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 484
            yield "
   ";
            // line 485
            yield $this->getTemplateForMacro("macro_field", $context, 485, $this->getSourceContext())->macro_field(...[($context["name"] ?? null), ($context["field"] ?? null), ($context["label"] ?? null), Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["id" => (("dropdown_" . Twig\Extension\CoreExtension::replace(($context["name"] ?? null), ["[" => "_", "]" => "_"])) . CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "rand", [], "any", false, false, false, 485))])]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 488
    public function macro_dropdownArrayField($name = null, $value = null, $elements = null, $label = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "value" => $value,
            "elements" => $elements,
            "label" => $label,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 489
            yield "    ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["rand" => Twig\Extension\CoreExtension::random($this->env->getCharset()), "disabled" => false, "width" => "100%"],             // line 493
($context["options"] ?? null));
            // line 494
            yield "
   ";
            // line 495
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 495), "isReadonlyField", [($context["name"] ?? null)], "method", true, true, false, 495)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, false, false, 495), "isReadonlyField", [($context["name"] ?? null)], "method", false, false, false, 495), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 496
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["readonly" => true]);
                // line 497
                yield "   ";
            }
            // line 498
            yield "
   ";
            // line 499
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "disabled", [], "any", false, false, false, 499)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 500
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["specific_tags" => ["disabled" => "disabled"]]);
                // line 501
                yield "   ";
            }
            // line 502
            yield "
   ";
            // line 503
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 503), "isMandatoryField", [($context["name"] ?? null)], "method", true, true, false, 503)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, false, false, 503), "isMandatoryField", [($context["name"] ?? null)], "method", false, false, false, 503), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 504
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(["required" => true], ($context["options"] ?? null));
                // line 505
                yield "   ";
            }
            // line 506
            yield "
   ";
            // line 507
            $context["field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 508
                yield "      ";
                $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Dropdown::showFromArray", [($context["name"] ?? null), ($context["elements"] ?? null), Twig\Extension\CoreExtension::merge(["value" =>                 // line 509
($context["value"] ?? null), "rand" => CoreExtension::getAttribute($this->env, $this->source,                 // line 510
($context["options"] ?? null), "rand", [], "any", false, false, false, 510)],                 // line 511
($context["options"] ?? null))]);
                // line 512
                yield "   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 513
            yield "
   ";
            // line 514
            yield $this->getTemplateForMacro("macro_field", $context, 514, $this->getSourceContext())->macro_field(...[($context["name"] ?? null), ($context["field"] ?? null), ($context["label"] ?? null), Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["id" => (("dropdown_" . Twig\Extension\CoreExtension::replace(($context["name"] ?? null), ["[" => "_", "]" => "_"])) . CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "rand", [], "any", false, false, false, 514))])]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 517
    public function macro_dropdownTimestampField($name = null, $value = null, $label = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "value" => $value,
            "label" => $label,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 518
            yield "    ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["rand" => Twig\Extension\CoreExtension::random($this->env->getCharset()), "width" => "100%", "disabled" => false],             // line 522
($context["options"] ?? null));
            // line 523
            yield "   ";
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 523), "isMandatoryField", [($context["name"] ?? null)], "method", true, true, false, 523)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, false, false, 523), "isMandatoryField", [($context["name"] ?? null)], "method", false, false, false, 523), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 524
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(["required" => true], ($context["options"] ?? null));
                // line 525
                yield "   ";
            }
            // line 526
            yield "
   ";
            // line 527
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 527), "isReadonlyField", [($context["name"] ?? null)], "method", true, true, false, 527)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, false, false, 527), "isReadonlyField", [($context["name"] ?? null)], "method", false, false, false, 527), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 528
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["readonly" => true]);
                // line 529
                yield "   ";
            }
            // line 530
            yield "
   ";
            // line 531
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "disabled", [], "any", false, false, false, 531)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 532
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["specific_tags" => ["disabled" => "disabled"]]);
                // line 533
                yield "   ";
            }
            // line 534
            yield "
   ";
            // line 535
            $context["field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 536
                yield "      ";
                $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Dropdown::showTimestamp", [($context["name"] ?? null), Twig\Extension\CoreExtension::merge(["value" =>                 // line 537
($context["value"] ?? null)],                 // line 538
($context["options"] ?? null))]);
                // line 539
                yield "   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 540
            yield "
   ";
            // line 541
            yield $this->getTemplateForMacro("macro_field", $context, 541, $this->getSourceContext())->macro_field(...[($context["name"] ?? null), ($context["field"] ?? null), ($context["label"] ?? null), Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["id" => (("dropdown_" . Twig\Extension\CoreExtension::replace(($context["name"] ?? null), ["[" => "_", "]" => "_"])) . CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "rand", [], "any", false, false, false, 541))])]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 544
    public function macro_dropdownYesNo($name = null, $value = null, $label = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "value" => $value,
            "label" => $label,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 545
            yield "    ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["rand" => Twig\Extension\CoreExtension::random($this->env->getCharset()), "width" => "100%", "disabled" => false],             // line 549
($context["options"] ?? null));
            // line 550
            yield "   ";
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 550), "isMandatoryField", [($context["name"] ?? null)], "method", true, true, false, 550)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, false, false, 550), "isMandatoryField", [($context["name"] ?? null)], "method", false, false, false, 550), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 551
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(["required" => true], ($context["options"] ?? null));
                // line 552
                yield "   ";
            }
            // line 553
            yield "
   ";
            // line 554
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 554), "isReadonlyField", [($context["name"] ?? null)], "method", true, true, false, 554)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, false, false, 554), "isReadonlyField", [($context["name"] ?? null)], "method", false, false, false, 554), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 555
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["readonly" => true]);
                // line 556
                yield "   ";
            }
            // line 557
            yield "
   ";
            // line 558
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "disabled", [], "any", false, false, false, 558)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 559
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["specific_tags" => ["disabled" => "disabled"]]);
                // line 560
                yield "   ";
            }
            // line 561
            yield "
   ";
            // line 562
            $context["field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 563
                yield "      ";
                $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Dropdown::showYesNo", [($context["name"] ?? null), ($context["value"] ?? null),  -1, ($context["options"] ?? null)]);
                // line 564
                yield "   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 565
            yield "
   ";
            // line 566
            yield $this->getTemplateForMacro("macro_field", $context, 566, $this->getSourceContext())->macro_field(...[($context["name"] ?? null), ($context["field"] ?? null), ($context["label"] ?? null), Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["id" => (("dropdown_" . Twig\Extension\CoreExtension::replace(($context["name"] ?? null), ["[" => "_", "]" => "_"])) . CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "rand", [], "any", false, false, false, 566))])]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 569
    public function macro_dropdownItemTypes($name = null, $value = null, $label = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "value" => $value,
            "label" => $label,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 570
            yield "    ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["rand" => Twig\Extension\CoreExtension::random($this->env->getCharset()), "width" => "100%", "disabled" => false],             // line 574
($context["options"] ?? null));
            // line 575
            yield "   ";
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 575), "isMandatoryField", [($context["name"] ?? null)], "method", true, true, false, 575)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, false, false, 575), "isMandatoryField", [($context["name"] ?? null)], "method", false, false, false, 575), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 576
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(["required" => true], ($context["options"] ?? null));
                // line 577
                yield "   ";
            }
            // line 578
            yield "
   ";
            // line 579
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 579), "isReadonlyField", [($context["name"] ?? null)], "method", true, true, false, 579)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, false, false, 579), "isReadonlyField", [($context["name"] ?? null)], "method", false, false, false, 579), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 580
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["readonly" => true]);
                // line 581
                yield "   ";
            }
            // line 582
            yield "
   ";
            // line 583
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "disabled", [], "any", false, false, false, 583)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 584
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["specific_tags" => ["disabled" => "disabled"]]);
                // line 585
                yield "   ";
            }
            // line 586
            yield "
   ";
            // line 587
            $context["types"] = ((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "types", [], "array", true, true, false, 587)) ? (Twig\Extension\CoreExtension::default((($_v13 = ($context["options"] ?? null)) && is_array($_v13) || $_v13 instanceof ArrayAccess ? ($_v13["types"] ?? null) : null), [])) : ([]));
            // line 588
            yield "
   ";
            // line 589
            $context["field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 590
                yield "      ";
                $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Dropdown::showItemTypes", [($context["name"] ?? null), ($context["types"] ?? null), Twig\Extension\CoreExtension::merge(["rand" => CoreExtension::getAttribute($this->env, $this->source,                 // line 591
($context["options"] ?? null), "rand", [], "any", false, false, false, 591), "value" =>                 // line 592
($context["value"] ?? null)],                 // line 593
($context["options"] ?? null))]);
                // line 594
                yield "   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 595
            yield "
   ";
            // line 596
            yield $this->getTemplateForMacro("macro_field", $context, 596, $this->getSourceContext())->macro_field(...[($context["name"] ?? null), ($context["field"] ?? null), ($context["label"] ?? null), Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["id" => (("dropdown_" . Twig\Extension\CoreExtension::replace(($context["name"] ?? null), ["[" => "_", "]" => "_"])) . CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "rand", [], "any", false, false, false, 596))])]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 599
    public function macro_dropdownItemsFromItemtypes($name = null, $label = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "label" => $label,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 600
            yield "   ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["rand" => Twig\Extension\CoreExtension::random($this->env->getCharset())],             // line 602
($context["options"] ?? null));
            // line 603
            yield "
   ";
            // line 604
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 604), "isReadonlyField", [($context["name"] ?? null)], "method", true, true, false, 604)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, false, false, 604), "isReadonlyField", [($context["name"] ?? null)], "method", false, false, false, 604), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 605
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["readonly" => true]);
                // line 606
                yield "   ";
            }
            // line 607
            yield "
   ";
            // line 608
            $context["field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 609
                yield "      ";
                $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Dropdown::showSelectItemFromItemtypes", [($context["options"] ?? null)]);
                // line 610
                yield "   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 611
            yield "   ";
            yield $this->getTemplateForMacro("macro_field", $context, 611, $this->getSourceContext())->macro_field(...[($context["name"] ?? null), ($context["field"] ?? null), ($context["label"] ?? null), Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["id" => (("dropdown_" . Twig\Extension\CoreExtension::replace(($context["name"] ?? null), ["[" => "_", "]" => "_"])) . CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "rand", [], "any", false, false, false, 611))])]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 614
    public function macro_dropdownIcons($name = null, $value = null, $label = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "value" => $value,
            "label" => $label,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 615
            yield "    ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["rand" => Twig\Extension\CoreExtension::random($this->env->getCharset()), "width" => "100%", "disabled" => false],             // line 619
($context["options"] ?? null));
            // line 620
            yield "   ";
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 620), "isMandatoryField", [($context["name"] ?? null)], "method", true, true, false, 620)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, false, false, 620), "isMandatoryField", [($context["name"] ?? null)], "method", false, false, false, 620), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 621
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(["required" => true], ($context["options"] ?? null));
                // line 622
                yield "   ";
            }
            // line 623
            yield "
   ";
            // line 624
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 624), "isReadonlyField", [($context["name"] ?? null)], "method", true, true, false, 624)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, false, false, 624), "isReadonlyField", [($context["name"] ?? null)], "method", false, false, false, 624), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 625
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["readonly" => true]);
                // line 626
                yield "   ";
            }
            // line 627
            yield "
   ";
            // line 628
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "disabled", [], "any", false, false, false, 628)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 629
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["specific_tags" => ["disabled" => "disabled"]]);
                // line 630
                yield "   ";
            }
            // line 631
            yield "
   ";
            // line 632
            $context["field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 633
                yield "      ";
                $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Dropdown::dropdownIcons", [($context["name"] ?? null), ($context["value"] ?? null), "", ($context["options"] ?? null)]);
                // line 634
                yield "   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 635
            yield "
   ";
            // line 636
            yield $this->getTemplateForMacro("macro_field", $context, 636, $this->getSourceContext())->macro_field(...[($context["name"] ?? null), ($context["field"] ?? null), ($context["label"] ?? null), Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["id" => (("dropdown_" . Twig\Extension\CoreExtension::replace(($context["name"] ?? null), ["[" => "_", "]" => "_"])) . CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "rand", [], "any", false, false, false, 636))])]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 639
    public function macro_dropdownWebIcons($name = null, $value = null, $label = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "value" => $value,
            "label" => $label,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 640
            yield "    ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["rand" => Twig\Extension\CoreExtension::random($this->env->getCharset())], Twig\Extension\CoreExtension::merge(            // line 642
($context["options"] ?? null), ["noselect2" => true]));
            // line 645
            yield "    ";
            // line 646
            yield "    ";
            $context["value"] = Twig\Extension\CoreExtension::replace(($context["value"] ?? null), ["ti " => ""]);
            // line 647
            yield "
    ";
            // line 648
            yield $this->getTemplateForMacro("macro_dropdownArrayField", $context, 648, $this->getSourceContext())->macro_dropdownArrayField(...[($context["name"] ?? null), ($context["value"] ?? null), [ (string)($context["value"] ?? null) => ($context["value"] ?? null)], ($context["label"] ?? null), ($context["options"] ?? null)]);
            yield "
    <script type=\"module\">
        import(\x27/js/modules/Form/WebIconSelector.js\x27).then((m) => {
            const dropdown_id = \x27";
            // line 651
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::replace((("dropdown_" . ($context["name"] ?? null)) . CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "rand", [], "any", false, false, false, 651)), ["[" => "_", "]" => "_"]), "js"), "html", null, true);
            yield "\x27;
            const selector = new m.default(document.getElementById(dropdown_id));
            selector.init();
        });
    </script>
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 658
    public function macro_dropdownHoursField($name = null, $value = null, $label = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "value" => $value,
            "label" => $label,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 659
            yield "    ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["rand" => Twig\Extension\CoreExtension::random($this->env->getCharset()), "width" => "100%", "disabled" => false],             // line 663
($context["options"] ?? null));
            // line 664
            yield "   ";
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 664), "isMandatoryField", [($context["name"] ?? null)], "method", true, true, false, 664)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, false, false, 664), "isMandatoryField", [($context["name"] ?? null)], "method", false, false, false, 664), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 665
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(["required" => true], ($context["options"] ?? null));
                // line 666
                yield "   ";
            }
            // line 667
            yield "
   ";
            // line 668
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 668), "isReadonlyField", [($context["name"] ?? null)], "method", true, true, false, 668)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, false, false, 668), "isReadonlyField", [($context["name"] ?? null)], "method", false, false, false, 668), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 669
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["readonly" => true]);
                // line 670
                yield "   ";
            }
            // line 671
            yield "
   ";
            // line 672
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "disabled", [], "any", false, false, false, 672)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 673
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["specific_tags" => ["disabled" => "disabled"]]);
                // line 674
                yield "   ";
            }
            // line 675
            yield "
   ";
            // line 676
            $context["field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 677
                yield "      ";
                $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Dropdown::showHours", [($context["name"] ?? null), Twig\Extension\CoreExtension::merge(["value" =>                 // line 678
($context["value"] ?? null)],                 // line 679
($context["options"] ?? null))]);
                // line 680
                yield "   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 681
            yield "
   ";
            // line 682
            yield $this->getTemplateForMacro("macro_field", $context, 682, $this->getSourceContext())->macro_field(...[($context["name"] ?? null), ($context["field"] ?? null), ($context["label"] ?? null), Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["id" => (("dropdown_" . Twig\Extension\CoreExtension::replace(($context["name"] ?? null), ["[" => "_", "]" => "_"])) . CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "rand", [], "any", false, false, false, 682))])]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 685
    public function macro_dropdownFrequency($name = null, $value = null, $label = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "value" => $value,
            "label" => $label,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 686
            yield "   ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["rand" => Twig\Extension\CoreExtension::random($this->env->getCharset()), "disabled" => false],             // line 689
($context["options"] ?? null));
            // line 690
            yield "   ";
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 690), "isMandatoryField", [($context["name"] ?? null)], "method", true, true, false, 690)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, false, false, 690), "isMandatoryField", [($context["name"] ?? null)], "method", false, false, false, 690), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 691
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(["required" => true], ($context["options"] ?? null));
                // line 692
                yield "   ";
            }
            // line 693
            yield "
   ";
            // line 694
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 694), "isReadonlyField", [($context["name"] ?? null)], "method", true, true, false, 694)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, false, false, 694), "isReadonlyField", [($context["name"] ?? null)], "method", false, false, false, 694), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 695
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["readonly" => true]);
                // line 696
                yield "   ";
            }
            // line 697
            yield "
   ";
            // line 698
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "disabled", [], "any", false, false, false, 698)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 699
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["specific_tags" => ["disabled" => "disabled"]]);
                // line 700
                yield "   ";
            }
            // line 701
            yield "
   ";
            // line 702
            $context["field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 703
                yield "      ";
                $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Dropdown::showFrequency", [($context["name"] ?? null), ($context["value"] ?? null), Twig\Extension\CoreExtension::merge(["width" => "100%", "value" =>                 // line 705
($context["value"] ?? null)],                 // line 706
($context["options"] ?? null))]);
                // line 707
                yield "   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 708
            yield "
   ";
            // line 709
            yield $this->getTemplateForMacro("macro_field", $context, 709, $this->getSourceContext())->macro_field(...[($context["name"] ?? null), ($context["field"] ?? null), ($context["label"] ?? null), Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["id" => (("dropdown_" . Twig\Extension\CoreExtension::replace(($context["name"] ?? null), ["[" => "_", "]" => "_"])) . CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "rand", [], "any", false, false, false, 709))])]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 712
    public function macro_dropdownField($itemtype = null, $name = null, $value = null, $label = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "itemtype" => $itemtype,
            "name" => $name,
            "value" => $value,
            "label" => $label,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 713
            yield "   ";
            if ((($tmp = (((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "multiple", [], "any", true, true, false, 713) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "multiple", [], "any", false, false, false, 713)))) ? (CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "multiple", [], "any", false, false, false, 713)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 714
                yield "      ";
                // line 715
                yield "      ";
                $context["defined_input_name"] = (("_" . ($context["name"] ?? null)) . "_defined");
                // line 716
                yield "      <input type=\"hidden\" name=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["defined_input_name"] ?? null), "html", null, true);
                yield "\" value=\"1\"></input>

      ";
                // line 719
                yield "      ";
                $context["name"] = (($context["name"] ?? null) . "[]");
                // line 720
                yield "   ";
            }
            // line 721
            yield "    ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["rand" => Twig\Extension\CoreExtension::random($this->env->getCharset()), "width" => "100%", "disabled" => false],             // line 725
($context["options"] ?? null));
            // line 726
            yield "   ";
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 726), "isMandatoryField", [($context["name"] ?? null)], "method", true, true, false, 726)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, false, false, 726), "isMandatoryField", [($context["name"] ?? null)], "method", false, false, false, 726), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 727
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(["specific_tags" => ["required" => true]], ($context["options"] ?? null));
                // line 728
                yield "   ";
            }
            // line 729
            yield "
   ";
            // line 730
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 730), "isReadonlyField", [($context["name"] ?? null)], "method", true, true, false, 730)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, false, false, 730), "isReadonlyField", [($context["name"] ?? null)], "method", false, false, false, 730), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 731
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["readonly" => true]);
                // line 732
                yield "   ";
            }
            // line 733
            yield "
   ";
            // line 734
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "disabled", [], "any", false, false, false, 734)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 735
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["specific_tags" => ["disabled" => "disabled"]]);
                // line 736
                yield "   ";
            }
            // line 737
            yield "
   ";
            // line 738
            $context["field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 739
                yield "      ";
                yield $this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeDropdown(($context["itemtype"] ?? null), Twig\Extension\CoreExtension::merge(["name" =>                 // line 740
($context["name"] ?? null), "value" =>                 // line 741
($context["value"] ?? null)],                 // line 742
($context["options"] ?? null)));
                yield "
   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 744
            yield "
   ";
            // line 745
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(Twig\Extension\CoreExtension::trim(($context["field"] ?? null)))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 746
                yield "      ";
                yield $this->getTemplateForMacro("macro_field", $context, 746, $this->getSourceContext())->macro_field(...[($context["name"] ?? null), ($context["field"] ?? null), ($context["label"] ?? null), Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["id" => (("dropdown_" . Twig\Extension\CoreExtension::replace(($context["name"] ?? null), ["[" => "_", "]" => "_"])) . CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "rand", [], "any", false, false, false, 746))])]);
                yield "
   ";
            }
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 750
    public function macro_dropdownAjaxField($url = null, $name = null, $value = null, $label = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "url" => $url,
            "name" => $name,
            "value" => $value,
            "label" => $label,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 751
            yield "    ";
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "multiple", [], "any", false, false, false, 751)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 752
                yield "        ";
                // line 753
                yield "        ";
                $context["defined_input_name"] = (("_" . ($context["name"] ?? null)) . "_defined");
                // line 754
                yield "        <input type=\"hidden\" name=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["defined_input_name"] ?? null), "html", null, true);
                yield "\" value=\"1\"></input>

        ";
                // line 757
                yield "        ";
                $context["name"] = (($context["name"] ?? null) . "[]");
                // line 758
                yield "    ";
            }
            // line 759
            yield "    ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["rand" => Twig\Extension\CoreExtension::random($this->env->getCharset()), "width" => "100%"],             // line 762
($context["options"] ?? null));
            // line 763
            yield "    ";
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 763), "isMandatoryField", [($context["name"] ?? null)], "method", true, true, false, 763)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, false, false, 763), "isMandatoryField", [($context["name"] ?? null)], "method", false, false, false, 763), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 764
                yield "        ";
                $context["options"] = Twig\Extension\CoreExtension::merge(["specific_tags" => ["required" => true]], ($context["options"] ?? null));
                // line 765
                yield "    ";
            }
            // line 766
            yield "
    ";
            // line 767
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 767), "isReadonlyField", [($context["name"] ?? null)], "method", true, true, false, 767)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, false, false, 767), "isReadonlyField", [($context["name"] ?? null)], "method", false, false, false, 767), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 768
                yield "        ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["readonly" => true]);
                // line 769
                yield "    ";
            }
            // line 770
            yield "
    ";
            // line 771
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "disabled", [], "any", false, false, false, 771)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 772
                yield "        ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["specific_tags" => ["disabled" => "disabled"]]);
                // line 773
                yield "    ";
            }
            // line 774
            yield "
    ";
            // line 775
            $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["id" => (("dropdown_" . Twig\Extension\CoreExtension::replace(            // line 776
($context["name"] ?? null), ["[" => "_", "]" => "_"])) . CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "rand", [], "any", false, false, false, 776))]);
            // line 778
            yield "    ";
            $context["field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 779
                yield "        ";
                $context["ajax_opts"] = Twig\Extension\CoreExtension::filter($this->env, $this->env->hasExtension(\Twig\Extension\SandboxExtension::class) && $this->env->getExtension(\Twig\Extension\SandboxExtension::class)->isSandboxed($this->source), ($context["options"] ?? null), function ($__v__, $__k__) use ($context, $macros) { $context["v"] = $__v__; $context["k"] = $__k__; return CoreExtension::inFilter(($context["k"] ?? null), ["templateResult", "templateSelection", "rand"]); });
                // line 780
                yield "        ";
                yield $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Html::jsAjaxDropdown", [($context["name"] ?? null), CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "id", [], "any", false, false, false, 780), ($context["url"] ?? null), ($context["ajax_opts"] ?? null)]);
                yield "
    ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 782
            yield "
    ";
            // line 783
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(Twig\Extension\CoreExtension::trim(($context["field"] ?? null)))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 784
                yield "        ";
                yield $this->getTemplateForMacro("macro_field", $context, 784, $this->getSourceContext())->macro_field(...[($context["name"] ?? null), ($context["field"] ?? null), ($context["label"] ?? null), ($context["options"] ?? null)]);
                yield "
    ";
            }
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 788
    public function macro_htmlField($name = null, $value = null, $label = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "value" => $value,
            "label" => $label,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 789
            yield "   ";
            if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["value"] ?? null)) == 0)) {
                // line 790
                yield "      ";
                $context["value"] = "&nbsp;";
                // line 791
                yield "   ";
            }
            // line 792
            yield "   ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["wrapper_class" => "form-control-plaintext"],             // line 794
($context["options"] ?? null));
            // line 795
            yield "
   ";
            // line 796
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 796), "isReadonlyField", [($context["name"] ?? null)], "method", true, true, false, 796)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, false, false, 796), "isReadonlyField", [($context["name"] ?? null)], "method", false, false, false, 796), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 797
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["readonly" => true]);
                // line 798
                yield "   ";
            }
            // line 799
            yield "
   ";
            // line 800
            $context["value"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 801
                yield "      <span class=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "wrapper_class", [], "any", false, false, false, 801), "html", null, true);
                yield "\">";
                yield ($context["value"] ?? null);
                yield "</span>
   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 803
            yield "   ";
            yield $this->getTemplateForMacro("macro_field", $context, 803, $this->getSourceContext())->macro_field(...[($context["name"] ?? null), ($context["value"] ?? null), ($context["label"] ?? null), ($context["options"] ?? null)]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 806
    public function macro_field($name = null, $field = null, $label = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "field" => $field,
            "label" => $label,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 807
            yield "   ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["rand" => Twig\Extension\CoreExtension::random($this->env->getCharset()), "is_horizontal" => true, "include_field" => true, "add_field_html" => "", "locked" => false, "locked_fields" => [], "no_label" => false],             // line 815
($context["options"] ?? null));
            // line 816
            yield "
   ";
            // line 817
            if (CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "locked_fields", [], "any", false, true, false, 817), ($context["name"] ?? null), [], "array", true, true, false, 817)) {
                // line 818
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["locked" => true, "locked_value" => (($_v14 = CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "locked_fields", [], "any", false, false, false, 818)) && is_array($_v14) || $_v14 instanceof ArrayAccess ? ($_v14[(($_v15 = ($context["name"] ?? null)) instanceof \Stringable ? (string) $_v15 : $_v15)] ?? null) : null)]);
                // line 819
                yield "   ";
            } elseif (CoreExtension::inFilter(($context["name"] ?? null), CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "locked_fields", [], "any", false, false, false, 819))) {
                // line 820
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["locked" => true]);
                // line 821
                yield "   ";
            }
            // line 822
            yield "
   ";
            // line 823
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 823), "isReadonlyField", [($context["name"] ?? null)], "method", true, true, false, 823)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, false, false, 823), "isReadonlyField", [($context["name"] ?? null)], "method", false, false, false, 823), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 824
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["readonly" => true]);
                // line 825
                yield "   ";
            }
            // line 826
            yield "
   ";
            // line 827
            if ((($tmp =  !CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "include_field", [], "any", false, false, false, 827)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 828
                yield "      ";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["field"] ?? null), "html", null, true);
                yield "
   ";
            } else {
                // line 830
                yield "      ";
                $context["id"] = Html::sanitizeDomId(((((Twig\Extension\CoreExtension::length($this->env->getCharset(), (((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "id", [], "any", true, true, false, 830) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "id", [], "any", false, false, false, 830)))) ? (CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "id", [], "any", false, false, false, 830)) : (""))) > 0) && (CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "id", [], "any", false, false, false, 830) != "%id%"))) ? (CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "id", [], "any", false, false, false, 830)) : (((($context["name"] ?? null) . "_") . CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "rand", [], "any", false, false, false, 830)))));
                // line 831
                yield "      ";
                // line 832
                yield "      ";
                $context["field"] = Twig\Extension\CoreExtension::replace(($context["field"] ?? null), [ (string)$this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape("%id%", "css"), "js") =>                 // line 833
($context["id"] ?? null),  (string)$this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape("%id%", "js") =>                 // line 834
($context["id"] ?? null),  (string)$this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape("%id%", "css") =>                 // line 835
($context["id"] ?? null), "%id%" =>                 // line 836
($context["id"] ?? null)]);
                // line 838
                yield "      ";
                $context["add_field_html"] = (((Twig\Extension\CoreExtension::length($this->env->getCharset(), CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "add_field_html", [], "any", false, false, false, 838)) > 0)) ? (CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "add_field_html", [], "any", false, false, false, 838)) : (""));
                // line 839
                yield "
      ";
                // line 840
                if ((($tmp =  !((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 840), "isHiddenField", [($context["name"] ?? null)], "method", true, true, false, 840)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, false, false, 840), "isHiddenField", [($context["name"] ?? null)], "method", false, false, false, 840), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 841
                    yield "         ";
                    if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "no_label", [], "any", false, false, false, 841)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                        // line 842
                        yield "            ";
                        yield $this->getTemplateForMacro("macro_noLabelField", $context, 842, $this->getSourceContext())->macro_noLabelField(...[($context["field"] ?? null), ($context["id"] ?? null), ($context["add_field_html"] ?? null), ($context["options"] ?? null)]);
                        yield "
         ";
                    } elseif ((($tmp = CoreExtension::getAttribute($this->env, $this->source,                     // line 843
($context["options"] ?? null), "is_horizontal", [], "any", false, false, false, 843)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                        // line 844
                        yield "            ";
                        yield $this->getTemplateForMacro("macro_horizontalField", $context, 844, $this->getSourceContext())->macro_horizontalField(...[($context["label"] ?? null), ($context["field"] ?? null), ($context["id"] ?? null), ($context["add_field_html"] ?? null), Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["name" => ($context["name"] ?? null)])]);
                        yield "
         ";
                    } else {
                        // line 846
                        yield "            ";
                        yield $this->getTemplateForMacro("macro_verticalField", $context, 846, $this->getSourceContext())->macro_verticalField(...[($context["label"] ?? null), ($context["field"] ?? null), ($context["id"] ?? null), ($context["add_field_html"] ?? null), Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["name" => ($context["name"] ?? null)])]);
                        yield "
         ";
                    }
                    // line 848
                    yield "      ";
                }
                // line 849
                yield "   ";
            }
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 852
    public function macro_ajaxField($id = null, $value = null, $label = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "id" => $id,
            "value" => $value,
            "label" => $label,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 853
            yield "   ";
            $context["field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 854
                yield "      <div id=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["id"] ?? null), "html", null, true);
                yield "\" class=\"form-field-ajax\">
         ";
                // line 855
                if ((($tmp =  !(null === ($context["value"] ?? null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 856
                    yield "            ";
                    yield ($context["value"] ?? null);
                    yield "
         ";
                }
                // line 858
                yield "      </div>
   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 860
            yield "   ";
            yield $this->getTemplateForMacro("macro_field", $context, 860, $this->getSourceContext())->macro_field(...[($context["id"] ?? null), ($context["field"] ?? null), ($context["label"] ?? null), Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["id" => ((($context["id"] ?? null) . "_") . (((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "rand", [], "any", true, true, false, 860) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "rand", [], "any", false, false, false, 860)))) ? (CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "rand", [], "any", false, false, false, 860)) : (Twig\Extension\CoreExtension::random($this->env->getCharset()))))])]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 863
    public function macro_nullField($options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 864
            yield "   ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["is_horizontal" => true], ($context["options"] ?? null));
            // line 865
            yield "
   ";
            // line 866
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "is_horizontal", [], "any", false, false, false, 866)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 867
                yield "      ";
                yield $this->getTemplateForMacro("macro_horizontalField", $context, 867, $this->getSourceContext())->macro_horizontalField(...[null, null, null, null, ($context["options"] ?? null)]);
                yield "
   ";
            } else {
                // line 869
                yield "      ";
                yield $this->getTemplateForMacro("macro_verticalField", $context, 869, $this->getSourceContext())->macro_verticalField(...[null, null, null, null, ($context["options"] ?? null)]);
                yield "
   ";
            }
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 874
    public function macro_noLabelField($field = null, $id = "", $add_field_html = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "field" => $field,
            "id" => $id,
            "add_field_html" => $add_field_html,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 875
            yield "   ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["full_width" => false, "mb" => "mb-3", "add_field_class" => "", "add_field_attribs" => [], "inline_add_field_html" => false],             // line 881
($context["options"] ?? null));
            // line 882
            yield "
   ";
            // line 883
            $context["class"] = (((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "field_class", [], "any", true, true, false, 883) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "field_class", [], "any", false, false, false, 883)))) ? (CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "field_class", [], "any", false, false, false, 883)) : ("col-12 col-sm-6"));
            // line 884
            yield "   ";
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "full_width", [], "any", false, false, false, 884)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 885
                yield "      ";
                $context["class"] = "col-12";
                // line 886
                yield "   ";
            }
            // line 887
            yield "   ";
            $context["class"] = ((($context["class"] ?? null) . " ") . CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "add_field_class", [], "any", false, false, false, 887));
            // line 888
            yield "
   ";
            // line 889
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "add_field_attribs", [], "any", false, false, false, 889))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 890
                yield "      ";
                $context["extra_attribs"] = $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Html::parseAttributes", ["options" => CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "add_field_attribs", [], "any", false, false, false, 890)]);
                // line 891
                yield "   ";
            } else {
                // line 892
                yield "      ";
                $context["extra_attribs"] = "";
                // line 893
                yield "   ";
            }
            // line 894
            yield "
   <div class=\"";
            // line 895
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["class"] ?? null), "html", null, true);
            yield " ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "mb", [], "any", false, false, false, 895), "html", null, true);
            yield " ";
            yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "inline_add_field_html", [], "any", false, false, false, 895)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("d-flex") : (""));
            yield "\" ";
            yield ($context["extra_attribs"] ?? null);
            yield ">
      ";
            // line 896
            yield ($context["field"] ?? null);
            yield "
      ";
            // line 897
            yield ($context["add_field_html"] ?? null);
            yield "
   </div>
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 902
    public function macro_horizontalField($label = null, $field = null, $id = null, $add_field_html = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "label" => $label,
            "field" => $field,
            "id" => $id,
            "add_field_html" => $add_field_html,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 903
            yield "   ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["full_width" => false, "align_label_right" => true, "mb" => "mb-2", "field_class" => "col-12 col-sm-6", "container_id" => "", "add_field_class" => "", "add_label_class" => "", "add_field_attribs" => [], "center" => false, "label_align" => "end", "inline_add_field_html" => false, "icon_label" => false],             // line 916
($context["options"] ?? null));
            // line 917
            yield "
   ";
            // line 918
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "icon_label", [], "any", false, false, false, 918)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 919
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(["label_class" => "col-2", "input_class" => "col-10"],                 // line 922
($context["options"] ?? null));
                // line 923
                yield "   ";
            }
            // line 924
            yield "
   ";
            // line 925
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "full_width", [], "any", false, false, false, 925)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 926
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["field_class" => "col-12 glpi-full-width"]);
                // line 929
                yield "   ";
            }
            // line 930
            yield "
   ";
            // line 931
            $context["options"] = Twig\Extension\CoreExtension::merge(["label_class" => "col-xxl-5", "input_class" => "col-xxl-7"],             // line 934
($context["options"] ?? null));
            // line 935
            yield "
   ";
            // line 936
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "align_label_right", [], "any", false, false, false, 936)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 937
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["label_class" => ((CoreExtension::getAttribute($this->env, $this->source,                 // line 938
($context["options"] ?? null), "label_class", [], "any", false, false, false, 938) . " text-xxl-") . CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "label_align", [], "any", false, false, false, 938))]);
                // line 940
                yield "   ";
            }
            // line 941
            yield "
   ";
            // line 942
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "add_field_attribs", [], "any", false, false, false, 942))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 943
                yield "      ";
                $context["extra_attribs"] = $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Html::parseAttributes", ["options" => CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "add_field_attribs", [], "any", false, false, false, 943)]);
                // line 944
                yield "   ";
            } else {
                // line 945
                yield "      ";
                $context["extra_attribs"] = "";
                // line 946
                yield "   ";
            }
            // line 947
            yield "
   ";
            // line 949
            yield "   ";
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "container_id", [], "any", false, false, false, 949))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 950
                yield "      ";
                $context["container_id"] = ("id=" . CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "container_id", [], "any", false, false, false, 950));
                // line 951
                yield "   ";
            } else {
                // line 952
                yield "      ";
                $context["container_id"] = "";
                // line 953
                yield "   ";
            }
            // line 954
            yield "
   <div class=\"form-field row align-items-center ";
            // line 955
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "field_class", [], "any", false, false, false, 955), "html", null, true);
            yield " ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "add_field_class", [], "any", false, false, false, 955), "html", null, true);
            yield " ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "mb", [], "any", false, false, false, 955), "html", null, true);
            yield "\" ";
            yield ($context["extra_attribs"] ?? null);
            yield " ";
            if ((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "name", [], "any", true, true, false, 955) &&  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "name", [], "any", false, false, false, 955)))) {
                yield "data-testid=\"form-field-";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "name", [], "any", false, false, false, 955), "html", null, true);
                yield "\"";
            }
            yield ">
      ";
            // line 956
            $macros["_inputs"] = $this->load("components/form/basic_inputs_macros.html.twig", 956)->unwrap();
            // line 957
            yield "      ";
            yield $macros["_inputs"]->getTemplateForMacro("macro_label", $context, 957, $this->getSourceContext())->macro_label(...[($context["label"] ?? null), ($context["id"] ?? null), ($context["options"] ?? null), ((("col-form-label " . CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "label_class", [], "any", false, false, false, 957)) . " ") . CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "add_label_class", [], "any", false, false, false, 957))]);
            yield "
      ";
            // line 958
            $context["flex_class"] = (((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "center", [], "any", false, false, false, 958)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("d-flex align-items-center") : ((((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "inline_add_field_html", [], "any", false, false, false, 958)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("d-flex") : (""))));
            // line 959
            yield "      <div ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["container_id"] ?? null), "html", null, true);
            yield " class=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "input_class", [], "any", false, false, false, 959), "html", null, true);
            yield " ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["flex_class"] ?? null), "html", null, true);
            yield " field-container\">
         ";
            // line 960
            yield ($context["field"] ?? null);
            yield "
         ";
            // line 961
            yield ($context["add_field_html"] ?? null);
            yield "
      </div>
   </div>
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 967
    public function macro_verticalField($label = null, $field = null, $id = null, $add_field_html = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "label" => $label,
            "field" => $field,
            "id" => $id,
            "add_field_html" => $add_field_html,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 968
            yield "   ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["full_width" => false, "mb" => "mb-2", "field_class" => "col-12 col-sm-6", "add_field_class" => "", "add_field_attribs" => [], "insert_content_after_label" => "", "label_class" => "", "input_class" => ""],             // line 977
($context["options"] ?? null));
            // line 978
            yield "
   ";
            // line 979
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "full_width", [], "any", false, false, false, 979)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 980
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["field_class" => "col-12"]);
                // line 983
                yield "   ";
            }
            // line 984
            yield "
   ";
            // line 985
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "add_field_attribs", [], "any", false, false, false, 985))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 986
                yield "      ";
                $context["extra_attribs"] = $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Html::parseAttributes", ["options" => CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "add_field_attribs", [], "any", false, false, false, 986)]);
                // line 987
                yield "   ";
            } else {
                // line 988
                yield "      ";
                $context["extra_attribs"] = "";
                // line 989
                yield "   ";
            }
            // line 990
            yield "
   <div class=\"form-field ";
            // line 991
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "field_class", [], "any", false, false, false, 991), "html", null, true);
            yield " ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "add_field_class", [], "any", false, false, false, 991), "html", null, true);
            yield " ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "mb", [], "any", false, false, false, 991), "html", null, true);
            yield "\" ";
            yield ($context["extra_attribs"] ?? null);
            yield " ";
            if ((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "name", [], "any", true, true, false, 991) &&  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "name", [], "any", false, false, false, 991)))) {
                yield "data-testid=\"form-field-";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "name", [], "any", false, false, false, 991), "html", null, true);
                yield "\"";
            }
            yield ">
      ";
            // line 992
            $macros["_inputs"] = $this->load("components/form/basic_inputs_macros.html.twig", 992)->unwrap();
            // line 993
            yield "      <div class=\"d-flex align-items-center\">
         ";
            // line 994
            yield $macros["_inputs"]->getTemplateForMacro("macro_label", $context, 994, $this->getSourceContext())->macro_label(...[($context["label"] ?? null), ($context["id"] ?? null), ($context["options"] ?? null), ("col-form-label " . CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "label_class", [], "any", false, false, false, 994))]);
            yield "
         ";
            // line 995
            yield CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "insert_content_after_label", [], "any", false, false, false, 995);
            yield "
      </div>
      <div class=\"";
            // line 997
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "input_class", [], "any", false, false, false, 997), "html", null, true);
            yield " field-container\">
         ";
            // line 998
            yield ($context["field"] ?? null);
            yield "
      </div>
      ";
            // line 1000
            yield ($context["add_field_html"] ?? null);
            yield "
   </div>
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 1004
    public function macro_label($label = null, $id = null, $options = [], $class = "form-label", ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "label" => $label,
            "id" => $id,
            "options" => $options,
            "class" => $class,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 1005
            yield "    ";
            $macros["_inputs"] = $this->load("components/form/basic_inputs_macros.html.twig", 1005)->unwrap();
            // line 1006
            yield "    ";
            yield $macros["_inputs"]->getTemplateForMacro("macro_label", $context, 1006, $this->getSourceContext())->macro_label(...[($context["label"] ?? null), ($context["id"] ?? null), ($context["options"] ?? null), ($context["class"] ?? null)]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 1009
    public function macro_codeField($name = null, $value = null, $label = null, $options = null, ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "value" => $value,
            "label" => $label,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 1010
            yield "    ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["single_line" => false, "language" => "twig", "completions" => [], "helper" => Twig\Extension\CoreExtension::sprintf(__("This field accepts %s content. Press Ctrl+Space to trigger autocompletion."), "Twig")],             // line 1015
($context["options"] ?? null));
            // line 1016
            yield "
    ";
            // line 1017
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "helper", [], "any", false, false, false, 1017))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 1018
                yield "        ";
                $context["options"] = Twig\Extension\CoreExtension::merge(($context["options"] ?? null), ["helper" => Twig\Extension\CoreExtension::sprintf(CoreExtension::getAttribute($this->env, $this->source,                 // line 1019
($context["options"] ?? null), "helper", [], "any", false, false, false, 1019), CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "language", [], "any", false, false, false, 1019))]);
                // line 1021
                yield "    ";
            }
            // line 1022
            yield "
    ";
            // line 1023
            $context["code_container_id"] = ((($context["name"] ?? null) . "_") . Twig\Extension\CoreExtension::random($this->env->getCharset()));
            // line 1024
            yield "    ";
            $context["code_container"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 1025
                yield "        <div id=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["code_container_id"] ?? null), "html", null, true);
                yield "\" class=\"form-control overflow-hidden text-start\" style=\"height: ";
                yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "single_line", [], "any", false, false, false, 1025)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("36px") : ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "height", [], "any", true, true, false, 1025)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "height", [], "any", false, false, false, 1025), "auto")) : ("auto")), "html", null, true)));
                yield ";\"></div>
    ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 1027
            yield "    ";
            yield $this->getTemplateForMacro("macro_htmlField", $context, 1027, $this->getSourceContext())->macro_htmlField(...[($context["name"] ?? null), ($context["code_container"] ?? null), ($context["label"] ?? null), Twig\Extension\CoreExtension::merge(["wrapper_class" => "d-flex flex-grow-1"],             // line 1029
($context["options"] ?? null))]);
            yield "
    <script>
        \$(() => {
            const editor_options = ";
            // line 1032
            yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "single_line", [], "any", false, false, false, 1032)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("true") : ("false"));
            yield " ? window.GLPI.Monaco.getSingleLineEditorOptions() : {};
            window.GLPI.Monaco.createEditor(\x27";
            // line 1033
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["code_container_id"] ?? null), "js"), "html", null, true);
            yield "\x27, \x27";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "language", [], "any", false, false, false, 1033), "js"), "html", null, true);
            yield "\x27, \"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["value"] ?? null), "js"), "html", null, true);
            yield "\", ";
            yield json_encode(CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "completions", [], "any", false, false, false, 1033));
            yield ", editor_options).then(() => {
                \$(\x27#";
            // line 1034
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["code_container_id"] ?? null), "css"), "js"), "html", null, true);
            yield "\x27).closest(\x27form\x27).on(\x27formdata\x27, (e) => {
                    const editors = window.monaco.editor.getEditors().filter((editor) => {
                        return editor._domElement.id === \x27";
            // line 1036
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["code_container_id"] ?? null), "js"), "html", null, true);
            yield "\x27;
                    });
                    if (editors.length) {
                        e.originalEvent.formData.delete(\x27";
            // line 1039
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["name"] ?? null), "js"), "html", null, true);
            yield "\x27);
                        e.originalEvent.formData.append(\x27";
            // line 1040
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["name"] ?? null), "js"), "html", null, true);
            yield "\x27, editors[0].getValue());
                    }
                });
            });
        });
    </script>
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 1048
    public function macro_illustrationField($name = null, $value = null, $label = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "value" => $value,
            "label" => $label,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 1049
            yield "    ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["extra_css_classes" => "", "backdrop" => true],             // line 1052
($context["options"] ?? null));
            // line 1053
            yield "    ";
            $context["custom_icon_prefix"] = Twig\Extension\CoreExtension::constant("Glpi\\UI\\IllustrationManager::CUSTOM_ILLUSTRATION_PREFIX");
            // line 1056
            yield "    ";
            $context["field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 1057
                yield "        ";
                $context["container_id"] = ("container-" . Twig\Extension\CoreExtension::random($this->env->getCharset()));
                // line 1058
                yield "
        <div id=\"";
                // line 1059
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["container_id"] ?? null), "html", null, true);
                yield "\">
            <input
                name=\"";
                // line 1061
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["name"] ?? null), "html", null, true);
                yield "\"
                type=\"hidden\"
                value=\"";
                // line 1063
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["value"] ?? null), "html", null, true);
                yield "\"
                data-glpi-icon-picker-value
            >

            ";
                // line 1068
                yield "            ";
                $context["modal_id"] = ("illustration-modal-" . Twig\Extension\CoreExtension::random($this->env->getCharset()));
                // line 1069
                yield "            <div
                class=\"illustration-selector d-flex align-items-center card border-1 ";
                // line 1070
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "extra_css_classes", [], "any", false, false, false, 1070), "html", null, true);
                yield "\"
                role=\"button\"
                aria-label=\"";
                // line 1072
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Select an illustration"), "html", null, true);
                yield "\"
                data-bs-toggle=\"modal\"
                data-bs-target=\"#";
                // line 1074
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["modal_id"] ?? null), "html", null, true);
                yield "\"
                data-glpi-icon-picker-value-preview
            >
                <div class=\"card-body aspect-ratio-1\">
                    ";
                // line 1078
                $context["is_custom_file"] = (is_string($_v16 = ($context["value"] ?? null)) && is_string($_v17 = ($context["custom_icon_prefix"] ?? null)) && str_starts_with($_v16, $_v17));
                // line 1079
                yield "                    <div
                        ";
                // line 1080
                if ((($tmp = ($context["is_custom_file"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 1081
                    yield "                            data-glpi-icon-picker-value-preview-custom
                            data-testid=\"illustration-custom-preview\"
                        ";
                } else {
                    // line 1084
                    yield "                            data-glpi-icon-picker-value-preview-native
                        ";
                }
                // line 1086
                yield "                    >
                        ";
                // line 1087
                yield $this->extensions['Glpi\Application\View\Extension\IllustrationExtension']->renderIllustration(($context["value"] ?? null), 100);
                yield "
                    </div>

                    ";
                // line 1093
                yield "                    ";
                if ((($tmp = ($context["is_custom_file"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 1094
                    yield "                        <div
                            class=\"d-none\"
                            data-glpi-icon-picker-value-preview-native
                        >
                           ";
                    // line 1098
                    yield $this->extensions['Glpi\Application\View\Extension\IllustrationExtension']->renderIllustration("", 100);
                    yield "
                        </div>
                    ";
                } else {
                    // line 1101
                    yield "                        <div
                            class=\"d-none\"
                            data-glpi-icon-picker-value-preview-custom
                            data-testid=\"illustration-custom-preview\"
                        >
                           ";
                    // line 1106
                    yield $this->extensions['Glpi\Application\View\Extension\IllustrationExtension']->renderIllustration(($context["custom_icon_prefix"] ?? null), 100);
                    yield "
                        </div>
                    ";
                }
                // line 1109
                yield "                </div>
            </div>

            ";
                // line 1113
                yield "            ";
                yield Twig\Extension\CoreExtension::include($this->env, $context, "components/illustration/icon_picker_modal.html.twig", ["id" =>                 // line 1114
($context["modal_id"] ?? null), "backdrop" => CoreExtension::getAttribute($this->env, $this->source,                 // line 1115
($context["options"] ?? null), "backdrop", [], "any", false, false, false, 1115)], false);
                // line 1116
                yield "

            ";
                // line 1119
                yield "            <script defer type=\"module\">
                (async () => {
                    const module = await import(
                        \"/js/modules/IllustrationPicker/Controller.js\"
                    );
                    new module.GlpiIllustrationPickerController(
                        document.getElementById(\x27";
                // line 1125
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["container_id"] ?? null), "js"), "html", null, true);
                yield "\x27),
                        document.getElementById(\x27";
                // line 1126
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["modal_id"] ?? null), "js"), "html", null, true);
                yield "\x27),
                        \"";
                // line 1127
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["custom_icon_prefix"] ?? null), "js"), "html", null, true);
                yield "\",
                    );
                })();
            </script>
        </div>
    ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 1133
            yield "
    ";
            // line 1134
            $context["options"] = Twig\Extension\CoreExtension::merge(["id" => "%id%"],             // line 1136
($context["options"] ?? null));
            // line 1137
            yield "    ";
            yield $this->getTemplateForMacro("macro_field", $context, 1137, $this->getSourceContext())->macro_field(...[($context["name"] ?? null), ($context["field"] ?? null), ($context["label"] ?? null), ($context["options"] ?? null)]);
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
        return "components/form/fields_macros.html.twig";
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
        return array (  3203 => 1137,  3201 => 1136,  3200 => 1134,  3197 => 1133,  3187 => 1127,  3183 => 1126,  3179 => 1125,  3171 => 1119,  3167 => 1116,  3165 => 1115,  3164 => 1114,  3162 => 1113,  3157 => 1109,  3151 => 1106,  3144 => 1101,  3138 => 1098,  3132 => 1094,  3129 => 1093,  3123 => 1087,  3120 => 1086,  3116 => 1084,  3111 => 1081,  3109 => 1080,  3106 => 1079,  3104 => 1078,  3097 => 1074,  3092 => 1072,  3087 => 1070,  3084 => 1069,  3081 => 1068,  3074 => 1063,  3069 => 1061,  3064 => 1059,  3061 => 1058,  3058 => 1057,  3055 => 1056,  3052 => 1053,  3050 => 1052,  3048 => 1049,  3033 => 1048,  3020 => 1040,  3016 => 1039,  3010 => 1036,  3005 => 1034,  2995 => 1033,  2991 => 1032,  2985 => 1029,  2983 => 1027,  2974 => 1025,  2971 => 1024,  2969 => 1023,  2966 => 1022,  2963 => 1021,  2961 => 1019,  2959 => 1018,  2957 => 1017,  2954 => 1016,  2952 => 1015,  2950 => 1010,  2935 => 1009,  2926 => 1006,  2923 => 1005,  2908 => 1004,  2899 => 1000,  2894 => 998,  2890 => 997,  2885 => 995,  2881 => 994,  2878 => 993,  2876 => 992,  2860 => 991,  2857 => 990,  2854 => 989,  2851 => 988,  2848 => 987,  2845 => 986,  2843 => 985,  2840 => 984,  2837 => 983,  2834 => 980,  2832 => 979,  2829 => 978,  2827 => 977,  2825 => 968,  2809 => 967,  2799 => 961,  2795 => 960,  2786 => 959,  2784 => 958,  2779 => 957,  2777 => 956,  2761 => 955,  2758 => 954,  2755 => 953,  2752 => 952,  2749 => 951,  2746 => 950,  2743 => 949,  2740 => 947,  2737 => 946,  2734 => 945,  2731 => 944,  2728 => 943,  2726 => 942,  2723 => 941,  2720 => 940,  2718 => 938,  2716 => 937,  2714 => 936,  2711 => 935,  2709 => 934,  2708 => 931,  2705 => 930,  2702 => 929,  2699 => 926,  2697 => 925,  2694 => 924,  2691 => 923,  2689 => 922,  2687 => 919,  2685 => 918,  2682 => 917,  2680 => 916,  2678 => 903,  2662 => 902,  2653 => 897,  2649 => 896,  2639 => 895,  2636 => 894,  2633 => 893,  2630 => 892,  2627 => 891,  2624 => 890,  2622 => 889,  2619 => 888,  2616 => 887,  2613 => 886,  2610 => 885,  2607 => 884,  2605 => 883,  2602 => 882,  2600 => 881,  2598 => 875,  2583 => 874,  2573 => 869,  2567 => 867,  2565 => 866,  2562 => 865,  2559 => 864,  2547 => 863,  2538 => 860,  2533 => 858,  2527 => 856,  2525 => 855,  2520 => 854,  2517 => 853,  2502 => 852,  2495 => 849,  2492 => 848,  2486 => 846,  2480 => 844,  2478 => 843,  2473 => 842,  2470 => 841,  2468 => 840,  2465 => 839,  2462 => 838,  2460 => 836,  2459 => 835,  2458 => 834,  2457 => 833,  2455 => 832,  2453 => 831,  2450 => 830,  2444 => 828,  2442 => 827,  2439 => 826,  2436 => 825,  2433 => 824,  2431 => 823,  2428 => 822,  2425 => 821,  2422 => 820,  2419 => 819,  2416 => 818,  2414 => 817,  2411 => 816,  2409 => 815,  2407 => 807,  2392 => 806,  2383 => 803,  2374 => 801,  2372 => 800,  2369 => 799,  2366 => 798,  2363 => 797,  2361 => 796,  2358 => 795,  2356 => 794,  2354 => 792,  2351 => 791,  2348 => 790,  2345 => 789,  2330 => 788,  2320 => 784,  2318 => 783,  2315 => 782,  2308 => 780,  2305 => 779,  2302 => 778,  2300 => 776,  2299 => 775,  2296 => 774,  2293 => 773,  2290 => 772,  2288 => 771,  2285 => 770,  2282 => 769,  2279 => 768,  2277 => 767,  2274 => 766,  2271 => 765,  2268 => 764,  2265 => 763,  2263 => 762,  2261 => 759,  2258 => 758,  2255 => 757,  2249 => 754,  2246 => 753,  2244 => 752,  2241 => 751,  2225 => 750,  2215 => 746,  2213 => 745,  2210 => 744,  2204 => 742,  2203 => 741,  2202 => 740,  2200 => 739,  2198 => 738,  2195 => 737,  2192 => 736,  2189 => 735,  2187 => 734,  2184 => 733,  2181 => 732,  2178 => 731,  2176 => 730,  2173 => 729,  2170 => 728,  2167 => 727,  2164 => 726,  2162 => 725,  2160 => 721,  2157 => 720,  2154 => 719,  2148 => 716,  2145 => 715,  2143 => 714,  2140 => 713,  2124 => 712,  2116 => 709,  2113 => 708,  2109 => 707,  2107 => 706,  2106 => 705,  2104 => 703,  2102 => 702,  2099 => 701,  2096 => 700,  2093 => 699,  2091 => 698,  2088 => 697,  2085 => 696,  2082 => 695,  2080 => 694,  2077 => 693,  2074 => 692,  2071 => 691,  2068 => 690,  2066 => 689,  2064 => 686,  2049 => 685,  2041 => 682,  2038 => 681,  2034 => 680,  2032 => 679,  2031 => 678,  2029 => 677,  2027 => 676,  2024 => 675,  2021 => 674,  2018 => 673,  2016 => 672,  2013 => 671,  2010 => 670,  2007 => 669,  2005 => 668,  2002 => 667,  1999 => 666,  1996 => 665,  1993 => 664,  1991 => 663,  1989 => 659,  1974 => 658,  1962 => 651,  1956 => 648,  1953 => 647,  1950 => 646,  1948 => 645,  1946 => 642,  1944 => 640,  1929 => 639,  1921 => 636,  1918 => 635,  1914 => 634,  1911 => 633,  1909 => 632,  1906 => 631,  1903 => 630,  1900 => 629,  1898 => 628,  1895 => 627,  1892 => 626,  1889 => 625,  1887 => 624,  1884 => 623,  1881 => 622,  1878 => 621,  1875 => 620,  1873 => 619,  1871 => 615,  1856 => 614,  1847 => 611,  1843 => 610,  1840 => 609,  1838 => 608,  1835 => 607,  1832 => 606,  1829 => 605,  1827 => 604,  1824 => 603,  1822 => 602,  1820 => 600,  1806 => 599,  1798 => 596,  1795 => 595,  1791 => 594,  1789 => 593,  1788 => 592,  1787 => 591,  1785 => 590,  1783 => 589,  1780 => 588,  1778 => 587,  1775 => 586,  1772 => 585,  1769 => 584,  1767 => 583,  1764 => 582,  1761 => 581,  1758 => 580,  1756 => 579,  1753 => 578,  1750 => 577,  1747 => 576,  1744 => 575,  1742 => 574,  1740 => 570,  1725 => 569,  1717 => 566,  1714 => 565,  1710 => 564,  1707 => 563,  1705 => 562,  1702 => 561,  1699 => 560,  1696 => 559,  1694 => 558,  1691 => 557,  1688 => 556,  1685 => 555,  1683 => 554,  1680 => 553,  1677 => 552,  1674 => 551,  1671 => 550,  1669 => 549,  1667 => 545,  1652 => 544,  1644 => 541,  1641 => 540,  1637 => 539,  1635 => 538,  1634 => 537,  1632 => 536,  1630 => 535,  1627 => 534,  1624 => 533,  1621 => 532,  1619 => 531,  1616 => 530,  1613 => 529,  1610 => 528,  1608 => 527,  1605 => 526,  1602 => 525,  1599 => 524,  1596 => 523,  1594 => 522,  1592 => 518,  1577 => 517,  1569 => 514,  1566 => 513,  1562 => 512,  1560 => 511,  1559 => 510,  1558 => 509,  1556 => 508,  1554 => 507,  1551 => 506,  1548 => 505,  1545 => 504,  1543 => 503,  1540 => 502,  1537 => 501,  1534 => 500,  1532 => 499,  1529 => 498,  1526 => 497,  1523 => 496,  1521 => 495,  1518 => 494,  1516 => 493,  1514 => 489,  1498 => 488,  1490 => 485,  1487 => 484,  1483 => 483,  1481 => 482,  1480 => 481,  1479 => 480,  1477 => 479,  1475 => 478,  1472 => 477,  1469 => 476,  1466 => 475,  1463 => 474,  1460 => 473,  1457 => 472,  1455 => 471,  1452 => 470,  1449 => 469,  1446 => 468,  1444 => 467,  1441 => 466,  1439 => 465,  1437 => 461,  1422 => 460,  1413 => 457,  1402 => 456,  1393 => 453,  1390 => 452,  1387 => 451,  1384 => 450,  1382 => 444,  1379 => 443,  1365 => 442,  1358 => 439,  1356 => 436,  1351 => 435,  1348 => 434,  1346 => 433,  1343 => 432,  1340 => 431,  1337 => 430,  1335 => 429,  1332 => 428,  1327 => 426,  1325 => 423,  1322 => 422,  1314 => 419,  1312 => 417,  1311 => 414,  1308 => 413,  1304 => 412,  1298 => 409,  1295 => 408,  1292 => 407,  1277 => 406,  1268 => 403,  1265 => 402,  1262 => 401,  1259 => 400,  1254 => 398,  1247 => 394,  1242 => 393,  1229 => 384,  1227 => 383,  1223 => 382,  1220 => 381,  1216 => 379,  1214 => 378,  1207 => 377,  1199 => 375,  1196 => 374,  1193 => 373,  1190 => 372,  1188 => 371,  1185 => 370,  1182 => 369,  1166 => 368,  1157 => 365,  1150 => 363,  1147 => 362,  1144 => 361,  1142 => 360,  1140 => 356,  1125 => 355,  1117 => 352,  1114 => 351,  1107 => 349,  1104 => 348,  1102 => 347,  1099 => 346,  1097 => 345,  1095 => 343,  1080 => 342,  1072 => 338,  1069 => 337,  1066 => 336,  1063 => 335,  1050 => 332,  1047 => 331,  1044 => 330,  1041 => 329,  1038 => 327,  1031 => 325,  1028 => 324,  1026 => 323,  1023 => 322,  1021 => 321,  1020 => 320,  1019 => 319,  1018 => 318,  1016 => 316,  1001 => 315,  993 => 311,  990 => 310,  983 => 308,  980 => 307,  978 => 306,  975 => 305,  973 => 304,  971 => 302,  956 => 301,  948 => 297,  945 => 296,  938 => 294,  935 => 293,  932 => 292,  917 => 291,  909 => 287,  906 => 286,  899 => 284,  896 => 283,  893 => 282,  878 => 281,  870 => 277,  867 => 276,  864 => 275,  861 => 274,  858 => 273,  855 => 272,  852 => 271,  850 => 270,  847 => 269,  844 => 268,  840 => 267,  838 => 265,  837 => 264,  836 => 263,  835 => 261,  834 => 260,  832 => 259,  829 => 258,  826 => 257,  822 => 256,  820 => 254,  819 => 253,  818 => 251,  816 => 250,  813 => 249,  810 => 248,  808 => 247,  805 => 246,  798 => 244,  795 => 243,  793 => 242,  790 => 241,  787 => 240,  784 => 239,  782 => 238,  780 => 237,  777 => 236,  775 => 235,  774 => 227,  772 => 221,  757 => 220,  748 => 216,  743 => 214,  737 => 212,  733 => 210,  731 => 209,  726 => 208,  723 => 207,  720 => 206,  705 => 205,  697 => 201,  694 => 200,  687 => 198,  684 => 197,  682 => 196,  679 => 195,  677 => 194,  675 => 192,  660 => 191,  652 => 187,  649 => 186,  644 => 184,  638 => 182,  636 => 181,  633 => 180,  622 => 179,  618 => 178,  614 => 177,  610 => 176,  606 => 175,  602 => 174,  596 => 173,  590 => 172,  587 => 171,  585 => 170,  582 => 169,  580 => 168,  578 => 160,  575 => 159,  572 => 158,  569 => 157,  566 => 156,  564 => 155,  562 => 153,  559 => 152,  544 => 151,  536 => 147,  533 => 146,  526 => 144,  523 => 143,  521 => 142,  518 => 141,  516 => 140,  514 => 137,  499 => 136,  491 => 133,  488 => 132,  483 => 130,  480 => 128,  477 => 127,  475 => 126,  472 => 125,  470 => 124,  468 => 122,  453 => 121,  445 => 118,  442 => 117,  435 => 115,  432 => 114,  430 => 113,  427 => 112,  424 => 111,  421 => 110,  419 => 106,  417 => 105,  415 => 104,  412 => 103,  409 => 102,  407 => 101,  404 => 100,  402 => 99,  400 => 97,  385 => 96,  377 => 92,  374 => 91,  371 => 90,  368 => 89,  365 => 88,  362 => 87,  359 => 86,  356 => 85,  354 => 83,  352 => 82,  348 => 81,  345 => 80,  329 => 79,  320 => 74,  315 => 72,  312 => 71,  310 => 70,  305 => 69,  299 => 66,  296 => 65,  294 => 64,  290 => 63,  286 => 62,  282 => 61,  279 => 60,  276 => 59,  273 => 58,  258 => 57,  249 => 52,  244 => 50,  241 => 49,  239 => 48,  234 => 47,  228 => 44,  225 => 43,  223 => 42,  219 => 41,  214 => 39,  211 => 38,  208 => 37,  205 => 36,  202 => 35,  199 => 34,  184 => 33,  178 => 1047,  175 => 1008,  172 => 1003,  168 => 965,  164 => 900,  160 => 872,  157 => 862,  154 => 851,  151 => 805,  148 => 787,  145 => 749,  142 => 711,  139 => 684,  136 => 657,  133 => 638,  130 => 613,  127 => 598,  124 => 568,  121 => 543,  118 => 516,  115 => 487,  112 => 459,  109 => 455,  106 => 441,  103 => 405,  100 => 367,  97 => 354,  93 => 340,  89 => 313,  85 => 299,  81 => 289,  77 => 279,  73 => 218,  69 => 203,  65 => 189,  61 => 149,  58 => 135,  55 => 120,  51 => 94,  48 => 78,  45 => 56,  42 => 32,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "components/form/fields_macros.html.twig", "/var/www/html/glpi/templates/components/form/fields_macros.html.twig");
    }
}
