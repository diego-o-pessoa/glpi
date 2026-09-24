<?php

declare(strict_types=1);

use GlpiPlugin\Ativaworkspace\GraphClient;

require dirname(__DIR__) . '/src/GraphClient.php';

/** Teste unitario pequeno, sem rede e sem bootstrap do GLPI. */
function expect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function base64url(string $value): string
{
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}

$reflection = new ReflectionClass(GraphClient::class);
$claimsMethod = $reflection->getMethod('tokenClaims');
$expirationMethod = $reflection->getMethod('expiration');
$permissionMethod = $reflection->getMethod('tapPermission');

$payload = [
    'tid'   => '11111111-1111-1111-1111-111111111111',
    'aud'   => '00000003-0000-0000-c000-000000000000',
    'roles' => ['UserAuthMethod-TAP.ReadWrite.All'],
    'exp'   => 2000000000,
];
$jwt = base64url('{"alg":"none"}') . '.'
    . base64url((string) json_encode($payload, JSON_THROW_ON_ERROR)) . '.signature';
$decoded = $claimsMethod->invoke(null, $jwt);

expect($decoded['tid'] === $payload['tid'], 'Tenant nao foi extraido do token.');
expect($decoded['roles'][0] === 'UserAuthMethod-TAP.ReadWrite.All', 'App role do TAP nao foi extraida.');
expect(
    $permissionMethod->invoke(null, ['UserAuthMethod-TAP.ReadWrite.All']) === 'UserAuthMethod-TAP.ReadWrite.All',
    'A permissao especifica do TAP deveria ser aceita.'
);
expect(
    $permissionMethod->invoke(null, ['UserAuthenticationMethod.ReadWrite.All']) === 'UserAuthenticationMethod.ReadWrite.All',
    'A permissao ampla compativel deveria ser aceita.'
);
expect($permissionMethod->invoke(null, ['User.Read.All']) === '', 'Uma permissao sem acesso ao TAP foi aceita.');

$invalidRejected = false;
try {
    $claimsMethod->invoke(null, 'token-invalido');
} catch (ReflectionException | RuntimeException $exception) {
    $invalidRejected = true;
}
expect($invalidRejected, 'Token malformado deveria ser recusado.');

$expiresAt = $expirationMethod->invoke(null, '2026-09-24T12:00:00Z', 60);
expect($expiresAt === '2026-09-24T13:00:00+00:00', 'Calculo de expiracao do TAP incorreto.');

echo "GraphClient: 7 verificacoes concluidas.\n";
