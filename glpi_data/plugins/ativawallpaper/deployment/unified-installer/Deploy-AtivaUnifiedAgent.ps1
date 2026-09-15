<#
.SYNOPSIS
    Instala o Ativa Unified Agent em computadores remotos, em modo silencioso.

.DESCRIPTION
    Para cada computador: copia o instalador pelo PowerShell Remoting, confere o SHA-256,
    executa o setup (Inno Setup) sem interface e com tempo limite, confere o serviço e a
    versão instalada, traz o log da instalação e apaga o instalador copiado.

    O instalador contém o segredo de bootstrap: ele é apagado da máquina ao final,
    com ou sem sucesso.

.EXAMPLE
    .\Deploy-AtivaUnifiedAgent.ps1 -ComputerName TI-01-000013, DESKTOP-R1C8ICN `
        -InstallerPath .\dist\Ativa-Unified-Agent-Setup-1.6.2.exe

.EXAMPLE
    .\Deploy-AtivaUnifiedAgent.ps1 -ComputerName (Get-Content .\computadores.txt) `
        -InstallerPath .\dist\Ativa-Unified-Agent-Setup-1.6.2.exe -Credential (Get-Credential)
#>
[CmdletBinding()]
param(
    [Parameter(Mandatory)]
    [string[]]$ComputerName,

    [Parameter(Mandatory)]
    [string]$InstallerPath,

    [pscredential]$Credential,

    [ValidateRange(5, 120)]
    [int]$TimeoutMinutes = 20,

    [string]$ReportDirectory = ".\deploy-logs"
)

$ErrorActionPreference = "Stop"

$installer = Get-Item -LiteralPath $InstallerPath
if ($installer.Name -notmatch '^Ativa-Unified-Agent-Setup-(\d+\.\d+\.\d+)\.exe$') {
    throw "Nome inesperado: $($installer.Name). Use o arquivo gerado em dist (Ativa-Unified-Agent-Setup-X.Y.Z.exe)."
}
$expectedVersion = $Matches[1]
$expectedHash = (Get-FileHash -LiteralPath $installer.FullName -Algorithm SHA256).Hash
New-Item -ItemType Directory -Force -Path $ReportDirectory | Out-Null
$remoteDirectory = "C:\ProgramData\AtivaLocacao\Deploy"

