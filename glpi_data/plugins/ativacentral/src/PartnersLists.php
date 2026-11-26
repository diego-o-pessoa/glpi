<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativacentral;

use Contact;
use Contract;
use Session;

/**
 * Telas "Contatos" e "Contratos" (todos, nao so de um fornecedor) no
 * estilo do Ativa Central. Mesmas gavetas da aba do fornecedor, com o
 * campo "Fornecedor" para ja vincular ao criar.
 */
final class PartnersLists
{
    public static function contactsUrl(): string
    {
        return Shell::url('/front/contacts.php');
    }

    public static function contractsUrl(): string
    {
        return Shell::url('/front/contracts.php');
    }

    /** @return array<int, array<string, string>> fornecedores de cada item (contato ou contrato) */
    private static function suppliersOf(string $table, string $field, array $ids): array
    {
        global $DB;
        if ($ids === []) {
            return [];
        }
        $out = [];
        foreach ($DB->request([
            'SELECT'     => ["$table.$field", 'glpi_suppliers.id', 'glpi_suppliers.name'],
            'FROM'       => $table,
            'INNER JOIN' => ['glpi_suppliers' => ['ON' => [$table => 'suppliers_id', 'glpi_suppliers' => 'id']]],
            'WHERE'      => ["$table.$field" => $ids, 'glpi_suppliers.is_deleted' => 0],
        ]) as $row) {
            $out[(int) $row[$field]][] = ['id' => (int) $row['id'], 'name' => (string) $row['name'], 'url' => SupplierPage::url((int) $row['id'])];
        }
        return $out;
    }

    /** @return array<int, string> */
    private static function suppliers(): array
    {
        global $DB;
        $out = [];
        $where = ['is_deleted' => 0];
        $entities = getEntitiesRestrictCriteria('glpi_suppliers', '', '', true);
        foreach ($DB->request(['SELECT' => ['id', 'name'], 'FROM' => 'glpi_suppliers', 'WHERE' => $entities === [] ? $where : [$where, $entities], 'ORDER' => 'name ASC']) as $row) {
            $out[(int) $row['id']] = (string) $row['name'];
        }
        return $out;
    }

    // ------------------------------------------------------------------
    // Contatos
    // ------------------------------------------------------------------

    /** @return array<string, mixed> */
    public static function contactsData(int $editId = 0, bool $openNew = false): array
    {
        global $DB;
        $types = SupplierPage::options('glpi_contacttypes');
        $titles = SupplierPage::options('glpi_usertitles');
        $where = ['is_deleted' => 0];
        $entities = getEntitiesRestrictCriteria('glpi_contacts', '', '', true);
        $rows = [];
        foreach ($DB->request(['FROM' => 'glpi_contacts', 'WHERE' => $entities === [] ? $where : [$where, $entities], 'ORDER' => ['name ASC', 'firstname ASC']]) as $row) {
            $rows[(int) $row['id']] = $row;
        }
        $suppliers = self::suppliersOf('glpi_contacts_suppliers', 'contacts_id', array_keys($rows));

        $contacts = [];
        foreach ($rows as $id => $row) {
            $name = trim((string) $row['firstname'] . ' ' . (string) $row['name']) ?: 'Contato #' . $id;
            $contacts[] = [
                'id'              => $id,
                'full_name'       => $name,
                'name'            => $name,
                'initials'        => self::initials($name),
                'type'            => $types[(int) $row['contacttypes_id']] ?? '',
                'title'           => $titles[(int) $row['usertitles_id']] ?? '',
                'contacttypes_id' => (int) $row['contacttypes_id'],
                'usertitles_id'   => (int) $row['usertitles_id'],
                'phone'           => (string) $row['phone'],
                'mobile'          => (string) $row['mobile'],
                'email'           => (string) $row['email'],
                'address'         => (string) $row['address'],
                'town'            => (string) $row['town'],
                'postcode'        => (string) $row['postcode'],
                'state'           => (string) $row['state'],
                'country'         => (string) $row['country'],
                'suppliers'       => $suppliers[$id] ?? [],
            ];
        }

        return [
            'contacts'       => $contacts,
            'contact_types'  => $types,
            'contact_titles' => $titles,
            'suppliers'      => self::suppliers(),
            'filters'        => array_values(array_unique(array_filter(array_column($contacts, 'type')))),
            'kpis'           => [
                'total'      => count($contacts),
                'with_email' => count(array_filter($contacts, static fn (array $c): bool => $c['email'] !== '')),
                'with_phone' => count(array_filter($contacts, static fn (array $c): bool => $c['phone'] !== '' || $c['mobile'] !== '')),
                'no_supplier' => count(array_filter($contacts, static fn (array $c): bool => $c['suppliers'] === [])),
            ],
            'can_create'     => Contact::canCreate(),
            'action'         => self::contactsUrl(),
            'csrf'           => Session::getNewCSRFToken(),
            'edit_id'        => $editId,
            'open_new'       => $openNew,
            'css_url'        => Shell::assetUrl('css/app.css'),
        ];
    }

