<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativawallpaper;

use PluginAtivawallpaperProfile;
use Session;

/** Data of the "Visao geral" page, shared by the page and its live endpoint. */
final class OverviewPage
{
    public const PAGE_SIZE = 25;
    /** Sort options of "Ordenar por" and their natural direction. */
    public const SORTS = [
        'last_check'        => ['Último contato', 'desc'],
        'last_apply'        => ['Última aplicação', 'desc'],
        'hostname'          => ['Computador', 'asc'],
        'username'          => ['Usuário', 'asc'],
        'wallpaper_version' => ['Wallpaper', 'desc'],
        'client_version'    => ['Cliente', 'desc'],
        'status'            => ['Status', 'asc'],
    ];
    public const STATUSES = [
        'all'     => 'Todos',
        'updated' => 'Atualizados',
        'pending' => 'Pendentes',
        'offline' => 'Offline',
        'error'   => 'Com erro',
    ];

    public static function filters(array $query): array
    {
        $sort = array_key_exists((string) ($query['sort'] ?? ''), self::SORTS) ? (string) $query['sort'] : 'last_check';
        $direction = strtolower((string) ($query['direction'] ?? ''));
        return [
            'q'         => Security::cleanText($query['q'] ?? '', 255),
            'status'    => array_key_exists((string) ($query['status'] ?? ''), self::STATUSES) ? (string) $query['status'] : 'all',
            'sort'      => $sort,
            'direction' => in_array($direction, ['asc', 'desc'], true) ? $direction : self::SORTS[$sort][1],
        ];
    }

    public static function context(array $query, string $rootDoc): array
    {
        $base = $rootDoc . '/plugins/ativawallpaper';
        $wallpaperManager = new WallpaperManager();
        $current = $wallpaperManager->current();
        if ($current !== null) {
            $current['created_by_name'] = getUserName((int) $current['created_by']);
        }

        $filters = self::filters($query);
        $dashboard = new DashboardService();
        $summary = $dashboard->summary($current);
        $rollout = $dashboard->rolloutProgress();
        $lastCheckAge = $dashboard->lastCheckAgeSeconds();

        return [
            'current'        => $current,
            'enabled'        => ConfigService::getBool('enabled'),
            'summary'        => $summary,
            'error_percentage' => $summary['managed'] > 0 ? round(($summary['errors'] / $summary['managed']) * 100, 1) : 0.0,
            'rollout'        => $rollout,
            'activity'       => $dashboard->activity($rollout),
            'last_check_age' => DashboardService::describeAge($lastCheckAge),
            'clients'        => $dashboard->clients($filters, $current, (int) ($query['page'] ?? 1), self::PAGE_SIZE),
            'filters'        => $filters,
            'sorts'          => self::SORTS,
            'statuses'       => self::STATUSES,
            'max_upload_mb'  => ConfigService::getInt('max_upload_mb'),
            'urls'           => [
                'action'    => $base . '/front/action.php',
                'dashboard' => $base . '/front/dashboard.php',
                'overview'  => $base . '/front/overview.php',
                'history'   => $base . '/front/history.php',
                'events'    => $base . '/front/events.php',
                'settings'  => $base . '/front/settings.php',
                'image'     => $base . '/front/image.php',
                'computer'  => $rootDoc . '/front/computer.form.php',
            ],
            'rights'         => [
                'publish'       => Session::haveRight(PluginAtivawallpaperProfile::RIGHT_PUBLISH, UPDATE),
                'config'        => Session::haveRight(PluginAtivawallpaperProfile::RIGHT_CONFIG, UPDATE),
                'config_view'   => Session::haveRight(PluginAtivawallpaperProfile::RIGHT_CONFIG, READ),
                'clients'       => Session::haveRight(PluginAtivawallpaperProfile::RIGHT_CLIENTS, UPDATE),
            ],
        ];
    }
}
