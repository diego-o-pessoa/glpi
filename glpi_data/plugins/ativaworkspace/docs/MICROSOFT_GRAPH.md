# Microsoft Graph e Temporary Access Pass

O Ativa Workspace usa uma credencial de aplicativo (client credentials) para
criar um Temporary Access Pass (TAP) no momento em que o técnico solicita. A
credencial fica somente no servidor GLPI. Ela não entra no instalador e não é
enviada ao serviço Windows.

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
