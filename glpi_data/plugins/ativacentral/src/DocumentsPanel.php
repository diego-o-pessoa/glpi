<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativacentral;

use CommonDBTM;
use Document;
use Document_Item;
use Glpi\Application\View\TemplateRenderer;
use Session;

/**
 * Aba "Documentos" no estilo do Ativa Central, em qualquer tela do GLPI
 * (fornecedor, projeto, computador, chamado...). Entra pelos ganchos
 * pre/post_show_tab: mostra o nosso painel e esconde o conteudo nativo
 * (que continua na pagina, escondido, para nao quebrar nada).
 */
final class DocumentsPanel
{
    private static bool $open = false;

    public static function actionUrl(): string
    {
        return Shell::url('/front/documents.action.php');
    }

    /** @param array{item: CommonDBTM, options: array<string, mixed>} $params */
    public static function preTab(array $params): void
    {
        $item = $params['item'] ?? null;
        $options = $params['options'] ?? [];
        if (!self::applies($item, $options)) {
            return;
        }
        try {
            TemplateRenderer::getInstance()->display('@ativacentral/documents_panel.html.twig', self::data($item));
            echo '<div class="ac-docs-native" hidden>';
            self::$open = true;
        } catch (\Throwable $exception) {
            \Toolbox::logInFile('ativacentral', 'Falha no painel de documentos: ' . $exception->getMessage() . "\n");
        }
    }

    /** @param array{item: CommonDBTM, options: array<string, mixed>} $params */
    public static function postTab(array $params): void
    {
        if (self::$open) {
            echo '</div>';
            self::$open = false;
        }
    }

    private static function applies(mixed $item, array $options): bool
    {
        return $item instanceof CommonDBTM
            && ($options['itemtype'] ?? '') === Document_Item::class
            && !$item instanceof Document
            && !$item->isNewItem()
            && Session::getCurrentInterface() === 'central'
            && Shell::active();
    }

    /** @return array<string, mixed> */
    public static function data(CommonDBTM $item): array
    {
        global $DB, $CFG_GLPI;

        $itemtype = $item::class;
        $id = (int) $item->getID();
        $categories = [];
        foreach ($DB->request(['SELECT' => ['id', 'completename'], 'FROM' => 'glpi_documentcategories', 'ORDER' => 'completename ASC']) as $row) {
            $categories[(int) $row['id']] = (string) $row['completename'];
        }

        $docs = [];
        foreach ($DB->request([
            'SELECT'     => [
                'glpi_documents_items.id AS link_id', 'glpi_documents_items.date_creation AS linked_at',
                'glpi_documents.id', 'glpi_documents.name', 'glpi_documents.filename', 'glpi_documents.filepath',
                'glpi_documents.mime', 'glpi_documents.link', 'glpi_documents.documentcategories_id', 'glpi_documents.users_id',
            ],
            'FROM'       => 'glpi_documents_items',
            'INNER JOIN' => ['glpi_documents' => ['ON' => ['glpi_documents_items' => 'documents_id', 'glpi_documents' => 'id']]],
            'WHERE'      => ['glpi_documents_items.itemtype' => $itemtype, 'glpi_documents_items.items_id' => $id, 'glpi_documents.is_deleted' => 0],
            'ORDER'      => 'glpi_documents_items.id DESC',
        ]) as $row) {
            $file = (string) $row['filename'];
            $size = '';
            if ((string) $row['filepath'] !== '' && is_file(GLPI_DOC_DIR . '/' . $row['filepath'])) {
                $size = self::size((int) filesize(GLPI_DOC_DIR . '/' . $row['filepath']));
            }
            $docs[] = [
                'id'       => (int) $row['id'],
                'link_id'  => (int) $row['link_id'],
                'name'     => (string) ($row['name'] ?: $file ?: 'Documento #' . $row['id']),
                'file'     => $file,
                'size'     => $size,
                'icon'     => self::icon((string) $row['mime'], $file, (string) $row['link']),
                'category' => $categories[(int) $row['documentcategories_id']] ?? '',
                'date'     => !empty($row['linked_at']) ? date('d/m/Y', strtotime((string) $row['linked_at'])) : '',
                'author'   => self::userName((int) $row['users_id']),
                'download' => $file !== ''
                    ? rtrim((string) $CFG_GLPI['root_doc'], '/') . '/front/document.send.php?' . http_build_query(['docid' => (int) $row['id'], 'itemtype' => $itemtype, 'items_id' => $id])
                    : (string) $row['link'],
                'open'     => rtrim((string) $CFG_GLPI['root_doc'], '/') . '/front/document.form.php?id=' . (int) $row['id'],
            ];
        }

        $canEdit = Document::canCreate() && $item->can($id, UPDATE);
        return [
            'docs'       => $docs,
            'itemtype'   => $itemtype,
            'items_id'   => $id,
            'can_edit'   => $canEdit,
            'categories' => $categories,
            'action'     => self::actionUrl(),
            'csrf'       => Session::getNewCSRFToken(),
            'max_mb'     => (int) ($CFG_GLPI['document_max_size'] ?? 2),
            'existing_html' => $canEdit ? Document::dropdown([
                'name' => 'documents_id', 'display' => false, 'width' => '100%', 'entity' => $item->getEntityID(), 'used' => array_column($docs, 'id'),
            ]) : '',
            'uid'        => mt_rand(),
            'css_url'    => Shell::assetUrl('css/app.css'),
            'can_new_category' => \DocumentCategory::canCreate(),
        ];
    }

