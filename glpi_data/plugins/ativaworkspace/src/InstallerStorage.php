<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace;

use RuntimeException;
use Toolbox;

/**
 * Armazenamento dos instaladores do catalogo.
 *
 * - Fica em GLPI_PLUGIN_DOC_DIR/ativaworkspace/installers (files/_plugins),
 *   fora do public/ do plugin: o navegador nao alcanca o arquivo por URL.
 * - O nome no disco e aleatorio (hex) + extensao validada; o nome original,
 *   sanitizado, fica so no banco. Sem path traversal e sem sobrescrita.
 * - Nada aqui executa o arquivo: so le cabecalhos para validar o tipo.
 */
final class InstallerStorage
{
    /** Teto do plugin; o limite real e o menor entre este e o do PHP. */
    public const MAX_BYTES = 2 * 1024 * 1024 * 1024;

    /**
     * Tipos de instalador: extensoes aceitas e assinatura (magic bytes) esperada.
     * OUTRO aceita pacotes do Windows que nao sao scripts (nada de .ps1/.bat).
     */
    public const TYPES = [
        'MSI'   => ['label' => 'MSI',   'extensions' => ['msi'], 'magic' => ["\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1"]],
        'EXE'   => ['label' => 'EXE',   'extensions' => ['exe'], 'magic' => ['MZ']],
        'ZIP'   => ['label' => 'ZIP',   'extensions' => ['zip'], 'magic' => ["PK\x03\x04"]],
        'OTHER' => [
            'label'      => 'Outro',
            'extensions' => ['msix', 'msixbundle', 'appx', 'appxbundle', 'cab', 'msp'],
            'magic'      => ["PK\x03\x04", 'MSCF', "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1"],
        ],
    ];

    public const SIGNATURE_LABELS = [
        'not_checked'       => 'Não verificada',
        'signed_unverified' => 'Assinado (cadeia não validada)',
        'unsigned'          => 'Sem assinatura digital',
    ];

    public static function directory(): string
    {
        return GLPI_PLUGIN_DOC_DIR . '/ativaworkspace/installers';
    }

