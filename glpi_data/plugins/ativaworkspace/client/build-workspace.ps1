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
    & $VenvPython -m pip install pyinstaller
    if ($LASTEXITCODE -ne 0) {
        throw "Nao foi possivel instalar o PyInstaller para compilar o Ativa Workspace."
    }
}

# uiautomation: automacao da tela (clicar Conectar -> Ingressar no Entra), na
# sessao do usuario. Depende de comtypes.
& $VenvPython -m pip install uiautomation
if ($LASTEXITCODE -ne 0) {
    throw "Nao foi possivel instalar uiautomation (automacao da tela do Entra)."
}

# Console subsystem de proposito, pelo mesmo motivo do Guardian/Updater: em build
# --noconsole o bootloader do PyInstaller abre MessageBox de aviso e, como SYSTEM
# na sessao 0, ninguem consegue fechar - o processo travaria.
# ativa_workspace_entra.py entra como modulo importado (import normal).
& $VenvPython -m PyInstaller `
    --noconfirm `
    --clean `
    --onefile `
    --console `
    --name "AtivaWorkspace" `
    --distpath $DistDirectory `
    --workpath $BuildDirectory `
    --specpath $BuildDirectory `
    --paths $ClientDirectory `
    --hidden-import ativa_workspace_entra `
    --collect-all uiautomation `
    --collect-all comtypes `
    (Join-Path $ClientDirectory "ativa_workspace_service.py")

$Executable = Join-Path $DistDirectory "AtivaWorkspace.exe"
if ($LASTEXITCODE -ne 0 -or -not (Test-Path -LiteralPath $Executable -PathType Leaf)) {
    throw "PyInstaller nao gerou AtivaWorkspace.exe."
}

$Version = ((& $Executable --version) | Select-Object -First 1).Trim()
if ($LASTEXITCODE -ne 0 -or $Version -notmatch '^\d+\.\d+\.\d+$') {
    throw "Nao foi possivel identificar a versao do Ativa Workspace compilado."
}

Write-Host "Ativa Workspace gerado: $Executable"
Write-Host "Versao: $Version"
Write-Host "SHA-256: $((Get-FileHash -LiteralPath $Executable -Algorithm SHA256).Hash)"
