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

/* components/form/basic_inputs_macros.html.twig */
class __TwigTemplate_47067db859132235cda594fa94668b1f extends Template
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
        // line 123
        yield "

";
        // line 138
        yield "

";
        // line 167
        yield "

";
        // line 197
        yield "

";
        // line 202
        yield "

";
        // line 207
        yield "

";
        // line 224
        yield "

";
        // line 229
        yield "

";
        // line 343
        yield "

";
        // line 350
        yield "

";
        // line 453
        yield "

";
        // line 477
        yield "

";
        // line 500
        yield "

";
        // line 505
        yield "
";
        yield from [];
    }

    // line 33
    public function macro_input($name = null, $value = null, $options = [], ...$varargs): string|Markup
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
            // line 34
            yield "    ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["id" => null, "type" => "text", "input_addclass" => "", "additional_attributes" => [], "readonly" => false, "disabled" => false, "multiple" => false, "required" => false, "maxlength" => null, "is_disclosable" => false, "is_copyable" => false, "clearable" => false, "with_class" => true],             // line 48
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 48, $this->source); })()));
            // line 49
            yield "
    ";
            // line 50
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 50), "isMandatoryField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 50, $this->source); })())], "method", true, true, false, 50)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 50, $this->source); })()), "fields_template", [], "any", false, false, false, 50), "isMandatoryField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 50, $this->source); })())], "method", false, false, false, 50), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 51
                yield "        ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 51, $this->source); })()), ["required" => true]);
                // line 52
                yield "    ";
            }
            // line 53
            yield "
    ";
            // line 54
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 54), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 54, $this->source); })())], "method", true, true, false, 54)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 54, $this->source); })()), "fields_template", [], "any", false, false, false, 54), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 54, $this->source); })())], "method", false, false, false, 54), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 55
                yield "        ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 55, $this->source); })()), ["readonly" => true]);
                // line 56
                yield "    ";
            }
            // line 57
            yield "
    ";
            // line 58
            $context["has_addons"] = (CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 58, $this->source); })()), "is_disclosable", [], "any", false, false, false, 58) || CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 58, $this->source); })()), "is_copyable", [], "any", false, false, false, 58));
            // line 59
            yield "
    ";
            // line 60
            if (((isset($context["has_addons"]) || array_key_exists("has_addons", $context) ? $context["has_addons"] : (function () { throw new RuntimeError('Variable "has_addons" does not exist.', 60, $this->source); })()) && (null === CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 60, $this->source); })()), "id", [], "any", false, false, false, 60)))) {
                // line 61
                yield "        ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 61, $this->source); })()), ["id" => ((Html::sanitizeDomId(                // line 62
(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 62, $this->source); })())) . "_") . Twig\Extension\CoreExtension::random($this->env->getCharset()))]);
                // line 64
                yield "    ";
            }
            // line 65
            yield "
    ";
            // line 66
            $context["input"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 67
                yield "        <input type=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 67, $this->source); })()), "type", [], "any", false, false, false, 67), "html", null, true);
                yield "\" ";
                yield (((CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 67, $this->source); })()), "id", [], "any", false, false, false, 67) != null)) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(("id=" . CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 67, $this->source); })()), "id", [], "any", false, false, false, 67)), "html", null, true)) : (""));
                yield "
        ";
                // line 68
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 68, $this->source); })()), "with_class", [], "any", false, false, false, 68)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 69
                    yield "               class=\"form-control ";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 69, $this->source); })()), "input_addclass", [], "any", false, false, false, 69), "html", null, true);
                    yield " ";
                    yield (((($tmp = (isset($context["has_addons"]) || array_key_exists("has_addons", $context) ? $context["has_addons"] : (function () { throw new RuntimeError('Variable "has_addons" does not exist.', 69, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("rounded-end-0") : (""));
                    yield "\"
        ";
                }
                // line 71
                yield "               name=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 71, $this->source); })()), "html", null, true);
                yield "\" value=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 71, $this->source); })()), "html", null, true);
                yield "\"
            ";
                // line 72
                $context['_parent'] = $context;
                $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 72, $this->source); })()), "additional_attributes", [], "any", false, false, false, 72));
                foreach ($context['_seq'] as $context["attr"] => $context["value"]) {
                    // line 73
                    yield "               ";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["attr"], "html", null, true);
                    yield "=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["value"], "html", null, true);
                    yield "\"
            ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['attr'], $context['value'], $context['_parent']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 75
                yield "               ";
                yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 75, $this->source); })()), "maxlength", [], "any", false, false, false, 75)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(("maxlength=" . CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 75, $this->source); })()), "maxlength", [], "any", false, false, false, 75)), "html", null, true)) : (""));
                yield "
               ";
                // line 76
                yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 76, $this->source); })()), "readonly", [], "any", false, false, false, 76)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("readonly") : (""));
                yield "
               ";
                // line 77
                yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 77, $this->source); })()), "disabled", [], "any", false, false, false, 77)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("disabled") : (""));
                yield "
               ";
                // line 78
                yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 78, $this->source); })()), "multiple", [], "any", false, false, false, 78)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("multiple") : (""));
                yield " ";
                // line 79
                yield "               ";
                yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 79, $this->source); })()), "required", [], "any", false, false, false, 79)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("required") : (""));
                yield "
               ";
                // line 80
                if (CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "pattern", [], "any", true, true, false, 80)) {
                    yield "pattern=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 80, $this->source); })()), "pattern", [], "any", false, false, false, 80), "html", null, true);
                    yield "\"";
                }
                // line 81
                yield "               ";
                if (CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "min", [], "any", true, true, false, 81)) {
                    yield "min=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 81, $this->source); })()), "min", [], "any", false, false, false, 81), "html", null, true);
                    yield "\"";
                }
                // line 82
                yield "               ";
                if (CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "max", [], "any", true, true, false, 82)) {
                    yield "max=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 82, $this->source); })()), "max", [], "any", false, false, false, 82), "html", null, true);
                    yield "\"";
                }
                // line 83
                yield "               ";
                if (CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "step", [], "any", true, true, false, 83)) {
                    yield "step=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 83, $this->source); })()), "step", [], "any", false, false, false, 83), "html", null, true);
                    yield "\"";
                }
                yield " />
    ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 85
            yield "
    ";
            // line 86
            $context["more_html"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 87
                yield "        ";
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 87, $this->source); })()), "is_disclosable", [], "any", false, false, false, 87)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 88
                    yield "            <button type=\"button\" class=\"btn btn-outline-secondary\"
                 onmousedown=\"showDisclosablePasswordField(\x27";
                    // line 89
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 89, $this->source); })()), "id", [], "any", false, false, false, 89), "js"), "html", null, true);
                    yield "\x27)\"
                 onmouseup=\"hideDisclosablePasswordField(\x27";
                    // line 90
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 90, $this->source); })()), "id", [], "any", false, false, false, 90), "js"), "html", null, true);
                    yield "\x27)\"
                 onmouseout=\"hideDisclosablePasswordField(\x27";
                    // line 91
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 91, $this->source); })()), "id", [], "any", false, false, false, 91), "js"), "html", null, true);
                    yield "\x27)\">
                <i class=\"ti ti-eye disclose\"></i>
            </button>
        ";
                }
                // line 95
                yield "
        ";
                // line 96
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 96, $this->source); })()), "is_copyable", [], "any", false, false, false, 96)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 97
                    yield "            <button type=\"button\" class=\"btn btn-outline-secondary\" onclick=\"copyDisclosablePasswordFieldToClipboard(\x27";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 97, $this->source); })()), "id", [], "any", false, false, false, 97), "js"), "html", null, true);
                    yield "\x27)\">
                <i class=\"ti ti-clipboard-copy disclose\"></i>
            </button>
        ";
                }
                // line 101
                yield "    ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 102
            yield "
    ";
            // line 103
            if ((($tmp = (isset($context["has_addons"]) || array_key_exists("has_addons", $context) ? $context["has_addons"] : (function () { throw new RuntimeError('Variable "has_addons" does not exist.', 103, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 104
                yield "        ";
                $context["input"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                    // line 105
                    yield "            <div class=\"btn-group btn-group-sm d-flex\">
                ";
                    // line 106
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["input"]) || array_key_exists("input", $context) ? $context["input"] : (function () { throw new RuntimeError('Variable "input" does not exist.', 106, $this->source); })()), "html", null, true);
                    yield "
                ";
                    // line 107
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["more_html"]) || array_key_exists("more_html", $context) ? $context["more_html"] : (function () { throw new RuntimeError('Variable "more_html" does not exist.', 107, $this->source); })()), "html", null, true);
                    yield "
            </div>
        ";
                    yield from [];
                })())) ? '' : new Markup($tmp, $this->env->getCharset());
                // line 110
                yield "    ";
            }
            // line 111
            yield "
    ";
            // line 112
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["input"]) || array_key_exists("input", $context) ? $context["input"] : (function () { throw new RuntimeError('Variable "input" does not exist.', 112, $this->source); })()), "html", null, true);
            yield "

    ";
            // line 114
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 114, $this->source); })()), "clearable", [], "any", false, false, false, 114)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 115
                yield "        <div class=\"d-flex align-items-center gap-1 mt-1\">
            <input type=\"checkbox\" name=\"_blank_";
                // line 116
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 116, $this->source); })()), "html", null, true);
                yield "\" id=\"_blank_";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 116, $this->source); })()), "html", null, true);
                yield "\" class=\"form-check-input\">
            <label for=\"_blank_";
                // line 117
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 117, $this->source); })()), "html", null, true);
                yield "\" class=\"form-check-label\">
                ";
                // line 118
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Clear"), "html", null, true);
                yield "
            </label>
        </div>
    ";
            }
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 125
    public function macro_text($name = null, $value = null, $options = [], ...$varargs): string|Markup
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
            // line 126
            yield "    ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["copyable" => false],             // line 128
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 128, $this->source); })()));
            // line 129
            yield "
    ";
            // line 130
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 130, $this->source); })()), "copyable", [], "any", false, false, false, 130)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 131
                yield "        <div class=\"copy_to_clipboard_wrapper\">
    ";
            }
            // line 133
            yield "    ";
            yield $this->getTemplateForMacro("macro_input", $context, 133, $this->getSourceContext())->macro_input(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 133, $this->source); })()), (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 133, $this->source); })()), Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 133, $this->source); })()), ["type" => "text"])]);
            yield "
    ";
            // line 134
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 134, $this->source); })()), "copyable", [], "any", false, false, false, 134)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 135
                yield "        </div>
    ";
            }
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 140
    public function macro_number($name = null, $value = null, $options = [], ...$varargs): string|Markup
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
            // line 141
            yield "    ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["step" => 1],             // line 143
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 143, $this->source); })()));
            // line 144
            yield "
    ";
            // line 145
            if ((( !CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "decimals", [], "any", true, true, false, 145) &&  !((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "readonly", [], "any", true, true, false, 145)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 145, $this->source); })()), "readonly", [], "any", false, false, false, 145), false)) : (false))) &&  !((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "disabled", [], "any", true, true, false, 145)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 145, $this->source); })()), "disabled", [], "any", false, false, false, 145), false)) : (false)))) {
                // line 146
                yield "        ";
                // line 147
                yield "        ";
                $context["decimals_part"] = Twig\Extension\CoreExtension::split($this->env->getCharset(), CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 147, $this->source); })()), "step", [], "any", false, false, false, 147), ".");
                // line 148
                yield "        ";
                $context["decimals"] = ((CoreExtension::getAttribute($this->env, $this->source, ($context["decimals_part"] ?? null), 1, [], "array", true, true, false, 148)) ? (Twig\Extension\CoreExtension::length($this->env->getCharset(), CoreExtension::getAttribute($this->env, $this->source, (isset($context["decimals_part"]) || array_key_exists("decimals_part", $context) ? $context["decimals_part"] : (function () { throw new RuntimeError('Variable "decimals_part" does not exist.', 148, $this->source); })()), 1, [], "array", false, false, false, 148))) : (0));
                // line 149
                yield "        ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 149, $this->source); })()), ["decimals" => (isset($context["decimals"]) || array_key_exists("decimals", $context) ? $context["decimals"] : (function () { throw new RuntimeError('Variable "decimals" does not exist.', 149, $this->source); })())]);
                // line 150
                yield "    ";
            }
            // line 151
            yield "
    ";
            // line 152
            if (((isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 152, $this->source); })()) == "")) {
                // line 153
                yield "        ";
                $context["value"] = ((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "min", [], "any", true, true, false, 153)) ? (CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 153, $this->source); })()), "min", [], "any", false, false, false, 153)) : (0));
                // line 154
                yield "    ";
            }
            // line 155
            yield "
    ";
            // line 156
            if (((CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 156, $this->source); })()), "step", [], "any", false, false, false, 156) != "any") && (Twig\Extension\CoreExtension::round(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 156, $this->source); })()), "step", [], "any", false, false, false, 156), 0, "floor") != CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 156, $this->source); })()), "step", [], "any", false, false, false, 156)))) {
                // line 157
                yield "        ";
                if (CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "decimals", [], "any", true, true, false, 157)) {
                    // line 158
                    yield "            ";
                    $context["value"] = $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Html::formatNumber", [(isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 158, $this->source); })()), true, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 158, $this->source); })()), "decimals", [], "any", false, false, false, 158)]);
                    // line 159
                    yield "        ";
                } else {
                    // line 160
                    yield "            ";
                    // line 161
                    yield "            ";
                    $context["value"] = $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Html::formatNumber", [(isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 161, $this->source); })()), true]);
                    // line 162
                    yield "        ";
                }
                // line 163
                yield "    ";
            }
            // line 164
            yield "
    ";
            // line 165
            yield $this->getTemplateForMacro("macro_input", $context, 165, $this->getSourceContext())->macro_input(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 165, $this->source); })()), (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 165, $this->source); })()), Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 165, $this->source); })()), ["type" => "number"])]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 169
    public function macro_color($name = null, $value = null, $options = [], ...$varargs): string|Markup
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
            // line 170
            yield "    ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["id" => ((            // line 171
