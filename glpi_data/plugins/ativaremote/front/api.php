<?php

declare(strict_types=1);

use GlpiPlugin\Ativaremote\ClientRepository;

define('DO_NOT_CHECK_HTTP_REFERER', 1);
define('GLPI_KEEP_CSRF_TOKEN', 1);

include '../../../inc/includes.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

$action = $payload['action'] ?? '';
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';

try {
    $repo = new ClientRepository();

    if ($action === 'register') {
        $result = $repo->register($payload, $ipAddress);
        echo json_encode($result);
        exit;
    } elseif ($action === 'heartbeat') {
        $token = $payload['token'] ?? '';
        if (!$token) {
            throw new Exception("Missing token", 401);
        }
        $client = $repo->authenticate($token);
        $repo->heartbeat($client, $payload, $ipAddress);

        // Fetch again to get updated status
        $updatedClient = $repo->findById((int)$client['id']);
        
        echo json_encode([
            'status' => 'ok',
            'require_consent' => (bool)$updatedClient['require_consent'],
            'remote_access_status' => $updatedClient['remote_access_status'],
            'next_check_seconds' => 10 // poll every 10 seconds for fast remote access response
        ]);
        exit;
    }

    throw new Exception("Unknown action", 400);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['error' => $e->getMessage()]);
}
