#ifndef AgentMsiPath
  #error AgentMsiPath is required
#endif
#ifndef WallpaperClientPath
  #error WallpaperClientPath is required
#endif
#ifndef WallpaperUpdaterPath
  #error WallpaperUpdaterPath is required
#endif
#ifndef BootstrapConfigPath
  #error BootstrapConfigPath is required
#endif
#ifndef BuildOutputDir
  #error BuildOutputDir is required
#endif
#ifndef BundleVersion
  #define BundleVersion "1.4.0"
#endif
#ifndef AgentVersion
  #define AgentVersion "1.19"
#endif
#ifndef AgentServerUrl
  #error AgentServerUrl is required
#endif

[Setup]
AppId={{FD34078B-EDE4-4F22-9E2B-C556116421D2}
AppName=Ativa GLPI Agent
AppVersion={#BundleVersion}
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
OutputBaseFilename=Ativa-GLPI-Agent-Setup
Compression=lzma2/ultra64
SolidCompression=yes
WizardStyle=modern
SetupLogging=yes
CloseApplications=no
RestartApplications=no

[Files]
Source: "{#AgentMsiPath}"; DestDir: "{tmp}"; DestName: "GLPI-Agent-{#AgentVersion}-x64.msi"; Flags: deleteafterinstall ignoreversion
Source: "{#WallpaperClientPath}"; DestDir: "{tmp}"; DestName: "AtivaWallpaperClient.exe"; Flags: deleteafterinstall ignoreversion
Source: "{#WallpaperUpdaterPath}"; DestDir: "{commonappdata}\AtivaLocacao\Wallpaper"; DestName: "AtivaWallpaperUpdater.exe"; Flags: ignoreversion
Source: "{#BootstrapConfigPath}"; DestDir: "{tmp}"; DestName: "bootstrap-config.json"; Flags: deleteafterinstall ignoreversion

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

procedure StartForInteractiveUser();
var
  ResultCode: Integer;
  ClientPath: String;
begin
  ClientPath := ExpandConstant('{commonappdata}\AtivaLocacao\Wallpaper\AtivaWallpaperClient.exe');
  if not FileExists(ClientPath) then begin
    Log('Cliente instalado nao foi encontrado para iniciar o polling: ' + ClientPath);
    exit;
  end;

  { Run without --once: synchronization starts immediately and the process
    remains alive to receive dashboard commands at each polling interval. }
  if ExecAsOriginalUser(ClientPath, '', '', SW_HIDE, ewNoWait, ResultCode) then begin
    Log('Cliente de wallpaper iniciado em modo continuo para o usuario interativo.');
  end else begin
    Log('Nao ha usuario interativo disponivel; o cliente iniciara no proximo login.');
  end;
end;

procedure CurStepChanged(CurStep: TSetupStep);
var
  AgentMsi: String;
  AgentParameters: String;
  UpdaterPath: String;
  TaskParameters: String;
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
  RunRequired(
    'Instalando e registrando o cliente de wallpaper...',
    ExpandConstant('{tmp}\AtivaWallpaperClient.exe'),
    '--install --bootstrap-config "' + ExpandConstant('{tmp}\bootstrap-config.json') + '"'
  );
  UpdaterPath := ExpandConstant('{commonappdata}\AtivaLocacao\Wallpaper\AtivaWallpaperUpdater.exe');
  TaskParameters := '/Create /TN "Ativa Wallpaper Updater" /SC MINUTE /MO 1 /RU SYSTEM /RL HIGHEST /F /TR "' +
    UpdaterPath + ' --check"';
  RunRequired(
    'Configurando atualizacoes automaticas...',
    ExpandConstant('{sys}\schtasks.exe'),
    TaskParameters
  );
  RunRequired(
    'Iniciando verificacao de atualizacoes...',
    ExpandConstant('{sys}\schtasks.exe'),
    '/Run /TN "Ativa Wallpaper Updater"'
  );
  StartForInteractiveUser();
end;

function NeedRestart(): Boolean;
begin
  Result := AgentRestartRequired;
end;