(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 171, $this->source); })()) . "_") . (((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "rand", [], "any", true, true, false, 171) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 171, $this->source); })()), "rand", [], "any", false, false, false, 171)))) ? (CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 171, $this->source); })()), "rand", [], "any", false, false, false, 171)) : (Twig\Extension\CoreExtension::random($this->env->getCharset()))))],             // line 172
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 172, $this->source); })()));
            // line 173
            yield "
    ";
            // line 174
            yield $this->getTemplateForMacro("macro_input", $context, 174, $this->getSourceContext())->macro_input(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 174, $this->source); })()), (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 174, $this->source); })()), Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 174, $this->source); })()), ["type" => "text", "input_addclass" => "rounded-0"])]);
            // line 177
            yield "
    <script>
        \$(function () {
            \$(\"#";
            // line 180
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 180, $this->source); })()), "id", [], "any", false, false, false, 180), "css"), "js"), "html", null, true);
            yield "\").spectrum({
                showInput: true,
                preferredFormat: \"hex\",
                type: \"text\",
                cancelText: \"";
            // line 184
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Cancel"), "js"), "html", null, true);
            yield "\",
                chooseText: \"";
            // line 185
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Validate"), "js"), "html", null, true);
            yield "\",
                change: function (color) {
                    if (color !== null && color.getAlpha() !== 1) {
                        let hex = color.toHexString();
                        hex += (\"0\" + Math.round(parseFloat(color.getAlpha()) * 255).toString(16)).slice(-2);
                        this.value = hex;
                    }
                }
            });
        });
    </script>
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 199
    public function macro_password($name = null, $value = null, $options = [], ...$varargs): string|Markup
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
            // line 200
            yield "    ";
            yield $this->getTemplateForMacro("macro_input", $context, 200, $this->getSourceContext())->macro_input(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 200, $this->source); })()), (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 200, $this->source); })()), Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 200, $this->source); })()), ["type" => "password"])]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 204
    public function macro_email($name = null, $value = null, $options = [], ...$varargs): string|Markup
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
            // line 205
            yield "    ";
            yield $this->getTemplateForMacro("macro_input", $context, 205, $this->getSourceContext())->macro_input(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 205, $this->source); })()), (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 205, $this->source); })()), Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 205, $this->source); })()), ["type" => "email"])]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 209
    public function macro_file($name = null, $value = null, $options = [], ...$varargs): string|Markup
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
            // line 210
            yield "    ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["simple" => false],             // line 212
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 212, $this->source); })()));
            // line 213
            yield "
    ";
            // line 214
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 214, $this->source); })()), "simple", [], "any", false, false, false, 214)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 215
                yield "        ";
                yield $this->getTemplateForMacro("macro_input", $context, 215, $this->getSourceContext())->macro_input(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 215, $this->source); })()), (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 215, $this->source); })()), Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 215, $this->source); })()), ["type" => "file"])]);
                yield "
    ";
            } else {
                // line 217
                yield "        ";
                $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Html::file", [Twig\Extension\CoreExtension::merge(                // line 218
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 218, $this->source); })()), ["name" =>                 // line 219
(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 219, $this->source); })())])]);
                // line 222
                yield "    ";
            }
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 226
    public function macro_hidden($name = null, $value = null, $options = [], ...$varargs): string|Markup
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
            // line 227
            yield "    ";
            yield $this->getTemplateForMacro("macro_input", $context, 227, $this->getSourceContext())->macro_input(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 227, $this->source); })()), (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 227, $this->source); })()), Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 227, $this->source); })()), ["type" => "hidden", "with_class" => false])]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 231
    public function macro_date($name = null, $value = null, $options = [], ...$varargs): string|Markup
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
            // line 232
            yield "    ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["rand" => Twig\Extension\CoreExtension::random($this->env->getCharset()), "enableTime" => false, "noCalendar" => false, "checkIsExpired" => false, "clearable" => false, "container_addclass" => "", "input_addclass" => "", "readonly" => false, "disabled" => false, "maybeempty" => false],             // line 243
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 243, $this->source); })()));
            // line 244
            yield "
    ";
            // line 245
            $context["editable"] = ( !CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 245, $this->source); })()), "readonly", [], "any", false, false, false, 245) &&  !CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 245, $this->source); })()), "disabled", [], "any", false, false, false, 245));
            // line 246
            yield "
    ";
            // line 247
            $context["options"] = Twig\Extension\CoreExtension::merge(["id" => ((Html::sanitizeDomId(            // line 248
(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 248, $this->source); })())) . "_") . CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 248, $this->source); })()), "rand", [], "any", false, false, false, 248))],             // line 249
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 249, $this->source); })()));
            // line 250
            yield "
    ";
            // line 251
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 251), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 251, $this->source); })())], "method", true, true, false, 251)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 251, $this->source); })()), "fields_template", [], "any", false, false, false, 251), "isReadonlyField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 251, $this->source); })())], "method", false, false, false, 251), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 252
                yield "        ";
                $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 252, $this->source); })()), ["readonly" => true]);
                // line 253
                yield "    ";
            }
            // line 254
            yield "
    ";
            // line 255
            if (((isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 255, $this->source); })()) == "NULL")) {
                // line 256
                yield "      ";
                $context["value"] = null;
                // line 257
                yield "   ";
            }
            // line 258
            yield "
    ";
            // line 259
            $context["final_expiration_class"] = "";
            // line 260
            yield "    ";
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 260, $this->source); })()), "checkIsExpired", [], "any", false, false, false, 260)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 261
                yield "        ";
                if (($this->extensions['Twig\Extension\CoreExtension']->formatDate((isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 261, $this->source); })()), "Y-m-d H:i:s") < $this->extensions['Twig\Extension\CoreExtension']->formatDate("now", "Y-m-d H:i:s"))) {
                    // line 262
                    yield "            ";
                    $context["final_expiration_class"] = " warn";
                    // line 263
                    yield "        ";
                }
                // line 264
                yield "    ";
            } else {
                // line 265
                yield "        ";
                if (CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "expiration_class", [], "any", true, true, false, 265)) {
                    // line 266
                    yield "            ";
                    $context["final_expiration_class"] = (" " . CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 266, $this->source); })()), "expiration_class", [], "any", false, false, false, 266));
                    // line 267
                    yield "        ";
                } else {
                    // line 268
                    yield "            ";
                    $context["final_expiration_class"] = "";
                    // line 269
                    yield "        ";
                }
                // line 270
                yield "    ";
            }
            // line 271
            yield "
    <div
        class=\"btn-group flex-grow-1 flatpickr d-flex ";
            // line 273
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 273, $this->source); })()), "container_addclass", [], "any", false, false, false, 273), "html", null, true);
            yield "\"
        id=\"";
            // line 274
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 274, $this->source); })()), "id", [], "any", false, false, false, 274), "html", null, true);
            yield "\"
        data-bs-toggle=\"tooltip\"
        data-bs-placement=\"bottom\"
        title=\"";
            // line 277
            yield (((($tmp = (isset($context["editable"]) || array_key_exists("editable", $context) ? $context["editable"] : (function () { throw new RuntimeError('Variable "editable" does not exist.', 277, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Enter or select a date"), "html", null, true)) : ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(_n("Date", "Dates", 1), "html", null, true)));
            yield "\"
    >
        ";
            // line 279
            yield $this->getTemplateForMacro("macro_input", $context, 279, $this->getSourceContext())->macro_input(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 279, $this->source); })()), (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 279, $this->source); })()), Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 279, $this->source); })()), ["type" => "text", "id" => (CoreExtension::getAttribute($this->env, $this->source,             // line 281
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 281, $this->source); })()), "id", [], "any", false, false, false, 281) . "_input"), "additional_attributes" => Twig\Extension\CoreExtension::merge((((CoreExtension::getAttribute($this->env, $this->source,             // line 282
($context["options"] ?? null), "additional_attributes", [], "any", true, true, false, 282) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 282, $this->source); })()), "additional_attributes", [], "any", false, false, false, 282)))) ? (CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 282, $this->source); })()), "additional_attributes", [], "any", false, false, false, 282)) : ([])), ["data-input" => ""]), "input_addclass" => (CoreExtension::getAttribute($this->env, $this->source,             // line 283
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 283, $this->source); })()), "input_addclass", [], "any", false, false, false, 283) . (isset($context["final_expiration_class"]) || array_key_exists("final_expiration_class", $context) ? $context["final_expiration_class"] : (function () { throw new RuntimeError('Variable "final_expiration_class" does not exist.', 283, $this->source); })())), "clearable" => false])]);
            // line 285
            yield "

        ";
            // line 287
            if ((($tmp = (isset($context["editable"]) || array_key_exists("editable", $context) ? $context["editable"] : (function () { throw new RuntimeError('Variable "editable" does not exist.', 287, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 288
                yield "            ";
                $context["calendar_icon"] = (((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 288, $this->source); })()), "enableTime", [], "any", false, false, false, 288)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("ti ti-calendar-time") : ("ti ti-calendar"));
                // line 289
                yield "            <button type=\"button\" class=\"btn btn-outline-secondary btn-sm\" data-toggle>
                <i class=\"";
                // line 290
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["calendar_icon"]) || array_key_exists("calendar_icon", $context) ? $context["calendar_icon"] : (function () { throw new RuntimeError('Variable "calendar_icon" does not exist.', 290, $this->source); })()), "html", null, true);
                yield "\"></i>
                <span class=\"sr-only\">";
                // line 291
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Enter or select a date"), "html", null, true);
                yield "</span>
            </button>
            ";
                // line 294
                yield "            ";
                if ((CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 294, $this->source); })()), "clearable", [], "any", false, false, false, 294) || CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 294, $this->source); })()), "maybeempty", [], "any", false, false, false, 294))) {
                    // line 295
                    yield "                <button type=\"button\" class=\"btn btn-outline-secondary btn-sm\" data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\" data-clear title=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Clear"), "html", null, true);
                    yield "\">
                    <i class=\"ti ti-circle-x\"></i>
                </button>
            ";
                }
                // line 299
                yield "        ";
            }
            // line 300
            yield "    </div>

    ";
            // line 302
            if ((($tmp = (isset($context["editable"]) || array_key_exists("editable", $context) ? $context["editable"] : (function () { throw new RuntimeError('Variable "editable" does not exist.', 302, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 303
                yield "        ";
                $context["locale"] = $this->extensions['Glpi\Application\View\Extension\I18nExtension']->getCurrentLocale();
                // line 304
                yield "        ";
                if ((CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 304, $this->source); })()), "enableTime", [], "any", false, false, false, 304) &&  !CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 304, $this->source); })()), "noCalendar", [], "any", false, false, false, 304))) {
                    // line 305
                    yield "            ";
                    $context["date_format"] = "Y-m-d H:i:S";
                    // line 306
                    yield "            ";
                    $context["alt_format"] = ($this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Toolbox::getDateFormat", ["js"]) . (((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 306, $this->source); })()), "enableTime", [], "any", false, false, false, 306)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (" H:i:S") : ("")));
                    // line 307
                    yield "        ";
                } elseif ((CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 307, $this->source); })()), "enableTime", [], "any", false, false, false, 307) && CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 307, $this->source); })()), "noCalendar", [], "any", false, false, false, 307))) {
                    // line 308
                    yield "            ";
                    $context["date_format"] = "H:i:S";
                    // line 309
                    yield "            ";
                    $context["alt_format"] = (((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 309, $this->source); })()), "enableTime", [], "any", false, false, false, 309)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (" H:i:S") : (""));
                    // line 310
                    yield "        ";
                } elseif (( !CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 310, $this->source); })()), "enableTime", [], "any", false, false, false, 310) &&  !CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 310, $this->source); })()), "noCalendar", [], "any", false, false, false, 310))) {
                    // line 311
                    yield "            ";
                    $context["date_format"] = "Y-m-d";
                    // line 312
                    yield "            ";
                    $context["alt_format"] = $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Toolbox::getDateFormat", ["js"]);
                    // line 313
                    yield "        ";
                } else {
                    // line 314
                    yield "            ";
                    // line 315
                    yield "            ";
                    $context["date_format"] = "Y-m-d H:i:S";
                    // line 316
                    yield "        ";
                }
                // line 317
                yield "        <script>
            \$(function() {
                \$(\"#";
                // line 319
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 319, $this->source); })()), "id", [], "any", false, false, false, 319), "css"), "js"), "html", null, true);
                yield "\").flatpickr({
                    wrap: true,
                    altInput: true,
                    dateFormat: \x27";
                // line 322
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["date_format"]) || array_key_exists("date_format", $context) ? $context["date_format"] : (function () { throw new RuntimeError('Variable "date_format" does not exist.', 322, $this->source); })()), "js"), "html", null, true);
                yield "\x27,
                    altFormat: \x27";
                // line 323
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["alt_format"]) || array_key_exists("alt_format", $context) ? $context["alt_format"] : (function () { throw new RuntimeError('Variable "alt_format" does not exist.', 323, $this->source); })()), "js"), "html", null, true);
                yield "\x27,
                    enableTime: ";
                // line 324
                yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 324, $this->source); })()), "enableTime", [], "any", false, false, false, 324)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("true") : ("false"));
                yield ",
                    enableSeconds: ";
                // line 325
                yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 325, $this->source); })()), "enableTime", [], "any", false, false, false, 325)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("true") : ("false"));
                yield ",
                    noCalendar: ";
                // line 326
                yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 326, $this->source); })()), "noCalendar", [], "any", false, false, false, 326)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("true") : ("false"));
                yield ",
                    weekNumbers: true,
                    time_24hr: true,
                    allowInput: ";
                // line 329
                yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 329, $this->source); })()), "readonly", [], "any", false, false, false, 329)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("false") : ("true"));
                yield ",
                    clickOpens: ";
                // line 330
                yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 330, $this->source); })()), "readonly", [], "any", false, false, false, 330)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("false") : ("true"));
                yield ",
                    locale: getFlatPickerLocale(\"";
                // line 331
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["locale"]) || array_key_exists("locale", $context) ? $context["locale"] : (function () { throw new RuntimeError('Variable "locale" does not exist.', 331, $this->source); })()), "language", [], "array", false, false, false, 331), "js"), "html", null, true);
                yield "\", \"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["locale"]) || array_key_exists("locale", $context) ? $context["locale"] : (function () { throw new RuntimeError('Variable "locale" does not exist.', 331, $this->source); })()), "region", [], "array", false, false, false, 331), "js"), "html", null, true);
                yield "\"),
                    onClose(dates, currentdatestring, picker) {
                        picker.setDate(picker.altInput.value, true, picker.config.altFormat)
                    },
                    plugins: [
                        CustomFlatpickrButtons()
                    ]
                });
            });
        </script>
    ";
            }
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 345
    public function macro_datetime($name = null, $value = null, $options = [], ...$varargs): string|Markup
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
            // line 346
            yield "    ";
            yield $this->getTemplateForMacro("macro_date", $context, 346, $this->getSourceContext())->macro_date(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 346, $this->source); })()), (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 346, $this->source); })()), Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 346, $this->source); })()), ["enableTime" => true])]);
            // line 348
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 352
    public function macro_textarea($name = null, $value = null, $options = [], ...$varargs): string|Markup
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
            // line 353
            yield "    ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["id" => null, "rand" => Twig\Extension\CoreExtension::random($this->env->getCharset()), "rows" => 3, "enable_richtext" => false, "enable_images" => true, "mention_options" => ["enabled" => (CoreExtension::getAttribute($this->env, $this->source,             // line 360
