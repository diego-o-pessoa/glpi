<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativacentral;

use Config;
use Glpi\Plugin\Hooks;
use Session;
use Throwable;
use Toolbox;

/**
 * Liga o visual novo em cada pagina: CSS, JS, barra lateral e os dados que o
 * JS precisa (enviados numa tag <meta>, sem requisicao extra).
 *
 * Tres estados:
 *  - plugin desligado nas opcoes  -> GLPI original, nada e carregado;
 *  - "visual classico" na sessao  -> GLPI original + atalho para voltar;
 *  - ligado                       -> visual novo.
 */
final class Shell
{
    /** Sessao em que a pessoa pediu o visual classico do GLPI. */
    public const SESSION_CLASSIC = 'ativacentral_classic';

    /** A animacao de entrada da marca ja foi mostrada nesta sessao. */
    private const SESSION_BRAND = 'ativacentral_brand_intro';

    /** Layout que a pessoa usava antes (o visual novo exige o menu lateral). */
    private const SESSION_LAYOUT = 'ativacentral_previous_layout';

    public static function boot(): void
    {
        global $PLUGIN_HOOKS;

        // So a interface de administracao; o autoatendimento nao muda.
        if (Session::getLoginUserID() === false || Session::getCurrentInterface() !== 'central') {
            return;
        }

        if (!self::active()) {
            self::restoreLayout();
            if (Settings::enabled() && self::isFullPageRequest()) {
                // Visual classico so nesta sessao: atalho no menu do usuario para voltar.
                $PLUGIN_HOOKS[Hooks::ADD_JAVASCRIPT]['ativacentral'] = [self::asset('js/classic.js')];
                $PLUGIN_HOOKS[Hooks::ADD_HEADER_TAG]['ativacentral'] = [
                    self::meta(['toggle' => self::url('/front/toggle.php?mode=new')]),
                ];
            }
            return;
        }

        self::forceVerticalLayout();

        $PLUGIN_HOOKS[Hooks::ADD_CSS]['ativacentral'] = [self::asset('css/shell.css'), self::asset('css/native.css')];
        $PLUGIN_HOOKS[Hooks::ADD_JAVASCRIPT]['ativacentral'] = [self::asset('js/shell.js')];
        $PLUGIN_HOOKS[Hooks::REDEFINE_MENUS]['ativacentral'] = [self::class, 'redefineMenus'];

        if (!self::isFullPageRequest()) {
            return;
        }
        $tags = [self::meta(self::clientData())];
        if (!self::isNativeHome() && empty($_SESSION[self::SESSION_BRAND])) {
            // Animacao de entrada da marca: so na primeira pagina da sessao.
            $_SESSION[self::SESSION_BRAND] = 1;
            $tags[] = ['tag' => 'link', 'properties' => ['rel' => 'stylesheet', 'href' => self::assetUrl('css/brand-intro.css')]];
        }
        if (self::isNativeHome() && !self::panelOnly()) {
            // A pagina inicial do GLPI vira a "Visao geral". O plugin nao
            // consegue redirecionar no servidor nesta fase, entao a propria
            // pagina manda o navegador para la antes de desenhar qualquer coisa.
            $tags[] = ['tag' => 'meta', 'properties' => ['http-equiv' => 'refresh', 'content' => '0;url=' . self::url('/front/home.php')]];
            $tags[] = ['tag' => 'link', 'properties' => ['rel' => 'stylesheet', 'href' => self::url('/css/redirect.css')]];
        }
        $PLUGIN_HOOKS[Hooks::ADD_HEADER_TAG]['ativacentral'] = $tags;
    }

    /**
     * Sem chamados, ativos nem configuracao: a pessoa so usa paineis de
     * plugins (ex.: "Ativa - Gestor" com o Ativa Ramal ou o Ativa Wallpaper).
     * A "Visao geral" da T.I. nao e para ela: a pagina inicial fica com o GLPI,
     * que leva cada um ao seu painel.
     */
    public static function panelOnly(): bool
    {
        return !Session::haveRightsOr('ticket', [CREATE, \Ticket::READMY, \Ticket::READALL, \Ticket::READGROUP])
            && !Session::haveRight('computer', READ)
            && !Session::haveRight('config', READ);
    }

    /** Visual novo valendo para esta sessao. */
    public static function active(): bool
    {
        return Session::getLoginUserID() !== false
            && Session::getCurrentInterface() === 'central'
            && Settings::enabled()
            && empty($_SESSION[self::SESSION_CLASSIC]);
    }

