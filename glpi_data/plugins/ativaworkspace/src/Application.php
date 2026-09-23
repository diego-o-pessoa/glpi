<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace;

use CommonDBTM;
use PluginAtivaworkspaceProfile;
use Session;

/**
 * Aplicativo do catalogo (glpi_plugin_ativaworkspace_applications).
 * Nesta etapa e so cadastro: nada e instalado.
 */
final class Application extends CommonDBTM
{
    /** Quem vai instalar (execucao a partir da Etapa 2). */
    public const PROVIDERS = [
        'manual'      => 'Manual',
        'winget'      => 'Winget',
        'glpi_deploy' => 'GLPI Inventory Deploy',
        'msi'         => 'Pacote MSI',
    ];

    public static $rightname = PluginAtivaworkspaceProfile::RIGHT_APPLICATIONS;

    public $dohistory = true;

    public static function getTypeName($nb = 0): string
    {
        return $nb > 1 ? 'Aplicativos' : 'Aplicativo';
    }

    public static function providerLabel(string $provider): string
    {
        return self::PROVIDERS[$provider] ?? $provider;
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
        if ($isNew || array_key_exists('name', $input)) {
            $name = trim((string) ($input['name'] ?? ''));
            if ($name === '') {
                Session::addMessageAfterRedirect('Informe o nome do aplicativo.', false, ERROR);
                return false;
            }
            $input['name'] = mb_substr($name, 0, 255);
        }
        if ($isNew || array_key_exists('provider', $input)) {
            $provider = (string) ($input['provider'] ?? 'manual');
            if (!array_key_exists($provider, self::PROVIDERS)) {
                Session::addMessageAfterRedirect('Provider inválido.', false, ERROR);
                return false;
            }
            $input['provider'] = $provider;
        }
        if (array_key_exists('desired_version', $input)) {
            $version = trim((string) $input['desired_version']);
            if ($version !== '' && !preg_match('/^[\w.+\-]{1,64}$/u', $version)) {
                Session::addMessageAfterRedirect('Versão desejada inválida (use letras, números, ".", "-", "+" ou "_").', false, ERROR);
                return false;
            }
            $input['desired_version'] = $version;
        }
        if (array_key_exists('comment', $input)) {
            $input['comment'] = trim((string) $input['comment']);
        }
        if (array_key_exists('is_active', $input)) {
            $input['is_active'] = (int) (bool) $input['is_active'];
        }
        return $input;
    }

    public function post_addItem()
    {
        Event::log(Event::LEVEL_INFO, 'application', 'Aplicativo criado: ' . $this->fields['name'], ['id' => (int) $this->getID()]);
    }

    public function post_updateItem($history = true)
    {
        if ($this->updates !== []) {
            Event::log(Event::LEVEL_INFO, 'application', 'Aplicativo alterado: ' . $this->fields['name'], [
                'id'     => (int) $this->getID(),
                'fields' => array_values($this->updates),
            ]);
        }
    }

    public function post_purgeItem()
    {
        Event::log(Event::LEVEL_WARNING, 'application', 'Aplicativo excluído: ' . $this->fields['name'], ['id' => (int) $this->getID()]);
    }
}
