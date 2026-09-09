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
class __TwigTemplate_952e2ef28faa07d42da2861c38117691 extends Template
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
            if ((($tmp = (isset($context["first"]) || array_key_exists("first", $context) ? $context["first"] : (function () { throw new RuntimeError('Variable "first" does not exist.', 35, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
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
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["margins"]) || array_key_exists("margins", $context) ? $context["margins"] : (function () { throw new RuntimeError('Variable "margins" does not exist.', 39, $this->source); })()), "html", null, true);
            yield "\">
      <div class=\"card-header mb-3 pt-2 border-top rounded-0\">
         <h4 class=\"card-title ";
            // line 41
            yield (((($tmp = Twig\Extension\CoreExtension::length($this->env->getCharset(), (isset($context["icon"]) || array_key_exists("icon", $context) ? $context["icon"] : (function () { throw new RuntimeError('Variable "icon" does not exist.', 41, $this->source); })()))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("ms-5") : (""));
            yield "\">
            ";
            // line 42
            if ((($tmp = Twig\Extension\CoreExtension::length($this->env->getCharset(), (isset($context["icon"]) || array_key_exists("icon", $context) ? $context["icon"] : (function () { throw new RuntimeError('Variable "icon" does not exist.', 42, $this->source); })()))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 43
                yield "               <div class=\"ribbon ribbon-bookmark ribbon-top ribbon-start bg-blue s-1\">
                  <i class=\"fs-2x ";
                // line 44
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["icon"]) || array_key_exists("icon", $context) ? $context["icon"] : (function () { throw new RuntimeError('Variable "icon" does not exist.', 44, $this->source); })()), "html", null, true);
                yield "\"></i>
               </div>
            ";
            }
            // line 47
            yield "            ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 47, $this->source); })()), "html", null, true);
            yield "
            ";
            // line 48
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty((isset($context["helper"]) || array_key_exists("helper", $context) ? $context["helper"] : (function () { throw new RuntimeError('Variable "helper" does not exist.', 48, $this->source); })()))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 49
                yield "               <span class=\"form-help\" data-bs-toggle=\"tooltip\" data-bs-placement=\"top\" data-bs-html=\"true\"
                     data-bs-title=\"";
                // line 50
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["helper"]) || array_key_exists("helper", $context) ? $context["helper"] : (function () { throw new RuntimeError('Variable "helper" does not exist.', 50, $this->source); })()), "html", null, true);
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
            $context["id"] = ((((isset($context["id"]) || array_key_exists("id", $context) ? $context["id"] : (function () { throw new RuntimeError('Variable "id" does not exist.', 59, $this->source); })()) != "")) ? ((isset($context["id"]) || array_key_exists("id", $context) ? $context["id"] : (function () { throw new RuntimeError('Variable "id" does not exist.', 59, $this->source); })())) : (("formsection" . Twig\Extension\CoreExtension::random($this->env->getCharset()))));
            // line 60
            yield "
   <div class=\"card border-0 shadow-none p-0 m-0 ";
            // line 61
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["margins"]) || array_key_exists("margins", $context) ? $context["margins"] : (function () { throw new RuntimeError('Variable "margins" does not exist.', 61, $this->source); })()), "html", null, true);
            yield "\">
      <div id=\"";
            // line 62
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["id"]) || array_key_exists("id", $context) ? $context["id"] : (function () { throw new RuntimeError('Variable "id" does not exist.', 62, $this->source); })()), "html", null, true);
            yield "\" class=\"card-header mb-1 p-0 ps-3 py-1\">
         <h4 class=\"card-subtitle ";
            // line 63
            yield (((($tmp = Twig\Extension\CoreExtension::length($this->env->getCharset(), (isset($context["icon"]) || array_key_exists("icon", $context) ? $context["icon"] : (function () { throw new RuntimeError('Variable "icon" does not exist.', 63, $this->source); })()))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("ms-4") : (""));
            yield "\">
            ";
            // line 64
            if ((($tmp = Twig\Extension\CoreExtension::length($this->env->getCharset(), (isset($context["icon"]) || array_key_exists("icon", $context) ? $context["icon"] : (function () { throw new RuntimeError('Variable "icon" does not exist.', 64, $this->source); })()))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 65
                yield "               <div class=\"ribbon ribbon-bookmark ribbon-top ribbon-start bg-blue s-1\">
                  <i class=\"fs-2x ";
                // line 66
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["icon"]) || array_key_exists("icon", $context) ? $context["icon"] : (function () { throw new RuntimeError('Variable "icon" does not exist.', 66, $this->source); })()), "html", null, true);
                yield "\"></i>
               </div>
            ";
            }
            // line 69
            yield "             <span class=\"ms-2\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 69, $this->source); })()), "html", null, true);
            yield "</span>
            ";
            // line 70
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty((isset($context["helper"]) || array_key_exists("helper", $context) ? $context["helper"] : (function () { throw new RuntimeError('Variable "helper" does not exist.', 70, $this->source); })()))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 71
                yield "               <span class=\"form-help\" data-bs-toggle=\"tooltip\" data-bs-placement=\"top\" data-bs-html=\"true\"
                     data-bs-title=\"";
                // line 72
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["helper"]) || array_key_exists("helper", $context) ? $context["helper"] : (function () { throw new RuntimeError('Variable "helper" does not exist.', 72, $this->source); })()), "html", null, true);
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
            $context["tpl_value"] = (((Twig\Extension\CoreExtension::length($this->env->getCharset(), (((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "value", [], "any", true, true, false, 80) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 80, $this->source); })()), "value", [], "any", false, false, false, 80)))) ? (CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 80, $this->source); })()), "value", [], "any", false, false, false, 80)) : (""))) > 0)) ? (CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 80, $this->source); })()), "value", [], "any", false, false, false, 80)) : (CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 80, $this->source); })()), "fields", [], "any", false, false, false, 80), (isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 80, $this->source); })()), [], "array", false, false, false, 80)));
            // line 81
            yield "   ";
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 81, $this->source); })()), "isTemplate", [], "method", false, false, false, 81)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                yield " ";
                // line 82
                yield "       ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 82, $this->source); })()), ["tpl_mark" => CoreExtension::getAttribute($this->env, $this->source,                 // line 83
(isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 83, $this->source); })()), "getAutofillMark", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 83, $this->source); })()), ["withtemplate" => (isset($context["withtemplate"]) || array_key_exists("withtemplate", $context) ? $context["withtemplate"] : (function () { throw new RuntimeError('Variable "withtemplate" does not exist.', 83, $this->source); })())], (isset($context["tpl_value"]) || array_key_exists("tpl_value", $context) ? $context["tpl_value"] : (function () { throw new RuntimeError('Variable "tpl_value" does not exist.', 83, $this->source); })())], "method", false, false, false, 83)]);
                // line 85
                yield "   ";
            }
            // line 86
            yield "   ";
            if ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, true, false, 86), (isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 86, $this->source); })()), [], "array", true, true, false, 86) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 86, $this->source); })()), "fields", [], "any", false, false, false, 86), (isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 86, $this->source); })()), [], "array", false, false, false, 86)))) {
                // line 87
                yield "      ";
                $context["value"] = $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("autoName", [CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 87, $this->source); })()), "fields", [], "any", false, false, false, 87), (isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 87, $this->source); })()), [], "array", false, false, false, 87), (isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 87, $this->source); })()), ((isset($context["withtemplate"]) || array_key_exists("withtemplate", $context) ? $context["withtemplate"] : (function () { throw new RuntimeError('Variable "withtemplate" does not exist.', 87, $this->source); })()) == 2), CoreExtension::getAttribute($this->env, $this->source, (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 87, $this->source); })()), "getType", [], "method", false, false, false, 87), (((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, true, false, 87), "entities_id", [], "array", true, true, false, 87) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 87, $this->source); })()), "fields", [], "any", false, false, false, 87), "entities_id", [], "array", false, false, false, 87)))) ? (CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["item"]) || array_key_exists("item", $context) ? $context["item"] : (function () { throw new RuntimeError('Variable "item" does not exist.', 87, $this->source); })()), "fields", [], "any", false, false, false, 87), "entities_id", [], "array", false, false, false, 87)) : (null))]);
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
            yield $this->getTemplateForMacro("macro_textField", $context, 92, $this->getSourceContext())->macro_textField(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 92, $this->source); })()), (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 92, $this->source); })()), (isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 92, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 92, $this->source); })())]);
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
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 99, $this->source); })()));
            // line 100
            yield "
   ";
            // line 101
            if (CoreExtension::inFilter((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 101, $this->source); })()), ["name"])) {
                // line 102
                yield "        ";
                $context["current_attrs"] = ((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "additional_attributes", [], "any", true, true, false, 102)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 102, $this->source); })()), "additional_attributes", [], "any", false, false, false, 102), [])) : ([]));
                // line 103
                yield "
         ";
                // line 104
                if ((($tmp =  !CoreExtension::getAttribute($this->env, $this->source, ($context["current_attrs"] ?? null), "autocomplete", [], "any", true, true, false, 104)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 105
                    yield "            ";
                    $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 105, $this->source); })()), ["additional_attributes" => Twig\Extension\CoreExtension::merge(                    // line 106
(isset($context["current_attrs"]) || array_key_exists("current_attrs", $context) ? $context["current_attrs"] : (function () { throw new RuntimeError('Variable "current_attrs" does not exist.', 106, $this->source); })()), ["autocomplete" => "off"])]);
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
                yield $macros["_inputs"]->getTemplateForMacro("macro_text", $context, 115, $this->getSourceContext())->macro_text(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 115, $this->source); })()), (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 115, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 115, $this->source); })())]);
                yield "
   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 117
            yield "
   ";
            // line 118
            yield $this->getTemplateForMacro("macro_field", $context, 118, $this->getSourceContext())->macro_field(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 118, $this->source); })()), (isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 118, $this->source); })()), (isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 118, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 118, $this->source); })())]);
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
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 124, $this->source); })()));
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
                yield $macros["_inputs"]->getTemplateForMacro("macro_input", $context, 128, $this->getSourceContext())->macro_input(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 128, $this->source); })()), (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 128, $this->source); })()), Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 128, $this->source); })()), ["type" => "url"])]);
                // line 130
                yield "
    ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 132
            yield "
    ";
            // line 133
            yield $this->getTemplateForMacro("macro_field", $context, 133, $this->getSourceContext())->macro_field(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 133, $this->source); })()), (isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 133, $this->source); })()), (isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 133, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 133, $this->source); })())]);
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
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 140, $this->source); })()));
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
                yield $macros["_inputs"]->getTemplateForMacro("macro_checkbox", $context, 144, $this->getSourceContext())->macro_checkbox(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 144, $this->source); })()), (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 144, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 144, $this->source); })())]);
                yield "
    ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 146
            yield "
    ";
            // line 147
            yield $this->getTemplateForMacro("macro_field", $context, 147, $this->getSourceContext())->macro_field(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 147, $this->source); })()), (isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 147, $this->source); })()), (isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 147, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 147, $this->source); })())]);
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
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 152), "isMandatoryField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 152, $this->source); })())], "method", true, true, false, 152)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 152, $this->source); })()), "fields_template", [], "any", false, false, false, 152), "isMandatoryField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 152, $this->source); })())], "method", false, false, false, 152), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 153
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(["required" => true],                 // line 155
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 155, $this->source); })()));
                // line 156
                yield "   ";
            }
            // line 157
            yield "   ";
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 157), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 157, $this->source); })())], "method", true, true, false, 157)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 157, $this->source); })()), "fields_template", [], "any", false, false, false, 157), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 157, $this->source); })())], "method", false, false, false, 157), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 158
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 158, $this->source); })()), ["readonly" => true]);
                // line 159
                yield "   ";
            }
            // line 160
            yield "   ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["no_value" => 0, "yes_value" => 1, "readonly" => false, "required" => false, "disabled" => false, "additional_attributes" => [], "label2" => ""],             // line 168
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 168, $this->source); })()));
            // line 169
            yield "
   ";
            // line 170
            $context["field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 171
                yield "      <label class=\"form-check form-switch mt-2\">
         <input type=\"hidden\"   name=\"";
                // line 172
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 172, $this->source); })()), "html", null, true);
                yield "\" value=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 172, $this->source); })()), "no_value", [], "any", false, false, false, 172), "html", null, true);
                yield "\" />
         <input type=\"checkbox\" name=\"";
                // line 173
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 173, $this->source); })()), "html", null, true);
                yield "\" value=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 173, $this->source); })()), "yes_value", [], "any", false, false, false, 173), "html", null, true);
                yield "\" class=\"form-check-input\" id=\"%id%\"
                ";
                // line 174
                yield ((((isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 174, $this->source); })()) == 1)) ? ("checked") : (""));
                yield "
                ";
                // line 175
                yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 175, $this->source); })()), "readonly", [], "any", false, false, false, 175)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("readonly") : (""));
                yield "
                ";
                // line 176
                yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 176, $this->source); })()), "required", [], "any", false, false, false, 176)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("required") : (""));
                yield "
                ";
                // line 177
                yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 177, $this->source); })()), "disabled", [], "any", false, false, false, 177)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("disabled") : (""));
                yield "
                ";
                // line 178
                $context['_parent'] = $context;
                $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 178, $this->source); })()), "additional_attributes", [], "any", false, false, false, 178));
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
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 181, $this->source); })()), "label2", [], "any", false, false, false, 181)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 182
                    yield "            <span class=\"form-check-label\">";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 182, $this->source); })()), "label2", [], "any", false, false, false, 182), "html", null, true);
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
            yield $this->getTemplateForMacro("macro_field", $context, 187, $this->getSourceContext())->macro_field(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 187, $this->source); })()), (isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 187, $this->source); })()), (isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 187, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 187, $this->source); })())]);
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
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 194, $this->source); })()));
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
                yield $macros["_inputs"]->getTemplateForMacro("macro_number", $context, 198, $this->getSourceContext())->macro_number(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 198, $this->source); })()), (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 198, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 198, $this->source); })())]);
                yield "
    ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 200
            yield "
    ";
            // line 201
            yield $this->getTemplateForMacro("macro_field", $context, 201, $this->getSourceContext())->macro_field(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 201, $this->source); })()), (isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 201, $this->source); })()), (isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 201, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 201, $this->source); })())]);
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
            $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 206, $this->source); })()), ["readonly" => true]);
            // line 207
            yield "   ";
            $context["value"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 208
                yield "      <span class=\"form-control ";
                yield (((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "input_addclass", [], "any", true, true, false, 208) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 208, $this->source); })()), "input_addclass", [], "any", false, false, false, 208)))) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 208, $this->source); })()), "input_addclass", [], "any", false, false, false, 208), "html", null, true)) : (""));
                yield "\" readonly>
         ";
                // line 209
                if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 209, $this->source); })())) == 0)) {
                    // line 210
                    yield "            &nbsp;
         ";
                } else {
                    // line 212
                    yield "            ";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 212, $this->source); })()), "html", null, true);
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
            yield $this->getTemplateForMacro("macro_field", $context, 216, $this->getSourceContext())->macro_field(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 216, $this->source); })()), (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 216, $this->source); })()), (isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 216, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 216, $this->source); })())]);
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
($context["options"] ?? null), "enable_mentions", [], "any", true, true, false, 227) && CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 227, $this->source); })()), "enable_mentions", [], "any", false, false, false, 227)), "full" => true, "users" => []], "entities_id" => $this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpiactive_entity"), "uploads" => [], "rows" => 3, "readonly" => false],             // line 235
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 235, $this->source); })()));
            // line 236
            yield "
   ";
            // line 237
            if ((($tmp =  !CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "id", [], "any", true, true, false, 237)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 238
                yield "       ";
                // line 239
                yield "       ";
                $context["options"] = Twig\Extension\CoreExtension::merge(["id" => ((Html::sanitizeDomId((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 239, $this->source); })())) . "_") . CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 239, $this->source); })()), "rand", [], "any", false, false, false, 239))], (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 239, $this->source); })()));
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
                yield $macros["_inputs"]->getTemplateForMacro("macro_textarea", $context, 244, $this->getSourceContext())->macro_textarea(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 244, $this->source); })()), (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 244, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 244, $this->source); })())]);
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
            if (( !CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 248, $this->source); })()), "readonly", [], "any", false, false, false, 248) && CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 248, $this->source); })()), "enable_fileupload", [], "any", false, false, false, 248))) {
                // line 249
                yield "      ";
                $context["add_html"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                    // line 250
                    yield "         ";
                    $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Html::file", [["editor_id" => CoreExtension::getAttribute($this->env, $this->source,                     // line 251
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 251, $this->source); })()), "id", [], "any", false, false, false, 251), "multiple" => true, "uploads" => CoreExtension::getAttribute($this->env, $this->source,                     // line 253
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 253, $this->source); })()), "uploads", [], "any", false, false, false, 253), "required" => ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source,                     // line 254
($context["options"] ?? null), "fields_template", [], "any", false, true, false, 254), "isMandatoryField", ["_documents_id"], "method", true, true, false, 254)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 254, $this->source); })()), "fields_template", [], "any", false, false, false, 254), "isMandatoryField", ["_documents_id"], "method", false, false, false, 254), false)) : (false))]]);
                    // line 256
                    yield "      ";
                    yield from [];
                })())) ? '' : new Markup($tmp, $this->env->getCharset());
                // line 257
                yield "   ";
            } elseif (((( !CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 257, $this->source); })()), "readonly", [], "any", false, false, false, 257) &&  !CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 257, $this->source); })()), "enable_fileupload", [], "any", false, false, false, 257)) && CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 257, $this->source); })()), "enable_richtext", [], "any", false, false, false, 257)) && CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 257, $this->source); })()), "enable_images", [], "any", false, false, false, 257))) {
                // line 258
                yield "      ";
                $context["add_html"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                    // line 259
                    yield "         ";
                    $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Html::file", [["editor_id" => CoreExtension::getAttribute($this->env, $this->source,                     // line 260
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 260, $this->source); })()), "id", [], "any", false, false, false, 260), "name" =>                     // line 261
(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 261, $this->source); })()), "only_uploaded_files" => true, "uploads" => CoreExtension::getAttribute($this->env, $this->source,                     // line 263
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 263, $this->source); })()), "uploads", [], "any", false, false, false, 263), "required" => ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source,                     // line 264
($context["options"] ?? null), "fields_template", [], "any", false, true, false, 264), "isMandatoryField", ["_documents_id"], "method", true, true, false, 264)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 264, $this->source); })()), "fields_template", [], "any", false, false, false, 264), "isMandatoryField", ["_documents_id"], "method", false, false, false, 264), false)) : (false)), "init" => (((CoreExtension::getAttribute($this->env, $this->source,                     // line 265
($context["options"] ?? null), "init_fileupload", [], "any", true, true, false, 265) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 265, $this->source); })()), "init_fileupload", [], "any", false, false, false, 265)))) ? (CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 265, $this->source); })()), "init_fileupload", [], "any", false, false, false, 265)) : ((((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "init", [], "any", true, true, false, 265) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 265, $this->source); })()), "init", [], "any", false, false, false, 265)))) ? (CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 265, $this->source); })()), "init", [], "any", false, false, false, 265)) : (true))))]]);
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
            if (((isset($context["add_html"]) || array_key_exists("add_html", $context) ? $context["add_html"] : (function () { throw new RuntimeError('Variable "add_html" does not exist.', 270, $this->source); })()) != "")) {
                // line 271
                yield "      ";
                if (CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "add_field_html", [], "any", true, true, false, 271)) {
                    // line 272
                    yield "         ";
                    $context["add_html"] = ((isset($context["add_html"]) || array_key_exists("add_html", $context) ? $context["add_html"] : (function () { throw new RuntimeError('Variable "add_html" does not exist.', 272, $this->source); })()) . CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 272, $this->source); })()), "add_field_html", [], "any", false, false, false, 272));
                    // line 273
                    yield "      ";
                }
                // line 274
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 274, $this->source); })()), ["add_field_html" => (isset($context["add_html"]) || array_key_exists("add_html", $context) ? $context["add_html"] : (function () { throw new RuntimeError('Variable "add_html" does not exist.', 274, $this->source); })())]);
                // line 275
                yield "   ";
            }
            // line 276
            yield "
   ";
            // line 277
            yield $this->getTemplateForMacro("macro_field", $context, 277, $this->getSourceContext())->macro_field(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 277, $this->source); })()), (isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 277, $this->source); })()), (isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 277, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 277, $this->source); })())]);
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
                yield $macros["_inputs"]->getTemplateForMacro("macro_date", $context, 284, $this->getSourceContext())->macro_date(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 284, $this->source); })()), (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 284, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 284, $this->source); })())]);
                yield "
   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 286
            yield "
   ";
            // line 287
            yield $this->getTemplateForMacro("macro_field", $context, 287, $this->getSourceContext())->macro_field(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 287, $this->source); })()), (isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 287, $this->source); })()), (isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 287, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 287, $this->source); })())]);
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
                yield $macros["_inputs"]->getTemplateForMacro("macro_datetime", $context, 294, $this->getSourceContext())->macro_datetime(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 294, $this->source); })()), (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 294, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 294, $this->source); })())]);
                yield "
   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 296
            yield "
   ";
            // line 297
            yield $this->getTemplateForMacro("macro_field", $context, 297, $this->getSourceContext())->macro_field(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 297, $this->source); })()), (isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 297, $this->source); })()), (isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 297, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 297, $this->source); })())]);
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
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 304, $this->source); })()));
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
                yield $macros["_inputs"]->getTemplateForMacro("macro_color", $context, 308, $this->getSourceContext())->macro_color(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 308, $this->source); })()), (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 308, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 308, $this->source); })())]);
                yield "
    ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 310
            yield "
    ";
            // line 311
            yield $this->getTemplateForMacro("macro_field", $context, 311, $this->getSourceContext())->macro_field(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 311, $this->source); })()), (isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 311, $this->source); })()), (isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 311, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 311, $this->source); })())]);
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
($context["options"] ?? null), "can_regenerate", [], "any", true, true, false, 318) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 318, $this->source); })()), "can_regenerate", [], "any", false, false, false, 318)))) ? (CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 318, $this->source); })()), "can_regenerate", [], "any", false, false, false, 318)) : (false)), "clearable" => ((CoreExtension::getAttribute($this->env, $this->source,             // line 319
($context["options"] ?? null), "clearable", [], "any", true, true, false, 319)) ? (CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 319, $this->source); })()), "clearable", [], "any", false, false, false, 319)) : ( !(((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "is_disclosable", [], "any", true, true, false, 319) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 319, $this->source); })()), "is_disclosable", [], "any", false, false, false, 319)))) ? (CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 319, $this->source); })()), "is_disclosable", [], "any", false, false, false, 319)) : (false)))), "is_copyable" => (((CoreExtension::getAttribute($this->env, $this->source,             // line 320
($context["options"] ?? null), "is_disclosable", [], "any", true, true, false, 320) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 320, $this->source); })()), "is_disclosable", [], "any", false, false, false, 320)))) ? (CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 320, $this->source); })()), "is_disclosable", [], "any", false, false, false, 320)) : (false))],             // line 321
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 321, $this->source); })()));
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
                yield $macros["_inputs"]->getTemplateForMacro("macro_password", $context, 325, $this->getSourceContext())->macro_password(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 325, $this->source); })()), (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 325, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 325, $this->source); })())]);
                yield "
    ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 327
            yield "
   ";
            // line 329
            yield "   ";
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 329, $this->source); })()), "can_regenerate", [], "any", false, false, false, 329)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 330
                yield "      ";
                $context["regenerate_chk"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                    // line 331
                    yield "         <div class=\"d-flex align-items-center gap-1 mt-1\">
             <input class=\"form-check-input\" type=\"checkbox\" name=\"_regenerate_";
                    // line 332
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 332, $this->source); })()), "html", null, true);
                    yield "\" id=\"_regenerate_";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 332, $this->source); })()), "html", null, true);
                    yield "\"><label class=\"form-check-label\" for=\"_regenerate_";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 332, $this->source); })()), "html", null, true);
                    yield "\">";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Regenerate"), "html", null, true);
                    yield "</label>
         </div>
      ";
                    yield from [];
                })())) ? '' : new Markup($tmp, $this->env->getCharset());
                // line 335
                yield "      ";
                $context["field"] = ((isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 335, $this->source); })()) . (isset($context["regenerate_chk"]) || array_key_exists("regenerate_chk", $context) ? $context["regenerate_chk"] : (function () { throw new RuntimeError('Variable "regenerate_chk" does not exist.', 335, $this->source); })()));
                // line 336
                yield "   ";
            }
            // line 337
            yield "
   ";
            // line 338
            yield $this->getTemplateForMacro("macro_field", $context, 338, $this->getSourceContext())->macro_field(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 338, $this->source); })()), (isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 338, $this->source); })()), (isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 338, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 338, $this->source); })())]);
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
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 345, $this->source); })()));
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
                yield $macros["_inputs"]->getTemplateForMacro("macro_email", $context, 349, $this->getSourceContext())->macro_email(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 349, $this->source); })()), (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 349, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 349, $this->source); })())]);
                yield "
    ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 351
            yield "
   ";
            // line 352
            yield $this->getTemplateForMacro("macro_field", $context, 352, $this->getSourceContext())->macro_field(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 352, $this->source); })()), (isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 352, $this->source); })()), (isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 352, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 352, $this->source); })())]);
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
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 360, $this->source); })()));
            // line 361
            yield "   ";
            $context["field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 362
                yield "        ";
                $macros["_inputs"] = $this->load("components/form/basic_inputs_macros.html.twig", 362)->unwrap();
                // line 363
                yield "        ";
                yield $macros["_inputs"]->getTemplateForMacro("macro_file", $context, 363, $this->getSourceContext())->macro_file(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 363, $this->source); })()), (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 363, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 363, $this->source); })())]);
                yield "
   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 365
            yield "   ";
            yield $this->getTemplateForMacro("macro_field", $context, 365, $this->getSourceContext())->macro_field(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 365, $this->source); })()), (isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 365, $this->source); })()), (isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 365, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 365, $this->source); })())]);
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
                $context["clearable"] = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 371, $this->source); })()), "clearable", [], "array", false, false, false, 371);
                // line 372
                yield "         ";
                $context["url"] = (((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "url", [], "array", true, true, false, 372) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 372, $this->source); })()), "url", [], "array", false, false, false, 372)))) ? (CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 372, $this->source); })()), "url", [], "array", false, false, false, 372)) : (null));
                // line 373
                yield "         ";
                $context["options"] = Twig\Extension\CoreExtension::filter($this->env, $this->env->hasExtension(\Twig\Extension\SandboxExtension::class) && $this->env->getExtension(\Twig\Extension\SandboxExtension::class)->isSandboxed($this->source), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 373, $this->source); })()), function ($__v__, $__k__) use ($context, $macros) { $context["v"] = $__v__; $context["k"] = $__k__; return (((isset($context["k"]) || array_key_exists("k", $context) ? $context["k"] : (function () { throw new RuntimeError('Variable "k" does not exist.', 373, $this->source); })()) != "url") && ((isset($context["k"]) || array_key_exists("k", $context) ? $context["k"] : (function () { throw new RuntimeError('Variable "k" does not exist.', 373, $this->source); })()) != "clearable")); });
                // line 374
                yield "         ";
                if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty((isset($context["url"]) || array_key_exists("url", $context) ? $context["url"] : (function () { throw new RuntimeError('Variable "url" does not exist.', 374, $this->source); })()))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 375
                    yield "            <a href=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["url"]) || array_key_exists("url", $context) ? $context["url"] : (function () { throw new RuntimeError('Variable "url" does not exist.', 375, $this->source); })()), "html", null, true);
                    yield "\" ";
                    yield $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Html::parseAttributes", [(isset($context["link_options"]) || array_key_exists("link_options", $context) ? $context["link_options"] : (function () { throw new RuntimeError('Variable "link_options" does not exist.', 375, $this->source); })())]);
                    yield ">
         ";
                }
                // line 377
                yield "               <img src=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 377, $this->source); })()), "html", null, true);
                yield "\" ";
                yield $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Html::parseAttributes", [(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 377, $this->source); })())]);
                yield " />
         ";
                // line 378
                if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty((isset($context["url"]) || array_key_exists("url", $context) ? $context["url"] : (function () { throw new RuntimeError('Variable "url" does not exist.', 378, $this->source); })()))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 379
                    yield "            </a>
         ";
                }
                // line 381
                yield "         ";
                if ((($tmp = (isset($context["clearable"]) || array_key_exists("clearable", $context) ? $context["clearable"] : (function () { throw new RuntimeError('Variable "clearable" does not exist.', 381, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 382
                    yield "            <input type=\"hidden\" name=\"_blank_";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 382, $this->source); })()), "html", null, true);
                    yield "\" />";
                    // line 383
                    $context["clear_js"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                        // line 384
                        yield "const blank_input = \$(\x27input[name=\\\x27_blank_";
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 384, $this->source); })()), "css"), "js"), "html", null, true);
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
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["clear_js"]) || array_key_exists("clear_js", $context) ? $context["clear_js"] : (function () { throw new RuntimeError('Variable "clear_js" does not exist.', 394, $this->source); })()), "html", null, true);
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
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 400), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 400, $this->source); })())], "method", true, true, false, 400)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 400, $this->source); })()), "fields_template", [], "any", false, false, false, 400), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 400, $this->source); })())], "method", false, false, false, 400), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 401
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 401, $this->source); })()), ["readonly" => true]);
                // line 402
                yield "   ";
            }
            // line 403
            yield "   ";
            yield $this->getTemplateForMacro("macro_field", $context, 403, $this->getSourceContext())->macro_field(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 403, $this->source); })()), (isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 403, $this->source); })()), (isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 403, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 403, $this->source); })())]);
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
                $context['_seq'] = CoreExtension::ensureTraversable((isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 412, $this->source); })()));
                foreach ($context['_seq'] as $context["i"] => $context["picture"]) {
                    // line 413
                    yield "            <div class=\"picture_gallery_item\" style=\"position: relative; width: fit-content\">
               ";
                    // line 414
                    yield $this->getTemplateForMacro("macro_imageField", $context, 414, $this->getSourceContext())->macro_imageField(...[(((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 414, $this->source); })()) . "_") . $context["i"]), $context["picture"], "", ["style" => "max-width: 300px; max-height: 150px", "class" => "picture_square", "clearable" => CoreExtension::getAttribute($this->env, $this->source,                     // line 417
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 417, $this->source); })()), "clearable", [], "array", false, false, false, 417), "no_label" => true]]);
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
                yield $this->getTemplateForMacro("macro_fileField", $context, 423, $this->getSourceContext())->macro_fileField(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 423, $this->source); })()), null, "", ["onlyimages" => true, "multiple" => true]]);
                // line 426
                yield "
   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 428
            yield "
   ";
            // line 429
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 429), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 429, $this->source); })())], "method", true, true, false, 429)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 429, $this->source); })()), "fields_template", [], "any", false, false, false, 429), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 429, $this->source); })())], "method", false, false, false, 429), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 430
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 430, $this->source); })()), ["readonly" => true]);
                // line 431
                yield "   ";
            }
            // line 432
            yield "
   ";
            // line 433
            $context["id"] = (((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "id", [], "any", true, true, false, 433) && (Twig\Extension\CoreExtension::length($this->env->getCharset(), CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 433, $this->source); })()), "id", [], "any", false, false, false, 433)) > 0))) ? (CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 433, $this->source); })()), "id", [], "any", false, false, false, 433)) : (((Html::sanitizeDomId((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 433, $this->source); })())) . "_") . (((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "rand", [], "any", true, true, false, 433) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 433, $this->source); })()), "rand", [], "any", false, false, false, 433)))) ? (CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 433, $this->source); })()), "rand", [], "any", false, false, false, 433)) : ("")))));
            // line 434
            yield "   ";
            $macros["_inputs"] = $this->load("components/form/basic_inputs_macros.html.twig", 434)->unwrap();
            // line 435
            yield "   ";
            yield $macros["_inputs"]->getTemplateForMacro("macro_label", $context, 435, $this->getSourceContext())->macro_label(...[(isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 435, $this->source); })()), (isset($context["id"]) || array_key_exists("id", $context) ? $context["id"] : (function () { throw new RuntimeError('Variable "id" does not exist.', 435, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 435, $this->source); })())]);
            yield "
   ";
            // line 436
            yield $this->getTemplateForMacro("macro_field", $context, 436, $this->getSourceContext())->macro_field(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 436, $this->source); })()), (isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 436, $this->source); })()), (isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 436, $this->source); })()), Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 436, $this->source); })()), ["full_width" => true, "no_label" => true])]);
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
            if ((($tmp =  !is_iterable((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 443, $this->source); })()))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 444
                yield "        ";
                // line 450
                yield "        ";
                $context["options"] = ((CoreExtension::getAttribute($this->env, $this->source, ($context["varargs"] ?? null), 0, [], "array", true, true, false, 450)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, (isset($context["varargs"]) || array_key_exists("varargs", $context) ? $context["varargs"] : (function () { throw new RuntimeError('Variable "varargs" does not exist.', 450, $this->source); })()), 0, [], "array", false, false, false, 450), [])) : ([]));
                // line 451
                yield "    ";
            }
            // line 452
            yield "    ";
            $macros["_inputs"] = $this->load("components/form/basic_inputs_macros.html.twig", 452)->unwrap();
            // line 453
            yield "    ";
            yield $macros["_inputs"]->getTemplateForMacro("macro_hidden", $context, 453, $this->getSourceContext())->macro_hidden(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 453, $this->source); })()), (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 453, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 453, $this->source); })())]);
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
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 465, $this->source); })()));
            // line 466
            yield "
   ";
            // line 467
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 467), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 467, $this->source); })())], "method", true, true, false, 467)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 467, $this->source); })()), "fields_template", [], "any", false, false, false, 467), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 467, $this->source); })())], "method", false, false, false, 467), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 468
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 468, $this->source); })()), ["readonly" => true]);
                // line 469
                yield "   ";
            }
            // line 470
            yield "
   ";
            // line 471
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 471, $this->source); })()), "disabled", [], "any", false, false, false, 471)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 472
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 472, $this->source); })()), ["specific_tags" => ["disabled" => "disabled"]]);
                // line 473
                yield "   ";
            }
            // line 474
            yield "   ";
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 474), "isMandatoryField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 474, $this->source); })())], "method", true, true, false, 474)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 474, $this->source); })()), "fields_template", [], "any", false, false, false, 474), "isMandatoryField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 474, $this->source); })())], "method", false, false, false, 474), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 475
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(["specific_tags" => ["required" => true]], (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 475, $this->source); })()));
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
                $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Dropdown::showNumber", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 479, $this->source); })()), Twig\Extension\CoreExtension::merge(["value" =>                 // line 480
(isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 480, $this->source); })()), "rand" => CoreExtension::getAttribute($this->env, $this->source,                 // line 481
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 481, $this->source); })()), "rand", [], "any", false, false, false, 481)],                 // line 482
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 482, $this->source); })()))]);
                // line 483
                yield "   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 484
            yield "
   ";
            // line 485
            yield $this->getTemplateForMacro("macro_field", $context, 485, $this->getSourceContext())->macro_field(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 485, $this->source); })()), (isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 485, $this->source); })()), (isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 485, $this->source); })()), Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 485, $this->source); })()), ["id" => (("dropdown_" . Twig\Extension\CoreExtension::replace((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 485, $this->source); })()), ["[" => "_", "]" => "_"])) . CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 485, $this->source); })()), "rand", [], "any", false, false, false, 485))])]);
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
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 493, $this->source); })()));
            // line 494
            yield "
   ";
            // line 495
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 495), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 495, $this->source); })())], "method", true, true, false, 495)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 495, $this->source); })()), "fields_template", [], "any", false, false, false, 495), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 495, $this->source); })())], "method", false, false, false, 495), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 496
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 496, $this->source); })()), ["readonly" => true]);
                // line 497
                yield "   ";
            }
            // line 498
            yield "
   ";
            // line 499
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 499, $this->source); })()), "disabled", [], "any", false, false, false, 499)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 500
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 500, $this->source); })()), ["specific_tags" => ["disabled" => "disabled"]]);
                // line 501
                yield "   ";
            }
            // line 502
            yield "
   ";
            // line 503
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 503), "isMandatoryField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 503, $this->source); })())], "method", true, true, false, 503)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 503, $this->source); })()), "fields_template", [], "any", false, false, false, 503), "isMandatoryField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 503, $this->source); })())], "method", false, false, false, 503), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 504
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(["required" => true], (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 504, $this->source); })()));
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
                $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Dropdown::showFromArray", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 508, $this->source); })()), (isset($context["elements"]) || array_key_exists("elements", $context) ? $context["elements"] : (function () { throw new RuntimeError('Variable "elements" does not exist.', 508, $this->source); })()), Twig\Extension\CoreExtension::merge(["value" =>                 // line 509
(isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 509, $this->source); })()), "rand" => CoreExtension::getAttribute($this->env, $this->source,                 // line 510
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 510, $this->source); })()), "rand", [], "any", false, false, false, 510)],                 // line 511
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 511, $this->source); })()))]);
                // line 512
                yield "   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 513
            yield "
   ";
            // line 514
            yield $this->getTemplateForMacro("macro_field", $context, 514, $this->getSourceContext())->macro_field(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 514, $this->source); })()), (isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 514, $this->source); })()), (isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 514, $this->source); })()), Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 514, $this->source); })()), ["id" => (("dropdown_" . Twig\Extension\CoreExtension::replace((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 514, $this->source); })()), ["[" => "_", "]" => "_"])) . CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 514, $this->source); })()), "rand", [], "any", false, false, false, 514))])]);
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
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 522, $this->source); })()));
            // line 523
            yield "   ";
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 523), "isMandatoryField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 523, $this->source); })())], "method", true, true, false, 523)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 523, $this->source); })()), "fields_template", [], "any", false, false, false, 523), "isMandatoryField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 523, $this->source); })())], "method", false, false, false, 523), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 524
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(["required" => true], (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 524, $this->source); })()));
                // line 525
                yield "   ";
            }
            // line 526
            yield "
   ";
            // line 527
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 527), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 527, $this->source); })())], "method", true, true, false, 527)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 527, $this->source); })()), "fields_template", [], "any", false, false, false, 527), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 527, $this->source); })())], "method", false, false, false, 527), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 528
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 528, $this->source); })()), ["readonly" => true]);
                // line 529
                yield "   ";
            }
            // line 530
            yield "
   ";
            // line 531
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 531, $this->source); })()), "disabled", [], "any", false, false, false, 531)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 532
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 532, $this->source); })()), ["specific_tags" => ["disabled" => "disabled"]]);
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
                $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Dropdown::showTimestamp", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 536, $this->source); })()), Twig\Extension\CoreExtension::merge(["value" =>                 // line 537
(isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 537, $this->source); })())],                 // line 538
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 538, $this->source); })()))]);
                // line 539
                yield "   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 540
            yield "
   ";
            // line 541
            yield $this->getTemplateForMacro("macro_field", $context, 541, $this->getSourceContext())->macro_field(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 541, $this->source); })()), (isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 541, $this->source); })()), (isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 541, $this->source); })()), Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 541, $this->source); })()), ["id" => (("dropdown_" . Twig\Extension\CoreExtension::replace((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 541, $this->source); })()), ["[" => "_", "]" => "_"])) . CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 541, $this->source); })()), "rand", [], "any", false, false, false, 541))])]);
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
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 549, $this->source); })()));
            // line 550
            yield "   ";
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 550), "isMandatoryField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 550, $this->source); })())], "method", true, true, false, 550)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 550, $this->source); })()), "fields_template", [], "any", false, false, false, 550), "isMandatoryField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 550, $this->source); })())], "method", false, false, false, 550), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 551
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(["required" => true], (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 551, $this->source); })()));
                // line 552
                yield "   ";
            }
            // line 553
            yield "
   ";
            // line 554
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 554), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 554, $this->source); })())], "method", true, true, false, 554)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 554, $this->source); })()), "fields_template", [], "any", false, false, false, 554), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 554, $this->source); })())], "method", false, false, false, 554), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 555
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 555, $this->source); })()), ["readonly" => true]);
                // line 556
                yield "   ";
            }
            // line 557
            yield "
   ";
            // line 558
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 558, $this->source); })()), "disabled", [], "any", false, false, false, 558)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 559
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 559, $this->source); })()), ["specific_tags" => ["disabled" => "disabled"]]);
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
                $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Dropdown::showYesNo", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 563, $this->source); })()), (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 563, $this->source); })()),  -1, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 563, $this->source); })())]);
                // line 564
                yield "   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 565
            yield "
   ";
            // line 566
            yield $this->getTemplateForMacro("macro_field", $context, 566, $this->getSourceContext())->macro_field(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 566, $this->source); })()), (isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 566, $this->source); })()), (isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 566, $this->source); })()), Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 566, $this->source); })()), ["id" => (("dropdown_" . Twig\Extension\CoreExtension::replace((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 566, $this->source); })()), ["[" => "_", "]" => "_"])) . CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 566, $this->source); })()), "rand", [], "any", false, false, false, 566))])]);
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
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 574, $this->source); })()));
            // line 575
            yield "   ";
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 575), "isMandatoryField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 575, $this->source); })())], "method", true, true, false, 575)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 575, $this->source); })()), "fields_template", [], "any", false, false, false, 575), "isMandatoryField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 575, $this->source); })())], "method", false, false, false, 575), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 576
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(["required" => true], (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 576, $this->source); })()));
                // line 577
                yield "   ";
            }
            // line 578
            yield "
   ";
            // line 579
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 579), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 579, $this->source); })())], "method", true, true, false, 579)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 579, $this->source); })()), "fields_template", [], "any", false, false, false, 579), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 579, $this->source); })())], "method", false, false, false, 579), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 580
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 580, $this->source); })()), ["readonly" => true]);
                // line 581
                yield "   ";
            }
            // line 582
            yield "
   ";
            // line 583
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 583, $this->source); })()), "disabled", [], "any", false, false, false, 583)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 584
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 584, $this->source); })()), ["specific_tags" => ["disabled" => "disabled"]]);
                // line 585
                yield "   ";
            }
            // line 586
            yield "
   ";
            // line 587
            $context["types"] = ((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "types", [], "array", true, true, false, 587)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 587, $this->source); })()), "types", [], "array", false, false, false, 587), [])) : ([]));
            // line 588
            yield "
   ";
            // line 589
            $context["field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 590
                yield "      ";
                $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Dropdown::showItemTypes", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 590, $this->source); })()), (isset($context["types"]) || array_key_exists("types", $context) ? $context["types"] : (function () { throw new RuntimeError('Variable "types" does not exist.', 590, $this->source); })()), Twig\Extension\CoreExtension::merge(["rand" => CoreExtension::getAttribute($this->env, $this->source,                 // line 591
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 591, $this->source); })()), "rand", [], "any", false, false, false, 591), "value" =>                 // line 592
(isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 592, $this->source); })())],                 // line 593
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 593, $this->source); })()))]);
                // line 594
                yield "   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 595
            yield "
   ";
            // line 596
            yield $this->getTemplateForMacro("macro_field", $context, 596, $this->getSourceContext())->macro_field(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 596, $this->source); })()), (isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 596, $this->source); })()), (isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 596, $this->source); })()), Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 596, $this->source); })()), ["id" => (("dropdown_" . Twig\Extension\CoreExtension::replace((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 596, $this->source); })()), ["[" => "_", "]" => "_"])) . CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 596, $this->source); })()), "rand", [], "any", false, false, false, 596))])]);
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
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 602, $this->source); })()));
            // line 603
            yield "
   ";
            // line 604
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 604), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 604, $this->source); })())], "method", true, true, false, 604)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 604, $this->source); })()), "fields_template", [], "any", false, false, false, 604), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 604, $this->source); })())], "method", false, false, false, 604), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 605
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 605, $this->source); })()), ["readonly" => true]);
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
                $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Dropdown::showSelectItemFromItemtypes", [(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 609, $this->source); })())]);
                // line 610
                yield "   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 611
            yield "   ";
            yield $this->getTemplateForMacro("macro_field", $context, 611, $this->getSourceContext())->macro_field(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 611, $this->source); })()), (isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 611, $this->source); })()), (isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 611, $this->source); })()), Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 611, $this->source); })()), ["id" => (("dropdown_" . Twig\Extension\CoreExtension::replace((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 611, $this->source); })()), ["[" => "_", "]" => "_"])) . CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 611, $this->source); })()), "rand", [], "any", false, false, false, 611))])]);
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
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 619, $this->source); })()));
            // line 620
            yield "   ";
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 620), "isMandatoryField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 620, $this->source); })())], "method", true, true, false, 620)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 620, $this->source); })()), "fields_template", [], "any", false, false, false, 620), "isMandatoryField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 620, $this->source); })())], "method", false, false, false, 620), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 621
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(["required" => true], (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 621, $this->source); })()));
                // line 622
                yield "   ";
            }
            // line 623
            yield "
   ";
            // line 624
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 624), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 624, $this->source); })())], "method", true, true, false, 624)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 624, $this->source); })()), "fields_template", [], "any", false, false, false, 624), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 624, $this->source); })())], "method", false, false, false, 624), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 625
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 625, $this->source); })()), ["readonly" => true]);
                // line 626
                yield "   ";
            }
            // line 627
            yield "
   ";
            // line 628
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 628, $this->source); })()), "disabled", [], "any", false, false, false, 628)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 629
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 629, $this->source); })()), ["specific_tags" => ["disabled" => "disabled"]]);
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
                $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Dropdown::dropdownIcons", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 633, $this->source); })()), (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 633, $this->source); })()), "", (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 633, $this->source); })())]);
                // line 634
                yield "   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 635
            yield "
   ";
            // line 636
            yield $this->getTemplateForMacro("macro_field", $context, 636, $this->getSourceContext())->macro_field(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 636, $this->source); })()), (isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 636, $this->source); })()), (isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 636, $this->source); })()), Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 636, $this->source); })()), ["id" => (("dropdown_" . Twig\Extension\CoreExtension::replace((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 636, $this->source); })()), ["[" => "_", "]" => "_"])) . CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 636, $this->source); })()), "rand", [], "any", false, false, false, 636))])]);
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
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 642, $this->source); })()), ["noselect2" => true]));
            // line 645
            yield "    ";
            // line 646
            yield "    ";
            $context["value"] = Twig\Extension\CoreExtension::replace((isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 646, $this->source); })()), ["ti " => ""]);
            // line 647
            yield "
    ";
            // line 648
            yield $this->getTemplateForMacro("macro_dropdownArrayField", $context, 648, $this->getSourceContext())->macro_dropdownArrayField(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 648, $this->source); })()), (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 648, $this->source); })()), [ (string)(isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 648, $this->source); })()) => (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 648, $this->source); })())], (isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 648, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 648, $this->source); })())]);
            yield "
    <script type=\"module\">
        import(\x27/js/modules/Form/WebIconSelector.js\x27).then((m) => {
            const dropdown_id = \x27";
            // line 651
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::replace((("dropdown_" . (isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 651, $this->source); })())) . CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 651, $this->source); })()), "rand", [], "any", false, false, false, 651)), ["[" => "_", "]" => "_"]), "js"), "html", null, true);
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
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 663, $this->source); })()));
            // line 664
            yield "   ";
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 664), "isMandatoryField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 664, $this->source); })())], "method", true, true, false, 664)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 664, $this->source); })()), "fields_template", [], "any", false, false, false, 664), "isMandatoryField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 664, $this->source); })())], "method", false, false, false, 664), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 665
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(["required" => true], (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 665, $this->source); })()));
                // line 666
                yield "   ";
            }
            // line 667
            yield "
   ";
            // line 668
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 668), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 668, $this->source); })())], "method", true, true, false, 668)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 668, $this->source); })()), "fields_template", [], "any", false, false, false, 668), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 668, $this->source); })())], "method", false, false, false, 668), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 669
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 669, $this->source); })()), ["readonly" => true]);
                // line 670
                yield "   ";
            }
            // line 671
            yield "
   ";
            // line 672
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 672, $this->source); })()), "disabled", [], "any", false, false, false, 672)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 673
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 673, $this->source); })()), ["specific_tags" => ["disabled" => "disabled"]]);
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
                $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Dropdown::showHours", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 677, $this->source); })()), Twig\Extension\CoreExtension::merge(["value" =>                 // line 678
(isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 678, $this->source); })())],                 // line 679
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 679, $this->source); })()))]);
                // line 680
                yield "   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 681
            yield "
   ";
            // line 682
            yield $this->getTemplateForMacro("macro_field", $context, 682, $this->getSourceContext())->macro_field(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 682, $this->source); })()), (isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 682, $this->source); })()), (isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 682, $this->source); })()), Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 682, $this->source); })()), ["id" => (("dropdown_" . Twig\Extension\CoreExtension::replace((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 682, $this->source); })()), ["[" => "_", "]" => "_"])) . CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 682, $this->source); })()), "rand", [], "any", false, false, false, 682))])]);
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
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 689, $this->source); })()));
            // line 690
            yield "   ";
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 690), "isMandatoryField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 690, $this->source); })())], "method", true, true, false, 690)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 690, $this->source); })()), "fields_template", [], "any", false, false, false, 690), "isMandatoryField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 690, $this->source); })())], "method", false, false, false, 690), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 691
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(["required" => true], (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 691, $this->source); })()));
                // line 692
                yield "   ";
            }
            // line 693
            yield "
   ";
            // line 694
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 694), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 694, $this->source); })())], "method", true, true, false, 694)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 694, $this->source); })()), "fields_template", [], "any", false, false, false, 694), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 694, $this->source); })())], "method", false, false, false, 694), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 695
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 695, $this->source); })()), ["readonly" => true]);
                // line 696
                yield "   ";
            }
            // line 697
            yield "
   ";
            // line 698
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 698, $this->source); })()), "disabled", [], "any", false, false, false, 698)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 699
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 699, $this->source); })()), ["specific_tags" => ["disabled" => "disabled"]]);
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
                $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Dropdown::showFrequency", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 703, $this->source); })()), (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 703, $this->source); })()), Twig\Extension\CoreExtension::merge(["width" => "100%", "value" =>                 // line 705
(isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 705, $this->source); })())],                 // line 706
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 706, $this->source); })()))]);
                // line 707
                yield "   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 708
            yield "
   ";
            // line 709
            yield $this->getTemplateForMacro("macro_field", $context, 709, $this->getSourceContext())->macro_field(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 709, $this->source); })()), (isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 709, $this->source); })()), (isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 709, $this->source); })()), Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 709, $this->source); })()), ["id" => (("dropdown_" . Twig\Extension\CoreExtension::replace((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 709, $this->source); })()), ["[" => "_", "]" => "_"])) . CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 709, $this->source); })()), "rand", [], "any", false, false, false, 709))])]);
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
            if ((($tmp = (((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "multiple", [], "any", true, true, false, 713) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 713, $this->source); })()), "multiple", [], "any", false, false, false, 713)))) ? (CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 713, $this->source); })()), "multiple", [], "any", false, false, false, 713)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 714
                yield "      ";
                // line 715
                yield "      ";
                $context["defined_input_name"] = (("_" . (isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 715, $this->source); })())) . "_defined");
                // line 716
                yield "      <input type=\"hidden\" name=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["defined_input_name"]) || array_key_exists("defined_input_name", $context) ? $context["defined_input_name"] : (function () { throw new RuntimeError('Variable "defined_input_name" does not exist.', 716, $this->source); })()), "html", null, true);
                yield "\" value=\"1\"></input>

      ";
                // line 719
                yield "      ";
                $context["name"] = ((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 719, $this->source); })()) . "[]");
                // line 720
                yield "   ";
            }
            // line 721
            yield "    ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["rand" => Twig\Extension\CoreExtension::random($this->env->getCharset()), "width" => "100%", "disabled" => false],             // line 725
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 725, $this->source); })()));
            // line 726
            yield "   ";
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 726), "isMandatoryField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 726, $this->source); })())], "method", true, true, false, 726)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 726, $this->source); })()), "fields_template", [], "any", false, false, false, 726), "isMandatoryField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 726, $this->source); })())], "method", false, false, false, 726), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 727
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(["specific_tags" => ["required" => true]], (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 727, $this->source); })()));
                // line 728
                yield "   ";
            }
            // line 729
            yield "
   ";
            // line 730
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 730), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 730, $this->source); })())], "method", true, true, false, 730)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 730, $this->source); })()), "fields_template", [], "any", false, false, false, 730), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 730, $this->source); })())], "method", false, false, false, 730), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 731
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 731, $this->source); })()), ["readonly" => true]);
                // line 732
                yield "   ";
            }
            // line 733
            yield "
   ";
            // line 734
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 734, $this->source); })()), "disabled", [], "any", false, false, false, 734)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 735
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 735, $this->source); })()), ["specific_tags" => ["disabled" => "disabled"]]);
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
                yield $this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeDropdown((isset($context["itemtype"]) || array_key_exists("itemtype", $context) ? $context["itemtype"] : (function () { throw new RuntimeError('Variable "itemtype" does not exist.', 739, $this->source); })()), Twig\Extension\CoreExtension::merge(["name" =>                 // line 740
(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 740, $this->source); })()), "value" =>                 // line 741
(isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 741, $this->source); })())],                 // line 742
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 742, $this->source); })())));
                yield "
   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 744
            yield "
   ";
            // line 745
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(Twig\Extension\CoreExtension::trim((isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 745, $this->source); })())))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 746
                yield "      ";
                yield $this->getTemplateForMacro("macro_field", $context, 746, $this->getSourceContext())->macro_field(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 746, $this->source); })()), (isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 746, $this->source); })()), (isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 746, $this->source); })()), Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 746, $this->source); })()), ["id" => (("dropdown_" . Twig\Extension\CoreExtension::replace((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 746, $this->source); })()), ["[" => "_", "]" => "_"])) . CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 746, $this->source); })()), "rand", [], "any", false, false, false, 746))])]);
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
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 751, $this->source); })()), "multiple", [], "any", false, false, false, 751)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 752
                yield "        ";
                // line 753
                yield "        ";
                $context["defined_input_name"] = (("_" . (isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 753, $this->source); })())) . "_defined");
                // line 754
                yield "        <input type=\"hidden\" name=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["defined_input_name"]) || array_key_exists("defined_input_name", $context) ? $context["defined_input_name"] : (function () { throw new RuntimeError('Variable "defined_input_name" does not exist.', 754, $this->source); })()), "html", null, true);
                yield "\" value=\"1\"></input>

        ";
                // line 757
                yield "        ";
                $context["name"] = ((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 757, $this->source); })()) . "[]");
                // line 758
                yield "    ";
            }
            // line 759
            yield "    ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["rand" => Twig\Extension\CoreExtension::random($this->env->getCharset()), "width" => "100%"],             // line 762
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 762, $this->source); })()));
            // line 763
            yield "    ";
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 763), "isMandatoryField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 763, $this->source); })())], "method", true, true, false, 763)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 763, $this->source); })()), "fields_template", [], "any", false, false, false, 763), "isMandatoryField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 763, $this->source); })())], "method", false, false, false, 763), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 764
                yield "        ";
                $context["options"] = Twig\Extension\CoreExtension::merge(["specific_tags" => ["required" => true]], (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 764, $this->source); })()));
                // line 765
                yield "    ";
            }
            // line 766
            yield "
    ";
            // line 767
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 767), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 767, $this->source); })())], "method", true, true, false, 767)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 767, $this->source); })()), "fields_template", [], "any", false, false, false, 767), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 767, $this->source); })())], "method", false, false, false, 767), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 768
                yield "        ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 768, $this->source); })()), ["readonly" => true]);
                // line 769
                yield "    ";
            }
            // line 770
            yield "
    ";
            // line 771
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 771, $this->source); })()), "disabled", [], "any", false, false, false, 771)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 772
                yield "        ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 772, $this->source); })()), ["specific_tags" => ["disabled" => "disabled"]]);
                // line 773
                yield "    ";
            }
            // line 774
            yield "
    ";
            // line 775
            $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 775, $this->source); })()), ["id" => (("dropdown_" . Twig\Extension\CoreExtension::replace(            // line 776
(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 776, $this->source); })()), ["[" => "_", "]" => "_"])) . CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 776, $this->source); })()), "rand", [], "any", false, false, false, 776))]);
            // line 778
            yield "    ";
            $context["field"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 779
                yield "        ";
                $context["ajax_opts"] = Twig\Extension\CoreExtension::filter($this->env, $this->env->hasExtension(\Twig\Extension\SandboxExtension::class) && $this->env->getExtension(\Twig\Extension\SandboxExtension::class)->isSandboxed($this->source), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 779, $this->source); })()), function ($__v__, $__k__) use ($context, $macros) { $context["v"] = $__v__; $context["k"] = $__k__; return CoreExtension::inFilter((isset($context["k"]) || array_key_exists("k", $context) ? $context["k"] : (function () { throw new RuntimeError('Variable "k" does not exist.', 779, $this->source); })()), ["templateResult", "templateSelection", "rand"]); });
                // line 780
                yield "        ";
                yield $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Html::jsAjaxDropdown", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 780, $this->source); })()), CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 780, $this->source); })()), "id", [], "any", false, false, false, 780), (isset($context["url"]) || array_key_exists("url", $context) ? $context["url"] : (function () { throw new RuntimeError('Variable "url" does not exist.', 780, $this->source); })()), (isset($context["ajax_opts"]) || array_key_exists("ajax_opts", $context) ? $context["ajax_opts"] : (function () { throw new RuntimeError('Variable "ajax_opts" does not exist.', 780, $this->source); })())]);
                yield "
    ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 782
            yield "
    ";
            // line 783
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(Twig\Extension\CoreExtension::trim((isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 783, $this->source); })())))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 784
                yield "        ";
                yield $this->getTemplateForMacro("macro_field", $context, 784, $this->getSourceContext())->macro_field(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 784, $this->source); })()), (isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 784, $this->source); })()), (isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 784, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 784, $this->source); })())]);
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
            if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 789, $this->source); })())) == 0)) {
                // line 790
                yield "      ";
                $context["value"] = "&nbsp;";
                // line 791
                yield "   ";
            }
            // line 792
            yield "   ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["wrapper_class" => "form-control-plaintext"],             // line 794
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 794, $this->source); })()));
            // line 795
            yield "
   ";
            // line 796
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 796), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 796, $this->source); })())], "method", true, true, false, 796)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 796, $this->source); })()), "fields_template", [], "any", false, false, false, 796), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 796, $this->source); })())], "method", false, false, false, 796), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 797
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 797, $this->source); })()), ["readonly" => true]);
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
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 801, $this->source); })()), "wrapper_class", [], "any", false, false, false, 801), "html", null, true);
                yield "\">";
                yield (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 801, $this->source); })());
                yield "</span>
   ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 803
            yield "   ";
            yield $this->getTemplateForMacro("macro_field", $context, 803, $this->getSourceContext())->macro_field(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 803, $this->source); })()), (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 803, $this->source); })()), (isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 803, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 803, $this->source); })())]);
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
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 815, $this->source); })()));
            // line 816
            yield "
   ";
            // line 817
            if (CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "locked_fields", [], "any", false, true, false, 817), (isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 817, $this->source); })()), [], "array", true, true, false, 817)) {
                // line 818
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 818, $this->source); })()), ["locked" => true, "locked_value" => CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 818, $this->source); })()), "locked_fields", [], "any", false, false, false, 818), (isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 818, $this->source); })()), [], "array", false, false, false, 818)]);
                // line 819
                yield "   ";
            } elseif (CoreExtension::inFilter((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 819, $this->source); })()), CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 819, $this->source); })()), "locked_fields", [], "any", false, false, false, 819))) {
                // line 820
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 820, $this->source); })()), ["locked" => true]);
                // line 821
                yield "   ";
            }
            // line 822
            yield "
   ";
            // line 823
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 823), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 823, $this->source); })())], "method", true, true, false, 823)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 823, $this->source); })()), "fields_template", [], "any", false, false, false, 823), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 823, $this->source); })())], "method", false, false, false, 823), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 824
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 824, $this->source); })()), ["readonly" => true]);
                // line 825
                yield "   ";
            }
            // line 826
            yield "
   ";
            // line 827
            if ((($tmp =  !CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 827, $this->source); })()), "include_field", [], "any", false, false, false, 827)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 828
                yield "      ";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 828, $this->source); })()), "html", null, true);
                yield "
   ";
            } else {
                // line 830
                yield "      ";
                $context["id"] = Html::sanitizeDomId(((((Twig\Extension\CoreExtension::length($this->env->getCharset(), (((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "id", [], "any", true, true, false, 830) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 830, $this->source); })()), "id", [], "any", false, false, false, 830)))) ? (CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 830, $this->source); })()), "id", [], "any", false, false, false, 830)) : (""))) > 0) && (CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 830, $this->source); })()), "id", [], "any", false, false, false, 830) != "%id%"))) ? (CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 830, $this->source); })()), "id", [], "any", false, false, false, 830)) : ((((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 830, $this->source); })()) . "_") . CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 830, $this->source); })()), "rand", [], "any", false, false, false, 830)))));
                // line 831
                yield "      ";
                // line 832
                yield "      ";
                $context["field"] = Twig\Extension\CoreExtension::replace((isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 832, $this->source); })()), [ (string)$this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape("%id%", "css"), "js") =>                 // line 833
