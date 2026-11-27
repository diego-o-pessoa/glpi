<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativacentral;

use Glpi\RichText\RichText;
use Html;
use ITILFollowup;
use ITILSolution;
use PendingReason_Item;
use Session;
use Ticket;
use Ticket_User;
use TicketValidation;
use User;

/**
 * Tela do chamado para os atendentes: conversa grande (com resposta e nota
 * interna), situacao do chamado (status + motivo de pendencia), atendentes,
 * aprovacao simples e finalizacao. Abas do GLPI que ficaram: itens, custos,
 * projetos, problemas, contratos e estatisticas.
 */
final class TicketView
{
    /** Abas: chave => [titulo, icone, aba do GLPI ou null]. */
    public const TABS = [
        'chat'      => ['Conversa', 'ti-messages', null],
        'items'     => ['Itens', 'ti-package', 'Item_Ticket$1'],
        'costs'     => ['Custos', 'ti-currency-dollar', 'TicketCost$1'],
        'projects'  => ['Projetos', 'ti-briefcase', 'Itil_Project$1'],
        'problems'  => ['Problemas', 'ti-alert-triangle', 'Problem_Ticket$1'],
        'contracts' => ['Contratos', 'ti-file-certificate', 'Ticket_Contract$1'],
        'stats'     => ['Estatísticas', 'ti-chart-bar', 'Ticket$4'],
    ];

    /** Situacoes que o atendente escolhe (Finalizado e pelo botao Finalizar). */
    private const SETTABLE = ['in_progress', 'running', 'waiting_user', 'waiting_approval', 'waiting_third'];

    public static function url(int $id, string $tab = 'chat'): string
    {
        return Shell::url('/front/ticket.view.php?id=' . $id . ($tab !== 'chat' ? '&tab=' . rawurlencode($tab) : ''));
    }

    private static function cleanName(string $name): string
    {
        return trim((string) preg_replace('/\s+-\s+Ativa\s+Loca\S*o\b/iu', '', $name));
    }

    private static function initials(string $name): string
    {
        $parts = preg_split('/\s+/u', trim($name)) ?: ['?'];
        return mb_strtoupper(mb_substr($parts[0], 0, 1) . (count($parts) > 1 ? mb_substr((string) end($parts), 0, 1) : ''));
    }

    /** Usuarios que podem atender: "Nome - Ativa Locacao" com direito de receber chamado. */
    public static function technicians(): array
    {
        global $DB;
        $out = [];
        $req = [
            'SELECT'          => ['u.id', 'u.realname', 'u.firstname', 'u.name'],
            'DISTINCT'        => true,
            'FROM'            => 'glpi_users AS u',
            'INNER JOIN'      => [
                'glpi_profiles_users AS pu' => ['ON' => ['pu' => 'users_id', 'u' => 'id']],
                'glpi_profilerights AS pr'  => ['ON' => ['pr' => 'profiles_id', 'pu' => 'profiles_id']],
            ],
            'WHERE'           => [
                'u.is_active'  => 1,
                'u.is_deleted' => 0,
                'pr.name'      => 'ticket',
                new \Glpi\DBAL\QueryExpression('(' . $DB->quoteName('pr.rights') . ' & ' . Ticket::OWN . ') > 0'),
            ],
            'ORDER'           => ['u.realname', 'u.firstname'],
        ];
        foreach ($DB->request($req) as $u) {
            $full = trim((string) $u['firstname'] . ' ' . (string) $u['realname']);
            if (stripos((string) $u['realname'] . ' ' . (string) $u['firstname'], 'Ativa Loca') === false) {
                continue;
            }
            $out[(int) $u['id']] = self::cleanName((string) getUserName((int) $u['id'])) ?: $full;
        }
        asort($out);
        return $out;
    }

