<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativacentral;

use Html;
use Session;
use Ticket;
use User;

/**
 * Tela "Novo chamado" do Super-Admin no mesmo estilo do Self-Service:
 * departamento, tipo de mensagem, prioridade, assunto, tipo e mensagem
 * (com anexos). O atendente escolhe o requerente e pode se atribuir.
 */
final class TicketForm
{
    public const PRIORITIES = [2 => 'Baixa', 3 => 'Normal', 4 => 'Alta', 5 => 'Urgente'];

    public static function newUrl(): string
    {
        return Shell::url('/front/ticket.new.php');
    }

    /** Categorias "Departamento - Tipo" agrupadas por departamento (igual ao Self-Service). */
    private static function tree(): array
    {
        global $DB;
        $tree = [];
        foreach ($DB->request(['SELECT' => ['id', 'name'], 'FROM' => 'glpi_itilcategories', 'WHERE' => ['is_helpdeskvisible' => 1], 'ORDER' => 'name ASC']) as $row) {
            $name = (string) $row['name'];
            [$dept, $msg] = str_contains($name, ' - ') ? array_map('trim', explode(' - ', $name, 2)) : ['Outros', $name];
            $tree[$dept][] = ['id' => (int) $row['id'], 'name' => $msg];
        }
        ksort($tree);
        return $tree;
    }

    /** @return array<string, mixed> */
    public static function viewData(array $old = [], string $error = ''): array
    {
        global $DB;
        $me = (int) Session::getLoginUserID();
        $tree = self::tree();
        // Requerente: so atendentes "Nome - Ativa Locação" (some "glpi", "tech"...).
        $hidden = [];
        foreach ($DB->request(['SELECT' => ['id', 'realname', 'firstname'], 'FROM' => 'glpi_users']) as $u) {
            if (stripos((string) $u['realname'] . ' ' . (string) $u['firstname'], 'Ativa Loca') === false) {
                $hidden[] = (int) $u['id'];
            }
        }
        $default = (int) ($old['requester'] ?? $me);
        if (in_array($default, $hidden, true)) {
            $default = 0;
        }
        $catId = (int) ($old['itilcategories_id'] ?? 0);
        $dept = '';
        foreach ($tree as $d => $items) {
            foreach ($items as $it) {
                if ($it['id'] === $catId) {
                    $dept = $d;
                }
            }
        }
        return [
            'error'      => $error,
            'action'     => self::newUrl(),
            'back'       => TicketsList::url(),
            'csrf'       => Session::getNewCSRFToken(),
            'tree_json'  => (string) json_encode($tree, JSON_UNESCAPED_UNICODE),
            'departments' => array_keys($tree),
            'dept'       => $dept,
            'category'   => $catId,
            'name'       => (string) ($old['name'] ?? ''),
            'priority'   => (int) ($old['priority'] ?? 3),
            'priorities' => self::PRIORITIES,
            'kind'       => (string) ($old['kind'] ?? 'incident'),
            'kinds'      => array_map(static fn (array $k): string => $k[0], Statuses::KINDS),
            'assign_me'  => $old === [] ? false : !empty($old['assign_me']),
            'requester_html' => User::dropdown([
                'name'    => 'requester',
                'value'   => $default,
                'used'    => $hidden,
                // So quem pode atender chamados.
                'right'   => 'own_ticket',
                'display' => false,
                'width'   => '100%',
            ]),
            'content_html' => Html::textarea([
                'name'              => 'content',
                'value'             => (string) ($old['content'] ?? ''),
                'enable_richtext'   => true,
                'enable_fileupload' => true,
                'enable_images'     => true,
                'rows'              => 10,
                'display'           => false,
            ]),
            'css_url'    => Shell::assetUrl('css/app.css'),
        ];
    }

    /** @throws \InvalidArgumentException */
    public static function create(array $post): int
    {
        $name = trim((string) ($post['name'] ?? ''));
        $content = trim((string) ($post['content'] ?? ''));
        $category = (int) ($post['itilcategories_id'] ?? 0);
        if ($category <= 0) {
            throw new \InvalidArgumentException('Escolha o departamento e o tipo de mensagem.');
        }
        if ($name === '') {
            throw new \InvalidArgumentException('Informe o assunto do chamado.');
        }
        if (trim(strip_tags($content)) === '' && empty($post['_filename'])) {
            throw new \InvalidArgumentException('Descreva o chamado na mensagem.');
        }
        $requester = (int) ($post['requester'] ?? 0);
        if ($requester <= 0) {
            throw new \InvalidArgumentException('Escolha o requerente do chamado.');
        }
        $kind = isset(Statuses::KINDS[(string) ($post['kind'] ?? '')]) ? (string) $post['kind'] : 'incident';
        $input = [
            'name'                => $name,
            'content'             => $content,
            'itilcategories_id'   => $category,
            'priority'            => max(2, min(5, (int) ($post['priority'] ?? 3))),
            'type'                => Statuses::KINDS[$kind][1],
            'entities_id'         => (int) ($_SESSION['glpiactive_entity'] ?? 0),
            'requesttypes_id'     => 1,
            '_users_id_requester' => $requester,
        ];
        if (!empty($post['assign_me'])) {
            $input['_users_id_assign'] = (int) Session::getLoginUserID();
        }
        // Anexos e imagens do editor (enviados pelo GLPI para a pasta temporaria).
        foreach ($post as $key => $value) {
            if (preg_match('/^_(filename|prefix_filename|tag_filename|content|prefix_content|tag_content)$/', (string) $key) === 1) {
                $input[$key] = $value;
            }
        }
        $ticket = new Ticket();
        $id = $ticket->add($input);
        if (!$id) {
            throw new \InvalidArgumentException('Não foi possível abrir o chamado. Confira os campos e tente de novo.');
        }
        Statuses::saveKind((int) $id, $kind);
        return (int) $id;
    }
}
