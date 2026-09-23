<?php

declare(strict_types=1);

use GlpiPlugin\Ativaworkspace\Event;
use GlpiPlugin\Ativaworkspace\Page;

include('../../../inc/includes.php');

Page::requireAccess('logs');

$level = strtoupper((string) ($_GET['level'] ?? ''));
$level = in_array($level, Event::LEVELS, true) ? $level : null;

$limit = 50;
$total = Event::countByLevel($level);
$pages = max(1, (int) ceil($total / $limit));
$page  = min($pages, max(1, (int) ($_GET['page'] ?? 1)));

$counts = [];
foreach (Event::LEVELS as $name) {
    $counts[$name] = Event::countByLevel($name);
}

Page::render('logs', 'logs.html.twig', [
    'events'   => Event::recent($limit, $level, ($page - 1) * $limit),
    'levels'   => Event::LEVELS,
    'level'    => $level,
    'counts'   => $counts,
    'all_count' => array_sum($counts),
    'total'    => $total,
    'page'     => $page,
    'pages'    => $pages,
    'base_url' => Page::href('logs'),
]);