    /** @return array<string, mixed> */
    public static function viewData(Ticket $ticket, string $tab): array
    {
        global $DB, $CFG_GLPI;
        $id = (int) $ticket->getID();
        $f = $ticket->fields;
        $me = (int) Session::getLoginUserID();
        $canUpdate = $ticket->canUpdateItem();

        // Atores
        $actors = [1 => [], 2 => [], 3 => []];
        foreach ($DB->request(['FROM' => 'glpi_tickets_users', 'WHERE' => ['tickets_id' => $id]]) as $a) {
            $actors[(int) $a['type']][] = ['link_id' => (int) $a['id'], 'id' => (int) $a['users_id'], 'name' => self::cleanName((string) getUserName((int) $a['users_id']))];
        }
        $groupsAssigned = [];
        foreach ($DB->request(['SELECT' => ['g.name'], 'FROM' => 'glpi_groups_tickets AS gt', 'INNER JOIN' => ['glpi_groups AS g' => ['ON' => ['gt' => 'groups_id', 'g' => 'id']]], 'WHERE' => ['gt.tickets_id' => $id, 'gt.type' => 2]]) as $g) {
            $groupsAssigned[] = (string) $g['name'];
        }
        $requesterIds = array_column($actors[1], 'id');

        // Situacao
        $reasonRow = $DB->request(['SELECT' => ['r.name'], 'FROM' => 'glpi_pendingreasons_items AS pi', 'INNER JOIN' => ['glpi_pendingreasons AS r' => ['ON' => ['pi' => 'pendingreasons_id', 'r' => 'id']]], 'WHERE' => ['pi.itemtype' => 'Ticket', 'pi.items_id' => $id]])->current();
        $situation = Statuses::situation((int) $f['status'], (string) ($reasonRow['name'] ?? ''));
        $situations = [];
        foreach (self::SETTABLE as $key) {
            $situations[] = ['key' => $key, 'label' => Statuses::SITUATIONS[$key][0], 'tone' => Statuses::SITUATIONS[$key][1]];
        }

        // Requerente (primeiro)
        $requester = null;
        if ($requesterIds !== []) {
            $u = new User();
            if ($u->getFromDB($requesterIds[0])) {
                $name = self::cleanName((string) getUserName($requesterIds[0]));
                $dept = $DB->request(['SELECT' => ['g.name'], 'FROM' => 'glpi_groups_users AS gu', 'INNER JOIN' => ['glpi_groups AS g' => ['ON' => ['gu' => 'groups_id', 'g' => 'id']]], 'WHERE' => ['gu.users_id' => $requesterIds[0]], 'LIMIT' => 1])->current();
                $requester = [
                    'name'     => $name,
                    'initials' => self::initials($name),
                    'email'    => \UserEmail::getDefaultForUser($requesterIds[0]),
                    'phone'    => (string) ($u->fields['phone'] ?: $u->fields['mobile']),
                    'dept'     => (string) ($dept['name'] ?? ''),
                    'location' => $u->fields['locations_id'] ? \Dropdown::getDropdownName('glpi_locations', (int) $u->fields['locations_id']) : '',
                ];
            }
        }

        $kindRow = $DB->tableExists('glpi_plugin_ativacentral_ticketkinds')
            ? $DB->request(['SELECT' => 'kind', 'FROM' => 'glpi_plugin_ativacentral_ticketkinds', 'WHERE' => ['tickets_id' => $id]])->current()
            : null;
        $kind = (string) ($kindRow['kind'] ?? ((int) $f['type'] === 1 ? 'incident' : 'service'));

        $tabs = [];
        foreach (self::TABS as $key => [$label, $icon]) {
            $tabs[] = ['key' => $key, 'label' => $label, 'icon' => $icon, 'url' => self::url($id, $key), 'active' => $key === $tab];
        }

        $techs = self::technicians();
        $assignedIds = array_column($actors[2], 'id');
        $validations = self::validations($id);

        $data = [
            'id'            => $id,
            'title'         => (string) $f['name'],
            'tab'           => $tab,
            'tabs'          => $tabs,
            'back'          => TicketsList::url(),
            'classic_url'   => Shell::glpiUrl('/front/ticket.form.php?id=' . $id . '&classic=1'),
            'action'        => self::url($id, $tab),
            'csrf'          => Session::getNewCSRFToken(),
            'css_url'       => Shell::assetUrl('css/app.css'),
            'situation'     => $situation,
            'sit_label'     => Statuses::SITUATIONS[$situation][0],
            'sit_tone'      => Statuses::SITUATIONS[$situation][1],
            'situations'    => $situations,
            'is_open'       => (int) $f['status'] < 5,
            'is_solved'     => (int) $f['status'] === 5,
            'can_update'    => $canUpdate,
            'can_reply'     => (int) $f['status'] < 6 && ITILFollowup::canCreate(),
            'category'      => str_replace(' > ', ' › ', (string) \Dropdown::getDropdownName('glpi_itilcategories', (int) $f['itilcategories_id'])),
            'categories_id' => (int) $f['itilcategories_id'],
            'priority'      => (int) $f['priority'],
            'priorities'    => TicketForm::PRIORITIES,
            'priority_label' => Ticket::getPriorityName((int) $f['priority']),
            'kind'          => $kind,
            'kinds'         => array_map(static fn (array $k): string => $k[0], Statuses::KINDS),
            'date'          => Html::convDateTime((string) $f['date']),
            'requester'     => $requester,
            'requesters'    => $actors[1],
            'assigned'      => $actors[2],
            'observers'     => $actors[3],
            'groups'        => $groupsAssigned,
            'i_am_assigned' => in_array($me, $assignedIds, true),
            'techs'         => array_diff_key($techs, array_flip($assignedIds)),
            'validators'    => $techs + self::managers(),
            'validations'   => $validations,
            'can_validate'  => TicketValidation::canCreate(),
            'category_tree' => TicketForm::tree(),
        ];

        $glpiTab = self::TABS[$tab][2] ?? null;
        if ($glpiTab !== null) {
            $root = rtrim((string) $CFG_GLPI['root_doc'], '/');
            $data['tab_url'] = $root . '/ajax/common.tabs.php?' . http_build_query([
                '_target'   => $root . '/front/ticket.form.php',
                '_itemtype' => 'Ticket',
                '_glpi_tab' => $glpiTab,
                'id'        => $id,
            ]);
            return $data;
        }

        $data['timeline'] = self::timeline($ticket, $requesterIds);
        $data['editor_html'] = Html::textarea([
            'name'              => 'content',
            'value'             => '',
            'enable_richtext'   => true,
            'enable_fileupload' => true,
            'enable_images'     => true,
            'rows'              => 5,
            'display'           => false,
        ]);
        $data['solution_html'] = Html::textarea([
            'name'            => 'solution',
            'value'           => '',
            'enable_richtext' => true,
            'enable_images'   => false,
            'rows'            => 4,
            'display'         => false,
        ]);
        return $data;
    }

