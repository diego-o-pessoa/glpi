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

  const rollout = document.querySelector('[data-ativa-rollout]');
  if (!rollout) return;

  const forceForms = Array.from(document.querySelectorAll('[data-ativa-force-all]'));
  const machineStates = new Map();
  const machineCycles = new Map();
  const completedRollouts = new Set();
  let currentRolloutId = '';
  let pollTimer = null;
  let requestRunning = false;

  const statuses = {
    pending: { icon: 'ti-clock text-warning', label: 'aguardando consulta da API' },
    applying: { icon: 'ti-loader-2 text-primary ativa-spin', label: 'aplicando agora' },
    success: { icon: 'ti-circle-check text-success', label: 'atualizado' },
    error: { icon: 'ti-alert-circle text-danger', label: 'erro' },
  };
  const cycleActions = {
    already_current: { message: 'verificado; wallpaper ja estava correto, nenhuma aplicacao foi necessaria', className: 'text-muted' },
    initial_applied: { message: 'wallpaper aplicado pela primeira vez', className: 'text-success' },
    configuration_applied: { message: 'nova configuracao aplicada', className: 'text-success' },
    forced_applied: { message: 'reaplicacao solicitada concluida', className: 'text-success' },
    drift_corrected: { message: 'alteracao detectada e wallpaper corporativo restaurado', className: 'text-warning' },
    disabled: { message: 'distribuicao desativada; nenhuma aplicacao realizada', className: 'text-muted' },
    error: { message: 'falha durante a verificacao', className: 'text-danger' },
  };

  const setText = (selector, value) => {
    const element = rollout.querySelector(selector);
    if (element) element.textContent = String(value);
  };
  const timestamp = () => new Intl.DateTimeFormat('pt-BR', {
    hour: '2-digit', minute: '2-digit', second: '2-digit',
  }).format(new Date());
  const formatDuration = (value) => {
    const seconds = Math.max(0, Math.round(Number(value) || 0));
    const minutes = Math.floor(seconds / 60);
    return `${String(minutes).padStart(2, '0')}:${String(seconds % 60).padStart(2, '0')}`;
  };
  const appendActivity = (message, className = '') => {
    const list = rollout.querySelector('[data-rollout-events]');
    if (!list) return;
    const item = document.createElement('li');
    if (className) item.className = className;
    item.textContent = `${timestamp()} — ${message}`;
    list.prepend(item);
    while (list.children.length > 100) list.lastElementChild?.remove();
  };

  const setForceButtons = (active) => {
    forceForms.forEach((form) => {
      const button = form.querySelector('button[type="submit"]');
      if (!button) return;
      button.disabled = active;
      button.innerHTML = active
        ? '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Aplicacao em andamento'
        : '<i class="ti ti-device-desktop-check me-1"></i>Aplicar novamente';
    });
  };

  const renderCycleActivity = (machines) => {
    machines.forEach((machine) => {
      if (!machine.last_cycle_at || !machine.last_cycle_action) return;
      const key = `${machine.last_cycle_at}|${machine.last_cycle_action}`;
      if (machineCycles.get(machine.hostname) === key) return;
      machineCycles.set(machine.hostname, key);
      const cycle = cycleActions[machine.last_cycle_action];
      if (cycle) appendActivity(`${machine.hostname}: ${cycle.message}`, cycle.className);
    });
  };

  const renderMachines = (machines) => {
    const list = rollout.querySelector('[data-rollout-machines]');
    if (!list) return;
    list.replaceChildren();
    machines.forEach((machine) => {
      const status = statuses[machine.status] || statuses.pending;
      const previous = machineStates.get(machine.hostname);
      if (previous !== machine.status) {
        appendActivity(`${machine.hostname}: ${status.label}`, machine.status === 'error' ? 'text-danger' : '');
        machineStates.set(machine.hostname, machine.status);
      }
      const item = document.createElement('li');
      item.className = 'ativa-rollout-machine';
      item.dataset.status = machine.status;
      const line = document.createElement('div');
      const icon = document.createElement('i');
      icon.className = `ti ${status.icon}`;
      const name = document.createElement('strong');
      name.textContent = machine.hostname;
      const label = document.createElement('span');
      label.className = 'text-muted';
      label.textContent = ` — ${status.label}`;
      line.append(icon, document.createTextNode(' '), name, label);
      item.append(line);
      if (machine.last_error) {
        const error = document.createElement('div');
        error.className = 'small text-danger ms-4';
        error.textContent = machine.last_error;
        item.append(error);
      }
      list.append(item);
    });
    renderCycleActivity(machines);
  };

  const renderCurrentMachine = (data) => {
    const machines = Array.isArray(data.machines) ? data.machines : [];
    const applying = machines.find((machine) => machine.status === 'applying');
    const current = rollout.querySelector('[data-rollout-current]');
    if (!current) return;
    current.replaceChildren();
    const icon = document.createElement('i');
    const countdown = formatDuration(data.countdown_seconds);
    if (data.phase === 'applying' || applying) {
      icon.className = 'ti ti-loader-2 text-primary ativa-spin fs-3';
      current.append(icon, document.createTextNode(` Aplicando agora em ${(applying && applying.hostname) || data.last_cycle_hostname || 'um computador'}`));
    } else if (data.phase === 'waiting') {
      icon.className = 'ti ti-clock text-warning fs-3';
      const text = data.countdown_seconds === null
        ? ' Aguardando o cliente informar sua proxima execucao'
        : ` ${data.next_hostname || 'Cliente'} executara em ${countdown}`;
      current.append(icon, document.createTextNode(text));
    } else if (data.phase === 'cycle_complete') {
      icon.className = 'ti ti-circle-check text-success fs-3';
      current.append(icon, document.createTextNode(` Aplicacao concluida em ${data.last_cycle_hostname || 'todos os computadores'} — 100%`));
    } else {
      icon.className = data.error > 0 ? 'ti ti-alert-circle text-danger fs-3' : 'ti ti-radar text-primary fs-3';
      const text = data.countdown_seconds === null
        ? ' Monitoramento continuo ativo; aguardando horario informado pelos clientes'
        : ` Monitoramento continuo: ${data.next_hostname || 'cliente'} verifica novamente em ${countdown}`;
      current.append(icon, document.createTextNode(text));
    }
  };

  const renderProgress = (data) => {
    const countdownPhase = data.phase === 'waiting' || data.phase === 'monitoring';
    const value = countdownPhase ? Number(data.countdown_percentage || 0) : Number(data.percentage || 0);
    if (countdownPhase) {
      setText('[data-rollout-label]', data.next_hostname
        ? `Proxima verificacao: ${data.next_hostname}`
        : 'Aguardando o horario da proxima verificacao');
      setText('[data-rollout-percentage]', data.countdown_seconds === null ? '--:--' : formatDuration(data.countdown_seconds));
    } else {
      setText('[data-rollout-label]', `${data.processed} de ${data.total} computador(es) processado(s)`);
      setText('[data-rollout-percentage]', `${Number(data.percentage || 0).toLocaleString('pt-BR', { maximumFractionDigits: 1 })}%`);
    }
    const bar = rollout.querySelector('[data-rollout-bar]');
    if (!bar) return;
    bar.style.width = `${value}%`;
    bar.classList.toggle('progress-bar-animated', data.phase === 'applying');
    bar.classList.toggle('bg-primary', countdownPhase);
    bar.classList.toggle('bg-warning', !countdownPhase && Boolean(data.completed_with_error));
    bar.classList.toggle('bg-success', !countdownPhase && !data.completed_with_error);
    bar.closest('[role="progressbar"]')?.setAttribute('aria-valuenow', String(value));
  };

  const renderRollout = (data) => {
    if (!data || !data.id) return false;
    if (currentRolloutId !== String(data.id)) {
      currentRolloutId = String(data.id);
      machineStates.clear();
      completedRollouts.delete(currentRolloutId);
    }
    rollout.classList.remove('d-none');
    rollout.dataset.active = data.active ? '1' : '0';
    rollout.dataset.monitoring = data.monitoring ? '1' : '0';
    setText('[data-rollout-version]', `Versao ${data.version || '-'} · iniciada em ${data.started_at || '-'}`);
    setText('[data-rollout-pending]', data.pending);
    setText('[data-rollout-applying]', data.applying);
    setText('[data-rollout-success]', data.success);
    setText('[data-rollout-error]', data.error);
    setText('[data-rollout-last-update]', `Dados recebidos do servidor em ${timestamp()}; proxima atualizacao em 1 segundo.`);
    renderProgress(data);

    const state = rollout.querySelector('[data-rollout-state]');
    if (state) {
      if (data.phase === 'waiting') {
        state.textContent = 'Aguardando cliente';
        state.className = 'badge bg-warning';
      } else if (data.phase === 'applying') {
        state.textContent = 'Aplicando';
        state.className = 'badge bg-primary';
      } else if (data.phase === 'cycle_complete') {
        state.textContent = 'Aplicado';
        state.className = 'badge bg-success';
      } else {
        state.textContent = data.error > 0 ? 'Monitorando com erros' : 'Monitorando';
        state.className = `badge ${data.error > 0 ? 'bg-danger' : 'bg-primary'}`;
      }
    }
    const machines = Array.isArray(data.machines) ? data.machines : [];
    renderMachines(machines);
    renderCurrentMachine(data);
    setForceButtons(Boolean(data.active));
    if (data.complete && !completedRollouts.has(currentRolloutId)) {
      completedRollouts.add(currentRolloutId);
      appendActivity(
        data.error > 0
          ? 'Aplicacao encerrada com erros; o monitoramento continua ativo.'
          : 'Aplicacao concluida em todos os computadores; o monitoramento continua ativo.',
        data.error > 0 ? 'text-danger' : 'text-success',
      );
    }
    return true;
  };

  const schedulePoll = (delay = 1000) => {
    if (pollTimer !== null) window.clearTimeout(pollTimer);
    pollTimer = window.setTimeout(poll, delay);
  };
  const poll = async () => {
    if (requestRunning) return;
    requestRunning = true;
    try {
      const response = await fetch(rollout.dataset.progressUrl, {
        credentials: 'same-origin', cache: 'no-store', headers: { Accept: 'application/json' },
      });
      if (!response.ok) throw new Error(`HTTP ${response.status}`);
      const data = await response.json();
      if (!renderRollout(data)) throw new Error('O servidor nao retornou dados do ciclo.');
      if (data.active || data.monitoring) schedulePoll(1000);
    } catch (error) {
      setText('[data-rollout-last-update]', `Falha temporaria ao consultar: ${error.message}. Nova tentativa em 5 segundos.`);
      schedulePoll(5000);
    } finally {
      requestRunning = false;
    }
  };

  forceForms.forEach((form) => {
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      if (requestRunning) return;
      requestRunning = true;
      if (pollTimer !== null) window.clearTimeout(pollTimer);
      machineStates.clear();
      rollout.querySelector('[data-rollout-events]')?.replaceChildren();
      rollout.classList.remove('d-none');
      rollout.dataset.active = '1';
      rollout.dataset.monitoring = '0';
      setForceButtons(true);
      setText('[data-rollout-state]', 'Iniciando');
      setText('[data-rollout-label]', 'Enviando solicitacao ao servidor...');
      setText('[data-rollout-percentage]', '0%');
      setText('[data-rollout-last-update]', `Solicitacao enviada: ${timestamp()}`);
      const bar = rollout.querySelector('[data-rollout-bar]');
      if (bar) bar.style.width = '0%';
      renderCurrentMachine({ machines: [], phase: 'applying' });
      appendActivity('Solicitacao de aplicacao enviada ao servidor.');

      try {
        const response = await fetch(form.action, {
          method: 'POST', body: new FormData(form), credentials: 'same-origin', cache: 'no-store',
          headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const contentType = response.headers.get('Content-Type') || '';
        if (!contentType.includes('application/json')) throw new Error(`Resposta inesperada do servidor (HTTP ${response.status}).`);
        const payload = await response.json();
        if (!response.ok || !payload.ok) throw new Error(payload.message || `HTTP ${response.status}`);
        renderRollout(payload.rollout);
        appendActivity(payload.message || 'Aplicacao iniciada.');
        if (payload.rollout?.active || payload.rollout?.monitoring) schedulePoll(500);
      } catch (error) {
        rollout.dataset.active = '0';
        setForceButtons(false);
        const state = rollout.querySelector('[data-rollout-state]');
        if (state) {
          state.textContent = 'Falha ao iniciar';
          state.className = 'badge bg-danger';
        }
        setText('[data-rollout-last-update]', error.message);
        appendActivity(`Falha: ${error.message}`, 'text-danger');
      } finally {
        requestRunning = false;
      }
    });
  });

  if (rollout.dataset.active === '1' || rollout.dataset.monitoring === '1') schedulePoll(300);
});
