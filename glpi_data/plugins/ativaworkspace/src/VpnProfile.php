<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace;

use GLPIKey;
use RuntimeException;

/**
 * Configuracao do OpenVPN.
 *
 * - Perfil: .zip enviado na etapa (guardado como os instaladores, fora da area
 *   publica). Validado no upload: sem caminhos absolutos/"..", sem executaveis
 *   ou scripts, com pelo menos um .ovpn e sem diretivas que rodam comandos.
 * - Credenciais: usuario/senha da VPN do funcionario, digitados no job. A senha
 *   fica criptografada (GLPIKey), vai uma vez ao executor da maquina certa e e
 *   apagada quando a etapa conclui, o job termina ou e cancelado.
 */
final class VpnProfile
{
    private const MAX_ENTRIES = 50;
    private const MAX_UNCOMPRESSED = 10 * 1024 * 1024;
    private const MIN_ZIP_BYTES = 64;

    /** Extensoes aceitas dentro do .zip (perfil, certificados, chaves, textos). */
    private const ALLOWED_EXTENSIONS = ['ovpn', 'conf', 'crt', 'cer', 'pem', 'key', 'p12', 'pfx', 'txt', 'tlsauth', 'ta'];

    /** Diretivas do OpenVPN que executam programas/scripts na maquina. */
    private const FORBIDDEN_DIRECTIVES = [
        'up', 'down', 'route-up', 'route-pre-down', 'ipchange', 'tls-verify', 'auth-user-pass-verify',
        'client-connect', 'client-disconnect', 'learn-address', 'plugin', 'script-security',
    ];

    /**
     * Guarda e valida o .zip enviado na etapa.
     *
     * @param array{name?: string, tmp_name?: string, size?: int, error?: int} $upload
     * @return array{vpn_file: string, vpn_file_name: string, vpn_file_sha256: string, vpn_ovpn: string}
     * @throws RuntimeException mensagem pronta para o usuario
     */
    public static function storeUpload(array $upload): array
    {
        $stored = InstallerStorage::store($upload, 'ZIP', self::MIN_ZIP_BYTES);
        try {
            $ovpn = self::validateZip((string) InstallerStorage::path($stored['file_stored_name']));
        } catch (RuntimeException $exception) {
            InstallerStorage::delete($stored['file_stored_name']);
            throw $exception;
        }
        return [
            'vpn_file'        => $stored['file_stored_name'],
            'vpn_file_name'   => $stored['file_name'],
            'vpn_file_sha256' => $stored['file_sha256'],
            'vpn_ovpn'        => $ovpn,
        ];
    }

    /**
     * Confere o conteudo do .zip. Devolve o caminho (dentro do zip) do .ovpn.
     *
     * @throws RuntimeException
     */
    public static function validateZip(string $path): string
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new RuntimeException('A extensão zip do PHP não está disponível no servidor.');
        }
        $zip = new \ZipArchive();
        if ($path === '' || $zip->open($path) !== true) {
            throw new RuntimeException('Arquivo .zip inválido ou corrompido.');
        }
        try {
            if ($zip->numFiles < 1 || $zip->numFiles > self::MAX_ENTRIES) {
                throw new RuntimeException('O .zip deve ter entre 1 e ' . self::MAX_ENTRIES . ' arquivos.');
            }
            $total = 0;
            $ovpn = [];
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                $name = str_replace('\\', '/', (string) ($stat['name'] ?? ''));
                if ($name === '' || str_starts_with($name, '/') || preg_match('#(^|/)\.\.(/|$)#', $name) || preg_match('/^[a-z]:/i', $name)) {
                    throw new RuntimeException('O .zip tem um caminho inválido: ' . $name);
                }
                if (str_ends_with($name, '/')) {
                    continue; // pasta
                }
                $total += (int) ($stat['size'] ?? 0);
                if ($total > self::MAX_UNCOMPRESSED) {
                    throw new RuntimeException('O conteúdo do .zip passa de 10 MB.');
                }
                $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
                    throw new RuntimeException('Arquivo não permitido no .zip: ' . basename($name)
                        . '. Aceitos: ' . implode(', ', array_map(static fn ($e) => '.' . $e, self::ALLOWED_EXTENSIONS)) . '.');
                }
                if ($extension === 'ovpn') {
                    self::assertSafeConfig((string) $zip->getFromIndex($i), basename($name));
                    $ovpn[] = $name;
                }
            }
            if ($ovpn === []) {
                throw new RuntimeException('Nenhum arquivo .ovpn encontrado no .zip.');
            }
            if (count($ovpn) > 1) {
                throw new RuntimeException('O .zip tem mais de um .ovpn; envie só o perfil que será usado.');
            }
            return $ovpn[0];
        } finally {
            $zip->close();
        }
    }

    /** Recusa perfis que executam comandos (up/down/plugin/script-security...). */
    private static function assertSafeConfig(string $content, string $name): void
    {
        foreach (preg_split('/\R/', $content) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || $line[0] === ';') {
                continue;
            }
            $directive = strtolower((string) strtok(ltrim($line, '-'), " \t"));
            if (in_array($directive, self::FORBIDDEN_DIRECTIVES, true)) {
                throw new RuntimeException(sprintf(
                    'O perfil %s usa a diretiva "%s", que executa comandos na máquina. Remova-a e envie de novo.',
                    $name,
                    $directive
                ));
            }
        }
    }

    // ------------------------------------------------------------ credenciais

    /** Criptografa a senha da VPN para gravar no job. */
    public static function encryptSecret(string $password): string
    {
        return (string) (new GLPIKey())->encrypt($password);
    }

    public static function decryptSecret(string $stored): string
    {
        if ($stored === '') {
            return '';
        }
        return (string) (new GLPIKey())->decrypt($stored);
    }

    /** Apaga usuario/senha da VPN do job (etapa concluida, job encerrado/cancelado). */
    public static function clearCredentials(int $jobId): void
    {
        global $DB;

        $DB->update(Job::getTable(), ['vpn_user' => '', 'vpn_secret' => ''], ['id' => $jobId]);
    }

    /** A etapa (config JSON) e uma configuracao OpenVPN? */
    public static function isVpnConfig(mixed $config): bool
    {
        if (is_string($config)) {
            $config = json_decode($config, true);
        }
        return is_array($config)
            && ($config['configuration_key'] ?? '') === StepType::OPENVPN;
    }
}
