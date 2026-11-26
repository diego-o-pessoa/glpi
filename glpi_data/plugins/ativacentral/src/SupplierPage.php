<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativacentral;

use Contact;
use Contact_Supplier;
use Html;
use Session;
use Supplier;
use SupplierType;

/**
 * Fornecedores no estilo do Ativa Central: lista, adicionar/editar
 * (sem fax e comentarios; "Tipo" vira Categoria e o numero
 * administrativo vira CNPJ) e aba de contatos com gaveta propria.
 */
final class SupplierPage
{
    /** Abas: chave => [rotulo, icone, aba do GLPI (null = nossa)]. */
    public const TABS = [
        'main'      => ['Fornecedor', 'ti-building-store', null],
        'contacts'  => ['Contatos', 'ti-address-book', null],
        'contracts' => ['Contratos', 'ti-file-certificate', null],
        'items'     => ['Itens', 'ti-package', 'Infocom$1'],
        'documents' => ['Documentos', 'ti-files', 'Document_Item$1'],
        'changes'   => ['Mudanças', 'ti-clipboard-check', 'Change_Item$1'],
        'links'     => ['Links', 'ti-link', 'ManualLink$1'],
    ];

    /** Campos do contato na gaveta (sem sobrenome e sem fax). */
    private const CONTACT_FIELDS = [
        // Sem numero administrativo, telefone 2, comentarios e imagens
        // (campos que ja existem no GLPI ficam como estao).
        'name', 'phone', 'mobile', 'email', 'address', 'town', 'postcode', 'state', 'country',
    ];

    public static function listUrl(): string
    {
        return Shell::url('/front/suppliers.php');
    }

    public static function url(int $id, string $tab = 'main'): string
    {
        return Shell::url('/front/supplier.view.php?id=' . $id . ($tab !== 'main' ? '&tab=' . rawurlencode($tab) : ''));
    }

    public static function newUrl(): string
    {
        return Shell::url('/front/supplier.view.php');
    }

    // ------------------------------------------------------------------
    // Lista
    // ------------------------------------------------------------------

    /** @return array<string, mixed> */
    public static function listData(): array
    {
        global $DB;

        $categories = self::categories();
        $where = ['glpi_suppliers.is_deleted' => 0];
        $entities = getEntitiesRestrictCriteria('glpi_suppliers', '', '', true);
        $scope = $entities === [] ? [$where] : [$where, $entities];

        $rows = [];
        foreach ($DB->request([
            'SELECT' => ['id', 'name', 'suppliertypes_id', 'registration_number', 'phonenumber', 'email', 'website', 'town', 'state', 'is_active'],
            'FROM'   => 'glpi_suppliers',
            'WHERE'  => $scope,
            'ORDER'  => 'name ASC',
        ]) as $row) {
            $rows[(int) $row['id']] = $row;
        }
        $ids = array_keys($rows);

        $contacts = self::countBy('glpi_contacts_suppliers', 'suppliers_id', $ids);
        $contracts = self::countBy('glpi_contracts_suppliers', 'suppliers_id', $ids);

        // Contratos que vencem nos proximos 30 dias (inicio + duracao em meses).
        $expiring = [];
        if ($ids !== []) {
            $today = date('Y-m-d');
            $limit = date('Y-m-d', strtotime('+30 days'));
            foreach ($DB->request([
                'SELECT'     => ['glpi_contracts_suppliers.suppliers_id', 'glpi_contracts.begin_date', 'glpi_contracts.duration'],
                'FROM'       => 'glpi_contracts_suppliers',
                'INNER JOIN' => ['glpi_contracts' => ['ON' => ['glpi_contracts_suppliers' => 'contracts_id', 'glpi_contracts' => 'id']]],
                'WHERE'      => ['glpi_contracts_suppliers.suppliers_id' => $ids, 'glpi_contracts.is_deleted' => 0],
            ]) as $c) {
                if (empty($c['begin_date']) || (int) $c['duration'] <= 0) {
                    continue;
                }
                $end = date('Y-m-d', strtotime($c['begin_date'] . ' +' . (int) $c['duration'] . ' months'));
                if ($end >= $today && $end <= $limit) {
                    $expiring[(int) $c['suppliers_id']] = true;
                }
            }
        }

        $list = [];
        foreach ($rows as $id => $row) {
            $list[] = [
                'id'        => $id,
                'url'       => self::url($id),
                'name'      => (string) $row['name'],
                'initials'  => self::initials((string) $row['name']),
                'category'  => $categories[(int) $row['suppliertypes_id']] ?? '',
                'cnpj'      => (string) $row['registration_number'],
                'phone'     => (string) $row['phonenumber'],
                'email'     => (string) $row['email'],
                'city'      => trim((string) $row['town'] . ((string) $row['state'] !== '' ? ' / ' . $row['state'] : '')),
                'active'    => (int) $row['is_active'] === 1,
                'contacts'  => $contacts[$id] ?? 0,
                'contracts' => $contracts[$id] ?? 0,
                'expiring'  => isset($expiring[$id]),
            ];
        }

        $active = count(array_filter($list, static fn (array $s): bool => $s['active']));
        return [
            'suppliers'  => $list,
            'categories' => array_values(array_unique(array_filter(array_column($list, 'category')))),
            'kpis'       => [
                'total'     => count($list),
                'active'    => $active,
                'contracts' => count(array_filter($list, static fn (array $s): bool => $s['contracts'] > 0)),
                'expiring'  => count($expiring),
            ],
            'new_url'    => Supplier::canCreate() ? self::newUrl() : '',
            'css_url'    => Shell::assetUrl('css/app.css'),
        ];
    }