    /** Cria o diretorio com permissao restrita e um .htaccess de defesa extra. */
    public static function ensureDirectory(): void
    {
        $dir = self::directory();
        if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) {
            throw new RuntimeException('Não foi possível criar o diretório dos instaladores.');
        }
        @chmod($dir, 0750);
        // Mesmo que files/ fique exposto por engano no servidor web, nega tudo.
        $htaccess = $dir . '/.htaccess';
        if (!is_file($htaccess)) {
            file_put_contents($htaccess, "Require all denied\n");
        }
    }

    /** Limite efetivo de upload (plugin x upload_max_filesize x post_max_size). */
    public static function effectiveMaxBytes(): int
    {
        $limits = [self::MAX_BYTES];
        foreach (['upload_max_filesize', 'post_max_size'] as $ini) {
            $value = (int) Toolbox::return_bytes_from_ini_vars((string) ini_get($ini));
            if ($value > 0) {
                $limits[] = $value;
            }
        }
        return min($limits);
    }

    /** Extensoes aceitas por tipo, para o atributo accept do input. */
    public static function acceptAttribute(): string
    {
        $extensions = [];
        foreach (self::TYPES as $type) {
            foreach ($type['extensions'] as $extension) {
                $extensions[] = '.' . $extension;
            }
        }
        return implode(',', array_unique($extensions));
    }

    /**
     * Nome original sanitizado: so o nome-base, caracteres seguros, sem
     * pontos no inicio, ate 180 caracteres. Nunca e usado como caminho.
     */
    public static function sanitizeName(string $name): string
    {
        $name = basename(str_replace('\\', '/', $name));
        $name = preg_replace('/[^\p{L}\p{N} ._()+-]/u', '_', $name) ?? '';
        $name = ltrim(trim($name), '.');
        if (mb_strlen($name) > 180) {
            $extension = pathinfo($name, PATHINFO_EXTENSION);
            $name = mb_substr(pathinfo($name, PATHINFO_FILENAME), 0, 170) . '.' . $extension;
        }
        return $name;
    }

    /**
     * Valida e guarda um upload ($_FILES[...]). Nao sobrescreve nada: sempre
     * cria um arquivo novo. Quem chama remove o antigo depois de salvar o banco.
     *
     * @param array{name?: string, tmp_name?: string, size?: int, error?: int} $upload
     * @return array{file_name: string, file_stored_name: string, file_size: int, file_sha256: string, file_signature_status: string}
     * @throws RuntimeException mensagem pronta para o usuario
     */
    public static function store(array $upload, string $installerType): array
    {
        $error = (int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException(self::uploadErrorMessage($error));
        }

        $tmp = (string) ($upload['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new RuntimeException('Upload inválido.');
        }

        $type = self::TYPES[$installerType] ?? null;
        if ($type === null) {
            throw new RuntimeException('Tipo de instalador inválido.');
        }

        $name = self::sanitizeName((string) ($upload['name'] ?? ''));
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if ($name === '' || $extension === '' || !in_array($extension, $type['extensions'], true)) {
            throw new RuntimeException(sprintf(
                'Para o tipo %s envie um arquivo %s.',
                $type['label'],
                implode(', ', array_map(static fn ($e) => '.' . $e, $type['extensions']))
            ));
        }

        $size = (int) filesize($tmp);
        if ($size < 1024) {
            throw new RuntimeException('O arquivo é pequeno demais para ser um instalador.');
        }
        if ($size > self::effectiveMaxBytes()) {
            throw new RuntimeException('O arquivo passa do limite de ' . Toolbox::getSize(self::effectiveMaxBytes()) . '.');
        }

        // Conteudo tem que bater com o tipo (um .msi renomeado de .exe e recusado).
        $head = (string) file_get_contents($tmp, false, null, 0, 16);
        $matches = false;
        foreach ($type['magic'] as $magic) {
            if (str_starts_with($head, $magic)) {
                $matches = true;
                break;
            }
        }
        if (!$matches) {
            throw new RuntimeException('O conteúdo do arquivo não corresponde ao tipo ' . $type['label'] . '.');
        }

        $sha256 = hash_file('sha256', $tmp);
        if ($sha256 === false) {
            throw new RuntimeException('Não foi possível calcular o SHA-256 do arquivo.');
        }
        $signature = $installerType === 'EXE' ? self::peSignatureStatus($tmp) : 'not_checked';

        self::ensureDirectory();
        // Nome aleatorio: nao colide, nao sobrescreve, nao carrega nada do usuario.
        $stored = bin2hex(random_bytes(16)) . '.' . $extension;
        $destination = self::directory() . '/' . $stored;
        if (file_exists($destination) || !move_uploaded_file($tmp, $destination)) {
            throw new RuntimeException('Não foi possível armazenar o arquivo no servidor.');
        }
        @chmod($destination, 0640);

        return [
            'file_name'             => $name,
            'file_stored_name'      => $stored,
            'file_size'             => $size,
            'file_sha256'           => $sha256,
            'file_signature_status' => $signature,
        ];
    }

    /**
     * Caminho absoluto de um arquivo guardado, ou null se o nome nao for um
     * nome gerado por store() ou o arquivo nao existir dentro do diretorio.
     */
    public static function path(string $storedName): ?string
    {
        if (!preg_match('/^[a-f0-9]{32}\.[a-z0-9]{2,12}$/D', $storedName)) {
            return null;
        }
        $base = realpath(self::directory());
        if ($base === false) {
            return null;
        }
        $path = realpath($base . DIRECTORY_SEPARATOR . $storedName);
        if ($path === false || !is_file($path) || !str_starts_with($path, $base . DIRECTORY_SEPARATOR)) {
            return null;
        }
        return $path;
    }

    public static function delete(string $storedName): void
    {
        $path = self::path($storedName);
        if ($path !== null) {
            @unlink($path);
        }
    }

    /** Remove todos os instaladores (desinstalacao do plugin). */
    public static function removeAll(): void
    {
        $dir = self::directory();
        if (!is_dir($dir)) {
            return;
        }
        foreach (glob($dir . '/*') ?: [] as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
        @unlink($dir . '/.htaccess');
        @rmdir($dir);
        @rmdir(dirname($dir));
    }

    /**
     * Presenca de assinatura Authenticode num PE, so lendo o cabecalho (nao
     * executa e nao valida a cadeia de certificados - isso fica para o executor).
     */
    public static function peSignatureStatus(string $path): string
    {
        $handle = @fopen($path, 'rb');
        if ($handle === false) {
            return 'not_checked';
        }
        try {
            $dos = fread($handle, 64);
            if (strlen((string) $dos) < 64 || !str_starts_with((string) $dos, 'MZ')) {
                return 'not_checked';
            }
            $peOffset = unpack('V', substr($dos, 60, 4))[1];
            if ($peOffset <= 0 || fseek($handle, $peOffset) !== 0 || fread($handle, 4) !== "PE\0\0") {
                return 'not_checked';
            }
            fseek($handle, $peOffset + 24);
            $magic = unpack('v', (string) fread($handle, 2))[1] ?? 0;
            // Diretorio de seguranca (indice 4): PE32 em +128, PE32+ em +144 do optional header.
            $securityOffset = match ($magic) {
                0x10B   => $peOffset + 24 + 128,
                0x20B   => $peOffset + 24 + 144,
                default => 0,
            };
            if ($securityOffset === 0 || fseek($handle, $securityOffset) !== 0) {
                return 'not_checked';
            }
            $entry = fread($handle, 8);
            if (strlen((string) $entry) !== 8) {
                return 'not_checked';
            }
            $values = unpack('Vaddress/Vsize', $entry);
            return ((int) $values['address'] > 0 && (int) $values['size'] > 0) ? 'signed_unverified' : 'unsigned';
        } finally {
            fclose($handle);
        }
    }

    private static function uploadErrorMessage(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'O arquivo passa do limite de upload do servidor ('
                . Toolbox::getSize(self::effectiveMaxBytes()) . ').',
            UPLOAD_ERR_PARTIAL    => 'O envio do arquivo foi interrompido. Tente de novo.',
            UPLOAD_ERR_NO_FILE    => 'Nenhum arquivo foi enviado.',
            UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE => 'O servidor não conseguiu gravar o arquivo temporário.',
            default               => 'Falha no envio do arquivo (código ' . $error . ').',
        };
    }
}
