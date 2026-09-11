[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)]
    [string]$BootstrapConfig,
    [string]$OutputDirectory = ".\dist",
    [string]$Python = "py",
    [string]$AgentVersion = "1.19",
    [string]$AgentSha256 = "f3f933a54bc325ffe0d6063e177874e05138dd887fe690adef337640e8d6335c",
    [string]$AgentServerUrl = "https://chamados.ativalocacao.com.br:8443/marketplace/glpiinventory/",
    [switch]$AllComputers,
    [switch]$InstallInnoSetup,
    [switch]$InstallBuildTools
)

$ErrorActionPreference = "Stop"
$ScriptRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$PluginRoot = (Resolve-Path (Join-Path $ScriptRoot "..\..")).Path
$BootstrapPath = (Resolve-Path $BootstrapConfig).Path
$OutputPath = if ([IO.Path]::IsPathRooted($OutputDirectory)) {
    [IO.Path]::GetFullPath($OutputDirectory)
} else {
    [IO.Path]::GetFullPath((Join-Path (Get-Location).Path $OutputDirectory))
}
$CacheDirectory = Join-Path $ScriptRoot ".cache"
$AgentMsi = Join-Path $CacheDirectory "GLPI-Agent-$AgentVersion-x64.msi"
$ClientBuildScript = Join-Path $PluginRoot "client\build-client.ps1"
$ClientExe = Join-Path $PluginRoot "client\dist\AtivaWallpaperClient.exe"
$UpdaterExe = Join-Path $PluginRoot "client\dist\AtivaWallpaperUpdater.exe"
$IssFile = Join-Path $ScriptRoot "AtivaGLPIAgent.iss"
$ExpectedWallpaperApi = "https://chamados.ativalocacao.com.br:8443/plugins/ativawallpaper/api/v1"
$ExpectedAgentServer = "https://chamados.ativalocacao.com.br:8443/marketplace/glpiinventory/"

function Find-InnoSetupCompiler {
    $Command = Get-Command ISCC.exe -ErrorAction SilentlyContinue
    if ($Command) {
        return $Command.Source
    }

    $Candidates = @()
    if (${env:ProgramFiles(x86)}) {
        $Candidates += Join-Path ${env:ProgramFiles(x86)} "Inno Setup 6\ISCC.exe"
    }
    if ($env:ProgramFiles) {
        $Candidates += Join-Path $env:ProgramFiles "Inno Setup 6\ISCC.exe"
    }
    if ($env:LOCALAPPDATA) {
        $Candidates += Join-Path $env:LOCALAPPDATA "Programs\Inno Setup 6\ISCC.exe"
        $Candidates += Join-Path $env:LOCALAPPDATA "Inno Setup 6\ISCC.exe"
    }

    $UninstallRegistryKeys = @(
        "HKCU:\Software\Microsoft\Windows\CurrentVersion\Uninstall\Inno Setup 6_is1",
        "HKLM:\Software\Microsoft\Windows\CurrentVersion\Uninstall\Inno Setup 6_is1",
        "HKLM:\Software\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall\Inno Setup 6_is1"
    )
    foreach ($RegistryKey in $UninstallRegistryKeys) {
        $InstallLocation = (Get-ItemProperty -LiteralPath $RegistryKey -Name InstallLocation -ErrorAction SilentlyContinue).InstallLocation
        if ($InstallLocation) {
            $Candidates += Join-Path $InstallLocation "ISCC.exe"
        }
    }

    return $Candidates |
        Select-Object -Unique |
        Where-Object { Test-Path -LiteralPath $_ -PathType Leaf } |
        Select-Object -First 1
}

