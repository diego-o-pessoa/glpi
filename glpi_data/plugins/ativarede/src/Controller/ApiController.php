<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativarede\Controller;

use Config;
use Glpi\Controller\AbstractController;
use GlpiPlugin\Ativarede\ReportService;
use GlpiPlugin\Ativarede\Schema;
use InvalidArgumentException;
use Plugin;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;
use Toolbox;

/**
 * API de ingestao do Ativa Rede. Quem envia e o servico Ativa Guardian das
 * maquinas, com o MESMO token Bearer da API do Guardian (as maquinas nao
 * precisam de outra credencial). So recebe dados; nao devolve nem executa
 * nada na maquina.
 */
final class ApiController extends AbstractController
{
    /** Posicao + ate 8 monitores cabe com folga. */
    private const MAX_BODY_BYTES = 32768;

    #[Route('/api/v1/health', name: 'ativarede_api_health', methods: ['GET'])]
    public function health(): Response
    {
        return new JsonResponse(['status' => 'ok', 'plugin_version' => PLUGIN_ATIVAREDE_VERSION, 'api_version' => '1']);
    }

    #[Route('/api/v1/report', name: 'ativarede_api_report', methods: ['POST'])]
    public function report(Request $request): Response
    {
        if ($error = $this->checkAuth($request)) {
            return $error;
        }
        if (strlen($request->getContent()) > self::MAX_BODY_BYTES) {
            return $this->error('PAYLOAD_TOO_LARGE', 'Relatorio muito grande.', 413);
        }
        try {
            $payload = json_decode($request->getContent(), true, 16, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $this->error('INVALID_JSON', 'JSON invalido.', 400);
        }
        if (!is_array($payload)) {
            return $this->error('INVALID_PAYLOAD', 'Conteudo invalido.', 400);
        }

        try {
            Schema::upgrade();
        } catch (Throwable $exception) {
            Toolbox::logInFile('ativarede', 'Falha ao atualizar as tabelas: ' . $exception->getMessage() . "\n");
        }

        try {
            $report = ReportService::validate($payload);
        } catch (InvalidArgumentException $exception) {
            // Registrado para descobrir por que uma maquina nao aparece.
            Toolbox::logInFile('ativarede', sprintf(
                "Relatorio recusado de %s (%s): %s\n",
                is_scalar($payload['hostname'] ?? null) ? (string) $payload['hostname'] : '?',
                is_scalar($payload['machine_id'] ?? null) ? (string) $payload['machine_id'] : '?',
                $exception->getMessage()
            ));
            Schema::recordRejection($payload['machine_id'] ?? null, $payload['hostname'] ?? null,
                'Relatório recusado: ' . $exception->getMessage());
            return $this->error('VALIDATION_FAILED', $exception->getMessage(), 422);
        }

        try {
            $result = ReportService::process($report);
        } catch (Throwable $exception) {
            Toolbox::logInFile('ativarede', 'Falha ao processar relatorio de ' . $report['machine_id'] . ': ' . $exception->getMessage() . "\n");
            Schema::recordRejection($report['machine_id'], $report['hostname'],
                'Erro no GLPI ao gravar o relatório: ' . $exception->getMessage());
            return $this->error('DATABASE_ERROR', 'Nao foi possivel registrar o relatorio.', 500);
        }
        try {
            Schema::clearRejection($report['machine_id']);
        } catch (Throwable) {
            // So diagnostico.
        }

        return new JsonResponse(['ok' => true, 'events' => $result['events']], 202);
    }

    /** Mesmo token e mesma chave liga/desliga da API do Ativa Guardian. */
    private function checkAuth(Request $request): ?JsonResponse
    {
        if (!Plugin::isPluginActive('ativaguardian')) {
            return $this->error('GUARDIAN_INACTIVE', 'O Ativa Rede usa o token do Ativa Guardian, que nao esta ativo.', 503);
        }
        $guardian = Config::getConfigurationValues('plugin:ativaguardian', ['api_enabled', 'api_token']);
        if (!in_array($guardian['api_enabled'] ?? '0', [1, '1', true], true)) {
            return $this->error('API_DISABLED', 'A API do Ativa Guardian esta desabilitada.', 503);
        }

        $header = trim((string) $request->headers->get('Authorization', ''));
        if (!preg_match('/^Bearer\s+([^\s]+)$/D', $header, $matches)) {
            return $this->error('UNAUTHORIZED', 'Token Bearer ausente ou invalido.', 401);
        }
        $expected = (string) ($guardian['api_token'] ?? '');
        if ($expected === '' || !hash_equals($expected, $matches[1])) {
            return $this->error('FORBIDDEN', 'Token sem permissao para esta API.', 403);
        }
        return null;
    }

    private function error(string $code, string $message, int $status): JsonResponse
    {
        return new JsonResponse(['error' => ['code' => $code, 'message' => $message]], $status);
    }
}
