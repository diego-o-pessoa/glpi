<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace;

use CommonDBTM;
use PluginAtivaworkspaceProfile;
use Session;

/**
 * Aplicativo do catalogo (glpi_plugin_ativaworkspace_applications).
 * Guarda o instalador e os metadados que o executor usara no futuro.
 * Nesta etapa nada e instalado nem executado.
 */
final class Application extends CommonDBTM
{
    public const CATEGORIES = [
        'browser'       => 'Navegador',
        'communication' => 'Comunicação',
        'productivity'  => 'Produtividade',
        'security'      => 'Segurança e VPN',
        'remote'        => 'Acesso remoto',
        'utility'       => 'Utilitário',
        'business'      => 'Sistema da empresa',
        'other'         => 'Outro',
    ];

    /** Icones permitidos (Tabler), para nao aceitar classe CSS arbitraria. */
    public const ICONS = [
        ''                 => 'Padrão (pela categoria)',
        'ti-brand-chrome'  => 'Navegador (Chrome)',
        'ti-world'         => 'Navegador (genérico)',
        'ti-shield-lock'   => 'VPN / Segurança',
        'ti-brand-teams'   => 'Teams',
        'ti-brand-onedrive'=> 'OneDrive',
        'ti-mail'          => 'E-mail',
        'ti-headset'       => 'Atendimento / Telefonia',
        'ti-device-desktop-share' => 'Acesso remoto',
        'ti-file-text'     => 'Documentos',
        'ti-table'         => 'Planilhas',
        'ti-tool'          => 'Utilitário',
        'ti-building'      => 'Sistema da empresa',
        'ti-package'       => 'Pacote',
    ];

    private const CATEGORY_ICONS = [
        'browser'       => 'ti-world',
        'communication' => 'ti-message-circle',
        'productivity'  => 'ti-briefcase',
        'security'      => 'ti-shield-lock',
        'remote'        => 'ti-device-desktop-share',
        'utility'       => 'ti-tool',
        'business'      => 'ti-building',
        'other'         => 'ti-package',
    ];

    public const ARCHITECTURES = [
        'any'   => 'Qualquer',
        'x64'   => '64 bits (x64)',
        'x86'   => '32 bits (x86)',
        'arm64' => 'ARM64',
    ];

    public const TIMEOUT_MAX_MINUTES = 240;
    public const ATTEMPTS_MAX        = 10;

    /** Campos que so o upload (InstallerStorage::store) preenche. */
    private const FILE_FIELDS = [
        'file_name', 'file_stored_name', 'file_size', 'file_sha256', 'file_signature_status',
        'file_uploaded_at', 'file_users_id',
    ];

    public static $rightname = PluginAtivaworkspaceProfile::RIGHT_APPLICATIONS;

    public $dohistory = true;

    public static function getTypeName($nb = 0): string
    {
        return $nb > 1 ? 'Aplicativos' : 'Aplicativo';
    }

    public static function iconFor(array $row): string
    {
        $icon = (string) ($row['icon'] ?? '');
        if ($icon !== '' && array_key_exists($icon, self::ICONS)) {
            return 'ti ' . $icon;
        }
        return 'ti ' . (self::CATEGORY_ICONS[(string) ($row['category'] ?? '')] ?? 'ti-package');
    }

    /** Quantas etapas de perfil usam o aplicativo. */
    public static function usageCount(int $id): int
    {
        return countElementsInTable(ProfileStep::getTable(), ['plugin_ativaworkspace_applications_id' => $id]);
    }

    /**
     * Aplicativos ativos para o select da etapa SOFTWARE.
     *
     * @return array<int, string>
     */
    public static function activeChoices(): array
    {
        global $DB;

        $choices = [];
        foreach ($DB->request([
            'SELECT' => ['id', 'name', 'desired_version'],
            'FROM'   => self::getTable(),
            'WHERE'  => ['is_active' => 1],
            'ORDER'  => ['name ASC'],
        ]) as $row) {
            $choices[(int) $row['id']] = $row['name'] . ($row['desired_version'] !== '' ? ' (' . $row['desired_version'] . ')' : '');
        }
        return $choices;
    }

    public function prepareInputForAdd($input)
    {
        $input = $this->normalize($input, true);
        return $input === false ? false : parent::prepareInputForAdd($input);
    }

    public function prepareInputForUpdate($input)
    {
        $input = $this->normalize($input, false);
        return $input === false ? false : parent::prepareInputForUpdate($input);
    }

