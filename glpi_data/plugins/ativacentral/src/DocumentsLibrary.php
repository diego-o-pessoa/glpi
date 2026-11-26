<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativacentral;

use Document;
use Session;

/**
 * Tela "Documentos" (biblioteca) no estilo do Ativa Central: lista com
 * busca e filtro por categoria, gaveta para adicionar (arquivo, nome e
 * categoria, com "+ Nova categoria" so com o nome) e para editar.
 */
final class DocumentsLibrary
{
    public static function url(): string
    {
        return Shell::url('/front/documents.php');
    }

    /** @return array<string, mixed> */
    public static function data(int $editId = 0, bool $openNew = false): array
    {
        global $DB, $CFG_GLPI;

        $root = rtrim((string) $CFG_GLPI['root_doc'], '/');
        $categories = [];
        foreach ($DB->request(['SELECT' => ['id', 'completename'], 'FROM' => 'glpi_documentcategories', 'ORDER' => 'completename ASC']) as $row) {
            $categories[(int) $row['id']] = (string) $row['completename'];
        }

        $where = ['glpi_documents.is_deleted' => 0];
        $entities = getEntitiesRestrictCriteria('glpi_documents', '', '', true);
        $rows = [];
        foreach ($DB->request([
            'SELECT' => ['id', 'name', 'filename', 'filepath', 'mime', 'link', 'documentcategories_id', 'users_id', 'date_creation'],
            'FROM'   => 'glpi_documents',
            'WHERE'  => $entities === [] ? $where : [$where, $entities],
            'ORDER'  => 'id DESC',
        ]) as $row) {
            $rows[(int) $row['id']] = $row;
        }

        // Onde cada documento esta anexado (ate 5 itens por documento).
        $links = [];
        $counts = [];
        if ($rows !== []) {
            foreach ($DB->request([
                'SELECT' => ['documents_id', 'itemtype', 'items_id'],
                'FROM'   => 'glpi_documents_items',
                'WHERE'  => ['documents_id' => array_keys($rows)],
            ]) as $l) {
                $docId = (int) $l['documents_id'];
                $counts[$docId] = ($counts[$docId] ?? 0) + 1;
                if (count($links[$docId] ?? []) < 5) {
                    $links[$docId][] = self::linkedItem((string) $l['itemtype'], (int) $l['items_id']);
                }
            }
        }

        $docs = [];
        $usedCategories = [];
        foreach ($rows as $id => $row) {
            $file = (string) $row['filename'];
            $path = (string) $row['filepath'];
            $category = $categories[(int) $row['documentcategories_id']] ?? '';
            if ($category !== '') {
                $usedCategories[$category] = true;
            }
            $docs[] = [
                'id'          => $id,
                'name'        => (string) ($row['name'] ?: $file ?: 'Documento #' . $id),
                'file'        => $file,
                'icon'        => DocumentsPanel::icon((string) $row['mime'], $file, (string) $row['link']),
                'size'        => $path !== '' && is_file(GLPI_DOC_DIR . '/' . $path) ? DocumentsPanel::size((int) filesize(GLPI_DOC_DIR . '/' . $path)) : '',
                'category'    => $category,
                'category_id' => (int) $row['documentcategories_id'],
                'date'        => !empty($row['date_creation']) ? date('d/m/Y', strtotime((string) $row['date_creation'])) : '',
                'author'      => DocumentsPanel::userName((int) $row['users_id']),
                'download'    => $file !== '' ? $root . '/front/document.send.php?docid=' . $id : (string) $row['link'],
                'linked'      => array_values(array_filter($links[$id] ?? [])),
                'linked_count' => $counts[$id] ?? 0,
            ];
        }

        ksort($usedCategories);
        return [
            'docs'        => $docs,
            'categories'  => $categories,
            'filters'     => array_keys($usedCategories),
            'kpis'        => [
                'total'      => count($docs),
                'categories' => count($categories),
                'unlinked'   => count(array_filter($docs, static fn (array $d): bool => $d['linked_count'] === 0)),
                'month'      => count(array_filter($rows, static fn (array $r): bool => !empty($r['date_creation']) && substr((string) $r['date_creation'], 0, 7) === date('Y-m'))),
            ],
            'can_create'  => Document::canCreate(),
            'can_new_category' => \DocumentCategory::canCreate(),
            'category_list'    => self::categoryList(),
            'can_manage_categories' => \DocumentCategory::canCreate() || \DocumentCategory::canUpdate(),
            'open_categories'  => isset($_GET['categories']),
            'action'      => self::url(),
            'csrf'        => Session::getNewCSRFToken(),
            'max_mb'      => (int) ($CFG_GLPI['document_max_size'] ?? 2),
            'edit_id'     => $editId,
            'open_new'    => $openNew,
            'css_url'     => Shell::assetUrl('css/app.css'),
        ];
    }