    // ------------------------------------------------------------------
    // Fornecedor (novo / editar)
    // ------------------------------------------------------------------

    /** @return array<string, mixed> */
    public static function viewData(?Supplier $supplier, string $tab, array $old = [], string $error = ''): array
    {
        global $CFG_GLPI;

        $id = $supplier !== null ? (int) $supplier->getID() : 0;
        $f = $supplier?->fields ?? ['is_active' => 1];
        $value = static fn (string $key, mixed $default = '') => $old[$key] ?? $default;
        $categories = self::categories();

        $tabs = [];
        if ($id > 0) {
            foreach (self::TABS as $key => [$label, $icon]) {
                $tabs[] = [
                    'key' => $key, 'label' => $label, 'icon' => $icon,
                    'url' => self::url($id, $key), 'count' => self::tabCount($key, $id), 'active' => $key === $tab,
                ];
            }
        }

        $data = [
            'id'         => $id,
            'is_new'     => $id === 0,
            'tab'        => $tab,
            'tabs'       => $tabs,
            'error'      => $error,
            'back'       => self::listUrl(),
            'action'     => $id > 0 ? self::url($id, $tab) : self::newUrl(),
            'csrf'       => Session::getNewCSRFToken(),
            'css_url'    => Shell::assetUrl('css/app.css'),
            'title'      => $id > 0 ? (string) $f['name'] : 'Novo fornecedor',
            'category'   => $categories[(int) ($f['suppliertypes_id'] ?? 0)] ?? '',
            'header_phone' => (string) ($f['phonenumber'] ?? ''),
            'header_email' => (string) ($f['email'] ?? ''),
            'can_update' => $supplier === null ? Supplier::canCreate() : $supplier->can($id, UPDATE),
            'can_delete' => $supplier !== null && $supplier->can($id, DELETE),
            'active'     => (int) $value('is_active', $f['is_active'] ?? 1) === 1,
        ];

        $glpiTab = self::TABS[$tab][2] ?? null;
        if ($id > 0 && $glpiTab !== null) {
            $root = rtrim((string) $CFG_GLPI['root_doc'], '/');
            $data['tab_url'] = $root . '/ajax/common.tabs.php?' . http_build_query([
                '_target' => $root . '/front/supplier.form.php', '_itemtype' => 'Supplier', '_glpi_tab' => $glpiTab, 'id' => $id,
            ]);
            return $data;
        }
        if ($id > 0 && $tab === 'contacts') {
            return $data + self::contactsData($supplier);
        }
        if ($id > 0 && $tab === 'contracts') {
            return $data + self::contractsData($supplier);
        }

        foreach (['name', 'registration_number', 'phonenumber', 'email', 'website', 'address', 'town', 'state', 'postcode', 'country'] as $key) {
            $data['f_' . $key] = (string) $value($key, $f[$key] ?? '');
        }
        $data['categories_html'] = SupplierType::dropdown([
            'name' => 'suppliertypes_id', 'value' => (int) $value('suppliertypes_id', $f['suppliertypes_id'] ?? 0),
            'display' => false, 'width' => '100%',
        ]);
        $data['pictures'] = self::pictures($f['pictures'] ?? '');
        $data['pictures_html'] = Html::file([
            'name' => 'pictures', 'onlyimages' => true, 'multiple' => true, 'display' => false,
        ]);
        return $data;
    }