    /**
     * Enviar arquivos, vincular existente ou desvincular.
     *
     * @throws \InvalidArgumentException
     */
    public static function handle(array $post, array $files): string
    {
        global $CFG_GLPI;

        $itemtype = (string) ($post['itemtype'] ?? '');
        $id = (int) ($post['items_id'] ?? 0);
        if (!is_a($itemtype, CommonDBTM::class, true) || is_a($itemtype, Document::class, true)) {
            throw new \InvalidArgumentException('Item inválido.');
        }
        $item = new $itemtype();
        if ($id <= 0 || !$item->getFromDB($id) || !$item->can($id, UPDATE) || !Document::canCreate()) {
            throw new \InvalidArgumentException('Sem permissão para alterar os documentos deste item.');
        }
        $action = (string) ($post['doc_action'] ?? '');

        if ($action === 'unlink') {
            $link = new Document_Item();
            $linkId = (int) ($post['link_id'] ?? 0);
            if (!$link->getFromDB($linkId) || (string) $link->fields['itemtype'] !== $itemtype || (int) $link->fields['items_id'] !== $id) {
                throw new \InvalidArgumentException('Documento não encontrado neste item.');
            }
            $link->delete(['id' => $linkId], true);
            return 'Documento removido deste item (o arquivo continua no GLPI).';
        }

        if ($action === 'link') {
            $docId = (int) ($post['documents_id'] ?? 0);
            if ($docId <= 0) {
                throw new \InvalidArgumentException('Escolha um documento.');
            }
            if (countElementsInTable('glpi_documents_items', ['documents_id' => $docId, 'itemtype' => $itemtype, 'items_id' => $id]) === 0) {
                (new Document_Item())->add(['documents_id' => $docId, 'itemtype' => $itemtype, 'items_id' => $id]);
            }
            return 'Documento vinculado.';
        }

        if ($action !== 'upload') {
            throw new \InvalidArgumentException('Ação inválida.');
        }
        $uploads = self::normalizeFiles($files['files'] ?? []);
        if ($uploads === []) {
            throw new \InvalidArgumentException('Escolha pelo menos um arquivo.');
        }
        $category = self::category($post);
        $added = 0;
        // O GLPI avisa "Criar uma pasta PDF/xx", "Documento copiado..." a cada
        // arquivo: guarda o que ja havia e tira esses avisos tecnicos no fim.
        $infoBefore = count($_SESSION['MESSAGE_AFTER_REDIRECT'][INFO] ?? []);
        foreach ($uploads as $upload) {
            $docId = self::storeUpload($upload, [
                'documentcategories_id' => $category,
                'entities_id'           => $item->getEntityID(),
                'is_recursive'          => $item->isRecursive() ? 1 : 0,
                'itemtype'              => $itemtype,
                'items_id'              => $id,
            ]);
            if ($docId > 0) {
                $added++;
            }
        }
        self::dropInfoAfter($infoBefore);
        if ($added === 0) {
            throw new \InvalidArgumentException('Nenhum arquivo foi aceito (confira o tipo do arquivo).');
        }
        return $added === 1 ? 'Documento adicionado.' : $added . ' documentos adicionados.';
    }

    /**
     * Grava um arquivo enviado como documento do GLPI (o GLPI pega o arquivo
     * na pasta temporaria: prefixo + nome).
     *
     * @param array{name: string, tmp_name: string, error: int, size: int} $upload
     * @throws \InvalidArgumentException
     */
    public static function storeUpload(array $upload, array $fields): int
    {
        global $CFG_GLPI;
        $maxBytes = (int) ($CFG_GLPI['document_max_size'] ?? 2) * 1024 * 1024;
        if ((int) $upload['error'] !== UPLOAD_ERR_OK || !is_uploaded_file((string) $upload['tmp_name'])) {
            throw new \InvalidArgumentException('Falha ao enviar "' . $upload['name'] . '".');
        }
        if ((int) $upload['size'] > $maxBytes) {
            throw new \InvalidArgumentException('"' . $upload['name'] . '" passa do limite de ' . ($maxBytes / 1024 / 1024) . ' MB.');
        }
        $name = basename(str_replace(['/', '\\'], '_', (string) $upload['name']));
        $prefix = uniqid('acdoc', true) . '_';
        if (!move_uploaded_file((string) $upload['tmp_name'], GLPI_TMP_DIR . '/' . $prefix . $name)) {
            throw new \InvalidArgumentException('Não foi possível receber "' . $name . '".');
        }
        $fields += ['name' => $name];
        if (trim((string) $fields['name']) === '') {
            $fields['name'] = $name;
        }
        return (int) (new Document())->add($fields + [
            '_filename'        => [$prefix . $name],
            '_prefix_filename' => [$prefix],
        ]);
    }

