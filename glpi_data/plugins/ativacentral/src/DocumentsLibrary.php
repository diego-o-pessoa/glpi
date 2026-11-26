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