    /**
     * Adicionar, editar ou excluir.
     *
     * @throws \InvalidArgumentException
     */
    public static function handle(array $post, array $files): string
    {
        $action = (string) ($post['doc_action'] ?? '');
        if (str_starts_with($action, 'cat_')) {
            return self::categoryAction($action, $post);
        }
        $doc = new Document();
        $id = (int) ($post['documents_id'] ?? 0);

        if ($action === 'delete') {
            if (!$doc->can($id, DELETE)) {
                throw new \InvalidArgumentException('Sem permissão para excluir este documento.');
            }
            $doc->delete(['id' => $id]);
            return 'Documento movido para a lixeira.';
        }

        $name = trim((string) ($post['name'] ?? ''));
        $category = DocumentsPanel::category($post);

        if ($action === 'save') {
            if (!$doc->can($id, UPDATE)) {
                throw new \InvalidArgumentException('Sem permissão para editar este documento.');
            }
            if ($name === '') {
                throw new \InvalidArgumentException('Dê um nome ao documento.');
            }
            $doc->update(['id' => $id, 'name' => $name, 'documentcategories_id' => $category]);
            return 'Documento salvo.';
        }

        if ($action !== 'upload' || !Document::canCreate()) {
            throw new \InvalidArgumentException('Ação inválida.');
        }
        $uploads = DocumentsPanel::normalizeFiles($files['files'] ?? []);
        if ($uploads === []) {
            throw new \InvalidArgumentException('Escolha o arquivo.');
        }
        $infoBefore = count($_SESSION['MESSAGE_AFTER_REDIRECT'][INFO] ?? []);
        $added = 0;
        foreach ($uploads as $upload) {
            // Nome digitado vale quando vai um arquivo so; com varios, cada um leva o nome do arquivo.
            $fields = [
                'documentcategories_id' => $category,
                'entities_id'           => (int) ($_SESSION['glpiactive_entity'] ?? 0),
                'is_recursive'          => 1,
            ];
            if ($name !== '' && count($uploads) === 1) {
                $fields['name'] = $name;
            }
            if (DocumentsPanel::storeUpload($upload, $fields) > 0) {
                $added++;
            }
        }
        DocumentsPanel::dropInfoAfter($infoBefore);
        if ($added === 0) {
            throw new \InvalidArgumentException('Nenhum arquivo foi aceito (confira o tipo do arquivo).');
        }
        return $added === 1 ? 'Documento adicionado.' : $added . ' documentos adicionados.';
    }

