<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace;

/**
 * Dados da Visao Geral / Provisionamento. O mesmo formato vai embutido na
 * pagina (primeiro paint) e no JSON consultado em tempo real.
 */
final class Overview
{
    /**
     * @return array<string, mixed>
     */
    public static function payload(int $jobsLimit, int $eventsLimit, array $filters = []): array
    {
        return [
            'now'    => $_SESSION['glpi_currenttime'] ?? date('Y-m-d H:i:s'),
            'counts' => Job::overviewCounts(),
            'jobs'   => Job::listForPage(max(1, min(100, $jobsLimit)), $filters),
            'events' => $eventsLimit > 0 ? Event::recent(min(50, $eventsLimit)) : [],
        ];
    }
}
