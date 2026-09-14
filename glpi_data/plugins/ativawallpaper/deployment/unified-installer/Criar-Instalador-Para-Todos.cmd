@echo off
setlocal
cd /d "%~dp0"

if not exist "%~dp0bootstrap-config.json" (
    echo.
    echo ERRO: bootstrap-config.json nao encontrado.
    echo Baixe o arquivo em Administracao ^> Ativa Wallpaper ^> Configuracoes
    echo e coloque-o nesta mesma pasta.
    echo.
    pause
    exit /b 1
)

if not exist "%~dp0ativaupdater-service-config.json" (
    echo.
    echo ERRO: ativaupdater-service-config.json nao encontrado.
    echo Baixe o arquivo em Ativa Updater ^> Configuracoes
    echo e coloque-o nesta mesma pasta.
    echo.
    pause
    exit /b 1
)

echo Gerando os instaladores Ativa para todos os computadores...
powershell.exe -NoLogo -NoProfile -ExecutionPolicy Bypass ^
  -File "%~dp0build-unified-installer.ps1" ^
  -BootstrapConfig "%~dp0bootstrap-config.json" ^
  -UpdaterConfig "%~dp0ativaupdater-service-config.json" ^
  -OutputDirectory "%~dp0dist" ^
  -AllComputers ^
  -InstallBuildTools

if errorlevel 1 (
    echo.
    echo A criacao do instalador falhou. Revise a mensagem acima.
    echo.
    pause
    exit /b 1
)

echo.
echo Instaladores criados com sucesso em:
echo %~dp0dist
echo.
echo Esse mesmo arquivo pode ser instalado em todos os computadores Windows x64.
echo.
pause
exit /b 0
