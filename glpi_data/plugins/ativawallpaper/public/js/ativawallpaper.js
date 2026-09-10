document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-ativa-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      if (!window.confirm(form.dataset.ativaConfirm || 'Confirmar esta acao?')) {
        event.preventDefault();
      }
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

  const rollout = document.querySelector('[data-ativa-rollout]');
  if (!rollout) return;

  const forceForms = Array.from(document.querySelectorAll('[data-ativa-force-all]'));
  const machineStates = new Map();
  let pollTimer = null;
  let requestRunning = false;

  const statuses = {
    pending: { icon: 'ti-clock text-warning', label: 'aguardando consulta da API' },
    applying: { icon: 'ti-loader-2 text-primary ativa-spin', label: 'aplicando agora' },
    success: { icon: 'ti-circle-check text-success', label: 'concluido' },
    error: { icon: 'ti-alert-circle text-danger', label: 'erro' },
  };

  const setText = (selector, value) => {
    const element = rollout.querySelector(selector);
    if (element) element.textContent = String(value);
  };

  const timestamp = () => new Intl.DateTimeFormat('pt-BR', {
    hour: '2-digit', minute: '2-digit', second: '2-digit',
  }).format(new Date());

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

  const renderMachines = (machines) => {
    const list = rollout.querySelector('[data-rollout-machines]');
    if (!list) return;
    list.replaceChildren();

    machines.forEach((machine) => {
      const status = statuses[machine.status] || statuses.pending;
      const previous = machineStates.get(machine.hostname);
      if (previous !== machine.status) {
        if (previous === 'pending' && (machine.status === 'success' || machine.status === 'error')) {
          appendActivity(`${machine.hostname}: aplicacao iniciada`);
        }
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
  };

  const renderCurrentMachine = (data) => {
    const machines = Array.isArray(data.machines) ? data.machines : [];
    const applying = machines.find((machine) => machine.status === 'applying');
    const pending = machines.find((machine) => machine.status === 'pending');
    const current = rollout.querySelector('[data-rollout-current]');
    if (!current) return;
    current.replaceChildren();
    const icon = document.createElement('i');
    if (applying) {
      icon.className = 'ti ti-loader-2 text-primary ativa-spin fs-3';
      current.append(icon, document.createTextNode(` Aplicando agora em ${applying.hostname}`));
    } else if (pending) {
      icon.className = 'ti ti-clock text-warning fs-3';
      current.append(icon, document.createTextNode(` Aguardando ${pending.hostname} consultar a API`));
    } else if (data.complete) {
      icon.className = data.error > 0 ? 'ti ti-alert-circle text-danger fs-3' : 'ti ti-circle-check text-success fs-3';
      current.append(icon, document.createTextNode(data.error > 0 ? ' Processo finalizado com erros' : ' Todos os computadores foram processados'));
    } else {
      icon.className = 'ti ti-loader-2 text-primary ativa-spin fs-3';
      current.append(icon, document.createTextNode(' Preparando aplicacao'));
    }
  };

  const renderRollout = (data) => {
    if (!data || !data.id) return false;
    rollout.classList.remove('d-none');
    rollout.dataset.active = data.active ? '1' : '0';
    setText('[data-rollout-version]', `Versao ${data.version || '-'} · iniciada em ${data.started_at || '-'}`);
    setText('[data-rollout-label]', `${data.processed} de ${data.total} computador(es) processado(s)`);
    setText('[data-rollout-percentage]', `${Number(data.percentage || 0).toLocaleString('pt-BR', { maximumFractionDigits: 1 })}%`);
    setText('[data-rollout-pending]', data.pending);
    setText('[data-rollout-applying]', data.applying);
    setText('[data-rollout-success]', data.success);
    setText('[data-rollout-error]', data.error);
    setText('[data-rollout-last-update]', `Ultima consulta ao servidor: ${timestamp()}`);

    const bar = rollout.querySelector('[data-rollout-bar]');
    if (bar) {
      bar.style.width = `${data.percentage}%`;
      bar.classList.toggle('progress-bar-animated', Boolean(data.active));
      bar.classList.toggle('bg-warning', Boolean(data.completed_with_error));
      bar.classList.toggle('bg-success', !data.completed_with_error);
      bar.closest('[role="progressbar"]')?.setAttribute('aria-valuenow', String(data.percentage));
    }
    const state = rollout.querySelector('[data-rollout-state]');
    if (state) {
      state.textContent = data.complete ? (data.error > 0 ? 'Concluida com erros' : 'Concluida') : 'Em andamento';
      state.className = `badge ${data.complete ? (data.error > 0 ? 'bg-danger' : 'bg-success') : 'bg-primary'}`;
    }
    renderMachines(Array.isArray(data.machines) ? data.machines : []);
    renderCurrentMachine(data);
    setForceButtons(Boolean(data.active));
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
        credentials: 'same-origin',
        cache: 'no-store',
        headers: { Accept: 'application/json' },
      });
      if (!response.ok) throw new Error(`HTTP ${response.status}`);
      const data = await response.json();
      if (!renderRollout(data)) throw new Error('O servidor nao retornou uma aplicacao ativa.');
      if (data.active) schedulePoll(1000);
      else appendActivity(data.error > 0 ? 'Aplicacao encerrada com erros.' : 'Aplicacao concluida em todos os computadores.', data.error > 0 ? 'text-danger' : 'text-success');
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
      setForceButtons(true);
      setText('[data-rollout-state]', 'Iniciando');
      setText('[data-rollout-label]', 'Enviando solicitacao ao servidor...');
      setText('[data-rollout-percentage]', '0%');
      setText('[data-rollout-last-update]', `Solicitacao enviada: ${timestamp()}`);
      const bar = rollout.querySelector('[data-rollout-bar]');
      if (bar) bar.style.width = '0%';
      renderCurrentMachine({ machines: [], complete: false });
      appendActivity('Solicitacao de aplicacao enviada ao servidor.');

      try {
        const response = await fetch(form.action, {
          method: 'POST',
          body: new FormData(form),
          credentials: 'same-origin',
          cache: 'no-store',
          headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          },
        });
        const contentType = response.headers.get('Content-Type') || '';
        if (!contentType.includes('application/json')) throw new Error(`Resposta inesperada do servidor (HTTP ${response.status}).`);
        const payload = await response.json();
        if (!response.ok || !payload.ok) throw new Error(payload.message || `HTTP ${response.status}`);
        renderRollout(payload.rollout);
        appendActivity(payload.message || 'Aplicacao iniciada.');
        if (payload.rollout?.active) schedulePoll(500);
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

  if (rollout.dataset.active === '1') schedulePoll(300);
});
