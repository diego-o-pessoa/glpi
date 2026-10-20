<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativarede;

/**
 * Nome de quem usa a maquina (Ativa Rede e Ativa Workspace).
 *
 * Ordem:
 *  1. o nome definido a mao em Equipamentos;
 *  2. a conta do Windows / usuario do GLPI, se for de uma pessoa;
 *  3. o nome tirado da VPN conectada
 *     ("SIMP-ATI-VPN-01-UDP4-1195-atv.diego.pessoa" -> "Diego Pessoa";
 *     numeros sao ignorados: "diego.pessoa2" -> "Diego Pessoa"), guardado:
 *     vale mesmo com a VPN desconectada;
 *  4. senao, o que houver (mesmo generico).
 *
 * Conta generica = de uso comum, nao de uma pessoa: "Ativa", "Ativa
 * Locacao", "Ativa - Comercial", "User", "Usuario", "Admin", "Suporte"...
 * O computador no GLPI nao e alterado: o nome so vale nestes dois plugins.
 */
final class PersonName
{
    /** Contas que nao sao de uma pessoa (comparadas sem acento e minusculas). */
    private const GENERIC = [
        'ativa', 'ativa locacao', 'ativalocacao', 'atv', 'user', 'users', 'usuario', 'usuarios', 'usr',
        'admin', 'adm', 'administrador', 'administrator', 'administradores', 'suporte', 'ti', 't i',
        'tecnico', 'guest', 'convidado', 'default', 'defaultuser', 'defaultuser0', 'public', 'publico',
        'teste', 'test', 'local', 'pc', 'computador', 'notebook', 'desktop', 'laptop', 'windows',
        'owner', 'proprietario', 'cliente', 'recepcao', 'comercial', 'financeiro', 'estoque',
    ];

    /** Prefixos antes do nome no usuario da VPN ("atv.ana.csouza"). */
    private const VPN_PREFIXES = ['atv', 'ativa', 'vpn', 'usr', 'user'];

    public static function resolve(string $manual, string $auto, string $vpn): string
    {
        $manual = trim($manual);
        $auto = trim($auto);
        $vpn = trim($vpn);
        foreach ([$manual, $auto, $vpn] as $candidate) {
            if ($candidate !== '' && !self::isGeneric($candidate)) {
                return $candidate;
            }
        }
        return $manual !== '' ? $manual : $auto;
    }

    public static function isGeneric(string $name): bool
    {
        $plain = self::plain($name);
        if ($plain === '') {
            return true;
        }
        if (in_array($plain, self::GENERIC, true)) {
            return true;
        }
        // "Ativa - Comercial", "Ativa Locacao 2", "ativa.ti"...
        if (str_starts_with($plain, 'ativa ')) {
            return true;
        }
        // Nome de maquina no lugar do usuario ("DESKTOP-7QJ63DJ").
        return preg_match('/^(desktop|laptop|notebook|pc|win)\s?[a-z0-9]{4,}$/', $plain) === 1
            && preg_match('/\d/', $plain) === 1;
    }

    /**
     * Nome da pessoa no perfil da VPN. O export do pfSense nomeia o perfil
     * "<servidor>-<UDP4|TCP4>-<porta>-<usuario>"; o usuario e
     * "prefixo.nome.sobrenome" -> "Nome Sobrenome" (numeros ignorados).
     */
    public static function fromVpnProfile(string $profile): string
    {
        $profile = trim(basename(str_replace('\\', '/', $profile)));
        $profile = (string) preg_replace('/\.(ovpn|conf)$/i', '', $profile);
        if ($profile === '') {
            return '';
        }
        if (preg_match('/-(?:udp|tcp)\d?(?:6)?-\d{1,5}-(.+)$/i', $profile, $m)) {
            $user = $m[1];
        } else {
            $pos = strrpos($profile, '-');
            $user = $pos === false ? $profile : substr($profile, $pos + 1);
        }
        // Numeros fora ("diego.pessoa2" -> "diego.pessoa"); so letras ficam.
        $user = (string) preg_replace('/\d+/', '', mb_strtolower($user, 'UTF-8'));
        $tokens = array_values(array_filter(preg_split('/[^\p{L}]+/u', $user) ?: [], 'strlen'));
        while (count($tokens) > 1 && in_array($tokens[0], self::VPN_PREFIXES, true)) {
            array_shift($tokens);
        }
        $first = $tokens[0] ?? '';
        if (mb_strlen($first) < 2 || in_array($first, self::VPN_PREFIXES, true) || self::isGeneric($first)) {
            return '';
        }
        // Nome + sobrenome(s); letra solta ("a") e prefixo repetido ficam de fora.
        $parts = array_filter(array_slice($tokens, 0, 4), static fn(string $t): bool => mb_strlen($t) >= 2);
        return mb_substr(implode(' ', array_map(
            static fn(string $t): string => mb_convert_case($t, MB_CASE_TITLE, 'UTF-8'),
            $parts
        )), 0, 64);
    }

