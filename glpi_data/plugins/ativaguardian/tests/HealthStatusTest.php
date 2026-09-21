<?php

declare(strict_types=1);

/**
 * Teste unitário da lógica de status, sem depender do GLPI.
 * Uso: php tests/HealthStatusTest.php
 *
 * Cobre o cálculo do status geral (o pior dos componentes, com offline
 * sobrepondo tudo) e a validação de nomes e status de componente.
 */

require_once __DIR__ . '/../src/HealthStatus.php';

use GlpiPlugin\Ativaguardian\HealthStatus;

$failures = 0;
$assert = static function (string $name, $expected, $actual) use (&$failures): void {
    if ($expected === $actual) {
        echo "  ok   {$name}\n";
        return;
    }
    $failures++;
    echo "  FAIL {$name}: esperado " . var_export($expected, true) . ', obtido ' . var_export($actual, true) . "\n";
};

echo "HealthStatus::overall\n";
$assert('todos saudáveis => healthy', HealthStatus::HEALTHY, HealthStatus::overall(['healthy', 'healthy'], false));
$assert('sem componentes => unknown', HealthStatus::UNKNOWN, HealthStatus::overall([], false));
$assert('um warning => warning', HealthStatus::WARNING, HealthStatus::overall(['healthy', 'warning'], false));
$assert('version_outdated => warning', HealthStatus::WARNING, HealthStatus::overall(['healthy', 'version_outdated'], false));
$assert('service_stopped => error', HealthStatus::ERROR, HealthStatus::overall(['healthy', 'service_stopped'], false));
$assert('file_missing => error', HealthStatus::ERROR, HealthStatus::overall(['file_missing'], false));
$assert('pior vence (warning+error) => error', HealthStatus::ERROR, HealthStatus::overall(['warning', 'error'], false));
$assert('offline sobrepõe saudáveis', HealthStatus::OFFLINE, HealthStatus::overall(['healthy', 'healthy'], true));
$assert('offline sobrepõe erro', HealthStatus::OFFLINE, HealthStatus::overall(['error'], true));
$assert('status desconhecido tratado como unknown', HealthStatus::UNKNOWN, HealthStatus::overall(['coisa_estranha'], false));
$assert('unknown não vira erro', HealthStatus::UNKNOWN, HealthStatus::overall(['healthy', 'unknown'], false));

echo "HealthStatus::isValidComponentStatus\n";
$assert('healthy é válido', true, HealthStatus::isValidComponentStatus('healthy'));
$assert('service_stopped é válido', true, HealthStatus::isValidComponentStatus('service_stopped'));
$assert('offline NÃO é reportável por componente', false, HealthStatus::isValidComponentStatus('offline'));
$assert('status inventado é inválido', false, HealthStatus::isValidComponentStatus('exploding'));

echo "HealthStatus::isValidComponentName\n";
$assert('wallpaper é válido', true, HealthStatus::isValidComponentName('wallpaper'));
$assert('glpi_agent é válido', true, HealthStatus::isValidComponentName('glpi_agent'));
$assert('componente futuro (vpn) é válido', true, HealthStatus::isValidComponentName('vpn'));
$assert('maiúsculas são inválidas', false, HealthStatus::isValidComponentName('Wallpaper'));
$assert('espaço é inválido', false, HealthStatus::isValidComponentName('glpi agent'));
$assert('vazio é inválido', false, HealthStatus::isValidComponentName(''));
$assert('injeção é inválida', false, HealthStatus::isValidComponentName("a';DROP"));

echo "\n";
if ($failures === 0) {
    echo "Todos os testes passaram.\n";
    exit(0);
}
echo "{$failures} teste(s) falharam.\n";
exit(1);
