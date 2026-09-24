<?php

declare(strict_types=1);

use GlpiPlugin\Ativaworkspace\Event;
use GlpiPlugin\Ativaworkspace\GraphClient;
use GlpiPlugin\Ativaworkspace\Job;
use GlpiPlugin\Ativaworkspace\Page;

include('../../../inc/includes.php');

// Gera um Temporary Access Pass (TAP) para a conta do provisionamento (JSON).
// POST job=ID. CSRF validado pelo GLPI (AJAX header).
Page::requireAccess('provisioning');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$respond = static function (int $code, array $body): void {
    http_response_code($code);
    echo json_encode($body, JSON_UNESCAPED_UNICODE);
};

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $respond(405, ['ok' => false, 'message' => 'Método não permitido.']);
    return;
}

$jobId = (int) ($_POST['job'] ?? 0);
$job = new Job();
if ($jobId <= 0 || !$job->can($jobId, UPDATE)) {
    Event::log(Event::LEVEL_SECURITY, 'permission', 'Geração de TAP negada', ['job' => $jobId], $jobId);
    $respond(403, ['ok' => false, 'message' => 'Sem permissão para este provisionamento.']);
    return;
}

$upn = (string) $job->fields['upn'];

// Evita cliques repetidos que invalidam o TAP anterior e geram chamadas
// desnecessarias ao Graph. O limite e por sessao, usuario e provisionamento.
$cooldown = 15;
$rateKey = (string) Session::getLoginUserID() . ':' . $jobId;
$lastAttempt = (int) ($_SESSION['ativaworkspace_tap_last'][$rateKey] ?? 0);
$retryAfter = max(0, $cooldown - (time() - $lastAttempt));
if ($retryAfter > 0) {
    header('Retry-After: ' . $retryAfter);
    $respond(429, [
        'ok'          => false,
        'retry_after' => $retryAfter,
        'message'     => 'Aguarde ' . $retryAfter . ' segundo(s) antes de gerar outro TAP.',
    ]);
    return;
}
$_SESSION['ativaworkspace_tap_last'][$rateKey] = time();

try {
    $tap = GraphClient::createTap($upn);
    // Registra que um TAP foi gerado - NUNCA o codigo em si.
    Event::log(Event::LEVEL_SECURITY, 'entra', 'TAP gerado para ' . $upn, [
        'validade_min' => $tap['lifetime_minutes'],
    ], $jobId);
    $respond(200, ['ok' => true] + $tap);
} catch (RuntimeException $exception) {
    Event::log(Event::LEVEL_WARNING, 'entra', 'Falha ao gerar TAP para ' . $upn, [
        'motivo' => mb_substr($exception->getMessage(), 0, 200),
    ], $jobId);
    $respond(422, ['ok' => false, 'message' => $exception->getMessage()]);
} catch (Throwable $exception) {
    Event::log(Event::LEVEL_ERROR, 'entra', 'Erro inesperado ao gerar TAP para ' . $upn, [
        'tipo' => $exception::class,
    ], $jobId);
    $respond(500, ['ok' => false, 'message' => 'Erro inesperado ao gerar o TAP. Consulte os logs do GLPI.']);
}
