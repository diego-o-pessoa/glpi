document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-ativa-force-all]').forEach((form) => {
    form.addEventListener('submit', () => {
      const button = form.querySelector('button[type="submit"]');
      if (!button) return;
      button.disabled = true;
      button.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Iniciando...';
    });
  });

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
        error.textContent = maxBytes > 0 && file.size > maxBytes ? 'O arquivo excede o limite configurado.' : 'Selecione uma imagem JPG ou PNG.';
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

  const statuses = {
    pending: { icon: 'ti-clock text-warning', label: 'aguardando consulta' },
    applying: { icon: 'ti-loader-2 text-primary ativa-spin', label: 'aplicando agora' },
    success: { icon: 'ti-circle-check text-success', label: 'concluido' },
    error: { icon: 'ti-alert-circle text-danger', label: 'erro' },
  };
  const setText = (selector, value) => {
    const element = rollout.querySelector(selector);
    if (element) element.textContent = String(value);
  };
  const renderMachines = (machines) => {
    const list = rollout.querySelector('[data-rollout-machines]');
    if (!list) return;
    list.replaceChildren();
    machines.forEach((machine) => {
      const status = statuses[machine.status] || statuses.pending;
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
  const renderRollout = (data) => {
    if (!data || !data.id) return;
    rollout.classList.remove('d-none');
    rollout.dataset.active = data.active ? '1' : '0';
    setText('[data-rollout-version]', `Versao ${data.version || '-'} · iniciada em ${data.started_at || '-'}`);
    setText('[data-rollout-label]', `${data.processed} de ${data.total} computador(es) processado(s)`);
    setText('[data-rollout-percentage]', `${Number(data.percentage || 0).toLocaleString('pt-BR', { maximumFractionDigits: 1 })}%`);
    setText('[data-rollout-pending]', data.pending);
    setText('[data-rollout-applying]', data.applying);
    setText('[data-rollout-success]', data.success);
    setText('[data-rollout-error]', data.error);
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
  };
  const poll = async () => {
    try {
      const response = await fetch(rollout.dataset.progressUrl, {
        credentials: 'same-origin',
        cache: 'no-store',
        headers: { Accept: 'application/json' },
      });
      if (!response.ok) throw new Error(`HTTP ${response.status}`);
      const data = await response.json();
      renderRollout(data);
      if (data.active) {
        window.setTimeout(poll, 2000);
      } else if (data.complete && rollout.dataset.refreshed !== '1') {
        rollout.dataset.refreshed = '1';
        window.setTimeout(() => window.location.reload(), 1500);
      }
    } catch (_error) {
      window.setTimeout(poll, 5000);
    }
  };
  if (rollout.dataset.active === '1') window.setTimeout(poll, 500);
});
