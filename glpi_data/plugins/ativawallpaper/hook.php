<?php

declare(strict_types=1);

use GlpiPlugin\Ativawallpaper\InventoryUserMatcher;

function plugin_ativawallpaper_install(): bool
{
    require_once __DIR__ . '/install/install.php';
    return plugin_ativawallpaper_do_install();
}

function plugin_ativawallpaper_uninstall(): bool
{
    require_once __DIR__ . '/install/install.php';
    return plugin_ativawallpaper_do_uninstall();
}

/**
 * Keep native inventory from assigning an arbitrary duplicate user.
 */
function plugin_ativawallpaper_pre_inventory(mixed $data): mixed
{
    if (!is_object($data) || !isset($data->content) || !is_object($data->content)) {
        return $data;
    }

    $content = $data->content;
    $username = '';
    if (isset($content->hardware) && is_object($content->hardware)) {
        $username = (string) ($content->hardware->lastloggeduser ?? '');
    }
    if ($username === '' && isset($content->users)) {
        $users = is_array($content->users) ? $content->users : [$content->users];
        $first = $users[0] ?? null;
        if (is_object($first)) {
            $username = (string) ($first->login ?? '');
        } elseif (is_array($first)) {
            $username = (string) ($first['login'] ?? '');
        }
    }

    $resolved = InventoryUserMatcher::resolve($username);
    if (($resolved['status'] ?? '') === 'ambiguous') {
        if (isset($content->hardware) && is_object($content->hardware)) {
            $content->hardware->lastloggeduser = '';
        }
        // MainAsset::prepareForUsers() otherwise selects the first duplicate.
        if (property_exists($content, 'users')) {
            $content->users = [];
        }
    } elseif (($resolved['status'] ?? '') === 'unique'
        && isset($content->hardware)
        && is_object($content->hardware)
    ) {
        $content->hardware->lastloggeduser = (string) ($resolved['name'] ?? '');
    }

    return $data;
}

/**
 * When a user authenticates (including Microsoft/SSO), link the first still
 * unassigned computer that contacted the API from the same recent IP. The
 * matcher itself refuses ambiguous/shared-IP cases and never overwrites an
 * existing computer user.
 */
function plugin_ativawallpaper_init_session(): void
{
    $userId = (int) Session::getLoginUserID();
    if ($userId < 1) {
        return;
    }

    // Keep the same proxy order used by GLPI's authentication event log so
    // the address matches the one recorded by the API client request.
    $forwarded = trim((string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''));
    $ipAddress = $forwarded !== ''
        ? trim(explode(',', $forwarded)[0])
        : trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));

    InventoryUserMatcher::assignFromLogin($userId, $ipAddress);
}
