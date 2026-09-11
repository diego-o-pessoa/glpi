#ifndef AgentMsiPath
  #error AgentMsiPath is required
#endif
#ifndef BuildOutputDir
  #error BuildOutputDir is required
#endif
#ifndef AgentVersion
  #define AgentVersion "1.19"
#endif
#ifndef AgentServerUrl
  #error AgentServerUrl is required
#endif

[Setup]
AppId={{5C2A1E7B-0A8F-4D9C-9E2A-B665037420F2}
AppName=Ativa GLPI Agent Installer
AppVersion={#AgentVersion}
AppPublisher=Ativa Locacao
AppPublisherURL=https://chamados.ativalocacao.com.br:8443/
CreateAppDir=no
PrivilegesRequired=admin
ArchitecturesAllowed=x64compatible
ArchitecturesInstallIn64BitMode=x64compatible
DisableProgramGroupPage=yes
DisableReadyPage=no
Uninstallable=no
OutputDir={#BuildOutputDir}
OutputBaseFilename=Ativa-GLPI-Agent-Setup-Only
Compression=lzma2/ultra64
SolidCompression=yes
WizardStyle=modern
SetupLogging=yes
CloseApplications=no
RestartApplications=no

[Files]
Source: "{#AgentMsiPath}"; DestDir: "{tmp}"; DestName: "GLPI-Agent-{#AgentVersion}-x64.msi"; Flags: deleteafterinstall ignoreversion

[Code]
var
  AgentRestartRequired: Boolean;

procedure RunRequired(const Description, Filename, Parameters: String);
var
  ResultCode: Integer;
begin
  WizardForm.StatusLabel.Caption := Description;
  Log(Description + ': ' + Filename + ' ' + Parameters);
  if not Exec(Filename, Parameters, '', SW_HIDE, ewWaitUntilTerminated, ResultCode) then
    RaiseException(Description + ' nao pode ser iniciada. Codigo: ' + IntToStr(ResultCode));

  if (ResultCode <> 0) and (ResultCode <> 1641) and (ResultCode <> 3010) then
    RaiseException(Description + ' falhou. Codigo de saida: ' + IntToStr(ResultCode));

  if (ResultCode = 1641) or (ResultCode = 3010) then
    AgentRestartRequired := True;
end;

procedure CurStepChanged(CurStep: TSetupStep);
var
  AgentMsi: String;
  AgentParameters: String;
begin
  if CurStep <> ssPostInstall then
    exit;

  AgentMsi := ExpandConstant('{tmp}\GLPI-Agent-{#AgentVersion}-x64.msi');
  AgentParameters := '/i "' + AgentMsi + '" /qn /norestart ' +
    'SERVER="{#AgentServerUrl}" ' +
    'ADDLOCAL=ALL EXECMODE=1 RUNNOW=1 GLPI_VERSION=11 ' +
    'ADD_FIREWALL_EXCEPTION=1 NO_SSL_CHECK=0 NO_HTTPD=0 NO_P2P=0 ' +
    'SCAN_PROFILES=1 TAG="Ativa-Locacao"';

  RunRequired(
    'Instalando e configurando o GLPI Agent...',
    ExpandConstant('{sys}\msiexec.exe'),
    AgentParameters
  );
end;

function NeedRestart(): Boolean;
begin
  Result := AgentRestartRequired;
end;
