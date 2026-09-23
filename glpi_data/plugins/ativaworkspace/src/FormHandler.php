<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace;

use CommonDBTM;
use Html;
use Session;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * POST de criar/editar/ativar-desativar/excluir dos cadastros (perfis e
 * aplicativos). O CSRF ja foi validado pelo GLPI 11 (CheckCsrfListener) antes
 * de chegar aqui; nao validar de novo, o token e de uso unico.
 */
final class FormHandler
{
    /**
     * @param list<string> $fields campos aceitos do formulario (lista branca)
     */
    public static function handlePost(CommonDBTM $item, array $fields, string $listPage): never
    {
        // Nas listagens os botoes carregam o id no proprio valor
        // (name="toggle" value="ID"): um formulario e um token CSRF por pagina.
        $id = (int) ($_POST['id'] ?? 0);
        if ($id === 0) {
            $id = (int) ($_POST['toggle'] ?? $_POST['purge'] ?? 0);
        }
        $input = array_intersect_key($_POST, array_flip($fields));

        if (isset($_POST['add'])) {
            self::authorize($item, -1, CREATE, $input);
            $item->add($input);
        } elseif (isset($_POST['update'])) {
            self::authorize($item, $id, UPDATE);
            $item->update(['id' => $id] + $input);
        } elseif (isset($_POST['toggle'])) {
            self::authorize($item, $id, UPDATE);
            $item->update(['id' => $id, 'is_active' => (int) $item->fields['is_active'] === 1 ? 0 : 1]);
        } elseif (isset($_POST['purge'])) {
            self::authorize($item, $id, PURGE);
            $item->delete(['id' => $id], true);
        }

        Html::redirect(Page::href($listPage));
    }

    /**
     * Permissao no backend: direito do perfil + entidade do item (o can() do
     * GLPI carrega o registro e confere a entidade). Negado: evento SECURITY.
     *
     * @param array<string, mixed> $input
     */
    private static function authorize(CommonDBTM $item, int $id, int $right, array $input = []): void
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
