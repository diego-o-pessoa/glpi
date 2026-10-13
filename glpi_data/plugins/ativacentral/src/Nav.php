<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativacentral;

/**
 * Barra lateral do Ativa Central.
 *
 * O GLPI desenha a barra a partir de um array de menus; cada plugin pode
 * devolver outro array pelo gancho "redefine_menus". Aqui entram, na frente,
 * as entradas novas (links diretos, sem lista suspensa) agrupadas em secoes.
 *
 * Os menus originais do GLPI continuam no array, sem mexer no conteudo: e
 * deles que saem a trilha de navegacao, os botoes "Adicionar/Pesquisar" do
 * topo e a busca "Encontrar menu". Na barra eles ficam escondidos pelo CSS.
 */
final class Nav
{
    /** Secoes nativas do GLPI. */
    private const CORE = ['assets', 'helpdesk', 'management', 'tools', 'plugins', 'admin', 'config', 'preference'];

    /** Marcador (classe do icone) das linhas que sao titulo de secao. */
    public const SECTION_ICON = 'ac-section';

    /** Icones das solucoes Ativa (pela chave do plugin). */
    private const SOLUTION_ICONS = [
        'ativaguardian'  => 'ti ti-shield-check',
        'ativaramal'     => 'ti ti-phone',
        'ativarede'      => 'ti ti-network',
        'ativaremote'    => 'ti ti-screen-share',
        'ativaupdater'   => 'ti ti-refresh',
        'ativawallpaper' => 'ti ti-photo',
        'ativaworkspace' => 'ti ti-layout-grid',
    ];

    public static function homeUrl(): string
    {
        return '/plugins/ativacentral/front/home.php';
    }

    public static function hubUrl(string $key): string
    {
        return '/plugins/ativacentral/front/hub.php?s=' . rawurlencode($key);
    }

    /**
     * @param array<string, mixed> $menu menus do GLPI (ja com os dos plugins)
     * @return array<string, mixed>
     */
    public static function build(array $menu): array
    {
        $out = [];
        $out['ac_sec_workspace'] = self::label('Workspace');
        $out['ac_home'] = self::link('Visão geral', 'ti ti-layout-dashboard', self::homeUrl());

        if (self::hasOwnPages($menu['helpdesk'] ?? null)) {
            // Mesmo titulo na secao original: e por ele que o GLPI marca o
            // item ativo e monta a trilha de navegacao.
            $menu['helpdesk']['title'] = 'Chamados';
            $menu['helpdesk']['default'] = self::hubUrl('helpdesk');
            $out['ac_helpdesk'] = self::link('Chamados', 'ti ti-ticket', self::hubUrl('helpdesk'));
        }

        if (self::hasOwnPages($menu['assets'] ?? null)) {
            $title = (string) ($menu['assets']['title'] ?? 'Ativos');
            $out['ac_assets'] = self::link($title, 'ti ti-package', self::hubUrl('assets'));
            $menu['assets']['default'] = self::hubUrl('assets');
            if (isset($menu['assets']['content']['computer']['page'])) {
                $out['ac_computers'] = self::link('Computadores', 'ti ti-device-desktop', self::hubUrl('computers'));
                // Nas telas de computador, o item ativo e "Computadores".
                if (self::onComputerPages()) {
                    $menu['assets']['title'] = 'Computadores';
                    $menu['assets']['default'] = self::hubUrl('computers');
                }
            }
        }

        // --- Solucoes Ativa: plugins com secao propria e os que entram em
        // "Administracao" (Guardian, Remote, Updater, Wallpaper).
        $solutions = [];
        $others = [];
        foreach ($menu as $key => $sector) {
            if (!is_array($sector) || in_array($key, self::CORE, true) || !self::hasPages($sector)) {
                continue;
            }
            $pages = self::pages($sector);
            $url = count($pages) === 1 ? (string) reset($pages)['page'] : self::hubUrl((string) $key);
            $entry = [
                'title' => (string) ($sector['title'] ?? $key),
                'icon'  => self::SOLUTION_ICONS[$key] ?? (string) ($sector['icon'] ?? 'ti ti-apps'),
                'url'   => $url,
            ];
            $menu[$key]['default'] = $url;
            if (self::isAtiva((string) $key, $entry['title'])) {
                $solutions['ac_sol_' . $key] = $entry;
            } else {
                $others['ac_plg_' . $key] = $entry;
            }
        }
        foreach (self::embeddedSolutions($menu) as $key => $entry) {
            $solutions['ac_sol_' . $key] = $entry;
        }
        if ($solutions !== []) {
            uasort($solutions, static fn (array $a, array $b): int => strnatcasecmp($a['title'], $b['title']));
            $out['ac_sec_solutions'] = self::label('Soluções Ativa');
            foreach ($solutions as $key => $entry) {
                $out[$key] = self::link($entry['title'], $entry['icon'], $entry['url']);
            }
        }

        // --- Gestao e sistema
        $system = [];
        if (self::hasOwnPages($menu['admin'] ?? null)) {
            $menu['admin']['title'] = 'Usuários e equipes';
            $menu['admin']['default'] = self::hubUrl('admin');
            $system['ac_admin'] = self::link('Usuários e equipes', 'ti ti-users', self::hubUrl('admin'));
        }
        if (self::hasOwnPages($menu['management'] ?? null)) {
            $menu['management']['default'] = self::hubUrl('management');
            $system['ac_management'] = self::link((string) ($menu['management']['title'] ?? 'Gerência'), 'ti ti-wallet', self::hubUrl('management'));
        }
        if (self::hasOwnPages($menu['tools'] ?? null)) {
            // Titulo do proprio GLPI ("Ferramentas"): cabe na barra; a tela
            // do modulo explica que os relatorios ficam ali.
            $menu['tools']['default'] = self::hubUrl('tools');
            $system['ac_tools'] = self::link((string) ($menu['tools']['title'] ?? 'Ferramentas'), 'ti ti-chart-bar', self::hubUrl('tools'));
            if (isset($menu['tools']['content']['knowbaseitem']['page'])) {
                $system['ac_kb'] = self::link('Base de conhecimento', 'ti ti-book', (string) $menu['tools']['content']['knowbaseitem']['page']);
            }
        }
        foreach ($others as $key => $entry) {
            $system[$key] = self::link($entry['title'], $entry['icon'], $entry['url']);
        }
        if (self::hasOwnPages($menu['plugins'] ?? null)) {
            $menu['plugins']['default'] = self::hubUrl('plugins');
            $system['ac_plugins'] = self::link((string) ($menu['plugins']['title'] ?? 'Plugins'), 'ti ti-puzzle', self::hubUrl('plugins'));
        }
        if (self::hasOwnPages($menu['config'] ?? null)) {
            $menu['config']['title'] = 'Configurações';
            $menu['config']['default'] = self::hubUrl('config');
            $system['ac_config'] = self::link('Configurações', 'ti ti-settings', self::hubUrl('config'));
        }
        if ($system !== []) {
            $out['ac_sec_system'] = self::label('Gestão e sistema');
            $out += $system;
        }

        // Entradas novas primeiro; os menus originais vem depois (escondidos).
        return $out + $menu;
    }

