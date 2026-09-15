document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-ativa-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      if (!window.confirm(form.dataset.ativaConfirm || 'Confirmar esta acao?')) event.preventDefault();
    });
  });

  document.querySelectorAll('[data-ativa-upload-form]').forEach((form) => {
    const input = form.querySelector('[data-ativa-image-input]');
    const preview = form.querySelector('[data-ativa-image-preview]');
    const empty = form.querySelector('[data-ativa-preview-empty]');
    const error = form.querySelector('[data-ativa-upload-error]');
    if (!input || !preview || !error) return;
    input.addEventListener('change', () => {
      input.classList.remove('is-invalid');
      error.textContent = '';
      preview.classList.add('d-none');
      if (empty) empty.classList.remove('d-none');
      const file = input.files && input.files[0];
      if (!file) return;
      const maxBytes = Number(form.dataset.maxBytes || 0);
      if (!['image/jpeg', 'image/png'].includes(file.type) || (maxBytes > 0 && file.size > maxBytes)) {
        input.classList.add('is-invalid');
        error.textContent = maxBytes > 0 && file.size > maxBytes
          ? 'O arquivo excede o limite configurado.'
          : 'Selecione uma imagem JPG ou PNG.';
        input.value = '';
        return;
      }
      const objectUrl = URL.createObjectURL(file);
      preview.onload = () => URL.revokeObjectURL(objectUrl);
      preview.src = objectUrl;
      preview.classList.remove('d-none');
      if (empty) empty.classList.add('d-none');
    });
  });

  const updateMonitor = document.querySelector('[data-ativa-update-monitor]');
  if (updateMonitor) {
    const componentLabels = { wallpaper_client: 'Wallpaper Client', glpi_agent: 'GLPI Agent' };
    const statusLabels = {
      offered: ['Aguardando computador', 'secondary'], downloading: ['Baixando', 'info'],
      installing: ['Instalando', 'primary'], success: ['Concluido', 'success'],
      error: ['Problema', 'danger'], restart_required: ['Reinicio necessario', 'warning'],
    };
    const stageLabels = {
      draft: ['Rascunho', 'secondary'], pilot: ['Somente piloto', 'warning'],
      all: ['Todos os computadores', 'success'], paused: ['Pausado', 'secondary'],
    };
    let latestUpdateData = { packages: [], installations: [] };
    const setUpdateText = (selector, value) => {
      const element = updateMonitor.querySelector(selector);
      if (element) element.textContent = String(value);
    };
    const renderOverall = (packages, installations) => {
      const overall = updateMonitor.querySelector('[data-update-overall]');
      if (!overall) return;
      const requestedId = Number(overall.dataset.packageId || 0);
      const item = packages.find((candidate) => Number(candidate.id) === requestedId)
        || packages.find((candidate) => ['pilot', 'all'].includes(candidate.release_stage))
        || packages[0];
      if (!item || !item.summary) {
        setUpdateText('[data-update-overall-title]', 'Envie uma atualizacao para comecar');
        setUpdateText('[data-update-overall-percentage]', '0%');
        setUpdateText('[data-update-overall-message]', 'Nenhum pacote disponivel.');
        return;
      }
      overall.dataset.packageId = String(item.id);
      const summary = item.summary;
      const total = Number(summary.total || 0);
      const processed = Number(summary.processed || 0);
      const percentage = Number(summary.percentage || 0);
      const installing = Number(summary.downloading || 0) + Number(summary.installing || 0);
      const problemsCount = Number(summary.error || 0) + Number(summary.restart_required || 0);
      setUpdateText('[data-update-overall-title]', `${componentLabels[item.component] || item.component} ${item.version}`);
      setUpdateText('[data-update-overall-percentage]', `${percentage.toLocaleString('pt-BR', { maximumFractionDigits: 1 })}%`);
      setUpdateText('[data-update-overall-success]', summary.success || 0);
      setUpdateText('[data-update-overall-pending]', summary.offered || 0);
      setUpdateText('[data-update-overall-installing]', installing);
      setUpdateText('[data-update-overall-problem-count]', problemsCount);

      const state = updateMonitor.querySelector('[data-update-overall-state]');
      const stage = stageLabels[item.release_stage] || [item.release_stage, 'secondary'];
      if (state) {
        state.textContent = stage[0];
        state.className = `badge bg-${stage[1]}`;
      }
      const message = total === 0
        ? (item.release_stage === 'draft' ? 'O pacote ainda precisa ser liberado.' : 'Aguardando computadores elegiveis.')
        : processed < total
          ? `${processed} de ${total} computador(es) finalizaram. O restante sera atualizado na proxima verificacao.`
          : problemsCount > 0
            ? `Verificacao encerrada: ${problemsCount} computador(es) precisam de atencao. Veja o motivo abaixo.`
            : `Concluido: a versao esta instalada em todos os ${total} computador(es).`;
      setUpdateText('[data-update-overall-message]', message);

      const bar = updateMonitor.querySelector('[data-update-overall-bar]');
      if (bar) {
        bar.style.width = `${percentage}%`;
        bar.classList.remove('bg-primary', 'bg-success', 'bg-danger', 'bg-warning');
        bar.classList.toggle('progress-bar-animated', processed < total && item.release_stage !== 'draft');
        bar.classList.add(problemsCount > 0 ? 'bg-danger' : (processed === total && total > 0 ? 'bg-success' : 'bg-primary'));
        bar.closest('[role="progressbar"]')?.setAttribute('aria-valuenow', String(percentage));
      }

      const problems = updateMonitor.querySelector('[data-update-problems]');
      if (!problems) return;
      problems.replaceChildren();
      const affected = installations.filter((entry) => Number(entry.update_packages_id) === Number(item.id)
        && ['error', 'restart_required'].includes(entry.status));
      if (!affected.length) {
        problems.className = 'small text-success mt-2';
        problems.textContent = 'Nenhum computador apresentou problema nesta distribuicao.';
        return;
      }
      problems.className = 'mt-2';
      const list = document.createElement('ul');
      list.className = 'list-unstyled mb-0';
      affected.forEach((entry) => {
        const line = document.createElement('li');
        line.className = entry.status === 'error' ? 'text-danger mb-1' : 'text-warning mb-1';
        const reason = entry.message || (entry.status === 'restart_required'
          ? 'O computador precisa ser reiniciado para concluir.'
          : 'O cliente nao informou detalhes adicionais.');
        line.textContent = `${entry.hostname}: ${reason}`;
        list.append(line);
      });
      problems.append(list);
    };
    const fillInstallations = (items) => {
      const body = updateMonitor.querySelector('[data-update-installations]');
      if (!body) return;
      body.replaceChildren();
      if (!items.length) {
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.colSpan = 6;
        cell.className = 'text-center text-muted py-5';
        cell.textContent = 'Nenhuma atualizacao oferecida aos clientes.';
        row.append(cell);
        body.append(row);
        return;
      }
      items.forEach((item) => {
        const row = document.createElement('tr');
        const values = [item.hostname, componentLabels[item.component] || item.component];
        values.forEach((value, index) => {
          const cell = document.createElement('td');
          if (index === 0) {
            const strong = document.createElement('strong');
            strong.textContent = value;
            cell.append(strong);
          } else cell.textContent = value;
          row.append(cell);
        });
        const version = document.createElement('td');
        version.textContent = `${item.from_version || '-'} → ${item.to_version}`;
        row.append(version);
        const status = document.createElement('td');
        const badge = document.createElement('span');
        const definition = statusLabels[item.status] || [item.status, 'secondary'];
        badge.className = `badge bg-${definition[1]}`;
        badge.textContent = definition[0];
        status.append(badge);
        row.append(status);
        const updated = document.createElement('td');
        updated.textContent = item.updated_at || '-';
        row.append(updated);
        const message = document.createElement('td');
        message.className = 'ativa-error';
        message.textContent = item.message || '-';
        message.title = item.message || '';
        row.append(message);
        body.append(row);
      });
    };
    const refreshUpdates = async () => {
      try {
        const response = await fetch(updateMonitor.dataset.progressUrl, {
          credentials: 'same-origin', cache: 'no-store', headers: { Accept: 'application/json' },
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const data = await response.json();
        const packages = Array.isArray(data.packages) ? data.packages : [];
        const installations = Array.isArray(data.installations) ? data.installations : [];
        latestUpdateData = { packages, installations };
        packages.forEach((item) => {
          const row = updateMonitor.querySelector(`[data-update-package="${Number(item.id)}"]`);
          if (!row || !item.summary) return;
          const set = (selector, value) => { const element = row.querySelector(selector); if (element) element.textContent = String(value); };
          set('[data-update-offered]', item.summary.offered || 0);
          set('[data-update-installing]', Number(item.summary.downloading || 0) + Number(item.summary.installing || 0));
          set('[data-update-success]', item.summary.success || 0);
          set('[data-update-error]', item.summary.error || 0);
          set('[data-update-restart]', item.summary.restart_required || 0);
          set('[data-update-percentage]', `${Number(item.summary.percentage || 0).toLocaleString('pt-BR', { maximumFractionDigits: 1 })}%`);
          const bar = row.querySelector('[data-update-bar]');
          if (bar) {
            bar.style.width = `${Number(item.summary.percentage || 0)}%`;
            bar.closest('[role="progressbar"]')?.setAttribute('aria-valuenow', String(item.summary.percentage || 0));
          }
        });
        renderOverall(packages, installations);
        fillInstallations(installations);
        const refreshed = updateMonitor.querySelector('[data-update-last-refresh]');
        if (refreshed) refreshed.textContent = `Atualizado automaticamente em ${new Date().toLocaleTimeString('pt-BR')}. Proxima consulta em 3 segundos.`;
      } catch (error) {
        const refreshed = updateMonitor.querySelector('[data-update-last-refresh]');
        if (refreshed) refreshed.textContent = `Falha temporaria no monitoramento: ${error.message}.`;
      } finally {
        window.setTimeout(refreshUpdates, 3000);
      }
    };
    updateMonitor.querySelectorAll('[data-update-select]').forEach((button) => {
      button.addEventListener('click', () => {
        const overall = updateMonitor.querySelector('[data-update-overall]');
        if (overall) overall.dataset.packageId = button.dataset.updateSelect || '';
        renderOverall(latestUpdateData.packages, latestUpdateData.installations);
        overall?.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    });
    window.setTimeout(refreshUpdates, 500);
  }

  const overview = document.querySelector('[data-awp-overview]');
  if (!overview) return;

  const REFRESH_MS = 3000;
  const HIDDEN_REFRESH_MS = 15000;
  const lastSections = new Map();
  let pollTimer = null;
  let polling = false;
  let messageTimer = null;

  const csrfToken = () => document.querySelector('meta[property="glpi:csrf_token"]')?.getAttribute('content') || '';
  const liveBadge = overview.querySelector('[data-awp-live]');
  const liveText = overview.querySelector('[data-awp-live-text]');

  const showMessage = (text, tone = 'success') => {
    const box = overview.querySelector('[data-awp-message]');
    if (!box || !text) return;
    box.className = `awp-alert is-${tone}`;
    box.textContent = text;
    window.clearTimeout(messageTimer);
    messageTimer = window.setTimeout(() => box.classList.add('d-none'), 7000);
  };

  // Countdowns of the verification cycle ("00:50 para começar", "00:12 para o próximo")
  // run against the server clock, corrected by the offset of this browser.
  let clockOffsetMs = Number(overview.dataset.serverEpoch || 0) * 1000 - Date.now();
  const expiredCountdowns = new Set();
  const pad = (value) => String(value).padStart(2, '0');
  const tickCountdowns = () => {
    const nowMs = Date.now() + clockOffsetMs;
    overview.querySelectorAll('[data-awp-countdown]').forEach((element) => {
      const until = Number(element.dataset.awpCountdown || 0);
      const remaining = Math.max(0, Math.ceil((until * 1000 - nowMs) / 1000));
      element.textContent = `${pad(Math.floor(remaining / 60))}:${pad(remaining % 60)}`;
      if (remaining === 0 && until > 0 && !expiredCountdowns.has(until)) {
        // The cycle moves on now: ask the server instead of waiting for the next poll.
        expiredCountdowns.add(until);
        window.setTimeout(refreshNow, 800);
      }
    });
  };

  const applySections = (sections) => {
    Object.entries(sections || {}).forEach(([name, html]) => {
      if (lastSections.get(name) === html) return;
      const container = overview.querySelector(`[data-awp-section="${name}"]`);
      if (!container) return;
      lastSections.set(name, html);
      container.innerHTML = html;
    });
    tickCountdowns();
  };

  const schedule = (delay) => {
    window.clearTimeout(pollTimer);
    pollTimer = window.setTimeout(poll, delay ?? (document.hidden ? HIDDEN_REFRESH_MS : REFRESH_MS));
  };

  async function poll() {
    if (polling) return;
    polling = true;
    try {
      const response = await fetch(overview.dataset.endpoint + window.location.search, {
        credentials: 'same-origin', cache: 'no-store',
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      });
      if (!response.ok) throw new Error(`HTTP ${response.status}`);
      const data = await response.json();
      if (data.server_epoch) clockOffsetMs = Number(data.server_epoch) * 1000 - Date.now();
      applySections(data.sections);
      liveBadge?.classList.remove('is-offline');
      if (liveText) liveText.textContent = `Ao vivo • ${data.time}`;
    } catch (error) {
      liveBadge?.classList.add('is-offline');
      if (liveText) liveText.textContent = 'Reconectando...';
    } finally {
      polling = false;
      schedule();
    }
  }

  // Refresh right after an action (Aplicar novamente, reaplicar, revogar).
  const refreshNow = () => {
    window.clearTimeout(pollTimer);
    if (polling) {
      schedule(300);
    } else {
      poll();
    }
  };

  const postAction = async (fields) => {
    const body = new FormData();
    Object.entries(fields).forEach(([key, value]) => body.append(key, value));
    const response = await fetch(overview.dataset.actionUrl, {
      method: 'POST', body, credentials: 'same-origin', cache: 'no-store',
      headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-Glpi-Csrf-Token': csrfToken() },
    });
    const contentType = response.headers.get('Content-Type') || '';
    if (!contentType.includes('application/json')) throw new Error(`Resposta inesperada do servidor (HTTP ${response.status}).`);
    const payload = await response.json();
    if (!response.ok || payload.ok === false) throw new Error(payload.message || `HTTP ${response.status}`);
    return payload;
  };

  overview.addEventListener('click', async (event) => {
    const copy = event.target.closest('[data-awp-copy]');
    if (copy) {
      const value = copy.dataset.awpCopy || '';
      try {
        await navigator.clipboard.writeText(value);
        showMessage('SHA-256 copiado.');
      } catch (error) {
        window.prompt('Copie o SHA-256:', value);
      }
      return;
    }

    if (event.target.closest('[data-awp-change-wallpaper]')) {
      const publish = document.getElementById('awp-publish');
      publish?.scrollIntoView({ behavior: 'smooth', block: 'center' });
      publish?.querySelector('[data-ativa-image-input]')?.click();
      return;
    }

    const clientAction = event.target.closest('[data-awp-client-action]');
    if (clientAction) {
      if (clientAction.dataset.confirm && !window.confirm(clientAction.dataset.confirm)) return;
      clientAction.disabled = true;
      try {
        const payload = await postAction({ action: clientAction.dataset.awpClientAction, id: clientAction.dataset.id });
        showMessage(payload.message || 'Solicitação enviada.');
      } catch (error) {
        showMessage(error.message, 'error');
      } finally {
        clientAction.disabled = false;
        refreshNow();
      }
    }
  });

  overview.querySelectorAll('[data-awp-force-all]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      const button = form.querySelector('button[type="submit"]');
      if (!button || button.disabled) return;
      const label = button.innerHTML;
      button.disabled = true;
      button.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>Reiniciando...';
      try {
        const payload = await postAction({ action: 'force_all' });
        showMessage(payload.message || 'Nova análise iniciada em todos os computadores.');
      } catch (error) {
        showMessage(error.message, 'error');
      } finally {
        // Stays available: another click cancels this cycle and starts over again.
        button.disabled = false;
        button.innerHTML = label;
        refreshNow();
      }
    });
  });

  overview.querySelectorAll('[data-awp-dropzone]').forEach((zone) => {
    const input = zone.querySelector('input[type="file"]');
    if (!input) return;
    ['dragenter', 'dragover'].forEach((type) => zone.addEventListener(type, (event) => {
      event.preventDefault();
      zone.classList.add('is-dragging');
    }));
    ['dragleave', 'dragend', 'drop'].forEach((type) => zone.addEventListener(type, () => zone.classList.remove('is-dragging')));
    zone.addEventListener('drop', (event) => {
      event.preventDefault();
      if (!event.dataTransfer || !event.dataTransfer.files.length) return;
      input.files = event.dataTransfer.files;
      input.dispatchEvent(new Event('change', { bubbles: true }));
    });
  });

  document.addEventListener('visibilitychange', () => {
    if (!document.hidden) refreshNow();
  });
  tickCountdowns();
  window.setInterval(tickCountdowns, 1000);
  schedule(REFRESH_MS);
});
