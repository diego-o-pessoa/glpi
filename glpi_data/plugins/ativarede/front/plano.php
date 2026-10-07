<?php

declare(strict_types=1);

use GlpiPlugin\Ativarede\Settings;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

include '../../../inc/includes.php';

// Imagem de fundo da planta, com o tipo certo (o GLPI deduz o tipo dos
// arquivos estaticos pelo conteudo, o que nem sempre reconhece SVG).
// So arquivos da pasta public/plans do proprio plugin.
if (!PluginAtivaredeProfile::canView()) {
    throw new AccessDeniedHttpException();
}

global $DB;
$plan = $DB->request(['SELECT' => ['background'], 'FROM' => Settings::TABLE_PLANS, 'WHERE' => ['id' => (int) ($_GET['plan'] ?? 0)], 'LIMIT' => 1])->current();
$name = (string) ($plan['background'] ?? '');
$types = ['svg' => 'image/svg+xml', 'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'webp' => 'image/webp'];
$extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
$file = PLUGIN_ATIVAREDE_DIR . '/public/plans/' . $name;

if (!preg_match('/^[A-Za-z0-9._-]{1,120}$/D', $name) || !isset($types[$extension]) || !is_file($file)) {
    throw new NotFoundHttpException();
}

session_write_close();
header('Content-Type: ' . $types[$extension]);
header('Cache-Control: private, max-age=3600');
header('X-Content-Type-Options: nosniff');
// SVG aberto direto no navegador nao executa nada.
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; sandbox");
readfile($file);
