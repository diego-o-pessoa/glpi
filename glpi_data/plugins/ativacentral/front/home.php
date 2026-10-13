<?php

declare(strict_types=1);

use GlpiPlugin\Ativacentral\Hubs;
use GlpiPlugin\Ativacentral\Metrics;
use GlpiPlugin\Ativacentral\Nav;
use GlpiPlugin\Ativacentral\Page;
use GlpiPlugin\Ativacentral\Settings;
use GlpiPlugin\Ativacentral\Shell;

include '../../../inc/includes.php';

// "Visao geral": pagina inicial da interface de administracao.
Session::checkCentralAccess();

$data = Metrics::dashboard(Settings::periodDays());
$data['urls'] = [
    'data'     => Shell::url('/front/home.data.php'),
    'new'      => Shell::glpiUrl('/front/ticket.form.php'),
    'helpdesk' => Shell::glpiUrl(Nav::hubUrl('helpdesk')),
    'open'     => Shell::glpiUrl(Hubs::ticketSearchUrl(['status' => 'notold'])),
    'waiting'  => Shell::glpiUrl(Hubs::ticketSearchUrl(['status' => Ticket::INCOMING])),
    'solved'   => Shell::glpiUrl(Hubs::ticketSearchUrl(['status' => 'old'])),
    'mine'     => Shell::glpiUrl(Hubs::ticketSearchUrl(['status' => 'notold', 'mine' => true])),
    'stats'    => Shell::glpiUrl('/front/stat.php'),
    'computers' => Shell::glpiUrl(Nav::hubUrl('computers')),
    // Paineis nativos do GLPI (continuam disponiveis).
    'native'   => Shell::glpiUrl('/front/central.php?ac_native=1'),
];

Page::render('Visão geral', 'ac_home', 'home', 'home', $data);
