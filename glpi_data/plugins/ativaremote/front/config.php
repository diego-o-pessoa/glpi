<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativaremote\Settings;

include '../../../inc/includes.php';

// Only GLPI administrators: these settings protect the machines of the board and of T.I.
Session::checkRight('config', UPDATE);

global $CFG_GLPI, $DB;

$base = $CFG_GLPI['root_doc'] . '/plugins/ativaremote';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $user = getUserName((int) Session::getLoginUserID());
    switch ((string) ($_POST['action'] ?? '')) {
        case 'save_groups':
            $groups = is_array($_POST['groups'] ?? null) ? $_POST['groups'] : [];
            Settings::setProtectedGroupIds($groups);
            Toolbox::logInFile('ativaremote', sprintf(
                "Grupos protegidos alterados por %s: %s\n",
                $user,
                implode(', ', Settings::protectedGroupIds()) ?: 'nenhum'
            ));
            Session::addMessageAfterRedirect('Grupos protegidos atualizados.', true, INFO);
            break;

        case 'change_ti_password':
            $password = (string) ($_POST['new_password'] ?? '');
            $confirmation = (string) ($_POST['confirm_password'] ?? '');
            if (mb_strlen($password) < Settings::MIN_PASSWORD_LENGTH) {
                Session::addMessageAfterRedirect(
                    sprintf('A senha do T.I. precisa ter pelo menos %d caracteres.', Settings::MIN_PASSWORD_LENGTH),
                    false,
                    ERROR
                );
            } elseif (!hash_equals($password, $confirmation)) {
                Session::addMessageAfterRedirect('A confirmação não confere com a nova senha.', false, ERROR);
            } else {
                Settings::setTiPassword($password);
                Toolbox::logInFile('ativaremote', sprintf("Senha do T.I. alterada por %s\n", $user));
                Session::addMessageAfterRedirect('Senha do T.I. alterada.', true, INFO);
            }
            break;

        default:
            Session::addMessageAfterRedirect('Ação inválida.', false, ERROR);
    }
    Html::redirect($base . '/front/config.php');
}

Settings::ensureDefaults();
$selected = Settings::protectedGroupIds();
$groups = [];
foreach ($DB->request([
    'SELECT' => ['id', 'completename'],
    'FROM'   => 'glpi_groups',
    'ORDER'  => ['completename ASC'],
]) as $group) {
    $groups[] = [
        'id'       => (int) $group['id'],
        'name'     => $group['completename'],
        'selected' => in_array((int) $group['id'], $selected, true),
    ];
}

Html::header('Ativa Remote', '', 'admin', 'pluginativaremotemenu', 'config');
TemplateRenderer::getInstance()->display('@ativaremote/config.html.twig', [
    'groups'              => $groups,
    'default_password'    => Settings::isDefaultTiPassword(),
    'min_password_length' => Settings::MIN_PASSWORD_LENGTH,
    'urls'                => [
        'config'    => $base . '/front/config.php',
        'dashboard' => $base . '/front/dashboard.php',
    ],
]);
Html::footer();
