<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaramal;

use RuntimeException;
use Throwable;
use Toolbox;

/**
 * Diagnostico da API da TW Solutions: requisicoes GET autenticadas para
 * descobrir quais endpoints e campos existem antes do dashboard.
 *
 * - So GET (nada e alterado na TW) e so no host da URL base configurada:
 *   o caminho e relativo e validado (sem "://", "..", espacos).
 * - Sem seguir redirecionamento: o Location aparece no resultado.
 * - Resposta exibida com campos sensiveis ocultos e tamanho limitado.
 *
 * Endpoints: os de consulta (GET) da documentacao oficial "API V3 - TW
 * CONNECT" (DOCS_URL). Autenticacao: Bearer com o Token do painel da TW.
 */
final class ApiDiagnostics
{
    private const TIMEOUT = 12;
    private const MAX_BODY = 262144;      // le no maximo 256 KB
    private const PREVIEW_CHARS = 12000;  // mostra no maximo isto

    /** Chaves cujo valor nunca e exibido. */
    private const SENSITIVE = ['token', 'secret', 'password', 'pass', 'senha', 'pin', 'authorization', 'cookie', 'apikey', 'api_key', 'credential'];

    /** Documentacao oficial (Postman) enviada pelo suporte da TW. */
    public const DOCS_URL = 'https://documenter.getpostman.com/view/13040224/TzCTa5yB';

    public const MAPPING_NOTE = 'Endpoints da documentação oficial "API V3 - TW CONNECT" (Postman), só os de consulta (GET). '
        . 'Ficam de fora: gerarToken (cria um token novo), click2Call (faz uma ligação) e downloads de áudio.';

    /**
     * Endpoints de consulta da API V3 testados por "Testar API da TW", na ordem.
     * O historico de chamadas usa o dia de hoje (00:00 ate agora).
     *
     * @return array<string, array{label: string, paths: list<string>}>
     */
    public static function candidates(): array
    {
        $period = http_build_query([
            'data_inicial' => date('Y-m-d') . ' 00:00:00',
            'data_final'   => date('Y-m-d H:i:s'),
        ], '', '&', PHP_QUERY_RFC3986);

        return [
            'extensions' => ['label' => 'Ramais', 'paths' => ['/api/v3/ramal/consultarRamal']],
            'queues'     => ['label' => 'Filas', 'paths' => ['/api/v3/fila/consultarFila']],
            'calls'      => ['label' => 'Ligações de hoje (atendidas/não atendidas pelo campo "disposition")', 'paths' => ['/api/v3/chamada/consultarChamada?' . $period]],
            'realtime'   => ['label' => 'Ligações em tempo real', 'paths' => ['/api/v3/chamada/consultarChamadaTempoReal']],
            'others'     => ['label' => 'Outros cadastros', 'paths' => [
                '/api/v3/usuario/consultarUsuario',
                '/api/v3/grupo/consultarGrupo',
                '/api/v3/did/consultarDid',
                '/api/v3/perfil/consultarPerfil',
            ]],
        ];
    }

