#ifndef WallpaperClientPath
  #error WallpaperClientPath is required
#endif
#ifndef UnifiedUpdaterPath
  #error UnifiedUpdaterPath is required
#endif
#ifndef UpdaterConfigPath
  #error UpdaterConfigPath is required
#endif
#ifndef BootstrapConfigPath
  #error BootstrapConfigPath is required
#endif
#ifndef BuildOutputDir
  #error BuildOutputDir is required
#endif
#ifndef BundleVersion
  #define BundleVersion "1.4.4"
#endif

[Setup]
AppId={{9F8B7C6D-E5D4-4C32-8A1A-B445015310C1}
AppName=Ativa Wallpaper Client
AppVersion={#BundleVersion}
VersionInfoVersion={#BundleVersion}
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
Source: "{#UnifiedUpdaterPath}"; DestDir: "{commonappdata}\AtivaLocacao\UnifiedUpdater"; DestName: "AtivaUnifiedUpdater.exe"; Flags: ignoreversion
Source: "{#BootstrapConfigPath}"; DestDir: "{tmp}"; DestName: "bootstrap-config.json"; Flags: deleteafterinstall ignoreversion
Source: "{#UpdaterConfigPath}"; DestDir: "{tmp}"; DestName: "ativaupdater-service-config.json"; Flags: deleteafterinstall ignoreversion

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

procedure RunOptional(const Filename, Parameters: String);
var
  ResultCode: Integer;
begin
  Log('Executando: ' + Filename + ' ' + Parameters);
  if Exec(Filename, Parameters, '', SW_HIDE, ewWaitUntilTerminated, ResultCode) then
    Log('Codigo de saida: ' + IntToStr(ResultCode));
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
  ResultCode: Integer;
begin
  if CurStep = ssInstall then begin
    { O servico pode ter iniciado este proprio instalador. Pare-o antes de substituir o executavel. }
    RunOptional(ExpandConstant('{sys}\sc.exe'), 'stop AtivaUnifiedUpdater');
    Sleep(5000);
    exit;
  end;

  if CurStep <> ssPostInstall then
    exit;

  RunRequired(
    'Instalando e registrando o cliente de wallpaper...',
    ExpandConstant('{tmp}\AtivaWallpaperClient.exe'),
    '--install --bootstrap-config "' + ExpandConstant('{tmp}\bootstrap-config.json') + '"'
  );
  
  UpdaterPath := ExpandConstant('{commonappdata}\AtivaLocacao\UnifiedUpdater\AtivaUnifiedUpdater.exe');
  RunRequired(
    'Configurando o servico de atualizacao...',
    UpdaterPath,
    '--configure --config "' + ExpandConstant('{tmp}\ativaupdater-service-config.json') + '" --installed-version {#BundleVersion}'
  );

  { Remove a tarefa do atualizador anterior para nao haver dois mecanismos concorrentes. }
  RunOptional(ExpandConstant('{sys}\schtasks.exe'), '/Delete /TN "Ativa Wallpaper Updater" /F');

  if not Exec(ExpandConstant('{sys}\sc.exe'), 'query AtivaUnifiedUpdater', '', SW_HIDE, ewWaitUntilTerminated, ResultCode) or (ResultCode <> 0) then begin
    RunRequired(
      'Registrando o servico de atualizacao...',
      ExpandConstant('{sys}\sc.exe'),
      'create AtivaUnifiedUpdater binPath= "' + UpdaterPath + ' --service" start= auto DisplayName= "Ativa Unified Updater"'
    );
  end;
  RunRequired(
    'Atualizando os parametros do servico...',
    ExpandConstant('{sys}\sc.exe'),
    'config AtivaUnifiedUpdater binPath= "' + UpdaterPath + ' --service" start= auto DisplayName= "Ativa Unified Updater"'
  );
  RunOptional(
    ExpandConstant('{sys}\sc.exe'),
    'failure AtivaUnifiedUpdater reset= 86400 actions= restart/60000/restart/60000/restart/60000'
  );
  RunRequired(
    'Iniciando o servico de atualizacao...',
    ExpandConstant('{sys}\sc.exe'),
    'start AtivaUnifiedUpdater'
  );
  StartForInteractiveUser();
end;

procedure DeinitializeSetup();
begin
  { Se a atualizacao falhar depois de parar o servico, devolva o monitoramento ao Windows. }
  if FileExists(ExpandConstant('{commonappdata}\AtivaLocacao\UnifiedUpdater\AtivaUnifiedUpdater.exe')) then
    RunOptional(ExpandConstant('{sys}\sc.exe'), 'start AtivaUnifiedUpdater');
end;
