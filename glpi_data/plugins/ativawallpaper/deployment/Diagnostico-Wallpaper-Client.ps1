<#
.SYNOPSIS
    Diagnóstico (somente leitura) do Ativa Wallpaper Client neste computador.

.DESCRIPTION
    Explica por que o computador aparece "Offline" na aba Ativa Wallpaper enquanto o
    Ativa Updater o mostra online. Não altera nada e não lê o token do cliente
    (client.json). O resultado é exibido e salvo em
    Diagnostico-Wallpaper-<computador>-<data>.txt, ao lado deste script.

.EXAMPLE
    powershell -NoProfile -ExecutionPolicy Bypass -File .\Diagnostico-Wallpaper-Client.ps1
#>
$ErrorActionPreference = "Continue"
$root = "C:\ProgramData\AtivaLocacao\Wallpaper"
$scriptDirectory = if ($PSScriptRoot) { $PSScriptRoot } else { (Get-Location).Path }
$output = Join-Path $scriptDirectory ("Diagnostico-Wallpaper-{0}-{1:yyyyMMdd-HHmmss}.txt" -f $env:COMPUTERNAME, (Get-Date))

$report = & {
    "=== Ativa Wallpaper Client - diagnóstico"
    "Computador: $env:COMPUTERNAME"
    "Data local: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss zzz')"
    ""
    "=== Usuários conectados (o cliente só roda em sessão de usuário)"
    $sessions = & quser.exe 2>&1
    if ($LASTEXITCODE -ne 0 -and -not $sessions) { "(nenhum usuário conectado)" } else { $sessions }
    ""
    "=== Processos AtivaWallpaperClient.exe"
    $clients = @(Get-CimInstance Win32_Process -Filter "Name='AtivaWallpaperClient.exe'")
    if ($clients.Count -eq 0) {
        "NENHUM processo em execução -> a aba Ativa Wallpaper mostrará Offline."
    }
    foreach ($process in $clients) {
        $owner = Invoke-CimMethod -InputObject $process -MethodName GetOwner -ErrorAction SilentlyContinue
        "PID {0} | pai {1} | sessão {2} | início {3} | usuário {4}\{5} | {6}" -f $process.ProcessId, $process.ParentProcessId,
            $process.SessionId, $process.CreationDate, $owner.Domain, $owner.User, $process.CommandLine
    }
    ""
    "=== Início automático no logon (HKLM Run)"
    (Get-ItemProperty "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Run" -ErrorAction SilentlyContinue).AtivaWallpaperClient
    ""
    "=== Versão instalada"
    Get-Content (Join-Path $root "version.json") -ErrorAction SilentlyContinue
    Get-Item (Join-Path $root "AtivaWallpaperClient.exe") -ErrorAction SilentlyContinue |
        ForEach-Object { "Executável: $($_.Length) bytes, gravado em $($_.LastWriteTime)" }
    ""
    "=== Serviço Ativa Unified Updater"
    Get-Service AtivaUnifiedUpdater -ErrorAction SilentlyContinue | ForEach-Object { "$($_.Name): $($_.Status)" }
    ""
    "=== Conexão com o servidor (porta 8443)"
    $test = Test-NetConnection chamados.ativalocacao.com.br -Port 8443 -WarningAction SilentlyContinue
    "TcpTestSucceeded: $($test.TcpTestSucceeded) | IP: $($test.RemoteAddress)"
    ""
    $logs = @(Get-ChildItem (Join-Path $root "logs") -Filter "client-*.log" -ErrorAction SilentlyContinue | Sort-Object LastWriteTime -Descending)
    "=== Logs do cliente (um por usuário)"
    $logs | ForEach-Object { "{0} | {1} bytes | última escrita {2}" -f $_.Name, $_.Length, $_.LastWriteTime }
    foreach ($log in ($logs | Select-Object -First 3)) {
        ""
        "--- $($log.Name): início, parada, erros e exceções (últimos 40)"
        Select-String -LiteralPath $log.FullName -Pattern "Client started|Stop requested|ERROR|CRITICAL|INTERNAL_ERROR|Traceback|Exception|ALREADY_RUNNING|INTERACTIVE_SESSION_REQUIRED|HTTP_4|HTTP_5|SERVER_UNAVAILABLE" |
            Select-Object -Last 40 | ForEach-Object { $_.Line }
        ""
        "--- $($log.Name): últimas 25 linhas"
        Get-Content -LiteralPath $log.FullName -Tail 25
    }
}

$report | Tee-Object -FilePath $output
""
"Diagnóstico salvo em: $output"
