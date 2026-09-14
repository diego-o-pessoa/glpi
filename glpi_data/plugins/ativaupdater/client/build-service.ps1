[CmdletBinding()]
param(
    [string]$Python = "py"
)

$ErrorActionPreference = "Stop"
$ClientDirectory = Split-Path -Parent $MyInvocation.MyCommand.Path
$VenvDirectory = Join-Path $ClientDirectory ".venv-build"
$BuildDirectory = Join-Path $ClientDirectory "build"
$DistDirectory = Join-Path $ClientDirectory "dist"

& $Python -m venv $VenvDirectory
$VenvPython = Join-Path $VenvDirectory "Scripts\python.exe"
New-Item -ItemType Directory -Force -Path $BuildDirectory, $DistDirectory | Out-Null

& $VenvPython -c "import importlib.util; raise SystemExit(0 if importlib.util.find_spec('PyInstaller') else 1)"
if ($LASTEXITCODE -ne 0) {
    & $VenvPython -m pip install -r (Join-Path $ClientDirectory "requirements-build.txt")
    if ($LASTEXITCODE -ne 0) {
        throw "Nao foi possivel instalar o PyInstaller para compilar o servico."
    }
}

# Console subsystem on purpose. In windowed (--noconsole) builds the PyInstaller
# bootloader reports warnings such as "Failed to remove temporary directory"
# with a blocking MessageBox. As SYSTEM in session 0 nobody can close it, so
# "--configure" hung the unified installer forever. Services, scheduled tasks
# and installer steps run without a visible console anyway.
& $VenvPython -m PyInstaller `
    --noconfirm `
    --clean `
    --onefile `
    --console `
    --name "AtivaUnifiedUpdater" `
    --distpath $DistDirectory `
    --workpath $BuildDirectory `
    --specpath $BuildDirectory `
    (Join-Path $ClientDirectory "unified_updater_service.py")

$Executable = Join-Path $DistDirectory "AtivaUnifiedUpdater.exe"
if ($LASTEXITCODE -ne 0 -or -not (Test-Path -LiteralPath $Executable -PathType Leaf)) {
    throw "PyInstaller nao gerou AtivaUnifiedUpdater.exe."
}

$Version = ((& $VenvPython (Join-Path $ClientDirectory "unified_updater_service.py") --version) | Select-Object -First 1).Trim()
if ($LASTEXITCODE -ne 0 -or $Version -notmatch '^\d+\.\d+\.\d+$') {
    throw "Nao foi possivel identificar a versao do servico."
}

Write-Host "Servico gerado: $Executable"
Write-Host "Versao: $Version"
Write-Host "SHA-256: $((Get-FileHash -LiteralPath $Executable -Algorithm SHA256).Hash)"