    /** Gestores (quem tem perfil de aprovacao) que nao sao atendentes: entram na lista de aprovadores. */
    private static function managers(): array
    {
        global $DB;
        $out = [];
        foreach ($DB->request([
            'SELECT'     => ['u.id'],
            'DISTINCT'   => true,
            'FROM'       => 'glpi_users AS u',
            'INNER JOIN' => [
                'glpi_profiles_users AS pu' => ['ON' => ['pu' => 'users_id', 'u' => 'id']],
                'glpi_profilerights AS pr'  => ['ON' => ['pr' => 'profiles_id', 'pu' => 'profiles_id']],
            ],
            'WHERE'      => [
                'u.is_active'  => 1,
                'u.is_deleted' => 0,
                'pr.name'      => 'ticketvalidation',
                new \Glpi\DBAL\QueryExpression('(' . $DB->quoteName('pr.rights') . ' & ' . (TicketValidation::VALIDATEINCIDENT | TicketValidation::VALIDATEREQUEST) . ') > 0'),
            ],
        ]) as $u) {
            $name = (string) getUserName((int) $u['id']);
            if (stripos($name, 'Ativa Loca') !== false) {
                $out[(int) $u['id']] = self::cleanName($name);
            }
        }
        return $out;
    }

    /** @return list<array<string, mixed>> */
    private static function validations(int $id): array
    {
        global $DB;
        $out = [];
        $labels = [2 => ['Aguardando', 'orange'], 3 => ['Aprovado', 'green'], 4 => ['Recusado', 'red']];
        foreach ($DB->request(['FROM' => 'glpi_ticketvalidations', 'WHERE' => ['tickets_id' => $id], 'ORDER' => 'submission_date ASC']) as $v) {
            $target = (int) ($v['items_id_target'] ?: $v['users_id_validate']);
            $status = (int) $v['status'];
            $out[] = [
                'id'      => (int) $v['id'],
                'who'     => $v['itemtype_target'] === 'Group' ? (string) \Dropdown::getDropdownName('glpi_groups', $target) : self::cleanName((string) getUserName($target)),
                'label'   => $labels[$status][0] ?? 'Aguardando',
                'tone'    => $labels[$status][1] ?? 'orange',
                'date'    => Html::convDateTime((string) $v['submission_date']),
                'comment' => trim(strip_tags((string) $v['comment_validation'])),
            ];
        }
        return $out;
    }

