[CmdletBinding()]
param(
    [string]$Python = "py"
)

$ErrorActionPreference = "Stop"
$ClientDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$VenvDir = Join-Path $ClientDir ".venv-build"
$DistDir = Join-Path $ClientDir "dist"

& $Python -m venv $VenvDir
$VenvPython = Join-Path $VenvDir "Scripts\python.exe"
$BuildDir = Join-Path $ClientDir "build"
New-Item -ItemType Directory -Force -Path $BuildDir, $DistDir | Out-Null
& $VenvPython -c "import PyInstaller" 2>$null
if ($LASTEXITCODE -ne 0) {
    & $VenvPython -m pip install -r (Join-Path $ClientDir "requirements-client.txt")
    if ($LASTEXITCODE -ne 0) {
        throw "Nao foi possivel instalar as dependencias de compilacao do cliente."
    }
}
& $VenvPython -m PyInstaller `
    --noconfirm `
    --clean `
    --onefile `
    --noconsole `
    --name "AtivaWallpaperClient" `
    --distpath $DistDir `
    --workpath $BuildDir `
    --specpath $BuildDir `
    (Join-Path $ClientDir "wallpaper_client.py")

$Exe = Join-Path $DistDir "AtivaWallpaperClient.exe"
if (-not (Test-Path $Exe)) {
    throw "PyInstaller did not create $Exe"
}
$Hash = Get-FileHash -Algorithm SHA256 $Exe
Write-Host "Built: $Exe"
Write-Host "SHA-256: $($Hash.Hash)"
