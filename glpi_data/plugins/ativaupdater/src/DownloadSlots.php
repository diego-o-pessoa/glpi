<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaupdater;

/**
 * Limita quantos computadores baixam o instalador ao mesmo tempo.
 *
 * Cada download ocupa um processo do Apache com o GLPI carregado durante
 * minutos. "Verificar agora" fazia os ~90 computadores baixarem juntos e o
 * servidor (8 GB, sem swap) ficava sem memoria e travava. Quem nao consegue
 * vaga recebe 503: o servico do Updater (todas as versoes) tenta de novo em
 * 10 s, 30 s e depois na verificacao seguinte (5 min), retomando o download
 * de onde parou, sem contar como falha de instalacao.
 *
 * A vaga e um flock em arquivo: o sistema a libera sozinho quando o processo
 * termina, inclusive se o download for interrompido ou o PHP cair.
 */
final class DownloadSlots
{
    public const DEFAULT_SLOTS = 4;
    public const RETRY_AFTER_SECONDS = 60;

    /** @var resource|null Mantida aberta ate o fim da requisicao (download enviado). */
    private static $held = null;

    public static function limit(): int
    {
        return max(1, min(50, ConfigService::getInt('max_parallel_downloads', self::DEFAULT_SLOTS)));
    }

    /** true se conseguiu uma vaga (ou se nao da para controlar: nunca bloqueia por erro local). */
    public static function acquire(?string $directory = null, ?int $limit = null): bool
    {
        if (self::$held !== null) {
            return true;
        }
        $directory ??= (defined('GLPI_TMP_DIR') ? GLPI_TMP_DIR : sys_get_temp_dir()) . '/ativaupdater-downloads';
        if (!is_dir($directory) && !@mkdir($directory, 0770, true) && !is_dir($directory)) {
            return true;
        }
        $limit ??= self::limit();
        for ($slot = 0; $slot < $limit; $slot++) {
            $handle = @fopen($directory . '/slot-' . $slot . '.lock', 'c');
            if ($handle === false) {
                continue;
            }
            if (flock($handle, LOCK_EX | LOCK_NB)) {
                self::$held = $handle;
                return true;
            }
            fclose($handle);
        }
        return false;
    }

    /** Libera a vaga (testes; em producao o fim do processo libera). */
    public static function release(): void
    {
        if (self::$held !== null) {
            flock(self::$held, LOCK_UN);
            fclose(self::$held);
            self::$held = null;
        }
    }
}
