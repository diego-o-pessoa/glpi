# Ativa Wallpaper Client 1.0.0

Cliente sem dependencias de runtime para Windows 10/11 x64. O executavel final e
gerado com PyInstaller e nao requer Python no computador gerenciado.

## Build

Em uma estacao Windows x64 com Python 3.11+:

```powershell
Set-ExecutionPolicy -Scope Process Bypass
.\build-client.ps1
```

O resultado fica em `dist\AtivaWallpaperClient.exe`. Guarde o SHA-256 exibido
pelo script junto do pacote GLPI Inventory.

## Diagnostico

```powershell
AtivaWallpaperClient.exe --version
AtivaWallpaperClient.exe --once --debug
```

Logs rotativos ficam em `C:\ProgramData\AtivaLocacao\Wallpaper\logs` (5 arquivos
de 5 MB por usuario). Tokens, segredo de registro e cabecalhos Authorization nao
sao registrados.

O `--install` precisa executar como SYSTEM/administrador; o modo normal executa
sem elevacao na sessao do usuario e altera somente o HKCU daquele usuario.
