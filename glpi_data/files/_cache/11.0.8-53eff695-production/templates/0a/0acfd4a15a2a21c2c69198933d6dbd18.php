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

/* layout/parts/dcbreadcrumbs.html.twig */
class __TwigTemplate_e85001690800e499c21955e3ae88d0ec extends Template
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
        if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["breadcrumbs"] ?? null)) > 0)) {
            // line 34
            yield "    <div class=\"row\">
        ";
            // line 35
            $context["previous_location_id"] = null;
            // line 36
            yield "        ";
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["breadcrumbs"] ?? null));
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
            foreach ($context['_seq'] as $context["_key"] => $context["breadcrumb_item"]) {
                // line 37
                yield "            <div class=\"col-auto p-1\">
                ";
                // line 38
                yield (($_v0 = $context["breadcrumb_item"]) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0["link"] ?? null) : null);
                yield "

                ";
                // line 40
                if (CoreExtension::getAttribute($this->env, $this->source, $context["breadcrumb_item"], "side", [], "array", true, true, false, 40)) {
                    // line 41
                    yield "                    ";
                    if (((($_v1 = $context["breadcrumb_item"]) && is_array($_v1) || $_v1 instanceof ArrayAccess ? ($_v1["side"] ?? null) : null) == Twig\Extension\CoreExtension::constant("PDU_Rack::SIDE_LEFT"))) {
                        // line 42
                        yield "                        ";
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("On left"), "html", null, true);
                        yield "
                    ";
                    } elseif (((($_v2 =                     // line 43
$context["breadcrumb_item"]) && is_array($_v2) || $_v2 instanceof ArrayAccess ? ($_v2["side"] ?? null) : null) == Twig\Extension\CoreExtension::constant("PDU_Rack::SIDE_RIGHT"))) {
                        // line 44
                        yield "                        ";
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("On right"), "html", null, true);
                        yield "
                    ";
                    } elseif (((($_v3 =                     // line 45
$context["breadcrumb_item"]) && is_array($_v3) || $_v3 instanceof ArrayAccess ? ($_v3["side"] ?? null) : null) == Twig\Extension\CoreExtension::constant("PDU_Rack::SIDE_TOP"))) {
                        // line 46
                        yield "                        ";
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("On top"), "html", null, true);
                        yield "
                    ";
                    } elseif (((($_v4 =                     // line 47
$context["breadcrumb_item"]) && is_array($_v4) || $_v4 instanceof ArrayAccess ? ($_v4["side"] ?? null) : null) == Twig\Extension\CoreExtension::constant("PDU_Rack::SIDE_BOTTOM"))) {
                        // line 48
                        yield "                        ";
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(__("On bottom"), "html", null, true);
                        yield "
                    ";
                    }
                    // line 50
                    yield "                ";
                }
                // line 51
                yield "
                ";
                // line 52
                if (CoreExtension::getAttribute($this->env, $this->source, $context["breadcrumb_item"], "position", [], "array", true, true, false, 52)) {
                    // line 53
                    yield "                    ";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::sprintf(__("(U%d)"), (($_v5 = $context["breadcrumb_item"]) && is_array($_v5) || $_v5 instanceof ArrayAccess ? ($_v5["position"] ?? null) : null)), "html", null, true);
                    yield "
                ";
                }
                // line 55
                yield "
                ";
                // line 56
                if (( !(null === (($_v6 = $context["breadcrumb_item"]) && is_array($_v6) || $_v6 instanceof ArrayAccess ? ($_v6["location"] ?? null) : null)) && ((($_v7 = (($_v8 = $context["breadcrumb_item"]) && is_array($_v8) || $_v8 instanceof ArrayAccess ? ($_v8["location"] ?? null) : null)) && is_array($_v7) || $_v7 instanceof ArrayAccess ? ($_v7["id"] ?? null) : null) != ($context["previous_location_id"] ?? null)))) {
                    // line 57
                    yield "                    <br>
                    <span class=\"p-0 float-left badge bg-blue-lt d-inline-block text-truncate\"
                          data-bs-toggle=\"tooltip\"
                          title=\"";
                    // line 60
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v9 = (($_v10 = $context["breadcrumb_item"]) && is_array($_v10) || $_v10 instanceof ArrayAccess ? ($_v10["location"] ?? null) : null)) && is_array($_v9) || $_v9 instanceof ArrayAccess ? ($_v9["completename"] ?? null) : null), "html", null, true);
                    yield "\"
                          style=\"max-width: 100px;\">
                        <i class=\"";
                    // line 62
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Glpi\Application\View\Extension\PhpExtension']->call("Location::getIcon"), "html", null, true);
                    yield "\"></i>
                        ";
                    // line 63
                    if (((($_v11 = (($_v12 = $context["breadcrumb_item"]) && is_array($_v12) || $_v12 instanceof ArrayAccess ? ($_v12["location"] ?? null) : null)) && is_array($_v11) || $_v11 instanceof ArrayAccess ? ($_v11["locations_id"] ?? null) : null) == ($context["previous_location_id"] ?? null))) {
                        // line 64
                        yield "                           ";
                        if ((($tmp =  !(null === ($context["previous_location_id"] ?? null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                            // line 65
                            yield "                              &gt;
                           ";
                        }
                        // line 67
                        yield "                           ";
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v13 = (($_v14 = $context["breadcrumb_item"]) && is_array($_v14) || $_v14 instanceof ArrayAccess ? ($_v14["location"] ?? null) : null)) && is_array($_v13) || $_v13 instanceof ArrayAccess ? ($_v13["name"] ?? null) : null), "html", null, true);
                        yield "
                        ";
                    } else {
                        // line 69
                        yield "                           ";
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v15 = (($_v16 = $context["breadcrumb_item"]) && is_array($_v16) || $_v16 instanceof ArrayAccess ? ($_v16["location"] ?? null) : null)) && is_array($_v15) || $_v15 instanceof ArrayAccess ? ($_v15["completename"] ?? null) : null), "html", null, true);
                        yield "
                        ";
                    }
                    // line 71
                    yield "                    </span>
                    ";
                    // line 72
                    $context["previous_location_id"] = (($_v17 = (($_v18 = $context["breadcrumb_item"]) && is_array($_v18) || $_v18 instanceof ArrayAccess ? ($_v18["location"] ?? null) : null)) && is_array($_v17) || $_v17 instanceof ArrayAccess ? ($_v17["id"] ?? null) : null);
                    // line 73
                    yield "                ";
                }
                // line 74
                yield "            </div>
            ";
                // line 75
                if ((CoreExtension::getAttribute($this->env, $this->source, $context["loop"], "last", [], "any", false, false, false, 75) == false)) {
                    // line 76
                    yield "                <div class=\"col-auto p-1\">
                    &gt;
                </div>
            ";
                }
                // line 80
                yield "        ";
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
            unset($context['_seq'], $context['_key'], $context['breadcrumb_item'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 81
            yield "    </div>
";
        }
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "layout/parts/dcbreadcrumbs.html.twig";
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
        return array (  197 => 81,  183 => 80,  177 => 76,  175 => 75,  172 => 74,  169 => 73,  167 => 72,  164 => 71,  158 => 69,  152 => 67,  148 => 65,  145 => 64,  143 => 63,  139 => 62,  134 => 60,  129 => 57,  127 => 56,  124 => 55,  118 => 53,  116 => 52,  113 => 51,  110 => 50,  104 => 48,  102 => 47,  97 => 46,  95 => 45,  90 => 44,  88 => 43,  83 => 42,  80 => 41,  78 => 40,  73 => 38,  70 => 37,  52 => 36,  50 => 35,  47 => 34,  45 => 33,  42 => 32,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "layout/parts/dcbreadcrumbs.html.twig", "/var/www/html/glpi/templates/layout/parts/dcbreadcrumbs.html.twig");
    }
}
