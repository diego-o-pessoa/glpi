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

/* pages/management/document_item.html.twig */
class __TwigTemplate_05496faaaa17d252035dd49f4153c387 extends Template
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
        $macros["inputs"] = $this->macros["inputs"] = $this->load("components/form/basic_inputs_macros.html.twig", 34)->unwrap();
        // line 35
        yield "
<div class=\"row g-3 mb-3\">

    <div class=\"col-12 col-lg-6\">
        <div class=\"card shadow-sm h-100\">
            <div class=\"card-header fw-bold\">
                ";
        // line 41
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Add a document"), "html", null, true);
        yield "
            </div>
            <div class=\"card-body\">
                <form method=\"post\" action=\"";
        // line 44
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeFormPath("Document"), "html", null, true);
        yield "\" enctype=\"multipart/form-data\" data-submit-once>
                    <div class=\"d-flex flex-column align-items-center\">
                        ";
        // line 46
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownField", $context, 46, $this->getSourceContext())->macro_dropdownField(...["DocumentCategory", "documentcategories_id", (((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, true, false, 46), "documentcategories_id", [], "array", true, true, false, 46) &&  !(null === (($_v0 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 46)) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0["documentcategories_id"] ?? null) : null)))) ? ((($_v1 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 46)) && is_array($_v1) || $_v1 instanceof ArrayAccess ? ($_v1["documentcategories_id"] ?? null) : null)) : ("")), $this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("DocumentCategory"), ["entity" =>         // line 47
($context["entities"] ?? null), "field_class" => "col-12 col-sm-10"]]);
        // line 49
        yield "

                        ";
        // line 51
        yield $macros["fields"]->getTemplateForMacro("macro_fileField", $context, 51, $this->getSourceContext())->macro_fileField(...["filename", null, null, ["no_label" => true, "multiple" => true, "field_class" => "col-12 col-sm-10"]]);
        // line 55
        yield "

                        <div class=\"text-end\">
                            ";
        // line 58
        yield $macros["inputs"]->getTemplateForMacro("macro_submit", $context, 58, $this->getSourceContext())->macro_submit(...["add", _x("button", "Add a new file"), 1, ["icon" => "ti ti-plus"]]);
        yield "
                        </div>

                        ";
        // line 61
        yield $macros["inputs"]->getTemplateForMacro("macro_hidden", $context, 61, $this->getSourceContext())->macro_hidden(...["entities_id", ($context["entity"] ?? null)]);
        yield "
                        ";
        // line 62
        yield $macros["inputs"]->getTemplateForMacro("macro_hidden", $context, 62, $this->getSourceContext())->macro_hidden(...["is_recursive", (((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "isRecursive", [], "method", false, false, false, 62)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("1") : ("0"))]);
        yield "
                        ";
        // line 63
        yield $macros["inputs"]->getTemplateForMacro("macro_hidden", $context, 63, $this->getSourceContext())->macro_hidden(...["itemtype", CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "getType", [], "method", false, false, false, 63)]);
        yield "
                        ";
        // line 64
        yield $macros["inputs"]->getTemplateForMacro("macro_hidden", $context, 64, $this->getSourceContext())->macro_hidden(...["items_id", CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "getID", [], "method", false, false, false, 64)]);
        yield "
                        ";
        // line 65
        if ((CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "getType", [], "method", false, false, false, 65) == "Ticket")) {
            // line 66
            yield "                            ";
            yield $macros["inputs"]->getTemplateForMacro("macro_hidden", $context, 66, $this->getSourceContext())->macro_hidden(...["tickets_id", CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "getID", [], "method", false, false, false, 66)]);
            yield "
                        ";
        }
        // line 68
        yield "                        ";
        yield $macros["inputs"]->getTemplateForMacro("macro_hidden", $context, 68, $this->getSourceContext())->macro_hidden(...["_glpi_csrf_token", Session::getNewCSRFToken()]);
        yield "
                    </div>
                </form>
            </div>
        </div>
    </div>

    ";
        // line 75
        if ((($tmp = ($context["canview"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 76
            yield "        <div class=\"col-12 col-lg-6\">
            <div class=\"card shadow-sm h-100\">
                <div class=\"card-header fw-bold\">
                    ";
            // line 79
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Associate an existing document"), "html", null, true);
            yield "
                </div>
                <div class=\"card-body d-flex flex-column h-100\">
                    <form method=\"post\" action=\"";
            // line 82
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeFormPath("Document_Item"), "html", null, true);
            yield "\" data-submit-once class=\"d-flex flex-column h-100\">

                        <div class=\"d-flex flex-column align-items-center\">
                            ";
            // line 85
            yield $macros["fields"]->getTemplateForMacro("macro_dropdownField", $context, 85, $this->getSourceContext())->macro_dropdownField(...["Document", "documents_id", null, $this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("Document"), ["entity" =>             // line 86
($context["entities"] ?? null), "used" =>             // line 87
($context["used"] ?? null), "field_class" => "col-12 col-sm-12", "label_class" => "col-xxl-2", "input_class" => "col-xxl-10"]]);
            // line 91
            yield "
                        </div>

                        <div class=\"d-flex flex-column align-items-center text-end mt-auto\">
                            ";
            // line 95
            yield $macros["inputs"]->getTemplateForMacro("macro_submit", $context, 95, $this->getSourceContext())->macro_submit(...["add", _x("button", "Associate"), 1, ["icon" => "ti ti-link"]]);
            yield "
                        </div>

                        ";
            // line 98
            yield $macros["inputs"]->getTemplateForMacro("macro_hidden", $context, 98, $this->getSourceContext())->macro_hidden(...["itemtype", CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "getType", [], "method", false, false, false, 98)]);
            yield "
                        ";
            // line 99
            yield $macros["inputs"]->getTemplateForMacro("macro_hidden", $context, 99, $this->getSourceContext())->macro_hidden(...["items_id", CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "getID", [], "method", false, false, false, 99)]);
            yield "
                        ";
            // line 100
            if ((CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "getType", [], "method", false, false, false, 100) == "Ticket")) {
                // line 101
                yield "                            ";
                yield $macros["inputs"]->getTemplateForMacro("macro_hidden", $context, 101, $this->getSourceContext())->macro_hidden(...["tickets_id", CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "getID", [], "method", false, false, false, 101)]);
                yield "
                            ";
                // line 102
                yield $macros["inputs"]->getTemplateForMacro("macro_hidden", $context, 102, $this->getSourceContext())->macro_hidden(...["documentcategories_id", $this->extensions['Glpi\Application\View\Extension\ConfigExtension']->config("documentcategories_id_forticket")]);
                yield "
                        ";
            }
            // line 104
            yield "                        ";
            yield $macros["inputs"]->getTemplateForMacro("macro_hidden", $context, 104, $this->getSourceContext())->macro_hidden(...["_glpi_csrf_token", Session::getNewCSRFToken()]);
            yield "
                    </form>
                </div>
            </div>
        </div>
    ";
        }
        // line 110
        yield "
</div>

";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "pages/management/document_item.html.twig";
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
        return array (  188 => 110,  178 => 104,  173 => 102,  168 => 101,  166 => 100,  162 => 99,  158 => 98,  152 => 95,  146 => 91,  144 => 87,  143 => 86,  142 => 85,  136 => 82,  130 => 79,  125 => 76,  123 => 75,  112 => 68,  106 => 66,  104 => 65,  100 => 64,  96 => 63,  92 => 62,  88 => 61,  82 => 58,  77 => 55,  75 => 51,  71 => 49,  69 => 47,  68 => 46,  63 => 44,  57 => 41,  49 => 35,  47 => 34,  45 => 33,  42 => 32,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "pages/management/document_item.html.twig", "/var/www/html/glpi/templates/pages/management/document_item.html.twig");
    }
}
