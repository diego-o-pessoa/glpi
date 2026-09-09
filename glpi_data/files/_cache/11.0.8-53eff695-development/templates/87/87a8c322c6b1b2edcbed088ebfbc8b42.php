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
class __TwigTemplate_26de7c671d2b7300797a40274a5b4dad extends Template
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
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["chart_id"]) || array_key_exists("chart_id", $context) ? $context["chart_id"] : (function () { throw new RuntimeError('Variable "chart_id" does not exist.', 6, $this->source); })()), "html", null, true);
        yield " .chart\x27)
                        : \$(\x27#";
        // line 7
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["chart_id"]) || array_key_exists("chart_id", $context) ? $context["chart_id"] : (function () { throw new RuntimeError('Variable "chart_id" does not exist.', 7, $this->source); })()), "html", null, true);
        yield " .chart\x27);
                    const chart_options = ";
        // line 8
        yield json_encode((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 8, $this->source); })()));
        yield ";
                    const palette = ";
        // line 9
        yield json_encode((isset($context["palette"]) || array_key_exists("palette", $context) ? $context["palette"] : (function () { throw new RuntimeError('Variable "palette" does not exist.', 9, $this->source); })()));
        yield ";
                    \$.each(chart_options.series, function (index, serie) {
                        if (";
        // line 11
        yield (((($tmp = (isset($context["distributed"]) || array_key_exists("distributed", $context) ? $context["distributed"] : (function () { throw new RuntimeError('Variable "distributed" does not exist.', 11, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("true") : ("false"));
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
        yield (((($tmp = (isset($context["horizontal"]) || array_key_exists("horizontal", $context) ? $context["horizontal"] : (function () { throw new RuntimeError('Variable "horizontal" does not exist.', 22, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("true") : ("false"));
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
        return new Source("            <script type=\"module\">
                (async () => {
                    await import(\x27/js/modules/Dashboard/Dashboard.js\x27);

                    const target = GLPI.Dashboard.getActiveDashboard() ?
                        GLPI.Dashboard.getActiveDashboard().element.find(\x27#{{ chart_id }} .chart\x27)
                        : \$(\x27#{{ chart_id }} .chart\x27);
                    const chart_options = {{ options|json_encode|raw }};
                    const palette = {{ palette|json_encode|raw }};
                    \$.each(chart_options.series, function (index, serie) {
                        if ({{ distributed ? \x27true\x27 : \x27false\x27 }}) {
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
                    if ({{ horizontal ? \x27true\x27 : \x27false\x27 }}) {
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
            </script>", "__string_template__2a5f7ae1e258a6621d653bb678cc573f", "");
    }
}
