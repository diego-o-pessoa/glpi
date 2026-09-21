@echo off
setlocal EnableExtensions
title Recuperar Ativa Updater

REM ============================================================================
REM  Recupera maquinas onde o Windows Defender colocou o Ativa Updater em
REM  quarentena. Roda sem MeshCentral, sem deploy e sem WinRM: basta executar
REM  este arquivo uma vez na maquina. Ele se auto-eleva (pede o UAC sozinho).
REM
REM  Ordem que importa: exclui do Defender ANTES de restaurar/copiar, senao o
REM  arquivo restaurado seria posto em quarentena de novo na mesma hora.
REM
REM  SHARE (opcional): caminho de rede para o AtivaUnifiedUpdater.exe 1.7.3, usado
REM  so quando a quarentena ja foi esvaziada e nao ha o que restaurar. Deixe em
REM  branco para apenas restaurar da quarentena. Ajuste para o seu servidor.
REM ============================================================================
set "SHARE=\\SERVIDOR\ativa\AtivaUnifiedUpdater.exe"
set "DEST=C:\ProgramData\AtivaLocacao\UnifiedUpdater\AtivaUnifiedUpdater.exe"
set "LOG=%ProgramData%\AtivaLocacao\recuperacao.log"

REM --- Auto-elevacao: relanca este mesmo arquivo como administrador ----------
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo Solicitando privilegios de administrador...
    powershell -NoProfile -Command "Start-Process -FilePath '%~f0' -Verb RunAs" >nul 2>&1
    exit /b
)

echo.
echo Recuperando o Ativa Updater nesta maquina...
echo.

REM --- 1) Exclusoes primeiro (idempotente; Add-MpPreference deduplica) -------
powershell -NoProfile -ExecutionPolicy Bypass -Command "try { Add-MpPreference -ExclusionPath 'C:\ProgramData\AtivaLocacao' -ErrorAction Stop; Add-MpPreference -ExclusionProcess 'AtivaUnifiedUpdater.exe','AtivaWallpaperClient.exe' -ErrorAction Stop; 'exclusoes-ok' } catch { 'exclusoes-recusadas: ' + $_.Exception.Message }" >> "%LOG%" 2>&1

REM --- 2) Restaura o que o Defender guardou na quarentena --------------------
powershell -NoProfile -ExecutionPolicy Bypass -Command "try { Restore-MpThreat -ErrorAction Stop; 'restore-ok' } catch { 'restore-nada: ' + $_.Exception.Message }" >> "%LOG%" 2>&1

REM --- 3) Se ainda faltar o executavel, repoe de um share (se configurado) ---
if not exist "%DEST%" (
    if not "%SHARE%"=="" (
        if exist "%SHARE%" (
            if not exist "C:\ProgramData\AtivaLocacao\UnifiedUpdater" mkdir "C:\ProgramData\AtivaLocacao\UnifiedUpdater"
            copy /y "%SHARE%" "%DEST%" >nul 2>&1
            echo copia-do-share >> "%LOG%"
        )
    )
)

REM --- 4) Religa o servico (recria vigia e tarefa agendada sozinho) ----------
sc start AtivaUnifiedUpdater >nul 2>&1

REM --- Resultado ------------------------------------------------------------
if exist "%DEST%" (
    echo.
    echo Pronto. O executavel esta no lugar e o servico foi iniciado.
    echo A maquina deve voltar a aparecer no painel em alguns minutos.
) else (
    echo.
    echo ATENCAO: o executavel ainda nao esta presente.
    echo A quarentena pode ter sido esvaziada e o share nao foi encontrado.
    echo Configure a variavel SHARE no topo deste arquivo e rode de novo.
)
echo.
echo Log: %LOG%
echo.
timeout /t 8 >nul
endlocal