    /** Caminho relativo seguro (com query opcional). */
    public static function normalizePath(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '';
        }
        if ($path[0] !== '/') {
            $path = '/' . $path;
        }
        if (strlen($path) > 300 || str_contains($path, '://') || str_contains($path, '..')
            || preg_match('/[\s\\\\@#]/', $path) || str_starts_with($path, '//')) {
            throw new RuntimeException('Caminho inválido: use só o caminho relativo à URL base (ex.: /extensions?limit=10).');
        }
        return $path;
    }

    /**
     * Faz um GET autenticado e devolve o resultado para a tela.
     *
     * @return array<string, mixed>
     */
    /**
     * Token de sistema (client credentials) pedido uma vez por requisicao do
     * diagnostico e mantido so em memoria.
     */
    private static ?string $clientToken = null;

    /**
     * Formas de enviar o Token + Key do painel da TW. A TW nao documenta qual
     * usa: o diagnostico testa cada uma e mostra o resultado de todas.
     */
    public const APIKEY_SCHEMES = [
        'bearer'     => '"Authorization: Bearer <token>" (documentação V3)',
        'headers'    => 'Cabeçalhos "token" e "key"',
        'x_headers'  => 'Cabeçalhos "X-Token" e "X-Key"',
        'bearer_key' => '"Authorization: Bearer <token>" + cabeçalho "key"',
        'query'      => 'Parâmetros ?token=…&key=… na URL',
    ];

    /**
     * @param string $mode 'user' (token da conexao OAuth), 'client' (client
     *                     credentials, nao salvo) ou 'apikey' (Token + Key do painel)
     * @return array<string, mixed>
     */
    public static function probe(string $path, string $mode = 'user', string $scheme = ''): array
    {
        $path = self::normalizePath($path);
        $base = RamalConfig::baseUrl();
        if (!RamalConfig::isHttpsUrl($base)) {
            throw new RuntimeException('Configure a URL base da API (HTTPS) antes de testar.');
        }
        if ($mode === 'apikey') {
            return self::probeApiKey($base, $path, $scheme);
        }
        if ($mode === 'client') {
            if (self::$clientToken === null) {
                $response = OAuthClient::clientCredentials();
                self::$clientToken = (string) ($response['access_token'] ?? '');
                Logger::info('Diagnóstico: token client credentials obtido (não salvo)');
            }
            $token = self::$clientToken;
            $type = 'Bearer';
        } else {
            $token = TokenManager::accessToken();
            $type = RamalConfig::get('token_type') ?: 'Bearer';
        }
        if ($token === '') {
            throw new RuntimeException('Sem access token: conecte à TW Solutions antes de testar.');
        }
        $authorization = (strcasecmp($type, 'bearer') === 0 ? 'Bearer' : $type) . ' ' . $token;
        return self::request($base . $path, $base . $path, $path, ['Authorization' => $authorization]);
    }

    /**
     * Token + Key do painel da TW, na forma de envio $scheme (APIKEY_SCHEMES).
     * Na forma "query" a URL exibida/logada tem os valores ocultos.
     *
     * @return array<string, mixed>
     */
    private static function probeApiKey(string $base, string $path, string $scheme): array
    {
        if (!isset(self::APIKEY_SCHEMES[$scheme])) {
            throw new RuntimeException('Forma de envio do Token/Key inválida.');
        }
        $token = RamalConfig::secret('api_token');
        $key = RamalConfig::secret('api_key');
        if ($token === '') {
            throw new RuntimeException('Salve o Token da API (criado no painel da TW, em /gerenciar-tokens) antes de testar.');
        }
        if ($key === '' && $scheme !== 'bearer') {
            throw new RuntimeException('Esta forma de envio usa também a Key: salve a Key da API.');
        }
        $url = $base . $path;
        $display = $url;
        $headers = [];
        switch ($scheme) {
            case 'bearer':
                $headers = ['Authorization' => 'Bearer ' . $token];
                break;
            case 'headers':
                $headers = ['token' => $token, 'key' => $key];
                break;
            case 'x_headers':
                $headers = ['X-Token' => $token, 'X-Key' => $key];
                break;
            case 'bearer_key':
                $headers = ['Authorization' => 'Bearer ' . $token, 'key' => $key];
                break;
            case 'query':
                $glue = str_contains($url, '?') ? '&' : '?';
                $url .= $glue . http_build_query(['token' => $token, 'key' => $key], '', '&', PHP_QUERY_RFC3986);
                $display .= $glue . 'token=[oculto]&key=[oculto]';
                break;
        }
        $result = self::request($url, $display, $path, $headers);
        $result['scheme'] = $scheme;
        $result['scheme_label'] = self::APIKEY_SCHEMES[$scheme];
        return $result;
    }

    /**
     * GET com os cabecalhos de autenticacao dados. $displayUrl e a URL sem
     * segredos (vai para a tela e para o log).
     *
     * @param array<string, string> $authHeaders
     * @return array<string, mixed>
     */
    private static function request(string $url, string $displayUrl, string $path, array $authHeaders): array
    {
        $started = microtime(true);
        try {
            $response = Toolbox::getGuzzleClient([
                'timeout'         => self::TIMEOUT,
                'http_errors'     => false,
                'allow_redirects' => false,
            ])->get($url, [
                'headers' => ['Accept' => 'application/json'] + $authHeaders,
                'stream'  => true,
            ]);
        } catch (Throwable $exception) {
            $reason = self::connectionReason($exception);
            Logger::warning('Diagnóstico: falha de conexão', ['url' => $displayUrl, 'motivo' => $reason]);
            return [
                'url' => $displayUrl, 'path' => $path, 'status' => 0, 'ms' => (int) round((microtime(true) - $started) * 1000),
                'content_type' => '', 'bytes' => 0, 'json' => false, 'fields' => [], 'preview' => '',
                'error' => $reason, 'location' => '', 'headers' => [],
            ];
        }
        $ms = (int) round((microtime(true) - $started) * 1000);

        $stream = $response->getBody();
        $raw = '';
        while (!$stream->eof() && strlen($raw) < self::MAX_BODY) {
            $raw .= $stream->read(8192);
        }
        $truncated = !$stream->eof();

        $status = $response->getStatusCode();
        $contentType = $response->getHeaderLine('Content-Type');
        $decoded = json_decode($raw, true);
        $isJson = is_array($decoded);

        if ($isJson) {
            $clean = self::redact($decoded);
            $preview = (string) json_encode($clean, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $fields = self::fields($decoded);
        } else {
            $preview = self::maskText(mb_substr($raw, 0, self::PREVIEW_CHARS));
            $fields = [];
        }
        if (mb_strlen($preview) > self::PREVIEW_CHARS) {
            $preview = mb_substr($preview, 0, self::PREVIEW_CHARS) . "\n… (cortado)";
        }

        Logger::info('Diagnóstico da API', ['url' => $displayUrl, 'status' => $status, 'ms' => $ms, 'bytes' => strlen($raw)]);

        return [
            'url'          => $displayUrl,
            'path'         => $path,
            'status'       => $status,
            'ms'           => $ms,
            'content_type' => mb_substr($contentType, 0, 80),
            'bytes'        => strlen($raw),
            'truncated'    => $truncated,
            'json'         => $isJson,
            'fields'       => $fields,
            'preview'      => $preview,
            'location'     => self::maskText(mb_substr($response->getHeaderLine('Location'), 0, 300)),
            'headers'      => self::interestingHeaders($response->getHeaders()),
            'error'        => '',
        ];
    }

    /** Motivo legivel da falha de conexao, pelo codigo do cURL (sem URL/cabecalhos). */
    private static function connectionReason(Throwable $exception): string
    {
        $errno = 0;
        if (method_exists($exception, 'getHandlerContext')) {
            $errno = (int) ($exception->getHandlerContext()['errno'] ?? 0);
        }
        if ($errno === 0 && preg_match('/cURL error (\d+)/', $exception->getMessage(), $matches)) {
            $errno = (int) $matches[1];
        }
        return match ($errno) {
            6       => 'O endereço não existe (DNS não encontrou o host). Confira a URL base.',
            7       => 'O servidor recusou a conexão (porta fechada ou bloqueada).',
            28      => 'Tempo esgotado: o servidor não respondeu.',
            35, 60  => 'Erro de TLS/certificado ao conectar.',
            0       => 'Sem resposta (' . (new \ReflectionClass($exception))->getShortName() . ').',
            default => 'Falha de conexão (cURL ' . $errno . ').',
        };
    }

    /** Oculta valores de chaves sensiveis e strings com cara de token. */
    private static function redact(mixed $value, string $key = ''): mixed
    {
        $lower = strtolower($key);
        foreach (self::SENSITIVE as $needle) {
            if ($lower !== '' && str_contains($lower, $needle)) {
                return '[oculto]';
            }
        }
        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                $out[$k] = self::redact($v, (string) $k);
            }
            return $out;
        }
        return is_string($value) ? self::maskText($value) : $value;
    }

    private static function maskText(string $text): string
    {
        $text = preg_replace('/\b(Bearer|Basic)\s+[A-Za-z0-9._~+\/=-]+/i', '$1 [oculto]', $text) ?? '';
        return preg_replace('/\beyJ[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+\.[A-Za-z0-9_-]*/', '[jwt oculto]', $text) ?? '';
    }

    /**
     * Estrutura da resposta para descobrir os campos: chaves do topo e, se
     * houver uma lista de objetos (direto ou em data/items/results...), os
     * campos do primeiro item com o tipo de cada um.
     *
     * @return list<array{path: string, type: string, example: string}>
     */
    private static function fields(array $data): array
    {
        $fields = [];
        $list = null;
        $listPath = '';
        if (array_is_list($data)) {
            $list = $data;
            $listPath = '[]';
        } else {
            foreach ($data as $key => $value) {
                $fields[] = ['path' => (string) $key, 'type' => self::typeOf($value), 'example' => self::example((string) $key, $value)];
                if ($list === null && is_array($value) && array_is_list($value) && isset($value[0]) && is_array($value[0])) {
                    $list = $value;
                    $listPath = $key . '[]';
                }
            }
        }
        if ($list !== null && isset($list[0]) && is_array($list[0])) {
            foreach ($list[0] as $key => $value) {
                $fields[] = ['path' => $listPath . '.' . $key, 'type' => self::typeOf($value), 'example' => self::example((string) $key, $value)];
            }
            $fields[] = ['path' => $listPath, 'type' => 'lista com ' . count($list) . ' item(ns)', 'example' => ''];
        }
        return array_slice($fields, 0, 80);
    }

    private static function typeOf(mixed $value): string
    {
        return match (true) {
            is_array($value) && array_is_list($value) => 'lista',
            is_array($value)                          => 'objeto',
            is_bool($value)                           => 'booleano',
            is_int($value), is_float($value)          => 'número',
            $value === null                           => 'nulo',
            default                                   => 'texto',
        };
    }

    private static function example(string $key, mixed $value): string
    {
        $redacted = self::redact($value, $key);
        if (is_array($redacted)) {
            return '';
        }
        return mb_substr(is_bool($redacted) ? ($redacted ? 'true' : 'false') : (string) $redacted, 0, 60);
    }

    /**
     * Cabecalhos uteis para entender a API (paginacao, limites, versao).
     *
     * @param array<string, list<string>> $headers
     * @return array<string, string>
     */
    private static function interestingHeaders(array $headers): array
    {
        $out = [];
        foreach ($headers as $name => $values) {
            $lower = strtolower($name);
            if (preg_match('/^(x-ratelimit|ratelimit|x-total|x-page|link$|x-api-version|api-version|www-authenticate|allow$|server$)/', $lower)) {
                $out[$name] = self::maskText(mb_substr(implode(', ', $values), 0, 200));
            }
        }
        return $out;
    }
}