($context["options"] ?? null), "enable_mentions", [], "any", true, true, false, 360) && CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 360, $this->source); })()), "enable_mentions", [], "any", false, false, false, 360)), "full" => true, "users" => []], "entities_id" => $this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpiactive_entity"), "readonly" => false, "disabled" => false, "required" => false, "add_body_classes" => [], "toolbar" => true, "toolbar_location" => "top", "init" => true, "init_on_demand" => false, "placeholder" => "", "enable_form_tags" => false, "form_tags_form_id" => null, "aria_label" => "", "statusbar" => true, "content_style" => "", "input_addclass" => "", "additional_attributes" => [], "plugins_to_remove" => []],             // line 382
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 382, $this->source); })()));
            // line 383
            yield "
    ";
            // line 384
            if ((($tmp = ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 384), "isMandatoryField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 384, $this->source); })())], "method", true, true, false, 384)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 384, $this->source); })()), "fields_template", [], "any", false, false, false, 384), "isMandatoryField", [(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 384, $this->source); })())], "method", false, false, false, 384), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 385
                yield "        ";
                $context["options"] = Twig\Extension\CoreExtension::merge(["required" => true], (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 385, $this->source); })()));
                // line 386
                yield "    ";
            }
            // line 387
            yield "    ";
            $context["options"] = Twig\Extension\CoreExtension::merge((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 387, $this->source); })()), ["id" => (((Twig\Extension\CoreExtension::length($this->env->getCharset(), CoreExtension::getAttribute($this->env, $this->source,             // line 388
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 388, $this->source); })()), "id", [], "any", false, false, false, 388)) > 0)) ? (CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 388, $this->source); })()), "id", [], "any", false, false, false, 388)) : ((((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 388, $this->source); })()) . "_") . CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 388, $this->source); })()), "rand", [], "any", false, false, false, 388))))]);
            // line 390
            yield "
    ";
            // line 392
            yield "    <textarea class=\"form-control ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 392, $this->source); })()), "input_addclass", [], "any", false, false, false, 392), "html", null, true);
            yield "\"
            id=\"";
            // line 393
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 393, $this->source); })()), "id", [], "any", false, false, false, 393), "html", null, true);
            yield "\" name=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 393, $this->source); })()), "html", null, true);
            yield "\" rows=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 393, $this->source); })()), "rows", [], "any", false, false, false, 393), "html", null, true);
            yield "\"
            style=\"width: 100%;\"
            ";
            // line 395
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 395, $this->source); })()), "additional_attributes", [], "any", false, false, false, 395));
            foreach ($context['_seq'] as $context["attr"] => $context["value"]) {
                // line 396
                yield "               ";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["attr"], "html", null, true);
                yield "=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["value"], "html", null, true);
                yield "\"
            ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['attr'], $context['value'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 398
            yield "            ";
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 398, $this->source); })()), "aria_label", [], "any", false, false, false, 398))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 399
                yield "                aria-label=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 399, $this->source); })()), "aria_label", [], "any", false, false, false, 399), "html", null, true);
                yield "\"
            ";
            }
            // line 401
            yield "            ";
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 401, $this->source); })()), "placeholder", [], "any", false, false, false, 401))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 402
                yield "                placeholder=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 402, $this->source); })()), "placeholder", [], "any", false, false, false, 402), "html", null, true);
                yield "\"
            ";
            }
            // line 404
            yield "            ";
            yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 404, $this->source); })()), "disabled", [], "any", false, false, false, 404)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("disabled") : (""));
            yield "
            ";
            // line 405
            yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 405, $this->source); })()), "readonly", [], "any", false, false, false, 405)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("readonly") : (""));
            yield "
            ";
            // line 406
            yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 406, $this->source); })()), "required", [], "any", false, false, false, 406)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("required") : (""));
            yield ">";
            yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 406, $this->source); })()), "enable_richtext", [], "any", false, false, false, 406)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getSafeHtml((isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 406, $this->source); })())))) : ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 406, $this->source); })()), "html", null, true)));
            yield "</textarea>

    ";
            // line 408
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 408, $this->source); })()), "enable_richtext", [], "any", false, false, false, 408)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 409
                yield "        ";
                $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Html::initEditorSystem", [CoreExtension::getAttribute($this->env, $this->source,                 // line 410
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 410, $this->source); })()), "id", [], "any", false, false, false, 410), CoreExtension::getAttribute($this->env, $this->source,                 // line 411
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 411, $this->source); })()), "rand", [], "any", false, false, false, 411), true, ((CoreExtension::getAttribute($this->env, $this->source,                 // line 413
($context["options"] ?? null), "disabled", [], "any", true, true, false, 413)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 413, $this->source); })()), "disabled", [], "any", false, false, false, 413), false)) : (false)), CoreExtension::getAttribute($this->env, $this->source,                 // line 414
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 414, $this->source); })()), "enable_images", [], "any", false, false, false, 414), ((CoreExtension::getAttribute($this->env, $this->source,                 // line 415
($context["options"] ?? null), "editor_height", [], "any", true, true, false, 415)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 415, $this->source); })()), "editor_height", [], "any", false, false, false, 415), 150)) : (150)), CoreExtension::getAttribute($this->env, $this->source,                 // line 416
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 416, $this->source); })()), "add_body_classes", [], "any", false, false, false, 416), CoreExtension::getAttribute($this->env, $this->source,                 // line 417
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 417, $this->source); })()), "toolbar_location", [], "any", false, false, false, 417), CoreExtension::getAttribute($this->env, $this->source,                 // line 418
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 418, $this->source); })()), "init", [], "any", false, false, false, 418), CoreExtension::getAttribute($this->env, $this->source,                 // line 419
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 419, $this->source); })()), "placeholder", [], "any", false, false, false, 419), CoreExtension::getAttribute($this->env, $this->source,                 // line 420
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 420, $this->source); })()), "toolbar", [], "any", false, false, false, 420), CoreExtension::getAttribute($this->env, $this->source,                 // line 421
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 421, $this->source); })()), "statusbar", [], "any", false, false, false, 421), CoreExtension::getAttribute($this->env, $this->source,                 // line 422
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 422, $this->source); })()), "content_style", [], "any", false, false, false, 422), CoreExtension::getAttribute($this->env, $this->source,                 // line 423
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 423, $this->source); })()), "init_on_demand", [], "any", false, false, false, 423), CoreExtension::getAttribute($this->env, $this->source,                 // line 424
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 424, $this->source); })()), "plugins_to_remove", [], "any", false, false, false, 424)]);
                // line 426
                yield "   ";
            }
            // line 427
            yield "   ";
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 427, $this->source); })()), "enable_form_tags", [], "any", false, false, false, 427)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 428
                yield "        <script>
            \$(function() {
                const form_tags = new GLPI.RichText.FormTags(
                    tinymce.get(\x27";
                // line 431
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 431, $this->source); })()), "id", [], "any", false, false, false, 431), "js"), "html", null, true);
                yield "\x27),
                    ";
                // line 432
                yield json_encode(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 432, $this->source); })()), "form_tags_form_id", [], "any", false, false, false, 432));
                yield ",
                );
                form_tags.register();
            });
        </script>
    ";
            }
            // line 438
            yield "
    ";
            // line 439
            if ((((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "mention_options", [], "any", false, true, false, 439), "enabled", [], "any", true, true, false, 439)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 439, $this->source); })()), "mention_options", [], "any", false, false, false, 439), "enabled", [], "any", false, false, false, 439), false)) : (false)) && $this->extensions['Glpi\Application\View\Extension\ConfigExtension']->config("use_notifications"))) {
                // line 440
                yield "        <script>
            \$(function() {
                const user_mention = new GLPI.RichText.UserMention(
                    tinymce.get(\x27";
                // line 443
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 443, $this->source); })()), "id", [], "any", false, false, false, 443), "js"), "html", null, true);
                yield "\x27),
                    ";
                // line 444
                yield json_encode(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 444, $this->source); })()), "entities_id", [], "any", false, false, false, 444));
                yield ",
                    \x27";
                // line 445
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Session::getNewIDORToken("User", ["right" => "all", "entity_restrict" => json_encode(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 445, $this->source); })()), "entities_id", [], "any", false, false, false, 445))]), "html", null, true);
                yield "\x27,
                    ";
                // line 446
                yield json_encode(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 446, $this->source); })()), "mention_options", [], "any", false, false, false, 446));
                yield "
                );
                user_mention.register();
            });
        </script>
    ";
            }
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 455
    public function macro_checkbox($name = null, $value = null, $options = [], ...$varargs): string|Markup
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
            // line 456
            yield "    ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["id" => null, "input_addclass" => "", "readonly" => false, "disabled" => false, "required" => false, "additional_attributes" => []],             // line 463
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 463, $this->source); })()));
            // line 464
            yield "
    <input type=\"hidden\"   name=\"";
            // line 465
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 465, $this->source); })()), "html", null, true);
            yield "\" value=\"0\" />
    <input type=\"checkbox\" name=\"";
            // line 466
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 466, $this->source); })()), "html", null, true);
            yield "\" value=\"1\"
           class=\"form-check-input ";
            // line 467
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 467, $this->source); })()), "input_addclass", [], "any", false, false, false, 467), "html", null, true);
            yield "\"
           ";
            // line 468
            yield (((CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 468, $this->source); })()), "id", [], "any", false, false, false, 468) != null)) ? ((("id=\"" . $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 468, $this->source); })()), "id", [], "any", false, false, false, 468))) . "\"")) : (""));
            yield "
           ";
            // line 469
            yield ((((isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 469, $this->source); })()) == 1)) ? ("checked") : (""));
            yield "
           ";
            // line 470
            yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 470, $this->source); })()), "readonly", [], "any", false, false, false, 470)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("readonly") : (""));
            yield "
           ";
            // line 471
            yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 471, $this->source); })()), "required", [], "any", false, false, false, 471)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("required") : (""));
            yield "
           ";
            // line 472
            yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 472, $this->source); })()), "disabled", [], "any", false, false, false, 472)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("disabled") : (""));
            yield "
            ";
            // line 473
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 473, $this->source); })()), "additional_attributes", [], "any", false, false, false, 473));
            foreach ($context['_seq'] as $context["attr"] => $context["value"]) {
                // line 474
                yield "                ";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["attr"], "html", null, true);
                yield "=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["value"], "html", null, true);
                yield "\"
            ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['attr'], $context['value'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 475
            yield "/>
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 479
    public function macro_button($name = null, $label = "", $type = "button", $value = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "label" => $label,
            "type" => $type,
            "value" => $value,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 480
            yield "    ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["type" => "submit", "class" => "btn btn-primary", "icon" => "", "icon_title" => "", "additional_attributes" => []],             // line 486
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 486, $this->source); })()));
            // line 487
            yield "
    <button class=\"";
            // line 488
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 488, $this->source); })()), "class", [], "any", false, false, false, 488), "html", null, true);
            yield "\" type=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["type"]) || array_key_exists("type", $context) ? $context["type"] : (function () { throw new RuntimeError('Variable "type" does not exist.', 488, $this->source); })()), "html", null, true);
            yield "\" name=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 488, $this->source); })()), "html", null, true);
            yield "\" value=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 488, $this->source); })()), "html", null, true);
            yield "\"
        ";
            // line 489
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 489, $this->source); })()), "additional_attributes", [], "any", false, false, false, 489));
            foreach ($context['_seq'] as $context["attr"] => $context["value"]) {
                // line 490
                yield "            ";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["attr"], "html", null, true);
                yield "=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["value"], "html", null, true);
                yield "\"
        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['attr'], $context['value'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 491
            yield ">
        ";
            // line 492
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 492, $this->source); })()), "icon", [], "any", false, false, false, 492))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 493
                yield "            <i class=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 493, $this->source); })()), "icon", [], "any", false, false, false, 493), "html", null, true);
                yield "\" title=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 493, $this->source); })()), "icon_title", [], "any", false, false, false, 493), "html", null, true);
                yield "\"></i>
        ";
            }
            // line 495
            yield "        ";
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty((isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 495, $this->source); })()))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 496
                yield "            <span>";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 496, $this->source); })()), "html", null, true);
                yield "</span>
        ";
            }
            // line 498
            yield "    </button>
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 502
    public function macro_submit($name = null, $label = "", $value = "", $options = [], ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "name" => $name,
            "label" => $label,
            "value" => $value,
            "options" => $options,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 503
            yield "    ";
            yield $this->getTemplateForMacro("macro_button", $context, 503, $this->getSourceContext())->macro_button(...[(isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 503, $this->source); })()), (isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 503, $this->source); })()), "submit", (isset($context["value"]) || array_key_exists("value", $context) ? $context["value"] : (function () { throw new RuntimeError('Variable "value" does not exist.', 503, $this->source); })()), (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 503, $this->source); })())]);
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    // line 506
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
            // line 507
            yield "    ";
            $context["options"] = Twig\Extension\CoreExtension::merge(["locked" => false, "locked_value" => null, "tpl_mark" => null, "helper" => false],             // line 512
(isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 512, $this->source); })()));
            // line 513
            yield "
    ";
            // line 514
            $context["required_mark"] = "";
            // line 515
            yield "    ";
            if (((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "name", [], "any", true, true, false, 515) && ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "fields_template", [], "any", false, true, false, 515), "isMandatoryField", [CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 515, $this->source); })()), "name", [], "any", false, false, false, 515)], "method", true, true, false, 515)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 515, $this->source); })()), "fields_template", [], "any", false, false, false, 515), "isMandatoryField", [CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 515, $this->source); })()), "name", [], "any", false, false, false, 515)], "method", false, false, false, 515), false)) : (false))) || (((CoreExtension::getAttribute($this->env, $this->source, ($context["options"] ?? null), "required", [], "any", true, true, false, 515) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 515, $this->source); })()), "required", [], "any", false, false, false, 515)))) ? (CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 515, $this->source); })()), "required", [], "any", false, false, false, 515)) : (false)))) {
                // line 516
                yield "        ";
                $context["required_mark"] = "<span class=\"required\">*</span>";
                // line 517
                yield "    ";
            }
            // line 518
            yield "
    ";
            // line 519
            $context["helper"] = "";
            // line 520
            yield "    ";
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 520, $this->source); })()), "helper", [], "any", false, false, false, 520)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 521
                yield "        ";
                // line 522
                yield "        ";
                // line 523
                yield "        ";
                $context["helper_safe_text"] = Twig\Extension\CoreExtension::nl2br($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 523, $this->source); })()), "helper", [], "any", false, false, false, 523)));
                // line 524
                yield "        ";
                $context["helper"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                    // line 525
                    yield "        <span class=\"form-help\"
              data-bs-toggle=\"tooltip\"
              data-bs-placement=\"top\"
              data-bs-html=\"true\"
              data-bs-title=\"";
                    // line 529
                    yield (isset($context["helper_safe_text"]) || array_key_exists("helper_safe_text", $context) ? $context["helper_safe_text"] : (function () { throw new RuntimeError('Variable "helper_safe_text" does not exist.', 529, $this->source); })());
                    yield "\">
            ?
        </span>
        ";
                    yield from [];
                })())) ? '' : new Markup($tmp, $this->env->getCharset());
                // line 533
                yield "    ";
            }
            // line 534
            yield "
    ";
            // line 535
            $context["locked_mark"] = "";
            // line 536
            yield "    ";
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 536, $this->source); })()), "locked", [], "any", false, false, false, 536)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 537
                yield "        ";
                $context["locked_mark"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                    // line 538
                    yield "        ";
                    $context["locked_title"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Field will not be updated from inventory"), "html", null, true);
                        yield from [];
                    })())) ? '' : new Markup($tmp, $this->env->getCharset());
                    // line 539
                    yield "        ";
                    if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 539, $this->source); })()), "locked_value", [], "any", false, false, false, 539))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                        // line 540
                        yield "            ";
                        $context["locked_title"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["locked_title"]) || array_key_exists("locked_title", $context) ? $context["locked_title"] : (function () { throw new RuntimeError('Variable "locked_title" does not exist.', 540, $this->source); })()), "html", null, true);
                            yield "
            -
            ";
                            // line 542
                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((__("Last inventory value was:") . " ") . CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 542, $this->source); })()), "locked_value", [], "any", false, false, false, 542)), "html", null, true);
                            yield from [];
                        })())) ? '' : new Markup($tmp, $this->env->getCharset());
                        // line 543
                        yield "        ";
                    }
                    // line 544
                    yield "        <i class=\"ti ti-lock\" title=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["locked_title"]) || array_key_exists("locked_title", $context) ? $context["locked_title"] : (function () { throw new RuntimeError('Variable "locked_title" does not exist.', 544, $this->source); })()), "html", null, true);
                    yield "\" data-bs-toggle=\"tooltip\"></i>
        ";
                    yield from [];
                })())) ? '' : new Markup($tmp, $this->env->getCharset());
                // line 546
                yield "    ";
            }
            // line 547
            yield "
    <label class=\"";
            // line 548
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["class"]) || array_key_exists("class", $context) ? $context["class"] : (function () { throw new RuntimeError('Variable "class" does not exist.', 548, $this->source); })()), "html", null, true);
            yield "\" for=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["id"]) || array_key_exists("id", $context) ? $context["id"] : (function () { throw new RuntimeError('Variable "id" does not exist.', 548, $this->source); })()), "html", null, true);
            yield "\">
        ";
            // line 549
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["label"]) || array_key_exists("label", $context) ? $context["label"] : (function () { throw new RuntimeError('Variable "label" does not exist.', 549, $this->source); })()), "html", null, true);
            yield "
        ";
            // line 550
            yield (isset($context["locked_mark"]) || array_key_exists("locked_mark", $context) ? $context["locked_mark"] : (function () { throw new RuntimeError('Variable "locked_mark" does not exist.', 550, $this->source); })());
            yield "
        ";
            // line 551
            yield (isset($context["required_mark"]) || array_key_exists("required_mark", $context) ? $context["required_mark"] : (function () { throw new RuntimeError('Variable "required_mark" does not exist.', 551, $this->source); })());
            yield "
        ";
            // line 552
            yield (isset($context["helper"]) || array_key_exists("helper", $context) ? $context["helper"] : (function () { throw new RuntimeError('Variable "helper" does not exist.', 552, $this->source); })());
            yield "
        ";
            // line 553
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 553, $this->source); })()), "tpl_mark", [], "any", false, false, false, 553))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 554
                yield "            ";
                yield CoreExtension::getAttribute($this->env, $this->source, (isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 554, $this->source); })()), "tpl_mark", [], "any", false, false, false, 554);
                yield "
        ";
            }
            // line 556
            yield "    </label>
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "components/form/basic_inputs_macros.html.twig";
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
        return array (  1431 => 556,  1425 => 554,  1423 => 553,  1419 => 552,  1415 => 551,  1411 => 550,  1407 => 549,  1401 => 548,  1398 => 547,  1395 => 546,  1388 => 544,  1385 => 543,  1381 => 542,  1374 => 540,  1371 => 539,  1365 => 538,  1362 => 537,  1359 => 536,  1357 => 535,  1354 => 534,  1351 => 533,  1343 => 529,  1337 => 525,  1334 => 524,  1331 => 523,  1329 => 522,  1327 => 521,  1324 => 520,  1322 => 519,  1319 => 518,  1316 => 517,  1313 => 516,  1310 => 515,  1308 => 514,  1305 => 513,  1303 => 512,  1301 => 507,  1286 => 506,  1277 => 503,  1262 => 502,  1255 => 498,  1249 => 496,  1246 => 495,  1238 => 493,  1236 => 492,  1233 => 491,  1222 => 490,  1218 => 489,  1208 => 488,  1205 => 487,  1203 => 486,  1201 => 480,  1185 => 479,  1178 => 475,  1167 => 474,  1163 => 473,  1159 => 472,  1155 => 471,  1151 => 470,  1147 => 469,  1143 => 468,  1139 => 467,  1135 => 466,  1131 => 465,  1128 => 464,  1126 => 463,  1124 => 456,  1110 => 455,  1097 => 446,  1093 => 445,  1089 => 444,  1085 => 443,  1080 => 440,  1078 => 439,  1075 => 438,  1066 => 432,  1062 => 431,  1057 => 428,  1054 => 427,  1051 => 426,  1049 => 424,  1048 => 423,  1047 => 422,  1046 => 421,  1045 => 420,  1044 => 419,  1043 => 418,  1042 => 417,  1041 => 416,  1040 => 415,  1039 => 414,  1038 => 413,  1037 => 411,  1036 => 410,  1034 => 409,  1032 => 408,  1025 => 406,  1021 => 405,  1016 => 404,  1010 => 402,  1007 => 401,  1001 => 399,  998 => 398,  987 => 396,  983 => 395,  974 => 393,  969 => 392,  966 => 390,  964 => 388,  962 => 387,  959 => 386,  956 => 385,  954 => 384,  951 => 383,  949 => 382,  948 => 360,  946 => 353,  932 => 352,  925 => 348,  922 => 346,  908 => 345,  888 => 331,  884 => 330,  880 => 329,  874 => 326,  870 => 325,  866 => 324,  862 => 323,  858 => 322,  852 => 319,  848 => 317,  845 => 316,  842 => 315,  840 => 314,  837 => 313,  834 => 312,  831 => 311,  828 => 310,  825 => 309,  822 => 308,  819 => 307,  816 => 306,  813 => 305,  810 => 304,  807 => 303,  805 => 302,  801 => 300,  798 => 299,  790 => 295,  787 => 294,  782 => 291,  778 => 290,  775 => 289,  772 => 288,  770 => 287,  766 => 285,  764 => 283,  763 => 282,  762 => 281,  761 => 279,  756 => 277,  750 => 274,  746 => 273,  742 => 271,  739 => 270,  736 => 269,  733 => 268,  730 => 267,  727 => 266,  724 => 265,  721 => 264,  718 => 263,  715 => 262,  712 => 261,  709 => 260,  707 => 259,  704 => 258,  701 => 257,  698 => 256,  696 => 255,  693 => 254,  690 => 253,  687 => 252,  685 => 251,  682 => 250,  680 => 249,  679 => 248,  678 => 247,  675 => 246,  673 => 245,  670 => 244,  668 => 243,  666 => 232,  652 => 231,  643 => 227,  629 => 226,  622 => 222,  620 => 219,  619 => 218,  617 => 217,  611 => 215,  609 => 214,  606 => 213,  604 => 212,  602 => 210,  588 => 209,  579 => 205,  565 => 204,  556 => 200,  542 => 199,  524 => 185,  520 => 184,  513 => 180,  508 => 177,  506 => 174,  503 => 173,  501 => 172,  500 => 171,  498 => 170,  484 => 169,  476 => 165,  473 => 164,  470 => 163,  467 => 162,  464 => 161,  462 => 160,  459 => 159,  456 => 158,  453 => 157,  451 => 156,  448 => 155,  445 => 154,  442 => 153,  440 => 152,  437 => 151,  434 => 150,  431 => 149,  428 => 148,  425 => 147,  423 => 146,  421 => 145,  418 => 144,  416 => 143,  414 => 141,  400 => 140,  392 => 135,  390 => 134,  385 => 133,  381 => 131,  379 => 130,  376 => 129,  374 => 128,  372 => 126,  358 => 125,  347 => 118,  343 => 117,  337 => 116,  334 => 115,  332 => 114,  327 => 112,  324 => 111,  321 => 110,  314 => 107,  310 => 106,  307 => 105,  304 => 104,  302 => 103,  299 => 102,  295 => 101,  287 => 97,  285 => 96,  282 => 95,  275 => 91,  271 => 90,  267 => 89,  264 => 88,  261 => 87,  259 => 86,  256 => 85,  245 => 83,  238 => 82,  231 => 81,  225 => 80,  220 => 79,  217 => 78,  213 => 77,  209 => 76,  204 => 75,  193 => 73,  189 => 72,  182 => 71,  174 => 69,  172 => 68,  165 => 67,  163 => 66,  160 => 65,  157 => 64,  155 => 62,  153 => 61,  151 => 60,  148 => 59,  146 => 58,  143 => 57,  140 => 56,  137 => 55,  135 => 54,  132 => 53,  129 => 52,  126 => 51,  124 => 50,  121 => 49,  119 => 48,  117 => 34,  103 => 33,  97 => 505,  93 => 500,  89 => 477,  85 => 453,  81 => 350,  77 => 343,  73 => 229,  69 => 224,  65 => 207,  61 => 202,  57 => 197,  53 => 167,  49 => 138,  45 => 123,  42 => 32,);
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