    /** @throws \InvalidArgumentException */
    public static function save(?Supplier $supplier, array $input): int
    {
        $name = trim((string) ($input['name'] ?? ''));
        if ($name === '') {
            throw new \InvalidArgumentException('Dê um nome ao fornecedor.');
        }
        $fields = ['name' => $name, 'suppliertypes_id' => max(0, (int) ($input['suppliertypes_id'] ?? 0)), 'is_active' => !empty($input['is_active']) ? 1 : 0];
        foreach (['registration_number', 'phonenumber', 'email', 'website', 'address', 'town', 'state', 'postcode', 'country'] as $key) {
            $fields[$key] = trim((string) ($input[$key] ?? ''));
        }
        if ($fields['email'] !== '' && filter_var($fields['email'], FILTER_VALIDATE_EMAIL) === false) {
            throw new \InvalidArgumentException('E-mail inválido.');
        }
        // Imagens enviadas pelo campo de arquivos do GLPI.
        foreach (['_pictures', '_prefix_pictures', '_tag_pictures'] as $key) {
            if (isset($input[$key])) {
                $fields[$key] = $input[$key];
            }
        }

        if ($supplier === null) {
            $supplier = new Supplier();
            $id = (int) $supplier->add($fields + ['entities_id' => (int) ($_SESSION['glpiactive_entity'] ?? 0), 'is_recursive' => 1]);
            if ($id <= 0) {
                throw new \InvalidArgumentException('Não foi possível criar o fornecedor.');
            }
            return $id;
        }
        $id = (int) $supplier->getID();
        if (!$supplier->update(['id' => $id] + $fields)) {
            throw new \InvalidArgumentException('Não foi possível salvar o fornecedor.');
        }
        return $id;
    }

    // ------------------------------------------------------------------
    // Contatos
    // ------------------------------------------------------------------

    /** @return array<string, mixed> */
    private static function contactsData(Supplier $supplier): array
    {
        global $DB;

        $types = self::options('glpi_contacttypes');
        $titles = self::options('glpi_usertitles');
        $contacts = [];
        foreach ($DB->request([
            'SELECT'     => ['glpi_contacts.*', 'glpi_contacts_suppliers.id AS link_id'],
            'FROM'       => 'glpi_contacts_suppliers',
            'INNER JOIN' => ['glpi_contacts' => ['ON' => ['glpi_contacts_suppliers' => 'contacts_id', 'glpi_contacts' => 'id']]],
            'WHERE'      => ['glpi_contacts_suppliers.suppliers_id' => (int) $supplier->getID(), 'glpi_contacts.is_deleted' => 0],
            'ORDER'      => 'glpi_contacts.name ASC',
        ]) as $row) {
            $name = self::contactName($row);
            $contact = [
                'id'        => (int) $row['id'],
                'link_id'   => (int) $row['link_id'],
                'full_name' => $name,
                'initials'  => self::initials($name),
                'type'      => $types[(int) $row['contacttypes_id']] ?? '',
                'title'     => $titles[(int) $row['usertitles_id']] ?? '',
                'contacttypes_id' => (int) $row['contacttypes_id'],
                'usertitles_id'   => (int) $row['usertitles_id'],
            ];
            foreach (self::CONTACT_FIELDS as $key) {
                $contact[$key] = $key === 'name' ? $name : (string) ($row[$key] ?? '');
            }
            $contacts[] = $contact;
        }

        // Contatos ja cadastrados que ainda nao estao neste fornecedor.
        $linked = array_column($contacts, 'id');
        $existing = [];
        $where = ['is_deleted' => 0];
        if ($linked !== []) {
            $where[] = ['NOT' => ['id' => $linked]];
        }
        $entities = getEntitiesRestrictCriteria('glpi_contacts', '', '', true);
        foreach ($DB->request(['SELECT' => ['id', 'name', 'firstname', 'email'], 'FROM' => 'glpi_contacts', 'WHERE' => $entities === [] ? $where : [$where, $entities], 'ORDER' => 'name ASC']) as $row) {
            $existing[] = ['id' => (int) $row['id'], 'label' => self::contactName($row) . ((string) $row['email'] !== '' ? ' — ' . $row['email'] : '')];
        }

        return [
            'contacts'        => $contacts,
            'contacts_json'   => $contacts,
            'existing'        => $existing,
            'contact_types'   => $types,
            'contact_titles'  => $titles,
            'can_contacts'    => Contact::canCreate(),
        ];
    }

