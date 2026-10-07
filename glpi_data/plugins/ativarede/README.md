# Ativa Rede

Mostra, na planta de cada sala, **em qual porta do switch está cada mesa**, se a
máquina da mesa está ligada, quais **monitores** estão nela e avisa quando um
**computador ou monitor muda de mesa**.

## Como funciona

```
Máquina (Ativa Guardian 1.6.0+)                 GLPI (Ativa Rede)
──────────────────────────────                 ─────────────────
escuta o LLDP do switch (pktmon) ──┐            ┌─► compara com a última posição
lê monitores (WMI) e série da BIOS ┼─► /report ─┤   mudou? → alerta
                                   ┘            └─► planta, equipamentos, aba do Computador
```

- **Switch e porta:** o switch anuncia em cada porta, a cada ~30 s, um quadro LLDP
  ("sou o switch X, esta é a porta 15"). O Guardian só escuta esse quadro com o
  `pktmon` do Windows. Nada é enviado para a rede, nada é instalado e o switch não
  precisa de SNMP (funciona com o Instant On no modo nuvem).
- **Monitores:** fabricante, modelo, número de série, ano e tipo de conexão (WMI).
  A tela interna de notebook fica de fora.
- **Computador do GLPI:** vinculado pela série da BIOS (inventário do GLPI Agent) ou,
  na falta, pelo nome. Só vincula quando há exatamente um candidato.
- **Envio:** o Guardian coleta a cada 15 min e envia só quando algo mudou (ou a cada 6 h).

## Alertas

| Alerta | Quando |
|---|---|
| Computador mudou de mesa | apareceu em outra porta em **2 relatórios seguidos** |
| Monitor mudou de mesa | o número de série apareceu em outro computador |
| Monitor novo | série nunca vista, em máquina já conhecida |
| Monitor ausente | sumiu da máquina (que segue ligada) há 2 dias |
| Porta compartilhada | mais de uma máquina na mesma porta (mini switch) |
| Mesa vazia | o computador saiu da mesa e ninguém ocupou a porta em 3 dias |
| Máquina sem relatório | 7 dias sem enviar a posição |

Cada alerta pode ser **autorizado** (mudança de computador atualiza a Localização
dele para a da planta), virar **chamado** ou ser **ignorado**. Os prazos ficam em
`glpi_configs`, contexto `plugin:ativarede`.

O primeiro relatório de cada máquina só registra a situação atual (não há "de
onde" comparar). Wi-Fi ou cabo sem LLDP mantém a última posição conhecida.

## Instalação

1. Requisitos: GLPI 11, **Ativa Guardian ativo** (a API usa o mesmo token).
2. `./update-glpi.sh ativarede` no servidor (instala e ativa).
3. Perfil → aba **Ativa Rede**: "Visualizar" para quem só consulta; "Editar" para a T.I.
4. Publicar o pacote unificado **1.8.6** (Guardian 1.6.0) pelo Ativa Updater.

A planta da **Sala principal - Anexo** já vem com as 54 mesas do layout
(A1–A6, B1–B2, C1–C4 e ilhas D a J, posições 1–3 à esquerda e 4–6 à direita).

## Mapeando as mesas

1. Depois que as máquinas enviarem a posição, abra **Ativa Rede → Planta → Editar planta**.
2. A lista "Portas com máquina e sem mesa" mostra cada porta com o computador e o
   usuário que estão nela. Selecione a mesa na planta e clique em **Usar na mesa**.
3. Em **Equipamentos → Switches**, dê um apelido curto a cada switch (aparece nas mesas).

## API

`POST /plugins/ativarede/api/v1/report` — `Authorization: Bearer <token do Ativa Guardian>`

```json
{
  "machine_id": "ID-PERSISTENTE-DO-GUARDIAN",
  "hostname": "ATV-045",
  "guardian_version": "1.6.0",
  "bios_serial": "GHC4Q74",
  "network": {
    "link": "wired",
    "ip": "192.168.80.231",
    "mac": "D0:C1:B5:7D:F3:EB",
    "lldp": {
      "chassis_id": "14:AB:EC:22:C9:EC",
      "port_id": "14:AB:EC:22:C9:FB",
      "port_description": "15",
      "system_name": "TW46LNT1GH",
      "system_description": "HPE Networking Instant On Switch 24p Gigabit 4p SFP+ 1930 JL682A",
      "mgmt_ip": "192.168.80.43"
    }
  },
  "monitors": [
    { "manufacturer": "DEL", "product_code": "423E", "serial": "CJCN8Q3",
      "model": "DELL P2222H", "year": 2024, "week": 30, "connection": "HDMI" }
  ]
}
```

- `link`: `wired`, `wifi`, `none` ou `unknown`. `lldp` só é considerado com `wired`.
- Até 8 monitores; corpo até 32 KB. Fora do padrão → `422` e nada é gravado.
- Respostas: `202 {"ok": true, "events": N}`; `401/403` token; `503` Guardian inativo ou API desligada.
- `GET /plugins/ativarede/api/v1/health` (sem token).

## Validação numa máquina

Em um Prompt **como administrador**:

```
"C:\Program Files\Ativa Locacao\Guardian\AtivaGuardian.exe" --network
```

Mostra o que seria enviado (leva ~35 s). Em `network.lldp` devem aparecer o switch e a porta.
Repita numa máquina de cada switch, numa ligada pelo telefone IP e num notebook na dock.

## Limitações

- Máquina desligada não informa nada; a mudança aparece quando ela ligar.
- Notebook só no Wi-Fi não tem porta de switch (aparece como "Só Wi-Fi").
- Monitor sem número de série (VGA/adaptador/genérico) só é reconhecido dentro da própria máquina.
- Máquina atrás de mini switch ou telefone IP pode receber o LLDP do aparelho intermediário.
- Se alguém estiver usando o `pktmon` na máquina, a coleta daquele ciclo é pulada.
