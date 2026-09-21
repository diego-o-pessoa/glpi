<#
.SYNOPSIS
    Instala o Ativa Unified Agent em modo silencioso, sem perguntas.

.DESCRIPTION
    Basta executar (duplo clique em Instalar-Ativa-Agent.cmd). O script:
      - pede elevação ao Windows se não estiver como administrador;
      - usa o instalador de maior versão encontrado ao lado do script ou na pasta dist;
      - instala neste computador ou, se existir computadores.txt ao lado do script,
        em cada computador listado (PowerShell Remoting);
      - para o serviço do updater e encerra instaladores travados antes de começar;
      - executa o setup silencioso com tempo limite, confere serviço e versão,
        salva logs em deploy-logs e apaga a cópia do instalador (contém o segredo
        de bootstrap).

    Os parâmetros são opcionais e servem apenas para RMM ou uso avançado.

.EXAMPLE
    .\Deploy-AtivaUnifiedAgent.ps1

.EXAMPLE
    .\Deploy-AtivaUnifiedAgent.ps1 -ComputerName PC01, PC02 -InstallerPath D:\Ativa-Unified-Agent-Setup-1.6.2.exe -NoPause
#>
[CmdletBinding()]
param(
    [string[]]$ComputerName,
    [string]$InstallerPath,
    [ValidateRange(5, 120)]
    [int]$TimeoutMinutes = 20,
    [switch]$NoPause
)

$ErrorActionPreference = "Stop"
$ScriptDirectory = if ($PSScriptRoot) { $PSScriptRoot } else { Split-Path -Parent $MyInvocation.MyCommand.Path }
$ReportDirectory = Join-Path $ScriptDirectory "deploy-logs"
$RemoteDirectory = "C:\ProgramData\AtivaLocacao\Deploy"

function Wait-BeforeClose {
    if ($NoPause) { return }
    Write-Host ""
    Write-Host "Esta janela fecha sozinha em 30 segundos."
    Start-Sleep -Seconds 30
}

