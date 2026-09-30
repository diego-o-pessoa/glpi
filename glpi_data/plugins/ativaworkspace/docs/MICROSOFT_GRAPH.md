# Microsoft Graph e Temporary Access Pass

O Ativa Workspace usa uma credencial de aplicativo (client credentials) para
criar um Temporary Access Pass (TAP) quando a etapa Microsoft Entra precisa
ingressar o dispositivo. Client ID/Secret ficam somente no servidor GLPI e
nunca entram no instalador. Apenas o TAP temporário é enviado ao executor da
máquina, que o entrega ao helper efêmero, apaga o arquivo e não o registra.

## Configuração no Microsoft Entra

1. Crie um registro de aplicativo de tenant único chamado `Ativa Workspace`.
2. Guarde o `Application (client) ID` e o `Directory (tenant) ID`.
3. Crie um Client Secret e copie o campo **Value** enquanto ele estiver visível.
4. Em Microsoft Graph > Application permissions, conceda
   `UserAuthMethod-TAP.ReadWrite.All` e aplique o consentimento administrativo.
   A permissão ampla antiga `UserAuthenticationMethod.ReadWrite.All` também é
   reconhecida, mas não é a recomendada para um aplicativo que só cria TAPs.
5. Em Authentication methods > Policies, habilite Temporary Access Pass e
   inclua o grupo que contém os usuários provisionados.
6. Garanta que a validade mínima e máxima da política aceite o valor escolhido
   no Workspace.

## Configuração no GLPI

1. Abra Ativa Workspace > Configurações.
2. Preencha o Tenant ID e salve a configuração Entra.
3. Preencha o Client ID, o **Value** do Client Secret e a validade.
4. Salve e clique em **Testar conexão**.

Não copie a `glpicrypt.key`. O GLPI usa essa chave automaticamente para
proteger o Client Secret no banco de dados. A chave deve permanecer no servidor,
legível apenas pelo processo do GLPI e incluída no backup seguro da instalação.

## Uso do TAP

O TAP criado pelo Workspace é de uso único e nunca é salvo nos logs. Um novo
TAP substitui o anterior ainda válido. Se o Windows pedir a credencial outra
vez durante o ingresso ou a configuração do Windows Hello, gere um novo TAP.

Antes de liberar para todos, valide com um usuário piloto que esteja incluído
na política do TAP e acompanhe o evento de geração nos Logs do Workspace.

## Outlook PWA

A configuração `Outlook PWA` deve ficar depois de `Autenticação Microsoft` no
perfil. O executor:

1. confirma que o computador está no tenant esperado e que existe uma sessão
   do usuário Entra;
2. instala o Google Chrome pelo winget, se necessário;
3. ativa `CloudAPAuthEnabled` e mescla o Outlook em
   `WebAppInstallForceList`, preservando outras PWAs corporativas;
4. reinicia o Chrome na sessão do usuário, abre o Outlook e aguarda a PWA;
5. confirma a janela autenticada pelo SSO e fixa o atalho na barra de tarefas.

O TAP não é reutilizado no navegador. A conta do Windows fornece o SSO ao
Chrome; por isso nenhuma senha permanente é armazenada, digitada ou exibida.
