# Instalador unificado

Sempre que alterar qualquer componente que entra no instalador unificado
(AtivaWallpaperClient.iss, build-unified-installer.ps1, ou os clientes Windows:
Wallpaper, Updater, Guardian, Workspace), aumente em 1 o último número de
`glpi_data/plugins/ativawallpaper/deployment/unified-installer/unified-version.txt`.

Exemplo: 1.9.5 -> 1.9.6.

O arquivo não tem quebra de linha no final (use `printf '1.9.6' > ...`).
Suba a versão uma vez por entrega, no mesmo commit da mudança.
