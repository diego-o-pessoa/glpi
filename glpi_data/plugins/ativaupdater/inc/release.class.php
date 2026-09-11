<?php

class PluginAtivaupdaterRelease extends CommonDBTM
{
    public static $rightname = 'plugin_ativaupdater_release';

    public static function getTypeName($nb = 0): string
    {
        return _n('Release', 'Releases', $nb, 'ativaupdater');
    }

    public function getActiveRelease(): ?array
    {
        global $DB;
        $iterator = $DB->request([
            'FROM'  => $this->getTable(),
            'WHERE' => ['active' => 1],
            'LIMIT' => 1
        ]);

        if (count($iterator) > 0) {
            return $iterator->current();
        }

        return null;
    }

    public function setActive(int $id): bool
    {
        global $DB;

        // Deactivate all
        $DB->update(
            $this->getTable(),
            ['active' => 0],
            [true] // WHERE 1
        );

        // Activate the selected one
        return $DB->update(
            $this->getTable(),
            ['active' => 1],
            ['id' => $id]
        );
    }
    
    public function canCreateItem()
    {
        return Session::haveRight(PluginAtivaupdaterProfile::RIGHT_MANAGE, UPDATE);
    }

    public function canUpdateItem()
    {
        return Session::haveRight(PluginAtivaupdaterProfile::RIGHT_MANAGE, UPDATE);
    }

    public function canDeleteItem()
    {
        return Session::haveRight(PluginAtivaupdaterProfile::RIGHT_MANAGE, UPDATE);
    }
}
