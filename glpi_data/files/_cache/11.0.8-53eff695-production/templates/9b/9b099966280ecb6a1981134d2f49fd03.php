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

/* __string_template__0075d21ee51a6ba930ad278e45597422 */
class __TwigTemplate_31a9bac68d3385a253ed53817457d2d7 extends Template
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
        yield "            <div class=\x27documentation\x27>";
        yield ($context["md"] ?? null);
        yield "</div>
            <script type=\"module\">
                import(\x27/js/modules/Monaco/MonacoEditor.js\x27).then(() => {
                    const lang_elements = \$(\x27code[class^=\"language-\"]\x27);
                    lang_elements.each((index, element) => {
                        const el = \$(element);
                        const code = el.text();
                        let lang = el.attr(\x27class\x27).replace(\x27language-\x27, \x27\x27);
                        switch (lang) {
                            case \x27bash\x27:
                                lang = \x27shell\x27;
                                break;
                            case \x27json\x27:
                                lang = \x27javascript\x27;
                                break;
                        }
                        window.GLPI.Monaco.colorizeText(code, lang).then((html) => {
                            el.html(html);
                        });
                    });
                });
            </script>";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "__string_template__0075d21ee51a6ba930ad278e45597422";
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
        return array (  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "__string_template__0075d21ee51a6ba930ad278e45597422", "");
    }
}
