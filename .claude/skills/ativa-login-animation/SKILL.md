---
name: ativa-login-animation
description: Use esta skill ao implementar, corrigir ou testar a animação e identidade visual da Ativa na tela de login do GLPI, especialmente o plugin ativasplash.
---

# GLPI ATIVA - Login Animation

## Objetivo

A sequência visual correta é obrigatoriamente:

vídeo da Ativa
→ logo horizontal
→ logo vertical
→ logo diminui e sobe
→ ocupa o lugar da antiga logo GLPI
→ login permanece com a logo Ativa

## Estado final do vídeo

O vídeo existente NÃO deve ser alterado.

Ele termina com:

[símbolo]   Ativa
            Locação

Esse é o estado inicial da animação feita em código.

## Transformação horizontal para vertical

Depois do vídeo:

[símbolo]   Ativa
            Locação

deve se transformar suavemente em:

       [símbolo]

         Ativa
        Locação

O símbolo e os textos devem ser elementos manipuláveis.

Não fazer apenas crossfade entre duas imagens.

Preferir SVG/DOM com:

- símbolo
- texto Ativa
- texto Locação

O grupo "Ativa Locação" deve realmente se mover da direita para baixo do símbolo.

## Movimento para o login

Depois que a logo vertical estiver formada:

1. reduzir a logo suavemente;
2. mover a logo inteira para cima;
3. levar até o local onde originalmente ficava a logo GLPI;
4. manter uma logo permanente da Ativa nessa posição.

Usar a posição real do elemento destino.

Preferir:

getBoundingClientRect()

e técnica FLIP quando apropriado.

Evitar coordenadas fixas frágeis.

## Logo GLPI

A logo GLPI deve ser ocultada somente na página de login.

Não alterar o core do GLPI.

A logo permanente da Ativa deve continuar aparecendo mesmo quando:

- a splash já foi executada;
- sessionStorage pular a animação;
- vídeo falhar;
- autoplay falhar;
- prefers-reduced-motion estiver ativo.

## Animação

Priorizar:

transform
opacity

Easing preferencial:

cubic-bezier(.22, 1, .36, 1)

Transformação horizontal → vertical:

aproximadamente 450–700ms

Movimento logo → header:

aproximadamente 400–600ms

A transição deve ser fluida, rápida e sem pausas artificiais.

## Regras obrigatórias

- Não alterar o vídeo existente.
- Não alterar o core do GLPI.
- Trabalhar apenas no plugin ativasplash quando possível.
- Não alterar autenticação.
- Não alterar banco de dados.
- Não alterar formulário sem necessidade.
- Não fazer refatorações não relacionadas.
- Não adicionar bibliotecas pesadas.
- Não criar animação 3D.
- Não redesenhar a identidade da Ativa.

## Segurança

A splash nunca pode bloquear permanentemente o login.

Manter:

- timeout de segurança;
- tratamento de video.error;
- tratamento de autoplay recusado;
- prefers-reduced-motion;
- sessionStorage.

Qualquer erro deve resultar em:

splash removida
→ login funcional
→ logo permanente da Ativa visível

## Processo antes de editar

Antes de modificar qualquer código:

1. analisar o plugin ativasplash atual;
2. localizar CSS e JS envolvidos;
3. identificar como o vídeo termina;
4. identificar a logo GLPI no DOM;
5. identificar o destino real da logo Ativa;
6. explicar brevemente o plano;
7. somente depois implementar.

Faça a menor alteração possível.

## Validação

Depois de implementar:

- verificar console por erros;
- testar primeira abertura;
- testar F5;
- testar nova sessão;
- testar Chrome;
- testar Edge;
- testar 1920x1080;
- testar 1366x768;
- testar vídeo indisponível;
- confirmar que a logo GLPI não reaparece.