    /**
     * Acoes da aba Contatos: salvar (novo ou editar), vincular existente,
     * desvincular e excluir.
     *
     * @throws \InvalidArgumentException
     */
    public static function contactAction(Supplier $supplier, array $input): string
    {
        $supplierId = (int) $supplier->getID();
        $action = (string) ($input['contact_action'] ?? '');
        $link = new Contact_Supplier();

        if ($action === 'link') {
            $contactId = (int) ($input['contacts_id'] ?? 0);
            if ($contactId <= 0) {
                throw new \InvalidArgumentException('Escolha um contato.');
            }
            if (countElementsInTable('glpi_contacts_suppliers', ['suppliers_id' => $supplierId, 'contacts_id' => $contactId]) === 0) {
                $link->add(['suppliers_id' => $supplierId, 'contacts_id' => $contactId]);
            }
            return 'Contato vinculado.';
        }
        if ($action === 'unlink') {
            $link->delete(['id' => (int) ($input['link_id'] ?? 0)], true);
            return 'Contato desvinculado deste fornecedor.';
        }
        if ($action === 'delete') {
            $contact = new Contact();
            $contactId = (int) ($input['contacts_id'] ?? 0);
            if (!$contact->can($contactId, DELETE)) {
                throw new \InvalidArgumentException('Sem permissão para excluir este contato.');
            }
            $contact->delete(['id' => $contactId]);
            return 'Contato excluído.';
        }
        if ($action !== 'save') {
            throw new \InvalidArgumentException('Ação inválida.');
        }

        [$contactId, $created] = self::saveContact($input, (int) $supplier->fields['entities_id']);
        if (!$created) {
            return 'Contato salvo.';
        }
        $link->add(['suppliers_id' => $supplierId, 'contacts_id' => $contactId]);
        return 'Contato criado e vinculado ao fornecedor.';
    }

    /**
     * Cria ou atualiza um contato (campos da gaveta). Usado no fornecedor e
     * na tela de Contatos.
     *
     * @return array{0: int, 1: bool} id e se foi criado agora
     * @throws \InvalidArgumentException
     */
    public static function saveContact(array $input, int $entity): array
    {
        $name = trim((string) ($input['name'] ?? ''));
        if ($name === '') {
            throw new \InvalidArgumentException('Dê um nome ao contato.');
        }
        $fields = ['name' => $name, 'firstname' => '', 'contacttypes_id' => max(0, (int) ($input['contacttypes_id'] ?? 0)), 'usertitles_id' => max(0, (int) ($input['usertitles_id'] ?? 0))];
        foreach (self::CONTACT_FIELDS as $key) {
            if ($key !== 'name') {
                $fields[$key] = trim((string) ($input[$key] ?? ''));
            }
        }
        if ($fields['email'] !== '' && filter_var($fields['email'], FILTER_VALIDATE_EMAIL) === false) {
            throw new \InvalidArgumentException('E-mail do contato inválido.');
        }
        foreach (['_pictures', '_prefix_pictures', '_tag_pictures'] as $key) {
            if (isset($input[$key])) {
                $fields[$key] = $input[$key];
            }
        }

        $contact = new Contact();
        $contactId = (int) ($input['contacts_id'] ?? 0);
        if ($contactId > 0) {
            if (!$contact->can($contactId, UPDATE) || !$contact->update(['id' => $contactId] + $fields)) {
                throw new \InvalidArgumentException('Não foi possível salvar o contato.');
            }
            return [$contactId, false];
        }
        $contactId = (int) $contact->add($fields + ['entities_id' => $entity, 'is_recursive' => 1]);
        if ($contactId <= 0) {
            throw new \InvalidArgumentException('Não foi possível criar o contato.');
        }
        return [$contactId, true];
    }