    /**
     * @return array<string, mixed>|false
     */
    private function normalize(array $input, bool $isNew): array|false
    {
        $fail = static function (string $message): bool {
            Session::addMessageAfterRedirect($message, false, ERROR);
            return false;
        };

        // Campos do arquivo so entram pelo upload (chave interna _installer).
        $upload = $input['_installer'] ?? null;
        foreach (self::FILE_FIELDS as $field) {
            unset($input[$field]);
        }
        unset($input['_installer']);
        if (is_array($upload)) {
            $input += $upload + [
                'file_uploaded_at' => $_SESSION['glpi_currenttime'] ?? date('Y-m-d H:i:s'),
                'file_users_id'    => (int) Session::getLoginUserID(),
            ];
        }

        if ($isNew || array_key_exists('name', $input)) {
            $name = trim((string) ($input['name'] ?? ''));
            if ($name === '') {
                return $fail('Informe o nome do aplicativo.');
            }
            $input['name'] = mb_substr($name, 0, 255);
        }

        $choices = [
            'category'       => [self::CATEGORIES, 'other', 'Categoria inválida.'],
            'icon'           => [self::ICONS, '', 'Ícone inválido.'],
            'architecture'   => [self::ARCHITECTURES, 'any', 'Arquitetura inválida.'],
            'installer_type' => [InstallerStorage::TYPES, 'OTHER', 'Tipo de instalador inválido.'],
        ];
        foreach ($choices as $field => [$allowed, $default, $message]) {
            if ($isNew || array_key_exists($field, $input)) {
                $value = (string) ($input[$field] ?? $default);
                if (!array_key_exists($value, $allowed)) {
                    return $fail($message);
                }
                $input[$field] = $value;
            }
        }

        // Trocar o tipo sem enviar arquivo novo: o arquivo atual tem que servir.
        if (!$isNew && isset($input['installer_type']) && !is_array($upload)) {
            $currentFile = (string) ($this->fields['file_name'] ?? '');
            $extension = strtolower(pathinfo($currentFile, PATHINFO_EXTENSION));
            if ($currentFile !== '' && !in_array($extension, InstallerStorage::TYPES[$input['installer_type']]['extensions'], true)) {
                return $fail('O instalador atual (' . $currentFile . ') não é do tipo ' . $input['installer_type'] . '. Envie um arquivo novo.');
            }
        }

        if (array_key_exists('desired_version', $input)) {
            $version = trim((string) $input['desired_version']);
            if ($version !== '' && !preg_match('/^[\w.+\-]{1,64}$/u', $version)) {
                return $fail('Versão inválida (use letras, números, ".", "-", "+" ou "_").');
            }
            $input['desired_version'] = $version;
        }

        $ranges = [
            'timeout_minutes' => [1, self::TIMEOUT_MAX_MINUTES, 'O timeout deve ficar entre 1 e ' . self::TIMEOUT_MAX_MINUTES . ' minutos.'],
            'max_attempts'    => [1, self::ATTEMPTS_MAX, 'As tentativas devem ficar entre 1 e ' . self::ATTEMPTS_MAX . '.'],
        ];
        foreach ($ranges as $field => [$min, $max, $message]) {
            if (array_key_exists($field, $input)) {
                $value = (int) $input[$field];
                if ($value < $min || $value > $max) {
                    return $fail($message);
                }
                $input[$field] = $value;
            }
        }

        foreach (['comment', 'notes'] as $field) {
            if (array_key_exists($field, $input)) {
                $input[$field] = trim((string) $input[$field]);
            }
        }
        if (array_key_exists('expected_signer', $input)) {
            $input['expected_signer'] = mb_substr(trim((string) $input['expected_signer']), 0, 255);
        }
        foreach (['is_active', 'requires_reboot'] as $flag) {
            if (array_key_exists($flag, $input)) {
                $input[$flag] = (int) (bool) $input[$flag];
            }
        }
        // Configuracao interna de instalacao: nunca vem do formulario.
        unset($input['install_config'], $input['provider']);

        return $input;
    }

    /** Nao exclui aplicativo que algum perfil ainda usa. */
    public function pre_deleteItem()
    {
        $used = self::usageCount((int) $this->getID());
        if ($used > 0) {
            Session::addMessageAfterRedirect(
                sprintf('"%s" é usado em %d etapa(s) de perfil. Remova das etapas ou desative o aplicativo.', $this->fields['name'], $used),
                false,
                ERROR
            );
            return false;
        }
        return true;
    }

    public function post_addItem()
    {
        Event::log(Event::LEVEL_INFO, 'application', 'Aplicativo criado: ' . $this->fields['name'], ['id' => (int) $this->getID()]);
        if ((string) ($this->fields['file_stored_name'] ?? '') !== '') {
            $this->logUpload(false);
        }
    }

    public function post_updateItem($history = true)
    {
        if (in_array('file_stored_name', $this->updates, true)) {
            // O banco ja aponta para o arquivo novo: agora o antigo pode sair.
            InstallerStorage::delete((string) ($this->oldvalues['file_stored_name'] ?? ''));
            $this->logUpload(((string) ($this->oldvalues['file_stored_name'] ?? '')) !== '');
        }
        $changed = array_values(array_diff($this->updates, self::FILE_FIELDS));
        if ($changed !== []) {
            Event::log(Event::LEVEL_INFO, 'application', 'Aplicativo alterado: ' . $this->fields['name'], [
                'id'     => (int) $this->getID(),
                'fields' => $changed,
            ]);
        }
    }

    public function post_purgeItem()
    {
        InstallerStorage::delete((string) ($this->fields['file_stored_name'] ?? ''));
        Event::log(Event::LEVEL_WARNING, 'application', 'Aplicativo excluído: ' . $this->fields['name'], ['id' => (int) $this->getID()]);
    }

    private function logUpload(bool $replaced): void
    {
        Event::log(Event::LEVEL_INFO, 'application', ($replaced ? 'Instalador substituído: ' : 'Instalador enviado: ') . $this->fields['name'], [
            'id'        => (int) $this->getID(),
            'arquivo'   => (string) $this->fields['file_name'],
            'tamanho'   => (int) $this->fields['file_size'],
            'sha256'    => (string) $this->fields['file_sha256'],
            'assinatura'=> (string) $this->fields['file_signature_status'],
        ]);
    }
}
