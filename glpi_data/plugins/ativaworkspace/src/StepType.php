<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace;

/**
 * Tipos de etapa de um perfil de provisionamento. Fonte unica: validacao,
 * formulario e telas leem daqui.
 *
 * 'config' lista os campos de configuracao aceitos para o tipo (gravados em
 * JSON em profilesteps.config). Nenhum tipo aceita comando, script ou senha.
 */
final class StepType
{
    public const SOFTWARE            = 'SOFTWARE';
    public const CONFIGURATION       = 'CONFIGURATION';
    public const ENTRA_LOGIN         = 'ENTRA_LOGIN';
    public const MANUAL_INTERVENTION = 'MANUAL_INTERVENTION';
    public const WALLPAPER           = 'WALLPAPER';
    public const INVENTORY           = 'INVENTORY';
    public const CUSTOM              = 'CUSTOM';

    /** Configuracoes predefinidas para CONFIGURATION (o executor sabera aplicar). */
    public const CONFIGURATION_KEYS = [
        'vpn_ativa'   => 'VPN Ativa',
        'openvpn'     => 'Perfil do OpenVPN',
        'outlook_pwa' => 'Outlook PWA',
        'onedrive'    => 'OneDrive',
        'teams'       => 'Microsoft Teams',
        'company'     => 'Configuração específica da empresa',
    ];

    /**
     * @return array<string, array{label: string, icon: string, description: string, application: bool, config: array<string, array{label: string, type: string, required?: bool, choices?: array<string, string>, default?: string, helper?: string}>}>
     */
    public static function all(): array
    {
        return [
            self::SOFTWARE => [
                'label'       => 'Software',
                'icon'        => 'ti ti-package',
                'description' => 'Instala um aplicativo do catálogo.',
                'application' => true,
                'config'      => [],
            ],
            self::CONFIGURATION => [
                'label'       => 'Configuração',
                'icon'        => 'ti ti-adjustments',
                'description' => 'Aplica uma configuração predefinida (VPN, Outlook PWA...).',
                'application' => false,
                'config'      => [
                    'configuration_key' => [
                        'label'    => 'Configuração',
                        'type'     => 'select',
                        'required' => true,
                        'choices'  => self::CONFIGURATION_KEYS,
                    ],
                ],
            ],
            self::ENTRA_LOGIN => [
                'label'       => 'Autenticação Microsoft',
                'icon'        => 'ti ti-brand-windows',
                'description' => 'Pausa para o TI fazer o primeiro login do funcionário no Windows. Nenhuma senha é pedida ou guardada.',
                'application' => false,
                'config'      => [
                    'message' => [
                        'label'   => 'Mensagem para o técnico',
                        'type'    => 'textarea',
                        'default' => 'Realize o primeiro acesso do usuário no Windows.',
                    ],
                ],
            ],
            self::MANUAL_INTERVENTION => [
                'label'       => 'Intervenção manual',
                'icon'        => 'ti ti-hand-stop',
                'description' => 'Pausa até o técnico confirmar que concluiu uma ação.',
                'application' => false,
                'config'      => [
                    'message' => [
                        'label'    => 'Instruções para o técnico',
                        'type'     => 'textarea',
                        'required' => true,
                    ],
                ],
            ],
            self::WALLPAPER => [
                'label'       => 'Wallpaper',
                'icon'        => 'ti ti-photo',
                'description' => 'Aplica o papel de parede da empresa (futuramente pelo Ativa Wallpaper).',
                'application' => false,
                'config'      => [],
            ],
            self::INVENTORY => [
                'label'       => 'Inventário',
                'icon'        => 'ti ti-list-check',
                'description' => 'Força um inventário da máquina no GLPI.',
                'application' => false,
                'config'      => [],
            ],
            self::CUSTOM => [
                'label'       => 'Personalizada',
                'icon'        => 'ti ti-puzzle',
                'description' => 'Etapa descrita em texto, para ações ainda sem tipo próprio. Não executa scripts.',
                'application' => false,
                'config'      => [
                    'message' => [
                        'label'    => 'Descrição da etapa',
                        'type'     => 'textarea',
                        'required' => true,
                    ],
                ],
            ],
        ];
    }

    public static function exists(string $type): bool
    {
        return array_key_exists($type, self::all());
    }

    public static function label(string $type): string
    {
        return self::all()[$type]['label'] ?? $type;
    }

    public static function icon(string $type): string
    {
        return self::all()[$type]['icon'] ?? 'ti ti-point';
    }

    /** @return array<string, string> tipo => rotulo, para selects */
    public static function choices(): array
    {
        $choices = [];
        foreach (self::all() as $type => $meta) {
            $choices[$type] = $meta['label'];
        }
        return $choices;
    }

    /**
     * Valida a configuracao enviada para o tipo e devolve so os campos aceitos.
     *
     * @param array<string, mixed> $raw
     * @return array<string, string>
     * @throws \InvalidArgumentException mensagem pronta para o usuario
     */
    public static function normalizeConfig(string $type, array $raw): array
    {
        $fields = self::all()[$type]['config'] ?? [];
        $config = [];
        foreach ($fields as $key => $field) {
            $value = trim((string) ($raw[$key] ?? ''));
            if ($value === '' && isset($field['default'])) {
                $value = $field['default'];
            }
            if ($value === '' && !empty($field['required'])) {
                throw new \InvalidArgumentException('Preencha "' . $field['label'] . '".');
            }
            if ($field['type'] === 'select' && $value !== '' && !array_key_exists($value, $field['choices'] ?? [])) {
                throw new \InvalidArgumentException('Valor inválido em "' . $field['label'] . '".');
            }
            if (mb_strlen($value) > 2000) {
                throw new \InvalidArgumentException('"' . $field['label'] . '" passa de 2000 caracteres.');
            }
            if ($value !== '') {
                $config[$key] = $value;
            }
        }
        return $config;
    }

    /**
     * Resumo curto da configuracao para as listagens.
     *
     * @param array<string, mixed> $config
     */
    public static function summary(string $type, array $config): string
    {
        if ($type === self::CONFIGURATION) {
            return self::CONFIGURATION_KEYS[$config['configuration_key'] ?? ''] ?? '';
        }
        $message = (string) ($config['message'] ?? '');
        return mb_strlen($message) > 90 ? mb_substr($message, 0, 87) . '…' : $message;
    }
}
