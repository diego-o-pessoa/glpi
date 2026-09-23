<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace;

use CommonDBTM;
use Html;
use RuntimeException;
use Session;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * POST de criar/editar/ativar-desativar/excluir/duplicar dos cadastros (perfis e
 * aplicativos). O CSRF ja foi validado pelo GLPI 11 (CheckCsrfListener) antes
 * de chegar aqui; nao validar de novo, o token e de uso unico.
 */
final class FormHandler
{
    /**
     * @param list<string> $fields campos aceitos do formulario (lista branca)
     * @param array{
     *     prepare?: callable(array<string, mixed>): array<string, mixed>,
     *     rollback?: callable(array<string, mixed>): void,
     *     form_page?: string,
     *     duplicate?: callable(int): int,
     * } $options
     *   prepare:   roda DEPOIS da permissao validada (ex.: gravar o upload);
     *              pode lancar RuntimeException para voltar ao formulario.
     *   rollback:  desfaz o prepare se o banco recusar (ex.: apagar o upload).
     *   form_page: pagina do formulario; criar leva ao item novo (ex.: para
     *              adicionar as etapas do perfil logo em seguida).
     *   duplicate: duplica o item (recebe o id, devolve o id da copia).
     */
    public static function handlePost(CommonDBTM $item, array $fields, string $listPage, array $options = []): never
    {
        // Nas listagens os botoes carregam o id no proprio valor
        // (name="toggle" value="ID"): um formulario e um token CSRF por pagina.
        $id = (int) ($_POST['id'] ?? 0);
        if ($id === 0) {
            $id = (int) ($_POST['toggle'] ?? $_POST['purge'] ?? $_POST['duplicate'] ?? 0);
        }
        $input = array_intersect_key($_POST, array_flip($fields));
        $formPage = $options['form_page'] ?? null;
        $back = static fn (int $itemId): string => $formPage !== null
            ? Page::href($formPage, $itemId > 0 ? ['id' => $itemId] : [])
            : Page::href($listPage);

        if (isset($_POST['add'])) {
            self::authorize($item, -1, CREATE, $input);
            $input = self::prepare($options, $input, $back(0));
            $newId = (int) $item->add($input);
            if ($newId <= 0) {
                self::rollback($options, $input);
                Html::redirect($back(0));
            }
            Html::redirect($formPage !== null ? $back($newId) : Page::href($listPage));
        }

        if (isset($_POST['update'])) {
            self::authorize($item, $id, UPDATE);
            $input = self::prepare($options, $input, $back($id));
            if (!$item->update(['id' => $id] + $input)) {
                self::rollback($options, $input);
            }
            Html::redirect($back($id));
        }

        if (isset($_POST['toggle'])) {
            self::authorize($item, $id, UPDATE);
            $item->update(['id' => $id, 'is_active' => (int) $item->fields['is_active'] === 1 ? 0 : 1]);
        } elseif (isset($_POST['purge'])) {
            self::authorize($item, $id, PURGE);
            $item->delete(['id' => $id], true);
        } elseif (isset($_POST['duplicate']) && isset($options['duplicate'])) {
            // Duplicar = ler a origem + criar um item novo.
            self::authorize($item, $id, READ);
            self::authorize($item, -1, CREATE, ['entities_id' => (int) ($item->fields['entities_id'] ?? 0)]);
            try {
                $copyId = $options['duplicate']($id);
                Session::addMessageAfterRedirect('Perfil duplicado. A cópia está inativa até ser revisada.', false, INFO);
                Html::redirect($back($copyId));
            } catch (RuntimeException $exception) {
                Session::addMessageAfterRedirect($exception->getMessage(), false, ERROR);
            }
        }

        Html::redirect(Page::href($listPage));
    }

    /**
     * @param array<string, mixed> $options
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    private static function prepare(array $options, array $input, string $backUrl): array
    {
        if (!isset($options['prepare'])) {
            return $input;
        }
        try {
            return $options['prepare']($input);
        } catch (RuntimeException $exception) {
            Session::addMessageAfterRedirect($exception->getMessage(), false, ERROR);
            Html::redirect($backUrl);
        }
    }

    /**
     * @param array<string, mixed> $options
     * @param array<string, mixed> $input
     */
    private static function rollback(array $options, array $input): void
    {
        if (isset($options['rollback'])) {
            $options['rollback']($input);
        }
    }

    /**
     * Permissao no backend: direito do perfil + entidade do item (o can() do
     * GLPI carrega o registro e confere a entidade). Negado: evento SECURITY.
     *
     * @param array<string, mixed> $input
     */
    public static function authorize(CommonDBTM $item, int $id, int $right, array $input = []): void
    {
        if ($item->can($id, $right, $input)) {
            return;
        }

        Event::log(Event::LEVEL_SECURITY, 'permission', 'Ação negada em ' . $item::getTypeName(1), [
            'itemtype' => $item::class,
            'id'       => $id,
            'right'    => $right,
            'user'     => Session::getLoginUserID(),
        ]);
        throw new AccessDeniedHttpException();
    }
}