    // ------------------------------------------------------------------
    // Contratos
    // ------------------------------------------------------------------

    /** Campos do contrato (sem numero contabil, nota fiscal, max. itens, horas de suporte e comentarios). */
    private const CONTRACT_FIELDS = ['name', 'num', 'contracttypes_id', 'states_id', 'locations_id', 'begin_date', 'duration', 'notice', 'periodicity', 'renewal', 'alert'];

    /** @return array<string, mixed> */
    private static function contractsData(Supplier $supplier): array
    {
        global $DB;

        $types = self::options('glpi_contracttypes');
        $states = self::options('glpi_states');
        $locations = [];
        foreach ($DB->request(['SELECT' => ['id', 'completename'], 'FROM' => 'glpi_locations', 'ORDER' => 'completename ASC']) as $row) {
            $locations[(int) $row['id']] = (string) $row['completename'];
        }
        $renewals = [0 => 'Nunca', 1 => 'Tácita (automática)', 2 => 'Expressa (precisa confirmar)'];
        $alerts = [];
        foreach (\Contract::getAlertName() as $value => $label) {
            $alerts[(int) $value] = (int) $value === 0 ? 'Sem aviso' : (string) $label;
        }

        $today = date('Y-m-d');
        $contracts = [];
        foreach ($DB->request([
            'SELECT'     => ['glpi_contracts.*', 'glpi_contracts_suppliers.id AS link_id'],
            'FROM'       => 'glpi_contracts_suppliers',
            'INNER JOIN' => ['glpi_contracts' => ['ON' => ['glpi_contracts_suppliers' => 'contracts_id', 'glpi_contracts' => 'id']]],
            'WHERE'      => ['glpi_contracts_suppliers.suppliers_id' => (int) $supplier->getID(), 'glpi_contracts.is_deleted' => 0],
            'ORDER'      => 'glpi_contracts.begin_date DESC',
        ]) as $row) {
            $item = self::contractRow($row, $types, $states, $locations, $renewals) + ['link_id' => (int) $row['link_id']];
            $contracts[] = $item;
        }

        $linked = array_column($contracts, 'id');
        $existing = [];
        $where = ['is_deleted' => 0, 'is_template' => 0];
        if ($linked !== []) {
            $where[] = ['NOT' => ['id' => $linked]];
        }
        $entities = getEntitiesRestrictCriteria('glpi_contracts', '', '', true);
        foreach ($DB->request(['SELECT' => ['id', 'name', 'num'], 'FROM' => 'glpi_contracts', 'WHERE' => $entities === [] ? $where : [$where, $entities], 'ORDER' => 'name ASC']) as $row) {
            $existing[] = ['id' => (int) $row['id'], 'label' => (string) $row['name'] . ((string) $row['num'] !== '' ? ' — nº ' . $row['num'] : '')];
        }

        return [
            'contracts'         => $contracts,
            'existing'          => $existing,
            'contract_types'    => $types,
            'contract_states'   => $states,
            'contract_locations' => $locations,
            'contract_renewals' => $renewals,
            'contract_alerts'   => $alerts,
            'can_contracts'     => \Contract::canCreate(),
        ];
    }

