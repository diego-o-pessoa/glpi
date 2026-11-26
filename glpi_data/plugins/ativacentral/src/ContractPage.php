<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativacentral;

use Contract;
use Contract_Supplier;
use ContractType;
use Location;
use Session;
use State;
use Supplier;

/**
 * Tela de contrato (novo e ficha com abas) no estilo de Fornecedores do
 * Ativa Central: formulario principal, aba de fornecedores vinculada, custos,
 * itens, documentos (painel Ativa Central), chamados e links.
 */
final class ContractPage
{
    /** Abas da tela do contrato: chave => [titulo, icone, aba do GLPI ou null]. */
    public const TABS = [
        'main'      => ['Contrato', 'ti-file-certificate', null],
        'suppliers' => ['Fornecedores', 'ti-building-store', null],
        'costs'     => ['Custos', 'ti-currency-dollar', 'ContractCost$1'],
        'items'     => ['Itens', 'ti-package', 'Contract_Item$1'],
        'documents' => ['Documentos', 'ti-files', 'Document_Item$1'],
        'tickets'   => ['Chamados', 'ti-ticket', 'Ticket_Contract$1'],
        'links'     => ['Links', 'ti-link', 'ManualLink$1'],
    ];

    public static function listUrl(): string
    {
        return Shell::url('/front/contracts.php');
    }

    public static function newUrl(int $supplierId = 0): string
    {
        return Shell::url('/front/contract.view.php' . ($supplierId > 0 ? '?suppliers_id=' . $supplierId : ''));
    }

    public static function url(int $id, string $tab = 'main'): string
    {
        return Shell::url('/front/contract.view.php?id=' . $id . ($tab !== 'main' ? '&tab=' . rawurlencode($tab) : ''));
    }

