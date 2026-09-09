[CmdletBinding()]
param(
    [string]$ExpectedHostname = "DESKTOP-R1C8ICN-2026-08-17-14-54-59"
)

$ErrorActionPreference = "Stop"
$Root = Join-Path $env:ProgramData "AtivaLocacao\Wallpaper"
$Exe = Join-Path $Root "AtivaWallpaperClient.exe"

if (-not $env:COMPUTERNAME.Equals($ExpectedHostname, [StringComparison]::OrdinalIgnoreCase)) {
    throw "Pilot guard: this computer is $env:COMPUTERNAME, expected $ExpectedHostname"
}
if (-not (Test-Path -LiteralPath $Exe)) {
    throw "Client executable not found at $Exe"
}

$RunValue = Get-ItemPropertyValue -Path "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Run" -Name "AtivaWallpaperClient"
if ($RunValue -notlike "*$Exe*") {
    throw "HKLM Run entry does not point to the installed executable"
}

& $Exe --once --debug
if ($LASTEXITCODE -ne 0) {
    throw "One-shot synchronization failed with exit code $LASTEXITCODE"
}

Write-Host "Pilot local checks passed. Confirm the computer status in GLPI > Ativa Wallpaper."
