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

/* __string_template__2a5f7ae1e258a6621d653bb678cc573f */
class __TwigTemplate_ebad1e810e018ee12ef38d9971d5cff7 extends Template
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
        // line 1
        yield "            <script type=\"module\">
                (async () => {
                    await import(\x27/js/modules/Dashboard/Dashboard.js\x27);

                    const target = GLPI.Dashboard.getActiveDashboard() ?
                        GLPI.Dashboard.getActiveDashboard().element.find(\x27#";
        // line 6
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["chart_id"] ?? null), "html", null, true);
        yield " .chart\x27)
                        : \$(\x27#";
        // line 7
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["chart_id"] ?? null), "html", null, true);
        yield " .chart\x27);
                    const chart_options = ";
        // line 8
        yield json_encode(($context["options"] ?? null));
        yield ";
                    const palette = ";
        // line 9
        yield json_encode(($context["palette"] ?? null));
        yield ";
                    \$.each(chart_options.series, function (index, serie) {
                        if (";
        // line 11
        yield (((($tmp = ($context["distributed"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("true") : ("false"));
        yield ") {
                            serie[\x27itemStyle\x27] = {
                                ...serie[\x27itemStyle\x27],
                                \x27color\x27: (param) => palette[param.dataIndex % palette.length]
                            }
                        }
                        serie[\x27label\x27] = {
                            ...serie[\x27label\x27],
                            \x27formatter\x27: (param) => param.data.value == 0 ? \x27\x27 : param.data.value
                        };
                    });
                    if (";
        // line 22
        yield (((($tmp = ($context["horizontal"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("true") : ("false"));
        yield ") {
                        chart_options[\x27xAxis\x27] = {
                            ...chart_options[\x27xAxis\x27],
                            \x27axisLabel\x27: {
                                \x27formatter\x27: (value) => {
                                    if (value < 1e3) {
                                        return value;
                                    } else if (value < 1e6) {
                                        return value / 1e3 + \"K\";
                                    } else {
                                        return value / 1e6 + \"M\";
                                    }
                                }
                            }
                        };
                    }

                    const myChart = echarts.init(target[0]);
                    myChart.setOption(chart_options);
                    myChart
                        .on(\x27click\x27, function (params) {
                            const data_url = _.get(params, \x27data.url\x27, \x27\x27);
                            if (data_url.length > 0) {
                                window.location.href = data_url;
                            }
                        });

                    target.on(\x27mouseover\x27, () => {
                        myChart.setOption({\x27toolbox\x27: {\x27show\x27: true}});
                    }).on(\x27mouseout\x27, () => {
                        myChart.setOption({\x27toolbox\x27: {\x27show\x27: false}});
                    });
                })();
            </script>";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "__string_template__2a5f7ae1e258a6621d653bb678cc573f";
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
        return array (  80 => 22,  66 => 11,  61 => 9,  57 => 8,  53 => 7,  49 => 6,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "__string_template__2a5f7ae1e258a6621d653bb678cc573f", "");
    }
}
