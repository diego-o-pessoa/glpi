<?php

/**
 * Script de testes manuais da API do Ativa Updater.
 * Uso: 
 * 1. Configure o $token com o valor gerado no GLPI.
 * 2. Ajuste a $base_url para o endereço do seu servidor GLPI.
 * 3. Execute: php api_tests.php
 */

$token = "COLOQUE_O_TOKEN_AQUI";
$base_url = "http://localhost/plugins/ativaupdater/api/v1";

echo "=============================================\n";
echo "Testando API do Ativa Updater\n";
echo "=============================================\n\n";

echo "[1] Testando GET /latest\n";
$ch = curl_init("$base_url/latest");
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "HTTP Code: $http_code\n";
echo "Response: $response\n\n";
curl_close($ch);

if ($http_code === 200) {
    $data = json_decode($response, true);
    if (isset($data['version'])) {
        $version = $data['version'];
        echo "[2] Testando GET /releases/$version\n";
        $ch2 = curl_init("$base_url/releases/$version");
        curl_setopt($ch2, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        $res2 = curl_exec($ch2);
        $code2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
        echo "HTTP Code: $code2\n";
        echo "Response: $res2\n\n";
        curl_close($ch2);
        
        echo "[3] Testando Download (apenas lendo headers)\n";
        $ch3 = curl_init("$base_url/download/$version");
        curl_setopt($ch3, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
        curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch3, CURLOPT_HEADER, true);
        curl_setopt($ch3, CURLOPT_NOBODY, true);
        $res3 = curl_exec($ch3);
        $code3 = curl_getinfo($ch3, CURLINFO_HTTP_CODE);
        echo "HTTP Code: $code3\n";
        echo "Headers: \n$res3\n";
        curl_close($ch3);
    }
} else {
    echo "Falha ao obter /latest. Verifique o token e a URL.\n";
}

echo "Testes concluídos.\n";
