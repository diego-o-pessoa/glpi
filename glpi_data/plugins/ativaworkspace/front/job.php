<?php

declare(strict_types=1);

use GlpiPlugin\Ativaworkspace\Job;
use GlpiPlugin\Ativaworkspace\JobStep;
use GlpiPlugin\Ativaworkspace\Page;
use GlpiPlugin\Ativaworkspace\ProvisioningEngine;
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
    $step['can_confirm'] = $canManage && $isCurrent && $step['status'] === JobStep::WAITING_HUMAN;
    $steps[] = $step;
}

$current = null;
foreach ($steps as $step) {
    if ($step['is_current']) {
        $current = $step;
    }
}

Page::render('provisioning', 'job.html.twig', [
    'job'          => $job,
    'steps'        => $steps,
    'events'       => $details['events'],
    'current'      => $current,
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
