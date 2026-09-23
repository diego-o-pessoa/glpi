<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace;

use Glpi\Application\View\TemplateRenderer;
use Html;
use PluginAtivaworkspaceProfile;
use Session;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Secoes do Workspace: titulo, icone, arquivo e direito exigido. Menu,
 * navegacao e validacao de acesso saem daqui, para nao divergirem.
 */
final class Page
{
    private const BASE = '/plugins/ativaworkspace/front/';

    /** Arquivos que nao sao secoes do menu, mas tem URL propria. */
    private const EXTRA_FILES = [
        'profile_form'     => 'profile.form.php',
        'application_form' => 'application.form.php',
        'step_form'        => 'step.form.php',
        'job_form'         => 'job.form.php',
        'job'              => 'job.php',
        'job_action'       => 'job.action.php',
        'job_data'         => 'job.data.php',
        'overview_data'    => 'overview.data.php',
    ];

    /** Intervalo da atualizacao em tempo real das telas (ms). */
    public const REFRESH_MS = 5000;

    /**
     * @return array<string, array{title: string, icon: string, file: string, right: string, form?: string}>
     */
    public static function sections(): array
    {
        return [
            'overview' => [
                'title' => 'Visão Geral',
                'icon'  => 'ti ti-layout-dashboard',
                'file'  => 'overview.php',
                'right' => PluginAtivaworkspaceProfile::RIGHT_VIEW,
            ],
            'provisioning' => [
                'title' => 'Provisionamento',
                'icon'  => 'ti ti-rocket',
                'file'  => 'provisioning.php',
                'right' => PluginAtivaworkspaceProfile::RIGHT_VIEW,
            ],
            'profiles' => [
                'title' => 'Perfis',
                'icon'  => 'ti ti-users',
                'file'  => 'profiles.php',
                'right' => PluginAtivaworkspaceProfile::RIGHT_PROFILES,
                'form'  => 'profile_form',
            ],
            'applications' => [
                'title' => 'Aplicativos',
                'icon'  => 'ti ti-apps',
                'file'  => 'applications.php',
                'right' => PluginAtivaworkspaceProfile::RIGHT_APPLICATIONS,
                'form'  => 'application_form',
            ],
            'logs' => [
                'title' => 'Logs',
                'icon'  => 'ti ti-file-text',
                'file'  => 'logs.php',
                'right' => PluginAtivaworkspaceProfile::RIGHT_VIEW,
            ],
            'settings' => [
                'title' => 'Configurações',
                'icon'  => 'ti ti-settings',
                'file'  => 'settings.php',
                'right' => PluginAtivaworkspaceProfile::RIGHT_CONFIG,
            ],
        ];
    }

    /**
     * Caminho relativo ao GLPI (sem root_doc). E o formato que o menu espera:
     * o layout do GLPI aplica path() e prefixa o root_doc sozinho.
     */
    public static function url(string $key): string
    {
        $file = self::sections()[$key]['file'] ?? self::EXTRA_FILES[$key] ?? null;
        if ($file === null) {
            throw new \InvalidArgumentException('Pagina desconhecida: ' . $key);
        }
        return self::BASE . $file;
    }

    /** URL completa (com root_doc), para links, formularios e redirects. */
    public static function href(string $key, array $query = []): string
    {
        global $CFG_GLPI;

        $url = $CFG_GLPI['root_doc'] . self::url($key);
        return $query === [] ? $url : $url . '?' . http_build_query($query);
    }

    /** URL de um arquivo estatico do plugin (public/), com cache-bust pela versao. */
    public static function asset(string $path): string
    {
        global $CFG_GLPI;

        return $CFG_GLPI['root_doc'] . '/plugins/ativaworkspace/' . ltrim($path, '/')
            . '?v=' . rawurlencode(PLUGIN_ATIVAWORKSPACE_VERSION);
    }

    public static function canAccess(string $key): bool
    {
        $section = self::sections()[$key] ?? null;
        return $section !== null
            && PluginAtivaworkspaceProfile::canViewWorkspace()
            && Session::haveRight($section['right'], READ);
    }

    /** Validacao no backend: todo front/ chama antes de qualquer coisa. */
    public static function requireAccess(string $key): void
    {
        $section = self::sections()[$key] ?? null;
        if ($section === null) {
            throw new AccessDeniedHttpException();
        }
        PluginAtivaworkspaceProfile::requireRight(PluginAtivaworkspaceProfile::RIGHT_VIEW, READ);
        PluginAtivaworkspaceProfile::requireRight($section['right'], READ);
    }

    /**
     * Configuracao do JS de tempo real (workspace.js), comum a Visao Geral e
     * ao Provisionamento.
     *
     * @param array<string, mixed> $initial dados do primeiro paint (Overview::payload)
     * @return array<string, mixed>
     */
    public static function liveConfig(array $initial, int $jobsLimit, int $eventsLimit, array $filters = []): array
    {
        global $CFG_GLPI;

        return [
            'initial'    => $initial,
            'refreshMs'  => self::REFRESH_MS,
            'jobsLimit'  => $jobsLimit,
            'eventsLimit'=> $eventsLimit,
            // Filtros da listagem: o polling pede a mesma selecao.
            'filters'    => array_filter($filters, static fn ($v) => $v !== '' && $v !== 0),
            'urls'       => [
                'data'     => self::href('overview_data'),
                'job'      => self::href('job_data'),
                'jobForm'  => self::href('job_form'),
                'jobPage'  => self::href('job'),
                'computer' => $CFG_GLPI['root_doc'] . '/front/computer.form.php',
                'logs'     => self::href('logs'),
            ],
        ];
    }

    /**
     * Cabecalho do GLPI + template da secao + rodape.
     *
     * @param array<string, mixed> $vars
     */
    public static function render(string $key, string $template, array $vars = []): void
    {
        $section = self::sections()[$key];

        // Secao propria "ativaworkspace" na barra lateral; o item ativo e a chave
        // da entrada no menu multi-entradas (PluginAtivaworkspaceMenu).
        Html::header('Ativa Workspace - ' . $section['title'], '', 'ativaworkspace', $key);

        // Abas entre as secoes (so as que o usuario pode acessar).
        $nav = [];
        foreach (self::sections() as $navKey => $navSection) {
            if (self::canAccess($navKey)) {
                $nav[] = [
                    'title'  => $navSection['title'],
                    'icon'   => $navSection['icon'],
                    'href'   => self::href($navKey),
                    'active' => $navKey === $key,
                ];
            }
        }

        TemplateRenderer::getInstance()->display('@ativaworkspace/' . $template, $vars + [
            'nav'           => $nav,
            'section_title' => $section['title'],
            'section_icon'  => $section['icon'],
            'script_url'    => self::asset('js/workspace.js'),
        ]);

        Html::footer();
    }
}
