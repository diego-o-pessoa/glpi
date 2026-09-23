<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace;

/**
 * Subestados internos da etapa ENTRA_LOGIN e mensagens amigaveis.
 *
 * O executor (servico na maquina) reporta o subestado; o Workspace guarda em
 * jobsteps.runtime (JSON) e mostra a mensagem correspondente. Nenhum dado
 * sensivel entra aqui: so subestado, DeviceId/TenantId observados e um log
 * tecnico curto.
 */
final class EntraStep
{
    public const PRECHECK              = 'PRECHECK';
    public const OPENING_SETTINGS      = 'OPENING_SETTINGS';
    public const OPENING_CONNECT       = 'OPENING_CONNECT';
    public const OPENING_ENTRA_JOIN    = 'OPENING_ENTRA_JOIN';
    public const WAITING_CREDENTIAL_UI = 'WAITING_CREDENTIAL_UI';
    public const WAITING_HUMAN         = 'WAITING_HUMAN';
    public const VERIFYING_JOIN        = 'VERIFYING_JOIN';
    public const SUCCESS               = 'SUCCESS';
    public const FAILED                = 'FAILED';

    public const SUBSTATES = [
        self::PRECHECK, self::OPENING_SETTINGS, self::OPENING_CONNECT, self::OPENING_ENTRA_JOIN,
        self::WAITING_CREDENTIAL_UI, self::WAITING_HUMAN, self::VERIFYING_JOIN, self::SUCCESS, self::FAILED,
    ];

    /** Mensagem mostrada no Workspace para cada subestado. */
    public const MESSAGES = [
        self::PRECHECK              => 'Verificando o estado do dispositivo...',
        self::OPENING_SETTINGS      => 'Abrindo as configurações do Windows...',
        self::OPENING_CONNECT       => 'Preparando "Acessar trabalho ou escola"...',
        self::OPENING_ENTRA_JOIN    => 'Preparando o ingresso no Microsoft Entra ID...',
        self::WAITING_CREDENTIAL_UI => 'Aguardando a tela de login da Microsoft...',
        self::WAITING_HUMAN         => 'Aguardando a autenticação do técnico.',
        self::VERIFYING_JOIN        => 'Verificando o ingresso no Microsoft Entra ID...',
        self::SUCCESS               => 'Microsoft Entra ID configurado com sucesso.',
        self::FAILED                => 'A autenticação Microsoft não foi concluída.',
    ];

    public static function message(string $substate): string
    {
        return self::MESSAGES[$substate] ?? '';
    }

    public static function isValidSubstate(string $substate): bool
    {
        return in_array($substate, self::SUBSTATES, true);
    }

    /**
     * Lê o runtime (JSON) de uma etapa.
     *
     * @return array<string, mixed>
     */
    public static function runtime(?string $raw): array
    {
        $data = json_decode((string) $raw, true);
        return is_array($data) ? $data : [];
    }
}
