<?php

/**
 * Teste manual da API de heartbeat do Ativa Guardian.
 * Uso:
 *   1. Baixe o token em GLPI > Configurar > Plugins > Ativa Guardian (ou pela
 *      engrenagem do plugin) e cole abaixo.
 *   2. Ajuste $base_url para o seu servidor.
 *   3. Execute: php heartbeat_test.php
 *
 * Não depende do GLPI: usa apenas curl para bater na API real.
 */

$token    = 'COLOQUE_O_TOKEN_AQUI';
$base_url = 'https://chamados.ativalocacao.com.br:8443/plugins/ativaguardian/api/v1';

function call(string $method, string $url, string $token, ?array $body = null): array
{
    $ch = curl_init($url);
    $headers = ['Authorization: Bearer ' . $token, 'Accept: application/json'];
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($body !== null) {
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['status' => $status, 'body' => $response];
}

echo "=============================================\n";
echo "Testando API do Ativa Guardian\n";
echo "=============================================\n\n";

echo "[1] GET /health (público)\n";
$r = call('GET', "$base_url/health", $token);
echo "  HTTP {$r['status']} -> {$r['body']}\n\n";

echo "[2] POST /heartbeat (máquina de exemplo)\n";
$heartbeat = [
    'machine_id'       => 'TESTE-GUARDIAN-0001',
    'hostname'         => 'PC-TESTE-01',
    'guardian_version' => '1.0.0',
    'antivirus'        => 'Microsoft Defender',
    'components'       => [
        'wallpaper'  => ['version' => '1.4.1', 'status' => 'healthy'],
        'updater'    => ['version' => '1.4.3', 'status' => 'service_stopped'],
        'remote'     => ['status' => 'healthy'],
        'glpi_agent' => ['status' => 'healthy'],
    ],
];
$r = call('POST', "$base_url/heartbeat", $token, $heartbeat);
echo "  HTTP {$r['status']} -> {$r['body']}\n";
echo "  (esperado: 202 e a máquina PC-TESTE-01 aparecendo na dashboard com Updater = 'Serviço parado')\n\n";

echo "[3] POST /heartbeat com status inválido (deve ser rejeitado)\n";
$bad = $heartbeat;
$bad['components']['updater']['status'] = 'explodindo';
$r = call('POST', "$base_url/heartbeat", $token, $bad);
echo "  HTTP {$r['status']} -> {$r['body']}\n";
echo "  (esperado: 422 INVALID_COMPONENT)\n\n";

echo "[4] POST /heartbeat sem token (deve ser rejeitado)\n";
$r = call('POST', "$base_url/heartbeat", 'token-errado', $heartbeat);
echo "  HTTP {$r['status']} -> {$r['body']}\n";
echo "  (esperado: 403 FORBIDDEN)\n\n";

echo "Concluido.\n";
