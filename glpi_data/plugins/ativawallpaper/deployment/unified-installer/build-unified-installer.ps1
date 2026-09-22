[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)]
    [string]$BootstrapConfig,
    [string]$UpdaterConfig = ".\ativaupdater-service-config.json",
    [string]$GuardianConfig = ".\ativaguardian-service-config.json",
    [string]$OutputDirectory = ".\dist",
    [string]$Python = "py",
    [string]$BundleVersion = "",
    [string]$AgentVersion = "1.19",
    [string]$AgentSha256 = "f3f933a54bc325ffe0d6063e177874e05138dd887fe690adef337640e8d6335c",
    [string]$AgentServerUrl = "https://chamados.ativalocacao.com.br:8443/marketplace/glpiinventory/",
    [switch]$AllComputers,
    [switch]$InstallInnoSetup,
    [switch]$InstallBuildTools,
    # Rebuild a bundle version that already exists in dist (only for packages never distributed).
    [switch]$AllowOverwrite
)

$ErrorActionPreference = "Stop"
$ScriptRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$PluginRoot = (Resolve-Path (Join-Path $ScriptRoot "..\..")).Path
$BootstrapPath = (Resolve-Path $BootstrapConfig).Path
if (-not (Test-Path -LiteralPath $UpdaterConfig -PathType Leaf)) {
    throw "Configuracao do Ativa Updater nao encontrada: $UpdaterConfig. Baixe-a em Ativa Updater > Configuracoes."
}
$UpdaterConfigPath = (Resolve-Path $UpdaterConfig).Path
if (-not (Test-Path -LiteralPath $GuardianConfig -PathType Leaf)) {
    throw "Configuracao do Ativa Guardian nao encontrada: $GuardianConfig. Baixe-a em Ativa Guardian > Configurar > Baixar configuracao do servico."
}
$GuardianConfigPath = (Resolve-Path $GuardianConfig).Path
$UpdaterPluginRoot = (Resolve-Path (Join-Path $ScriptRoot "..\..\..\ativaupdater")).Path
$GuardianPluginRoot = (Resolve-Path (Join-Path $ScriptRoot "..\..\..\ativaguardian")).Path
$OutputPath = if ([IO.Path]::IsPathRooted($OutputDirectory)) {
    [IO.Path]::GetFullPath($OutputDirectory)
} else {
    [IO.Path]::GetFullPath((Join-Path (Get-Location).Path $OutputDirectory))
}
$CacheDirectory = Join-Path $ScriptRoot ".cache"
$AgentMsi = Join-Path $CacheDirectory "GLPI-Agent-$AgentVersion-x64.msi"
$ClientBuildScript = Join-Path $PluginRoot "client\build-client.ps1"
$ClientExe = Join-Path $PluginRoot "client\dist\AtivaWallpaperClient.exe"
$RustDeskExe = Join-Path $CacheDirectory "rustdesk.exe"
$UnifiedUpdaterBuildScript = Join-Path $UpdaterPluginRoot "client\build-service.ps1"
$UnifiedUpdaterExe = Join-Path $UpdaterPluginRoot "client\dist\AtivaUnifiedUpdater.exe"
$GuardianBuildScript = Join-Path $GuardianPluginRoot "client\build-guardian.ps1"
$GuardianExe = Join-Path $GuardianPluginRoot "client\dist\AtivaGuardian.exe"
$ClientVersionFile = Join-Path $PluginRoot "client\dist\client-version.txt"
$BundleVersionFile = Join-Path $ScriptRoot "unified-version.txt"
$ClientIssFile = Join-Path $ScriptRoot "AtivaWallpaperClient.iss"
$ExpectedWallpaperApi = "https://chamados.ativalocacao.com.br:8443/plugins/ativawallpaper/api/v1"
$ExpectedUpdaterApi = "https://chamados.ativalocacao.com.br:8443/plugins/ativaupdater/api/v1"
$ExpectedGuardianApi = "https://chamados.ativalocacao.com.br:8443/plugins/ativaguardian/api/v1"
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
if (-not (Test-Path -LiteralPath $UnifiedUpdaterBuildScript)) {
    throw "Script de build do servico de atualizacao nao encontrado: $UnifiedUpdaterBuildScript"
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
New-Item -ItemType Directory -Force -Path $CacheDirectory, $OutputPath | Out-Null

$UpdaterBootstrap = Get-Content -Raw -LiteralPath $UpdaterConfigPath | ConvertFrom-Json
if ($UpdaterBootstrap.verify_tls -ne $true) {
    throw "ativaupdater-service-config.json deve conter verify_tls=true."
}
if ([string]$UpdaterBootstrap.api_url -ne $ExpectedUpdaterApi) {
    throw "A API do Ativa Updater deve ser $ExpectedUpdaterApi"
}
if ([string]$UpdaterBootstrap.api_token -notmatch '^[a-fA-F0-9]{64}$') {
    throw "ativaupdater-service-config.json nao contem um token valido."
}
$UpdaterInterval = [int]$UpdaterBootstrap.check_interval_seconds
if ($UpdaterInterval -lt 300 -or $UpdaterInterval -gt 86400) {
    throw "O intervalo do Ativa Updater deve estar entre 300 e 86400 segundos."
}

# A configuracao do Guardian e validada aqui, no build, pelas mesmas regras que o
# --configure aplica na maquina. Assim um token errado falha no seu computador e
# nao numa instalacao silenciosa em producao.
$GuardianBootstrap = Get-Content -Raw -LiteralPath $GuardianConfigPath | ConvertFrom-Json
if ($GuardianBootstrap.verify_tls -ne $true) {
    throw "ativaguardian-service-config.json deve conter verify_tls=true."
}
if ([string]$GuardianBootstrap.api_url -ne $ExpectedGuardianApi) {
    throw "A API do Ativa Guardian deve ser $ExpectedGuardianApi"
}
if ([string]$GuardianBootstrap.api_token -notmatch '^[a-fA-F0-9]{64}$') {
    throw "ativaguardian-service-config.json nao contem um token valido."
}
$GuardianInterval = [int]$GuardianBootstrap.heartbeat_interval_seconds
if ($GuardianInterval -lt 60 -or $GuardianInterval -gt 86400) {
    throw "O intervalo de heartbeat do Guardian deve estar entre 60 e 86400 segundos."
}
if (-not (Test-Path -LiteralPath $AgentMsi)) {
    $AgentDownloadUrl = "https://github.com/glpi-project/glpi-agent/releases/download/$AgentVersion/GLPI-Agent-$AgentVersion-x64.msi"
    Write-Host "Baixando GLPI Agent $AgentVersion da release oficial..."
    Invoke-WebRequest -UseBasicParsing -Uri $AgentDownloadUrl -OutFile $AgentMsi
}
Assert-Sha256 -Path $AgentMsi -Expected $AgentSha256

if (-not (Test-Path -LiteralPath $RustDeskExe)) {
    $RustDeskUrl = "https://github.com/rustdesk/rustdesk/releases/download/1.3.1/rustdesk-1.3.1-x86_64.exe"
    Write-Host "Baixando RustDesk portable (1.3.1)..."
    Invoke-WebRequest -UseBasicParsing -Uri $RustDeskUrl -OutFile $RustDeskExe
}
# O servico Ativa Updater instala este executavel como SYSTEM: so aceita a release oficial conferida.
Assert-Sha256 -Path $RustDeskExe -Expected "fc20fd159eea217fa8ba30309aef177ec00913007f42b325e6b7dd1f21a2f245"

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
if ($LASTEXITCODE -ne 0 -or -not (Test-Path -LiteralPath $ClientExe)) {
    throw "Falha ao compilar o cliente de wallpaper."
}
if (-not (Test-Path -LiteralPath $ClientVersionFile)) {
    throw "O build nao gerou client-version.txt."
}
$ClientVersion = (Get-Content -Raw -LiteralPath $ClientVersionFile).Trim()
if ($ClientVersion -notmatch '^\d+\.\d+\.\d+$') {
    throw "Nao foi possivel identificar a versao incorporada no Wallpaper Client."
}
if ([string]::IsNullOrWhiteSpace($BundleVersion)) {
    if (-not (Test-Path -LiteralPath $BundleVersionFile -PathType Leaf)) {
        throw "Arquivo de versao unificada nao encontrado: $BundleVersionFile"
    }
    $BundleVersion = (Get-Content -Raw -LiteralPath $BundleVersionFile).Trim()
}
if ($BundleVersion -notmatch '^\d+\.\d+\.\d+$') {
    throw "Versao do instalador unificado invalida: $BundleVersion"
}
$UnifiedInstaller = Join-Path $OutputPath "Ativa-Unified-Agent-Setup-$BundleVersion.exe"
if ((Test-Path -LiteralPath $UnifiedInstaller) -and -not $AllowOverwrite) {
    # Two different files named 1.6.1 existed: one of them carried Wallpaper Client
    # 1.6.0, and computers installed with it kept the older client.
    throw "Ativa-Unified-Agent-Setup-$BundleVersion.exe ja existe em $OutputPath. Aumente unified-version.txt para gerar um pacote novo (use -AllowOverwrite somente se esta versao nunca foi distribuida)."
}
$Bootstrap.client_version = $ClientVersion

Write-Host "Compilando o servico AtivaUnifiedUpdater.exe..."
& $UnifiedUpdaterBuildScript -Python $PythonExecutable
if ($LASTEXITCODE -ne 0 -or -not (Test-Path -LiteralPath $UnifiedUpdaterExe)) {
    throw "Falha ao compilar o servico Ativa Unified Updater."
}
$UnifiedUpdaterVersion = ((& $UnifiedUpdaterExe --version) | Select-Object -First 1)
$UnifiedUpdaterVersion = if ($UnifiedUpdaterVersion) { $UnifiedUpdaterVersion.ToString().Trim() } else { "" }
if ($UnifiedUpdaterVersion -notmatch '^\d+\.\d+\.\d+$') {
    throw "Nao foi possivel identificar a versao do servico Ativa Unified Updater compilado."
}

Write-Host "Compilando o servico AtivaGuardian.exe..."
& $GuardianBuildScript -Python $PythonExecutable
if ($LASTEXITCODE -ne 0 -or -not (Test-Path -LiteralPath $GuardianExe)) {
    throw "Falha ao compilar o servico Ativa Guardian."
}
$GuardianVersion = ((& $GuardianExe --version) | Select-Object -First 1)
$GuardianVersion = if ($GuardianVersion) { $GuardianVersion.ToString().Trim() } else { "" }
if ($GuardianVersion -notmatch '^\d+\.\d+\.\d+$') {
    throw "Nao foi possivel identificar a versao do servico Ativa Guardian compilado."
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

try {
    $PreparedBootstrap = Join-Path $WorkingDirectory "bootstrap-config.json"
    $PreparedUpdaterConfig = Join-Path $WorkingDirectory "ativaupdater-service-config.json"
    $PreparedGuardianConfig = Join-Path $WorkingDirectory "ativaguardian-service-config.json"
    $CompilerOutput = Join-Path $WorkingDirectory "output"
    New-Item -ItemType Directory -Path $CompilerOutput | Out-Null
    $Utf8WithoutBom = New-Object Text.UTF8Encoding($false)
    [IO.File]::WriteAllText(
        $PreparedBootstrap,
        ($Bootstrap | ConvertTo-Json -Depth 8),
        $Utf8WithoutBom
    )
    [IO.File]::WriteAllText(
        $PreparedUpdaterConfig,
        ($UpdaterBootstrap | ConvertTo-Json -Depth 8),
        $Utf8WithoutBom
    )
    [IO.File]::WriteAllText(
        $PreparedGuardianConfig,
        ($GuardianBootstrap | ConvertTo-Json -Depth 8),
        $Utf8WithoutBom
    )

    Write-Host "Gerando um unico instalador com GLPI Agent, Wallpaper Client, Ativa Updater e Ativa Guardian..."
    & $Iscc `
        "/DWallpaperClientPath=$ClientExe" `
        "/DUnifiedUpdaterPath=$UnifiedUpdaterExe" `
        "/DGuardianPath=$GuardianExe" `
        "/DGuardianConfigPath=$PreparedGuardianConfig" `
        "/DGuardianVersion=$GuardianVersion" `
        "/DRustDeskPath=$RustDeskExe" `
        "/DAgentMsiPath=$AgentMsi" `
        "/DAgentVersion=$AgentVersion" `
        "/DAgentServerUrl=$AgentServerUrl" `
        "/DUpdaterConfigPath=$PreparedUpdaterConfig" `
        "/DBootstrapConfigPath=$PreparedBootstrap" `
        "/DBuildOutputDir=$CompilerOutput" `
        "/DBundleVersion=$BundleVersion" `
        "/DClientVersion=$ClientVersion" `
        "/DUpdaterVersion=$UnifiedUpdaterVersion" `
        $ClientIssFile
    if ($LASTEXITCODE -ne 0) {
        throw "O compilador do Inno Setup retornou codigo $LASTEXITCODE ao compilar o instalador unificado."
    }

    $CompiledUnifiedInstaller = Join-Path $CompilerOutput "Ativa-Unified-Agent-Setup.exe"
    if (-not (Test-Path -LiteralPath $CompiledUnifiedInstaller)) {
        throw "O Inno Setup nao gerou o instalador esperado: $CompiledUnifiedInstaller"
    }
    Copy-Item -LiteralPath $CompiledUnifiedInstaller -Destination $UnifiedInstaller -Force

} finally {
    $ResolvedWorkingDirectory = [IO.Path]::GetFullPath($WorkingDirectory)
    if ($ResolvedWorkingDirectory.StartsWith($TemporaryRoot, [StringComparison]::OrdinalIgnoreCase)) {
        Remove-Item -LiteralPath $ResolvedWorkingDirectory -Recurse -Force -ErrorAction SilentlyContinue
    }
}

if (-not (Test-Path -LiteralPath $UnifiedInstaller)) {
    throw "O instalador unificado nao foi gerado corretamente."
}
$Manifest = [ordered]@{
    bundle_version = $BundleVersion
    wallpaper_client_version = $ClientVersion
    unified_updater_version = $UnifiedUpdaterVersion
    glpi_agent_version = $AgentVersion
    glpi_agent_server = $AgentServerUrl
    glpi_agent_sha256 = (Get-FileHash -LiteralPath $AgentMsi -Algorithm SHA256).Hash
    wallpaper_client_sha256 = (Get-FileHash -LiteralPath $ClientExe -Algorithm SHA256).Hash
    unified_updater_sha256 = (Get-FileHash -LiteralPath $UnifiedUpdaterExe -Algorithm SHA256).Hash
    updater_api = $ExpectedUpdaterApi
    updater_interval_seconds = $UpdaterInterval
    unified_installer_filename = [IO.Path]::GetFileName($UnifiedInstaller)
    unified_installer_sha256 = (Get-FileHash -LiteralPath $UnifiedInstaller -Algorithm SHA256).Hash
    all_computers = [bool]$AllComputers
    generated_at = (Get-Date).ToString("o")
}
$ManifestPath = Join-Path $OutputPath "Ativa-Unified-Agent-Setup-$BundleVersion.manifest.json"
[IO.File]::WriteAllText($ManifestPath, ($Manifest | ConvertTo-Json), (New-Object Text.UTF8Encoding($false)))

Write-Host "Instalador unificado: $UnifiedInstaller"
Write-Host "Conteudo do pacote $BundleVersion`: Wallpaper Client $ClientVersion | Ativa Unified Updater $UnifiedUpdaterVersion | GLPI Agent $AgentVersion"
Write-Host "Manifesto e hashes: $ManifestPath"
Write-Warning "O instalador contem o segredo de bootstrap. Distribua-o somente por canal protegido e rotacione o segredo apos o rollout."
