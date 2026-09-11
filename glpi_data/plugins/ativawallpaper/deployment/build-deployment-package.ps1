[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)]
    [string]$ClientExe,
    [Parameter(Mandatory = $true)]
    [string]$BootstrapConfig,
    [string]$OutputDirectory = ".\Ativa-Wallpaper-Client-Bootstrap"
)

$ErrorActionPreference = "Stop"
$ExePath = (Resolve-Path $ClientExe).Path
$ConfigPath = (Resolve-Path $BootstrapConfig).Path
$Config = Get-Content -Raw -LiteralPath $ConfigPath | ConvertFrom-Json

if ($Config.verify_tls -ne $true) {
    throw "bootstrap config must contain verify_tls=true"
}
$ServerUri = [Uri]$Config.server
if (-not $ServerUri.Scheme.Equals("https", [StringComparison]::OrdinalIgnoreCase)) {
    throw "bootstrap server must use HTTPS"
}
if (-not $ServerUri.Host.Equals("chamados.ativalocacao.com.br", [StringComparison]::OrdinalIgnoreCase)) {
    throw "Use chamados.ativalocacao.com.br, never an IP or another hostname"
}
if (-not $ServerUri.AbsolutePath.TrimEnd('/').EndsWith("/plugins/ativawallpaper/api/v1", [StringComparison]::Ordinal)) {
    throw "bootstrap server must point to /plugins/ativawallpaper/api/v1"
}

New-Item -ItemType Directory -Force -Path $OutputDirectory | Out-Null
Copy-Item -LiteralPath $ExePath -Destination (Join-Path $OutputDirectory "AtivaWallpaperClient.exe") -Force
Copy-Item -LiteralPath $ConfigPath -Destination (Join-Path $OutputDirectory "bootstrap-config.json") -Force

$ExeHash = Get-FileHash -Algorithm SHA512 (Join-Path $OutputDirectory "AtivaWallpaperClient.exe")
$Manifest = @{
    package = "Ativa Wallpaper Client - Bootstrap"
    client_version = "1.4.0"
    client_sha512 = $ExeHash.Hash
    install_command = 'AtivaWallpaperClient.exe --install --bootstrap-config "bootstrap-config.json"'
    generated_at = (Get-Date).ToString("o")
}
$Manifest | ConvertTo-Json | Set-Content -Encoding UTF8 (Join-Path $OutputDirectory "package-manifest.json")

Write-Host "Package files prepared in: $OutputDirectory"
Write-Host "Client SHA-512: $($ExeHash.Hash)"
Write-Host "Do not publish bootstrap-config.json outside the protected GLPI Inventory package."