(isset($context["id"]) || array_key_exists("id", $context) ? $context["id"] : (function () { throw new RuntimeError('Variable "id" does not exist.', 833, $this->source); })()),  (string)$this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape("%id%", "js") =>                 // line 834
(isset($context["id"]) || array_key_exists("id", $context) ? $context["id"] : (function () { throw new RuntimeError('Variable "id" does not exist.', 834, $this->source); })()),  (string)$this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape("%id%", "css") =>                 // line 835
(isset($context["id"]) || array_key_exists("id", $context) ? $context["id"] : (function () { throw new RuntimeError('Variable "id" does not exist.', 835, $this->source); })()), "%id%" =>                 // line 836
(isset($context["id"]) || array_key_exists("id", $context) ? $context["id"] : (function () { throw new RuntimeError('Variable "id" does not exist.', 836, $this->source); })())]);
                // line 838
                yield "      ";
                $context["add_field_html"] = (((Twig\Extension\CoreExtension::length($this->env->getCharset(), CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 838, $this->source); })()), "add_field_html", [], "any", false, false, false, 838)) > 0)) ? (CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 838, $this->source); })()), "add_field_html", [], "any", false, false, false, 838)) : (""));
                // line 839
                yield "
      ";
                // line 840
                if ((($tmp =  !((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 840), "isHiddenField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 840, $this->source); })())], "method", true, true, false, 840)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 840, $this->source); })()), "fields_template", [], "any", false, false, false, 840), "isHiddenField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 840, $this->source); })())], "method", false, false, false, 840), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 841
                    yield "         ";
                    if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 841, $this->source); })()), "no_label", [], "any", false, false, false, 841)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                        // line 842
                        yield "            ";
                        yield $this->getTemplateForMacro("macro_noLabelField", $context, 842, $this->getSourceContext())->macro_noLabelField(...[(isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 842, $this->source); })()), (isset($context["id"]) || array_key_exists("id", $context) ? $context["id"] : (function () { throw new RuntimeError('Variable "id" does not exist.', 842, $this->source); })()), (isset($context["add_field_html"]) || array_key_exists("add_field_html", $context) ? $context["add_field_html"] : (function () { throw new RuntimeError('Variable "add_field_html" does not exist.', 842, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 842, $this->source); })())]);
                        yield "
         ";
                    } elseif ((($tmp = CoreExtension::getAttribute($this->env, $this->source,                     // line 843
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 843, $this->source); })()), "is_horizontal", [], "any", false, false, false, 843)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                        // line 844
                        yield "            ";
                        yield $this->getTemplateForMacro("macro_horizontalField", $context, 844, $this->getSourceContext())->macro_horizontalField(...[(isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 844, $this->source); })()), (isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 844, $this->source); })()), (isset($context["id"]) || array_key_exists("id", $context) ? $context["id"] : (function () { throw new RuntimeError('Variable "id" does not exist.', 844, $this->source); })()), (isset($context["add_field_html"]) || array_key_exists("add_field_html", $context) ? $context["add_field_html"] : (function () { throw new RuntimeError('Variable "add_field_html" does not exist.', 844, $this->source); })()), Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 844, $this->source); })()), ["name" => (isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 844, $this->source); })())])]);
                        yield "
         ";
                    } else {
                        // line 846
                        yield "            ";
                        yield $this->getTemplateForMacro("macro_verticalField", $context, 846, $this->getSourceContext())->macro_verticalField(...[(isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 846, $this->source); })()), (isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 846, $this->source); })()), (isset($context["id"]) || array_key_exists("id", $context) ? $context["id"] : (function () { throw new RuntimeError('Variable "id" does not exist.', 846, $this->source); })()), (isset($context["add_field_html"]) || array_key_exists("add_field_html", $context) ? $context["add_field_html"] : (function () { throw new RuntimeError('Variable "add_field_html" does not exist.', 846, $this->source); })()), Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 846, $this->source); })()), ["name" => (isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 846, $this->source); })())])]);
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
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["id"]) || array_key_exists("id", $context) ? $context["id"] : (function () { throw new RuntimeError('Variable "id" does not exist.', 854, $this->source); })()), "html", null, true);
                yield "\" class=\"form-field-ajax\">
         ";
                // line 855
                if ((($tmp =  !(null === (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 855, $this->source); })()))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 856
                    yield "            ";
                    yield (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 856, $this->source); })());
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
            yield $this->getTemplateForMacro("macro_field", $context, 860, $this->getSourceContext())->macro_field(...[(isset($context["id"]) || array_key_exists("id", $context) ? $context["id"] : (function () { throw new RuntimeError('Variable "id" does not exist.', 860, $this->source); })()), (isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 860, $this->source); })()), (isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 860, $this->source); })()), Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 860, $this->source); })()), ["id" => (((isset($context["id"]) || array_key_exists("id", $context) ? $context["id"] : (function () { throw new RuntimeError('Variable "id" does not exist.', 860, $this->source); })()) . "_") . (((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "rand", [], "any", true, true, false, 860) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 860, $this->source); })()), "rand", [], "any", false, false, false, 860)))) ? (CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 860, $this->source); })()), "rand", [], "any", false, false, false, 860)) : (Twig\Extension\CoreExtension::random($this->env->getCharset()))))])]);
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
            $context["options"] = Twig\Extension\CoreExtension::merge(["is_horizontal" => true], (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 864, $this->source); })()));
            // line 865
            yield "
   ";
            // line 866
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 866, $this->source); })()), "is_horizontal", [], "any", false, false, false, 866)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 867
                yield "      ";
                yield $this->getTemplateForMacro("macro_horizontalField", $context, 867, $this->getSourceContext())->macro_horizontalField(...[null, null, null, null, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 867, $this->source); })())]);
                yield "
   ";
            } else {
                // line 869
                yield "      ";
                yield $this->getTemplateForMacro("macro_verticalField", $context, 869, $this->getSourceContext())->macro_verticalField(...[null, null, null, null, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 869, $this->source); })())]);
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
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 881, $this->source); })()));
            // line 882
            yield "
   ";
            // line 883
            $context["class"] = (((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "field_class", [], "any", true, true, false, 883) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 883, $this->source); })()), "field_class", [], "any", false, false, false, 883)))) ? (CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 883, $this->source); })()), "field_class", [], "any", false, false, false, 883)) : ("col-12 col-sm-6"));
            // line 884
            yield "   ";
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 884, $this->source); })()), "full_width", [], "any", false, false, false, 884)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 885
                yield "      ";
                $context["class"] = "col-12";
                // line 886
                yield "   ";
            }
            // line 887
            yield "   ";
            $context["class"] = (((isset($context["class"]) || array_key_exists("class", $context) ? $context["class"] : (function () { throw new RuntimeError('Variable "class" does not exist.', 887, $this->source); })()) . " ") . CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 887, $this->source); })()), "add_field_class", [], "any", false, false, false, 887));
            // line 888
            yield "
   ";
            // line 889
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 889, $this->source); })()), "add_field_attribs", [], "any", false, false, false, 889))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 890
                yield "      ";
                $context["extra_attribs"] = $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Html::parseAttributes", ["options" => CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 890, $this->source); })()), "add_field_attribs", [], "any", false, false, false, 890)]);
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
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["class"]) || array_key_exists("class", $context) ? $context["class"] : (function () { throw new RuntimeError('Variable "class" does not exist.', 895, $this->source); })()), "html", null, true);
            yield " ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 895, $this->source); })()), "mb", [], "any", false, false, false, 895), "html", null, true);
            yield " ";
            yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 895, $this->source); })()), "inline_add_field_html", [], "any", false, false, false, 895)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("d-flex") : (""));
            yield "\" ";
            yield (isset($context["extra_attribs"]) || array_key_exists("extra_attribs", $context) ? $context["extra_attribs"] : (function () { throw new RuntimeError('Variable "extra_attribs" does not exist.', 895, $this->source); })());
            yield ">
      ";
            // line 896
            yield (isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 896, $this->source); })());
            yield "
      ";
            // line 897
            yield (isset($context["add_field_html"]) || array_key_exists("add_field_html", $context) ? $context["add_field_html"] : (function () { throw new RuntimeError('Variable "add_field_html" does not exist.', 897, $this->source); })());
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
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 916, $this->source); })()));
            // line 917
            yield "
   ";
            // line 918
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 918, $this->source); })()), "icon_label", [], "any", false, false, false, 918)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 919
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge(["label_class" => "col-2", "input_class" => "col-10"],                 // line 922
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 922, $this->source); })()));
                // line 923
                yield "   ";
            }
            // line 924
            yield "
   ";
            // line 925
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 925, $this->source); })()), "full_width", [], "any", false, false, false, 925)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 926
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 926, $this->source); })()), ["field_class" => "col-12 glpi-full-width"]);
                // line 929
                yield "   ";
            }
            // line 930
            yield "
   ";
            // line 931
            $context["options"] = Twig\Extension\CoreExtension::merge(["label_class" => "col-xxl-5", "input_class" => "col-xxl-7"],             // line 934
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 934, $this->source); })()));
            // line 935
            yield "
   ";
            // line 936
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 936, $this->source); })()), "align_label_right", [], "any", false, false, false, 936)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 937
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 937, $this->source); })()), ["label_class" => ((CoreExtension::getAttribute($this->env, $this->source,                 // line 938
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 938, $this->source); })()), "label_class", [], "any", false, false, false, 938) . " text-xxl-") . CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 938, $this->source); })()), "label_align", [], "any", false, false, false, 938))]);
                // line 940
                yield "   ";
            }
            // line 941
            yield "
   ";
            // line 942
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 942, $this->source); })()), "add_field_attribs", [], "any", false, false, false, 942))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 943
                yield "      ";
                $context["extra_attribs"] = $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Html::parseAttributes", ["options" => CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 943, $this->source); })()), "add_field_attribs", [], "any", false, false, false, 943)]);
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
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 949, $this->source); })()), "container_id", [], "any", false, false, false, 949))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 950
                yield "      ";
                $context["container_id"] = ("id=" . CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 950, $this->source); })()), "container_id", [], "any", false, false, false, 950));
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
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 955, $this->source); })()), "field_class", [], "any", false, false, false, 955), "html", null, true);
            yield " ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 955, $this->source); })()), "add_field_class", [], "any", false, false, false, 955), "html", null, true);
            yield " ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 955, $this->source); })()), "mb", [], "any", false, false, false, 955), "html", null, true);
            yield "\" ";
            yield (isset($context["extra_attribs"]) || array_key_exists("extra_attribs", $context) ? $context["extra_attribs"] : (function () { throw new RuntimeError('Variable "extra_attribs" does not exist.', 955, $this->source); })());
            yield " ";
            if ((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "name", [], "any", true, true, false, 955) &&  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 955, $this->source); })()), "name", [], "any", false, false, false, 955)))) {
                yield "data-testid=\"form-field-";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 955, $this->source); })()), "name", [], "any", false, false, false, 955), "html", null, true);
                yield "\"";
            }
            yield ">
      ";
            // line 956
            $macros["_inputs"] = $this->load("components/form/basic_inputs_macros.html.twig", 956)->unwrap();
            // line 957
            yield "      ";
            yield $macros["_inputs"]->getTemplateForMacro("macro_label", $context, 957, $this->getSourceContext())->macro_label(...[(isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 957, $this->source); })()), (isset($context["id"]) || array_key_exists("id", $context) ? $context["id"] : (function () { throw new RuntimeError('Variable "id" does not exist.', 957, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 957, $this->source); })()), ((("col-form-label " . CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 957, $this->source); })()), "label_class", [], "any", false, false, false, 957)) . " ") . CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 957, $this->source); })()), "add_label_class", [], "any", false, false, false, 957))]);
            yield "
      ";
            // line 958
            $context["flex_class"] = (((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 958, $this->source); })()), "center", [], "any", false, false, false, 958)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("d-flex align-items-center") : ((((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 958, $this->source); })()), "inline_add_field_html", [], "any", false, false, false, 958)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("d-flex") : (""))));
            // line 959
            yield "      <div ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["container_id"]) || array_key_exists("container_id", $context) ? $context["container_id"] : (function () { throw new RuntimeError('Variable "container_id" does not exist.', 959, $this->source); })()), "html", null, true);
            yield " class=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 959, $this->source); })()), "input_class", [], "any", false, false, false, 959), "html", null, true);
            yield " ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["flex_class"]) || array_key_exists("flex_class", $context) ? $context["flex_class"] : (function () { throw new RuntimeError('Variable "flex_class" does not exist.', 959, $this->source); })()), "html", null, true);
            yield " field-container\">
         ";
            // line 960
            yield (isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 960, $this->source); })());
            yield "
         ";
            // line 961
            yield (isset($context["add_field_html"]) || array_key_exists("add_field_html", $context) ? $context["add_field_html"] : (function () { throw new RuntimeError('Variable "add_field_html" does not exist.', 961, $this->source); })());
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
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 977, $this->source); })()));
            // line 978
            yield "
   ";
            // line 979
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 979, $this->source); })()), "full_width", [], "any", false, false, false, 979)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 980
                yield "      ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 980, $this->source); })()), ["field_class" => "col-12"]);
                // line 983
                yield "   ";
            }
            // line 984
            yield "
   ";
            // line 985
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 985, $this->source); })()), "add_field_attribs", [], "any", false, false, false, 985))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 986
                yield "      ";
                $context["extra_attribs"] = $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Html::parseAttributes", ["options" => CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 986, $this->source); })()), "add_field_attribs", [], "any", false, false, false, 986)]);
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
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 991, $this->source); })()), "field_class", [], "any", false, false, false, 991), "html", null, true);
            yield " ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 991, $this->source); })()), "add_field_class", [], "any", false, false, false, 991), "html", null, true);
            yield " ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 991, $this->source); })()), "mb", [], "any", false, false, false, 991), "html", null, true);
            yield "\" ";
            yield (isset($context["extra_attribs"]) || array_key_exists("extra_attribs", $context) ? $context["extra_attribs"] : (function () { throw new RuntimeError('Variable "extra_attribs" does not exist.', 991, $this->source); })());
            yield " ";
            if ((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "name", [], "any", true, true, false, 991) &&  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 991, $this->source); })()), "name", [], "any", false, false, false, 991)))) {
                yield "data-testid=\"form-field-";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 991, $this->source); })()), "name", [], "any", false, false, false, 991), "html", null, true);
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
            yield $macros["_inputs"]->getTemplateForMacro("macro_label", $context, 994, $this->getSourceContext())->macro_label(...[(isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 994, $this->source); })()), (isset($context["id"]) || array_key_exists("id", $context) ? $context["id"] : (function () { throw new RuntimeError('Variable "id" does not exist.', 994, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 994, $this->source); })()), ("col-form-label " . CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 994, $this->source); })()), "label_class", [], "any", false, false, false, 994))]);
            yield "
         ";
            // line 995
            yield CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 995, $this->source); })()), "insert_content_after_label", [], "any", false, false, false, 995);
            yield "
      </div>
      <div class=\"";
            // line 997
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 997, $this->source); })()), "input_class", [], "any", false, false, false, 997), "html", null, true);
            yield " field-container\">
         ";
            // line 998
            yield (isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 998, $this->source); })());
            yield "
      </div>
      ";
            // line 1000
            yield (isset($context["add_field_html"]) || array_key_exists("add_field_html", $context) ? $context["add_field_html"] : (function () { throw new RuntimeError('Variable "add_field_html" does not exist.', 1000, $this->source); })());
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
            yield $macros["_inputs"]->getTemplateForMacro("macro_label", $context, 1006, $this->getSourceContext())->macro_label(...[(isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 1006, $this->source); })()), (isset($context["id"]) || array_key_exists("id", $context) ? $context["id"] : (function () { throw new RuntimeError('Variable "id" does not exist.', 1006, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 1006, $this->source); })()), (isset($context["class"]) || array_key_exists("class", $context) ? $context["class"] : (function () { throw new RuntimeError('Variable "class" does not exist.', 1006, $this->source); })())]);
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
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 1015, $this->source); })()));
            // line 1016
            yield "
    ";
            // line 1017
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 1017, $this->source); })()), "helper", [], "any", false, false, false, 1017))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 1018
                yield "        ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 1018, $this->source); })()), ["helper" => Twig\Extension\CoreExtension::sprintf(CoreExtension::getAttribute($this->env, $this->source,                 // line 1019
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 1019, $this->source); })()), "helper", [], "any", false, false, false, 1019), CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 1019, $this->source); })()), "language", [], "any", false, false, false, 1019))]);
                // line 1021
                yield "    ";
            }
            // line 1022
            yield "
    ";
            // line 1023
            $context["code_container_id"] = (((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 1023, $this->source); })()) . "_") . Twig\Extension\CoreExtension::random($this->env->getCharset()));
            // line 1024
            yield "    ";
            $context["code_container"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 1025
                yield "        <div id=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["code_container_id"]) || array_key_exists("code_container_id", $context) ? $context["code_container_id"] : (function () { throw new RuntimeError('Variable "code_container_id" does not exist.', 1025, $this->source); })()), "html", null, true);
                yield "\" class=\"form-control overflow-hidden text-start\" style=\"height: ";
                yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 1025, $this->source); })()), "single_line", [], "any", false, false, false, 1025)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("36px") : ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "height", [], "any", true, true, false, 1025)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 1025, $this->source); })()), "height", [], "any", false, false, false, 1025), "auto")) : ("auto")), "html", null, true)));
                yield ";\"></div>
    ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 1027
            yield "    ";
            yield $this->getTemplateForMacro("macro_htmlField", $context, 1027, $this->getSourceContext())->macro_htmlField(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 1027, $this->source); })()), (isset($context["code_container"]) || array_key_exists("code_container", $context) ? $context["code_container"] : (function () { throw new RuntimeError('Variable "code_container" does not exist.', 1027, $this->source); })()), (isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 1027, $this->source); })()), Twig\Extension\CoreExtension::merge(["wrapper_class" => "d-flex flex-grow-1"],             // line 1029
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 1029, $this->source); })()))]);
            yield "
    <script>
        \$(() => {
            const editor_options = ";
            // line 1032
            yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 1032, $this->source); })()), "single_line", [], "any", false, false, false, 1032)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("true") : ("false"));
            yield " ? window.GLPI.Monaco.getSingleLineEditorOptions() : {};
            window.GLPI.Monaco.createEditor(\x27";
            // line 1033
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["code_container_id"]) || array_key_exists("code_container_id", $context) ? $context["code_container_id"] : (function () { throw new RuntimeError('Variable "code_container_id" does not exist.', 1033, $this->source); })()), "js"), "html", null, true);
            yield "\x27, \x27";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 1033, $this->source); })()), "language", [], "any", false, false, false, 1033), "js"), "html", null, true);
            yield "\x27, \"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 1033, $this->source); })()), "js"), "html", null, true);
            yield "\", ";
            yield json_encode(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 1033, $this->source); })()), "completions", [], "any", false, false, false, 1033));
            yield ", editor_options).then(() => {
                \$(\x27#";
            // line 1034
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["code_container_id"]) || array_key_exists("code_container_id", $context) ? $context["code_container_id"] : (function () { throw new RuntimeError('Variable "code_container_id" does not exist.', 1034, $this->source); })()), "css"), "js"), "html", null, true);
            yield "\x27).closest(\x27form\x27).on(\x27formdata\x27, (e) => {
                    const editors = window.monaco.editor.getEditors().filter((editor) => {
                        return editor._domElement.id === \x27";
            // line 1036
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["code_container_id"]) || array_key_exists("code_container_id", $context) ? $context["code_container_id"] : (function () { throw new RuntimeError('Variable "code_container_id" does not exist.', 1036, $this->source); })()), "js"), "html", null, true);
            yield "\x27;
                    });
                    if (editors.length) {
                        e.originalEvent.formData.delete(\x27";
            // line 1039
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 1039, $this->source); })()), "js"), "html", null, true);
            yield "\x27);
                        e.originalEvent.formData.append(\x27";
            // line 1040
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 1040, $this->source); })()), "js"), "html", null, true);
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
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 1052, $this->source); })()));
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
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["container_id"]) || array_key_exists("container_id", $context) ? $context["container_id"] : (function () { throw new RuntimeError('Variable "container_id" does not exist.', 1059, $this->source); })()), "html", null, true);
                yield "\">
            <input
                name=\"";
                // line 1061
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 1061, $this->source); })()), "html", null, true);
                yield "\"
                type=\"hidden\"
                value=\"";
                // line 1063
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 1063, $this->source); })()), "html", null, true);
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
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 1070, $this->source); })()), "extra_css_classes", [], "any", false, false, false, 1070), "html", null, true);
                yield "\"
                role=\"button\"
                aria-label=\"";
                // line 1072
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Select an illustration"), "html", null, true);
                yield "\"
                data-bs-toggle=\"modal\"
                data-bs-target=\"#";
                // line 1074
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["modal_id"]) || array_key_exists("modal_id", $context) ? $context["modal_id"] : (function () { throw new RuntimeError('Variable "modal_id" does not exist.', 1074, $this->source); })()), "html", null, true);
                yield "\"
                data-glpi-icon-picker-value-preview
            >
                <div class=\"card-body aspect-ratio-1\">
                    ";
                // line 1078
                $context["is_custom_file"] = (is_string($_v0 = (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 1078, $this->source); })())) && is_string($_v1 = (isset($context["custom_icon_prefix"]) || array_key_exists("custom_icon_prefix", $context) ? $context["custom_icon_prefix"] : (function () { throw new RuntimeError('Variable "custom_icon_prefix" does not exist.', 1078, $this->source); })())) && str_starts_with($_v0, $_v1));
                // line 1079
                yield "                    <div
                        ";
                // line 1080
                if ((($tmp = (isset($context["is_custom_file"]) || array_key_exists("is_custom_file", $context) ? $context["is_custom_file"] : (function () { throw new RuntimeError('Variable "is_custom_file" does not exist.', 1080, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
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
                yield $this->extensions['Glpi\Application\View\Extension\IllustrationExtension']->renderIllustration((isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 1087, $this->source); })()), 100);
                yield "
                    </div>

                    ";
                // line 1093
                yield "                    ";
                if ((($tmp = (isset($context["is_custom_file"]) || array_key_exists("is_custom_file", $context) ? $context["is_custom_file"] : (function () { throw new RuntimeError('Variable "is_custom_file" does not exist.', 1093, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
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
                    yield $this->extensions['Glpi\Application\View\Extension\IllustrationExtension']->renderIllustration((isset($context["custom_icon_prefix"]) || array_key_exists("custom_icon_prefix", $context) ? $context["custom_icon_prefix"] : (function () { throw new RuntimeError('Variable "custom_icon_prefix" does not exist.', 1106, $this->source); })()), 100);
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
(isset($context["modal_id"]) || array_key_exists("modal_id", $context) ? $context["modal_id"] : (function () { throw new RuntimeError('Variable "modal_id" does not exist.', 1114, $this->source); })()), "backdrop" => CoreExtension::getAttribute($this->env, $this->source,                 // line 1115
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 1115, $this->source); })()), "backdrop", [], "any", false, false, false, 1115)], false);
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
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["container_id"]) || array_key_exists("container_id", $context) ? $context["container_id"] : (function () { throw new RuntimeError('Variable "container_id" does not exist.', 1125, $this->source); })()), "js"), "html", null, true);
                yield "\x27),
                        document.getElementById(\x27";
                // line 1126
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["modal_id"]) || array_key_exists("modal_id", $context) ? $context["modal_id"] : (function () { throw new RuntimeError('Variable "modal_id" does not exist.', 1126, $this->source); })()), "js"), "html", null, true);
                yield "\x27),
                        \"";
                // line 1127
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["custom_icon_prefix"]) || array_key_exists("custom_icon_prefix", $context) ? $context["custom_icon_prefix"] : (function () { throw new RuntimeError('Variable "custom_icon_prefix" does not exist.', 1127, $this->source); })()), "js"), "html", null, true);
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
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 1136, $this->source); })()));
            // line 1137
            yield "    ";
            yield $this->getTemplateForMacro("macro_field", $context, 1137, $this->getSourceContext())->macro_field(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 1137, $this->source); })()), (isset($context["field"]) || array_key_exists("field", $context) ? $context["field"] : (function () { throw new RuntimeError('Variable "field" does not exist.', 1137, $this->source); })()), (isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 1137, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 1137, $this->source); })())]);
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

