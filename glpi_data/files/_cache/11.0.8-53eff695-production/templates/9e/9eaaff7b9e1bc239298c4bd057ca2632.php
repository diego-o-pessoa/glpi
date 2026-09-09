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

/* components/itilobject/timeline/simple_form.html.twig */
class __TwigTemplate_f575e8dd6edf5c98701b7a1c87e78628 extends Template
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
        $context["target"] = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "getFormURL", [], "method", false, false, false, 35);
        // line 36
        $context["is_new_item"] = ((($_v0 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 36)) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0["id"] ?? null) : null) == 0);
        // line 37
        $context["show_form"] = ((array_key_exists("no_form", $context)) ? ( !($context["no_form"] ?? null)) : (true));
        // line 38
        yield "
";
        // line 39
        $context["field_options"] = ["is_horizontal" => false, "full_width" => true, "fields_template" => ((        // line 42
array_key_exists("itiltemplate", $context)) ? (Twig\Extension\CoreExtension::default(($context["itiltemplate"] ?? null), null)) : (null)), "disabled" => (!(        // line 43
($context["canupdate"] ?? null) || ($context["can_requester"] ?? null)))];
        // line 45
        yield "
";
        // line 46
        if ((($tmp = ($context["show_form"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 47
            yield "<form method=\"post\" action=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["target"] ?? null), "html", null, true);
            yield "\" ";
            yield (((array_key_exists("formoptions", $context) &&  !(null === $context["formoptions"]))) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["formoptions"], "html", null, true)) : (""));
            yield " enctype=\"multipart/form-data\" data-submit-once>
";
        }
        // line 49
        yield "   <div class=\"row flex-column\">
      ";
        // line 50
        if ((($tmp =  !CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "isNewItem", [], "method", false, false, false, 50)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 51
            yield "        ";
            $context["recipient"] = ((((($_v1 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 51)) && is_array($_v1) || $_v1 instanceof ArrayAccess ? ($_v1["users_id_recipient"] ?? null) : null) > 0)) ? ((($_v2 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 51)) && is_array($_v2) || $_v2 instanceof ArrayAccess ? ($_v2["users_id_recipient"] ?? null) : null)) : ($this->extensions['Glpi\Application\View\Extension\SessionExtension']->session("glpiID")));
            // line 52
            yield "        ";
            yield $macros["fields"]->getTemplateForMacro("macro_dropdownField", $context, 52, $this->getSourceContext())->macro_dropdownField(...["User", "users_id_recipient",             // line 55
($context["recipient"] ?? null), __("By"), Twig\Extension\CoreExtension::merge(            // line 57
($context["field_options"] ?? null), ["maxlength" => 255, "entity" => (($_v3 = CoreExtension::getAttribute($this->env, $this->source,             // line 59
($context["item"] ?? null), "fields", [], "any", false, false, false, 59)) && is_array($_v3) || $_v3 instanceof ArrayAccess ? ($_v3["entities_id"] ?? null) : null), "right" => "all", "display_emptychoice" => false])]);
            // line 63
            yield "
      ";
        }
        // line 65
        yield "
      ";
        // line 66
        yield $macros["fields"]->getTemplateForMacro("macro_textField", $context, 66, $this->getSourceContext())->macro_textField(...["name", (($_v4 = CoreExtension::getAttribute($this->env, $this->source,         // line 68
($context["item"] ?? null), "fields", [], "any", false, false, false, 68)) && is_array($_v4) || $_v4 instanceof ArrayAccess ? ($_v4["name"] ?? null) : null), __("Title"), Twig\Extension\CoreExtension::merge(        // line 70
($context["field_options"] ?? null), ["maxlength" => 255, "additional_attributes" => ["autocomplete" => "on"]])]);
        // line 74
        yield "

      ";
        // line 76
        $context["uploads"] = [];
        // line 77
        yield "      ";
        if (CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "input", [], "any", false, true, false, 77), "_content", [], "any", true, true, false, 77)) {
            // line 78
            yield "         ";
            $context["uploads"] = Twig\Extension\CoreExtension::merge(($context["uploads"] ?? null), ["_content" => CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "input", [], "any", false, false, false, 78), "_content", [], "any", false, false, false, 78), "_tag_content" => CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "input", [], "any", false, false, false, 78), "_tag_content", [], "any", false, false, false, 78)]);
            // line 79
            yield "      ";
        }
        // line 80
        yield "      ";
        if (CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "input", [], "any", false, true, false, 80), "_filename", [], "any", true, true, false, 80)) {
            // line 81
            yield "         ";
            $context["uploads"] = Twig\Extension\CoreExtension::merge(($context["uploads"] ?? null), ["_filename" => CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "input", [], "any", false, false, false, 81), "_filename", [], "any", false, false, false, 81), "_tag_filename" => CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "input", [], "any", false, false, false, 81), "_tag_filename", [], "any", false, false, false, 81)]);
            // line 82
            yield "      ";
        }
        // line 83
        yield "
      ";
        // line 84
        yield $macros["fields"]->getTemplateForMacro("macro_textareaField", $context, 84, $this->getSourceContext())->macro_textareaField(...["content", (($_v5 = CoreExtension::getAttribute($this->env, $this->source,         // line 86
($context["item"] ?? null), "fields", [], "any", false, false, false, 86)) && is_array($_v5) || $_v5 instanceof ArrayAccess ? ($_v5["content"] ?? null) : null), __("Description"), Twig\Extension\CoreExtension::merge(        // line 88
($context["field_options"] ?? null), ["enable_richtext" => true, "enable_fileupload" => (((($tmp = ((CoreExtension::getAttribute($this->env, $this->source,         // line 90
($context["itiltemplate"] ?? null), "isHiddenField", ["_documents_id"], "method", true, true, false, 90)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["itiltemplate"] ?? null), "isHiddenField", ["_documents_id"], "method", false, false, false, 90), false)) : (false))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (false) : (true)), "mention_options" => ((        // line 91
array_key_exists("mention_options", $context)) ? (Twig\Extension\CoreExtension::default(($context["mention_options"] ?? null), [])) : ([])), "entities_id" => (((($tmp = CoreExtension::getAttribute($this->env, $this->source,         // line 92
($context["item"] ?? null), "isNewItem", [], "method", false, false, false, 92)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (($context["entities_id"] ?? null)) : ((($_v6 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 92)) && is_array($_v6) || $_v6 instanceof ArrayAccess ? ($_v6["entities_id"] ?? null) : null))), "uploads" =>         // line 93
($context["uploads"] ?? null), "add_field_class" => "col-12 itil-textarea-content"])]);
        // line 96
        yield "
   </div>

   ";
        // line 99
        if ((( !($context["is_new_item"] ?? null) && ($context["show_form"] ?? null)) &&  !(((CoreExtension::getAttribute($this->env, $this->source, ($context["params"] ?? null), "template_preview", [], "array", true, true, false, 99) &&  !(null === (($_v7 = ($context["params"] ?? null)) && is_array($_v7) || $_v7 instanceof ArrayAccess ? ($_v7["template_preview"] ?? null) : null)))) ? ((($_v8 = ($context["params"] ?? null)) && is_array($_v8) || $_v8 instanceof ArrayAccess ? ($_v8["template_preview"] ?? null) : null)) : (false)))) {
            // line 100
            yield "      <div class=\"d-flex card-footer mx-n3 mb-n3\">
         <button class=\"btn btn-primary me-2\" type=\"submit\" name=\"update\">
            <i class=\"ti ti-device-floppy\"></i>
            <span>";
            // line 103
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(_x("button", "Save"), "html", null, true);
            yield "</span>
         </button>
      </div>

      <input type=\"hidden\" name=\"id\" value=\"";
            // line 107
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v9 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 107)) && is_array($_v9) || $_v9 instanceof ArrayAccess ? ($_v9["id"] ?? null) : null), "html", null, true);
            yield "\" />
      <input type=\"hidden\" name=\"_glpi_csrf_token\" value=\"";
            // line 108
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Session::getNewCSRFToken(), "html", null, true);
            yield "\" />
   </form>
   ";
        }
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "components/itilobject/timeline/simple_form.html.twig";
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
        return array (  159 => 108,  155 => 107,  148 => 103,  143 => 100,  141 => 99,  136 => 96,  134 => 93,  133 => 92,  132 => 91,  131 => 90,  130 => 88,  129 => 86,  128 => 84,  125 => 83,  122 => 82,  119 => 81,  116 => 80,  113 => 79,  110 => 78,  107 => 77,  105 => 76,  101 => 74,  99 => 70,  98 => 68,  97 => 66,  94 => 65,  90 => 63,  88 => 59,  87 => 57,  86 => 55,  84 => 52,  81 => 51,  79 => 50,  76 => 49,  68 => 47,  66 => 46,  63 => 45,  61 => 43,  60 => 42,  59 => 39,  56 => 38,  54 => 37,  52 => 36,  50 => 35,  47 => 34,  45 => 33,  42 => 32,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "components/itilobject/timeline/simple_form.html.twig", "/var/www/html/glpi/templates/components/itilobject/timeline/simple_form.html.twig");
    }
}