    /** Conversa: descricao, respostas, tarefas, solucoes e aprovacoes em ordem. */
    private static function timeline(Ticket $ticket, array $requesterIds): array
    {
        global $DB;
        $id = (int) $ticket->getID();
        $items = [];
        $add = static function (string $type, int $itemId, string $date, int $userId, string $content, array $extra = []) use (&$items): void {
            $items[] = ['type' => $type, 'item_id' => $itemId, 'date_raw' => $date, 'users_id' => $userId, 'content' => $content] + $extra;
        };

        $opener = (int) ($ticket->fields['users_id_recipient'] ?: ($requesterIds[0] ?? 0));
        $add('Ticket', $id, (string) $ticket->fields['date'], $opener, (string) $ticket->fields['content'], ['title' => (string) $ticket->fields['name']]);
        foreach ($DB->request(['FROM' => 'glpi_itilfollowups', 'WHERE' => ['itemtype' => 'Ticket', 'items_id' => $id]]) as $r) {
            $add('ITILFollowup', (int) $r['id'], (string) $r['date'], (int) $r['users_id'], (string) $r['content'], ['private' => (bool) $r['is_private']]);
        }
        foreach ($DB->request(['FROM' => 'glpi_tickettasks', 'WHERE' => ['tickets_id' => $id]]) as $r) {
            $add('TicketTask', (int) $r['id'], (string) $r['date'], (int) $r['users_id'], (string) $r['content'], ['private' => (bool) $r['is_private']]);
        }
        foreach ($DB->request(['FROM' => 'glpi_itilsolutions', 'WHERE' => ['itemtype' => 'Ticket', 'items_id' => $id]]) as $r) {
            $add('ITILSolution', (int) $r['id'], (string) $r['date_creation'], (int) $r['users_id'], (string) $r['content'], ['sol_status' => (int) $r['status']]);
        }
        foreach ($DB->request(['FROM' => 'glpi_ticketvalidations', 'WHERE' => ['tickets_id' => $id]]) as $r) {
            $target = (int) ($r['items_id_target'] ?: $r['users_id_validate']);
            $who = $r['itemtype_target'] === 'Group' ? (string) \Dropdown::getDropdownName('glpi_groups', $target) : self::cleanName((string) getUserName($target));
            $add('ValidationAsk', (int) $r['id'], (string) $r['submission_date'], (int) $r['users_id'], (string) $r['comment_submission'], ['who' => $who]);
            if ((int) $r['status'] !== 2 && $r['validation_date']) {
                $add('ValidationAnswer', (int) $r['id'], (string) $r['validation_date'], $target, (string) $r['comment_validation'], ['approved' => (int) $r['status'] === 3]);
            }
        }
        usort($items, static fn (array $a, array $b): int => [strtotime($a['date_raw']), $a['type'] === 'Ticket' ? 0 : 1] <=> [strtotime($b['date_raw']), $b['type'] === 'Ticket' ? 0 : 1]);

        // Anexos de cada item
        $docs = [];
        foreach ($DB->request([
            'SELECT'     => ['di.itemtype', 'di.items_id', 'd.id', 'd.filename', 'd.mime'],
            'FROM'       => 'glpi_documents_items AS di',
            'INNER JOIN' => ['glpi_documents AS d' => ['ON' => ['di' => 'documents_id', 'd' => 'id']]],
            'WHERE'      => ['OR' => [
                ['di.itemtype' => 'Ticket', 'di.items_id' => $id],
                ['di.itemtype' => 'ITILFollowup', 'di.items_id' => array_merge([0], array_column(array_filter($items, static fn ($i) => $i['type'] === 'ITILFollowup'), 'item_id'))],
                ['di.itemtype' => 'TicketTask', 'di.items_id' => array_merge([0], array_column(array_filter($items, static fn ($i) => $i['type'] === 'TicketTask'), 'item_id'))],
                ['di.itemtype' => 'ITILSolution', 'di.items_id' => array_merge([0], array_column(array_filter($items, static fn ($i) => $i['type'] === 'ITILSolution'), 'item_id'))],
            ]],
        ]) as $d) {
            $docs[$d['itemtype'] . '#' . $d['items_id']][] = [
                'name' => (string) $d['filename'],
                'url'  => Shell::glpiUrl('/front/document.send.php?docid=' . (int) $d['id'] . '&itemtype=Ticket&items_id=' . $id),
                'image' => str_starts_with((string) $d['mime'], 'image/'),
            ];
        }

        $names = [];
        $out = [];
        foreach ($items as $it) {
            $uid = (int) $it['users_id'];
            $names[$uid] ??= $uid > 0 ? self::cleanName((string) getUserName($uid)) : 'Sistema';
            $isRequester = in_array($uid, $requesterIds, true);
            $content = trim((string) $it['content']);
            // Textos antigos gravados com o HTML escapado (&lt;p&gt;).
            if (str_contains($content, '&lt;') && !str_contains($content, '<')) {
                $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
            $out[] = $it + [
                'author'    => $names[$uid],
                'initials'  => self::initials($names[$uid]),
                'side'      => $isRequester ? 'user' : 'staff',
                'html'      => $content !== '' ? RichText::getEnhancedHtml($content) : '',
                'date'      => Html::convDateTime($it['date_raw']),
                'docs'      => $it['type'] === 'Ticket' ? ($docs['Ticket#' . $id] ?? []) : ($docs[$it['type'] . '#' . $it['item_id']] ?? []),
            ];
        }
        return $out;
    }

    /**
     * Acoes da tela (POST).
     *
     * @throws \InvalidArgumentException
     */
    public static function handle(Ticket $ticket, array $post): string
    {
        $id = (int) $ticket->getID();
        $action = (string) ($post['ticket_action'] ?? '');
        $me = (int) Session::getLoginUserID();

        switch ($action) {
            case 'reply':
                $content = trim((string) ($post['content'] ?? ''));
                if (trim(strip_tags($content)) === '' && empty($post['_filename'])) {
                    throw new \InvalidArgumentException('Escreva a mensagem antes de enviar.');
                }
                $input = ['itemtype' => 'Ticket', 'items_id' => $id, 'content' => $content, 'is_private' => !empty($post['is_private']) ? 1 : 0];
                foreach ($post as $key => $value) {
                    if (preg_match('/^_(filename|prefix_filename|tag_filename|content|prefix_content|tag_content)$/', (string) $key) === 1) {
                        $input[$key] = $value;
                    }
                }
                $fup = new ITILFollowup();
                if (!$fup->can(-1, CREATE, $input) || !$fup->add($input)) {
                    throw new \InvalidArgumentException('Não foi possível enviar a resposta.');
                }
                // Responder ao usuario: quem respondeu passa a ser atendente (se ninguem era).
                self::assignIfNobody($ticket, $me);
                return !empty($post['is_private']) ? 'Nota interna adicionada.' : 'Resposta enviada ao usuário.';

            case 'situation':
                self::requireUpdate($ticket);
                return self::setSituation($ticket, (string) ($post['situation'] ?? ''));

            case 'assign_me':
            case 'assign':
                self::requireUpdate($ticket);
                $uid = $action === 'assign_me' ? $me : (int) ($post['users_id'] ?? 0);
                if ($uid <= 0) {
                    throw new \InvalidArgumentException('Escolha o atendente.');
                }
                (new Ticket_User())->add(['tickets_id' => $id, 'users_id' => $uid, 'type' => 2]);
                return $action === 'assign_me' ? 'Chamado atribuído a você.' : 'Atendente adicionado.';

            case 'unassign':
                self::requireUpdate($ticket);
                $link = new Ticket_User();
                if ($link->getFromDB((int) ($post['link_id'] ?? 0)) && (int) $link->fields['tickets_id'] === $id) {
                    $link->delete(['id' => $link->getID()]);
                }
                return 'Atendente removido.';

            case 'details':
                self::requireUpdate($ticket);
                $input = ['id' => $id];
                $prio = (int) ($post['priority'] ?? 0);
                if ($prio >= 2 && $prio <= 5) {
                    $input['priority'] = $prio;
                }
                if (isset($post['itilcategories_id'])) {
                    $input['itilcategories_id'] = max(0, (int) $post['itilcategories_id']);
                }
                $kind = (string) ($post['kind'] ?? '');
                if (isset(Statuses::KINDS[$kind])) {
                    $input['type'] = Statuses::KINDS[$kind][1];
                    Statuses::saveKind($id, $kind);
                }
                $ticket->update($input);
                return 'Dados do chamado salvos.';

            case 'approval':
                $uid = (int) ($post['validator'] ?? 0);
                if ($uid <= 0) {
                    throw new \InvalidArgumentException('Escolha quem vai aprovar.');
                }
                $val = new TicketValidation();
                $input = ['tickets_id' => $id, 'itemtype_target' => 'User', 'items_id_target' => $uid, 'comment_submission' => trim((string) ($post['comment'] ?? ''))];
                if (!$val->can(-1, CREATE, $input) || !$val->add($input)) {
                    throw new \InvalidArgumentException('Não foi possível pedir a aprovação.');
                }
                // Enquanto espera, o chamado fica "Aguardando aprovacao".
                $ticket->getFromDB($id);
                self::setSituation($ticket, 'waiting_approval');
                return 'Aprovação pedida para ' . self::cleanName((string) getUserName($uid)) . '.';

            case 'solve':
                $content = trim((string) ($post['solution'] ?? ''));
                if (trim(strip_tags($content)) === '') {
                    throw new \InvalidArgumentException('Descreva a solução para finalizar o chamado.');
                }
                $sol = new ITILSolution();
                $input = ['itemtype' => 'Ticket', 'items_id' => $id, 'content' => $content];
                if (!$sol->can(-1, CREATE, $input) || !$sol->add($input)) {
                    throw new \InvalidArgumentException('Não foi possível finalizar o chamado.');
                }
                self::assignIfNobody($ticket, $me);
                return 'Chamado finalizado.';

            case 'reopen':
                self::requireUpdate($ticket);
                $ticket->update(['id' => $id, 'status' => Ticket::ASSIGNED]);
                return 'Chamado reaberto.';
        }
        throw new \InvalidArgumentException('Ação inválida.');
    }

    private static function requireUpdate(Ticket $ticket): void
    {
        if (!$ticket->canUpdateItem()) {
            throw new \InvalidArgumentException('Você não tem permissão para alterar este chamado.');
        }
    }

    private static function assignIfNobody(Ticket $ticket, int $me): void
    {
        global $DB;
        $count = countElementsInTable('glpi_tickets_users', ['tickets_id' => $ticket->getID(), 'type' => 2])
            + countElementsInTable('glpi_groups_tickets', ['tickets_id' => $ticket->getID(), 'type' => 2]);
        if ($count === 0 && $ticket->canUpdateItem()) {
            (new Ticket_User())->add(['tickets_id' => $ticket->getID(), 'users_id' => $me, 'type' => 2]);
        }
    }

    /** Troca a situacao: status do GLPI + motivo de pendencia. */
    private static function setSituation(Ticket $ticket, string $situation): string
    {
        global $DB;
        if (!in_array($situation, self::SETTABLE, true)) {
            throw new \InvalidArgumentException('Situação inválida.');
        }
        $id = (int) $ticket->getID();
        $reasonNames = ['waiting_user' => 'Aguardando usuário', 'waiting_approval' => 'Aguardando aprovação', 'waiting_third' => 'Aguardando terceiro'];
        if (isset($reasonNames[$situation])) {
            $reason = $DB->request(['SELECT' => 'id', 'FROM' => 'glpi_pendingreasons', 'WHERE' => ['name' => $reasonNames[$situation]]])->current();
            $ticket->update(['id' => $id, 'status' => Ticket::WAITING]);
            if ($reason) {
                PendingReason_Item::createForItem($ticket, ['pendingreasons_id' => (int) $reason['id'], 'followup_frequency' => 0, 'followups_before_resolution' => 0]);
            }
        } else {
            PendingReason_Item::deleteForItem($ticket);
            $ticket->update(['id' => $id, 'status' => $situation === 'running' ? Ticket::PLANNED : Ticket::ASSIGNED]);
        }
        return 'Situação: ' . Statuses::SITUATIONS[$situation][0] . '.';
    }
}
