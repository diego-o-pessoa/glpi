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
 * A lista CANDIDATES NAO vem da documentacao da TW (nao ha documentacao
 * publica): sao caminhos comuns em APIs de PABX, exibidos na tela como
 * "candidatos nao confirmados". O resultado real de cada um e o que vale.
 */
final class ApiDiagnostics
{
    private const TIMEOUT = 12;
    private const MAX_BODY = 262144;      // le no maximo 256 KB
    private const PREVIEW_CHARS = 12000;  // mostra no maximo isto

    /** Chaves cujo valor nunca e exibido. */
    private const SENSITIVE = ['token', 'secret', 'password', 'senha', 'authorization', 'cookie', 'apikey', 'api_key', 'credential'];

    /**
     * Grupos testados por "Testar API da TW", na ordem.
     *
     * @var array<string, array{label: string, paths: list<string>}>
     */
    public const CANDIDATES = [
        'discovery' => [
            'label' => 'Documentação (Swagger do TW Connect)',
            'paths' => ['/api/documentation', '/docs'],
        ],
        'extensions' => [
            'label' => 'Ramais',
            'paths' => ['/api/v1/ramal'],
        ],
        'calls' => [
            'label' => 'Ligações (CDR)',
            'paths' => ['/api/v1/cdr', '/api/v1/cdr/report'],
        ],
        'answered' => [
            'label' => 'Atendidas / não atendidas (teste de filtro, parâmetro não confirmado)',
            'paths' => ['/api/v1/cdr?disposition=ANSWERED', '/api/v1/cdr?disposition=NO%20ANSWER'],
        ],
        'others' => [
            'label' => 'Outros recursos encontrados',
            'paths' => ['/api/v1/did', '/api/v1/usuario', '/api/v1/tronco', '/api/v1/rota', '/api/v1/cliente'],
        ],
    ];

    /**
     * Como a lista acima foi levantada (01/10/2026): GET sem token em
     * https://ativa-locacao-1.twsolutions.com.br com Accept: application/json.
     * Rota existente responde 401 "Unauthenticated."; inexistente, 404.
     * Filas: nenhuma rota encontrada (fila, filas, queue, queues, grupo,
     * callcenter, atendimento... todas 404).
     */
    public const MAPPING_NOTE = 'Rotas confirmadas em 01/10/2026 (respondem 401 sem token = existem). Nenhuma rota de filas foi encontrada (fila, filas, queue, queues, grupo, callcenter… = 404).';

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
        if ($token === '' || $key === '') {
            throw new RuntimeException('Salve o Token e a Key da API (criados no painel da TW) antes de testar.');
        }
        $url = $base . $path;
        $display = $url;
        $headers = [];
        switch ($scheme) {
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
