# Changelog

## Em desenvolvimento

- Botao unico **Aplicar em todos** para solicitar a reaplicacao do wallpaper
  atual em todos os clientes nao revogados.
- Diagnostico explicito de falta de espaco e permissoes no armazenamento
  privado durante uploads.

## 1.0.0 - 2026-09-08

- Plugin GLPI 11 instalavel com menu, direitos de perfil e aba no computador.
- Upload JPG/PNG validado, thumbnail, SHA-256, versao e rollback com auditoria.
- Dashboard paginado com status atualizado, pendente, offline e erro.
- API v1 HTTPS com registro, bearer token com hash, rate limiting, ETag e 304.
- Cliente Windows 10/11 com instalacao SYSTEM, execucao no usuario, troca atomica,
  politicas conservadoras, backoff, jitter e logs rotativos.
- Build PyInstaller, pacote de bootstrap e roteiro de rollout piloto.
