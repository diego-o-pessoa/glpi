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

/* __string_template__03771e810ba598d47cb233202bd74730 */
class __TwigTemplate_cee5ede796a149a6fa14d9e68f4d481b extends Template
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
                    const myChart = echarts.init(target[0]);
                    myChart.setOption(";
        // line 9
        yield json_encode((isset($context["options"]) || array_key_exists("options", $context) ? $context["options"] : (function () { throw new RuntimeError('Variable "options" does not exist.', 9, $this->source); })()));
        yield ");
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
        return "__string_template__03771e810ba598d47cb233202bd74730";
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
        return array (  58 => 9,  53 => 7,  49 => 6,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("            <script type=\"module\">
                (async () => {
                    await import(\x27/js/modules/Dashboard/Dashboard.js\x27);

                    const target = GLPI.Dashboard.getActiveDashboard() ?
                        GLPI.Dashboard.getActiveDashboard().element.find(\x27#{{ chart_id }} .chart\x27)
                        : \$(\x27#{{ chart_id }} .chart\x27);
                    const myChart = echarts.init(target[0]);
                    myChart.setOption({{ options|json_encode|raw }});
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
            </script>", "__string_template__03771e810ba598d47cb233202bd74730", "");
    }
}
