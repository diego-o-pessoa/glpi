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

/* components/itilobject/actors/field.html.twig */
class __TwigTemplate_0ba55172534b13c15f69b12973176ba3 extends Template
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
        $context["rand"] = Html::sanitizeDomId(((array_key_exists("rand", $context)) ? (Twig\Extension\CoreExtension::default(($context["rand"] ?? null), Twig\Extension\CoreExtension::random($this->env->getCharset()))) : (Twig\Extension\CoreExtension::random($this->env->getCharset()))));
        // line 34
        yield "
";
        // line 35
        $context["load_actors"] = (((CoreExtension::getAttribute($this->env, $this->source, ($context["params"] ?? null), "load_actors", [], "array", true, true, false, 35) &&  !(null === (($_v0 = ($context["params"] ?? null)) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0["load_actors"] ?? null) : null)))) ? ((($_v1 = ($context["params"] ?? null)) && is_array($_v1) || $_v1 instanceof ArrayAccess ? ($_v1["load_actors"] ?? null) : null)) : (true));
        // line 36
        $context["actors"] = (((($tmp = ($context["load_actors"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "getActorsForType", [($context["actortypeint"] ?? null), ($context["params"] ?? null)], "method", false, false, false, 36)) : ([]));
        // line 37
        yield "
";
        // line 38
        $context["required"] = false;
        // line 39
        if (((CoreExtension::getAttribute($this->env, $this->source, ($context["itiltemplate"] ?? null), "isMandatoryField", [("_users_id_" . ($context["actortype"] ?? null))], "method", false, false, false, 39) || CoreExtension::getAttribute($this->env, $this->source, ($context["itiltemplate"] ?? null), "isMandatoryField", [("_groups_id_" . ($context["actortype"] ?? null))], "method", false, false, false, 39)) || ((($context["actortype"] ?? null) == "assign") && CoreExtension::getAttribute($this->env, $this->source, ($context["itiltemplate"] ?? null), "isMandatoryField", [("_suppliers_id_" . ($context["actortype"] ?? null))], "method", false, false, false, 39)))) {
            // line 40
            yield "   ";
            $context["required"] = true;
        }
        // line 42
        yield "
";
        // line 43
        $context["is_actor_hidden"] = false;
        // line 44
        if (((CoreExtension::getAttribute($this->env, $this->source, ($context["itiltemplate"] ?? null), "isHiddenField", [("_users_id_" . ($context["actortype"] ?? null))], "method", false, false, false, 44) && CoreExtension::getAttribute($this->env, $this->source, ($context["itiltemplate"] ?? null), "isHiddenField", [("_groups_id_" . ($context["actortype"] ?? null))], "method", false, false, false, 44)) && ((($context["actortype"] ?? null) != "assign") || CoreExtension::getAttribute($this->env, $this->source, ($context["itiltemplate"] ?? null), "isHiddenField", [("_suppliers_id_" . ($context["actortype"] ?? null))], "method", false, false, false, 44)))) {
            // line 45
            yield "   ";
            $context["is_actor_hidden"] = true;
        }
        // line 47
        yield "
";
        // line 48
        $context["onchange"] = "";
        // line 49
        $context["allow_auto_submit"] = (((array_key_exists("allow_auto_submit", $context) &&  !(null === $context["allow_auto_submit"]))) ? ($context["allow_auto_submit"]) : (true));
        // line 50
        if (((($context["allow_auto_submit"] ?? null) && CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "isNewItem", [], "method", false, false, false, 50)) && (($context["actortype"] ?? null) == "requester"))) {
            // line 51
            yield "   ";
            $context["onchange"] = "e.target.form.submit();";
        }
        // line 53
        yield "
";
        // line 54
        if ((($tmp =  !($context["is_actor_hidden"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 55
            yield "   <select class=\"form-select\" multiple=\"true\" id=\"actor_";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
            yield "\" data-actor-type=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["actortype"] ?? null), "html", null, true);
            yield "\" data-testid=\"select-";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["actortype"] ?? null), "html", null, true);
            yield "\"
        ";
            // line 56
            yield (((($tmp = ($context["required"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("required") : (""));
            yield ">
   ";
            // line 57
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["actors"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["actor"]) {
                // line 58
                yield "      ";
                $context["unique_id"] = ((("user_opt_" . ($context["actortype"] ?? null)) . CoreExtension::getAttribute($this->env, $this->source, $context["actor"], "itemtype", [], "any", false, false, false, 58)) . CoreExtension::getAttribute($this->env, $this->source, $context["actor"], "items_id", [], "any", false, false, false, 58));
                // line 59
                yield "      ";
                $context["actor_use_notification"] = (((CoreExtension::getAttribute($this->env, $this->source, $context["actor"], "use_notification", [], "array", true, true, false, 59) &&  !(null === (($_v2 = $context["actor"]) && is_array($_v2) || $_v2 instanceof ArrayAccess ? ($_v2["use_notification"] ?? null) : null)))) ? ((($_v3 = $context["actor"]) && is_array($_v3) || $_v3 instanceof ArrayAccess ? ($_v3["use_notification"] ?? null) : null)) : (false));
                // line 60
                yield "      <option selected=\"true\" value=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((((($_v4 = $context["actor"]) && is_array($_v4) || $_v4 instanceof ArrayAccess ? ($_v4["itemtype"] ?? null) : null) . "_") . (($_v5 = $context["actor"]) && is_array($_v5) || $_v5 instanceof ArrayAccess ? ($_v5["items_id"] ?? null) : null)), "html", null, true);
                yield "\"
            data-itemtype=\"";
                // line 61
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v6 = $context["actor"]) && is_array($_v6) || $_v6 instanceof ArrayAccess ? ($_v6["itemtype"] ?? null) : null), "html", null, true);
                yield "\" data-items-id=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v7 = $context["actor"]) && is_array($_v7) || $_v7 instanceof ArrayAccess ? ($_v7["items_id"] ?? null) : null), "html", null, true);
                yield "\"
            data-use-notification=\"";
                // line 62
                yield (((($tmp = ($context["actor_use_notification"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (1) : (0));
                yield "\"
            data-default-email=\"";
                // line 63
                yield (((CoreExtension::getAttribute($this->env, $this->source, $context["actor"], "default_email", [], "array", true, true, false, 63) &&  !(null === (($_v8 = $context["actor"]) && is_array($_v8) || $_v8 instanceof ArrayAccess ? ($_v8["default_email"] ?? null) : null)))) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v9 = $context["actor"]) && is_array($_v9) || $_v9 instanceof ArrayAccess ? ($_v9["default_email"] ?? null) : null), "html", null, true)) : (""));
                yield "\"
            data-alternative-email=\"";
                // line 64
                yield (((CoreExtension::getAttribute($this->env, $this->source, $context["actor"], "alternative_email", [], "array", true, true, false, 64) &&  !(null === (($_v10 = $context["actor"]) && is_array($_v10) || $_v10 instanceof ArrayAccess ? ($_v10["alternative_email"] ?? null) : null)))) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v11 = $context["actor"]) && is_array($_v11) || $_v11 instanceof ArrayAccess ? ($_v11["alternative_email"] ?? null) : null), "html", null, true)) : (""));
                yield "\"
            ";
                // line 65
                if (((((($_v12 = $context["actor"]) && is_array($_v12) || $_v12 instanceof ArrayAccess ? ($_v12["itemtype"] ?? null) : null) == "User") && CoreExtension::getAttribute($this->env, $this->source, ($context["itiltemplate"] ?? null), "isHiddenField", [("_users_id_" . ($context["actortype"] ?? null))], "method", false, false, false, 65)) || (((($_v13 = $context["actor"]) && is_array($_v13) || $_v13 instanceof ArrayAccess ? ($_v13["itemtype"] ?? null) : null) == "Group") && CoreExtension::getAttribute($this->env, $this->source, ($context["itiltemplate"] ?? null), "isHiddenField", [("_groups_id_" . ($context["actortype"] ?? null))], "method", false, false, false, 65)))) {
                    // line 66
                    yield "               disabled=\"disabled\" style=\"display: none;\"
            ";
                }
                // line 68
                yield "            data-text=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v14 = $context["actor"]) && is_array($_v14) || $_v14 instanceof ArrayAccess ? ($_v14["text"] ?? null) : null), "html", null, true);
                yield "\" data-title=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v15 = $context["actor"]) && is_array($_v15) || $_v15 instanceof ArrayAccess ? ($_v15["title"] ?? null) : null), "html", null, true);
                yield "\" data-glpi-popover-source=\"content";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["unique_id"] ?? null), "html", null, true);
                yield "\">
         ";
                // line 69
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v16 = $context["actor"]) && is_array($_v16) || $_v16 instanceof ArrayAccess ? ($_v16["title"] ?? null) : null), "html", null, true);
                yield "
      </option>
   ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['actor'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 72
            yield "   </select>

   ";
            // line 74
            if ((( !CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "isNewItem", [], "method", false, false, false, 74) &&  !(((CoreExtension::getAttribute($this->env, $this->source, ($context["params"] ?? null), "template_preview", [], "array", true, true, false, 74) &&  !(null === (($_v17 = ($context["params"] ?? null)) && is_array($_v17) || $_v17 instanceof ArrayAccess ? ($_v17["template_preview"] ?? null) : null)))) ? ((($_v18 = ($context["params"] ?? null)) && is_array($_v18) || $_v18 instanceof ArrayAccess ? ($_v18["template_preview"] ?? null) : null)) : (false))) && ($context["canassigntome"] ?? null))) {
                // line 75
                yield "      ";
                yield Twig\Extension\CoreExtension::include($this->env, $context, "components/itilobject/actors/assign_to_me.html.twig");
                yield "
   ";
            }
            // line 77
            yield "
    ";
            // line 87
            yield "    ";
            $context["safe_item_fields"] = Twig\Extension\CoreExtension::filter($this->env, $this->env->hasExtension(\Twig\Extension\SandboxExtension::class) && $this->env->getExtension(\Twig\Extension\SandboxExtension::class)->isSandboxed($this->source), CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 87), function ($__value__, $__key__) use ($context, $macros) { $context["value"] = $__value__; $context["key"] = $__key__; return (($context["key"] ?? null) != "content"); });
            // line 88
            yield "    <script type=\"module\" defer>
        const actorytype = \x27";
            // line 89
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["actortype"] ?? null), "js"), "html", null, true);
            yield "\x27;

        // function to display an option in the list or the selected input
        function genericTemplate(option = {}, is_selection = false) {
            const element   = \$(option.element);
            const itemtype  = element.data(\x27itemtype\x27) ?? option.itemtype;
            const items_id  = element.data(\x27items-id\x27) ?? option.items_id;
            let text        = _.escape(element.data(\x27text\x27) ?? option.text ?? \x27\x27);
            const title     = _.escape(element.data(\x27title\x27) ?? option.title ?? \x27\x27);
            const use_notif = element.data(\x27use-notification\x27) ?? option.use_notification ?? 1;
            const alt_email = element.data(\x27alternative-email\x27) ?? option.alternative_email ?? \x27\x27;

            let icon = \"\";
            let fk   = \"\";
            switch (itemtype) {
                case \"User\":
                    if (items_id == 0) {
                        text = alt_email;
                        icon = `<i class=\"ti ti-mail mx-1\" title=\"";
            // line 107
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Direct email"), "html"), "js"), "html", null, true);
            yield "\"></i>`;
                    } else {
                        icon = `<i class=\"ti ti-user mx-1\" title=\"";
            // line 109
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("User"), "html"), "js"), "html", null, true);
            yield "\"></i>`;
                    }
                    if (\"";
            // line 111
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["actortype"] ?? null), "js"), "html", null, true);
            yield "\" == \"assign\") {
                        fk = \"users_id_assign\";
                    } else if (\"";
            // line 113
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["actortype"] ?? null), "js"), "html", null, true);
            yield "\" == \"requester\") {
                        fk = \"users_id_requester\";
                    } else if (\"";
            // line 115
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["actortype"] ?? null), "js"), "html", null, true);
            yield "\" == \"observer\") {
                        fk = \"users_id_observer\";
                    }
                    break;
                case \"Group\":
                    icon = `<i class=\"ti ti-users mx-1\" title=\"";
            // line 120
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("Group"), "html"), "js"), "html", null, true);
            yield "\"></i>`;
                    if (\"";
            // line 121
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["actortype"] ?? null), "js"), "html", null, true);
            yield "\" == \"assign\") {
                        fk = \"groups_id_assign\";
                    } else if (\"";
            // line 123
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["actortype"] ?? null), "js"), "html", null, true);
            yield "\" == \"requester\") {
                        fk = \"groups_id_requester\";
                    } else if (\"";
            // line 125
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["actortype"] ?? null), "js"), "html", null, true);
            yield "\" == \"observer\") {
                        fk = \"groups_id_observer\";
                    }
                    break;
                case \"Supplier\":
                    icon = `<i class=\"ti ti-package mx-1\" title=\"";
            // line 130
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\ItemtypeExtension']->getItemtypeName("Supplier"), "html"), "js"), "html", null, true);
            yield "\"></i>`;
                    fk   = \"suppliers_id_assign\";
                    break;
            }

            let actions = \"\";
            ";
            // line 136
            if ((($tmp = ($context["canupdate"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 137
                yield "            if ([\x27User\x27, \x27Supplier\x27, \x27Email\x27].includes(itemtype)
                && is_selection) {
                const icon_class = use_notif ? \"ti-bell-filled\" : \"ti-bell\";
                actions = `
                    <button class=\"btn btn-sm btn-ghost-secondary edit-notify-user\"
                              data-bs-toggle=\"tooltip\" data-bs-placement=\"top\"
                              title=\"";
                // line 143
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Email followup"), "html"), "js"), "html", null, true);
                yield "\"
                              type=\"button\">
                        <i class=\"ti \${icon_class} notify-icon\"></i>
                    </button>`;
            }
            ";
            }
            // line 149
            yield "
            // manage specific display for tree data (like groups)
            let indent = \"\";
            if (!is_selection && \"level\" in option && option.level > 1) {
                for (let index = 1; index < option.level; index++) {
                    indent = \"&nbsp;&nbsp;&nbsp;\"+indent;
                }
                indent = indent+\"&raquo;\";
            }

            // prepare html for option element
            text = (is_selection && itemtype == \"Group\") ? title : text;
            const option_text    = `<span class=\"actor_text\">\${text}</span>`;
            const option_element = \$(`<span class=\"actor_entry\" data-itemtype=\"\${_.escape(itemtype)}\" data-items-id=\"\${_.escape(items_id)}\" data-actortype=\"\${_.escape(actorytype)}\" data-testid=\"actor-entry-\${_.escape(actorytype)}-\${_.escape(itemtype)}-\${_.escape(items_id)}\">\${indent}\${icon}\${option_text}\${actions}</span>`);

            if (is_selection && itemtype == \"User\") {
                const unique_id = \"user_opt_\" + actor_select.attr(\x27data-actor-type\x27) + \"User\" + items_id;
                \$.ajax({
                    url: `\${CFG_GLPI.root_doc}/ajax/comments.php`,
                    type: \x27POST\x27,
                    data: {
                        \x27itemtype\x27: \x27User\x27,
                        \x27value\x27: items_id,
                    }
                }).then((result) => {
                    ";
            // line 175
            yield "                    if (result) {
                        actor_select.parent().append(\x27<\x27 + `div id=\"content\${_.escape(unique_id)}\" style=\"display: none;\">\${result}<` + \x27/div>\x27);
                        option_element.attr(\x27data-glpi-popover-source\x27, `content\${unique_id}`);
                    }
                });
            }

            if (is_selection && itemtype == \"Group\") {
                const unique_id = \"group_opt_\" + actor_select.attr(\x27data-actor-type\x27) + \"Group\" + items_id;
                \$.ajax({
                    url: `\${CFG_GLPI.root_doc}/ajax/comments.php`,
                    type: \x27POST\x27,
                    data: {
                        \x27itemtype\x27: \x27Group\x27,
                        \x27value\x27: items_id,
                    }
                }).then((result) => {
                    ";
            // line 193
            yield "                    if (result) {
                        actor_select.parent().append(\x27<\x27 + `div id=\"content\${_.escape(unique_id)}\" style=\"display: none;\">\${result}<` + \x27/div>\x27);
                        option_element.attr(\x27data-glpi-popover-source\x27, `content\${unique_id}`);
                    }
                });
            }

            if (is_selection && itemtype == \"Supplier\") {
                const unique_id = \"supplier_opt_\" + actor_select.attr(\x27data-actor-type\x27) + \"Supplier\" + items_id;
                \$.ajax({
                    url: `\${CFG_GLPI.root_doc}/ajax/comments.php`,
                    type: \x27POST\x27,
                    data: {
                        \x27itemtype\x27: \x27Supplier\x27,
                        \x27value\x27: items_id,
                    }
                }).then((result) => {
                    ";
            // line 211
            yield "                    if (result) {
                        actor_select.parent().append(\x27<\x27 + `div id=\"content\${_.escape(unique_id)}\" style=\"display: none;\">\${result}<` + \x27/div>\x27);
                        option_element.attr(\x27data-glpi-popover-source\x27, `content\${unique_id}`);
                    }
                });
            }

            // manage ticket information (number of assigned ticket for an actor)
            if (is_selection && itemtype != \"Email\") {
                let label = \x27\x27;
                if (\"";
            // line 221
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["actortype"] ?? null), "js"), "html", null, true);
            yield "\" == \"assign\") {
                    label = \"";
            // line 222
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Number of tickets already assigned"), "js"), "html", null, true);
            yield "\";
                } else if (\"";
            // line 223
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["actortype"] ?? null), "js"), "html", null, true);
            yield "\" == \"requester\") {
                    label = \"";
            // line 224
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("Number of tickets as requester"), "js"), "html", null, true);
            yield "\";
                }
                const existing_element = \$(`
                    <span class=\"assign_infos ms-1\" title=\"\${_.escape(label)}\"
                        data-bs-toggle=\"tooltip\" data-bs-placement=\"top\"
                        data-id=\"\${_.escape(items_id)}\" data-fk=\"\${_.escape(fk)}\">
                        <span class=\"spinner-border spinner-border-sm\" role=\"status\" aria-hidden=\"true\"></span>
                    </span>
                `);
                option_element.append(existing_element);

                \$.get(`\${CFG_GLPI.root_doc}/ajax/actorinformation.php`, {
                    [fk]: items_id,
                    only_number: true,
                }).done((number) => {
                    ";
            // line 240
            yield "                    const badge = number.length > 0 ? `<span class=\"badge bg-secondary-lt\">\${number}</span>` : \x27\x27;
                    existing_element.html(badge);
                });
            }

            return option_element;
        }

        const select2_width = \"";
            // line 248
            yield (((($tmp = ($context["canassigntome"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("calc(100% - 30px)") : ("100%"));
            yield "\";

        const actor_select = \$(\"#actor_";
            // line 250
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
            yield "\");
        actor_select.select2({
            tags: true,
            width: select2_width,
            tokenSeparators: [\x27,\x27, \x27 \x27],
            containerCssClass: \x27actor-field\x27,
            templateSelection: (option) => genericTemplate(option, true),
            templateResult: (option) => genericTemplate(option, false),
            disabled: ";
            // line 258
            yield (((($tmp = ($context["canupdate"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("false") : ("true"));
            yield ",
            createTag: (params) => {
                const term = \$.trim(params.term);

                if (term === \x27\x27) {
                    return null;
                }

                // Don\x27t offset to create a tag if it\x27s not an email
                if (!new RegExp(";
            // line 267
            yield json_encode(Twig\Extension\CoreExtension::slice($this->env->getCharset(), Twig\Extension\CoreExtension::constant("Toolbox::ACTOR_EMAIL_VALIDATION_REGEX"), 1,  -2));
            yield ", \x27u\x27).test(term)) {
                    // Return null to disable tag creation
                    return null;
                }

                return {
                    id: term,
                    text: term,
                    itemtype: \"User\",
                    items_id: 0,
                    use_notification: 1,
                    alternative_email: term,
                }
            },
            ajax: {
                url: `\${CFG_GLPI.root_doc}/ajax/actors.php`,
                datatype: \x27json\x27,
                type: \x27POST\x27,
                delay:250,
                data: (params) => {
                    const is_new_item = ";
            // line 287
            yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "isNewItem", [], "method", false, false, false, 287)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("true") : ("false"));
            yield ";
                    return {
                        action: \x27getActors\x27,
                        actortype: actorytype,
                        users_right: \x27";
            // line 291
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((((array_key_exists("users_right", $context) &&  !(null === $context["users_right"]))) ? ($context["users_right"]) : ("all")), "js"), "html", null, true);
            yield "\x27,
                        entity_restrict: (window.actors.requester.length === 0 && is_new_item) ? -1 : ";
            // line 292
            yield json_encode(($context["entities_id"] ?? null));
            yield ",
                        searchText: params.term,
                        _idor_token: \x27";
            // line 294
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Session::getNewIDORToken(CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "getType", [], "method", false, false, false, 294), ["users_right" => (((array_key_exists("users_right", $context) &&  !(null === $context["users_right"]))) ? ($context["users_right"]) : ("all"))]), "js"), "html", null, true);
            yield "\x27,
                        itiltemplate_class: \x27";
            // line 295
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["itiltemplate"] ?? null), "getType", [], "method", false, false, false, 295), "js"), "html", null, true);
            yield "\x27,
                        itiltemplates_id: ";
            // line 296
            yield json_encode((((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["itiltemplate"] ?? null), "fields", [], "any", false, true, false, 296), "id", [], "array", true, true, false, 296) &&  !(null === (($_v19 = CoreExtension::getAttribute($this->env, $this->source, ($context["itiltemplate"] ?? null), "fields", [], "any", false, false, false, 296)) && is_array($_v19) || $_v19 instanceof ArrayAccess ? ($_v19["id"] ?? null) : null)))) ? ((($_v20 = CoreExtension::getAttribute($this->env, $this->source, ($context["itiltemplate"] ?? null), "fields", [], "any", false, false, false, 296)) && is_array($_v20) || $_v20 instanceof ArrayAccess ? ($_v20["id"] ?? null) : null)) : (0)));
            yield ",
                        itemtype: \x27";
            // line 297
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "getType", [], "method", false, false, false, 297), "js"), "html", null, true);
            yield "\x27,
                        items_id: ";
            // line 298
            yield json_encode((((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "isNewItem", [], "method", false, false, false, 298)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ( -1) : ((($_v21 = CoreExtension::getAttribute($this->env, $this->source, ($context["item"] ?? null), "fields", [], "any", false, false, false, 298)) && is_array($_v21) || $_v21 instanceof ArrayAccess ? ($_v21["id"] ?? null) : null))));
            yield ",
                        item: ";
            // line 299
            yield json_encode(($context["safe_item_fields"] ?? null));
            yield ",
                        returned_itemtypes: ";
            // line 300
            yield json_encode((((array_key_exists("returned_itemtypes", $context) &&  !(null === $context["returned_itemtypes"]))) ? ($context["returned_itemtypes"]) : (["User", "Group", "Supplier"])));
            yield ",
                        page: params.page || 1
                    };
                },
            }
        }).on(\x27select2:open\x27, () => {
            // Close popovers
            \$(\x27.popover\x27).popover(\x27hide\x27);
        });

        actor_select.parent().popover({
            selector: \x27[data-glpi-popover-source]\x27,
            container: actor_select.parent(),
            html: true,
            sanitize: false,
            trigger: \x27hover\x27,
            delay: {
                hide: 300
            },
            content: (el) => {
                // Close other popovers
                \$(\x27.popover\x27).popover(\x27hide\x27);
                return \$(\x27#\x27 + \$(el).attr(\x27data-glpi-popover-source\x27)).html();
            }
        }).on(\x27hide.bs.popover\x27, () => {
            // prevent closing popover when user card is hover
            if (\$(\x27.user-info-card:hover\x27).length > 0) {
                return false;
            }
            // prevent closing popover when group card is hover
            if (\$(\x27.group-info-card:hover\x27).length > 0) {
                return false;
            }
            // prevent closing popover when group card is hover
            if (\$(\x27.supplier-info-card:hover\x27).length > 0) {
                return false;
            }
        });

        // when the mouse leave the info card in the popover, close it
        \$(document).on(\x27mouseleave\x27, \x27.user-info-card, .group-info-card, .supplier-info-card\x27, (e) => {
            const popover = \$(e.target).closest(\x27.popover\x27);
            if (popover.length === 0) {
                return;
            }

            setTimeout(() => {
                // Find the trigger element and hide its popover
                const popover_element = \$(\x27[data-glpi-popover-source][aria-describedby=\"\x27 + CSS.escape(popover.attr(\x27id\x27)) + \x27\"]\x27);
                if (popover_element.length > 0) {
                    const popover_instance = bootstrap.Popover.getInstance(popover_element[0]);
                    popover_instance.hide();
                }
            }, 300);
        });

        // manage actors change
        function updateActors() {
            const data = \$(\"#actor_";
            // line 358
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
            yield "\").select2(\x27data\x27);

            const new_actors = [];
            data.forEach((selection) => {
                const element = \$(selection.element);

                let itemtype  = selection.itemtype ?? element.data(\x27itemtype\x27);
                const items_id  = selection.items_id ?? element.data(\x27items-id\x27);
                let use_notif = selection.use_notification  ?? element.data(\x27use-notification\x27)  ?? false;
                const def_email = selection.default_email ?? element.data(\x27default-email\x27) ?? \x27\x27;
                let alt_email = selection.alternative_email ?? element.data(\x27alternative-email\x27) ?? \x27\x27;

                if (itemtype == \"Email\") {
                    itemtype  = \"User\";
                    use_notif = true;
                    alt_email = selection.id;
                }

                new_actors.push({
                    itemtype: itemtype,
                    items_id: items_id,
                    use_notification: use_notif,
                    default_email: def_email,
                    alternative_email: alt_email,
                });
            });

            window.actors[actorytype] = new_actors;

            saveActorsToDom();
        }

        \$(\"#actor_";
            // line 390
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
            yield "\").on(\x27select2:select\x27, (e) => {
            updateActors();
            ";
            // line 392
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["onchange"] ?? null), "html", null, true);
            yield "
        });
        \$(\"#actor_";
            // line 394
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
            yield "\").on(\x27select2:unselect\x27, (e) => {
            updateActors();
            ";
            // line 396
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["onchange"] ?? null), "html", null, true);
            yield "
        });

        // intercept event for edit notification button
        document.addEventListener(\x27click\x27, event => {
            if (event.target.closest(\"#actor_";
            // line 401
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
            yield " + .select2 .edit-notify-user\")) {
                return openNotifyModal(event);
            }
            // if a click on assign info is detected prevent opening of select2
            if (event.target.closest(\"#actor_";
            // line 405
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
            yield " + .select2 .assign_infos\")) {
                event.stopPropagation();
            }
        }, {capture: true})
        document.addEventListener(\x27keydown\x27, event => {
            if (event.target.closest(\"#actor_";
            // line 410
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["rand"] ?? null), "html", null, true);
            yield " + .select2 .edit-notify-user\")
                && event.key == \"Enter\") {
                return openNotifyModal(event);
            }
        }, {capture: true})

        ";
            // line 416
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["itiltemplate"] ?? null), "isHiddenField", [("_users_id_" . ($context["actortype"] ?? null))], "method", false, false, false, 416)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 417
                yield "        \$(\".actor_entry[data-itemtype=\\\"User\\\"][data-actortype=\\\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["actortype"] ?? null), "css"), "js"), "html", null, true);
                yield "\\\"]\").parent().css(\"display\", \"none\");
        ";
            }
            // line 419
            yield "        ";
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["itiltemplate"] ?? null), "isHiddenField", [("_groups_id_" . ($context["actortype"] ?? null))], "method", false, false, false, 419)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 420
                yield "        \$(\".actor_entry[data-itemtype=\\\"Group\\\"][data-actortype=\\\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["actortype"] ?? null), "css"), "js"), "html", null, true);
                yield "\\\"]\").parent().css(\"display\", \"none\");
        ";
            }
            // line 422
            yield "    </script>
