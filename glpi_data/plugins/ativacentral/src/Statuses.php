<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativacentral;

use Config;
use Glpi\Cache\CacheManager;
use Toolbox;

/**
 * Status dos chamados da Ativa:
 * - motivos de pendencia "Aguardando usuario/aprovacao/terceiro";
 * - nomes dos status no Super-Admin via traducao local do GLPI
 *   (GLPI_LOCAL_I18N_DIR/core_ativa/pt_BR.php), sem alterar o nucleo.
 */
final class Statuses
{
    private const VERSION_KEY = 'statuses_version';
    private const VERSION = '2';

    public const REASONS = ['Aguardando usuário', 'Aguardando aprovação', 'Aguardando terceiro'];

    /** @return array<string, string> */
    private static function labels(): array
    {
        return [
            "status\004New"                   => 'Aguardando atendente',
            "status\004Processing (assigned)" => 'Em atendimento',
            "status\004Processing (planned)"  => 'Em execução',
            'Pending'                         => 'Aguardando',
            "status\004Solved"                => 'Finalizado',
            "status\004Closed"                => 'Finalizado (fechado)',
            // Prioridades: so 4 nomes (Baixa, Normal, Alta, Urgente).
            "priority\004Very low"            => 'Baixa',
            "priority\004Low"                 => 'Baixa',
            "priority\004Medium"              => 'Normal',
            "priority\004High"                => 'Alta',
            "priority\004Very high"           => 'Urgente',
            "priority\004Major"               => 'Urgente',
        ];
    }

    /** Tipos de chamado da Ativa: chave => [nome, tipo do GLPI (1 incidente, 2 requisicao)]. */
    public const KINDS = [
        'incident'    => ['Incidente', 1],
        'service'     => ['Serviço', 2],
        'improvement' => ['Melhoria', 2],
        'project'     => ['Projeto', 2],
    ];

    public static function saveKind(int $ticketId, string $kind): void
    {
        global $DB;
        if ($ticketId <= 0 || !isset(self::KINDS[$kind]) || !$DB->tableExists('glpi_plugin_ativacentral_ticketkinds')) {
            return;
        }
        $DB->updateOrInsert('glpi_plugin_ativacentral_ticketkinds', ['kind' => $kind], ['tickets_id' => $ticketId]);
    }

    /** Situacoes da tela de chamados: chave => [nome, cor]. */
    public const SITUATIONS = [
        'waiting_tech'     => ['Aguardando atendente', 'gray'],
        'in_progress'      => ['Em atendimento', 'blue'],
        'running'          => ['Em execução', 'cyan'],
        'waiting_user'     => ['Aguardando usuário', 'orange'],
        'waiting_approval' => ['Aguardando aprovação', 'purple'],
        'waiting_third'    => ['Aguardando terceiro', 'pink'],
        'finished'         => ['Finalizado', 'green'],
    ];

    /** Situacao a partir do status do GLPI e do motivo de pendencia. */
    public static function situation(int $status, string $reason = ''): string
    {
        switch ($status) {
            case 1:
                return 'waiting_tech';
            case 2:
                return 'in_progress';
            case 3:
                return 'running';
            case 4:
                $r = mb_strtolower($reason);
                return str_contains($r, 'aprova') ? 'waiting_approval' : (str_contains($r, 'terceiro') ? 'waiting_third' : 'waiting_user');
            default:
                return 'finished';
        }
    }

    public static function ensure(): void
    {
        global $DB;
        $config = Config::getConfigurationValues(Settings::CONTEXT, [self::VERSION_KEY]);
        $file = GLPI_LOCAL_I18N_DIR . '/core_ativa/pt_BR.php';
        if (($config[self::VERSION_KEY] ?? '') === self::VERSION) {
            return;
        }

        // Tipo do chamado da Ativa (Incidente, Servico, Melhoria, Projeto).
        $DB->doQuery("CREATE TABLE IF NOT EXISTS `glpi_plugin_ativacentral_ticketkinds` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `tickets_id` int unsigned NOT NULL,
            `kind` varchar(20) NOT NULL DEFAULT 'incident',
            PRIMARY KEY (`id`),
            UNIQUE KEY `tickets_id` (`tickets_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        foreach (self::REASONS as $i => $name) {
            $exists = $DB->request(['SELECT' => 'id', 'FROM' => 'glpi_pendingreasons', 'WHERE' => ['name' => $name]])->current();
            if (!$exists) {
                $DB->insert('glpi_pendingreasons', [
                    'name'          => $name,
                    'entities_id'   => 0,
                    'is_recursive'  => 1,
                    'is_default'    => $i === 0 ? 1 : 0,
                ]);
            }
        }

        $content = "<?php\n\nreturn " . var_export(self::labels(), true) . ";\n";
        if (!is_file($file) || @file_get_contents($file) !== $content) {
            $ok = (is_dir(dirname($file)) || @mkdir(dirname($file), 0775, true)) && @file_put_contents($file, $content) !== false;
            if (!$ok) {
                // Sem permissao de escrita: registra e nao tenta de novo a cada pagina
                // (antes isso repetia consultas e limpava o cache de traducoes sempre).
                Toolbox::logInFile('ativacentral', 'Nao foi possivel gravar ' . $file . "\n");
                Config::setConfigurationValues(Settings::CONTEXT, [self::VERSION_KEY => self::VERSION]);
                return;
            }
            // As traducoes desta requisicao ja foram carregadas (e voltariam
            // ao cache): limpa agora e mais uma vez na proxima requisicao.
            (new CacheManager())->getTranslationsCacheInstance()->clear();
            return;
        }
        (new CacheManager())->getTranslationsCacheInstance()->clear();
        Config::setConfigurationValues(Settings::CONTEXT, [self::VERSION_KEY => self::VERSION]);
    }
}
