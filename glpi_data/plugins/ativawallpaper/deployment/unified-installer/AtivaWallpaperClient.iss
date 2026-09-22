#ifndef WallpaperClientPath
  #error WallpaperClientPath is required
#endif
#ifndef UnifiedUpdaterPath
  #error UnifiedUpdaterPath is required
#endif
#ifndef RustDeskPath
  #error RustDeskPath is required
#endif
#ifndef GuardianPath
  #error GuardianPath is required
#endif
#ifndef GuardianConfigPath
  #error GuardianConfigPath is required
#endif
#ifndef UpdaterConfigPath
  #error UpdaterConfigPath is required
#endif
#ifndef BootstrapConfigPath
  #error BootstrapConfigPath is required
#endif
#ifndef AgentMsiPath
  #error AgentMsiPath is required
#endif
#ifndef AgentVersion
  #error AgentVersion is required
#endif
#ifndef AgentServerUrl
  #error AgentServerUrl is required
#endif
#ifndef BuildOutputDir
  #error BuildOutputDir is required
#endif
#ifndef BundleVersion
  #define BundleVersion "1.5.0"
#endif
#ifndef ClientVersion
  #define ClientVersion "?"
#endif
#ifndef UpdaterVersion
  #define UpdaterVersion "?"
#endif
#ifndef GuardianVersion
  #define GuardianVersion "?"
#endif

