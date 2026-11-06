# CLAUDE.md — GLPI da Ativa Locação

Responda sempre em **português**. Regras complementares (seguir sempre):
`.claude/rules/token-efficiency.md` e `.claude/rules/unified-installer.md`.

## O que é

GLPI 11 (PHP 8.2+, Symfony, Twig) da Ativa Locação com plugins próprios
"Ativa" e clientes Windows em Python. Produção: servidor Linux em `/opt/glpi`
(Docker), atualizado com `git pull origin main`. Cópia Windows do usuário em
`C:\Github\glpi` (onde se gera o instalador unificado).

## Estrutura

- `docker-compose.yml` — `mysql` (8.0.46), `glpi` (diouxx/glpi, porta 8443,
  monta `./glpi_data`), `portal_registro` (`./portal-ativa`), `rustdesk_hbbs`
  e `rustdesk_hbbr` (servidor RustDesk próprio; IP em `RUSTDESK_BIND_IP` no
  `.env`, chaves em `./rustdesk_data`, fora do Git).
- `glpi_data/` — GLPI completo. Código próprio fica em `glpi_data/plugins/ativa*`.
  `glpi_data/src/` = núcleo do GLPI (só consultar quando a tarefa depender dele).
- Não varrer: `glpi_data/vendor/`, `glpi_data/files/`, `glpi_data/marketplace/`,
  `__pycache__/`, `dist/`, `build/`.

## Plugins Ativa (`glpi_data/plugins/`)

| Plugin | Papel |
|---|---|
| ativacentral | Visual novo do GLPI (shell, menu, home); CSS/JS em todas as páginas |
| ativaupdater | Distribui o instalador unificado; serviço Windows `client/unified_updater_service.py` (versão em `UPDATER_VERSION`); limite de downloads em `src/DownloadSlots.php` |
| ativaremote | Acesso remoto (RustDesk): painel, API dos computadores (`/api/v1/report`), celulares (`/api/v1/mobile/*`) e app do T.I. (`/api/v1/tech/*`, `src/Controller/TechController.php`, `src/TechSessions.php`); servidor próprio em `src/RendezvousConfig.php` |
| ativawallpaper | Papel de parede + **instalador unificado** em `deployment/unified-installer/` (Inno Setup `.iss`, `build-unified-installer.ps1`, `unified-version.txt`) e cliente em `client/` |
| ativaguardian | Monitoramento/saúde das máquinas; cliente `client/ativa_guardian_service.py` |
| ativaworkspace | Provisionamento/inventário de estações; scripts em `client/` |
| ativaramal | Ramais/telefonia (API TW Solutions), dashboard e lista de ramais |
| ativarede | Mapa/planta da rede e coleta |
| ativaprint | Impressoras, insumos e volumetria |
| ativasplash | Tela de login |

`glpisaml*`, `painel`, `primeiroacesso` não são plugins Ativa: não mexer sem pedido.

Padrões dos plugins: `setup.php` (versão, hooks, rotas stateless em
`plugin_<nome>_boot`), `hook.php` (install), `src/` (namespace
`GlpiPlugin\<Nome>`), `src/Controller/` (rotas Symfony por atributo),
`front/` (páginas), `templates/` (Twig), `public/` (css/js). Colunas novas às
vezes são criadas "na hora" com marcador em `Config` (ver `MobileDevices::ensureSchema`).

## Repositório relacionado

`diego-o-pessoa/ativa-suporte-android` (fork do RustDesk, branch
`claude/ativa-suporte`): app Android **Ativa Suporte**, **Ativa Instalador**
(`instalador/`) e app Windows **Ativa Remote T.I.** (`flutter/lib/ativa/tech/`).
Builds só no GitHub Actions (`ativa-android.yml`, `ativa-instalador.yml`,
`ativa-windows.yml`); a ponte Rust/Dart não existe localmente.

## Testes (rodar só os da área alterada)

```bash
# Ativa Updater — serviço Windows (~60 s)
cd glpi_data/plugins/ativaupdater && python3 -m unittest tests.test_unified_updater
# Ativa Updater — PHP
php glpi_data/plugins/ativaupdater/tests/php/run.php
# Clientes Python
cd glpi_data/plugins/ativawallpaper/client && python3 -m unittest discover tests
cd glpi_data/plugins/ativaguardian/client && python3 -m unittest discover tests
# PHP: checagem de sintaxe
php -l <arquivo>
```

Para uma classe só: `python3 -m unittest tests.test_unified_updater.RemoteAccessTests`.
No Linux, 9 testes do Ativa Guardian já falham por dependerem do Windows
(`ctypes.WinDLL`, antivírus): não é regressão; compare com o estado antes da mudança.
Testar contra um GLPI real exige banco (não há um pronto no repositório).

## Convenções

- Commits em português, prefixo `Fix:`, `Feat:`, `Add:`, `Update:`; corpo
  explicando o porquê. Mudanças do GLPI vão para `main` (o usuário faz
  `git pull origin main` no servidor).
- Instalador unificado: qualquer mudança em `.iss`, build ou clientes Windows
  sobe 1 no último número de `unified-version.txt` (sem quebra de linha) e o
  usuário roda `git pull` em `C:\Github\glpi` antes do build
  (detalhes em `.claude/rules/unified-installer.md`).
- Mudou a versão do serviço (`UPDATER_VERSION`)? Atualizar também a asserção
  em `tests/test_unified_updater.py` (`VersionTests`).
- Nunca versionar segredos (`.env`, `bootstrap-config.json`, chaves).
- Comentários no código em português sem acento, como no restante do código.

## Como trabalhar (economia de contexto)

1. Não reler arquivos que não mudaram; reaproveitar o que já está no contexto.
2. Localizar com `grep`/busca antes de abrir arquivos; ler só o trecho relevante.
3. Nada de varreduras completas do projeto sem necessidade; começar pelo plugin
   da tarefa e só ir ao núcleo (`glpi_data/src/`) se precisar.
4. Não repetir diagnósticos e verificações já feitos na sessão.
5. Alterações pontuais, preservando o comportamento existente; não refatorar
   o que não foi pedido.
6. Planos e explicações curtos; relatar só o que mudou, decisões e resultado dos testes.
7. Agrupar operações relacionadas (leituras, edições e um único commit por entrega).
8. Rodar apenas os testes que cobrem a alteração (suíte completa só se a mudança for ampla).
9. Manter este arquivo atualizado quando mudar estrutura, comandos ou convenções.