    /** Tira os avisos tecnicos do GLPI ("Criar uma pasta PDF/xx"...) gerados depois de $infoBefore. */
    public static function dropInfoAfter(int $infoBefore): void
    {
        if (isset($_SESSION['MESSAGE_AFTER_REDIRECT'][INFO]) && is_array($_SESSION['MESSAGE_AFTER_REDIRECT'][INFO])) {
            $_SESSION['MESSAGE_AFTER_REDIRECT'][INFO] = array_slice($_SESSION['MESSAGE_AFTER_REDIRECT'][INFO], 0, $infoBefore);
            if ($_SESSION['MESSAGE_AFTER_REDIRECT'][INFO] === []) {
                unset($_SESSION['MESSAGE_AFTER_REDIRECT'][INFO]);
            }
        }
    }

    /**
     * Categoria escolhida ou criada na hora ("+ Nova categoria"); se ja
     * existir uma com o mesmo nome, usa ela.
     *
     * @throws \InvalidArgumentException
     */
    public static function category(array $post): int
    {
        global $DB;
        if ((string) ($post['documentcategories_id'] ?? '') !== 'new') {
            return max(0, (int) ($post['documentcategories_id'] ?? 0));
        }
        $name = trim((string) ($post['new_category'] ?? ''));
        if ($name === '') {
            throw new \InvalidArgumentException('Digite o nome da nova categoria.');
        }
        $row = $DB->request(['SELECT' => ['id'], 'FROM' => 'glpi_documentcategories', 'WHERE' => ['name' => $name, 'documentcategories_id' => 0], 'LIMIT' => 1])->current();
        if ($row !== null) {
            return (int) $row['id'];
        }
        if (!\DocumentCategory::canCreate()) {
            throw new \InvalidArgumentException('Sem permissão para criar categorias.');
        }
        $id = (int) (new \DocumentCategory())->add(['name' => $name, 'documentcategories_id' => 0]);
        if ($id <= 0) {
            throw new \InvalidArgumentException('Não foi possível criar a categoria.');
        }
        return $id;
    }

    /** @return list<array{name: string, tmp_name: string, error: int, size: int}> */
    public static function normalizeFiles(array $files): array
    {
        if (!isset($files['name'])) {
            return [];
        }
        $out = [];
        foreach ((array) $files['name'] as $i => $name) {
            if ((string) $name === '') {
                continue;
            }
            $out[] = [
                'name'     => (string) $name,
                'tmp_name' => (string) ((array) $files['tmp_name'])[$i],
                'error'    => (int) ((array) $files['error'])[$i],
                'size'     => (int) ((array) $files['size'])[$i],
            ];
        }
        return $out;
    }

    public static function icon(string $mime, string $file, string $link): string
    {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        return match (true) {
            $file === '' && $link !== '' => 'ti-link',
            str_starts_with($mime, 'image/') => 'ti-photo',
            $ext === 'pdf' => 'ti-file-type-pdf',
            in_array($ext, ['doc', 'docx', 'odt', 'rtf'], true) => 'ti-file-type-doc',
            in_array($ext, ['xls', 'xlsx', 'ods', 'csv'], true) => 'ti-file-spreadsheet',
            in_array($ext, ['zip', 'rar', '7z', 'gz'], true) => 'ti-file-zip',
            default => 'ti-file',
        };
    }

    public static function size(int $bytes): string
    {
        return $bytes >= 1048576 ? number_format($bytes / 1048576, 1, ',', '.') . ' MB' : max(1, (int) round($bytes / 1024)) . ' KB';
    }

    /** "Nome Sobrenome" (sem " - Ativa Locacao"). */
    public static function userName(int $id): string
    {
        $user = new \User();
        if ($id <= 0 || !$user->getFromDB($id)) {
            return '';
        }
        $name = trim((string) $user->fields['firstname'] . ' ' . (string) $user->fields['realname']) ?: (string) $user->fields['name'];
        return trim((string) preg_replace('/\s+-\s+.*$/u', '', $name)) ?: $name;
    }
}