{% macro largeTitle(label, icon = \x27\x27, first = false, helper = \x27\x27) %}
   {% set margins = \x27mt-3\x27 %}
   {% if first %}
      {% set margins = \x27mt-n2\x27 %}
   {% endif %}

   <div class=\"card border-0 shadow-none p-0 m-0 {{ margins }}\">
      <div class=\"card-header mb-3 pt-2 border-top rounded-0\">
         <h4 class=\"card-title {{ icon|length ? \x27ms-5\x27 : \x27\x27 }}\">
            {% if icon|length %}
               <div class=\"ribbon ribbon-bookmark ribbon-top ribbon-start bg-blue s-1\">
                  <i class=\"fs-2x {{ icon }}\"></i>
               </div>
            {% endif %}
            {{ label }}
            {% if helper is not empty %}
               <span class=\"form-help\" data-bs-toggle=\"tooltip\" data-bs-placement=\"top\" data-bs-html=\"true\"
                     data-bs-title=\"{{ helper }}\">?</span>
            {% endif %}
         </h4>
      </div>
   </div>
{% endmacro %}

{% macro smallTitle(label, icon = \x27\x27, helper = \x27\x27, id = \x27\x27) %}
   {% set margins = \x27mt-2 mb-2\x27 %}
   {% set id = id != \x27\x27 ? id : \x27formsection\x27 ~ random() %}

   <div class=\"card border-0 shadow-none p-0 m-0 {{ margins }}\">
      <div id=\"{{ id }}\" class=\"card-header mb-1 p-0 ps-3 py-1\">
         <h4 class=\"card-subtitle {{ icon|length ? \x27ms-4\x27 : \x27\x27 }}\">
            {% if icon|length %}
               <div class=\"ribbon ribbon-bookmark ribbon-top ribbon-start bg-blue s-1\">
                  <i class=\"fs-2x {{ icon }}\"></i>
               </div>
            {% endif %}
             <span class=\"ms-2\">{{ label }}</span>
            {% if helper is not empty %}
               <span class=\"form-help\" data-bs-toggle=\"tooltip\" data-bs-placement=\"top\" data-bs-html=\"true\"
                     data-bs-title=\"{{ helper }}\">?</span>
            {% endif %}
         </h4>
      </div>
   </div>
{% endmacro %}