    /**
     * Linha de contrato para as telas: vigencia (inicio -> fim), situacao
     * (vigente, vence em N dias, vencido) e os campos da gaveta.
     *
     * @return array<string, mixed>
     */
    public static function contractRow(array $row, array $types, array $states, array $locations, array $renewals): array
    {
        $today = date('Y-m-d');
        $begin = (string) ($row['begin_date'] ?? '');
        $duration = (int) $row['duration'];
        $end = $begin !== '' && $duration > 0 ? date('Y-m-d', strtotime($begin . ' +' . $duration . ' months')) : '';
        $situation = ['label' => 'Sem prazo definido', 'tone' => 'muted'];
        if ($end !== '') {
            $days = (int) floor((strtotime($end) - strtotime($today)) / 86400);
            if ($days < 0) {
                $situation = ['label' => 'Vencido em ' . date('d/m/Y', strtotime($end)), 'tone' => 'red'];
            } elseif ($days <= 30) {
                $situation = ['label' => $days === 0 ? 'Vence hoje' : 'Vence em ' . $days . ($days === 1 ? ' dia' : ' dias'), 'tone' => 'orange'];
            } else {
                $situation = ['label' => 'Vigente até ' . date('d/m/Y', strtotime($end)), 'tone' => 'green'];
            }
        }
        $item = [
            'id'          => (int) $row['id'],
            'type_label'  => $types[(int) $row['contracttypes_id']] ?? '',
            'state_label' => $states[(int) $row['states_id']] ?? '',
            'location_label' => $locations[(int) $row['locations_id']] ?? '',
            'begin_text'  => $begin !== '' ? date('d/m/Y', strtotime($begin)) : '',
            'end_text'    => $end !== '' ? date('d/m/Y', strtotime($end)) : '',
            'renewal_label' => $renewals[(int) $row['renewal']] ?? '',
            'situation'   => $situation,
        ];
        foreach (self::CONTRACT_FIELDS as $key) {
            $item[$key] = in_array($key, ['name', 'num', 'begin_date'], true) ? (string) ($row[$key] ?? '') : (int) ($row[$key] ?? 0);
        }
        $item['begin_date'] = substr($begin, 0, 10);
        return $item;
    }

    /** @return array{types: array, states: array, locations: array, renewals: array, alerts: array} */
    public static function contractOptions(): array
    {
        global $DB;
        $locations = [];
        foreach ($DB->request(['SELECT' => ['id', 'completename'], 'FROM' => 'glpi_locations', 'ORDER' => 'completename ASC']) as $row) {
            $locations[(int) $row['id']] = (string) $row['completename'];
        }
        $alerts = [];
        foreach (\Contract::getAlertName() as $value => $label) {
            $alerts[(int) $value] = (int) $value === 0 ? 'Sem aviso' : (string) $label;
        }
        return [
            'types'     => self::options('glpi_contracttypes'),
            'states'    => self::options('glpi_states'),
            'locations' => $locations,
            'renewals'  => [0 => 'Nunca', 1 => 'Tácita (automática)', 2 => 'Expressa (precisa confirmar)'],
            'alerts'    => $alerts,
        ];
    }

    /**
     * Acoes da aba Contratos: salvar (novo ou editar), vincular existente,
     * desvincular e excluir.
     *
     * @throws \InvalidArgumentException
     */
    public static function contractAction(Supplier $supplier, array $input): string
    {
        $supplierId = (int) $supplier->getID();
        $action = (string) ($input['contract_action'] ?? '');
        $link = new \Contract_Supplier();
        $contract = new \Contract();
        $contractId = (int) ($input['contracts_id'] ?? 0);

        if ($action === 'link') {
            if ($contractId <= 0) {
                throw new \InvalidArgumentException('Escolha um contrato.');
            }
            if (countElementsInTable('glpi_contracts_suppliers', ['suppliers_id' => $supplierId, 'contracts_id' => $contractId]) === 0) {
                $link->add(['suppliers_id' => $supplierId, 'contracts_id' => $contractId]);
            }
            return 'Contrato vinculado.';
        }
        if ($action === 'unlink') {
            $link->delete(['id' => (int) ($input['link_id'] ?? 0)], true);
            return 'Contrato desvinculado deste fornecedor.';
        }
        if ($action === 'delete') {
            if (!$contract->can($contractId, DELETE)) {
                throw new \InvalidArgumentException('Sem permissão para excluir este contrato.');
            }
            $contract->delete(['id' => $contractId]);
            return 'Contrato movido para a lixeira.';
        }
        if ($action !== 'save') {
            throw new \InvalidArgumentException('Ação inválida.');
        }

        [$contractId, $created] = self::saveContract($input, (int) $supplier->fields['entities_id']);
        if (!$created) {
            return 'Contrato salvo.';
        }
        $link->add(['suppliers_id' => $supplierId, 'contracts_id' => $contractId]);
        return 'Contrato criado e vinculado ao fornecedor.';
    }

