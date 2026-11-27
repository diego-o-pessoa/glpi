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
    private const VERSION = '1';

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
        ];
    }

    public static function ensure(): void
    {
        global $DB;
        $config = Config::getConfigurationValues(Settings::CONTEXT, [self::VERSION_KEY]);
        $file = GLPI_LOCAL_I18N_DIR . '/core_ativa/pt_BR.php';
        if (($config[self::VERSION_KEY] ?? '') === self::VERSION && is_file($file)) {
            return;
        }

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
        if (!is_file($file) || file_get_contents($file) !== $content) {
            if (!is_dir(dirname($file)) && !@mkdir(dirname($file), 0775, true)) {
                Toolbox::logInFile('ativacentral', "Nao foi possivel criar " . dirname($file) . "\n");
                return;
            }
            file_put_contents($file, $content);
            // As traducoes desta requisicao ja foram carregadas (e voltariam
            // ao cache): limpa de novo na proxima requisicao.
            (new CacheManager())->getTranslationsCacheInstance()->clear();
            return;
        }
        (new CacheManager())->getTranslationsCacheInstance()->clear();
        Config::setConfigurationValues(Settings::CONTEXT, [self::VERSION_KEY => self::VERSION]);
    }
}
