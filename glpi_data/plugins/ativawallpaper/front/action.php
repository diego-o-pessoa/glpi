<?php

declare(strict_types=1);

use GlpiPlugin\Ativawallpaper\Audit;
use GlpiPlugin\Ativawallpaper\ClientRepository;
use GlpiPlugin\Ativawallpaper\ConfigService;
use GlpiPlugin\Ativawallpaper\DashboardService;
use GlpiPlugin\Ativawallpaper\Security;
use GlpiPlugin\Ativawallpaper\UpdateManager;
use GlpiPlugin\Ativawallpaper\WallpaperManager;

include '../../../inc/includes.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}

// GLPI 11's CheckCsrfListener validates the token before loading this legacy
// script. Calling Session::checkCSRF() again would consume the same token twice.

global $CFG_GLPI;
$base = $CFG_GLPI['root_doc'] . '/plugins/ativawallpaper/front';
$action = (string) ($_POST['action'] ?? '');
$redirect = $base . '/dashboard.php';
$expectsJson = str_contains(strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? '')), 'application/json')
    || strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
$jsonPayload = null;

try {
    switch ($action) {
        case 'publish':
            Session::checkRight(PluginAtivawallpaperProfile::RIGHT_PUBLISH, UPDATE);
            $upload = $_FILES['wallpaper'] ?? [];
            // Symfony may rebuild its FileBag while GLPI stores the redirect
            // message. Do not leave a reference to a temporary file that this
            // action is about to move or remove.
            unset($_FILES['wallpaper']);
            $wallpaper = (new WallpaperManager())->publishUploaded(
                $upload,
                (string) ($_POST['style'] ?? 'fill'),
                isset($_POST['lock_change']),
                (int) Session::getLoginUserID()
            );
            Session::addMessageAfterRedirect('Wallpaper ' . $wallpaper['version'] . ' publicado.', true, INFO);
            break;

        case 'rollback':
            Session::checkRight(PluginAtivawallpaperProfile::RIGHT_PUBLISH, UPDATE);
            $wallpaper = (new WallpaperManager())->makeCurrent((int) ($_POST['id'] ?? 0));
            Session::addMessageAfterRedirect('Versao ' . $wallpaper['version'] . ' definida como atual.', true, INFO);
            $redirect = $base . '/history.php';
            break;

        case 'toggle':
            Session::checkRight(PluginAtivawallpaperProfile::RIGHT_CONFIG, UPDATE);
            $before = ConfigService::getBool('enabled');
            $enabled = ($_POST['enabled'] ?? '0') === '1';
            ConfigService::set(['enabled' => $enabled]);
            ConfigService::rotatePublicationRevision();
            Audit::record($enabled ? 'enable' : 'disable', 'configuration', null, $before, $enabled);
            Session::addMessageAfterRedirect($enabled ? 'Distribuicao ativada.' : 'Distribuicao desativada; wallpapers existentes foram mantidos.', true, INFO);
            break;

        case 'force':
            Session::checkRight(PluginAtivawallpaperProfile::RIGHT_CLIENTS, UPDATE);
            (new ClientRepository())->setForceReapply((int) ($_POST['id'] ?? 0));
            Session::addMessageAfterRedirect('Nova aplicacao marcada para a proxima checagem do cliente.', true, INFO);
            break;

        case 'force_all':
            Session::checkRight(PluginAtivawallpaperProfile::RIGHT_PUBLISH, UPDATE);
            if ((new WallpaperManager())->current() === null) {
                throw new RuntimeException('Publique um wallpaper antes de aplicar em todos os computadores.');
            }
            $wasEnabled = ConfigService::getBool('enabled');
            if (!$wasEnabled) {
                ConfigService::set(['enabled' => true]);
                Audit::record('enable', 'configuration', null, false, true);
            }
            $rollout = (new ClientRepository())->startRollout();
            if ($rollout['count'] === 0) {
                throw new RuntimeException(
                    'Nenhum cliente Ativa Wallpaper esta registrado. Implante o AtivaWallpaperClient nas maquinas antes de aplicar.'
                );
            }
            ConfigService::set([
                'active_rollout_id'         => $rollout['id'],
                'active_rollout_started_at' => $rollout['started_at'],
                'active_rollout_version'    => (string) (new WallpaperManager())->current()['version'],
            ]);
            $message = sprintf(
                'Aplicacao iniciada em %d computador(es). Acompanhe o andamento na barra de progresso.',
                $rollout['count']
            );
            if ($expectsJson) {
                $jsonPayload = [
                    'ok'      => true,
                    'message' => $message,
                    'rollout' => (new DashboardService())->rolloutProgress(),
                ];
            } else {
                Session::addMessageAfterRedirect($message, true, INFO);
            }
            break;

        case 'revoke':
            Session::checkRight(PluginAtivawallpaperProfile::RIGHT_CLIENTS, UPDATE);
            (new ClientRepository())->revoke((int) ($_POST['id'] ?? 0));
            Session::addMessageAfterRedirect('Cliente revogado. Um novo registro sera necessario.', true, INFO);
            break;

        case 'settings':
            Session::checkRight(PluginAtivawallpaperProfile::RIGHT_CONFIG, UPDATE);
            $redirect = $base . '/settings.php';
            $serverUrl = rtrim((string) ($_POST['server_url'] ?? ''), '/');
            $parts = parse_url($serverUrl);
            if (!is_array($parts) || ($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])
                || filter_var($parts['host'], FILTER_VALIDATE_IP) !== false
                || strtolower((string) $parts['host']) !== 'chamados.ativalocacao.com.br'
                || !str_ends_with((string) ($parts['path'] ?? ''), '/plugins/ativawallpaper/api/v1')) {
                throw new RuntimeException('A URL da API deve usar HTTPS, chamados.ativalocacao.com.br e terminar em /plugins/ativawallpaper/api/v1.');
            }
            $poll = max(60, min(86400, (int) ($_POST['poll_interval_seconds'] ?? 60)));
            $offline = max($poll * 2, min(2592000, (int) ($_POST['offline_after_seconds'] ?? 3600)));
            $jitter = max(0, min(3600, (int) ($_POST['poll_jitter_seconds'] ?? 10)));
            $maxUpload = max(1, min(100, (int) ($_POST['max_upload_mb'] ?? 20)));
            $minimum = Security::cleanText($_POST['minimum_client_version'] ?? '1.0.0', 32);
            $latest = Security::cleanText($_POST['latest_client_version'] ?? '1.4.1', 32);
            if (!Security::isValidVersion($minimum) || !Security::isValidVersion($latest)) {
                throw new RuntimeException('Versao minima ou mais recente invalida.');
            }
            $before = ConfigService::all();
            $values = [
                'server_url'                 => $serverUrl,
                'poll_interval_seconds'      => $poll,
                'poll_jitter_seconds'        => $jitter,
                'offline_after_seconds'      => $offline,
                'max_upload_mb'              => $maxUpload,
                'minimum_client_version'     => $minimum,
                'latest_client_version'      => $latest,
                'bootstrap_configured'       => isset($_POST['bootstrap_configured']),
                'setup_completed'            => isset($_POST['setup_completed']),
                'preserve_data_on_uninstall' => isset($_POST['preserve_data_on_uninstall']),
            ];
            ConfigService::set($values);
            Audit::record('settings_update', 'configuration', null, array_intersect_key($before, $values), $values);
            Session::addMessageAfterRedirect('Configuracoes salvas.', true, INFO);
            break;

        case 'update_upload':
            Session::checkRight(PluginAtivawallpaperProfile::RIGHT_CONFIG, UPDATE);
            $redirect = $base . '/updates.php';
            $upload = $_FILES['update_package'] ?? [];
            unset($_FILES['update_package']);
            $package = (new UpdateManager())->publishUploaded(
                $upload,
                (string) ($_POST['component'] ?? ''),
                (string) ($_POST['version'] ?? ''),
                (int) Session::getLoginUserID()
            );
            Session::addMessageAfterRedirect(
                sprintf('Pacote %s %s enviado como rascunho.', $package['component'], $package['version']),
                true,
                INFO
            );
            break;

        case 'update_release':
            Session::checkRight(PluginAtivawallpaperProfile::RIGHT_CONFIG, UPDATE);
            $redirect = $base . '/updates.php';
            $package = (new UpdateManager())->release(
                (int) ($_POST['id'] ?? 0),
                (string) ($_POST['stage'] ?? ''),
                isset($_POST['pilot_hostname']) ? (string) $_POST['pilot_hostname'] : null,
                (int) Session::getLoginUserID()
            );
            Session::addMessageAfterRedirect(
                sprintf('Atualizacao %s %s agora esta em: %s.', $package['component'], $package['version'], $package['release_stage']),
                true,
                INFO
            );
            break;

        case 'update_delete':
            Session::checkRight(PluginAtivawallpaperProfile::RIGHT_CONFIG, UPDATE);
            $redirect = $base . '/updates.php';
            (new UpdateManager())->deletePackage(
                (int) ($_POST['id'] ?? 0),
                (int) Session::getLoginUserID()
            );
            Session::addMessageAfterRedirect('Pacote de atualizacao removido com sucesso.', true, INFO);
            break;

        case 'bootstrap':
            Session::checkRight(PluginAtivawallpaperProfile::RIGHT_CONFIG, UPDATE);
            $rotated = ConfigService::rotateRegistrationSecret();
            Audit::record('registration_secret_rotate', 'configuration', null, null, ['rotated_at' => $rotated['rotated_at']]);
            $payload = [
                'server'                 => ConfigService::get('server_url'),
                'registration_secret'    => $rotated['secret'],
                'verify_tls'             => true,
                'allow_windows_server'   => false,
                'pilot_hostname'         => 'DESKTOP-R1C8ICN-2026-08-17-14-54-59',
                'client_version'         => ConfigService::get('latest_client_version'),
                'generated_at'           => date(DATE_ATOM),
            ];
            header('Content-Type: application/json; charset=utf-8');
            header('Content-Disposition: attachment; filename="bootstrap-config.json"');
            header('Cache-Control: no-store');
            header('X-Content-Type-Options: nosniff');
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            exit;

        default:
            throw new RuntimeException('Acao invalida.');
    }
} catch (Throwable $exception) {
    $message = Security::cleanText($exception->getMessage(), 500);
    if ($expectsJson) {
        http_response_code(422);
        $jsonPayload = ['ok' => false, 'message' => $message];
    } else {
        Session::addMessageAfterRedirect($message, true, ERROR);
    }
}

if ($expectsJson) {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('X-Content-Type-Options: nosniff');
    echo json_encode(
        $jsonPayload ?? ['ok' => true],
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
    );
    exit;
}

Html::redirect($redirect);