    /**
     * Dados da tela de contrato (novo ou existente, com abas).
     *
     * @param array<string, mixed> $old valores enviados quando houve erro
     * @return array<string, mixed>
     */
    public static function viewData(?Contract $contract, string $tab, array $old = [], string $error = '', int $presetSupplierId = 0): array
    {
        global $CFG_GLPI;

        $id = $contract !== null ? (int) $contract->getID() : 0;
        $f = $contract !== null ? $contract->fields : [];
        $value = static fn (string $key, mixed $default = '') => array_key_exists($key, $old) ? $old[$key] : $default;
        $opt = SupplierPage::contractOptions();
        $linkedSuppliers = $id > 0 ? self::linkedSuppliers($id) : [];

        $tabs = [];
        if ($id > 0) {
            foreach (self::TABS as $key => [$label, $icon]) {
                $tabs[] = [
                    'key'    => $key,
                    'label'  => $label,
                    'icon'   => $icon,
                    'url'    => self::url($id, $key),
                    'count'  => self::tabCount($key, $id),
                    'active' => $key === $tab,
                ];
            }
        }

        $summary = $id > 0
            ? SupplierPage::contractRow($f, $opt['types'], $opt['states'], $opt['locations'], $opt['renewals'])
            : [
                'type_label'  => '',
                'state_label' => '',
                'begin_text'  => '',
                'end_text'    => '',
                'situation'   => ['label' => '', 'tone' => 'muted'],
            ];

        $canUpdate = $contract === null ? Contract::canCreate() : $contract->can($id, UPDATE);
        $data = [
            'id'           => $id,
            'is_new'       => $id === 0,
            'tab'          => $tab,
            'tabs'         => $tabs,
            'error'        => $error,
            'back'         => self::listUrl(),
            'action'       => $id > 0 ? self::url($id, $tab) : self::newUrl($presetSupplierId),
            'csrf'         => Session::getNewCSRFToken(),
            'css_url'      => Shell::assetUrl('css/app.css'),
            'title'        => $id > 0 ? (string) ($f['name'] ?: 'Contrato #' . $id) : 'Novo contrato',
            'type_label'   => $summary['type_label'],
            'state_label'  => $summary['state_label'],
            'situation'    => $summary['situation'],
            'header_num'   => (string) ($f['num'] ?? ''),
            'begin_text'   => $summary['begin_text'],
            'end_text'     => $summary['end_text'],
            'suppliers'    => $linkedSuppliers,
            'can_update'   => $canUpdate,
            'can_delete'   => $contract !== null && $contract->can($id, DELETE),
        ];

        $glpiTab = self::TABS[$tab][2] ?? null;
        if ($id > 0 && $glpiTab !== null) {
            $root = rtrim((string) $CFG_GLPI['root_doc'], '/');
            $data['tab_url'] = $root . '/ajax/common.tabs.php?' . http_build_query([
                '_target'   => $root . '/front/contract.form.php',
                '_itemtype' => 'Contract',
                '_glpi_tab' => $glpiTab,
                'id'        => $id,
            ]);
            return $data;
        }
        if ($id > 0 && $tab === 'suppliers') {
            return $data + self::suppliersData($contract, $linkedSuppliers);
        }

        $entity = $contract !== null ? (int) $contract->getEntityID() : (int) ($_SESSION['glpiactive_entity'] ?? 0);
        $stateHelper = $contract ?? new Contract();
        $defaultAlert = 0;
        if ($contract === null) {
            $stateHelper->post_getEmpty();
            $defaultAlert = (int) ($stateHelper->fields['alert'] ?? 0);
        }

        $data['f_name'] = (string) $value('name', $f['name'] ?? '');
        $data['f_num'] = (string) $value('num', $f['num'] ?? '');
        $data['f_accounting_number'] = (string) $value('accounting_number', $f['accounting_number'] ?? '');
        $data['f_comment'] = (string) $value('comment', $f['comment'] ?? '');
        $data['f_begin_date'] = substr((string) $value('begin_date', $f['begin_date'] ?? ''), 0, 10);
        $data['f_duration'] = (int) $value('duration', $id > 0 ? ($f['duration'] ?? 0) : 12);
        $data['f_periodicity'] = (int) $value('periodicity', $id > 0 ? ($f['periodicity'] ?? 0) : 12);
        $data['f_billing'] = (int) $value('billing', $f['billing'] ?? 0);
        $data['f_notice'] = (int) $value('notice', $f['notice'] ?? 0);
        $data['f_renewal'] = (int) $value('renewal', $f['renewal'] ?? 0);
        $data['f_max_links_allowed'] = (int) $value('max_links_allowed', $f['max_links_allowed'] ?? 0);
        $data['f_alert'] = (int) $value('alert', $id > 0 ? ($f['alert'] ?? 0) : $defaultAlert);

        $data['contract_renewals'] = $opt['renewals'];
        $data['contract_alerts'] = $opt['alerts'];

        $data['types_html'] = ContractType::dropdown([
            'name'     => 'contracttypes_id',
            'value'    => (int) $value('contracttypes_id', $f['contracttypes_id'] ?? 0),
            'display'  => false,
            'width'    => '100%',
            'readonly' => !$canUpdate,
        ]);
        $data['states_html'] = State::dropdown([
            'name'      => 'states_id',
            'value'     => (int) $value('states_id', $f['states_id'] ?? 0),
            'entity'    => $entity,
            'condition' => $stateHelper->getStateVisibilityCriteria(),
            'display'   => false,
            'width'     => '100%',
            'readonly'  => !$canUpdate,
        ]);
        $data['locations_html'] = Location::dropdown([
            'name'     => 'locations_id',
            'value'    => (int) $value('locations_id', $f['locations_id'] ?? 0),
            'entity'   => $entity,
            'display'  => false,
            'width'    => '100%',
            'readonly' => !$canUpdate,
        ]);

        // Fornecedor principal na aba Contrato (quando ha mais de um, mostra resumo + link para a aba Fornecedores).
        $singleSupplierId = count($linkedSuppliers) === 1 ? (int) $linkedSuppliers[0]['id'] : 0;
        $selectedSupplier = (int) $value('suppliers_id', $id === 0 ? $presetSupplierId : $singleSupplierId);
        $data['multi_suppliers'] = count($linkedSuppliers) > 1;
        $data['suppliers_tab_url'] = $id > 0 ? self::url($id, 'suppliers') : '';
        $data['supplier_html'] = !$data['multi_suppliers'] ? Supplier::dropdown([
            'name'     => 'suppliers_id',
            'value'    => $selectedSupplier,
            'entity'   => $entity,
            'display'  => false,
            'width'    => '100%',
            'readonly' => !$canUpdate,
        ]) : '';

        return $data;
    }

