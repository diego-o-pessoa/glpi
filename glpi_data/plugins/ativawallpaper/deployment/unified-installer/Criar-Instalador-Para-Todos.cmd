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

if not exist "%~dp0ativaguardian-service-config.json" (
    echo.
    echo ERRO: ativaguardian-service-config.json nao encontrado.
    echo Baixe o arquivo em Ativa Guardian ^> Configurar ^> Baixar configuracao do servico
    echo e coloque-o nesta mesma pasta.
    echo.
    pause
    exit /b 1
)

echo Gerando o instalador unificado Ativa para todos os computadores...
powershell.exe -NoLogo -NoProfile -ExecutionPolicy Bypass ^
  -File "%~dp0build-unified-installer.ps1" ^
  -BootstrapConfig "%~dp0bootstrap-config.json" ^
  -UpdaterConfig "%~dp0ativaupdater-service-config.json" ^
  -GuardianConfig "%~dp0ativaguardian-service-config.json" ^
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
echo Instalador unificado criado com sucesso em:
echo %~dp0dist
echo.
echo Esse mesmo arquivo pode ser instalado em todos os computadores Windows x64.
echo.
pause
exit /b 0