$remoteInstall = {
    param($RemoteDirectory, $FileName, $ExpectedHash, $ExpectedVersion, $TimeoutMinutes)

    $ErrorActionPreference = "Stop"
    $setup = Join-Path $RemoteDirectory $FileName
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

    # Códigos de saída documentados do Inno Setup.
    $exitMessages = @{
        1 = "o setup não conseguiu inicializar"
        2 = "cancelado antes de começar a instalação"
        3 = "erro fatal na preparação da instalação"
        4 = "erro fatal durante a instalação (veja o log)"
        5 = "instalação cancelada ou abortada (ex.: Restart Manager não conseguiu fechar programas)"
        6 = "o setup foi encerrado à força"
        7 = "a etapa de preparação falhou"
        8 = "a etapa de preparação falhou e o Windows precisa reiniciar"
    }

    try {
        if ((Get-FileHash -LiteralPath $setup -Algorithm SHA256).Hash -ne $ExpectedHash) {
            throw "SHA-256 do instalador copiado não confere; a cópia pode estar corrompida."
        }

        $running = @(Get-Process -Name "Ativa-Unified-Agent-Setup*" -ErrorAction SilentlyContinue)
        if ($running.Count -gt 0) {
            throw "Já existe um instalador em execução (PID $($running.Id -join ', ')). Aguarde ou encerre-o antes."
        }

        # /NOCLOSEAPPLICATIONS: o Restart Manager não pode tentar parar o serviço do updater.
        $arguments = "/VERYSILENT /SUPPRESSMSGBOXES /NORESTART /NOCLOSEAPPLICATIONS /SP- /LOG=`"$log`""
        $process = Start-Process -FilePath $setup -ArgumentList $arguments -PassThru -WindowStyle Hidden
        $null = $process.Handle  # Sem isso, ExitCode pode voltar vazio.

        if (-not $process.WaitForExit($TimeoutMinutes * 60 * 1000)) {
            # Encerra o setup e tudo o que ele iniciou (msiexec, --configure etc.).
            & taskkill.exe /PID $process.Id /T /F | Out-Null
            throw "O instalador passou de $TimeoutMinutes minutos e foi encerrado. Veja o passo em que o log parou."
        }

        $result.ExitCode = $process.ExitCode
        if ($process.ExitCode -ne 0) {
            $detail = $exitMessages[[int]$process.ExitCode]
            if (-not $detail) { $detail = "código desconhecido" }
            throw "O instalador terminou com código $($process.ExitCode): $detail."
        }

        $service = Get-Service -Name "AtivaUnifiedUpdater" -ErrorAction SilentlyContinue
        if (-not $service) {
            throw "Instalador terminou, mas o serviço AtivaUnifiedUpdater não existe."
        }
        if ($service.Status -ne "Running") {
            try {
                $service.WaitForStatus("Running", [TimeSpan]::FromSeconds(30))
            }
            catch {
                # O estado final é conferido logo abaixo.
            }
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
    }
    finally {
        if (Test-Path -LiteralPath $log) {
            $result.LogText = (Get-Content -LiteralPath $log -Tail 120) -join [Environment]::NewLine
        }
        # O instalador contém o segredo de bootstrap: não deixe cópias na máquina.
        Remove-Item -LiteralPath $setup -Force -ErrorAction SilentlyContinue
    }

    [pscustomobject]$result
}

$results = foreach ($computer in $ComputerName) {
    Write-Host "[$computer] Instalando $($installer.Name)..."
    $session = $null
    try {
        $sessionParameters = @{ ComputerName = $computer }
        if ($Credential) { $sessionParameters.Credential = $Credential }
        $session = New-PSSession @sessionParameters

        Invoke-Command -Session $session -ScriptBlock {
            param($Directory)
            New-Item -ItemType Directory -Force -Path $Directory | Out-Null
        } -ArgumentList $remoteDirectory
        Copy-Item -LiteralPath $installer.FullName -Destination $remoteDirectory -ToSession $session -Force

        $result = Invoke-Command -Session $session -ScriptBlock $remoteInstall `
            -ArgumentList $remoteDirectory, $installer.Name, $expectedHash, $expectedVersion, $TimeoutMinutes
    }
    catch {
        $result = [pscustomobject]@{
            Computer         = $computer
            Status           = "Falha"
            ExitCode         = $null
            InstalledVersion = ""
            Service          = ""
            Message          = "Não foi possível executar remotamente: $($_.Exception.Message)"
            LogText          = ""
        }
    }
    finally {
        if ($session) { Remove-PSSession $session }
    }

    if ($result.LogText) {
        $result.LogText | Set-Content -LiteralPath (Join-Path $ReportDirectory "$computer.log") -Encoding UTF8
    }
    $color = if ($result.Status -eq "Sucesso") { "Green" } else { "Red" }
    Write-Host "[$computer] $($result.Status): $($result.Message)" -ForegroundColor $color

    $result | Select-Object Computer, Status, ExitCode, InstalledVersion, Service, Message
}

$results | Format-Table -AutoSize -Wrap
$reportCsv = Join-Path $ReportDirectory ("resultado-{0:yyyyMMdd-HHmmss}.csv" -f (Get-Date))
$results | Export-Csv -LiteralPath $reportCsv -NoTypeInformation -Encoding UTF8
Write-Host "Relatório: $reportCsv | Logs: $ReportDirectory"

if (@($results | Where-Object Status -ne "Sucesso").Count -gt 0) {
    exit 1
}
exit 0
