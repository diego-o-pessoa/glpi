<?php

declare(strict_types=1);

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Ativaremote\ClientRepository;
use GlpiPlugin\Ativaremote\ProtectionPolicy;
use GlpiPlugin\Ativaremote\Settings;
use GlpiPlugin\Ativaremote\TiPasswordGate;

include '../../../inc/includes.php';

// Only GLPI administrators: these settings protect the machines of the board and of T.I.
Session::checkRight('config', UPDATE);

global $CFG_GLPI, $DB;

$base = $CFG_GLPI['root_doc'] . '/plugins/ativaremote';
$repo = new ClientRepository();

$settings = ['hostname' => 'configurações do Ativa Remote'];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $user = getUserName((int) Session::getLoginUserID());
    $action = (string) ($_POST['action'] ?? '');

    // The page itself is behind the T.I. password, so nobody changes the protection
    // (or the password) with only the GLPI rights.
    if ($action === 'unlock') {
        try {
            TiPasswordGate::check((string) ($_POST['ti_password'] ?? ''), $settings, 'abrir as configurações');
            TiPasswordGate::unlockConfig();
            TiPasswordGate::log('Configuracoes abertas', $settings);
        } catch (RuntimeException $e) {
            Session::addMessageAfterRedirect($e->getMessage(), false, ERROR);
        }
        Html::redirect($base . '/front/config.php');
    }
    if ($action === 'lock') {
        TiPasswordGate::lockConfig();
        Html::redirect($base . '/front/config.php');
    }
    if (!TiPasswordGate::isConfigUnlocked()) {
        Session::addMessageAfterRedirect('Informe a senha do T.I. para alterar as configurações.', false, ERROR);
        Html::redirect($base . '/front/config.php');
    }

    switch ($action) {
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

        case 'save_manual':
            $ids = is_array($_POST['manual'] ?? null) ? $_POST['manual'] : [];
            $repo->setManualProtection($ids);
            Toolbox::logInFile('ativaremote', sprintf(
                "Protecao manual alterada por %s: %d computador(es)\n",
                $user,
                count(array_filter(array_map('intval', $ids)))
            ));
            Session::addMessageAfterRedirect('Computadores protegidos manualmente atualizados.', true, INFO);
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

if (!TiPasswordGate::isConfigUnlocked()) {
    Html::header('Ativa Remote', '', 'admin', 'pluginativaremotemenu', 'config');
    TemplateRenderer::getInstance()->display('@ativaremote/config-unlock.html.twig', [
        'default_password' => Settings::isDefaultTiPassword(),
        'minutes'          => (int) (TiPasswordGate::CONFIG_UNLOCK_SECONDS / 60),
        'urls'             => ['config' => $base . '/front/config.php', 'dashboard' => $base . '/front/dashboard.php'],
    ]);
    Html::footer();
    return;
}

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

// Why each computer is (or is not) protected.
$clients = $repo->all();
$reasons = ProtectionPolicy::evaluate($clients);
$computerIds = array_values(array_unique(array_filter(array_map('intval', array_column($clients, 'computers_id')))));
$inventory = [];
if ($computerIds !== []) {
    foreach ($DB->request([
        'SELECT'    => ['glpi_computers.id', 'glpi_computers.name', 'glpi_computers.users_id'],
        'FROM'      => 'glpi_computers',
        'WHERE'     => ['glpi_computers.id' => $computerIds],
    ]) as $row) {
        $inventory[(int) $row['id']] = [
            'name'        => $row['name'],
            'user'        => $row['users_id'] ? getUserName((int) $row['users_id']) : '',
            'groups'      => [],
            'user_groups' => [],
            'users_id'    => (int) $row['users_id'],
        ];
    }
    foreach ($DB->request([
        'SELECT'     => ['glpi_groups_items.items_id', 'glpi_groups.completename'],
        'FROM'       => 'glpi_groups_items',
        'INNER JOIN' => ['glpi_groups' => ['ON' => ['glpi_groups' => 'id', 'glpi_groups_items' => 'groups_id']]],
        'WHERE'      => ['glpi_groups_items.itemtype' => Computer::class, 'glpi_groups_items.items_id' => $computerIds],
    ]) as $row) {
        if (isset($inventory[(int) $row['items_id']])) {
            $inventory[(int) $row['items_id']]['groups'][] = $row['completename'];
        }
    }
    $userIds = array_values(array_unique(array_filter(array_column($inventory, 'users_id'))));
    if ($userIds !== []) {
        $groupsByUser = [];
        foreach ($DB->request([
            'SELECT'     => ['glpi_groups_users.users_id', 'glpi_groups.completename'],
            'FROM'       => 'glpi_groups_users',
            'INNER JOIN' => ['glpi_groups' => ['ON' => ['glpi_groups' => 'id', 'glpi_groups_users' => 'groups_id']]],
            'WHERE'      => ['glpi_groups_users.users_id' => $userIds],
        ]) as $row) {
            $groupsByUser[(int) $row['users_id']][] = $row['completename'];
        }
        foreach ($inventory as &$computer) {
            $computer['user_groups'] = $groupsByUser[$computer['users_id']] ?? [];
        }
        unset($computer);
    }
}

$computers = [];
foreach ($clients as $client) {
    $computers[] = [
        'id'        => (int) $client['id'],
        'hostname'  => $client['hostname'],
        'computer'  => $inventory[(int) $client['computers_id']] ?? null,
        'computers_id' => (int) $client['computers_id'],
        'manual'    => !empty($client['protected_manual']),
        'reason'    => $reasons[(int) $client['id']] ?? null,
    ];
}

Html::header('Ativa Remote', '', 'admin', 'pluginativaremotemenu', 'config');
TemplateRenderer::getInstance()->display('@ativaremote/config.html.twig', [
    'groups'              => $groups,
    'computers'           => $computers,
    'default_password'    => Settings::isDefaultTiPassword(),
    'min_password_length' => Settings::MIN_PASSWORD_LENGTH,
    'unlock_minutes'      => (int) (TiPasswordGate::CONFIG_UNLOCK_SECONDS / 60),
    'urls'                => [
        'config'    => $base . '/front/config.php',
        'dashboard' => $base . '/front/dashboard.php',
        'computer'  => $CFG_GLPI['root_doc'] . '/front/computer.form.php',
    ],
]);
Html::footer();