    /**
     * Cria ou atualiza um contrato com todos os campos da tela principal.
     *
     * @throws \InvalidArgumentException
     */
    public static function save(?Contract $contract, array $input): int
    {
        $name = trim((string) ($input['name'] ?? ''));
        if ($name === '') {
            throw new \InvalidArgumentException('Dê um nome ao contrato.');
        }

        $fields = [
            'name'              => $name,
            'num'               => trim((string) ($input['num'] ?? '')),
            'accounting_number' => trim((string) ($input['accounting_number'] ?? '')),
            'comment'           => trim((string) ($input['comment'] ?? '')),
        ];
        foreach (['contracttypes_id', 'states_id', 'locations_id', 'duration', 'notice', 'periodicity', 'billing', 'renewal', 'max_links_allowed', 'alert'] as $key) {
            $fields[$key] = max(0, (int) ($input[$key] ?? 0));
        }

        $begin = trim((string) ($input['begin_date'] ?? ''));
        $fields['begin_date'] = preg_match('/^\d{4}-\d{2}-\d{2}$/D', $begin) === 1 ? $begin : null;

        if ($contract === null) {
            if ($fields['begin_date'] === null) {
                unset($fields['begin_date']);
            }
            $contract = new Contract();
            $id = (int) $contract->add($fields + [
                'entities_id'  => (int) ($_SESSION['glpiactive_entity'] ?? 0),
                'is_recursive' => 1,
            ]);
            if ($id <= 0) {
                throw new \InvalidArgumentException('Não foi possível criar o contrato.');
            }
        } else {
            $id = (int) $contract->getID();
            if (!$contract->update(['id' => $id] + $fields)) {
                throw new \InvalidArgumentException('Não foi possível salvar o contrato.');
            }
        }

        if (array_key_exists('suppliers_id', $input)) {
            self::syncSupplier($id, max(0, (int) $input['suppliers_id']));
        }

        return $id;
    }

    /**
     * Acoes da aba Fornecedores do contrato: vincular ou desvincular fornecedor.
     *
     * @throws \InvalidArgumentException
     */
    public static function supplierAction(Contract $contract, array $input): string
    {
        $contractId = (int) $contract->getID();
        $action = (string) ($input['supplier_action'] ?? '');
        $link = new Contract_Supplier();

        if ($action === 'link') {
            $supplierId = (int) ($input['suppliers_id'] ?? 0);
            if ($supplierId <= 0) {
                throw new \InvalidArgumentException('Escolha um fornecedor.');
            }
            if (countElementsInTable('glpi_contracts_suppliers', ['contracts_id' => $contractId, 'suppliers_id' => $supplierId]) === 0) {
                $link->add(['contracts_id' => $contractId, 'suppliers_id' => $supplierId]);
            }
            return 'Fornecedor vinculado ao contrato.';
        }

        if ($action === 'unlink') {
            $linkId = (int) ($input['link_id'] ?? 0);
            if ($linkId <= 0 || !$link->getFromDB($linkId) || (int) $link->fields['contracts_id'] !== $contractId) {
                throw new \InvalidArgumentException('Vínculo de fornecedor não encontrado neste contrato.');
            }
            $link->delete(['id' => $linkId], true);
            return 'Fornecedor desvinculado deste contrato.';
        }

        throw new \InvalidArgumentException('Ação inválida.');
    }

    // ------------------------------------------------------------------
    // Aba Fornecedores
    // ------------------------------------------------------------------

    /**
     * @param list<array<string, mixed>> $linkedSuppliers
     * @return array<string, mixed>
     */
    private static function suppliersData(Contract $contract, array $linkedSuppliers): array
    {
        global $DB;

        $linkedIds = array_column($linkedSuppliers, 'id');
        $where = ['is_deleted' => 0];
        if ($linkedIds !== []) {
            $where[] = ['NOT' => ['id' => $linkedIds]];
        }
        $entities = getEntitiesRestrictCriteria('glpi_suppliers', '', '', true);
        $available = [];
        foreach ($DB->request([
            'SELECT' => ['id', 'name', 'registration_number'],
            'FROM'   => 'glpi_suppliers',
            'WHERE'  => $entities === [] ? $where : [$where, $entities],
            'ORDER'  => 'name ASC',
        ]) as $row) {
            $cnpj = trim((string) ($row['registration_number'] ?? ''));
            $available[] = [
                'id'    => (int) $row['id'],
                'label' => (string) $row['name'] . ($cnpj !== '' ? ' — CNPJ ' . $cnpj : ''),
            ];
        }

        return [
            'available_suppliers' => $available,
            'new_supplier_url'    => Supplier::canCreate() ? SupplierPage::newUrl() : '',
        ];
    }

