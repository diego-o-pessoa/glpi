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
        throw "Nao foi possivel instalar o PyInstaller para compilar o Guardian."
    }
}

# Roda a suite antes de empacotar: um build que passa nos testes custa menos que
# um servico quebrado distribuido para a frota.
& $VenvPython -m unittest discover -s (Join-Path $ClientDirectory "tests") -p "test_*.py"
if ($LASTEXITCODE -ne 0) {
    throw "Os testes do Guardian falharam; build interrompido."
}

# Console subsystem de proposito, pelo mesmo motivo do Ativa Updater: em build
# --noconsole o bootloader do PyInstaller abre MessageBox de aviso, e como
# SYSTEM na sessao 0 ninguem consegue fechar, o processo trava para sempre.
& $VenvPython -m PyInstaller `
    --noconfirm `
    --clean `
    --onefile `
    --console `
    --name "AtivaGuardian" `
    --distpath $DistDirectory `
    --workpath $BuildDirectory `
    --specpath $BuildDirectory `
    (Join-Path $ClientDirectory "ativa_guardian_service.py")

$Executable = Join-Path $DistDirectory "AtivaGuardian.exe"
if ($LASTEXITCODE -ne 0 -or -not (Test-Path -LiteralPath $Executable -PathType Leaf)) {
    throw "PyInstaller nao gerou AtivaGuardian.exe."
}

$Version = ((& $Executable --version) | Select-Object -First 1).Trim()
if ($LASTEXITCODE -ne 0 -or $Version -notmatch '^\d+\.\d+\.\d+$') {
    throw "Nao foi possivel identificar a versao do Guardian compilado."
}

Write-Host "Guardian gerado: $Executable"
Write-Host "Versao: $Version"
Write-Host "SHA-256: $((Get-FileHash -LiteralPath $Executable -Algorithm SHA256).Hash)"
Write-Host ""
Write-Host "Instalar na maquina de teste (como administrador):"
Write-Host "  copy `"$Executable`" `"C:\Program Files\Ativa Locacao\Guardian\AtivaGuardian.exe`""
Write-Host "  AtivaGuardian.exe --configure config.json"
Write-Host "  AtivaGuardian.exe --install-service"