# --- Elevação ---------------------------------------------------------------
$principal = New-Object Security.Principal.WindowsPrincipal([Security.Principal.WindowsIdentity]::GetCurrent())
if (-not $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    $relaunch = @("-NoProfile", "-ExecutionPolicy", "Bypass", "-File", "`"$PSCommandPath`"", "-TimeoutMinutes", $TimeoutMinutes)
    if ($ComputerName) { $relaunch += @("-ComputerName", (($ComputerName | ForEach-Object { "`"$_`"" }) -join ",")) }
    if ($InstallerPath) { $relaunch += @("-InstallerPath", "`"$((Resolve-Path -LiteralPath $InstallerPath).Path)`"") }
    if ($NoPause) { $relaunch += "-NoPause" }
    try {
        Start-Process -FilePath "powershell.exe" -ArgumentList $relaunch -Verb RunAs | Out-Null
    }
    catch {
        Write-Host "É preciso permitir a execução como administrador para instalar." -ForegroundColor Red
        Wait-BeforeClose
        exit 1
    }
    exit 0
}

New-Item -ItemType Directory -Force -Path $ReportDirectory | Out-Null
$transcript = Join-Path $ReportDirectory ("execucao-{0:yyyyMMdd-HHmmss}.txt" -f (Get-Date))
Start-Transcript -LiteralPath $transcript | Out-Null

# --- Exclusões do Defender (antes de copiar qualquer executável) ------------
# O Defender marca o updater por heurística de comportamento (serviço + tarefa
# agendada + auto-substituição do executável, tudo sem assinatura digital) e
# chega a removê-lo. Isso atinge também este setup enquanto ele ainda está na
# pasta de deploy, antes de conseguir rodar — por isso a exclusão vem antes do
# Copy-Item, e não dentro do instalador. Falhar aqui não interrompe o deploy: a
# máquina pode ter outro antivírus ou o Defender desativado por política.
$ExclusionBlock = {
    try {
        Add-MpPreference -ExclusionPath "C:\ProgramData\AtivaLocacao" -ErrorAction Stop
        Add-MpPreference -ExclusionProcess "AtivaUnifiedUpdater.exe", "AtivaWallpaperClient.exe" -ErrorAction Stop
        Write-Output "Exclusoes do Windows Defender registradas."
    }
    catch {
        Write-Output "Nao foi possivel registrar exclusoes no Defender: $($_.Exception.Message)"
    }
}

# --- Instalação em um computador (roda localmente ou via Invoke-Command) ----
$InstallBlock = {
    param($RemoteDirectory, $SetupPath, $ExpectedHash, $ExpectedVersion, $TimeoutMinutes)

    $ErrorActionPreference = "Stop"
    $serviceName = "AtivaUnifiedUpdater"
    $updaterExe = "C:\ProgramData\AtivaLocacao\UnifiedUpdater\AtivaUnifiedUpdater.exe"
    $log = Join-Path $RemoteDirectory ("install-{0:yyyyMMdd-HHmmss}.log" -f (Get-Date))
    $result = [ordered]@{
        Computer         = $env:COMPUTERNAME
        Status           = "Falha"
        ExitCode         = $null
        InstalledVersion = ""
        Service          = ""
        Message          = ""
        LogText          = ""
    }

    function Stop-Tree([int]$ProcessId) {
        # taskkill encerra também processos do SYSTEM; erros (processo já saiu) são ignorados.
        $ErrorActionPreference = "Continue"
        $null = & taskkill.exe /PID $ProcessId /T /F 2>&1
    }

    $exitMessages = @{
        1 = "o setup não conseguiu inicializar"
        2 = "cancelado antes de começar a instalação"
        3 = "erro fatal na preparação da instalação"
        4 = "erro fatal durante a instalação"
        5 = "instalação cancelada ou abortada"
        6 = "o setup foi encerrado à força"
        7 = "a etapa de preparação falhou"
        8 = "a etapa de preparação falhou e o Windows precisa reiniciar"
    }

    try {
        if ((Get-FileHash -LiteralPath $SetupPath -Algorithm SHA256).Hash -ne $ExpectedHash) {
            throw "SHA-256 do instalador copiado não confere; a cópia pode estar corrompida."
        }

        # 1. Para o serviço, para ele não iniciar outro instalador ao mesmo tempo.
        $service = Get-Service -Name $serviceName -ErrorAction SilentlyContinue
        if ($service -and $service.Status -ne "Stopped") {
            Write-Output "Parando o serviço $serviceName..."
            $ErrorActionPreference = "Continue"
            $null = & sc.exe stop $serviceName 2>&1
            $ErrorActionPreference = "Stop"
            try { $service.WaitForStatus("Stopped", [TimeSpan]::FromSeconds(60)) } catch { }
        }

        # 2. Encerra instaladores travados e processos do updater que sobraram (ex.: --configure preso).
        $leftovers = @(Get-CimInstance Win32_Process | Where-Object {
            $_.Name -like "Ativa-Unified-Agent-Setup*" -or $_.ExecutablePath -eq $updaterExe
        })
        foreach ($process in $leftovers) {
            Write-Output "Encerrando processo travado: $($process.Name) (PID $($process.ProcessId))"
            Stop-Tree $process.ProcessId
        }
        if ($leftovers.Count -gt 0) { Start-Sleep -Seconds 3 }

        # 3. Instalação silenciosa.
        Write-Output "Executando o instalador $ExpectedVersion (limite de $TimeoutMinutes min)..."
        $arguments = "/VERYSILENT /SUPPRESSMSGBOXES /NORESTART /NOCLOSEAPPLICATIONS /SP- /LOG=`"$log`""
        $setup = Start-Process -FilePath $SetupPath -ArgumentList $arguments -PassThru -WindowStyle Hidden
        $null = $setup.Handle  # Sem isso, ExitCode pode voltar vazio.

        if (-not $setup.WaitForExit($TimeoutMinutes * 60 * 1000)) {
            Stop-Tree $setup.Id
            throw "O instalador passou de $TimeoutMinutes minutos e foi encerrado. Veja em que passo o log parou."
        }

        $result.ExitCode = $setup.ExitCode
        if ($setup.ExitCode -ne 0) {
            $detail = $exitMessages[[int]$setup.ExitCode]
            if (-not $detail) { $detail = "código desconhecido" }
            throw "O instalador terminou com código $($setup.ExitCode): $detail."
        }

        # 4. Conferência.
        $service = Get-Service -Name $serviceName -ErrorAction SilentlyContinue
        if (-not $service) {
            throw "O instalador terminou, mas o serviço $serviceName não existe."
        }
        if ($service.Status -ne "Running") {
            try { $service.WaitForStatus("Running", [TimeSpan]::FromSeconds(30)) } catch { }
            $service.Refresh()
        }
        $result.Service = [string]$service.Status

        $statePath = "C:\ProgramData\AtivaLocacao\UnifiedUpdater\state.json"
        if (Test-Path -LiteralPath $statePath) {
            $result.InstalledVersion = [string](Get-Content -LiteralPath $statePath -Raw | ConvertFrom-Json).installed_version
        }
        if ($result.Service -ne "Running") {
            throw "Instalação concluída, mas o serviço está '$($result.Service)'."
        }
        if ($result.InstalledVersion -ne $ExpectedVersion) {
            throw "Instalação concluída, mas o updater registra a versão '$($result.InstalledVersion)' em vez de $ExpectedVersion."
        }

        $result.Status = "Sucesso"
        $result.Message = "Versão $ExpectedVersion instalada; serviço em execução."
    }
    catch {
        $result.Message = $_.Exception.Message
        # Nunca deixe o computador sem o serviço rodando.
        $ErrorActionPreference = "Continue"
        if (Get-Service -Name $serviceName -ErrorAction SilentlyContinue) {
            $null = & sc.exe start $serviceName 2>&1
        }
    }
    finally {
        if (Test-Path -LiteralPath $log) {
            $result.LogText = (Get-Content -LiteralPath $log -Tail 150) -join [Environment]::NewLine
        }
        # O instalador contém o segredo de bootstrap: não deixe cópias na máquina.
        Remove-Item -LiteralPath $SetupPath -Force -ErrorAction SilentlyContinue
    }

    [pscustomobject]$result
}

$exitCode = 0
try {
    # --- Instalador ---------------------------------------------------------
    if ($InstallerPath) {
        $installer = Get-Item -LiteralPath $InstallerPath
    }
    else {
        $installer = @(
            Get-ChildItem -LiteralPath $ScriptDirectory -Filter "Ativa-Unified-Agent-Setup-*.exe" -File -ErrorAction SilentlyContinue
            Get-ChildItem -LiteralPath (Join-Path $ScriptDirectory "dist") -Filter "Ativa-Unified-Agent-Setup-*.exe" -File -ErrorAction SilentlyContinue
        ) | Where-Object { $_.Name -match '(\d+\.\d+\.\d+)' } |
            Sort-Object { [version]([regex]::Match($_.Name, '\d+\.\d+\.\d+').Value) }, LastWriteTime |
            Select-Object -Last 1
        if (-not $installer) {
            throw "Nenhum Ativa-Unified-Agent-Setup-X.Y.Z.exe encontrado em $ScriptDirectory ou na pasta dist. Coloque o instalador ao lado deste script."
        }
    }
    $versionText = "$($installer.VersionInfo.ProductVersion) $($installer.Name)"
    $versionMatch = [regex]::Match($versionText, '\d+\.\d+\.\d+')
    if (-not $versionMatch.Success) {
        throw "Não foi possível identificar a versão de $($installer.FullName)."
    }
    $expectedVersion = $versionMatch.Value
    $expectedHash = (Get-FileHash -LiteralPath $installer.FullName -Algorithm SHA256).Hash

    # --- Computadores -------------------------------------------------------
    if (-not $ComputerName) {
        $listFile = Join-Path $ScriptDirectory "computadores.txt"
        if (Test-Path -LiteralPath $listFile) {
            $ComputerName = @(Get-Content -LiteralPath $listFile | ForEach-Object { $_.Trim() } | Where-Object { $_ -and -not $_.StartsWith("#") })
        }
        if (-not $ComputerName) { $ComputerName = @($env:COMPUTERNAME) }
    }
    $ComputerName = @($ComputerName | ForEach-Object { $_ -split "," } | ForEach-Object { $_.Trim() } | Where-Object { $_ } | Select-Object -Unique)

    Write-Host "Instalador: $($installer.FullName) (versão $expectedVersion)"
    Write-Host "Computadores: $($ComputerName -join ', ')"
    Write-Host ""

    $results = foreach ($computer in $ComputerName) {
        Write-Host "[$computer] Iniciando..." -ForegroundColor Cyan
        $isLocal = @($env:COMPUTERNAME, "localhost", ".", "127.0.0.1") -contains $computer
        $session = $null
        try {
            if ($isLocal) {
                New-Item -ItemType Directory -Force -Path $RemoteDirectory | Out-Null
                & $ExclusionBlock | ForEach-Object { Write-Host "[$computer] $_" }
                $setupCopy = Join-Path $RemoteDirectory $installer.Name
                Copy-Item -LiteralPath $installer.FullName -Destination $setupCopy -Force
                $output = & $InstallBlock $RemoteDirectory $setupCopy $expectedHash $expectedVersion $TimeoutMinutes
            }
            else {
                try {
                    $session = New-PSSession -ComputerName $computer
                }
                catch {
                    throw "Sem acesso remoto (WinRM). Na máquina $computer, rode como administrador 'Enable-PSRemoting -Force', ou copie este script e o instalador para ela e execute lá. Detalhe: $($_.Exception.Message)"
                }
                Invoke-Command -Session $session -ScriptBlock {
                    param($Directory)
                    New-Item -ItemType Directory -Force -Path $Directory | Out-Null
                } -ArgumentList $RemoteDirectory
                Invoke-Command -Session $session -ScriptBlock $ExclusionBlock |
                    ForEach-Object { Write-Host "[$computer] $_" }
                $setupCopy = Join-Path $RemoteDirectory $installer.Name
                Copy-Item -LiteralPath $installer.FullName -Destination $setupCopy -ToSession $session -Force
                $output = Invoke-Command -Session $session -ScriptBlock $InstallBlock `
                    -ArgumentList $RemoteDirectory, $setupCopy, $expectedHash, $expectedVersion, $TimeoutMinutes
            }
            $result = $null
            foreach ($item in @($output)) {
                if ($item -is [string]) { Write-Host "[$computer] $item" }
                elseif ($item.PSObject.Properties["Status"]) { $result = $item }
            }
            if (-not $result) { throw "A instalação não retornou resultado." }
        }
        catch {
            $result = [pscustomobject]@{
                Computer = $computer; Status = "Falha"; ExitCode = $null; InstalledVersion = ""
                Service = ""; Message = $_.Exception.Message; LogText = ""
            }
        }
        finally {
            if ($session) { Remove-PSSession $session }
        }

        if ($result.LogText) {
            $result.LogText | Set-Content -LiteralPath (Join-Path $ReportDirectory "$computer-instalador.log") -Encoding UTF8
        }
        $color = if ($result.Status -eq "Sucesso") { "Green" } else { "Red" }
        Write-Host "[$computer] $($result.Status): $($result.Message)" -ForegroundColor $color
        Write-Host ""

        $result | Select-Object Computer, Status, ExitCode, InstalledVersion, Service, Message
    }

    $results | Format-Table -AutoSize -Wrap | Out-String | Write-Host
    $reportCsv = Join-Path $ReportDirectory ("resultado-{0:yyyyMMdd-HHmmss}.csv" -f (Get-Date))
    $results | Export-Csv -LiteralPath $reportCsv -NoTypeInformation -Encoding UTF8
    Write-Host "Relatório e logs: $ReportDirectory"

    if (@($results | Where-Object Status -ne "Sucesso").Count -gt 0) { $exitCode = 1 }
}
catch {
    Write-Host "ERRO: $($_.Exception.Message)" -ForegroundColor Red
    $exitCode = 1
}
finally {
    Stop-Transcript | Out-Null
}

Wait-BeforeClose
exit $exitCode
