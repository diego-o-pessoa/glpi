; Desinstalador do Ativa Unified Agent.
;
; Remove os componentes escolhidos (ou tudo) deste computador. Sempre exige a
; senha do Ativa Manutencao: quem confere e o proprio Guardian, embutido aqui
; (--authorize-uninstall), que tambem libera por 15 minutos os servicos que a
; protecao do Guardian trava. Sem senha valida, nada e removido.
;
; Remover um componente sozinho nao impede o auto-reparo do Guardian/Updater
; de reinstala-lo depois (decisao do projeto). "Desinstalar tudo" remove os
; dois primeiro, entao nada volta.
;
; Uso: duplo clique (escolhe na tela) ou, pela entrada em Aplicativos do
; Windows, /TYPE=tudo (ja vem tudo marcado).

#ifndef GuardianPath
  #error GuardianPath is required
#endif
#ifndef VerifierPath
  #error VerifierPath is required
#endif
#ifndef BuildOutputDir
  #error BuildOutputDir is required
#endif
#ifndef BundleVersion
  #define BundleVersion "0.0.0"
#endif

[Setup]
AppId={{4B6E2C1A-7D3F-4E85-9A2B-C0D1E2F3A4B5}
AppName=Desinstalar Ativa Unified Agent
AppVersion={#BundleVersion}
VersionInfoVersion={#BundleVersion}
VersionInfoDescription=Desinstalador do Ativa Unified Agent
AppPublisher=Ativa Locacao
CreateAppDir=no
PrivilegesRequired=admin
ArchitecturesAllowed=x64compatible
ArchitecturesInstallIn64BitMode=x64compatible
DisableProgramGroupPage=yes
DisableReadyPage=no
Uninstallable=no
OutputDir={#BuildOutputDir}
OutputBaseFilename=Ativa-Unified-Agent-Uninstall
Compression=lzma2/ultra64
SolidCompression=yes
WizardStyle=modern
SetupLogging=yes
CloseApplications=no
RestartApplications=no
CloseApplicationsFilter=*.ativa-restart-manager-disabled

[Languages]
Name: "ptbr"; MessagesFile: "compiler:Languages\BrazilianPortuguese.isl"

[Messages]
SetupAppTitle=Desinstalar Ativa
SetupWindowTitle=Desinstalar Ativa Unified Agent
WelcomeLabel1=Desinstalar o Ativa Unified Agent
WelcomeLabel2=Este assistente remove os componentes da Ativa deste computador.%n%nVoce pode remover tudo ou escolher componentes. Sera pedida a senha do Ativa Manutencao.
WizardSelectComponents=O que desinstalar
SelectComponentsDesc=Quais componentes devem ser removidos deste computador?
SelectComponentsLabel2=Marque o que deve ser removido e clique em Avancar.%n%nUm componente removido sozinho pode ser reinstalado pelo auto-reparo do Guardian. Para que nada volte, use "Desinstalar tudo".
ComponentsDiskSpaceMBLabel=
ComponentsDiskSpaceGBLabel=
WizardReady=Pronto para desinstalar
ReadyLabel1=O desinstalador esta pronto para remover os componentes escolhidos.
ReadyLabel2b=Clique em Desinstalar. Em seguida sera pedida a senha do Ativa Manutencao.
ButtonInstall=&Desinstalar
WizardPreparing=Conferindo a senha
PreparingDesc=Digite a senha do Ativa Manutencao na janela que abrir.
WizardInstalling=Desinstalando
InstallingLabel=Aguarde enquanto os componentes sao removidos.
FinishedHeadingLabel=Desinstalacao concluida
FinishedLabelNoIcons=Resultado:
ClickFinish=Clique em Concluir para sair.

[Types]
Name: "tudo"; Description: "Desinstalar tudo"
Name: "personalizado"; Description: "Escolher o que desinstalar"; Flags: iscustom

[Components]
Name: "guardian"; Description: "Ativa Guardian (monitoramento e protecao)"; Types: tudo
Name: "updater"; Description: "Ativa Unified Updater (atualizacoes automaticas)"; Types: tudo
Name: "workspace"; Description: "Ativa Workspace"; Types: tudo
Name: "wallpaper"; Description: "Ativa Wallpaper"; Types: tudo
Name: "remote"; Description: "Ativa Remote (RustDesk)"; Types: tudo
Name: "glpiagent"; Description: "GLPI Agent (inventario)"; Types: tudo

[Files]
; Extraidos sob demanda (ExtractTemporaryFile), so para conferir a senha.
Source: "{#GuardianPath}"; DestName: "AtivaGuardianAuth.exe"; Flags: dontcopy
Source: "{#VerifierPath}"; DestName: "verifier.json"; Flags: dontcopy

[Code]
const
  UninstallRoot = 'SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall';
  ArpKey = 'SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\AtivaUnifiedAgent';
  RunKey = 'SOFTWARE\Microsoft\Windows\CurrentVersion\Run';

var
  Summary: String;

procedure Note(const Text: String);
begin
  Summary := Summary + Text + #13#10;
  Log(Text);
end;

procedure Status(const Text: String);
begin
  WizardForm.StatusLabel.Caption := Text;
  Log(Text);
end;

function RunWait(const Filename, Params: String): Integer;
begin
  Log('Executando: ' + Filename + ' ' + Params);
  if not Exec(Filename, Params, '', SW_HIDE, ewWaitUntilTerminated, Result) then
    Result := -1;
  Log('Codigo de saida: ' + IntToStr(Result));
end;

function Sc(const Params: String): Integer;
begin
  Result := RunWait(ExpandConstant('{sys}\sc.exe'), Params);
end;

function ServiceExists(const Name: String): Boolean;
begin
  Result := Sc('query ' + Name) = 0;
end;

function ServiceStopped(const Name: String): Boolean;
var
  ResultCode: Integer;
begin
  Result := Exec(ExpandConstant('{cmd}'),
    '/C ""' + ExpandConstant('{sys}\sc.exe') + '" query ' + Name + ' | "' + ExpandConstant('{sys}\find.exe') + '" "STOPPED""',
    '', SW_HIDE, ewWaitUntilTerminated, ResultCode) and (ResultCode = 0);
end;

{ Para e remove o servico. 1072 = ja marcado para exclusao (some quando os
  ultimos handles fecham). }
function RemoveService(const Name: String): Boolean;
var
  Attempt: Integer;
  Code: Integer;
begin
  Result := True;
  if not ServiceExists(Name) then
    exit;
  Sc('stop ' + Name);
  for Attempt := 1 to 30 do begin
    if ServiceStopped(Name) then
      break;
    Sleep(1000);
  end;
  Code := Sc('delete ' + Name);
  Result := (Code = 0) or (Code = 1072) or not ServiceExists(Name);
end;

procedure KillImage(const Image: String);
begin
  RunWait(ExpandConstant('{sys}\taskkill.exe'), '/F /T /IM "' + Image + '"');
end;

procedure DeleteTask(const Name: String);
begin
  RunWait(ExpandConstant('{sys}\schtasks.exe'), '/Delete /TN "' + Name + '" /F');
end;

{ Apaga a pasta; o que nao der (arquivo em uso) e apagado no proximo boot. }
function RemoveDir(const Path: String): Boolean;
begin
  Result := True;
  if not DirExists(Path) then
    exit;
  DelTree(Path, True, True, True);
  if DirExists(Path) then begin
    RunWait(ExpandConstant('{cmd}'), '/C rmdir /s /q "' + Path + '"');
    Result := not DirExists(Path);
  end;
end;

procedure Report(const Component: String; Ok: Boolean; const Detail: String);
begin
  if Ok then
    Note('OK      ' + Component)
  else
    Note('FALHOU  ' + Component + ' - ' + Detail);
end;

procedure UninstallGuardian();
var
  Exe: String;
  Ok: Boolean;
begin
  Status('Removendo o Ativa Guardian...');
  { A tarefa de protecao primeiro: ela religaria o Guardian a cada minuto. }
  DeleteTask('Ativa Guardian Protection');
  Exe := ExpandConstant('{commonpf}\Ativa Locacao\Guardian\AtivaGuardian.exe');
  if FileExists(Exe) then
    RunWait(Exe, '--uninstall-service');
  Ok := RemoveService('AtivaGuardian');
  KillImage('AtivaGuardian.exe');
  { Copias versionadas usadas pela tarefa de protecao. }
  RunWait(ExpandConstant('{sys}\taskkill.exe'), '/F /FI "IMAGENAME eq AtivaGuardian-*"');
  Ok := RemoveDir(ExpandConstant('{commonpf}\Ativa Locacao\Guardian')) and Ok;
  RemoveDir(ExpandConstant('{commonappdata}\AtivaLocacao\Guardian'));
  DeleteFile(ExpandConstant('{commonprograms}\Ativa\Manutencao Ativa.lnk'));
  RemoveDir(ExpandConstant('{commonprograms}\Ativa'));
  Report('Ativa Guardian', Ok, 'servico ou arquivos nao removidos (veja o log)');
end;

procedure UninstallUpdater();
var
  Ok: Boolean;
begin
  Status('Removendo o Ativa Unified Updater...');
  DeleteTask('Ativa Unified Updater Watchdog');
  Ok := RemoveService('AtivaUnifiedUpdater');
  KillImage('AtivaUnifiedUpdater.exe');
  Ok := RemoveDir(ExpandConstant('{commonappdata}\AtivaLocacao\UnifiedUpdater')) and Ok;
  Report('Ativa Unified Updater', Ok, 'servico ou arquivos nao removidos (veja o log)');
end;

procedure UninstallWorkspace();
var
  Exe: String;
  Ok: Boolean;
begin
  Status('Removendo o Ativa Workspace...');
  Exe := ExpandConstant('{commonappdata}\AtivaLocacao\Workspace\AtivaWorkspace.exe');
  if FileExists(Exe) then
    RunWait(Exe, '--uninstall-service');
  Ok := RemoveService('AtivaWorkspace');
  KillImage('AtivaWorkspace.exe');
  KillImage('AtivaWorkspaceUser.exe');
  Ok := RemoveDir(ExpandConstant('{commonappdata}\AtivaLocacao\Workspace')) and Ok;
  Report('Ativa Workspace', Ok, 'servico ou arquivos nao removidos (veja o log)');
end;

procedure UninstallWallpaper();
var
  Exe: String;
  Ok: Boolean;
begin
  Status('Removendo o Ativa Wallpaper...');
  Exe := ExpandConstant('{commonappdata}\AtivaLocacao\Wallpaper\AtivaWallpaperClient.exe');
  if FileExists(Exe) then
    RunWait(Exe, '--uninstall');
  KillImage('AtivaWallpaperClient.exe');
  DeleteTask('Ativa Wallpaper Logon');
  RegDeleteValue(HKLM64, RunKey, 'AtivaWallpaperClient');
  Ok := RemoveDir(ExpandConstant('{commonappdata}\AtivaLocacao\Wallpaper'));
  Report('Ativa Wallpaper', Ok, 'arquivos nao removidos (veja o log)');
end;

procedure UninstallRemote();
var
  Exe: String;
  Ok: Boolean;
begin
  Status('Removendo o Ativa Remote (RustDesk)...');
  Exe := ExpandConstant('{commonpf}\RustDesk\rustdesk.exe');
  if FileExists(Exe) then
    RunWait(Exe, '--uninstall');
  Ok := RemoveService('RustDesk');
  KillImage('rustdesk.exe');
  Ok := RemoveDir(ExpandConstant('{commonpf}\RustDesk')) and Ok;
  RegDeleteKeyIncludingSubkeys(HKLM64, UninstallRoot + '\RustDesk');
  Report('Ativa Remote (RustDesk)', Ok, 'servico ou arquivos nao removidos (veja o log)');
end;

{ GLPI Agent: MSI oficial. Procura a entrada "GLPI Agent ..." em Aplicativos
  e desinstala pelo codigo do produto (o nome da chave e o proprio GUID). }
procedure UninstallGlpiAgent();
var
  Keys: TArrayOfString;
  I: Integer;
  Name: String;
  Code: Integer;
  Found: Boolean;
  Ok: Boolean;
begin
  Status('Removendo o GLPI Agent...');
  Found := False;
  Ok := True;
  if RegGetSubkeyNames(HKLM64, UninstallRoot, Keys) then
    for I := 0 to GetArrayLength(Keys) - 1 do
      if RegQueryStringValue(HKLM64, UninstallRoot + '\' + Keys[I], 'DisplayName', Name) and
         (Pos('GLPI Agent', Name) = 1) and (Copy(Keys[I], 1, 1) = '{') then begin
        Found := True;
        Code := RunWait(ExpandConstant('{sys}\msiexec.exe'), '/x ' + Keys[I] + ' /qn /norestart');
        { 1605 = ja nao instalado; 3010/1641 = remove ao reiniciar }
        if (Code <> 0) and (Code <> 1605) and (Code <> 3010) and (Code <> 1641) then
          Ok := False;
      end;
  if not Found then
    Note('OK      GLPI Agent (nao estava instalado)')
  else
    Report('GLPI Agent', Ok, 'msiexec falhou (veja o log)');
end;

function RemoveEverything(): Boolean;
begin
  Result := WizardIsComponentSelected('guardian') and WizardIsComponentSelected('updater') and
    WizardIsComponentSelected('workspace') and WizardIsComponentSelected('wallpaper') and
    WizardIsComponentSelected('remote') and WizardIsComponentSelected('glpiagent');
end;

{ Antes de remover qualquer coisa: senha do Ativa Manutencao. }
function PrepareToInstall(var NeedsRestart: Boolean): String;
var
  Code: Integer;
begin
  Result := '';
  ExtractTemporaryFile('AtivaGuardianAuth.exe');
  ExtractTemporaryFile('verifier.json');
  if not Exec(ExpandConstant('{tmp}\AtivaGuardianAuth.exe'),
      '--authorize-uninstall --verifier-file "' + ExpandConstant('{tmp}\verifier.json') + '"',
      '', SW_HIDE, ewWaitUntilTerminated, Code) then
    Code := -1;
  Log('Autorizacao da desinstalacao: codigo ' + IntToStr(Code));
  if Code = 5 then
    Result := 'Senha incorreta ou cancelada. Nada foi removido.'
  else if Code <> 0 then
    Result := 'Nao foi possivel conferir a senha do Ativa Manutencao (codigo ' + IntToStr(Code) + '). Nada foi removido.';
end;

procedure CurStepChanged(CurStep: TSetupStep);
var
  ResultCodeDummy: Integer;
begin
  if CurStep <> ssInstall then
    exit;
  Summary := '';
  { Guardian e Updater primeiro: os dois reinstalam/religam o que sumir. }
  if WizardIsComponentSelected('guardian') then UninstallGuardian();
  if WizardIsComponentSelected('updater') then UninstallUpdater();
  if WizardIsComponentSelected('workspace') then UninstallWorkspace();
  if WizardIsComponentSelected('wallpaper') then UninstallWallpaper();
  if WizardIsComponentSelected('remote') then UninstallRemote();
  if WizardIsComponentSelected('glpiagent') then UninstallGlpiAgent();

  if RemoveEverything() then begin
    Status('Removendo os ultimos arquivos...');
    RegDeleteKeyIncludingSubkeys(HKLM64, ArpKey);
    RemoveDir(ExpandConstant('{commonappdata}\AtivaLocacao'));
    { A pasta do proprio desinstalador (em uso agora) sai depois que ele fechar. }
    Exec(ExpandConstant('{cmd}'),
      '/C ping -n 6 127.0.0.1 >nul & rmdir /s /q "' + ExpandConstant('{commonpf}\Ativa Locacao') + '"',
      '', SW_HIDE, ewNoWait, ResultCodeDummy);
  end;
end;

procedure CurPageChanged(CurPageID: Integer);
begin
  if (CurPageID = wpFinished) and (Summary <> '') then
    WizardForm.FinishedLabel.Caption := 'Resultado:' + #13#10#13#10 + Summary;
end;