function Find-PythonExecutable([string]$Requested) {
    $Candidates = @()
    $Command = Get-Command $Requested -ErrorAction SilentlyContinue
    if ($Command) {
        $Candidates += $Command.Source
    }
    if ($env:LOCALAPPDATA) {
        $LocalPythonRoot = Join-Path $env:LOCALAPPDATA "Programs\Python"
        $Candidates += Get-ChildItem -LiteralPath $LocalPythonRoot -Directory -Filter "Python*" -ErrorAction SilentlyContinue |
            Sort-Object Name -Descending |
            ForEach-Object { Join-Path $_.FullName "python.exe" }
        $Candidates += Join-Path $env:LOCALAPPDATA "Programs\Python\Launcher\py.exe"
    }
    if ($env:ProgramFiles) {
        $Candidates += Get-ChildItem -LiteralPath $env:ProgramFiles -Directory -Filter "Python*" -ErrorAction SilentlyContinue |
            Sort-Object Name -Descending |
            ForEach-Object { Join-Path $_.FullName "python.exe" }
    }
    if ($env:SystemRoot) {
        $Candidates += Join-Path $env:SystemRoot "py.exe"
    }

    foreach ($Candidate in ($Candidates | Select-Object -Unique)) {
        if (-not $Candidate -or -not (Test-Path -LiteralPath $Candidate)) {
            continue
        }
        try {
            & $Candidate -c "import sys; assert sys.version_info >= (3, 11)" 2>$null
            if ($LASTEXITCODE -eq 0) {
                return $Candidate
            }
        } catch {
            continue
        }
    }
    return $null
}

function Get-WingetExecutable {
    $Command = Get-Command winget.exe -ErrorAction SilentlyContinue
    if (-not $Command) {
        throw "winget nao foi encontrado. Instale o App Installer da Microsoft Store."
    }
    return $Command.Source
}

function Assert-Sha256([string]$Path, [string]$Expected) {
    $Actual = (Get-FileHash -LiteralPath $Path -Algorithm SHA256).Hash.ToLowerInvariant()
    if ($Actual -ne $Expected.ToLowerInvariant()) {
        throw "SHA-256 invalido para $Path. Esperado: $Expected; recebido: $Actual"
    }
}

if (-not [Environment]::Is64BitOperatingSystem) {
    throw "O instalador unificado suporta somente Windows x64."
}
if ($AgentVersion -notmatch '^\d+\.\d+(?:\.\d+)?$') {
    throw "Versao do GLPI Agent invalida: $AgentVersion"
}
if ($AgentSha256 -notmatch '^[a-fA-F0-9]{64}$') {
    throw "SHA-256 esperado do GLPI Agent e invalido."
}
if ($AgentServerUrl -ne $ExpectedAgentServer) {
    throw "O endpoint do GLPI Agent deve ser $ExpectedAgentServer"
}
if (-not (Test-Path -LiteralPath $ClientBuildScript)) {
    throw "Script de build do cliente nao encontrado: $ClientBuildScript"
}

$Bootstrap = Get-Content -Raw -LiteralPath $BootstrapPath | ConvertFrom-Json
if ($Bootstrap.verify_tls -ne $true) {
    throw "bootstrap-config.json deve conter verify_tls=true."
}
if ([string]$Bootstrap.server -ne $ExpectedWallpaperApi) {
    throw "A API do wallpaper deve ser $ExpectedWallpaperApi"
}
if (-not $Bootstrap.registration_secret -or ([string]$Bootstrap.registration_secret).Length -lt 32) {
    throw "bootstrap-config.json nao contem um segredo de registro valido."
}
if ($AllComputers) {
    $Bootstrap.pilot_hostname = ""
} elseif (-not $Bootstrap.pilot_hostname) {
    throw "Informe -AllComputers para gerar um instalador sem restricao de hostname."
}
$Bootstrap.client_version = "1.4.0"