";
        }
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "components/itilobject/actors/field.html.twig";
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
        return array (  652 => 422,  646 => 420,  643 => 419,  637 => 417,  635 => 416,  626 => 410,  618 => 405,  611 => 401,  603 => 396,  598 => 394,  593 => 392,  588 => 390,  553 => 358,  492 => 300,  488 => 299,  484 => 298,  480 => 297,  476 => 296,  472 => 295,  468 => 294,  463 => 292,  459 => 291,  452 => 287,  429 => 267,  417 => 258,  406 => 250,  401 => 248,  391 => 240,  373 => 224,  369 => 223,  365 => 222,  361 => 221,  349 => 211,  330 => 193,  311 => 175,  284 => 149,  275 => 143,  267 => 137,  265 => 136,  256 => 130,  248 => 125,  243 => 123,  238 => 121,  234 => 120,  226 => 115,  221 => 113,  216 => 111,  211 => 109,  206 => 107,  185 => 89,  182 => 88,  179 => 87,  176 => 77,  170 => 75,  168 => 74,  164 => 72,  155 => 69,  146 => 68,  142 => 66,  140 => 65,  136 => 64,  132 => 63,  128 => 62,  122 => 61,  117 => 60,  114 => 59,  111 => 58,  107 => 57,  103 => 56,  94 => 55,  92 => 54,  89 => 53,  85 => 51,  83 => 50,  81 => 49,  79 => 48,  76 => 47,  72 => 45,  70 => 44,  68 => 43,  65 => 42,  61 => 40,  59 => 39,  57 => 38,  54 => 37,  52 => 36,  50 => 35,  47 => 34,  45 => 33,  42 => 32,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "components/itilobject/actors/field.html.twig", "/var/www/html/glpi/templates/components/itilobject/actors/field.html.twig");
    }
}
