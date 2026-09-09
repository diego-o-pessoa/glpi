<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativawallpaper;

use Session;

final class Audit
{
    public static function record(
        string $action,
        ?string $targetType = null,
        ?int $targetId = null,
        mixed $oldValue = null,
        mixed $newValue = null
    ): void {
        global $DB;

        $encode = static function (mixed $value): ?string {
            if ($value === null) {
                return null;
            }
            $json = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            return $json === false ? null : mb_substr($json, 0, 65535);
        };

        $DB->insert('glpi_plugin_ativawallpaper_audits', [
            'users_id'   => (int) Session::getLoginUserID(),
            'action'     => Security::cleanText($action, 64),
            'target_type'=> $targetType === null ? null : Security::cleanText($targetType, 64),
            'target_id'  => $targetId,
            'old_value'  => $encode($oldValue),
            'new_value'  => $encode($newValue),
            'ip_address' => Security::cleanText($_SERVER['REMOTE_ADDR'] ?? '', 45),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
