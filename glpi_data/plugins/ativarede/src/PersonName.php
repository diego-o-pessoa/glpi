<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativarede;

/**
 * Nome de quem usa a maquina (Ativa Rede e Ativa Workspace).
 *
 * Ordem:
 *  1. o nome definido a mao em Equipamentos;
 *  2. a conta do Windows / usuario do GLPI, se for de uma pessoa;
 *  3. o primeiro nome tirado da VPN conectada
 *     ("SIMP-ATI-VPN-01-UDP4-1195-atv.ana.csouza" -> "Ana"), guardado: vale
 *     mesmo com a VPN desconectada;
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
     * Primeiro nome no perfil da VPN. O export do pfSense nomeia o perfil
     * "<servidor>-<UDP4|TCP4>-<porta>-<usuario>"; o usuario e "prefixo.nome.sobrenome".
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
        $tokens = array_values(array_filter(preg_split('/[._\s-]+/u', mb_strtolower($user)) ?: [], 'strlen'));
        while (count($tokens) > 1 && in_array($tokens[0], self::VPN_PREFIXES, true)) {
            array_shift($tokens);
        }
        $first = $tokens[0] ?? '';
        if (preg_match('/^\p{L}{2,30}$/u', $first) !== 1 || in_array($first, self::VPN_PREFIXES, true) || self::isGeneric($first)) {
            return '';
        }
        return mb_convert_case($first, MB_CASE_TITLE, 'UTF-8');
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