    /** "DOMINIO\usuario" ou "usuario@dominio" -> "usuario". */
    public static function shortUser(string $user): string
    {
        $user = trim($user);
        if (str_contains($user, '\\')) {
            $user = substr($user, strrpos($user, '\\') + 1);
        }
        if (str_contains($user, '@')) {
            $user = substr($user, 0, strpos($user, '@'));
        }
        return trim($user);
    }

    // ------------------------------------------------------------------
    // Regra unica para todos os plugins Ativa (Wallpaper, Guardian,
    // Updater, Remote, Workspace...): quem usa um computador.
    // ------------------------------------------------------------------

    /** @var array{id: array<int, array{manual: string, vpn: string}>, host: array<string, array{manual: string, vpn: string}>}|null */
    private static ?array $directory = null;

    /**
     * Nome da pessoa de um computador: o definido em Ativa Rede > Equipamentos;
     * senao a conta do Windows, se for de uma pessoa; senao o nome da VPN.
     * Sem nada melhor, a propria conta do Windows (mesmo generica).
     */
    public static function forComputer(int $computersId, string $hostname, string $windowsUser): string
    {
        $windowsUser = self::shortUser($windowsUser);
        $entry = self::lookup($computersId, $hostname);
        if ($entry === null) {
            return $windowsUser;
        }
        return self::resolve($entry['manual'], $windowsUser, $entry['vpn']);
    }

    /** @return array{manual: string, vpn: string}|null */
    private static function lookup(int $computersId, string $hostname): ?array
    {
        global $DB;

        if (self::$directory === null) {
            self::$directory = ['id' => [], 'host' => []];
            $table = Settings::TABLE_MACHINES;
            if ($DB->tableExists($table) && $DB->fieldExists($table, 'user_label')) {
                $hasVpn = $DB->fieldExists($table, 'vpn_user');
                $fields = array_merge(['computers_id', 'hostname', 'user_label'], $hasVpn ? ['vpn_profile', 'vpn_user'] : []);
                // Mais antigo primeiro: o relato mais recente sobrescreve.
                foreach ($DB->request(['SELECT' => $fields, 'FROM' => $table, 'ORDER' => ['last_report ASC']]) as $row) {
                    $entry = ['manual' => trim((string) $row['user_label']), 'vpn' => self::vpnName($row)];
                    if ($entry['manual'] === '' && $entry['vpn'] === '') {
                        continue;
                    }
                    if ((int) $row['computers_id'] > 0) {
                        self::$directory['id'][(int) $row['computers_id']] = $entry;
                    }
                    self::$directory['host'][self::hostKey((string) $row['hostname'])] = $entry;
                }
            }
        }
        return self::$directory['id'][$computersId] ?? self::$directory['host'][self::hostKey($hostname)] ?? null;
    }

    /** Nome da VPN de uma linha de maquinas: refeito do perfil (regra atual), senao o guardado. */
    public static function vpnName(array $row): string
    {
        $fromProfile = self::fromVpnProfile((string) ($row['vpn_profile'] ?? ''));
        return $fromProfile !== '' ? $fromProfile : trim((string) ($row['vpn_user'] ?? ''));
    }

    private static function hostKey(string $hostname): string
    {
        // "PC01.dominio.local" e "pc01" sao o mesmo computador.
        return mb_strtolower(explode('.', trim($hostname))[0]);
    }

    /** Minusculas, sem acento, so letras/numeros separados por um espaco. */
    private static function plain(string $name): string
    {
        $name = mb_strtolower(trim($name), 'UTF-8');
        $name = strtr($name, [
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'é' => 'e', 'ê' => 'e', 'è' => 'e', 'ë' => 'e',
            'í' => 'i', 'î' => 'i', 'ì' => 'i', 'ï' => 'i', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ò' => 'o', 'ö' => 'o',
            'ú' => 'u', 'û' => 'u', 'ù' => 'u', 'ü' => 'u', 'ç' => 'c', 'ñ' => 'n',
        ]);
        // "DOMINIO\usuario" e "usuario@dominio": so o usuario.
        if (str_contains($name, '\\')) {
            $name = substr($name, strrpos($name, '\\') + 1);
        }
        if (str_contains($name, '@')) {
            $name = substr($name, 0, strpos($name, '@'));
        }
        return trim((string) preg_replace('/[^a-z0-9]+/', ' ', $name));
    }
}
