<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaworkspace;

use GlpiPlugin\Ativaremote\ClientRepository;
use GlpiPlugin\Ativaremote\ProtectionPolicy;
use GlpiPlugin\Ativaremote\TiPasswordGate;
use Plugin;
use RuntimeException;
use Session;

/**
 * Ponte com o Ativa Remote. Usa as classes do proprio Ativa Remote (nada dele
 * e alterado); as regras dele valem como estao:
 *  - PROTEGIDO      -> exige a senha do T.I.;
 *  - pede autorizacao -> o usuario da maquina aceita;
 *  - nenhum dos dois -> a maquina libera sozinha.
 */
final class RemoteBridge
{
    public static function available(): bool
    {
        return Plugin::isPluginActive('ativaremote') && class_exists(ClientRepository::class);
    }

    public static function canConnect(): bool
    {
        return (bool) Session::haveRight('plugin_ativaremote', UPDATE);
    }

    public static function clientForComputer(int $computersId): ?array
    {
        global $DB;

        if ($computersId <= 0 || !self::available()) {
            return null;
        }
        $row = $DB->request([
            'SELECT' => ['id'],
            'FROM'   => ClientRepository::TABLE,
            'WHERE'  => ['computers_id' => $computersId],
            'ORDER'  => ['updated_at DESC'],
            'LIMIT'  => 1,
        ])->current();

        return is_array($row) ? (new ClientRepository())->findById((int) $row['id']) : null;
    }

    /**
     * Situacao da sessao para a tela do job. Sem a senha da sessao (essa so sai
     * pelo connect.php do Ativa Remote, na hora de conectar).
     *
     * @return array<string, mixed>
     */
    public static function status(int $computersId): array
    {
        if (!self::available()) {
            return ['available' => false, 'reason' => 'O Ativa Remote não está ativo.'];
        }
        $client = self::clientForComputer($computersId);
        if ($client === null) {
            return ['available' => false, 'reason' => 'Este computador ainda não está no Ativa Remote.'];
        }

        return [
            'available'       => true,
            'client_id'       => (int) $client['id'],
            'online'          => ClientRepository::isOnline($client),
            'rustdesk_ready'  => !empty($client['rustdesk_id']),
            'protected'       => ProtectionPolicy::isProtected($client),
            'require_consent' => ClientRepository::requiresConsent($client),
            'status'          => (string) ($client['remote_access_status'] ?? ''),
            'message'         => (string) ($client['status_message'] ?? ''),
            'can_connect'     => self::canConnect(),
        ];
    }

    /**
     * Solicita a sessao, com as mesmas regras do painel do Ativa Remote.
     *
     * @throws RuntimeException codigo 401 quando falta a senha do T.I.
     */
    public static function request(int $computersId, string $tiPassword, int $jobId): array
    {
        if (!self::canConnect()) {
            throw new RuntimeException('Você não tem permissão no Ativa Remote para acessar computadores.', 403);
        }
        $client = self::clientForComputer($computersId);
        if ($client === null) {
            throw new RuntimeException('Este computador ainda não está no Ativa Remote.', 404);
        }

        $protected = ProtectionPolicy::isProtected($client);
        if ($protected && !TiPasswordGate::isVerified($client)) {
            if ($tiPassword === '') {
                throw new RuntimeException('Computador protegido: informe a senha do T.I.', 401);
            }
            TiPasswordGate::check($tiPassword, $client, 'solicitar acesso (Ativa Workspace)');
            TiPasswordGate::markVerified($client);
        }

        try {
            $client = (new ClientRepository())->requestAccess((int) $client['id'], (int) Session::getLoginUserID());
        } catch (\Exception $exception) {
            throw new RuntimeException($exception->getMessage(), 409);
        }

        if ($protected) {
            TiPasswordGate::log('Acesso solicitado pelo Ativa Workspace (senha do T.I. conferida)', $client);
        }
        Event::log(Event::LEVEL_INFO, 'remote', 'Sessão remota solicitada', [
            'protegido'   => $protected,
            'autorizacao' => ClientRepository::requiresConsent($client),
        ], $jobId);

        return self::status($computersId);
    }
}
