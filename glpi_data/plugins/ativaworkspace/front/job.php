<?php

declare(strict_types=1);

use GlpiPlugin\Ativaworkspace\EntraStep;
use GlpiPlugin\Ativaworkspace\Job;
use GlpiPlugin\Ativaworkspace\JobStep;
use GlpiPlugin\Ativaworkspace\MachineIdentity;
use GlpiPlugin\Ativaworkspace\Page;
use GlpiPlugin\Ativaworkspace\ProvisioningEngine;
use GlpiPlugin\Ativaworkspace\RemoteBridge;
use GlpiPlugin\Ativaworkspace\StepType;
use GlpiPlugin\Ativaworkspace\WorkspaceConfig;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

include('../../../inc/includes.php');

Page::requireAccess('provisioning');

global $CFG_GLPI;

// details() ja restringe as entidades do usuario: fora delas vira 404.
$details = Job::details((int) ($_GET['id'] ?? 0));
if ($details === null) {
    throw new NotFoundHttpException();
}

$job     = $details['job'];
$canManage = (bool) Session::haveRight(PluginAtivaworkspaceProfile::RIGHT_PROVISION, UPDATE);

// Acoes possiveis por etapa (a validacao definitiva e do ProvisioningEngine).
$steps = [];
foreach ($details['steps'] as $step) {
    $isCurrent = $step['is_current'];
    $step['can_retry'] = $canManage && $isCurrent && $job['status'] === Job::FAILED
        && $step['status'] === JobStep::FAILED && $step['attempts'] < $step['max_attempts'];
    $step['retry_exhausted'] = $isCurrent && $step['status'] === JobStep::FAILED && $step['attempts'] >= $step['max_attempts'];
    // So MANUAL_INTERVENTION tem confirmacao manual. Na etapa Entra, a conclusao
    // vem do executor com a prova de ingresso (tenant certo); confirmar na mao
    // pularia essa checagem.
    $step['can_confirm'] = $canManage && $isCurrent && $step['status'] === JobStep::WAITING_HUMAN
        && $step['step_type'] === StepType::MANUAL_INTERVENTION;
    $steps[] = $step;
}

$current = null;
foreach ($steps as $step) {
    if ($step['is_current']) {
        $current = $step;
    }
}

// Painel especifico da etapa Microsoft Entra ID (quando e a etapa atual).
$entra = null;
if ($current !== null && $current['step_type'] === StepType::ENTRA_LOGIN) {
    $runtime = EntraStep::runtime($current['runtime'] ?? null);
    $substate = (string) ($runtime['substate'] ?? '');
    $entra = [
        'upn'          => $job['upn'],
        'substate'     => $substate,
        'substate_msg' => $substate !== '' ? EntraStep::message($substate) : $current['message'],
        'device_id'    => (string) ($runtime['device_id'] ?? ''),
        'tenant_id'    => (string) ($runtime['tenant_id'] ?? ''),
        'remote_url'   => MachineIdentity::remoteDashboardUrl((int) $job['computers_id']),
        'waiting'      => $job['status'] === Job::WAITING_INTERVENTION,
        'remote'       => RemoteBridge::status((int) $job['computers_id']),
        'remote_api'   => Page::href('remote', ['job' => (int) $job['id']]),
        'connect_api'  => $CFG_GLPI['root_doc'] . '/plugins/ativaremote/front/connect.php',
        'csrf'         => Session::getNewCSRFToken(),
        // Geracao automatica do TAP (senha temporaria) via Microsoft Graph.
        'tap_ready'    => WorkspaceConfig::graphConfigured() && $job['upn'] !== '',
        'tap_api'      => Page::href('tap'),
    ];
}

Page::render('provisioning', 'job.html.twig', [
    'job'          => $job,
    'steps'        => $steps,
    'events'       => $details['events'],
    'current'      => $current,
    'entra'        => $entra,
    'can_manage'   => $canManage,
    'can_cancel'   => $canManage && in_array($job['status'], array_merge(Job::ACTIVE, [Job::FAILED]), true),
    'can_simulate' => WorkspaceConfig::canSimulate() && in_array($job['status'], [Job::RUNNING, Job::WAITING_INTERVENTION], true),
    'results'      => ProvisioningEngine::RESULTS,
    'action_url'   => Page::href('job_action'),
    'data_url'     => Page::href('job_data', ['id' => $job['id']]),
    'list_url'     => Page::href('provisioning'),
    'computer_url' => $CFG_GLPI['root_doc'] . '/front/computer.form.php?id=' . $job['computers_id'],
    'refresh_ms'   => Page::REFRESH_MS,
]);