    /**
     * Solucoes Ativa registradas DENTRO de uma secao do GLPI (a chave do item
     * e o nome da classe do menu do plugin, em minusculas).
     *
     * @param array<string, mixed> $menu
     * @return array<string, array{title: string, icon: string, url: string}>
     */
    public static function embeddedSolutions(array $menu): array
    {
        $found = [];
        foreach (['admin', 'tools', 'management', 'config', 'plugins', 'assets', 'helpdesk'] as $core) {
            foreach ((array) ($menu[$core]['content'] ?? []) as $itemKey => $entry) {
                if (!is_array($entry) || !isset($entry['page']) || !self::isEmbeddedSolution((string) $itemKey)) {
                    continue;
                }
                $plugin = self::pluginOf((string) $itemKey);
                $found[(string) $itemKey] = [
                    'title' => (string) ($entry['title'] ?? $plugin),
                    'icon'  => self::SOLUTION_ICONS[$plugin] ?? (string) ($entry['icon'] ?? 'ti ti-apps'),
                    'url'   => (string) $entry['page'],
                ];
            }
        }
        return $found;
    }

    public static function isEmbeddedSolution(string $itemKey): bool
    {
        return str_starts_with($itemKey, 'pluginativa');
    }

    /** "pluginativaguardianmenu" -> "ativaguardian". */
    private static function pluginOf(string $itemKey): string
    {
        foreach (array_keys(self::SOLUTION_ICONS) as $plugin) {
            if (str_starts_with($itemKey, 'plugin' . $plugin)) {
                return $plugin;
            }
        }
        return preg_replace('/^plugin/', '', $itemKey) ?? $itemKey;
    }

    private static function isAtiva(string $key, string $title): bool
    {
        return str_starts_with(strtolower($key), 'ativa') || str_starts_with(mb_strtolower($title), 'ativa ');
    }

    /** @param mixed $sector */
    public static function hasPages($sector): bool
    {
        return is_array($sector) && self::pages($sector) !== [];
    }

    /**
     * Secao nativa com alguma funcao propria. Uma secao em que o perfil so
     * enxerga uma solucao Ativa nao vira item da barra: a solucao ja tem o
     * dela e a tela do modulo ficaria vazia.
     *
     * @param mixed $sector
     */
    public static function hasOwnPages($sector): bool
    {
        if (!is_array($sector)) {
            return false;
        }
        foreach (array_keys(self::pages($sector)) as $itemKey) {
            if (!self::isEmbeddedSolution((string) $itemKey)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Itens da secao que tem pagina (os unicos que o GLPI mostra).
     *
     * @param array<string, mixed> $sector
     * @return array<string, array<string, mixed>>
     */
    public static function pages(array $sector): array
    {
        $pages = [];
        foreach ((array) ($sector['content'] ?? []) as $key => $entry) {
            if (is_array($entry) && isset($entry['page'])) {
                $pages[(string) $key] = $entry;
            }
        }
        return $pages;
    }

    /** Telas nativas de computador (lista e ficha). */
    private static function onComputerPages(): bool
    {
        if (!empty($GLOBALS['ATIVACENTRAL_SECTION'])) {
            return $GLOBALS['ATIVACENTRAL_SECTION'] === 'computers';
        }
        $path = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
        return preg_match('#/front/computer(\.form)?\.php$#i', $path) === 1;
    }

    /** @return array<string, string> */
    private static function link(string $title, string $icon, string $url): array
    {
        return ['title' => $title, 'icon' => $icon, 'default' => $url];
    }

    /**
     * Titulo de secao: o GLPI so sabe desenhar links, entao vai como um link
     * marcado pelo icone; o CSS/JS do Ativa Central o transforma em rotulo.
     *
     * @return array<string, string>
     */
    private static function label(string $title): array
    {
        return ['title' => $title, 'icon' => self::SECTION_ICON, 'default' => '/front/central.php#ac-section'];
    }
}