{% macro autoNameField(name, item, label = \x27\x27, withtemplate = 0, options = {}) %}
   {% set tpl_value = (options.value ?? \x27\x27)|length > 0 ? options.value : item.fields[name] %}
   {% if item.isTemplate() %} {# TODO exluded types #}
       {% set options = options|merge({
           tpl_mark: item.getAutofillMark(name, {\x27withtemplate\x27: withtemplate}, tpl_value)
       }) %}
   {% endif %}
   {% if item.fields[name] is defined and item.fields[name] is not null %}
      {% set value = call(\x27autoName\x27, [item.fields[name], name, (withtemplate == 2), item.getType(), item.fields[\x27entities_id\x27] ?? null]) %}
   {% else %}
      {% set value = null %}
   {% endif %}

   {{ _self.textField(name, value, label, options) }}
{% endmacro %}


{% macro textField(name, value, label = \x27\x27, options = {}) %}
   {% set options = {
      \x27id\x27: \x27%id%\x27
   }|merge(options) %}

   {% if name in [\x27name\x27] %}
        {% set current_attrs = options.additional_attributes|default({}) %}

         {% if current_attrs.autocomplete is not defined %}
            {% set options = options|merge({
                  \x27additional_attributes\x27: current_attrs|merge({
                     \x27autocomplete\x27: \x27off\x27
                  })
            }) %}
         {% endif %}
   {% endif %}

   {% set field %}
        {% import \x27components/form/basic_inputs_macros.html.twig\x27 as _inputs %}
        {{ _inputs.text(name, value, options) }}
   {% endset %}

   {{ _self.field(name, field, label, options) }}
{% endmacro %}

{% macro urlField(name, value, label = \x27\x27, options = {}) %}
    {% set options = {
        \x27id\x27: \x27%id%\x27
    }|merge(options) %}

    {% set field %}
        {% import \x27components/form/basic_inputs_macros.html.twig\x27 as _inputs %}
        {{ _inputs.input(name, value, options|merge({
            \x27type\x27: \x27url\x27
        })) }}
    {% endset %}

    {{ _self.field(name, field, label, options) }}
{% endmacro %}

{% macro checkboxField(name, value, label = \x27\x27, options = {}) %}
   {% set options = {
      \x27id\x27: \x27%id%\x27,
      \x27center\x27: true,
   }|merge(options) %}

    {% set field %}
        {% import \x27components/form/basic_inputs_macros.html.twig\x27 as _inputs %}
        {{ _inputs.checkbox(name, value, options) }}
    {% endset %}

    {{ _self.field(name, field, label, options) }}
{% endmacro %}


{% macro sliderField(name, value, label = \x27\x27, options = {}) %}
   {% if options.fields_template.isMandatoryField(name)|default(false) %}
      {% set options = {
         \x27required\x27: true
      }|merge(options) %}
   {% endif %}
   {% if options.fields_template.isReadonlyField(name)|default(false) %}
      {% set options = options|merge({\x27readonly\x27: true}) %}
   {% endif %}
   {% set options = {
      \x27no_value\x27: 0,
      \x27yes_value\x27: 1,
      \x27readonly\x27: false,
      \x27required\x27: false,
      \x27disabled\x27: false,
      \x27additional_attributes\x27: [],
      \x27label2\x27: \x27\x27,
   }|merge(options) %}

   {% set field %}
      <label class=\"form-check form-switch mt-2\">
         <input type=\"hidden\"   name=\"{{ name }}\" value=\"{{ options.no_value }}\" />
         <input type=\"checkbox\" name=\"{{ name }}\" value=\"{{ options.yes_value }}\" class=\"form-check-input\" id=\"%id%\"
                {{ value == 1 ? \x27checked\x27 : \x27\x27 }}
                {{ options.readonly ? \x27readonly\x27 : \x27\x27 }}
                {{ options.required ? \x27required\x27 : \x27\x27 }}
                {{ options.disabled ? \x27disabled\x27 : \x27\x27 }}
                {% for attr, value in options.additional_attributes %}
                    {{ attr }}=\"{{ value }}\"
                {% endfor %} />
         {% if options.label2 %}
            <span class=\"form-check-label\">{{ options.label2 }}</span>
         {% endif %}
      </label>
   {% endset %}

   {{ _self.field(name, field, label, options) }}
{% endmacro %}


{% macro numberField(name, value, label = \x27\x27, options = {}) %}
    {% set options = {
        \x27id\x27: \x27%id%\x27
    }|merge(options) %}

    {% set field %}
        {% import \x27components/form/basic_inputs_macros.html.twig\x27 as _inputs %}
        {{ _inputs.number(name, value, options) }}
    {% endset %}

    {{ _self.field(name, field, label, options) }}
{% endmacro %}


{% macro readOnlyField(name, value, label = \x27\x27, options = {}) %}
   {% set options = options|merge({\x27readonly\x27: true}) %}
   {% set value %}
      <span class=\"form-control {{ options.input_addclass ?? \x27\x27 }}\" readonly>
         {% if value|length == 0 %}
            &nbsp;
         {% else %}
            {{ value }}
         {% endif %}
      </span>
   {% endset %}
   {{ _self.field(name, value, label, options) }}
{% endmacro %}


{% macro textareaField(name, value, label = \x27\x27, options = {}) %}
   {% set options = {
      \x27rand\x27: random(),
      \x27enable_richtext\x27: false,
      \x27enable_images\x27: true,
      \x27enable_fileupload\x27: false,
      \x27mention_options\x27: {
         \x27enabled\x27: options.enable_mentions is defined and options.enable_mentions,
         \x27full\x27: true,
         \x27users\x27: [],
      },
      \x27entities_id\x27: session(\x27glpiactive_entity\x27),
      \x27uploads\x27: [],
      \x27rows\x27: 3,
      \x27readonly\x27: false,
   }|merge(options) %}

   {% if options.id is not defined %}
       {# `id` is mandatory at this point to correctly handle file uploads #}
       {% set options = {id: name|safe_dom_id ~ \x27_\x27 ~ options.rand}|merge(options) %}
   {% endif %}

   {% set field %}
        {% import \x27components/form/basic_inputs_macros.html.twig\x27 as _inputs %}
        {{ _inputs.textarea(name, value, options) }}
   {% endset %}

   {% set add_html = \x27\x27 %}
   {% if not options.readonly and options.enable_fileupload %}
      {% set add_html %}
         {% do call(\x27Html::file\x27, [{
             \x27editor_id\x27: options.id,
             \x27multiple\x27: true,
             \x27uploads\x27: options.uploads,
             \x27required\x27: options.fields_template.isMandatoryField(\x27_documents_id\x27)|default(false)
         }]) %}
      {% endset %}
   {% elseif not options.readonly and not options.enable_fileupload and options.enable_richtext and options.enable_images %}
      {% set add_html %}
         {% do call(\x27Html::file\x27, [{
             \x27editor_id\x27: options.id,
             \x27name\x27: name,
             \x27only_uploaded_files\x27: true,
             \x27uploads\x27: options.uploads,
             \x27required\x27: options.fields_template.isMandatoryField(\x27_documents_id\x27)|default(false),
             \x27init\x27: options.init_fileupload ?? options.init ?? true
         }]) %}
      {% endset %}
   {% endif %}

   {% if add_html != \x27\x27 %}
      {% if options.add_field_html is defined %}
         {% set add_html = add_html ~ options.add_field_html %}
      {% endif %}
      {% set options = options|merge({\x27add_field_html\x27: add_html}) %}
   {% endif %}

   {{ _self.field(name, field, label, options) }}
{% endmacro %}


{% macro dateField(name, value, label = \x27\x27, options = {}) %}
   {% set field %}
        {% import \x27components/form/basic_inputs_macros.html.twig\x27 as _inputs %}
        {{ _inputs.date(name, value, options) }}
   {% endset %}

   {{ _self.field(name, field, label, options) }}
{% endmacro %}


{% macro datetimeField(name, value, label = \x27\x27, options = {}) %}
   {% set field %}
        {% import \x27components/form/basic_inputs_macros.html.twig\x27 as _inputs %}
        {{ _inputs.datetime(name, value, options) }}
   {% endset %}

   {{ _self.field(name, field, label, options) }}
{% endmacro %}


{% macro colorField(name, value, label = \x27\x27, options = {}) %}
    {% set options = {
        \x27id\x27: \x27%id%\x27
    }|merge(options) %}

    {% set field %}
        {% import \x27components/form/basic_inputs_macros.html.twig\x27 as _inputs %}
        {{ _inputs.color(name, value, options) }}
    {% endset %}

    {{ _self.field(name, field, label, options) }}
{% endmacro %}


{% macro passwordField(name, value, label = \x27\x27, options = {}) %}
   {% set options = {
      \x27id\x27: \x27%id%\x27,
      \x27can_regenerate\x27: options.can_regenerate ?? false,
      \x27clearable\x27: options.clearable is defined ? options.clearable : not (options.is_disclosable ?? false),
      \x27is_copyable\x27: options.is_disclosable ?? false
   }|merge(options) %}

    {% set field %}
        {% import \x27components/form/basic_inputs_macros.html.twig\x27 as _inputs %}
        {{ _inputs.password(name, value, options) }}
    {% endset %}

   {# Add checkbox if needed to regenerate password (controler side) #}
   {% if options.can_regenerate %}
      {% set regenerate_chk %}
         <div class=\"d-flex align-items-center gap-1 mt-1\">
             <input class=\"form-check-input\" type=\"checkbox\" name=\"_regenerate_{{ name }}\" id=\"_regenerate_{{ name }}\"><label class=\"form-check-label\" for=\"_regenerate_{{ name }}\">{{ __(\x27Regenerate\x27) }}</label>
         </div>
      {% endset %}
      {% set field = field ~ regenerate_chk %}
   {% endif %}

   {{ _self.field(name, field, label, options) }}
{% endmacro %}


{% macro emailField(name, value, label = \x27\x27, options = {}) %}
    {% set options = {
        \x27id\x27: \x27%id%\x27
    }|merge(options) %}

    {% set field %}
        {% import \x27components/form/basic_inputs_macros.html.twig\x27 as _inputs %}
        {{ _inputs.email(name, value, options) }}
    {% endset %}

   {{ _self.field(name, field, label, options) }}
{% endmacro %}

{% macro fileField(name, value, label = \x27\x27, options = {}) %}
   {% set options = {
      \x27id\x27: \x27%id%\x27,
      \x27rand\x27: random(),
      \x27simple\x27: false,
   }|merge(options) %}
   {% set field %}
        {% import \x27components/form/basic_inputs_macros.html.twig\x27 as _inputs %}
        {{ _inputs.file(name, value, options) }}
   {% endset %}
   {{ _self.field(name, field, label, options) }}
{% endmacro %}

{% macro imageField(name, value, label = \x27\x27, options = {}, link_options = {}) %}
   {% set field %}
      <div class=\"img-overlay-wrapper position-relative\">
         {% set clearable = options[\x27clearable\x27] %}
         {% set url = options[\x27url\x27] ?? null %}
         {% set options = options|filter((v, k) => k != \x27url\x27 and k != \x27clearable\x27) %}
         {% if url is not empty %}
            <a href=\"{{ url }}\" {{ call(\x27Html::parseAttributes\x27, [link_options])|raw }}>
         {% endif %}
               <img src=\"{{ value }}\" {{ call(\x27Html::parseAttributes\x27, [options])|raw }} />
         {% if url is not empty %}
            </a>
         {% endif %}
         {% if clearable %}
            <input type=\"hidden\" name=\"_blank_{{ name }}\" />
             {%- set clear_js -%}
                 const blank_input = \$(\x27input[name=\\\x27_blank_{{ name|e(\x27css\x27)|e(\x27js\x27) }}\\\x27]\x27);
                 blank_input.val(blank_input.val() ? \x27\x27 : true);
                 if (\$(this).closest(\x27.picture_gallery_item\x27).length) {
                    \$(this).closest(\x27.picture_gallery_item\x27).hide();
                    \$(this).closest(\x27.picture_gallery\x27).siblings(\x27.deletion_pending\x27).removeClass(\x27d-none\x27);
                 } else {
                    \$(this).closest(\x27.img-overlay-wrapper\x27).hide();
                 }
             {%- endset -%}
            <button type=\"button\" class=\"btn p-2 position-absolute top-0 start-0\" title=\"{{ __(\x27Delete\x27) }}\"
                    onclick=\"{{ clear_js }}\">
               <i class=\"ti ti-x\"></i>
            </button>
         {% endif %}
      </div>
   {% endset %}
   {% if options.fields_template.isReadonlyField(name)|default(false) %}
      {% set options = options|merge({\x27readonly\x27: true}) %}
   {% endif %}
   {{ _self.field(name, field, label, options) }}
{% endmacro %}

{% macro imageGalleryField(name, value, label = \x27\x27, options = {}) %}
   {% set field %}
       <div class=\"text-warning deletion_pending d-none\">
           {{ __(\x27The deletion will only take effect after saving the form\x27) }}
       </div>
      <div class=\"picture_gallery d-flex flex-wrap overflow-auto p-3\">
         {% for i, picture in value %}
            <div class=\"picture_gallery_item\" style=\"position: relative; width: fit-content\">
               {{ _self.imageField(name ~ \x27_\x27 ~ i, picture, \x27\x27, {
                  \x27style\x27: \x27max-width: 300px; max-height: 150px\x27,
                  \x27class\x27: \x27picture_square\x27,
                  \x27clearable\x27: options[\x27clearable\x27],
                  \x27no_label\x27: true,
               }) }}
            </div>
         {% endfor %}
      </div>
      {{ _self.fileField(name, null, \x27\x27, {
         \x27onlyimages\x27: true,
         \x27multiple\x27: true,
      }) }}
   {% endset %}

   {% if options.fields_template.isReadonlyField(name)|default(false) %}
      {% set options = options|merge({\x27readonly\x27: true}) %}
   {% endif %}

   {% set id = (options.id is defined and options.id|length > 0) ? options.id : (name|safe_dom_id ~ \x27_\x27 ~ (options.rand ?? \"\")) %}
   {% import \x27components/form/basic_inputs_macros.html.twig\x27 as _inputs %}
   {{ _inputs.label(label, id, options) }}
   {{ _self.field(name, field, label, options|merge({
      \x27full_width\x27: true,
      \x27no_label\x27: true
   })) }}
{% endmacro %}

{% macro hiddenField(name, value, options = {}) %}
    {% if options is not iterable %}
        {#
            If `options` is not iterable, it means that this macro was used
            with its previous signature (name, value, label, options = {}).
            We get the `options` value from the extra positional arguments (available in `varargs`).
            @TODO Add a deprecation in GLPI 11.1.
         #}
        {% set options = varargs[0]|default({}) %}
    {% endif %}
    {% import \x27components/form/basic_inputs_macros.html.twig\x27 as _inputs %}
    {{ _inputs.hidden(name, value, options) }}
{% endmacro %}

{% macro csrfField() %}
    {{ _self.hiddenField(\x27_glpi_csrf_token\x27, csrf_token()) }}
{% endmacro %}

{% macro dropdownNumberField(name, value, label = \x27\x27, options = {}) %}
    {% set options = {
        \x27rand\x27: random(),
        \x27width\x27: \x27100%\x27,
        \x27disabled\x27: false,
    }|merge(options) %}

   {% if options.fields_template.isReadonlyField(name)|default(false) %}
      {% set options = options|merge({\x27readonly\x27: true}) %}
   {% endif %}

   {% if options.disabled %}
      {% set options = options|merge({specific_tags: {\x27disabled\x27: \x27disabled\x27}}) %}
   {% endif %}
   {% if options.fields_template.isMandatoryField(name)|default(false) %}
      {% set options = {\x27specific_tags\x27: {\x27required\x27: true}}|merge(options) %}
   {% endif %}

   {% set field %}
      {% do call(\x27Dropdown::showNumber\x27, [name, {
         \x27value\x27: value,
         \x27rand\x27: options.rand
      }|merge(options)]) %}
   {% endset %}

   {{ _self.field(name, field, label, options|merge({\x27id\x27: \x27dropdown_\x27 ~ name|replace({\x27[\x27: \x27_\x27, \x27]\x27: \x27_\x27}) ~ options.rand})) }}
{% endmacro %}

{% macro dropdownArrayField(name, value, elements, label = \x27\x27, options = {}) %}
    {% set options = {
        \x27rand\x27: random(),
        \x27disabled\x27: false,
        \x27width\x27: \x27100%\x27,
    }|merge(options) %}

   {% if options.fields_template.isReadonlyField(name)|default(false) %}
      {% set options = options|merge({\x27readonly\x27: true}) %}
   {% endif %}

   {% if options.disabled %}
      {% set options = options|merge({specific_tags: {\x27disabled\x27: \x27disabled\x27}}) %}
   {% endif %}

   {% if options.fields_template.isMandatoryField(name)|default(false) %}
      {% set options = {\x27required\x27: true}|merge(options) %}
   {% endif %}

   {% set field %}
      {% do call(\x27Dropdown::showFromArray\x27, [name, elements, {
         \x27value\x27: value,
         \x27rand\x27: options.rand,
      }|merge(options)]) %}
   {% endset %}

   {{ _self.field(name, field, label, options|merge({\x27id\x27: \x27dropdown_\x27 ~ name|replace({\x27[\x27: \x27_\x27, \x27]\x27: \x27_\x27}) ~ options.rand})) }}
{% endmacro %}

{% macro dropdownTimestampField(name, value, label = \x27\x27, options = {}) %}
    {% set options = {
        \x27rand\x27: random(),
        \x27width\x27: \x27100%\x27,
        \x27disabled\x27: false,
    }|merge(options) %}
   {% if options.fields_template.isMandatoryField(name)|default(false) %}
      {% set options = {\x27required\x27: true}|merge(options) %}
   {% endif %}

   {% if options.fields_template.isReadonlyField(name)|default(false) %}
      {% set options = options|merge({\x27readonly\x27: true}) %}
   {% endif %}

   {% if options.disabled %}
      {% set options = options|merge({specific_tags: {\x27disabled\x27: \x27disabled\x27}}) %}
   {% endif %}

   {% set field %}
      {% do call(\x27Dropdown::showTimestamp\x27, [name, {
         \x27value\x27: value
      }|merge(options)]) %}
   {% endset %}

   {{ _self.field(name, field, label, options|merge({\x27id\x27: \x27dropdown_\x27 ~ name|replace({\x27[\x27: \x27_\x27, \x27]\x27: \x27_\x27}) ~ options.rand})) }}
{% endmacro %}

{% macro dropdownYesNo(name, value, label = \x27\x27, options = {}) %}
    {% set options = {
        \x27rand\x27: random(),
        \x27width\x27: \x27100%\x27,
        \x27disabled\x27: false
    }|merge(options) %}
   {% if options.fields_template.isMandatoryField(name)|default(false) %}
      {% set options = {\x27required\x27: true}|merge(options) %}
   {% endif %}

   {% if options.fields_template.isReadonlyField(name)|default(false) %}
      {% set options = options|merge({\x27readonly\x27: true}) %}
   {% endif %}

   {% if options.disabled %}
      {% set options = options|merge({specific_tags: {\x27disabled\x27: \x27disabled\x27}}) %}
   {% endif %}

   {% set field %}
      {% do call(\x27Dropdown::showYesNo\x27, [name, value, -1, options]) %}
   {% endset %}

   {{ _self.field(name, field, label, options|merge({\x27id\x27: \x27dropdown_\x27 ~ name|replace({\x27[\x27: \x27_\x27, \x27]\x27: \x27_\x27}) ~ options.rand})) }}
{% endmacro %}

{% macro dropdownItemTypes(name, value, label = \x27\x27, options = {}) %}
    {% set options = {
        \x27rand\x27: random(),
        \x27width\x27: \x27100%\x27,
        \x27disabled\x27: false,
    }|merge(options) %}
   {% if options.fields_template.isMandatoryField(name)|default(false) %}
      {% set options = {\x27required\x27: true}|merge(options) %}
   {% endif %}

   {% if options.fields_template.isReadonlyField(name)|default(false) %}
      {% set options = options|merge({\x27readonly\x27: true}) %}
   {% endif %}

   {% if options.disabled %}
      {% set options = options|merge({specific_tags: {\x27disabled\x27: \x27disabled\x27}}) %}
   {% endif %}

   {% set types = options[\x27types\x27]|default([]) %}

   {% set field %}
      {% do call(\x27Dropdown::showItemTypes\x27, [name, types, {
         \x27rand\x27: options.rand,
         \x27value\x27: value
      }|merge(options)]) %}
   {% endset %}

   {{ _self.field(name, field, label, options|merge({\x27id\x27: \x27dropdown_\x27 ~ name|replace({\x27[\x27: \x27_\x27, \x27]\x27: \x27_\x27}) ~ options.rand})) }}
{% endmacro %}

{% macro dropdownItemsFromItemtypes(name, label = \x27\x27, options = {}) %}
   {% set options = {
      \x27rand\x27: random()
   }|merge(options) %}

   {% if options.fields_template.isReadonlyField(name)|default(false) %}
      {% set options = options|merge({\x27readonly\x27: true}) %}
   {% endif %}

   {% set field %}
      {% do call(\x27Dropdown::showSelectItemFromItemtypes\x27, [options]) %}
   {% endset %}
   {{ _self.field(name, field, label, options|merge({\x27id\x27: \x27dropdown_\x27 ~ name|replace({\x27[\x27: \x27_\x27, \x27]\x27: \x27_\x27}) ~ options.rand})) }}
{% endmacro %}

{% macro dropdownIcons(name, value, label = \x27\x27, options = {}) %}
    {% set options = {
        \x27rand\x27: random(),
        \x27width\x27: \x27100%\x27,
        \x27disabled\x27: false,
    }|merge(options) %}
   {% if options.fields_template.isMandatoryField(name)|default(false) %}
      {% set options = {\x27required\x27: true}|merge(options) %}
   {% endif %}

   {% if options.fields_template.isReadonlyField(name)|default(false) %}
      {% set options = options|merge({\x27readonly\x27: true}) %}
   {% endif %}

   {% if options.disabled %}
      {% set options = options|merge({specific_tags: {\x27disabled\x27: \x27disabled\x27}}) %}
   {% endif %}

   {% set field %}
      {% do call(\x27Dropdown::dropdownIcons\x27, [name, value, \x27\x27, options]) %}
   {% endset %}

   {{ _self.field(name, field, label, options|merge({\x27id\x27: \x27dropdown_\x27 ~ name|replace({\x27[\x27: \x27_\x27, \x27]\x27: \x27_\x27}) ~ options.rand})) }}
{% endmacro %}

{% macro dropdownWebIcons(name, value, label = \x27\x27, options = {}) %}
    {% set options = {
        rand: random(),
    }|merge(options|merge({
        noselect2: true,
    })) %}
    {# Trim \x27ti \x27 prefix if it exists in the value #}
    {% set value = value|replace({\x27ti \x27: \x27\x27}) %}

    {{ _self.dropdownArrayField(name, value, {(value): value}, label, options) }}
    <script type=\"module\">
        import(\x27/js/modules/Form/WebIconSelector.js\x27).then((m) => {
            const dropdown_id = \x27{{ (\x27dropdown_\x27 ~ name ~ options.rand)|replace({\x27[\x27: \x27_\x27, \x27]\x27: \x27_\x27})|e(\x27js\x27) }}\x27;
            const selector = new m.default(document.getElementById(dropdown_id));
            selector.init();
        });
    </script>
{% endmacro %}

{% macro dropdownHoursField(name, value, label = \x27\x27, options = {}) %}
    {% set options = {
        \x27rand\x27: random(),
        \x27width\x27: \x27100%\x27,
        \x27disabled\x27: false,
    }|merge(options) %}
   {% if options.fields_template.isMandatoryField(name)|default(false) %}
      {% set options = {\x27required\x27: true}|merge(options) %}
   {% endif %}

   {% if options.fields_template.isReadonlyField(name)|default(false) %}
      {% set options = options|merge({\x27readonly\x27: true}) %}
   {% endif %}

   {% if options.disabled %}
      {% set options = options|merge({specific_tags: {\x27disabled\x27: \x27disabled\x27}}) %}
   {% endif %}

   {% set field %}
      {% do call(\x27Dropdown::showHours\x27, [name, {
         \x27value\x27: value
      }|merge(options)]) %}
   {% endset %}

   {{ _self.field(name, field, label, options|merge({\x27id\x27: \x27dropdown_\x27 ~ name|replace({\x27[\x27: \x27_\x27, \x27]\x27: \x27_\x27}) ~ options.rand})) }}
{% endmacro %}

{% macro dropdownFrequency(name, value, label = \x27\x27, options = {}) %}
   {% set options = {
       \x27rand\x27: random(),
       \x27disabled\x27: false,
   }|merge(options) %}
   {% if options.fields_template.isMandatoryField(name)|default(false) %}
      {% set options = {\x27required\x27: true}|merge(options) %}
   {% endif %}

   {% if options.fields_template.isReadonlyField(name)|default(false) %}
      {% set options = options|merge({\x27readonly\x27: true}) %}
   {% endif %}

   {% if options.disabled %}
      {% set options = options|merge({specific_tags: {\x27disabled\x27: \x27disabled\x27}}) %}
   {% endif %}

   {% set field %}
      {% do call(\x27Dropdown::showFrequency\x27, [name, value, {
         \x27width\x27: \x27100%\x27,
         \x27value\x27: value
      }|merge(options)]) %}
   {% endset %}

   {{ _self.field(name, field, label, options|merge({\x27id\x27: \x27dropdown_\x27 ~ name|replace({\x27[\x27: \x27_\x27, \x27]\x27: \x27_\x27}) ~ options.rand})) }}
{% endmacro %}

{% macro dropdownField(itemtype, name, value, label = \x27\x27, options = {}) %}
   {% if options.multiple ?? false %}
      {# Needed for empty value as the input wont be sent in this case... we need something to know the input was displayed AND empty #}
      {% set defined_input_name = \"_#{name}_defined\" %}
      <input type=\"hidden\" name=\"{{ defined_input_name }}\" value=\"1\"></input>

      {# Multiple values will be set, input need to be an array #}
      {% set name = \"#{name}[]\" %}
   {% endif %}
    {% set options = {
        \x27rand\x27: random(),
        \x27width\x27: \x27100%\x27,
        \x27disabled\x27: false,
    }|merge(options) %}
   {% if options.fields_template.isMandatoryField(name)|default(false) %}
      {% set options = {\x27specific_tags\x27: {\x27required\x27: true}}|merge(options) %}
   {% endif %}

   {% if options.fields_template.isReadonlyField(name)|default(false) %}
      {% set options = options|merge({\x27readonly\x27: true}) %}
   {% endif %}

   {% if options.disabled %}
      {% set options = options|merge({\x27specific_tags\x27: {\x27disabled\x27: \x27disabled\x27}}) %}
   {% endif %}

   {% set field %}
      {{ itemtype|itemtype_dropdown({
         \x27name\x27: name,
         \x27value\x27: value,
      }|merge(options)) }}
   {% endset %}

   {% if field|trim is not empty %}
      {{ _self.field(name, field, label, options|merge({\x27id\x27: \x27dropdown_\x27 ~ name|replace({\x27[\x27: \x27_\x27, \x27]\x27: \x27_\x27}) ~ options.rand})) }}
   {% endif %}
{% endmacro %}

{% macro dropdownAjaxField(url, name, value, label = \x27\x27, options = {}) %}
    {% if options.multiple %}
        {# Needed for empty value as the input wont be sent in this case... we need something to know the input was displayed AND empty #}
        {% set defined_input_name = \"_#{name}_defined\" %}
        <input type=\"hidden\" name=\"{{ defined_input_name }}\" value=\"1\"></input>

        {# Multiple values will be set, input need to be an array #}
        {% set name = \"#{name}[]\" %}
    {% endif %}
    {% set options = {
        \x27rand\x27: random(),
        \x27width\x27: \x27100%\x27,
    }|merge(options) %}
    {% if options.fields_template.isMandatoryField(name)|default(false) %}
        {% set options = {\x27specific_tags\x27: {\x27required\x27: true}}|merge(options) %}
    {% endif %}

    {% if options.fields_template.isReadonlyField(name)|default(false) %}
        {% set options = options|merge({\x27readonly\x27: true}) %}
    {% endif %}

    {% if options.disabled %}
        {% set options = options|merge({\x27specific_tags\x27: {\x27disabled\x27: \x27disabled\x27}}) %}
    {% endif %}

    {% set options = options|merge({
        \x27id\x27: \x27dropdown_\x27 ~ name|replace({\x27[\x27: \x27_\x27, \x27]\x27: \x27_\x27}) ~ options.rand
    }) %}
    {% set field %}
        {% set ajax_opts = options|filter((v, k) => k in [\x27templateResult\x27, \x27templateSelection\x27, \x27rand\x27]) %}
        {{ call(\x27Html::jsAjaxDropdown\x27, [name, options.id, url, ajax_opts])|raw }}
    {% endset %}

    {% if field|trim is not empty %}
        {{ _self.field(name, field, label, options) }}
    {% endif %}
{% endmacro %}

{% macro htmlField(name, value, label = \x27\x27, options = {}) %}
   {% if value|length == 0 %}
      {% set value = \x27&nbsp;\x27 %}
   {% endif %}
   {% set options = {
      wrapper_class: \x27form-control-plaintext\x27
   }|merge(options) %}

   {% if options.fields_template.isReadonlyField(name)|default(false) %}
      {% set options = options|merge({\x27readonly\x27: true}) %}
   {% endif %}

   {% set value %}
      <span class=\"{{ options.wrapper_class }}\">{{ value|raw }}</span>
   {% endset %}
   {{ _self.field(name, value, label, options) }}
{% endmacro %}

{% macro field(name, field, label = \x27\x27, options = {}) %}
   {% set options = {
      \x27rand\x27: random(),
      \x27is_horizontal\x27: true,
      \x27include_field\x27: true,
      \x27add_field_html\x27: \x27\x27,
      \x27locked\x27: false,
      \x27locked_fields\x27: [],
       \x27no_label\x27: false,
   }|merge(options) %}

   {% if options.locked_fields[name] is defined %}
      {% set options = options|merge({\x27locked\x27: true, \x27locked_value\x27: options.locked_fields[name]}) %}
   {% elseif name in options.locked_fields %}
      {% set options = options|merge({\x27locked\x27: true}) %}
   {% endif %}

   {% if options.fields_template.isReadonlyField(name)|default(false) %}
      {% set options = options|merge({\x27readonly\x27: true}) %}
   {% endif %}

   {% if not options.include_field %}
      {{ field }}
   {% else %}
      {% set id    = ((options.id ?? \x27\x27)|length > 0 and options.id != \x27%id%\x27 ? options.id : (name ~ \x27_\x27 ~ options.rand))|safe_dom_id %}
      {# `%id%` may have been escaped by different filters #}
      {% set field = field|replace({
          (\x27%id%\x27|e(\x27css\x27)|e(\x27js\x27)): id,
          (\x27%id%\x27|e(\x27js\x27)): id,
          (\x27%id%\x27|e(\x27css\x27)): id,
          (\x27%id%\x27): id,
      }) %}
      {% set add_field_html = options.add_field_html|length > 0 ? options.add_field_html : \x27\x27 %}

      {% if not options.fields_template.isHiddenField(name)|default(false) %}
         {% if options.no_label %}
            {{ _self.noLabelField(field, id, add_field_html, options) }}
         {% elseif options.is_horizontal %}
            {{ _self.horizontalField(label, field, id, add_field_html, options|merge({\x27name\x27: name})) }}
         {% else %}
            {{ _self.verticalField(label, field, id, add_field_html, options|merge({\x27name\x27: name})) }}
         {% endif %}
      {% endif %}
   {% endif %}
{% endmacro %}

{% macro ajaxField(id, value, label = \x27\x27, options = {}) %}
   {% set field %}
      <div id=\"{{ id }}\" class=\"form-field-ajax\">
         {% if value is not null %}
            {{ value|raw }}
         {% endif %}
      </div>
   {% endset %}
   {{ _self.field(id, field, label, options|merge({\x27id\x27: id ~ \x27_\x27 ~ options.rand ?? random()})) }}
{% endmacro %}

{% macro nullField(options = {}) %}
   {% set options = {\x27is_horizontal\x27: true}|merge(options) %}

   {% if options.is_horizontal %}
      {{ _self.horizontalField(null, null, null, null, options) }}
   {% else %}
      {{ _self.verticalField(null, null, null, null, options) }}
   {% endif %}
{% endmacro %}


{% macro noLabelField(field, id = \x27\x27, add_field_html = \x27\x27, options = {}) %}
   {% set options = {
      \x27full_width\x27: false,
      \x27mb\x27: \x27mb-3\x27,
      \x27add_field_class\x27: \x27\x27,
      \x27add_field_attribs\x27: {},
      \x27inline_add_field_html\x27: false,
   }|merge(options) %}

   {% set class = options.field_class ?? \x27col-12 col-sm-6\x27 %}
   {% if options.full_width %}
      {% set class = \x27col-12\x27 %}
   {% endif %}
   {% set class = class ~ \x27 \x27 ~ options.add_field_class %}

   {% if options.add_field_attribs is not empty %}
      {% set extra_attribs = call(\x27Html::parseAttributes\x27, {options: options.add_field_attribs}) %}
   {% else %}
      {% set extra_attribs = \x27\x27 %}
   {% endif %}

   <div class=\"{{ class }} {{ options.mb }} {{ options.inline_add_field_html ? \x27d-flex\x27 : \x27\x27 }}\" {{ extra_attribs|raw }}>
      {{ field|raw }}
      {{ add_field_html|raw }}
   </div>
{% endmacro %}


{% macro horizontalField(label, field, id, add_field_html = \x27\x27, options = {}) %}
   {% set options = {
      \x27full_width\x27: false,
      \x27align_label_right\x27: true,
      \x27mb\x27: \x27mb-2\x27,
      \x27field_class\x27: \x27col-12 col-sm-6\x27,
      \x27container_id\x27: \x27\x27,
      \x27add_field_class\x27: \x27\x27,
      \x27add_label_class\x27: \x27\x27,
      \x27add_field_attribs\x27: {},
      \x27center\x27: false,
      \x27label_align\x27: \x27end\x27,
      \x27inline_add_field_html\x27: false,
       \x27icon_label\x27: false,
   }|merge(options) %}

   {% if options.icon_label %}
      {% set options = {
         label_class: \x27col-2\x27,
         input_class: \x27col-10\x27,
      }|merge(options) %}
   {% endif %}

   {% if options.full_width %}
      {% set options = options|merge({
         field_class: \x27col-12 glpi-full-width\x27,
      }) %}
   {% endif %}

   {% set options = {
      label_class: \x27col-xxl-5\x27,
      input_class: \x27col-xxl-7\x27,
   }|merge(options) %}

   {% if options.align_label_right %}
      {% set options = options|merge({
         label_class: options.label_class ~ \x27 text-xxl-\x27 ~ options.label_align,
      }) %}
   {% endif %}

   {% if options.add_field_attribs is not empty %}
      {% set extra_attribs = call(\x27Html::parseAttributes\x27, {options: options.add_field_attribs}) %}
   {% else %}
      {% set extra_attribs = \x27\x27 %}
   {% endif %}

   {# Usefull for Ajax::updateItemOnSelectEvent (DOM to update) #}
   {% if options.container_id is not empty %}
      {% set container_id = \x27id=\x27 ~ options.container_id %}
   {% else %}
      {% set container_id = \x27\x27 %}
   {% endif %}

   <div class=\"form-field row align-items-center {{ options.field_class }} {{ options.add_field_class }} {{ options.mb }}\" {{ extra_attribs|raw }} {% if options.name is defined and options.name is not empty %}data-testid=\"form-field-{{ options.name }}\"{% endif %}>
      {% import \x27components/form/basic_inputs_macros.html.twig\x27 as _inputs %}
      {{ _inputs.label(label, id, options, \x27col-form-label \x27 ~ options.label_class ~ \x27 \x27 ~ options.add_label_class) }}
      {% set flex_class = options.center ? \x27d-flex align-items-center\x27 : (options.inline_add_field_html ? \x27d-flex\x27 : \x27\x27) %}
      <div {{ container_id }} class=\"{{ options.input_class }} {{ flex_class }} field-container\">
         {{ field|raw }}
         {{ add_field_html|raw }}
      </div>
   </div>
{% endmacro %}


{% macro verticalField(label, field, id, add_field_html = \x27\x27, options = {}) %}
   {% set options = {
      \x27full_width\x27: false,
      \x27mb\x27: \x27mb-2\x27,
      \x27field_class\x27: \x27col-12 col-sm-6\x27,
      \x27add_field_class\x27: \x27\x27,
      \x27add_field_attribs\x27: {},
      \x27insert_content_after_label\x27: \x27\x27,
      \x27label_class\x27: \x27\x27,
      \x27input_class\x27: \x27\x27,
   }|merge(options) %}

   {% if options.full_width %}
      {% set options = options|merge({
         field_class: \x27col-12\x27,
      }) %}
   {% endif %}

   {% if options.add_field_attribs is not empty %}
      {% set extra_attribs = call(\x27Html::parseAttributes\x27, {options: options.add_field_attribs}) %}
   {% else %}
      {% set extra_attribs = \x27\x27 %}
   {% endif %}

   <div class=\"form-field {{ options.field_class }} {{ options.add_field_class }} {{ options.mb }}\" {{ extra_attribs|raw }} {% if options.name is defined and options.name is not empty %}data-testid=\"form-field-{{ options.name }}\"{% endif %}>
      {% import \x27components/form/basic_inputs_macros.html.twig\x27 as _inputs %}
      <div class=\"d-flex align-items-center\">
         {{ _inputs.label(label, id, options, \x27col-form-label \x27 ~ options.label_class) }}
         {{ options.insert_content_after_label|raw }}
      </div>
      <div class=\"{{ options.input_class }} field-container\">
         {{ field|raw }}
      </div>
      {{ add_field_html|raw }}
   </div>
{% endmacro %}

{% macro label(label, id, options = {}, class = \x27form-label\x27) %}
    {% import \x27components/form/basic_inputs_macros.html.twig\x27 as _inputs %}
    {{ _inputs.label(label, id, options, class) }}
{% endmacro %}

{% macro codeField(name, value, label, options) %}
    {% set options = {
        single_line: false,
        language: \x27twig\x27,
        completions: [],
        helper: __(\x27This field accepts %s content. Press Ctrl+Space to trigger autocompletion.\x27)|format(\x27Twig\x27),
    }|merge(options) %}

    {% if options.helper is not empty %}
        {% set options = options|merge({
            helper: options.helper|format(options.language)
        }) %}
    {% endif %}

    {% set code_container_id = name ~ \x27_\x27 ~ random() %}
    {% set code_container %}
        <div id=\"{{ code_container_id }}\" class=\"form-control overflow-hidden text-start\" style=\"height: {{ options.single_line ? \x2736px\x27 : options.height|default(\x27auto\x27) }};\"></div>
    {% endset %}
    {{ _self.htmlField(name, code_container, label, {
        wrapper_class: \x27d-flex flex-grow-1\x27,
    }|merge(options)) }}
    <script>
        \$(() => {
            const editor_options = {{ options.single_line ? \x27true\x27 : \x27false\x27 }} ? window.GLPI.Monaco.getSingleLineEditorOptions() : {};
            window.GLPI.Monaco.createEditor(\x27{{ code_container_id|e(\x27js\x27) }}\x27, \x27{{ options.language|e(\x27js\x27) }}\x27, \"{{ value|e(\x27js\x27) }}\", {{ options.completions|json_encode|raw }}, editor_options).then(() => {
                \$(\x27#{{ code_container_id|e(\x27css\x27)|e(\x27js\x27) }}\x27).closest(\x27form\x27).on(\x27formdata\x27, (e) => {
                    const editors = window.monaco.editor.getEditors().filter((editor) => {
                        return editor._domElement.id === \x27{{ code_container_id|e(\x27js\x27) }}\x27;
                    });
                    if (editors.length) {
                        e.originalEvent.formData.delete(\x27{{ name|e(\x27js\x27) }}\x27);
                        e.originalEvent.formData.append(\x27{{ name|e(\x27js\x27) }}\x27, editors[0].getValue());
                    }
                });
            });
        });
    </script>
{% endmacro %}

{% macro illustrationField(name, value, label = \x27\x27, options = {}) %}
    {% set options = {
        extra_css_classes: \"\",
        backdrop: true,
    }|merge(options) %}
    {% set custom_icon_prefix = constant(
        \x27Glpi\\\\UI\\\\IllustrationManager::CUSTOM_ILLUSTRATION_PREFIX\x27
    ) %}
    {% set field %}
        {% set container_id = \"container-\" ~ random() %}

        <div id=\"{{ container_id }}\">
            <input
                name=\"{{ name }}\"
                type=\"hidden\"
                value=\"{{ value }}\"
                data-glpi-icon-picker-value
            >

            {# Display the illustration, trigger the modal on click #}
            {% set modal_id = \"illustration-modal-\" ~ random() %}
            <div
                class=\"illustration-selector d-flex align-items-center card border-1 {{ options.extra_css_classes }}\"
                role=\"button\"
                aria-label=\"{{ __(\"Select an illustration\") }}\"
                data-bs-toggle=\"modal\"
                data-bs-target=\"#{{ modal_id }}\"
                data-glpi-icon-picker-value-preview
            >
                <div class=\"card-body aspect-ratio-1\">
                    {% set is_custom_file = value starts with custom_icon_prefix %}
                    <div
                        {% if is_custom_file %}
                            data-glpi-icon-picker-value-preview-custom
                            data-testid=\"illustration-custom-preview\"
                        {% else %}
                            data-glpi-icon-picker-value-preview-native
                        {% endif %}
                    >
                        {{ render_illustration(value, 100) }}
                    </div>

                    {# Keep the other format (native/custom) that wasn\x27t used
                    in a dedicated div that we can show later if the format
                    is changed #}
                    {% if is_custom_file %}
                        <div
                            class=\"d-none\"
                            data-glpi-icon-picker-value-preview-native
                        >
                           {{ render_illustration(\x27\x27, 100) }}
                        </div>
                    {% else %}
                        <div
                            class=\"d-none\"
                            data-glpi-icon-picker-value-preview-custom
                            data-testid=\"illustration-custom-preview\"
                        >
                           {{ render_illustration(custom_icon_prefix, 100) }}
                        </div>
                    {% endif %}
                </div>
            </div>

            {# Render the modal content #}
            {{ include(\x27components/illustration/icon_picker_modal.html.twig\x27, {
                \x27id\x27: modal_id,
                \x27backdrop\x27: options.backdrop,
            }, with_context: false) }}

            {# Start js controller #}
            <script defer type=\"module\">
                (async () => {
                    const module = await import(
                        \"/js/modules/IllustrationPicker/Controller.js\"
                    );
                    new module.GlpiIllustrationPickerController(
                        document.getElementById(\x27{{ container_id|e(\x27js\x27) }}\x27),
                        document.getElementById(\x27{{ modal_id|e(\x27js\x27) }}\x27),
                        \"{{ custom_icon_prefix|e(\x27js\x27) }}\",
                    );
                })();
            </script>
        </div>
    {% endset %}

    {% set options = {
        \x27id\x27: \x27%id%\x27,
    }|merge(options) %}
    {{ _self.field(name, field, label, options) }}
{% endmacro %}
", "components/form/fields_macros.html.twig", "/var/www/html/glpi/templates/components/form/fields_macros.html.twig");
    }
}
