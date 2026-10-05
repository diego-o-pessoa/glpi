<?php

declare(strict_types=1);

use SymfonyComponentHttpKernelExceptionAccessDeniedHttpException;

include '../../../inc/includes.php';

Session::checkLoginUser();

$targetProfileId = PluginAtivaramalProfile::availableGestorProfileId();
if ($targetProfileId === null) {
    throw new AccessDeniedHttpException();
}

// O CheckCsrfListener do GLPI ja validou e consumiu o token enviado pelo
// formulario. O alvo e obtido da sessao, nunca de um id recebido do cliente.
Session::changeProfile($targetProfileId);

global $CFG_GLPI;
Html::redirect($CFG_GLPI['root_doc'] . '/plugins/ativaramal/front/dashboard.php');