New-Item -ItemType Directory -Force -Path $CacheDirectory, $OutputPath | Out-Null
if (-not (Test-Path -LiteralPath $AgentMsi)) {
    $AgentDownloadUrl = "https://github.com/glpi-project/glpi-agent/releases/download/$AgentVersion/GLPI-Agent-$AgentVersion-x64.msi"
    Write-Host "Baixando GLPI Agent $AgentVersion da release oficial..."
    Invoke-WebRequest -UseBasicParsing -Uri $AgentDownloadUrl -OutFile $AgentMsi
}
Assert-Sha256 -Path $AgentMsi -Expected $AgentSha256

$AgentSignature = Get-AuthenticodeSignature -LiteralPath $AgentMsi
if ($AgentSignature.Status -eq 'NotSigned') {
    throw "O MSI oficial do GLPI Agent nao possui assinatura Authenticode."
}
if ($AgentSignature.Status -ne 'Valid') {
    Write-Warning "A assinatura do MSI retornou $($AgentSignature.Status). O SHA-256 oficial foi validado."
}

$PythonExecutable = Find-PythonExecutable -Requested $Python
if (-not $PythonExecutable -and $InstallBuildTools) {
    $Winget = Get-WingetExecutable
    Write-Host "Instalando Python 3.12 para compilar o cliente..."
    & $Winget install --id Python.Python.3.12 --exact --source winget --silent --scope user --disable-interactivity --accept-package-agreements --accept-source-agreements
    if ($LASTEXITCODE -ne 0) {
        throw "Falha ao instalar Python 3.12 pelo winget."
    }
    $PythonExecutable = Find-PythonExecutable -Requested $Python
}
if (-not $PythonExecutable) {
    throw "Python 3.11+ nao encontrado. Execute novamente com -InstallBuildTools."
}

Write-Host "Compilando AtivaWallpaperClient.exe..."
& $ClientBuildScript -Python $PythonExecutable
if ($LASTEXITCODE -ne 0 -or -not (Test-Path -LiteralPath $ClientExe) -or -not (Test-Path -LiteralPath $UpdaterExe)) {
    throw "Falha ao compilar o cliente ou o atualizador automatico."
}

$Iscc = Find-InnoSetupCompiler
if (-not $Iscc -and ($InstallInnoSetup -or $InstallBuildTools)) {
    $Winget = Get-WingetExecutable
    Write-Host "Instalando Inno Setup 6..."
    & $Winget install --id JRSoftware.InnoSetup --exact --source winget --silent --disable-interactivity --accept-package-agreements --accept-source-agreements
    if ($LASTEXITCODE -ne 0) {
        throw "Falha ao instalar Inno Setup. Execute 'winget install --id JRSoftware.InnoSetup --exact --source winget' e rode o builder novamente."
    }
    $Iscc = Find-InnoSetupCompiler
}
if (-not $Iscc) {
    throw "Inno Setup 6 nao encontrado. Execute novamente com -InstallBuildTools."
}