    /**
     * Cria ou atualiza um contrato (campos da gaveta). Usado no fornecedor e
     * na tela de Contratos.
     *
     * @return array{0: int, 1: bool} id e se foi criado agora
     * @throws \InvalidArgumentException
     */
    public static function saveContract(array $input, int $entity): array
    {
        $contract = new \Contract();
        $contractId = (int) ($input['contracts_id'] ?? 0);
        $name = trim((string) ($input['name'] ?? ''));
        if ($name === '') {
            throw new \InvalidArgumentException('Dê um nome ao contrato.');
        }
        $fields = ['name' => $name, 'num' => trim((string) ($input['num'] ?? ''))];
        foreach (['contracttypes_id', 'states_id', 'locations_id', 'duration', 'notice', 'periodicity', 'renewal', 'alert'] as $key) {
            $fields[$key] = max(0, (int) ($input[$key] ?? 0));
        }
        $begin = trim((string) ($input['begin_date'] ?? ''));
        $fields['begin_date'] = preg_match('/^\d{4}-\d{2}-\d{2}$/D', $begin) === 1 ? $begin : null;

        if ($contractId > 0) {
            if (!$contract->can($contractId, UPDATE) || !$contract->update(['id' => $contractId] + $fields)) {
                throw new \InvalidArgumentException('Não foi possível salvar o contrato.');
            }
            return [$contractId, false];
        }
        if ($fields['begin_date'] === null) {
            unset($fields['begin_date']);
        }
        // Campos que sairam da tela ficam nos padroes (sem limite de itens).
        $contractId = (int) $contract->add($fields + [
            'entities_id'       => $entity,
            'is_recursive'      => 1,
            'max_links_allowed' => 0,
        ]);
        if ($contractId <= 0) {
            throw new \InvalidArgumentException('Não foi possível criar o contrato.');
        }
        return [$contractId, true];
    }

    // ------------------------------------------------------------------
    // Auxiliares
    // ------------------------------------------------------------------

    /** Nome do contato: os dois campos antigos juntos (Nome + Sobrenome). */
    private static function contactName(array $row): string
    {
        return trim((string) ($row['firstname'] ?? '') . ' ' . (string) ($row['name'] ?? '')) ?: 'Contato #' . (int) $row['id'];
    }

    /** @return array<int, string> */
    private static function categories(): array
    {
        return self::options('glpi_suppliertypes');
    }

    /** @return array<int, string> */
    public static function options(string $table): array
    {
        global $DB;
        $out = [];
        foreach ($DB->request(['SELECT' => ['id', 'name'], 'FROM' => $table, 'ORDER' => 'name ASC']) as $row) {
            $out[(int) $row['id']] = (string) $row['name'];
        }
        return $out;
    }

    /** @return array<int, int> */
    private static function countBy(string $table, string $field, array $ids): array
    {
        global $DB;
        if ($ids === []) {
            return [];
        }
        $out = [];
        foreach ($DB->request([
            'SELECT' => [$field, new \Glpi\DBAL\QueryExpression('COUNT(*) AS total')],
            'FROM'   => $table,
            'WHERE'  => [$field => $ids],
            'GROUPBY' => $field,
        ]) as $row) {
            $out[(int) $row[$field]] = (int) $row['total'];
        }
        return $out;
    }

    private static function tabCount(string $tab, int $id): int
    {
        try {
            return match ($tab) {
                'contacts'  => countElementsInTable('glpi_contacts_suppliers', ['suppliers_id' => $id]),
                'contracts' => countElementsInTable('glpi_contracts_suppliers', ['suppliers_id' => $id]),
                'documents' => countElementsInTable('glpi_documents_items', ['itemtype' => 'Supplier', 'items_id' => $id]),
                'items'     => countElementsInTable('glpi_infocoms', ['suppliers_id' => $id]),
                default     => 0,
            };
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /** @return list<string> URLs das imagens ja salvas */
    private static function pictures(mixed $raw): array
    {
        $list = is_string($raw) && $raw !== '' ? json_decode($raw, true) : [];
        $urls = [];
        foreach (is_array($list) ? $list : [] as $path) {
            $urls[] = \Toolbox::getPictureUrl((string) $path);
        }
        return array_values(array_filter($urls));
    }

    private static function initials(string $name): string
    {
        $parts = preg_split('/\s+/u', trim($name)) ?: [];
        $parts = array_values(array_filter($parts, static fn ($p) => mb_strlen($p) > 2 || count($parts) <= 2));
        return mb_strtoupper(mb_substr($parts[0] ?? '?', 0, 1) . (count($parts) > 1 ? mb_substr((string) end($parts), 0, 1) : ''));
    }
}
