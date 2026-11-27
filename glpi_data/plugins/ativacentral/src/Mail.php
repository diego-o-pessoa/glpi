<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativacentral;

use Config;
use Group_User;
use NotificationTargetProject;
use Project;
use ProjectTeam;
use QueuedNotification;
use Session;
use Toolbox;
use User;
use UserEmail;

/**
 * E-mails da Ativa:
 * - modelos proprios para os avisos nativos de chamado (aberto, resposta do
 *   T.I. e solucionado), instalados uma vez por versao;
 * - aviso "voce foi adicionado ao projeto" (o GLPI nao tem esse evento),
 *   colocado na fila de e-mails do GLPI (enviado pela acao automatica).
 */
final class Mail
{
    private const VERSION_KEY = 'mail_templates_version';
    private const VERSION = '4';

    private static function layout(string $title, string $subtitle, string $content, string $button, string $url, string $footer): string
    {
        return '<div style="background:#eef1f5;padding:24px 12px;font-family:Segoe UI,Arial,sans-serif;color:#1f2937">'
            . '<div style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:10px;overflow:hidden">'
            . '<div style="background:#0b5cad;color:#ffffff;padding:16px 20px"><div style="font-size:18px;font-weight:600">' . $title . '</div>'
            . '<div style="font-size:13px;opacity:.85">' . $subtitle . '</div></div>'
            . '<div style="padding:20px;font-size:15px;line-height:1.5">' . $content
            . '<p style="margin:18px 0 0"><a href="' . $url . '" style="background:#0b5cad;color:#ffffff;text-decoration:none;padding:10px 18px;border-radius:6px;display:inline-block">' . $button . '</a></p></div>'
            . '<div style="padding:12px 20px;font-size:12px;color:#6b7280;border-top:1px solid #e5e7eb">' . $footer . '</div>'
            . '</div></div>';
    }

    private static function box(string $text): string
    {
        return '<div style="background:#f3f4f6;border-left:3px solid #0b5cad;padding:10px 12px;margin:12px 0;border-radius:4px">' . $text . '</div>';
    }

    private static function table(array $rows): string
    {
        $html = '<table style="width:100%;border-collapse:collapse;font-size:14px">';
        foreach ($rows as $label => $value) {
            $html .= '<tr><td style="color:#6b7280;width:120px;padding:3px 0">' . $label . '</td><td style="padding:3px 0">' . $value . '</td></tr>';
        }
        return $html . '</table>';
    }

    /** @return array<string, array{name: string, subject: string, html: string, text: string}> modelos por evento */
    private static function ticketTemplates(): array
    {
        $foot = 'Não responda este e-mail. Equipe de T.I. — Ativa Locação.';
        return [
            'new' => [
                'name'    => 'Ativa - Chamado aberto',
                'subject' => '[Chamado ##ticket.id##] Recebemos sua solicitação: ##ticket.title##',
                'html'    => self::layout(
                    'Recebemos seu chamado',
                    'T.I. · Ativa Locação',
                    '<p>Olá, ##ticket.authors##.</p><p>Seu chamado foi aberto e a equipe de T.I. já foi avisada. Você será informado por e-mail a cada atualização.</p>'
                        . self::table(['Chamado' => '###ticket.id## — ##ticket.title##', 'Categoria' => '##ticket.category##', 'Aberto em' => '##ticket.creationdate##'])
                        . self::box('##ticket.description##'),
                    'Acompanhar chamado',
                    '##ticket.url##',
                    $foot
                ),
                'text'    => "Olá, ##ticket.authors##.\n\nSeu chamado ###ticket.id## (##ticket.title##) foi aberto e a equipe de T.I. já foi avisada.\n\nAcompanhe em: ##ticket.url##",
            ],
            'add_followup' => [
                'name'    => 'Ativa - Resposta do T.I.',
                'subject' => '[Chamado ##ticket.id##] Nova resposta do T.I.',
                'html'    => self::layout(
                    'O T.I. respondeu seu chamado',
                    '###ticket.id## — ##ticket.title##',
                    '<p>Olá, ##ticket.authors##.</p>##FOREACH FIRST 1 followups##<p><b>##followup.author##</b> escreveu em ##followup.date##:</p>'
                        . self::box('##followup.description##') . '##ENDFOREACHfollowups##'
                        . '<p>Se precisar responder ou enviar algo, acesse o chamado.</p>',
                    'Responder no chamado',
                    '##ticket.url##',
                    $foot
                ),
                'text'    => "Olá, ##ticket.authors##.\n\n##FOREACH FIRST 1 followups####followup.author## escreveu em ##followup.date##:\n##followup.description##\n##ENDFOREACHfollowups##\nResponda em: ##ticket.url##",
            ],
            'solved' => [
                'name'    => 'Ativa - Chamado solucionado',
                'subject' => '[Chamado ##ticket.id##] Chamado solucionado',
                'html'    => self::layout(
                    'Seu chamado foi solucionado',
                    '###ticket.id## — ##ticket.title##',
                    '<p>Olá, ##ticket.authors##.</p><p>O T.I. finalizou seu chamado com a solução abaixo:</p>'
                        . self::box('##ticket.solution.description##'),
                    'Ver chamado',
                    '##ticket.url##',
                    'Obrigado! Equipe de T.I. — Ativa Locação.'
                ),
                'text'    => "Olá, ##ticket.authors##.\n\nO T.I. finalizou seu chamado ###ticket.id## (##ticket.title##):\n##ticket.solution.description##\n\n##ticket.url##",
            ],
        ];
    }

