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

echo Gerando Ativa-GLPI-Agent-Setup-1.4.0.exe para todos os computadores...
powershell.exe -NoLogo -NoProfile -ExecutionPolicy Bypass ^
  -File "%~dp0build-unified-installer.ps1" ^
  -BootstrapConfig "%~dp0bootstrap-config.json" ^
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
echo Instalador criado com sucesso:
echo %~dp0dist\Ativa-GLPI-Agent-Setup-1.4.0.exe
echo.
echo Esse mesmo arquivo pode ser instalado em todos os computadores Windows x64.
echo.
pause
exit /b 0
