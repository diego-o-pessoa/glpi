<?php

use GlpiPlugin\Ativaupdater\ReleasePolicy;

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

    /**
     * Publishes a release to every computer. With $allowDowngrade, computers
     * on a newer package are told to roll back to this exact version.
     */
    public function setActive(int $id, bool $allowDowngrade = false, int $userId = 0): bool
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
        if ($allowDowngrade && !ReleasePolicy::canRollbackTo((string) $target->current()['version'])) {
            return false;
        }

        $DB->beginTransaction();
        try {
            $DB->update($this->getTable(), ['active' => 0, 'allow_downgrade' => 0], [
                'OR' => ['active' => 1, 'allow_downgrade' => 1],
            ]);
            $result = $DB->update($this->getTable(), [
                'active'          => 1,
                'allow_downgrade' => $allowDowngrade ? 1 : 0,
                'activated_at'    => date('Y-m-d H:i:s'),
                'activated_by'    => $userId,
            ], ['id' => $id]);
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
    
    public function canCreateItem(): bool
    {
        return Session::haveRight(PluginAtivaupdaterProfile::RIGHT_MANAGE, UPDATE)
            || Session::haveRight('config', UPDATE);
    }

    public function canUpdateItem(): bool
    {
        return Session::haveRight(PluginAtivaupdaterProfile::RIGHT_MANAGE, UPDATE)
            || Session::haveRight('config', UPDATE);
    }

    public function canDeleteItem(): bool
    {
        return Session::haveRight(PluginAtivaupdaterProfile::RIGHT_MANAGE, UPDATE)
            || Session::haveRight('config', UPDATE);
    }
}
