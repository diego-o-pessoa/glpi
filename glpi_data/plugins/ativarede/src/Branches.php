<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativarede;

use Config;
use Entity;
use Location;

/**
 * Filial de cada maquina, para separar Equipamentos por filial.
 *
 * 1. Rede: o IP do switch onde a maquina esta ligada e, sem switch (Wi-Fi), o
 *    IP da propria maquina, pelas regras "192.168.80 = Matriz" (editaveis na
 *    tela; o prefixo mais longo vence).
 * 2. GLPI: a entidade do computador (se nao for a raiz) ou a localizacao,
 *    pelo nome curto ("Ativa Locacao - Matriz" -> "Matriz").
 * 3. Senao, vazio ("Sem filial").
 */
final class Branches
{
    public const SETTING = 'branch_networks';
    public const DEFAULT_RULES = '192.168.80 = Matriz';

    /** @var list<array{prefix: string, name: string}>|null */
    private static ?array $rules = null;

    /** @var array<string, string> */
    private static array $glpiNames = [];

    /** @return list<array{prefix: string, name: string}> */
    public static function rules(): array
    {
        if (self::$rules === null) {
            self::$rules = self::parse(self::rulesText())['rules'];
        }
        return self::$rules;
    }

    public static function rulesText(): string
    {
        $values = Config::getConfigurationValues(Settings::CONTEXT, [self::SETTING]);
        return (string) ($values[self::SETTING] ?? self::DEFAULT_RULES);
    }

    public static function save(string $text): array
    {
        $parsed = self::parse($text);
        if ($parsed['errors'] !== []) {
            return ['ok' => false, 'message' => 'Não salvei: ' . implode(' ', $parsed['errors'])];
        }
        $lines = array_map(static fn(array $r): string => $r['prefix'] . ' = ' . $r['name'], $parsed['rules']);
        Config::setConfigurationValues(Settings::CONTEXT, [self::SETTING => implode("\n", $lines)]);
        self::$rules = null;
        return ['ok' => true, 'message' => count($lines) === 0
            ? 'Regras de filial apagadas: a filial vem só do GLPI.'
            : 'Filiais por rede salvas (' . count($lines) . ').'];
    }

    /** @return array{rules: list<array{prefix: string, name: string}>, errors: list<string>} */
    public static function parse(string $text): array
    {
        $rules = [];
        $errors = [];
        foreach (preg_split('/\R/', $text) ?: [] as $number => $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (!preg_match('/^(\d{1,3}(?:\.\d{1,3}){0,3})\.?\s*(?:=|:)\s*(.{1,64})$/u', $line, $m)) {
                $errors[] = 'linha ' . ($number + 1) . ' ("' . mb_substr($line, 0, 40) . '") deve ser como "192.168.80 = Matriz".';
                continue;
            }
            $prefix = $m[1];
            foreach (explode('.', $prefix) as $octet) {
                if ((int) $octet > 255) {
                    $errors[] = 'linha ' . ($number + 1) . ': "' . $prefix . '" não é uma rede válida.';
                    continue 2;
                }
            }
            $rules[] = ['prefix' => $prefix, 'name' => trim($m[2])];
        }
        // Prefixo mais longo primeiro: "192.168.80.2" vence "192.168.80".
        usort($rules, static fn(array $a, array $b): int => strlen($b['prefix']) <=> strlen($a['prefix']));
        return ['rules' => $rules, 'errors' => $errors];
    }

    public static function byIp(string $ip): string
    {
        $ip = trim($ip);
        if ($ip === '') {
            return '';
        }
        foreach (self::rules() as $rule) {
            if ($ip === $rule['prefix'] || str_starts_with($ip, $rule['prefix'] . '.')) {
                return $rule['name'];
            }
        }
        return '';
    }

    /**
     * @param array<string, mixed> $machine Linha de Inventory::decoratedMachines()
     * @param array<int, array<string, mixed>> $switches Inventory::switches()
     */
    public static function ofMachine(array $machine, array $switches): string
    {
        $switchIp = (string) ($switches[(int) ($machine['switches_id'] ?? 0)]['mgmt_ip'] ?? '');
        return self::byIp($switchIp)
            ?: self::byIp((string) ($machine['ip'] ?? ''))
            ?: self::fromGlpi((int) ($machine['computer_entity'] ?? 0), (int) ($machine['computer_location'] ?? 0));
    }

    public static function fromGlpi(int $entityId, int $locationId): string
    {
        $key = $entityId . '|' . $locationId;
        if (!array_key_exists($key, self::$glpiNames)) {
            $name = '';
            $entity = new Entity();
            // A raiz ("Grupo Ativa") nao e filial.
            if ($entityId > 0 && $entity->getFromDB($entityId) && (int) $entity->fields['level'] > 1) {
                $name = self::shortName((string) $entity->fields['name']);
            }
            $location = new Location();
            if ($name === '' && $locationId > 0 && $location->getFromDB($locationId)) {
                $name = self::shortName((string) $location->fields['name']);
            }
            self::$glpiNames[$key] = $name;
        }
        return self::$glpiNames[$key];
    }

    /**
     * Nome curto da filial: separa por " - ", descarta codigos ("001001") e o
     * nome da empresa ("Ativa Locacao"); fica o ultimo que sobrar.
     */
    public static function shortName(string $name): string
    {
        $parts = array_values(array_filter(
            array_map('trim', explode(' - ', trim($name))),
            static fn(string $part): bool => $part !== '' && preg_match('/^[\d.\/-]+$/', $part) !== 1,
        ));
        if ($parts === []) {
            return trim($name);
        }
        $named = array_values(array_filter($parts, static fn(string $part): bool => preg_match('/^ativa\b/iu', $part) !== 1));
        return $named !== [] ? end($named) : end($parts);
    }
}
