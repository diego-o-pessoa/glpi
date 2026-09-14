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
        $target = $DB->request([
            'FROM'  => $this->getTable(),
            'WHERE' => ['id' => $id],
            'LIMIT' => 1,
        ]);
        if (count($target) !== 1) {
            return false;
        }

        $DB->beginTransaction();
        try {
            $DB->update($this->getTable(), ['active' => 0], ['active' => 1]);
            $result = $DB->update($this->getTable(), ['active' => 1], ['id' => $id]);
            if (!$result) {
                throw new RuntimeException('Falha ao ativar a release.');
            }
            $DB->commit();
            return true;
        } catch (Throwable) {
            $DB->rollBack();
            return false;
        }
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