[Setup]
AppId={{9F8B7C6D-E5D4-4C32-8A1A-B445015310C1}
AppName=Ativa Unified Agent
AppVersion={#BundleVersion}
VersionInfoVersion={#BundleVersion}
; Shown in the file properties: the bundle number alone does not tell which components it carries.
; Inno Setup keeps this field short (about 60 characters).
VersionInfoDescription=Ativa: Wallpaper {#ClientVersion}, Updater {#UpdaterVersion}, Guardian {#GuardianVersion}
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
OutputBaseFilename=Ativa-Unified-Agent-Setup
Compression=lzma2/ultra64
SolidCompression=yes
WizardStyle=modern
SetupLogging=yes
CloseApplications=no
RestartApplications=no
; Services up to 1.4.0 pass /CLOSEAPPLICATIONS, which overrides CloseApplications=no.
; The Restart Manager then tried to stop the updater service for 90 s and aborted
; the installation. A filter that matches no file keeps it from checking anything.
CloseApplicationsFilter=*.ativa-restart-manager-disabled

[Files]
Source: "{#WallpaperClientPath}"; DestDir: "{tmp}"; DestName: "AtivaWallpaperClient.exe"; Flags: deleteafterinstall ignoreversion
Source: "{#UnifiedUpdaterPath}"; DestDir: "{commonappdata}\AtivaLocacao\UnifiedUpdater"; DestName: "AtivaUnifiedUpdater.exe"; Flags: ignoreversion
Source: "{#RustDeskPath}"; DestDir: "{commonappdata}\AtivaLocacao\UnifiedUpdater"; DestName: "rustdesk.exe"; Flags: ignoreversion
Source: "{#AgentMsiPath}"; DestDir: "{tmp}"; DestName: "GLPI-Agent-{#AgentVersion}-x64.msi"; Flags: deleteafterinstall ignoreversion
Source: "{#BootstrapConfigPath}"; DestDir: "{tmp}"; DestName: "bootstrap-config.json"; Flags: deleteafterinstall ignoreversion
Source: "{#UpdaterConfigPath}"; DestDir: "{tmp}"; DestName: "ativaupdater-service-config.json"; Flags: deleteafterinstall ignoreversion
; O Guardian mora em Program Files (nao em ProgramData, onde ficam so os dados dele).
; O servico e parado em ssInstall para o arquivo nao estar em uso nesta copia.
Source: "{#GuardianPath}"; DestDir: "{commonpf}\Ativa Locacao\Guardian"; DestName: "AtivaGuardian.exe"; Flags: ignoreversion
Source: "{#GuardianConfigPath}"; DestDir: "{tmp}"; DestName: "ativaguardian-service-config.json"; Flags: deleteafterinstall ignoreversion

[Code]
var
  AgentRestartRequired: Boolean;
  ReplacedUpdaterPath: String;

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

procedure RunOptional(const Filename, Parameters: String);
var
  ResultCode: Integer;
begin
  Log('Executando: ' + Filename + ' ' + Parameters);
  if Exec(Filename, Parameters, '', SW_HIDE, ewWaitUntilTerminated, ResultCode) then
    Log('Codigo de saida: ' + IntToStr(ResultCode));
end;

procedure InstallGlpiAgent(const Parameters: String);
var
  Attempt: Integer;
  ResultCode: Integer;
begin
  WizardForm.StatusLabel.Caption := 'Instalando ou atualizando o GLPI Agent...';
  for Attempt := 1 to 10 do begin
    Log('Instalando o GLPI Agent (tentativa ' + IntToStr(Attempt) + '): msiexec.exe ' + Parameters);
    if not Exec(ExpandConstant('{sys}\msiexec.exe'), Parameters, '', SW_HIDE, ewWaitUntilTerminated, ResultCode) then
      RaiseException('A instalacao do GLPI Agent nao pode ser iniciada. Codigo: ' + IntToStr(ResultCode));
    { 1618: outra instalacao do Windows Installer em andamento (ex.: Windows Update). }
    if ResultCode <> 1618 then
      break;
    Log('Windows Installer ocupado (1618); nova tentativa em 30 segundos.');
    Sleep(30000);
  end;

  if (ResultCode <> 0) and (ResultCode <> 1641) and (ResultCode <> 3010) then
    RaiseException('A instalacao do GLPI Agent falhou. Codigo de saida: ' + IntToStr(ResultCode));

  if (ResultCode = 1641) or (ResultCode = 3010) then
    AgentRestartRequired := True;
end;

procedure StopUpdaterService();
var
  Attempt: Integer;
  ResultCode: Integer;
begin
  if not Exec(ExpandConstant('{sys}\sc.exe'), 'query AtivaUnifiedUpdater', '', SW_HIDE, ewWaitUntilTerminated, ResultCode) or (ResultCode <> 0) then begin
    Log('Servico AtivaUnifiedUpdater ainda nao instalado.');
    exit;
  end;

  { O servico pode ter iniciado este proprio instalador. Pare-o antes de substituir o executavel. }
  RunOptional(ExpandConstant('{sys}\sc.exe'), 'stop AtivaUnifiedUpdater');
  for Attempt := 1 to 60 do begin
    if Exec(ExpandConstant('{cmd}'),
      '/C ""' + ExpandConstant('{sys}\sc.exe') + '" query AtivaUnifiedUpdater | "' + ExpandConstant('{sys}\find.exe') + '" "STOPPED""',
      '', SW_HIDE, ewWaitUntilTerminated, ResultCode) and (ResultCode = 0) then begin
      Log('Servico AtivaUnifiedUpdater parado.');
      break;
    end;
    Sleep(1000);
  end;
  { O SCM informa STOPPED pouco antes de o processo liberar o executavel. }
  Sleep(3000);
end;

procedure StopGuardianService();
var
  Attempt: Integer;
  ResultCode: Integer;
begin
  if not Exec(ExpandConstant('{sys}\sc.exe'), 'query AtivaGuardian', '', SW_HIDE, ewWaitUntilTerminated, ResultCode) or (ResultCode <> 0) then begin
    Log('Servico AtivaGuardian ainda nao instalado.');
    exit;
  end;

  { Upgrade: o executavel fica em uso enquanto o servico roda, e a secao [Files]
    nao conseguiria substitui-lo. Ao contrario do Updater, o Guardian nunca e
    quem executa esta instalacao, entao parar e suficiente - nao ha necessidade
    de renomear o arquivo. }
  RunOptional(ExpandConstant('{sys}\sc.exe'), 'stop AtivaGuardian');
  for Attempt := 1 to 30 do begin
    if Exec(ExpandConstant('{cmd}'),
      '/C ""' + ExpandConstant('{sys}\sc.exe') + '" query AtivaGuardian | "' + ExpandConstant('{sys}\find.exe') + '" "STOPPED""',
      '', SW_HIDE, ewWaitUntilTerminated, ResultCode) and (ResultCode = 0) then begin
      Log('Servico AtivaGuardian parado para atualizacao.');
      break;
    end;
    Sleep(1000);
  end;
  { O SCM informa STOPPED pouco antes de o processo liberar o executavel. }
  Sleep(2000);
end;

procedure InstallGuardian();
var
  GuardianExe: String;
  ConfigPath: String;
  ResultCode: Integer;
  Attempt: Integer;
begin
  GuardianExe := ExpandConstant('{commonpf}\Ativa Locacao\Guardian\AtivaGuardian.exe');
  ConfigPath := ExpandConstant('{commonappdata}\AtivaLocacao\Guardian\config.json');

  ForceDirectories(ExpandConstant('{commonappdata}\AtivaLocacao\Guardian\logs'));

  { Upgrade preserva a configuracao existente: config.json (token/API) e
    machine.json (o machine_id) ficam em ProgramData e nao sao tocados. Só uma
    instalacao limpa grava a configuracao que veio no pacote.
    Consequencia a considerar: se o token for rotacionado no GLPI, maquinas ja
    instaladas continuam com o antigo ate rodar --configure de novo. }
  if FileExists(ConfigPath) then begin
    Log('Ativa Guardian: config.json existente preservado (upgrade).');
  end else begin
    RunRequired(
      'Configurando o Ativa Guardian...',
      GuardianExe,
      '--configure "' + ExpandConstant('{tmp}\ativaguardian-service-config.json') + '"'
    );
  end;

  { --install-service cria ou reconfigura (sem duplicar), define startup
    automatico, aplica a recuperacao restart/restart/restart e inicia. }
  RunRequired(
    'Registrando o servico Ativa Guardian...',
    GuardianExe,
    '--install-service'
  );

  { Confirma que ficou realmente Running; o SCM leva alguns segundos. }
  for Attempt := 1 to 20 do begin
    if Exec(ExpandConstant('{cmd}'),
      '/C ""' + ExpandConstant('{sys}\sc.exe') + '" query AtivaGuardian | "' + ExpandConstant('{sys}\find.exe') + '" "RUNNING""',
      '', SW_HIDE, ewWaitUntilTerminated, ResultCode) and (ResultCode = 0) then begin
      Log('Servico AtivaGuardian em execucao.');
      exit;
    end;
    Sleep(1000);
  end;
  { Nao aborta a instalacao: Agent, Wallpaper e Updater ja foram instalados com
    sucesso e o Windows ainda tentara subir o servico pela politica de falha. }
  Log('AVISO: o servico AtivaGuardian nao confirmou estado RUNNING.');
end;

function IsSupervisedByUpdater(): Boolean;
begin
  { O servico passa /SUPERVISED=1 quando acompanha este instalador ate o fim. }
  Result := ExpandConstant('{param:SUPERVISED|0}') = '1';
end;

procedure PrepareUpdaterExecutable();
var
  UpdaterPath: String;
  ReplacedPath: String;
begin
  UpdaterPath := ExpandConstant('{commonappdata}\AtivaLocacao\UnifiedUpdater\AtivaUnifiedUpdater.exe');
  if IsSupervisedByUpdater() and FileExists(UpdaterPath) then begin
    { O servico continua rodando para registrar falhas e tentar de novo. Um executavel em uso
      pode ser renomeado, liberando o caminho para a nova versao; o servico e reiniciado no fim. }
    ReplacedPath := UpdaterPath + '.old-' + GetDateTimeString('yyyymmddhhnnss', #0, #0);
    if RenameFile(UpdaterPath, ReplacedPath) then begin
      { Guardado para DeinitializeSetup devolver o executavel se a instalacao for abortada. }
      ReplacedUpdaterPath := ReplacedPath;
      Log('Executavel do servico em uso renomeado para ' + ReplacedPath);
      exit;
    end;
    Log('Nao foi possivel renomear o executavel do servico; o servico sera parado.');
  end;
  StopUpdaterService();
end;

procedure SetupWatchdog(const UpdaterPath: String);
var
  WatchdogDir: String;
  WatchdogExe: String;
begin
  { O vigia e uma copia separada do servico, executada por uma tarefa agendada como SYSTEM.
    Ele religa o servico, encerra instalador travado e restaura o executavel se uma atualizacao
    quebra-lo. O proprio servico substitui essa copia depois de conversar com a API. }
  WatchdogDir := ExpandConstant('{commonappdata}\AtivaLocacao\UnifiedUpdater\watchdog');
  WatchdogExe := WatchdogDir + '\AtivaUnifiedUpdater.exe';
  ForceDirectories(WatchdogDir);
  if not FileExists(WatchdogExe) then
    RunOptional(ExpandConstant('{cmd}'), '/C copy /Y "' + UpdaterPath + '" "' + WatchdogExe + '"');
  RunOptional(
    ExpandConstant('{sys}\schtasks.exe'),
    '/Create /F /TN "Ativa Unified Updater Watchdog" /RU SYSTEM /RL HIGHEST /SC MINUTE /MO 15 ' +
    '/TR "\"' + WatchdogExe + '\" --watchdog"'
  );
end;

procedure StartForInteractiveUser(const UpdaterPath: String);
var
  ResultCode: Integer;
  ClientPath: String;
begin
  ClientPath := ExpandConstant('{commonappdata}\AtivaLocacao\Wallpaper\AtivaWallpaperClient.exe');
  if not FileExists(ClientPath) then begin
    Log('Cliente instalado nao foi encontrado para iniciar o polling: ' + ClientPath);
    exit;
  end;

  { Como SYSTEM (servico de atualizacao ou GLPI Inventory), ExecAsOriginalUser iniciaria o
    cliente na sessao 0, sem area de trabalho visivel. O servico inicia uma instancia em cada
    sessao de usuario conectada e retorna 3 quando o instalador nao roda na sessao 0. }
  if Exec(UpdaterPath, '--start-wallpaper-clients', '', SW_HIDE, ewWaitUntilTerminated, ResultCode) and (ResultCode = 0) then begin
    Log('Cliente de wallpaper iniciado nas sessoes de usuario conectadas.');
    exit;
  end;
  Log('Inicio por sessao nao aplicado (codigo ' + IntToStr(ResultCode) + '); usando o usuario que executou o instalador.');

  if ExecAsOriginalUser(ClientPath, '', '', SW_HIDE, ewNoWait, ResultCode) then begin
    Log('Cliente de wallpaper iniciado em modo continuo para o usuario interativo.');
  end else begin
    Log('Nao ha usuario interativo disponivel; o cliente iniciara no proximo login.');
  end;
end;

{ O Defender marca o servico por heuristica de comportamento, nao por assinatura:
  instalar servico + criar tarefa agendada + substituir o proprio executavel e o
  padrao de um dropper, e os binarios PyInstaller nao sao assinados. Ele chegou a
  remover AtivaUnifiedUpdater.exe de maquinas em producao, deixando-as sem updater
  e sem vigia (que mora na mesma pasta e cai junto).

  As exclusoes entram antes de [Files] gravar qualquer coisa: registrar depois nao
  adianta, o arquivo recem-copiado ja teria sido posto em quarentena.

  Falhar aqui nao interrompe a instalacao. Em maquina com outro antivirus, com o
  Defender desativado por politica ou sem os cmdlets do modulo Defender, o comando
  apenas retorna erro e o restante segue normalmente. }
procedure ExcludeFromDefender();
var
  ProductDir: String;
  GuardianDir: String;
  Command: String;
begin
  { A raiz do produto, e nao cada subpasta: abaixo dela ficam UnifiedUpdater (o
    servico, o runner e o vigia), Wallpaper (o cliente por usuario) e Deploy (onde
    o Deploy-AtivaUnifiedAgent.ps1 deposita o proprio setup, que tambem ja foi
    posto em quarentena antes de conseguir rodar). Uma entrada so cobre as tres e
    qualquer subpasta que venha depois. }
  ProductDir := ExpandConstant('{commonappdata}\AtivaLocacao');
  { O Guardian e o unico componente que mora em Program Files; sem esta segunda
    entrada o executavel dele ficaria fora da exclusao e seria posto em
    quarentena pela mesma heuristica que ja removeu o Updater em producao. }
  GuardianDir := ExpandConstant('{commonpf}\Ativa Locacao');

  { ExclusionPath cobre os executaveis que o proprio servico regrava a cada
    atualizacao; ExclusionProcess e o que desarma o Behavior:Win32/Persistence,
    que observa o processo em execucao e nao o arquivo em disco. }
  Command :=
    '-NoProfile -NonInteractive -ExecutionPolicy Bypass -Command "' +
    'try { ' +
      'Add-MpPreference -ExclusionPath ''' + ProductDir + ''',''' + GuardianDir + ''' -ErrorAction Stop; ' +
      'Add-MpPreference -ExclusionProcess ''AtivaUnifiedUpdater.exe'',''AtivaWallpaperClient.exe'',''AtivaGuardian.exe'' -ErrorAction Stop; ' +
      'exit 0 ' +
    '} catch { exit 1 }"';

  WizardForm.StatusLabel.Caption := 'Registrando exclusoes no Windows Defender...';
  RunOptional(ExpandConstant('{sys}\WindowsPowerShell\v1.0\powershell.exe'), Command);
end;

procedure CurStepChanged(CurStep: TSetupStep);
var
  UpdaterPath: String;
  UpdaterLogDir: String;
  AgentMsi: String;
  AgentParameters: String;
  ResultCode: Integer;
begin
  if CurStep = ssInstall then begin
    { Antes de PrepareUpdaterExecutable, que ja grava o executavel em disco. }
    ExcludeFromDefender();
    PrepareUpdaterExecutable();
    { Libera AtivaGuardian.exe antes de [Files] tentar substitui-lo. }
    StopGuardianService();
    exit;
  end;

  if CurStep <> ssPostInstall then
    exit;

  { O log detalhado do msiexec e anexado pelo servico ao relatorio de falha no dashboard. }
  UpdaterLogDir := ExpandConstant('{commonappdata}\AtivaLocacao\UnifiedUpdater\logs');
  ForceDirectories(UpdaterLogDir);
  AgentMsi := ExpandConstant('{tmp}\GLPI-Agent-{#AgentVersion}-x64.msi');
  AgentParameters := '/i "' + AgentMsi + '" /qn /norestart ' +
    '/L*V "' + UpdaterLogDir + '\glpi-agent-msi.log" ' +
    'SERVER="{#AgentServerUrl}" ' +
    'ADDLOCAL=ALL EXECMODE=1 RUNNOW=1 GLPI_VERSION=11 ' +
    'ADD_FIREWALL_EXCEPTION=1 NO_SSL_CHECK=0 NO_HTTPD=0 NO_P2P=0 ' +
    'SCAN_PROFILES=1 TAG="Ativa-Locacao"';
  InstallGlpiAgent(AgentParameters);

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
  SetupWatchdog(UpdaterPath);

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
  { No modo acompanhado o servico antigo ainda esta rodando: pare-o para iniciar o novo executavel. }
  StopUpdaterService();
  RunRequired(
    'Iniciando o servico de atualizacao...',
    ExpandConstant('{sys}\sc.exe'),
    'start AtivaUnifiedUpdater'
  );
  StartForInteractiveUser(UpdaterPath);

  { Por ultimo: o Guardian monitora os outros componentes, entao o primeiro
    heartbeat sai depois que todos ja estao instalados e rodando. }
  InstallGuardian();
end;

function NeedRestart(): Boolean;
begin
  Result := AgentRestartRequired;
end;

procedure DeinitializeSetup();
var
  UpdaterPath: String;
begin
  UpdaterPath := ExpandConstant('{commonappdata}\AtivaLocacao\UnifiedUpdater\AtivaUnifiedUpdater.exe');
  { Instalacao abortada antes de copiar o novo executavel: o rollback do Inno nao conhece a
    renomeacao feita em PrepareUpdaterExecutable, entao o servico ficaria sem executavel. }
  if (ReplacedUpdaterPath <> '') and (not FileExists(UpdaterPath)) and FileExists(ReplacedUpdaterPath) then begin
    if RenameFile(ReplacedUpdaterPath, UpdaterPath) then
      Log('Executavel anterior do servico restaurado: ' + UpdaterPath)
    else
      Log('Falha ao restaurar o executavel anterior do servico a partir de ' + ReplacedUpdaterPath);
  end;
  { Se a atualizacao falhar depois de parar o servico, devolva o monitoramento ao Windows. }
  if FileExists(UpdaterPath) then
    RunOptional(ExpandConstant('{sys}\sc.exe'), 'start AtivaUnifiedUpdater');
end;