    /**
     * Categorias: adicionar, editar (nome e "pertence a") e remover.
     *
     * @throws \InvalidArgumentException
     */
    private static function categoryAction(string $action, array $post): string
    {
        global $DB;
        $category = new \DocumentCategory();
        $id = (int) ($post['category_id'] ?? 0);
        $name = trim((string) ($post['category_name'] ?? ''));
        $parent = max(0, (int) ($post['category_parent'] ?? 0));

        if ($action === 'cat_delete') {
            if (!$category->can($id, PURGE)) {
                throw new \InvalidArgumentException('Sem permissão para remover esta categoria.');
            }
            $parentOfDeleted = (int) $category->fields['documentcategories_id'];
            // Documentos ficam sem categoria; subcategorias sobem um nivel.
            $DB->update('glpi_documents', ['documentcategories_id' => 0], ['documentcategories_id' => $id]);
            foreach ($DB->request(['SELECT' => ['id'], 'FROM' => 'glpi_documentcategories', 'WHERE' => ['documentcategories_id' => $id]]) as $child) {
                (new \DocumentCategory())->update(['id' => (int) $child['id'], 'documentcategories_id' => $parentOfDeleted]);
            }
            $category->delete(['id' => $id], true);
            return 'Categoria removida.';
        }

        if ($name === '') {
            throw new \InvalidArgumentException('Dê um nome à categoria.');
        }
        if ($action === 'cat_add') {
            if (!\DocumentCategory::canCreate()) {
                throw new \InvalidArgumentException('Sem permissão para criar categorias.');
            }
            if ((int) $category->add(['name' => $name, 'documentcategories_id' => $parent]) <= 0) {
                throw new \InvalidArgumentException('Não foi possível criar a categoria.');
            }
            return 'Categoria criada.';
        }
        if ($action === 'cat_save') {
            if (!$category->can($id, UPDATE)) {
                throw new \InvalidArgumentException('Sem permissão para editar esta categoria.');
            }
            // Nao pode pertencer a ela mesma nem a uma filha dela.
            if ($parent === $id || in_array($parent, array_map('intval', array_keys(getSonsOf('glpi_documentcategories', $id))), true)) {
                throw new \InvalidArgumentException('Uma categoria não pode pertencer a ela mesma nem a uma subcategoria dela.');
            }
            $category->update(['id' => $id, 'name' => $name, 'documentcategories_id' => $parent]);
            return 'Categoria salva.';
        }
        throw new \InvalidArgumentException('Ação inválida.');
    }

    /** @return list<array{id: int, name: string, full: string, parent: int, docs: int, depth: int}> */
    private static function categoryList(): array
    {
        global $DB;
        $counts = [];
        foreach ($DB->request([
            'SELECT' => ['documentcategories_id', new \Glpi\DBAL\QueryExpression('COUNT(*) AS total')],
            'FROM'   => 'glpi_documents',
            'WHERE'  => ['is_deleted' => 0],
            'GROUPBY' => 'documentcategories_id',
        ]) as $row) {
            $counts[(int) $row['documentcategories_id']] = (int) $row['total'];
        }
        $list = [];
        foreach ($DB->request(['SELECT' => ['id', 'name', 'completename', 'documentcategories_id', 'level'], 'FROM' => 'glpi_documentcategories', 'ORDER' => 'completename ASC']) as $row) {
            $list[] = [
                'id'     => (int) $row['id'],
                'name'   => (string) $row['name'],
                'full'   => (string) $row['completename'],
                'parent' => (int) $row['documentcategories_id'],
                'docs'   => $counts[(int) $row['id']] ?? 0,
                'depth'  => max(0, (int) $row['level'] - 1),
            ];
        }
        return $list;
    }

    /** @return array{label: string, type: string, url: string}|null */
    private static function linkedItem(string $itemtype, int $id): ?array
    {
        $item = getItemForItemtype($itemtype);
        if (!$item || !$item->getFromDB($id)) {
            return null;
        }
        $url = $item->getLinkURL();
        // Telas novas do Ativa Central quando existem.
        if ($item instanceof \Supplier) {
            $url = SupplierPage::url($id, 'documents');
        } elseif ($item instanceof \Project) {
            $url = ProjectPage::url($id, 'documents');
        }
        return ['label' => (string) $item->getName(), 'type' => (string) $item::getTypeName(1), 'url' => $url];
    }
}