    /** @throws \InvalidArgumentException */
    public static function contactAction(array $post): string
    {
        $action = (string) ($post['contact_action'] ?? '');
        if ($action === 'delete') {
            $contact = new Contact();
            $id = (int) ($post['contacts_id'] ?? 0);
            if (!$contact->can($id, DELETE)) {
                throw new \InvalidArgumentException('Sem permissão para excluir este contato.');
            }
            $contact->delete(['id' => $id]);
            return 'Contato movido para a lixeira.';
        }
        if ($action !== 'save') {
            throw new \InvalidArgumentException('Ação inválida.');
        }
        [$id, $created] = SupplierPage::saveContact($post, (int) ($_SESSION['glpiactive_entity'] ?? 0));
        self::syncSupplier('glpi_contacts_suppliers', 'contacts_id', \Contact_Supplier::class, $id, (int) ($post['suppliers_id'] ?? 0));
        return $created ? 'Contato criado.' : 'Contato salvo.';
    }

    // ------------------------------------------------------------------
    // Contratos
    // ------------------------------------------------------------------

    /** @return array<string, mixed> */
    public static function contractsData(int $editId = 0, bool $openNew = false): array
    {
        global $DB;
        $opt = SupplierPage::contractOptions();
        $where = ['is_deleted' => 0, 'is_template' => 0];
        $entities = getEntitiesRestrictCriteria('glpi_contracts', '', '', true);
        $rows = [];
        foreach ($DB->request(['FROM' => 'glpi_contracts', 'WHERE' => $entities === [] ? $where : [$where, $entities], 'ORDER' => 'name ASC']) as $row) {
            $rows[(int) $row['id']] = $row;
        }
        $suppliers = self::suppliersOf('glpi_contracts_suppliers', 'contracts_id', array_keys($rows));

        $contracts = [];
        foreach ($rows as $id => $row) {
            $contracts[] = SupplierPage::contractRow($row, $opt['types'], $opt['states'], $opt['locations'], $opt['renewals'])
                + ['suppliers' => $suppliers[$id] ?? []];
        }
        // Vencidos e vencendo primeiro; depois por nome.
        $weight = ['red' => 0, 'orange' => 1, 'green' => 2, 'muted' => 3];
        usort($contracts, static fn (array $a, array $b): int => [$weight[$a['situation']['tone']], $a['name']] <=> [$weight[$b['situation']['tone']], $b['name']]);

        $tones = array_count_values(array_map(static fn (array $c): string => $c['situation']['tone'], $contracts));
        return [
            'contracts'          => $contracts,
            'contract_types'     => $opt['types'],
            'contract_states'    => $opt['states'],
            'contract_locations' => $opt['locations'],
            'contract_renewals'  => $opt['renewals'],
            'contract_alerts'    => $opt['alerts'],
            'suppliers'          => self::suppliers(),
            'kpis'               => [
                'total'    => count($contracts),
                'active'   => $tones['green'] ?? 0,
                'expiring' => $tones['orange'] ?? 0,
                'expired'  => $tones['red'] ?? 0,
            ],
            'can_create'         => Contract::canCreate(),
            'action'             => self::contractsUrl(),
            'csrf'               => Session::getNewCSRFToken(),
            'edit_id'            => $editId,
            'open_new'           => $openNew,
            'css_url'            => Shell::assetUrl('css/app.css'),
        ];
    }

    /** @throws \InvalidArgumentException */
    public static function contractAction(array $post): string
    {
        $action = (string) ($post['contract_action'] ?? '');
        if ($action === 'delete') {
            $contract = new Contract();
            $id = (int) ($post['contracts_id'] ?? 0);
            if (!$contract->can($id, DELETE)) {
                throw new \InvalidArgumentException('Sem permissão para excluir este contrato.');
            }
            $contract->delete(['id' => $id]);
            return 'Contrato movido para a lixeira.';
        }
        if ($action !== 'save') {
            throw new \InvalidArgumentException('Ação inválida.');
        }
        [$id, $created] = SupplierPage::saveContract($post, (int) ($_SESSION['glpiactive_entity'] ?? 0));
        self::syncSupplier('glpi_contracts_suppliers', 'contracts_id', \Contract_Supplier::class, $id, (int) ($post['suppliers_id'] ?? 0));
        return $created ? 'Contrato criado.' : 'Contrato salvo.';
    }

    /**
     * Campo "Fornecedor" (um so): com um unico fornecedor ligado, trocar ou
     * escolher "Nenhum" substitui o vinculo; com varios, so acrescenta.
     */
    private static function syncSupplier(string $table, string $field, string $linkClass, int $id, int $supplierId): void
    {
        global $DB;
        $links = [];
        foreach ($DB->request(['SELECT' => ['id', 'suppliers_id'], 'FROM' => $table, 'WHERE' => [$field => $id]]) as $row) {
            $links[(int) $row['suppliers_id']] = (int) $row['id'];
        }
        $link = new $linkClass();
        if (count($links) === 1 && !isset($links[$supplierId])) {
            $link->delete(['id' => reset($links)], true);
        }
        if ($supplierId > 0 && !isset($links[$supplierId])) {
            $link->add(['suppliers_id' => $supplierId, $field => $id]);
        }
    }

    private static function initials(string $name): string
    {
        $parts = preg_split('/\s+/u', trim($name)) ?: [];
        return mb_strtoupper(mb_substr($parts[0] ?? '?', 0, 1) . (count($parts) > 1 ? mb_substr((string) end($parts), 0, 1) : ''));
    }
}
