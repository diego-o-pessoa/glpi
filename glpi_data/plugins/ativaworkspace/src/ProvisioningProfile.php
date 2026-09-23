<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace;

use CommonDBTM;
use PluginAtivaworkspaceProfile;
use Session;

/**
 * Perfil de provisionamento (glpi_plugin_ativaworkspace_provisioningprofiles).
 * Tem entidade: o GLPI aplica a restricao de entidade no can()/check().
 */
final class ProvisioningProfile extends CommonDBTM
{
    public static $rightname = PluginAtivaworkspaceProfile::RIGHT_PROFILES;

    public $dohistory = true;

    public static function getTypeName($nb = 0): string
    {
        return $nb > 1 ? 'Perfis de provisionamento' : 'Perfil de provisionamento';
    }

    /**
     * Perfis ativos que o usuario pode usar num provisionamento (entidades
     * ativas, incluindo perfis recursivos das entidades pai).
     *
     * @return array<int, string> id => nome
     */
    public static function activeChoices(): array
    {
        global $DB;

        $table   = self::getTable();
        $choices = [];
        foreach ($DB->request([
            'SELECT' => ['id', 'name'],
            'FROM'   => $table,
            'WHERE'  => ['is_active' => 1] + getEntitiesRestrictCriteria($table, '', '', true),
            'ORDER'  => ['name ASC'],
        ]) as $row) {
            $choices[(int) $row['id']] = (string) $row['name'];
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
        if ($isNew || array_key_exists('name', $input)) {
            $name = trim((string) ($input['name'] ?? ''));
            if ($name === '') {
                Session::addMessageAfterRedirect('Informe o nome do perfil.', false, ERROR);
                return false;
            }
            $input['name'] = mb_substr($name, 0, 255);
        }
        if (array_key_exists('comment', $input)) {
            $input['comment'] = trim((string) $input['comment']);
        }
        foreach (['is_active', 'is_recursive'] as $flag) {
            if (array_key_exists($flag, $input)) {
                $input[$flag] = (int) (bool) $input[$flag];
            }
        }
        if (array_key_exists('entities_id', $input)) {
            $input['entities_id'] = (int) $input['entities_id'];
            // O can() da edicao confere a entidade ATUAL; a de destino e aqui.
            if (!Session::haveAccessToEntity($input['entities_id'])) {
                Event::log(Event::LEVEL_SECURITY, 'permission', 'Tentativa de usar entidade sem acesso', [
                    'entities_id' => $input['entities_id'],
                ]);
                Session::addMessageAfterRedirect('Você não tem acesso a essa entidade.', false, ERROR);
                return false;
            }
        }
        return $input;
    }

    public function post_addItem()
    {
        Event::log(Event::LEVEL_INFO, 'profile', 'Perfil criado: ' . $this->fields['name'], ['id' => (int) $this->getID()]);
    }

    public function post_updateItem($history = true)
    {
        if ($this->updates !== []) {
            Event::log(Event::LEVEL_INFO, 'profile', 'Perfil alterado: ' . $this->fields['name'], [
                'id'     => (int) $this->getID(),
                'fields' => array_values($this->updates),
            ]);
        }
    }

    public function post_purgeItem()
    {
        Event::log(Event::LEVEL_WARNING, 'profile', 'Perfil excluído: ' . $this->fields['name'], ['id' => (int) $this->getID()]);
    }

    public function cleanDBonPurge()
    {
        // As etapas pertencem ao perfil; jobs e eventos ficam como historico.
        (new ProfileStep())->deleteByCriteria([
            'plugin_ativaworkspace_provisioningprofiles_id' => (int) $this->getID(),
        ], true);
    }
}