{% macro input(name, value, options = {}) %}
    {% set options = {
        \x27id\x27: null,
        \x27type\x27: \x27text\x27,
        \x27input_addclass\x27: \x27\x27,
        \x27additional_attributes\x27: {},
        \x27readonly\x27: false,
        \x27disabled\x27: false,
        \x27multiple\x27: false,
        \x27required\x27: false,
        \x27maxlength\x27: null,
        \x27is_disclosable\x27: false,
        \x27is_copyable\x27: false,
        \x27clearable\x27: false,
        \x27with_class\x27: true
    }|merge(options) %}

    {% if options.fields_template.isMandatoryField(name)|default(false) %}
        {% set options = options|merge({\x27required\x27: true}) %}
    {% endif %}

    {% if options.fields_template.isReadonlyField(name)|default(false) %}
        {% set options = options|merge({\x27readonly\x27: true}) %}
    {% endif %}

    {% set has_addons = options.is_disclosable or options.is_copyable %}

    {% if has_addons and options.id is null %}
        {% set options = options|merge({
            id: name|safe_dom_id ~ \x27_\x27 ~ random()
        }) %}
    {% endif %}

    {% set input %}
        <input type=\"{{ options.type }}\" {{ options.id != null ? \x27id=\x27 ~ options.id : \x27\x27 }}
        {% if options.with_class %}
               class=\"form-control {{ options.input_addclass }} {{ has_addons ? \x27rounded-end-0\x27 : \x27\x27 }}\"
        {% endif %}
               name=\"{{ name }}\" value=\"{{ value }}\"
            {% for attr, value in options.additional_attributes %}
               {{ attr }}=\"{{ value }}\"
            {% endfor %}
               {{ options.maxlength ? \x27maxlength=\x27 ~ options.maxlength : \x27\x27 }}
               {{ options.readonly ? \x27readonly\x27 : \x27\x27 }}
               {{ options.disabled ? \x27disabled\x27 : \x27\x27 }}
               {{ options.multiple ? \x27multiple\x27 : \x27\x27 }} {# Only for emailField #}
               {{ options.required ? \x27required\x27 : \x27\x27 }}
               {% if options.pattern is defined %}pattern=\"{{ options.pattern }}\"{% endif %}
               {% if options.min is defined %}min=\"{{ options.min }}\"{% endif %}
               {% if options.max is defined %}max=\"{{ options.max }}\"{% endif %}
               {% if options.step is defined %}step=\"{{ options.step }}\"{% endif %} />
    {% endset %}

    {% set more_html %}
        {% if options.is_disclosable %}
            <button type=\"button\" class=\"btn btn-outline-secondary\"
                 onmousedown=\"showDisclosablePasswordField(\x27{{ options.id|e(\x27js\x27) }}\x27)\"
                 onmouseup=\"hideDisclosablePasswordField(\x27{{ options.id|e(\x27js\x27) }}\x27)\"
                 onmouseout=\"hideDisclosablePasswordField(\x27{{ options.id|e(\x27js\x27) }}\x27)\">
                <i class=\"ti ti-eye disclose\"></i>
            </button>
        {% endif %}

        {% if options.is_copyable %}
            <button type=\"button\" class=\"btn btn-outline-secondary\" onclick=\"copyDisclosablePasswordFieldToClipboard(\x27{{ options.id|e(\x27js\x27) }}\x27)\">
                <i class=\"ti ti-clipboard-copy disclose\"></i>
            </button>
        {% endif %}
    {% endset %}

    {% if has_addons %}
        {% set input %}
            <div class=\"btn-group btn-group-sm d-flex\">
                {{ input }}
                {{ more_html }}
            </div>
        {% endset %}
    {% endif %}

    {{ input }}

    {% if options.clearable %}
        <div class=\"d-flex align-items-center gap-1 mt-1\">
            <input type=\"checkbox\" name=\"_blank_{{ name }}\" id=\"_blank_{{ name }}\" class=\"form-check-input\">
            <label for=\"_blank_{{ name }}\" class=\"form-check-label\">
                {{ __(\x27Clear\x27) }}
            </label>
        </div>
    {% endif %}
{% endmacro %}


{% macro text(name, value, options = {}) %}
    {% set options = {
        \x27copyable\x27: false,
    }|merge(options) %}

    {% if options.copyable %}
        <div class=\"copy_to_clipboard_wrapper\">
    {% endif %}
    {{ _self.input(name, value, options|merge({\x27type\x27: \x27text\x27})) }}
    {% if options.copyable %}
        </div>
    {% endif %}
{% endmacro %}


{% macro number(name, value, options = {}) %}
    {% set options = {
        \x27step\x27: 1,
    }|merge(options) %}

    {% if options.decimals is not defined and not options.readonly|default(false) and not options.disabled|default(false) %}
        {# Automatically define the decimal count based on the configured step. #}
        {% set decimals_part = options.step|split(\x27.\x27) %}
        {% set decimals = decimals_part[1] is defined ? decimals_part[1]|length : 0 %}
        {% set options = options|merge({\x27decimals\x27: decimals}) %}
    {% endif %}

    {% if value == \"\" %}
        {% set value = (options.min is defined ? options.min : 0) %}
    {% endif %}

    {% if options.step != \x27any\x27 and options.step|round(0, \x27floor\x27) != options.step %}
        {% if options.decimals is defined %}
            {% set value = call(\x27Html::formatNumber\x27, [value, true, options.decimals]) %}
        {% else %}
            {# Only format number if not a whole number #}
            {% set value = call(\x27Html::formatNumber\x27, [value, true]) %}
        {% endif %}
    {% endif %}

    {{ _self.input(name, value, options|merge({\x27type\x27: \x27number\x27})) }}
{% endmacro %}


{% macro color(name, value, options = {}) %}
    {% set options = {
        id: name ~ \x27_\x27 ~ (options.rand ?? random()),
    }|merge(options) %}

    {{ _self.input(name, value, options|merge({
        \x27type\x27: \x27text\x27,
        \x27input_addclass\x27: \x27rounded-0\x27,
    })) }}
    <script>
        \$(function () {
            \$(\"#{{ options.id|e(\x27css\x27)|e(\x27js\x27) }}\").spectrum({
                showInput: true,
                preferredFormat: \"hex\",
                type: \"text\",
                cancelText: \"{{ __(\x27Cancel\x27)|e(\x27js\x27) }}\",
                chooseText: \"{{ __(\x27Validate\x27)|e(\x27js\x27) }}\",
                change: function (color) {
                    if (color !== null && color.getAlpha() !== 1) {
                        let hex = color.toHexString();
                        hex += (\"0\" + Math.round(parseFloat(color.getAlpha()) * 255).toString(16)).slice(-2);
                        this.value = hex;
                    }
                }
            });
        });
    </script>
{% endmacro %}


{% macro password(name, value, options = {}) %}
    {{ _self.input(name, value, options|merge({\x27type\x27: \x27password\x27})) }}
{% endmacro %}


{% macro email(name, value, options = {}) %}
    {{ _self.input(name, value, options|merge({\x27type\x27: \x27email\x27})) }}
{% endmacro %}


{% macro file(name, value, options = {}) %}
    {% set options = {
        \x27simple\x27: false,
    }|merge(options) %}

    {% if options.simple %}
        {{ _self.input(name, value, options|merge({\x27type\x27: \x27file\x27})) }}
    {% else %}
        {% do call(\x27Html::file\x27, [
            options|merge({
                \x27name\x27: name,
            })
        ]) %}
    {% endif %}
{% endmacro %}


{% macro hidden(name, value, options = {}) %}
    {{ _self.input(name, value, options|merge({\x27type\x27: \x27hidden\x27, \x27with_class\x27: false})) }}
{% endmacro %}


{% macro date(name, value, options = {}) %}
    {% set options = {
        \x27rand\x27: random(),
        \x27enableTime\x27: false,
        \x27noCalendar\x27: false,
        \x27checkIsExpired\x27: false,
        \x27clearable\x27: false,
        \x27container_addclass\x27: \x27\x27,
        \x27input_addclass\x27: \x27\x27,
        \x27readonly\x27: false,
        \x27disabled\x27: false,
        \x27maybeempty\x27: false,
    }|merge(options) %}

    {% set editable = not options.readonly and not options.disabled %}

    {% set options = {
        \x27id\x27: name|safe_dom_id ~ \x27_\x27 ~ options.rand
    }|merge(options) %}

    {% if options.fields_template.isReadonlyField(name)|default(false) %}
        {% set options = options|merge({\x27readonly\x27: true}) %}
    {% endif %}

    {% if value == \x27NULL\x27 %}
      {% set value = null %}
   {% endif %}

    {% set final_expiration_class = \x27\x27 %}
    {% if options.checkIsExpired %}
        {% if value|date(\x27Y-m-d H:i:s\x27) < \"now\"|date(\x27Y-m-d H:i:s\x27) %}
            {% set final_expiration_class = \x27 warn\x27 %}
        {% endif %}
    {% else %}
        {% if options.expiration_class is defined %}
            {% set final_expiration_class = \x27 \x27 ~ options.expiration_class %}
        {% else %}
            {% set final_expiration_class = \x27\x27 %}
        {% endif %}
    {% endif %}

    <div
        class=\"btn-group flex-grow-1 flatpickr d-flex {{ options.container_addclass }}\"
        id=\"{{ options.id }}\"
        data-bs-toggle=\"tooltip\"
        data-bs-placement=\"bottom\"
        title=\"{{ editable ? __(\x27Enter or select a date\x27) : _n(\x27Date\x27, \x27Dates\x27, 1) }}\"
    >
        {{ _self.input(name, value, options|merge({
            \x27type\x27: \x27text\x27,
            \x27id\x27: options.id ~ \x27_input\x27,
            \x27additional_attributes\x27: (options.additional_attributes ?? {})|merge({\x27data-input\x27: \x27\x27}),
            \x27input_addclass\x27: options.input_addclass ~ final_expiration_class,
            \x27clearable\x27: false
        })) }}

        {% if editable %}
            {% set calendar_icon = options.enableTime ? \x27ti ti-calendar-time\x27 : \x27ti ti-calendar\x27 %}
            <button type=\"button\" class=\"btn btn-outline-secondary btn-sm\" data-toggle>
                <i class=\"{{ calendar_icon }}\"></i>
                <span class=\"sr-only\">{{ __(\x27Enter or select a date\x27) }}</span>
            </button>
            {# maybeempty has no distinct purpose, but it was here first #}
            {% if options.clearable or options.maybeempty %}
                <button type=\"button\" class=\"btn btn-outline-secondary btn-sm\" data-bs-toggle=\"tooltip\" data-bs-placement=\"bottom\" data-clear title=\"{{ __(\x27Clear\x27) }}\">
                    <i class=\"ti ti-circle-x\"></i>
                </button>
            {% endif %}
        {% endif %}
    </div>

    {% if editable %}
        {% set locale = get_current_locale() %}
        {% if options.enableTime and not options.noCalendar %}
            {% set date_format = \x27Y-m-d H:i:S\x27 %}
            {% set alt_format = call(\x27Toolbox::getDateFormat\x27, [\x27js\x27]) ~ (options.enableTime ? \x27 H:i:S\x27 : \x27\x27) %}
        {% elseif options.enableTime and options.noCalendar %}
            {% set date_format = \x27H:i:S\x27 %}
            {% set alt_format = (options.enableTime ? \x27 H:i:S\x27 : \x27\x27) %}
        {% elseif not options.enableTime and not options.noCalendar %}
            {% set date_format = \x27Y-m-d\x27 %}
            {% set alt_format = call(\x27Toolbox::getDateFormat\x27, [\x27js\x27]) %}
        {% else %}
            {# No time and no date, doesn\x27t make sense so fallback to full format #}
            {% set date_format = \x27Y-m-d H:i:S\x27 %}
        {% endif %}
        <script>
            \$(function() {
                \$(\"#{{ options.id|e(\x27css\x27)|e(\x27js\x27) }}\").flatpickr({
                    wrap: true,
                    altInput: true,
                    dateFormat: \x27{{ date_format|e(\x27js\x27) }}\x27,
                    altFormat: \x27{{ alt_format|e(\x27js\x27) }}\x27,
                    enableTime: {{ options.enableTime ? \"true\" : \"false\" }},
                    enableSeconds: {{ options.enableTime ? \"true\" : \"false\" }},
                    noCalendar: {{ options.noCalendar ? \"true\" : \"false\" }},
                    weekNumbers: true,
                    time_24hr: true,
                    allowInput: {{ options.readonly ? \x27false\x27 : \x27true\x27 }},
                    clickOpens: {{ options.readonly ? \x27false\x27 : \x27true\x27 }},
                    locale: getFlatPickerLocale(\"{{ locale[\x27language\x27]|e(\x27js\x27) }}\", \"{{ locale[\x27region\x27]|e(\x27js\x27) }}\"),
                    onClose(dates, currentdatestring, picker) {
                        picker.setDate(picker.altInput.value, true, picker.config.altFormat)
                    },
                    plugins: [
                        CustomFlatpickrButtons()
                    ]
                });
            });
        </script>
    {% endif %}
{% endmacro %}


{% macro datetime(name, value, options = {}) %}
    {{ _self.date(name, value, options|merge({
        \x27enableTime\x27: true
    })) }}
{% endmacro %}


{% macro textarea(name, value, options = {}) %}
    {% set options = {
        \x27id\x27: null,
        \x27rand\x27: random(),
        \x27rows\x27: 3,
        \x27enable_richtext\x27: false,
        \x27enable_images\x27: true,
        \x27mention_options\x27: {
            \x27enabled\x27: options.enable_mentions is defined and options.enable_mentions,
            \x27full\x27: true,
            \x27users\x27: [],
        },
        \x27entities_id\x27: session(\x27glpiactive_entity\x27),
        \x27readonly\x27: false,
        \x27disabled\x27: false,
        \x27required\x27: false,
        \x27add_body_classes\x27: [],
        \x27toolbar\x27: true,
        \x27toolbar_location\x27: \x27top\x27,
        \x27init\x27: true,
        \x27init_on_demand\x27: false,
        \x27placeholder\x27: \"\",
        \x27enable_form_tags\x27: false,
        \x27form_tags_form_id\x27: null,
        \x27aria_label\x27: \"\",
        \x27statusbar\x27: true,
        \x27content_style\x27: \"\",
        \x27input_addclass\x27: \x27\x27,
        \x27additional_attributes\x27: [],
        \x27plugins_to_remove\x27: [],
    }|merge(options) %}

    {% if options.fields_template.isMandatoryField(name)|default(false) %}
        {% set options = {\x27required\x27: true}|merge(options) %}
    {% endif %}
    {% set options = options|merge({
        \x27id\x27: options.id|length > 0 ? options.id : (name ~ \x27_\x27 ~ options.rand)
    }) %}

    {# 100% width is here to prevent width issues with tinymce #}
    <textarea class=\"form-control {{ options.input_addclass }}\"
            id=\"{{ options.id }}\" name=\"{{ name }}\" rows=\"{{ options.rows }}\"
            style=\"width: 100%;\"
            {% for attr, value in options.additional_attributes %}
               {{ attr }}=\"{{ value }}\"
            {% endfor %}
            {% if not options.aria_label is empty %}
                aria-label=\"{{ options.aria_label }}\"
            {% endif %}
            {% if not options.placeholder is empty %}
                placeholder=\"{{ options.placeholder }}\"
            {% endif %}
            {{ options.disabled ? \x27disabled\x27 : \x27\x27 }}
            {{ options.readonly ? \x27readonly\x27 : \x27\x27 }}
            {{ options.required ? \x27required\x27 : \x27\x27 }}>{{ options.enable_richtext ? value|safe_html|escape : value }}</textarea>

    {% if options.enable_richtext %}
        {% do call(\x27Html::initEditorSystem\x27, [
            options.id,
            options.rand,
            true,
            options.disabled|default(false),
            options.enable_images,
            options.editor_height|default(150),
            options.add_body_classes,
            options.toolbar_location,
            options.init,
            options.placeholder,
            options.toolbar,
            options.statusbar,
            options.content_style,
            options.init_on_demand,
            options.plugins_to_remove,
        ]) %}
   {% endif %}
   {% if options.enable_form_tags %}
        <script>
            \$(function() {
                const form_tags = new GLPI.RichText.FormTags(
                    tinymce.get(\x27{{ options.id|e(\x27js\x27) }}\x27),
                    {{ options.form_tags_form_id|json_encode|raw }},
                );
                form_tags.register();
            });
        </script>
    {% endif %}

    {% if options.mention_options.enabled|default(false) and config(\x27use_notifications\x27) %}
        <script>
            \$(function() {
                const user_mention = new GLPI.RichText.UserMention(
                    tinymce.get(\x27{{ options.id|e(\x27js\x27) }}\x27),
                    {{ options.entities_id|json_encode|raw }},
                    \x27{{ idor_token(\x27User\x27, {\x27right\x27: \x27all\x27, \x27entity_restrict\x27: options.entities_id|json_encode|raw}) }}\x27,
                    {{ options.mention_options|json_encode|raw }}
                );
                user_mention.register();
            });
        </script>
    {% endif %}
{% endmacro %}


{% macro checkbox(name, value, options = {}) %}
    {% set options = {
        \x27id\x27: null,
        \x27input_addclass\x27: \x27\x27,
        \x27readonly\x27: false,
        \x27disabled\x27: false,
        \x27required\x27: false,
        \x27additional_attributes\x27: {},
    }|merge(options) %}

    <input type=\"hidden\"   name=\"{{ name }}\" value=\"0\" />
    <input type=\"checkbox\" name=\"{{ name }}\" value=\"1\"
           class=\"form-check-input {{ options.input_addclass }}\"
           {{ (options.id != null ? \x27id=\"\x27 ~ options.id|escape ~ \x27\"\x27 : \x27\x27)|raw }}
           {{ value == 1 ? \x27checked\x27 : \x27\x27 }}
           {{ options.readonly ? \x27readonly\x27 : \x27\x27 }}
           {{ options.required ? \x27required\x27 : \x27\x27 }}
           {{ options.disabled ? \x27disabled\x27 : \x27\x27 }}
            {% for attr, value in options.additional_attributes %}
                {{ attr }}=\"{{ value }}\"
            {% endfor %}/>
{% endmacro %}


{% macro button(name, label = \x27\x27, type = \x27button\x27, value = \x27\x27, options = {}) %}
    {% set options = {
        \x27type\x27: \x27submit\x27,
        \x27class\x27: \x27btn btn-primary\x27,
        \x27icon\x27: \x27\x27,
        \x27icon_title\x27: \x27\x27,
        \x27additional_attributes\x27: {},
    }|merge(options) %}

    <button class=\"{{ options.class }}\" type=\"{{ type }}\" name=\"{{ name }}\" value=\"{{ value }}\"
        {% for attr, value in options.additional_attributes %}
            {{ attr }}=\"{{ value }}\"
        {% endfor %}>
        {% if options.icon is not empty %}
            <i class=\"{{ options.icon }}\" title=\"{{ options.icon_title }}\"></i>
        {% endif %}
        {% if label is not empty %}
            <span>{{ label }}</span>
        {% endif %}
    </button>
{% endmacro %}


{% macro submit(name, label = \x27\x27, value = \x27\x27, options = {}) %}
    {{ _self.button(name, label, \x27submit\x27, value, options) }}
{% endmacro %}

{% macro label(label, id, options = {}, class = \x27form-label\x27) %}
    {% set options = {
        \x27locked\x27: false,
        \x27locked_value\x27: null,
        \x27tpl_mark\x27: null,
        \x27helper\x27: false
    }|merge(options) %}

    {% set required_mark = \x27\x27 %}
    {% if (options.name is defined and options.fields_template.isMandatoryField(options.name)|default(false)) or options.required ?? false %}
        {% set required_mark = \x27<span class=\"required\">*</span>\x27 %}
    {% endif %}

    {% set helper = \x27\x27 %}
    {% if options.helper %}
        {# `|escape` must be called before call to `|nl2br` to ensure that special chars in the text will escaped twice. #}
        {# Indeed, otherwise, due to the usage of `data-bs-html=\"true\"`, any code snippet would be interpreted. #}
        {% set helper_safe_text = options.helper|escape|nl2br %}
        {% set helper %}
        <span class=\"form-help\"
              data-bs-toggle=\"tooltip\"
              data-bs-placement=\"top\"
              data-bs-html=\"true\"
              data-bs-title=\"{{ helper_safe_text|raw }}\">
            ?
        </span>
        {% endset %}
    {% endif %}

    {% set locked_mark = \x27\x27 %}
    {% if options.locked %}
        {% set locked_mark %}
        {% set locked_title %}{{ __(\x27Field will not be updated from inventory\x27) }}{% endset %}
        {% if options.locked_value is not empty %}
            {% set locked_title %}{{ locked_title }}
            -
            {{ __(\x27Last inventory value was:\x27) ~ \x27 \x27 ~ options.locked_value }}{% endset %}
        {% endif %}
        <i class=\"ti ti-lock\" title=\"{{ locked_title }}\" data-bs-toggle=\"tooltip\"></i>
        {% endset %}
    {% endif %}

    <label class=\"{{ class }}\" for=\"{{ id }}\">
        {{ label }}
        {{ locked_mark|raw }}
        {{ required_mark|raw }}
        {{ helper|raw }}
        {% if options.tpl_mark is not empty %}
            {{ options.tpl_mark|raw }}
        {% endif %}
    </label>
{% endmacro %}
", "components/form/basic_inputs_macros.html.twig", "/var/www/html/glpi/templates/components/form/basic_inputs_macros.html.twig");
    }
}
