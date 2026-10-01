<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaramal;

use Session;
use Toolbox;

/**
 * Log proprio do plugin: files/_log/ativaramal.log.
 *
 * Nunca registra tokens, segredos, codigos de autorizacao ou senhas: chaves
 * sensiveis do contexto viram "[oculto]" e valores com cara de token sao
 * mascarados mesmo dentro de mensagens livres.
 */
final class Logger
{
    private const FILE = 'ativaramal';

    /** Partes de nome de chave que nunca vao para o log. */
    private const SENSITIVE = ['secret', 'token', 'password', 'senha', 'code', 'authorization', 'assertion', 'state', 'cookie'];

    public static function info(string $event, array $context = []): void
    {
        self::write('INFO', $event, $context);
    }

    public static function warning(string $event, array $context = []): void
    {
        self::write('WARNING', $event, $context);
    }

    public static function error(string $event, array $context = []): void
    {
        self::write('ERROR', $event, $context);
    }

    private static function write(string $level, string $event, array $context): void
    {
        $user = Session::getLoginUserID();
        $context = ['usuario' => $user ? getUserName((int) $user) : 'sistema'] + self::sanitize($context);
        $pairs = [];
        foreach ($context as $key => $value) {
            $pairs[] = $key . '=' . (is_scalar($value) || $value === null ? (string) $value : json_encode($value));
        }
        Toolbox::logInFile(self::FILE, sprintf(
            "[%s] %s%s\n",
            $level,
            self::mask($event),
            $pairs !== [] ? ' | ' . self::mask(implode(' ', $pairs)) : ''
        ));
    }

    /** @return array<string, mixed> */
    private static function sanitize(array $context): array
    {
        $clean = [];
        foreach ($context as $key => $value) {
            $lower = strtolower((string) $key);
            foreach (self::SENSITIVE as $needle) {
                if (str_contains($lower, $needle)) {
                    $value = '[oculto]';
                    break;
                }
            }
            if (is_array($value)) {
                $value = self::sanitize($value);
            } elseif (is_string($value)) {
                $value = mb_substr($value, 0, 300);
            }
            $clean[$key] = $value;
        }
        return $clean;
    }

    /** Mascara sequencias longas com cara de token/JWT/segredo em texto livre. */
    private static function mask(string $text): string
    {
        $text = preg_replace('/\b(Bearer|Basic)\s+[A-Za-z0-9._~+\/=-]+/i', '$1 [oculto]', $text) ?? '';
        $text = preg_replace('/\beyJ[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+\.[A-Za-z0-9_-]*/', '[jwt oculto]', $text) ?? '';
        $text = preg_replace('/\b[A-Za-z0-9_\-]{32,}\b/', '[oculto]', $text) ?? '';
        return preg_replace('/[\r\n]+/', ' ', $text) ?? '';
    }
}
