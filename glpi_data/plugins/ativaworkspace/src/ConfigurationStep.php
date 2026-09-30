<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace;

/** Subestados das configuracoes executadas automaticamente na maquina. */
final class ConfigurationStep
{
    public const PRECHECK             = 'CONFIG_PRECHECK';
    public const INSTALLING_CHROME    = 'INSTALLING_CHROME';
    public const APPLYING_POLICY      = 'APPLYING_POLICY';
    public const WAITING_USER_SESSION = 'WAITING_USER_SESSION';
    public const OPENING_OUTLOOK      = 'OPENING_OUTLOOK';
    public const INSTALLING_PWA       = 'INSTALLING_PWA';
    public const SIGNING_IN           = 'SIGNING_IN';
    public const PINNING_TASKBAR      = 'PINNING_TASKBAR';
    public const VERIFYING            = 'VERIFYING_CONFIGURATION';

    public const SUBSTATES = [
        self::PRECHECK,
        self::INSTALLING_CHROME,
        self::APPLYING_POLICY,
        self::WAITING_USER_SESSION,
        self::OPENING_OUTLOOK,
        self::INSTALLING_PWA,
        self::SIGNING_IN,
        self::PINNING_TASKBAR,
        self::VERIFYING,
    ];

    public const MESSAGES = [
        self::PRECHECK             => 'Verificando os pre-requisitos da configuracao...',
        self::INSTALLING_CHROME    => 'Instalando o Google Chrome...',
        self::APPLYING_POLICY      => 'Aplicando as politicas corporativas do Chrome...',
        self::WAITING_USER_SESSION => 'Aguardando uma sessao de usuario ativa...',
        self::OPENING_OUTLOOK      => 'Abrindo o Outlook no Google Chrome...',
        self::INSTALLING_PWA       => 'Instalando o Outlook como aplicativo (PWA)...',
        self::SIGNING_IN           => 'Autenticando no Outlook com o SSO do Microsoft Entra...',
        self::PINNING_TASKBAR      => 'Fixando o Outlook na barra de tarefas...',
        self::VERIFYING            => 'Validando a instalacao do Outlook PWA...',
    ];

    public static function isValidSubstate(string $substate): bool
    {
        return in_array($substate, self::SUBSTATES, true);
    }

    public static function message(string $substate): string
    {
        return self::MESSAGES[$substate] ?? '';
    }
}
