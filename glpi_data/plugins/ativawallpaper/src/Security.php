<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativawallpaper;

final class Security
{
    public static function randomToken(int $bytes = 32): string
    {
        return rtrim(strtr(base64_encode(random_bytes($bytes)), '+/', '-_'), '=');
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    public static function verifyToken(string $token, string $expectedHash): bool
    {
        if ($token === '' || strlen($expectedHash) !== 64) {
            return false;
        }
        return hash_equals($expectedHash, self::hashToken($token));
    }

    public static function cleanText(mixed $value, int $maxLength): string
    {
        $text = is_scalar($value) ? (string) $value : '';
        $text = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $text) ?? '';
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');
        return mb_substr($text, 0, $maxLength);
    }

    public static function isValidMachineGuid(string $guid): bool
    {
        return preg_match('/^[A-Za-z0-9{}._:-]{8,128}$/', $guid) === 1;
    }

    public static function isValidHostname(string $hostname): bool
    {
        return preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]{0,254}$/', $hostname) === 1;
    }

    public static function isValidVersion(string $version): bool
    {
        return preg_match('/^[0-9A-Za-z][0-9A-Za-z._+-]{0,31}$/', $version) === 1;
    }
}
