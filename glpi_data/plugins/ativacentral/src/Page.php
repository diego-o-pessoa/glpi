<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativacentral;

use Glpi\Application\View\TemplateRenderer;
use Html;

/**
 * Pagina do Ativa Central dentro do layout do GLPI. O conteudo e desenhado
 * no navegador (public/js/app.js) a partir dos dados enviados aqui.
 */
final class Page
{
    /**
     * @param string $sector  secao do menu (define o item ativo e a trilha)
     * @param string $section "computers" quando a tela e a de computadores
     * @param array<string, mixed> $data
     */
    public static function render(string $title, string $sector, string $section, string $view, array $data): void
    {
        // Lido por Nav::build() para marcar o item certo na barra lateral.
        $GLOBALS['ATIVACENTRAL_SECTION'] = $section;

        Html::header($title, '', $sector);
        TemplateRenderer::getInstance()->display('@ativacentral/page.html.twig', [
            'view'       => $view,
            'data_json'  => (string) json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'css_url'    => Shell::assetUrl('css/app.css'),
            'script_url' => Shell::assetUrl('js/app.js'),
        ]);
        Html::footer();
    }
}