    /** Instala/atualiza os modelos dos chamados e liga os avisos nativos a eles (uma vez por versao). */
    public static function ensureTemplates(): void
    {
        global $DB;
        self::ensureOwnEmail();
        $config = Config::getConfigurationValues(Settings::CONTEXT, [self::VERSION_KEY]);
        if (($config[self::VERSION_KEY] ?? '') === self::VERSION) {
            return;
        }
        foreach (self::ticketTemplates() as $event => $tpl) {
            $row = $DB->request(['SELECT' => 'id', 'FROM' => 'glpi_notificationtemplates', 'WHERE' => ['name' => $tpl['name'], 'itemtype' => 'Ticket']])->current();
            if ($row) {
                $tplId = (int) $row['id'];
            } else {
                $DB->insert('glpi_notificationtemplates', ['name' => $tpl['name'], 'itemtype' => 'Ticket', 'date_mod' => $_SESSION['glpi_currenttime'] ?? date('Y-m-d H:i:s'), 'date_creation' => date('Y-m-d H:i:s')]);
                $tplId = (int) $DB->insertId();
            }
            $DB->delete('glpi_notificationtemplatetranslations', ['notificationtemplates_id' => $tplId]);
            $DB->insert('glpi_notificationtemplatetranslations', [
                'notificationtemplates_id' => $tplId,
                'language'                 => '',
                'subject'                  => $tpl['subject'],
                'content_text'             => $tpl['text'],
                'content_html'             => $tpl['html'],
            ]);
            $ids = array_column(iterator_to_array($DB->request(['SELECT' => 'id', 'FROM' => 'glpi_notifications', 'WHERE' => ['itemtype' => 'Ticket', 'event' => $event]])), 'id');
            if ($ids !== []) {
                $DB->update('glpi_notifications_notificationtemplates', ['notificationtemplates_id' => $tplId], ['notifications_id' => $ids, 'mode' => 'mailing']);
                // So quem abriu o chamado (requerente), no e-mail do cadastro dele.
                $DB->delete('glpi_notificationtargets', ['notifications_id' => $ids]);
                foreach ($ids as $nid) {
                    $DB->insert('glpi_notificationtargets', ['notifications_id' => $nid, 'type' => \Notification::USER_TYPE, 'items_id' => \Notification::AUTHOR]);
                }
            }
        }
        // Modelos antigos gravados com o HTML escapado (&lt;div&gt;) chegam
        // com o codigo aparecendo no e-mail: volta para HTML normal.
        foreach ($DB->request(['SELECT' => ['id', 'content_html'], 'FROM' => 'glpi_notificationtemplatetranslations', 'WHERE' => ['content_html' => ['LIKE', '%&lt;%']]]) as $row) {
            $DB->update('glpi_notificationtemplatetranslations', ['content_html' => html_entity_decode((string) $row['content_html'], ENT_QUOTES | ENT_HTML5, 'UTF-8')], ['id' => (int) $row['id']]);
        }
        // Usuarios que entram pela Microsoft tem o e-mail so no login: copia
        // para o cadastro de e-mails (sem isso o GLPI nao tem para onde enviar).
        $DB->doQuery("INSERT INTO glpi_useremails (users_id, is_default, is_dynamic, email)
            SELECT u.id, 1, 0, u.name FROM glpi_users u
            WHERE u.name LIKE '%_@_%._%' AND u.is_deleted = 0
              AND NOT EXISTS (SELECT 1 FROM glpi_useremails e WHERE e.users_id = u.id)");
        Config::setConfigurationValues(Settings::CONTEXT, [self::VERSION_KEY => self::VERSION]);
    }

    /** @var int[] e-mails colocados na fila nesta requisicao */
    private static array $pending = [];

    /**
     * Gancho item_add de QueuedNotification: envia na hora (ao fim da
     * requisicao, depois de responder ao navegador) em vez de esperar a
     * acao automatica. Se falhar, o e-mail continua na fila para a acao.
     */
    public static function onQueued(QueuedNotification $item): void
    {
        if (($item->fields['mode'] ?? '') !== 'mailing') {
            return;
        }
        if (self::$pending === []) {
            register_shutdown_function([self::class, 'flush']);
        }
        self::$pending[] = (int) $item->getID();
    }

    public static function flush(): void
    {
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        $queue = new QueuedNotification();
        foreach (array_unique(self::$pending) as $id) {
            try {
                if ($queue->getFromDB($id) && strtotime((string) $queue->fields['send_time']) <= time()) {
                    $queue->sendById($id);
                }
            } catch (\Throwable $e) {
                Toolbox::logInFile('ativacentral', "Falha ao enviar e-mail $id na hora: " . $e->getMessage() . "\n");
            }
        }
        self::$pending = [];
    }

    /** Usuario logado sem e-mail cadastrado: usa o login quando ele e um e-mail. */
    private static function ensureOwnEmail(): void
    {
        $userId = (int) Session::getLoginUserID();
        $login = (string) ($_SESSION['glpiname'] ?? '');
        if ($userId <= 0 || !empty($_SESSION['ativa_email_ok']) || !filter_var($login, FILTER_VALIDATE_EMAIL)) {
            return;
        }
        $_SESSION['ativa_email_ok'] = 1;
        if (UserEmail::getDefaultForUser($userId) === '') {
            (new UserEmail())->add(['users_id' => $userId, 'is_default' => 1, 'email' => $login]);
        }
    }

    /** Gancho item_add de ProjectTeam: avisa por e-mail quem entrou na equipe. */
    public static function onTeamAdd(ProjectTeam $team): void
    {
        global $CFG_GLPI;
        try {
            if (empty($CFG_GLPI['use_notifications']) || empty($CFG_GLPI['notifications_mailing'])) {
                return;
            }
            $project = new Project();
            if (!$project->getFromDB((int) $team->fields['projects_id'])) {
                return;
            }
            $users = [];
            if ($team->fields['itemtype'] === 'User') {
                $users[] = (int) $team->fields['items_id'];
            } elseif ($team->fields['itemtype'] === 'Group') {
                foreach (Group_User::getGroupUsers((int) $team->fields['items_id']) as $u) {
                    $users[] = (int) $u['id'];
                }
            }
            foreach (array_unique($users) as $userId) {
                if ($userId > 0 && $userId !== Session::getLoginUserID()) {
                    self::queueTeamMail($project, $userId);
                }
            }
        } catch (\Throwable $e) {
            Toolbox::logInFile('ativacentral', 'Falha no e-mail de equipe do projeto: ' . $e->getMessage() . "\n");
        }
    }

    private static function queueTeamMail(Project $project, int $userId): void
    {
        global $CFG_GLPI;
        $email = UserEmail::getDefaultForUser($userId);
        $user = new User();
        if ($email === '' || !$user->getFromDB($userId) || empty($user->fields['is_active'])) {
            return;
        }
        $e = static fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        $name = $user->getFriendlyName();
        $manager = (int) $project->fields['users_id'] > 0 ? getUserName((int) $project->fields['users_id']) : '';
        $date = static fn ($d): string => $d ? \Html::convDate(substr((string) $d, 0, 10)) : '—';
        $url = $CFG_GLPI['url_base'] . '/index.php?redirect=' . rawurlencode('Project_' . $project->getID());
        $intro = $manager !== '' ? '<b>' . $e($manager) . '</b> adicionou você à equipe do projeto.' : 'Você foi adicionado à equipe do projeto.';

        $html = self::layout(
            'Você faz parte de um novo projeto',
            $e($project->fields['name']),
            '<p>Olá, ' . $e($name) . '.</p><p>' . $intro . '</p>'
                . self::table(['Projeto' => $e($project->fields['name']), 'Gestor' => $e($manager ?: '—'), 'Previsão' => $date($project->fields['plan_start_date']) . ' → ' . $date($project->fields['plan_end_date'])]),
            'Abrir projeto',
            $e($url),
            'Equipe de T.I. — Ativa Locação.'
        );
        $text = "Olá, $name.\n\nVocê foi adicionado à equipe do projeto \"{$project->fields['name']}\"" . ($manager !== '' ? " por $manager" : '') . ".\n\nAbrir: $url";

        $target = new NotificationTargetProject((int) $project->fields['entities_id'], 'update', $project);
        $sender = $target->getSender();
        $reply = $target->getReplyTo();
        $queue = new QueuedNotification();
        $queue->add([
            'itemtype'                 => 'Project',
            'items_id'                 => $project->getID(),
            'notificationtemplates_id' => 0,
            'entities_id'              => (int) $project->fields['entities_id'],
            'sender'                   => $sender['email'] ?? '',
            'sendername'               => $sender['name'] ?? '',
            'recipient'                => $email,
            'recipientname'            => $name,
            'replyto'                  => $reply['email'] ?? '',
            'replytoname'              => $reply['name'] ?? '',
            'name'                     => 'Você foi adicionado ao projeto ' . $project->fields['name'],
            'body_html'                => $html,
            'body_text'                => $text,
            'mode'                     => 'mailing',
            'event'                    => 'ativa_team_add',
            'headers'                  => [],
        ]);
    }
}
