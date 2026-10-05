<?php

declare(strict_types=1);

use SymfonyComponentHttpKernelExceptionAccessDeniedHttpException;

include '../../../inc/includes.php';

Session::checkLoginUser();

// A atribuicao pode ter sido feita enquanto a pessoa estava conectada.
// Recarregue os perfis antes de validar o alvo; o metodo continua exigindo
// uma atribuicao real em glpi_profiles_users.
$loginUserId = (int) Session::getLoginUserID();
if ($loginUserId > 0) {
    Session::initEntityProfiles($loginUserId);
}

$targetProfileId = PluginAtivaramalProfile::availableGestorProfileId();
if ($targetProfileId === null) {
    throw new AccessDeniedHttpException();
}

// O CheckCsrfListener do GLPI ja validou e consumiu o token enviado pelo
// formulario. O alvo e obtido da sessao, nunca de um id recebido do cliente.
Session::changeProfile($targetProfileId);

global $CFG_GLPI;
Html::redirect($CFG_GLPI['root_doc'] . '/plugins/ativaramal/front/dashboard.php');
