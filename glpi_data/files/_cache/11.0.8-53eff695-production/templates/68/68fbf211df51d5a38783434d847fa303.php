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

/* pages/tools/project_task.html.twig */
class __TwigTemplate_8ce4ccb86b39343893eec777e6d48f79 extends Template
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

        $this->blocks = [
            'form_fields' => [$this, 'block_form_fields'],
        ];
    }

    protected function doGetParent(array $context): bool|string|Template|TemplateWrapper
    {
        // line 33
        return "generic_show_form.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 34
        $macros["fields"] = $this->macros["fields"] = $this->load("components/form/fields_macros.html.twig", 34)->unwrap();
        // line 36
        $context["form_id"] = ("project_task_" . ($context["rand"] ?? null));
        // line 37
        $context["content_field_id"] = ("content_" . ($context["rand"] ?? null));
        // line 38
        $context["params"] = ["form_id" =>         // line 39
($context["form_id"] ?? null)];
        // line 33
        $this->parent = $this->load("generic_show_form.html.twig", 33);
        yield from $this->parent->unwrap()->yield($context, array_merge($this->blocks, $blocks));
    }

    // line 42
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_form_fields(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 43
        yield "
    ";
        // line 44
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownField", $context, 44, $this->getSourceContext())->macro_dropdownField(...["ProjectTaskTemplate", "projecttasktemplates_id", CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source,         // line 47
($context["item"] ?? null), "fields", [], "any", false, false, false, 47), "projecttasktemplates_id", [], "any", false, false, false, 47), $this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("ProjectTaskTemplate"), ["entity" => CoreExtension::getAttribute($this->env, $this->source,         // line 50
($context["item"] ?? null), "getEntityID", [], "method", false, false, false, 50)]]);
        // line 52
        yield "

    ";
        // line 54
        yield $macros["fields"]->getTemplateForMacro("macro_nullField", $context, 54, $this->getSourceContext())->macro_nullField(...[]);
        yield "

    ";
        // line 56
        $context["project_link"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 57
            yield "        <span class=\"col-form-label d-inline-flex\">";
            yield CoreExtension::getAttribute($this->env, $this->source, ($context["parent"] ?? null), "getLink", [], "method", false, false, false, 57);
            yield "</span>
    ";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
        // line 59
        yield "    ";
        yield $macros["fields"]->getTemplateForMacro("macro_field", $context, 59, $this->getSourceContext())->macro_field(...["_project",         // line 61
($context["project_link"] ?? null), CoreExtension::getAttribute($this->env, $this->source,         // line 62
($context["parent"] ?? null), "getTypeName", [], "method", false, false, false, 62)]);
        // line 63
        yield "

    ";
        // line 65
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "isNewID", [($context["id"] ?? null)], "method", false, false, false, 65)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 66
            yield "        <input type=\"hidden\" name=\"projects_id\" value=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["projects_id"] ?? null), "html", null, true);
            yield "\">
        <input type=\"hidden\" name=\"is_recursive\" value=\"";
            // line 67
            yield (((($tmp = ($context["recursive"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (1) : (0));
            yield "\">
    ";
        }
        // line 69
        yield "
    ";
        // line 70
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownField", $context, 70, $this->getSourceContext())->macro_dropdownField(...["ProjectTask", "projecttasks_id",         // line 73
($context["projecttasks_id"] ?? null), __("As child of"), ["entity" => CoreExtension::getAttribute($this->env, $this->source,         // line 76
($context["item"] ?? null), "getEntityID", [], "method", false, false, false, 76), "condition" => ["glpi_projecttasks.projects_id" =>         // line 77
($context["projects_id"] ?? null)], "used" => [CoreExtension::getAttribute($this->env, $this->source,         // line 78
($context["item"] ?? null), "getID", [], "method", false, false, false, 78)]]]);
        // line 80
        yield "

    ";
        // line 82
        yield $macros["fields"]->getTemplateForMacro("macro_textField", $context, 82, $this->getSourceContext())->macro_textField(...["name", CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source,         // line 84
($context["item"] ?? null), "fields", [], "any", false, false, false, 84), "name", [], "any", false, false, false, 84), __("Name")]);
        // line 86
        yield "

    ";
        // line 88
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownField", $context, 88, $this->getSourceContext())->macro_dropdownField(...["ProjectTaskType", "projecttasktypes_id", CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source,         // line 91
($context["item"] ?? null), "fields", [], "any", false, false, false, 91), "projecttasktypes_id", [], "any", false, false, false, 91), _n("Type", "Types", 1)]);
        // line 93
        yield "

    ";
        // line 95
        $context["auto_status"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 96
            yield "        ";
            yield $macros["fields"]->getTemplateForMacro("macro_checkboxField", $context, 96, $this->getSourceContext())->macro_checkboxField(...["auto_projectstates", CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source,             // line 98
($context["item"] ?? null), "fields", [], "any", false, false, false, 98), "auto_projectstates", [], "any", false, false, false, 98), __("Automatically calculate"), ["helper" => __("When automatic computation is active, state is computed based on this task percent done. Don\x27t forget to define them in the general config."), "field_class" => "col-12 w-100", "label_class" => "col-auto", "input_class" => "col"]]);
            // line 106
            yield "
    ";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
        // line 108
        yield "
    ";
        // line 109
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownField", $context, 109, $this->getSourceContext())->macro_dropdownField(...["ProjectState", "projectstates_id", CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source,         // line 112
($context["item"] ?? null), "fields", [], "any", false, false, false, 112), "projectstates_id", [], "any", false, false, false, 112), _x("item", "State"), ["disabled" => CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source,         // line 115
($context["item"] ?? null), "fields", [], "any", false, false, false, 115), "auto_projectstates", [], "any", false, false, false, 115), "add_field_html" =>         // line 116
($context["auto_status"] ?? null)]]);
        // line 118
        yield "

    ";
        // line 120
        $context["auto_percent"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 121
            yield "        ";
            yield $macros["fields"]->getTemplateForMacro("macro_checkboxField", $context, 121, $this->getSourceContext())->macro_checkboxField(...["auto_percent_done", CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source,             // line 123
($context["item"] ?? null), "fields", [], "any", false, false, false, 123), "auto_percent_done", [], "any", false, false, false, 123), __("Automatically calculate"), ["helper" => __("When automatic computation is active, percentage is computed based on the average of all child task percent done."), "field_class" => "col-12 w-100", "label_class" => "col-auto", "input_class" => "col"]]);
            // line 131
            yield "
    ";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
        // line 133
        yield "
    ";
        // line 134
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownNumberField", $context, 134, $this->getSourceContext())->macro_dropdownNumberField(...["percent_done", CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source,         // line 136
($context["item"] ?? null), "fields", [], "any", false, false, false, 136), "percent_done", [], "any", false, false, false, 136), __("Percent done"), Twig\Extension\CoreExtension::merge(["value" => CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source,         // line 139
($context["item"] ?? null), "fields", [], "any", false, false, false, 139), "percent_done", [], "any", false, false, false, 139), "min" => 0, "max" => 100, "step" => 5, "unit" => "%", "add_field_html" =>         // line 144
($context["auto_percent"] ?? null)], (((($tmp = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source,         // line 145
($context["item"] ?? null), "fields", [], "any", false, false, false, 145), "auto_percent_done", [], "any", false, false, false, 145)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (["specific_tags" => ["disabled" => "disabled"]]) : ([])))]);
        // line 146
        yield "

    ";
        // line 148
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownYesNo", $context, 148, $this->getSourceContext())->macro_dropdownYesNo(...["is_milestone", CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source,         // line 150
($context["item"] ?? null), "fields", [], "any", false, false, false, 150), "is_milestone", [], "any", false, false, false, 150), __("Milestone")]);
        // line 152
        yield "

    ";
        // line 154
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "isNewItem", [], "method", false, false, false, 154)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 155
            yield "        ";
            $context["teammember_list"] = $this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("ProjectTaskTeamDropdown::show", ["teammember_list", []]);
            // line 156
            yield "        ";
            yield $macros["fields"]->getTemplateForMacro("macro_htmlField", $context, 156, $this->getSourceContext())->macro_htmlField(...["teammember_list",             // line 158
($context["teammember_list"] ?? null), __("Team members")]);
            // line 160
            yield "
    ";
        }
        // line 162
        yield "
    <div class=\"hr-text\">
        <i class=\"ti ti-calendar-event\"></i>
        <span>";
        // line 165
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Planning"), "html", null, true);
        yield "</span>
    </div>

    ";
        // line 168
        yield $macros["fields"]->getTemplateForMacro("macro_datetimeField", $context, 168, $this->getSourceContext())->macro_datetimeField(...["plan_start_date", CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source,         // line 170
($context["item"] ?? null), "fields", [], "any", false, false, false, 170), "plan_start_date", [], "any", false, false, false, 170), __("Planned start date")]);
        // line 172
        yield "

    ";
        // line 174
        yield $macros["fields"]->getTemplateForMacro("macro_datetimeField", $context, 174, $this->getSourceContext())->macro_datetimeField(...["real_start_date", CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source,         // line 176
($context["item"] ?? null), "fields", [], "any", false, false, false, 176), "real_start_date", [], "any", false, false, false, 176), __("Real start date")]);
        // line 178
        yield "

    ";
        // line 180
        yield $macros["fields"]->getTemplateForMacro("macro_datetimeField", $context, 180, $this->getSourceContext())->macro_datetimeField(...["plan_end_date", CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source,         // line 182
($context["item"] ?? null), "fields", [], "any", false, false, false, 182), "plan_end_date", [], "any", false, false, false, 182), __("Planned end date"), ["field_class" => ("col-12 col-sm-6 is_milestone " . (((($tmp = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source,         // line 185
($context["item"] ?? null), "fields", [], "any", false, false, false, 185), "is_milestone", [], "any", false, false, false, 185)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("d-none") : ("")))]]);
        // line 187
        yield "

    ";
        // line 189
        yield $macros["fields"]->getTemplateForMacro("macro_datetimeField", $context, 189, $this->getSourceContext())->macro_datetimeField(...["real_end_date", CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source,         // line 191
($context["item"] ?? null), "fields", [], "any", false, false, false, 191), "real_end_date", [], "any", false, false, false, 191), __("Real end date"), ["field_class" => ("col-12 col-sm-6 is_milestone " . (((($tmp = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source,         // line 194
($context["item"] ?? null), "fields", [], "any", false, false, false, 194), "is_milestone", [], "any", false, false, false, 194)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("d-none") : ("")))]]);
        // line 196
        yield "

    ";
        // line 198
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownArrayField", $context, 198, $this->getSourceContext())->macro_dropdownArrayField(...["planned_duration", CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source,         // line 200
($context["item"] ?? null), "fields", [], "any", false, false, false, 200), "planned_duration", [], "any", false, false, false, 200),         // line 201
($context["duration_dropdown_to_add"] ?? null), __("Planned duration"), ["field_class" => ("col-12 col-sm-6 is_milestone " . (((($tmp = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source,         // line 204
($context["item"] ?? null), "fields", [], "any", false, false, false, 204), "is_milestone", [], "any", false, false, false, 204)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("d-none") : (""))), "display_emptychoice" => true]]);
        // line 207
        yield "

    ";
        // line 209
        yield $macros["fields"]->getTemplateForMacro("macro_dropdownArrayField", $context, 209, $this->getSourceContext())->macro_dropdownArrayField(...["effective_duration", CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source,         // line 211
($context["item"] ?? null), "fields", [], "any", false, false, false, 211), "effective_duration", [], "any", false, false, false, 211),         // line 212
($context["duration_dropdown_to_add"] ?? null), __("Effective duration"), ["field_class" => ("col-12 col-sm-6 is_milestone " . (((($tmp = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source,         // line 215
($context["item"] ?? null), "fields", [], "any", false, false, false, 215), "is_milestone", [], "any", false, false, false, 215)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("d-none") : (""))), "display_emptychoice" => true]]);
        // line 218
        yield "

    ";
        // line 220
        if ((($tmp = ($context["id"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 221
            yield "        ";
            $context["ticket_duration"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 222
                yield "            <span class=\"fw-normal col-form-label d-inline-flex \">";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getFormattedDuration(($context["duration"] ?? null), false), "html", null, true);
                yield "</span>
        ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 224
            yield "        ";
            yield $macros["fields"]->getTemplateForMacro("macro_field", $context, 224, $this->getSourceContext())->macro_field(...["_ticket_duration",             // line 226
($context["ticket_duration"] ?? null), __("Tickets duration")]);
            // line 228
            yield "

        ";
            // line 230
            $context["total_duration"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
                // line 231
                yield "            <span class=\"fw-normal col-form-label d-inline-flex \">";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\DataHelpersExtension']->getFormattedDuration((($context["duration"] ?? null) + CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 231), "effective_duration", [], "any", false, false, false, 231)), false), "html", null, true);
                yield "</span>
        ";
                yield from [];
            })())) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 233
            yield "        ";
            yield $macros["fields"]->getTemplateForMacro("macro_field", $context, 233, $this->getSourceContext())->macro_field(...["_total_duration",             // line 235
($context["total_duration"] ?? null), __("Total duration")]);
            // line 237
            yield "
    ";
        }
        // line 239
        yield "
    <div class=\"hr-text\">
        <i class=\"ti ti-file-description\"></i>
        <span>";
        // line 242
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Details"), "html", null, true);
        yield "</span>
    </div>

    ";
        // line 245
        yield $macros["fields"]->getTemplateForMacro("macro_textareaField", $context, 245, $this->getSourceContext())->macro_textareaField(...["content", CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source,         // line 247
($context["item"] ?? null), "fields", [], "any", false, false, false, 247), "content", [], "any", false, false, false, 247), __("Description"), ["name" => "content", "enable_richtext" => true, "id" =>         // line 252
($context["content_field_id"] ?? null), "label_class" => "col-xxl-2", "input_class" => "col-xxl-10", "field_class" => "col-12"]]);
        // line 257
        yield "

    ";
        // line 259
        yield $macros["fields"]->getTemplateForMacro("macro_textareaField", $context, 259, $this->getSourceContext())->macro_textareaField(...["comment", CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source,         // line 261
($context["item"] ?? null), "fields", [], "any", false, false, false, 261), "comment", [], "any", false, false, false, 261), __("Comments"), ["label_class" => "col-xxl-2", "input_class" => "col-xxl-10", "field_class" => "col-12"]]);
        // line 268
        yield "

    <script type=\"module\">
        const form = \$(\x27#";
        // line 271
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["form_id"] ?? null), "html", null, true);
        yield "\x27);
        function projecttasktemplate_update(value) {
            \$.ajax({
                url: CFG_GLPI.root_doc + \"/ajax/projecttask.php\",
                type: \"POST\",
                data: {
                    projecttasktemplates_id: value
                }
            }).done(function(data) {
                // Set simple inputs
                form.find(\x27input[name=name]\x27).val(data.name);
                form.find(\x27textarea[name=comment]\x27).val(data.comments);

                // Set flatpickr dates
                form.find(\x27input[name=plan_start_date]\x27).parent()[0]._flatpickr.setDate(data.plan_start_date);
                form.find(\x27input[name=plan_end_date]\x27).parent()[0]._flatpickr.setDate(data.plan_end_date);
                form.find(\x27input[name=real_start_date]\x27).parent()[0]._flatpickr.setDate(data.real_start_date);
                form.find(\x27input[name=real_end_date]\x27).parent()[0]._flatpickr.setDate(data.real_end_date);

                // Set content
                setRichTextEditorContent(\"";
        // line 291
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["content_field_id"] ?? null), "html", null, true);
        yield "\", data.description);

                // Set dropdowns and dates
                form.find(\x27select[name=projecttasks_id]\x27).trigger(\"setValue\", data.projecttasks_id);
                form.find(\x27select[name=projectstates_id]\x27).trigger(\"setValue\", data.projectstates_id);
                form.find(\x27select[name=projecttasktypes_id]\x27).trigger(\"setValue\", data.projecttasktypes_id);
                form.find(\x27select[name=percent_done]\x27).trigger(\"setValue\", data.percent_done);
                form.find(\x27select[name=is_milestone]\x27).trigger(\"setValue\", data.is_milestone);
                form.find(\x27select[name=effective_duration]\x27).trigger(\"setValue\", data.effective_duration);
                form.find(\x27select[name=planned_duration]\x27).trigger(\"setValue\", data.planned_duration);
            });
        }
        form.find(\x27select[name=\"projecttasktemplates_id\"]\x27).on(\x27change\x27, function() {
            projecttasktemplate_update(this.value);
        });
        form.find(\"input[name=auto_projectstates]\").on(\x27change\x27, function() {
            \$(\"select[name=\x27projectstates_id\x27]\").prop(\x27disabled\x27, \$(\"input[name=\x27auto_projectstates\x27]\").eq(1).prop(\x27checked\x27));
        });
        form.find(\x27select[name=is_milestone]\x27).on(\x27change\x27, function() {
            \$(\x27.is_milestone\x27).toggleClass(\x27d-none\x27, Boolean(Number(this.value)));
        });
        form.find(\"input[name=auto_percent_done]\").on(\x27change\x27, function() {
            \$(\"select[name=\x27percent_done\x27]\").prop(\x27disabled\x27, \$(\"input[name=\x27auto_percent_done\x27]\").eq(1).prop(\x27checked\x27));
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
        return "pages/tools/project_task.html.twig";
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
        return array (  354 => 291,  331 => 271,  326 => 268,  324 => 261,  323 => 259,  319 => 257,  317 => 252,  316 => 247,  315 => 245,  309 => 242,  304 => 239,  300 => 237,  298 => 235,  296 => 233,  289 => 231,  287 => 230,  283 => 228,  281 => 226,  279 => 224,  272 => 222,  269 => 221,  267 => 220,  263 => 218,  261 => 215,  260 => 212,  259 => 211,  258 => 209,  254 => 207,  252 => 204,  251 => 201,  250 => 200,  249 => 198,  245 => 196,  243 => 194,  242 => 191,  241 => 189,  237 => 187,  235 => 185,  234 => 182,  233 => 180,  229 => 178,  227 => 176,  226 => 174,  222 => 172,  220 => 170,  219 => 168,  213 => 165,  208 => 162,  204 => 160,  202 => 158,  200 => 156,  197 => 155,  195 => 154,  191 => 152,  189 => 150,  188 => 148,  184 => 146,  182 => 145,  181 => 144,  180 => 139,  179 => 136,  178 => 134,  175 => 133,  170 => 131,  168 => 123,  166 => 121,  164 => 120,  160 => 118,  158 => 116,  157 => 115,  156 => 112,  155 => 109,  152 => 108,  147 => 106,  145 => 98,  143 => 96,  141 => 95,  137 => 93,  135 => 91,  134 => 88,  130 => 86,  128 => 84,  127 => 82,  123 => 80,  121 => 78,  120 => 77,  119 => 76,  118 => 73,  117 => 70,  114 => 69,  109 => 67,  104 => 66,  102 => 65,  98 => 63,  96 => 62,  95 => 61,  93 => 59,  86 => 57,  84 => 56,  79 => 54,  75 => 52,  73 => 50,  72 => 47,  71 => 44,  68 => 43,  61 => 42,  56 => 33,  54 => 39,  53 => 38,  51 => 37,  49 => 36,  47 => 34,  40 => 33,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "pages/tools/project_task.html.twig", "/var/www/html/glpi/templates/pages/tools/project_task.html.twig");
    }
}
