<?php

declare(strict_types=1);

use GlpiPlugin\Ativaworkspace\FormHandler;
use GlpiPlugin\Ativaworkspace\Page;
use GlpiPlugin\Ativaworkspace\ProfileStep;
use GlpiPlugin\Ativaworkspace\ProvisioningProfile;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

include('../../../inc/includes.php');

Page::requireAccess('profiles');

$item = new ProvisioningProfile();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    FormHandler::handlePost($item, ['name', 'comment', 'entities_id', 'is_recursive', 'is_active'], 'profiles', [
        // Criar leva direto ao perfil novo, para adicionar as etapas.
        'form_page' => 'profile_form',
        'duplicate' => static fn (int $id): int => ProvisioningProfile::duplicate($id),
    ]);
}

$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    if (!$item->getFromDB($id)) {
        throw new NotFoundHttpException();
    }
    // Entidade conferida pelo can(): nao abre perfil de entidade que o usuario nao ve.
    if (!$item->can($id, READ)) {
        throw new AccessDeniedHttpException();
    }
} else {
    PluginAtivaworkspaceProfile::requireRight(PluginAtivaworkspaceProfile::RIGHT_PROFILES, CREATE);
    $item->getEmpty();
    $item->fields['entities_id'] = (int) ($_SESSION['glpiactive_entity'] ?? 0);
    $item->fields['is_active']   = 1;
}

$canEdit = $id === 0 || $item->can($id, UPDATE);

Page::render('profiles', 'profile_form.html.twig', [
    'item'        => $item->fields,
    'is_new'      => $id === 0,
    'can_edit'    => $canEdit,
    'can_create'  => (bool) Session::haveRight(PluginAtivaworkspaceProfile::RIGHT_PROFILES, CREATE),
    'steps'       => $id > 0 ? ProfileStep::forProfile($id) : [],
    'action_url'  => Page::href('profile_form'),
    'list_url'    => Page::href('profiles'),
    'step_url'    => Page::href('step_form'),
]);