    /** @return list<array<string, mixed>> */
    private static function linkedSuppliers(int $contractId): array
    {
        global $DB;

        $categories = SupplierPage::options('glpi_suppliertypes');
        $out = [];
        foreach ($DB->request([
            'SELECT'     => [
                'glpi_contracts_suppliers.id AS link_id',
                'glpi_suppliers.id',
                'glpi_suppliers.name',
                'glpi_suppliers.suppliertypes_id',
                'glpi_suppliers.registration_number',
                'glpi_suppliers.phonenumber',
                'glpi_suppliers.email',
                'glpi_suppliers.town',
                'glpi_suppliers.state',
                'glpi_suppliers.is_active',
            ],
            'FROM'       => 'glpi_contracts_suppliers',
            'INNER JOIN' => ['glpi_suppliers' => ['ON' => ['glpi_contracts_suppliers' => 'suppliers_id', 'glpi_suppliers' => 'id']]],
            'WHERE'      => ['glpi_contracts_suppliers.contracts_id' => $contractId, 'glpi_suppliers.is_deleted' => 0],
            'ORDER'      => 'glpi_suppliers.name ASC',
        ]) as $row) {
            $id = (int) $row['id'];
            $name = (string) $row['name'];
            $out[] = [
                'id'       => $id,
                'link_id'  => (int) $row['link_id'],
                'name'     => $name,
                'initials' => self::initials($name),
                'category' => $categories[(int) $row['suppliertypes_id']] ?? '',
                'cnpj'     => (string) $row['registration_number'],
                'phone'    => (string) $row['phonenumber'],
                'email'    => (string) $row['email'],
                'city'     => trim((string) $row['town'] . ((string) $row['state'] !== '' ? '/' . $row['state'] : ''), '/'),
                'active'   => (int) $row['is_active'] === 1,
                'url'      => SupplierPage::url($id),
            ];
        }
        return $out;
    }

    /** Sincroniza o fornecedor principal quando ha 0 ou 1 fornecedor ligado. */
    private static function syncSupplier(int $contractId, int $supplierId): void
    {
        global $DB;

        $links = [];
        foreach ($DB->request([
            'SELECT' => ['id', 'suppliers_id'],
            'FROM'   => 'glpi_contracts_suppliers',
            'WHERE'  => ['contracts_id' => $contractId],
        ]) as $row) {
            $links[(int) $row['suppliers_id']] = (int) $row['id'];
        }

        $link = new Contract_Supplier();
        if (count($links) === 1 && !isset($links[$supplierId])) {
            $link->delete(['id' => reset($links)], true);
        }
        if ($supplierId > 0 && !isset($links[$supplierId])) {
            $link->add(['contracts_id' => $contractId, 'suppliers_id' => $supplierId]);
        }
    }

    private static function tabCount(string $tab, int $id): int
    {
        try {
            return match ($tab) {
                'suppliers' => countElementsInTable('glpi_contracts_suppliers', ['contracts_id' => $id]),
                'costs'     => countElementsInTable('glpi_contractcosts', ['contracts_id' => $id]),
                'items'     => countElementsInTable('glpi_contracts_items', ['contracts_id' => $id]),
                'documents' => countElementsInTable('glpi_documents_items', ['itemtype' => 'Contract', 'items_id' => $id]),
                'tickets'   => countElementsInTable('glpi_tickets_contracts', ['contracts_id' => $id]),
                'links'     => countElementsInTable('glpi_manuallinks', ['itemtype' => 'Contract', 'items_id' => $id]),
                default     => 0,
            };
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private static function initials(string $name): string
    {
        $parts = preg_split('/\s+/u', trim($name)) ?: [];
        $parts = array_values(array_filter($parts, static fn ($p) => mb_strlen($p) > 2 || count($parts) <= 2));
        return mb_strtoupper(mb_substr($parts[0] ?? '?', 0, 1) . (count($parts) > 1 ? mb_substr((string) end($parts), 0, 1) : ''));
    }
}