$TemporaryRoot = [IO.Path]::GetFullPath([IO.Path]::GetTempPath())
$WorkingDirectory = Join-Path $TemporaryRoot ("AtivaUnifiedInstaller-" + [Guid]::NewGuid().ToString("N"))
New-Item -ItemType Directory -Path $WorkingDirectory | Out-Null
$Installer = Join-Path $OutputPath "Ativa-GLPI-Agent-Setup-1.4.0.exe"
try {
    $PreparedBootstrap = Join-Path $WorkingDirectory "bootstrap-config.json"
    $CompilerOutput = Join-Path $WorkingDirectory "output"
    New-Item -ItemType Directory -Path $CompilerOutput | Out-Null
    $Utf8WithoutBom = New-Object Text.UTF8Encoding($false)
    [IO.File]::WriteAllText(
        $PreparedBootstrap,
        ($Bootstrap | ConvertTo-Json -Depth 8),
        $Utf8WithoutBom
    )

    Write-Host "Gerando instalador unico..."
    & $Iscc `
        "/DAgentMsiPath=$AgentMsi" `
        "/DWallpaperClientPath=$ClientExe" `
        "/DWallpaperUpdaterPath=$UpdaterExe" `
        "/DBootstrapConfigPath=$PreparedBootstrap" `
        "/DBuildOutputDir=$CompilerOutput" `
        "/DBundleVersion=1.4.0" `
        "/DAgentVersion=$AgentVersion" `
        "/DAgentServerUrl=$AgentServerUrl" `
        $IssFile
    if ($LASTEXITCODE -ne 0) {
        throw "O compilador do Inno Setup retornou codigo $LASTEXITCODE."
    }

    $CompiledInstaller = Join-Path $CompilerOutput "Ativa-GLPI-Agent-Setup.exe"
    if (-not (Test-Path -LiteralPath $CompiledInstaller)) {
        throw "O Inno Setup nao gerou o instalador esperado: $CompiledInstaller"
    }
    Copy-Item -LiteralPath $CompiledInstaller -Destination $Installer -Force
} finally {
    $ResolvedWorkingDirectory = [IO.Path]::GetFullPath($WorkingDirectory)
    if ($ResolvedWorkingDirectory.StartsWith($TemporaryRoot, [StringComparison]::OrdinalIgnoreCase)) {
        Remove-Item -LiteralPath $ResolvedWorkingDirectory -Recurse -Force -ErrorAction SilentlyContinue
    }
}

if (-not (Test-Path -LiteralPath $Installer)) {
    throw "O instalador nao foi gerado em $Installer"
}
$ClientUpdatePackage = Join-Path $OutputPath "AtivaWallpaperClient-1.4.0.exe"
$AgentUpdatePackage = Join-Path $OutputPath "GLPI-Agent-$AgentVersion-x64.msi"
Copy-Item -LiteralPath $ClientExe -Destination $ClientUpdatePackage -Force
Copy-Item -LiteralPath $AgentMsi -Destination $AgentUpdatePackage -Force
$Manifest = [ordered]@{
    bundle_version = "1.4.0"
    glpi_agent_version = $AgentVersion
    glpi_agent_server = $AgentServerUrl
    glpi_agent_sha256 = (Get-FileHash -LiteralPath $AgentMsi -Algorithm SHA256).Hash
    wallpaper_client_sha256 = (Get-FileHash -LiteralPath $ClientExe -Algorithm SHA256).Hash
    wallpaper_updater_sha256 = (Get-FileHash -LiteralPath $UpdaterExe -Algorithm SHA256).Hash
    installer_sha256 = (Get-FileHash -LiteralPath $Installer -Algorithm SHA256).Hash
    all_computers = [bool]$AllComputers
    update_packages = @(
        [ordered]@{
            component = "wallpaper_client"
            version = "1.4.0"
            file = (Split-Path -Leaf $ClientUpdatePackage)
            sha256 = (Get-FileHash -LiteralPath $ClientUpdatePackage -Algorithm SHA256).Hash
        },
        [ordered]@{
            component = "glpi_agent"
            version = $AgentVersion
            file = (Split-Path -Leaf $AgentUpdatePackage)
            sha256 = (Get-FileHash -LiteralPath $AgentUpdatePackage -Algorithm SHA256).Hash
        }
    )
    generated_at = (Get-Date).ToString("o")
}
$ManifestPath = Join-Path $OutputPath "Ativa-GLPI-Agent-Setup-1.4.0.manifest.json"
[IO.File]::WriteAllText($ManifestPath, ($Manifest | ConvertTo-Json), (New-Object Text.UTF8Encoding($false)))

Write-Host "Instalador gerado: $Installer"
Write-Host "Manifesto e hashes: $ManifestPath"
Write-Host "Pacote de atualizacao do Wallpaper Client: $ClientUpdatePackage"
Write-Host "Pacote de atualizacao do GLPI Agent: $AgentUpdatePackage"
Write-Warning "O instalador contem o segredo de bootstrap. Distribua-o somente por canal protegido e rotacione o segredo apos o rollout."
