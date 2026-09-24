<?php

declare(strict_types=1);

use GlpiPlugin\Ativaworkspace\Event;
use GlpiPlugin\Ativaworkspace\Job;
use GlpiPlugin\Ativaworkspace\Page;
use GlpiPlugin\Ativaworkspace\RemoteBridge;

include('../../../inc/includes.php');

// Sessao remota de um provisionamento via Ativa Remote (JSON).
// GET  job=ID              -> situacao da sessao
// POST job=ID [ti_password] -> solicita a sessao (CSRF no header, AJAX)
Page::requireAccess('provisioning');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$respond = static function (int $code, array $body): void {
    http_response_code($code);
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
};

$jobId = (int) ($_POST['job'] ?? $_GET['job'] ?? 0);
$job = new Job();
if ($jobId <= 0 || !$job->can($jobId, UPDATE)) {
    Event::log(Event::LEVEL_SECURITY, 'permission', 'Sessão remota negada', ['job' => $jobId], $jobId);
    $respond(403, ['ok' => false, 'message' => 'Sem permissão para este provisionamento.']);
    return;
}
$computersId = (int) $job->fields['computers_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $respond(200, ['ok' => true] + RemoteBridge::status($computersId));
    return;
}

try {
    $status = RemoteBridge::request($computersId, (string) ($_POST['ti_password'] ?? ''), $jobId);
    $respond(200, ['ok' => true] + $status);
} catch (RuntimeException $exception) {
    $code = (int) $exception->getCode();
    $respond(in_array($code, [401, 403, 404, 409], true) ? $code : 422, [
        'ok'                => false,
        'password_required' => $code === 401,
        'message'           => $exception->getMessage(),
    ]);
}
