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
  #define BundleVersion "1.4.2"
#endif

[Setup]
AppId={{9F8B7C6D-E5D4-4C32-8A1A-B445015310C1}
AppName=Ativa Wallpaper Client
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
OutputBaseFilename=Ativa-Wallpaper-Client-Setup
Compression=lzma2/ultra64
SolidCompression=yes
WizardStyle=modern
SetupLogging=yes
CloseApplications=no
RestartApplications=no

[Files]
Source: "{#WallpaperClientPath}"; DestDir: "{tmp}"; DestName: "AtivaWallpaperClient.exe"; Flags: deleteafterinstall ignoreversion
Source: "{#WallpaperUpdaterPath}"; DestDir: "{commonappdata}\AtivaLocacao\Wallpaper"; DestName: "AtivaWallpaperUpdater.exe"; Flags: ignoreversion
Source: "{#BootstrapConfigPath}"; DestDir: "{tmp}"; DestName: "bootstrap-config.json"; Flags: deleteafterinstall ignoreversion

[Code]
procedure RunRequired(const Description, Filename, Parameters: String);
var
  ResultCode: Integer;
begin
  WizardForm.StatusLabel.Caption := Description;
  Log(Description + ': ' + Filename + ' ' + Parameters);
  if not Exec(Filename, Parameters, '', SW_HIDE, ewWaitUntilTerminated, ResultCode) then
    RaiseException(Description + ' nao pode ser iniciada. Codigo: ' + IntToStr(ResultCode));

  if (ResultCode <> 0) then
    RaiseException(Description + ' falhou. Codigo de saida: ' + IntToStr(ResultCode));
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

  if ExecAsOriginalUser(ClientPath, '', '', SW_HIDE, ewNoWait, ResultCode) then begin
    Log('Cliente de wallpaper iniciado em modo continuo para o usuario interativo.');
  end else begin
    Log('Nao ha usuario interativo disponivel; o cliente iniciara no proximo login.');
  end;
end;

procedure CurStepChanged(CurStep: TSetupStep);
var
  UpdaterPath: String;
  TaskParameters: String;
begin
  if CurStep <> ssPostInstall then
    exit;

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
