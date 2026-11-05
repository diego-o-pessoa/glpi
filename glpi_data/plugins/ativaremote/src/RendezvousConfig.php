<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaremote;

use Config;

/**
 * Servidor RustDesk proprio da Ativa (hbbs/hbbr na VPN), configurado em
 * Ativa Remote > Configuracoes. Vai para os computadores (pelo servico do
 * Ativa Updater) e para o app Ativa Remote T.I. Vazio = servidor publico do
 * RustDesk (como antes). Os celulares continuam no publico.
 */
final class RendezvousConfig
{
    private const HOST_KEY = 'rendezvous_host';
    private const PUBLIC_KEY = 'rendezvous_key';

    /** Endereco (nome ou IP, com porta opcional) sem espacos. */
    private const HOST_PATTERN = '/^[A-Za-z0-9.-]{1,253}(:\d{1,5})?$/D';
    /** Chave publica do hbbs (id_ed25519.pub, base64 de 32 bytes). */
    private const KEY_PATTERN = '/^[A-Za-z0-9+\/]{43}=$/D';

    /** @return array{host: string, key: string}|null */
    public static function forClients(): ?array
    {
        $config = Config::getConfigurationValues(Settings::CONTEXT, [self::HOST_KEY, self::PUBLIC_KEY]);
        $host = trim((string) ($config[self::HOST_KEY] ?? ''));
        $key = trim((string) ($config[self::PUBLIC_KEY] ?? ''));
        if ($host === '' || $key === '') {
            return null;
        }
        return ['host' => $host, 'key' => $key];
    }

    /** @return array{host: string, key: string} */
    public static function current(): array
    {
        $config = Config::getConfigurationValues(Settings::CONTEXT, [self::HOST_KEY, self::PUBLIC_KEY]);
        return ['host' => (string) ($config[self::HOST_KEY] ?? ''), 'key' => (string) ($config[self::PUBLIC_KEY] ?? '')];
    }

    /** @throws \InvalidArgumentException */
    public static function save(string $host, string $key): void
    {
        $host = trim($host);
        $key = trim($key);
        if ($host !== '' && !preg_match(self::HOST_PATTERN, $host)) {
            throw new \InvalidArgumentException('Endereço do servidor inválido (ex.: 10.0.0.5 ou rustdesk.ativa.local).');
        }
        if ($key !== '' && !preg_match(self::KEY_PATTERN, $key)) {
            throw new \InvalidArgumentException('Chave inválida: copie o conteúdo do arquivo id_ed25519.pub do servidor.');
        }
        if (($host === '') !== ($key === '')) {
            throw new \InvalidArgumentException('Preencha o endereço e a chave juntos (ou deixe os dois vazios para usar o servidor público).');
        }
        Config::setConfigurationValues(Settings::CONTEXT, [self::HOST_KEY => $host, self::PUBLIC_KEY => $key]);
    }
}