    /**
     * Gancho "redefine_menus": nunca pode quebrar a pagina; em caso de falha
     * devolve o menu original do GLPI.
     *
     * @param mixed $menu
     * @return mixed
     */
    public static function redefineMenus($menu)
    {
        if (!is_array($menu)) {
            return $menu;
        }
        try {
            return Nav::build($menu);
        } catch (Throwable $exception) {
            Toolbox::logInFile('ativacentral', 'Falha ao montar a barra lateral: ' . $exception->getMessage() . "\n");
            return $menu;
        }
    }

    /** Caminho publico (com a raiz do GLPI) de uma pagina/arquivo do plugin. */
    public static function url(string $path): string
    {
        global $CFG_GLPI;

        return rtrim((string) ($CFG_GLPI['root_doc'] ?? ''), '/') . '/plugins/ativacentral' . $path;
    }

    /** Caminho publico de uma pagina do GLPI. */
    public static function glpiUrl(string $path): string
    {
        global $CFG_GLPI;

        return rtrim((string) ($CFG_GLPI['root_doc'] ?? ''), '/') . $path;
    }

    /**
     * Arquivo de public/ com a data de modificacao: o navegador pega a versao
     * nova assim que o arquivo muda, sem precisar subir a versao do plugin.
     */
    public static function asset(string $file): string
    {
        $mtime = @filemtime(PLUGIN_ATIVACENTRAL_DIR . '/public/' . $file);
        return $file . '?m=' . (int) $mtime;
    }

    public static function assetUrl(string $file): string
    {
        return self::url('/' . self::asset($file));
    }

    public static function canConfigure(): bool
    {
        return (bool) Session::haveRight(Config::$rightname, UPDATE);
    }

    /**
     * Dados para o JS de todas as paginas (barra lateral e topo).
     *
     * @return array<string, mixed>
     */
    private static function clientData(): array
    {
        $new = 0;
        try {
            $new = Metrics::newTickets();
        } catch (Throwable $exception) {
            // Selo do menu e so um enfeite: sem ele a pagina segue normal.
        }

        return [
            // Quem so tem paineis nao tem "Visao geral": o shell.js leva ao painel dele.
            'home'      => self::panelOnly() ? '' : self::url('/front/home.php'),
            'panelOnly' => self::panelOnly(),
            'helpdesk'  => self::glpiUrl(Nav::hubUrl('helpdesk')),
            'classic'   => self::url('/front/toggle.php?mode=classic'),
            'finish'    => self::url('/front/ticket.finish.php'),
            'branch'    => self::url('/front/branch.php'),
            'config'    => self::canConfigure() ? self::url('/front/config.php') : '',
            'section'   => Nav::SECTION_ICON,
            'badges'    => ['helpdesk' => $new],
            'newUrl'    => self::glpiUrl(Hubs::ticketSearchUrl(['status' => \Ticket::INCOMING])),
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array{tag: string, properties: array<string, string>}
     */
    private static function meta(array $data): array
    {
        return [
            'tag'        => 'meta',
            'properties' => [
                'name'    => 'ativacentral',
                'content' => (string) json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ],
        ];
    }

    /** Pagina inteira (nao chamada AJAX, nao envio de formulario). */
    private static function isFullPageRequest(): bool
    {
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET'
            && empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && !str_contains($uri, '/ajax/');
    }

    /** Pagina inicial nativa do GLPI, sem pedido especial na URL. */
    private static function isNativeHome(): bool
    {
        $path = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
        if (preg_match('#/(front/central\.php|Central)$#', $path) !== 1) {
            return false;
        }
        // embed: painel incorporado; redirect: link direto apos o login;
        // ac_native: a pessoa pediu os paineis nativos do GLPI.
        return !isset($_GET['embed']) && !isset($_GET['redirect']) && !isset($_GET['ac_native']);
    }

    private static function forceVerticalLayout(): void
    {
        $current = $_SESSION['glpipage_layout'] ?? null;
        if ($current === 'vertical') {
            return;
        }
        if (!array_key_exists(self::SESSION_LAYOUT, $_SESSION)) {
            $_SESSION[self::SESSION_LAYOUT] = (string) ($current ?? '');
        }
        $_SESSION['glpipage_layout'] = 'vertical';
    }

    private static function restoreLayout(): void
    {
        if (!array_key_exists(self::SESSION_LAYOUT, $_SESSION)) {
            return;
        }
        $previous = (string) $_SESSION[self::SESSION_LAYOUT];
        if ($previous === '') {
            unset($_SESSION['glpipage_layout']);
        } else {
            $_SESSION['glpipage_layout'] = $previous;
        }
        unset($_SESSION[self::SESSION_LAYOUT]);
    }
}
