/*
 * Ativa Workspace - tempo real da Visao Geral e do Provisionamento.
 *
 * Le a configuracao de [data-aw-live] (Page::liveConfig), desenha a primeira
 * tela com os dados embutidos e consulta overview.data.php periodicamente.
 * Todo dado vindo do servidor entra no DOM por textContent (nunca innerHTML).
 */
(function () {
    'use strict';

    var root = document.querySelector('[data-aw-live]');
    if (!root || root.dataset.awStarted === '1') {
        return;
    }
    root.dataset.awStarted = '1';

    var config;
    try {
        config = JSON.parse(root.dataset.awLive);
    } catch (e) {
        console.error('Ativa Workspace: configuracao invalida.', e);
        return;
    }

    var urls = config.urls || {};
    var refreshMs = Math.max(2000, config.refreshMs || 5000);
    var maxBackoffMs = 60000;
    var delayMs = refreshMs;
    var timer = null;
    var lastJobsKey = '';
    var lastEventsKey = '';
    var detailJobId = 0;
    var detailTimer = null;

    var jobsBody = root.querySelector('[data-aw-jobs]');
    var eventsList = root.querySelector('[data-aw-events]');
    var liveStatus = root.querySelectorAll('[data-aw-live-status]');

    // ---------------------------------------------------------------- helpers

    function el(tag, className, text) {
        var node = document.createElement(tag);
        if (className) {
            node.className = className;
        }
        if (text !== undefined && text !== null) {
            node.textContent = String(text);
        }
        return node;
    }

    function icon(name) {
        var i = el('i', 'ti ' + name);
        i.setAttribute('aria-hidden', 'true');
        return i;
    }

    /** "2025-04-12 09:14:00" -> Date local (o GLPI grava no fuso da sessao). */
    function parseDate(value) {
        if (!value) {
            return null;
        }
        var m = /^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})(?::(\d{2}))?/.exec(value);
        if (!m) {
            return null;
        }
        return new Date(+m[1], +m[2] - 1, +m[3], +m[4], +m[5], +(m[6] || 0));
    }

    function pad(n) {
        return n < 10 ? '0' + n : String(n);
    }

    function formatDate(value) {
        var d = parseDate(value);
        if (!d) {
            return '—';
        }
        return pad(d.getDate()) + '/' + pad(d.getMonth() + 1) + '/' + d.getFullYear()
            + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }

    /** Tempo relativo usando o "agora" do servidor (sem depender do relogio do PC). */
    function relative(value, now) {
        var d = parseDate(value);
        var n = parseDate(now) || new Date();
        if (!d) {
            return '';
        }
        var seconds = Math.max(0, Math.round((n - d) / 1000));
        if (seconds < 60) {
            return 'agora';
        }
        var minutes = Math.round(seconds / 60);
        if (minutes < 60) {
            return 'há ' + minutes + ' min';
        }
        var hours = Math.round(minutes / 60);
        if (hours < 24) {
            return 'há ' + hours + ' h';
        }
        var days = Math.round(hours / 24);
        return days === 1 ? 'há 1 dia' : 'há ' + days + ' dias';
    }

    function setLive(ok) {
        liveStatus.forEach(function (node) {
            node.classList.toggle('aw-live-off', !ok);
            node.textContent = ok ? 'Ao vivo' : 'Reconectando…';
        });
    }

    function request(url, options) {
        options = options || {};
        options.credentials = 'same-origin';
        options.headers = Object.assign({
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }, options.headers || {});
        return fetch(url, options).then(function (response) {
            return response.json().catch(function () {
                return {};
            }).then(function (body) {
                if (!response.ok) {
                    var error = new Error(body.message || ('HTTP ' + response.status));
                    error.status = response.status;
                    throw error;
                }
                return body;
            });
        });
    }

    // Envia uma acao do job (ex.: cancel) como POST de formulario. O backend
    // (job.action.php) revalida permissao e entidade, e redireciona para o job.
    function submitAction(jobId, action) {
        var form = document.createElement('form');
        form.method = 'post';
        form.action = urls.jobAction;
        var fields = { _glpi_csrf_token: config.csrf || csrfToken(), job: jobId, action: action };
        Object.keys(fields).forEach(function (name) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = fields[name];
            form.appendChild(input);
        });
        document.body.appendChild(form);
        form.submit();
    }

    function csrfToken() {
        var meta = document.querySelector('meta[property="glpi:csrf_token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function computerHref(id) {
        return urls.computer + '?id=' + encodeURIComponent(id);
    }

    // ------------------------------------------------------------ rendering

    function renderCounts(counts) {
        if (!counts) {
            return;
        }
        root.querySelectorAll('[data-aw-count]').forEach(function (node) {
            var key = node.getAttribute('data-aw-count');
            if (Object.prototype.hasOwnProperty.call(counts, key)) {
                node.textContent = String(counts[key]);
            }
        });
    }

    function statusPill(job) {
        return el('span', 'aw-pill aw-pill-' + job.status_state, job.status_label);
    }

    function progressCell(job) {
        var wrap = el('div', 'aw-progress-wrap');
        wrap.appendChild(el('div', 'aw-progress-value', job.progress + '%'));
        var bar = el('div', 'aw-progress aw-progress-' + job.status_state);
        bar.setAttribute('role', 'progressbar');
        bar.setAttribute('aria-valuemin', '0');
        bar.setAttribute('aria-valuemax', '100');
        bar.setAttribute('aria-valuenow', String(job.progress));
        var fill = el('div', 'aw-progress-bar');
        fill.style.width = Math.max(0, Math.min(100, job.progress)) + '%';
        bar.appendChild(fill);
        wrap.appendChild(bar);
        if (job.steps_total > 0) {
            wrap.appendChild(el('div', 'aw-progress-steps', job.steps_done + ' de ' + job.steps_total + ' etapas'));
        }
        return wrap;
    }

    function actionsCell(job) {
        var box = el('div', 'd-inline-flex gap-1');

        var failed = job.status_state === 'failed';
        var view = el('a', 'btn btn-icon btn-sm ' + (failed ? 'btn-outline-danger' : 'btn-outline-secondary'));
        view.href = urls.jobPage + '?id=' + encodeURIComponent(job.id);
        view.title = failed ? 'Ver o motivo da falha' : 'Abrir o provisionamento';
        view.setAttribute('aria-label', view.title);
        view.appendChild(icon(failed ? 'ti-alert-circle' : 'ti-eye'));
        box.appendChild(view);

        var dropdown = el('div', 'dropdown');
        var toggle = el('button', 'btn btn-icon btn-sm btn-outline-secondary');
        toggle.type = 'button';
        toggle.setAttribute('data-bs-toggle', 'dropdown');
        toggle.setAttribute('data-bs-boundary', 'viewport');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Mais ações');
        toggle.appendChild(icon('ti-dots-vertical'));
        dropdown.appendChild(toggle);

        var menu = el('div', 'dropdown-menu dropdown-menu-end');
        var log = el('a', 'dropdown-item');
        log.href = '#';
        log.appendChild(icon('ti-file-text me-2'));
        log.appendChild(document.createTextNode('Ver log'));
        log.addEventListener('click', function (event) {
            event.preventDefault();
            openDetails(job.id);
        });
        menu.appendChild(log);
        if (job.computers_id > 0) {
            var computer = el('a', 'dropdown-item');
            computer.href = computerHref(job.computers_id);
            computer.appendChild(icon('ti-device-desktop me-2'));
            computer.appendChild(document.createTextNode('Abrir computador'));
            menu.appendChild(computer);
        }
        // Cancelar direto da lista (inclui "Aguardando intervenção" travado).
        var cancellable = job.is_active || job.status_state === 'failed';
        if (config.canManage && cancellable && urls.jobAction) {
            menu.appendChild(el('div', 'dropdown-divider'));
            var cancel = el('a', 'dropdown-item text-danger');
            cancel.href = '#';
            cancel.appendChild(icon('ti-ban me-2'));
            cancel.appendChild(document.createTextNode('Cancelar provisionamento'));
            cancel.addEventListener('click', function (event) {
                event.preventDefault();
                if (window.confirm('Cancelar o provisionamento #' + job.id + '? As etapas não concluídas serão canceladas.')) {
                    submitAction(job.id, 'cancel');
                }
            });
            menu.appendChild(cancel);
        }
        dropdown.appendChild(menu);
        box.appendChild(dropdown);

        return box;
    }

    function renderJobs(jobs) {
        if (!jobsBody || !Array.isArray(jobs)) {
            return;
        }
        var key = JSON.stringify(jobs);
        if (key === lastJobsKey) {
            return;
        }
        // Nao redesenha com um menu de acoes aberto: tentar de novo no proximo ciclo.
        if (jobsBody.querySelector('.dropdown-menu.show')) {
            return;
        }
        lastJobsKey = key;

        var emptyRow = jobsBody.querySelector('.aw-empty-row');
        Array.prototype.slice.call(jobsBody.querySelectorAll('tr:not(.aw-empty-row)')).forEach(function (tr) {
            tr.remove();
        });
        if (emptyRow) {
            emptyRow.classList.toggle('d-none', jobs.length > 0);
        }

        jobs.forEach(function (job) {
            var tr = el('tr', job.status_state === 'failed' ? 'aw-row-failed' : '');

            var computerTd = el('td');
            if (job.computers_id > 0) {
                var link = el('a', 'aw-computer', job.computer_name);
                link.href = computerHref(job.computers_id);
                computerTd.appendChild(link);
            } else {
                computerTd.appendChild(el('span', 'aw-computer', job.computer_name));
            }
            tr.appendChild(computerTd);

            tr.appendChild(el('td', '', job.employee_name || '—'));
            tr.appendChild(el('td', '', job.profile_name || '—'));

            var statusTd = el('td');
            statusTd.appendChild(statusPill(job));
            tr.appendChild(statusTd);

            var progressTd = el('td', 'aw-col-progress');
            progressTd.appendChild(progressCell(job));
            tr.appendChild(progressTd);

            tr.appendChild(el('td', 'text-nowrap', formatDate(job.date_start || job.date_creation)));
            tr.appendChild(el('td', '', job.requester || '—'));
            tr.appendChild(el('td', 'text-nowrap small text-muted', formatDate(job.date_mod)));

            var actionsTd = el('td', 'text-end');
            actionsTd.appendChild(actionsCell(job));
            tr.appendChild(actionsTd);

            jobsBody.appendChild(tr);
        });
    }

    var EVENT_TONES = {
        INFO: ['success', 'ti-check'],
        WARNING: ['warning', 'ti-clock'],
        ERROR: ['danger', 'ti-x'],
        SECURITY: ['purple', 'ti-shield-lock']
    };

    function renderEvents(events, now) {
        if (!eventsList || !Array.isArray(events)) {
            return;
        }
        // O "ha X min" muda com o tempo: a chave inclui o agora do servidor.
        var key = JSON.stringify(events) + '|' + now;
        if (key === lastEventsKey) {
            return;
        }
        lastEventsKey = key;

        eventsList.textContent = '';
        if (events.length === 0) {
            eventsList.appendChild(el('li', 'aw-timeline-empty text-muted', 'Nenhuma atividade registrada.'));
            return;
        }

        events.forEach(function (event) {
            var tone = EVENT_TONES[event.level] || EVENT_TONES.INFO;
            var li = el('li', 'aw-timeline-item');

            var dot = el('span', 'aw-timeline-dot aw-dot-' + tone[0]);
            dot.appendChild(icon(tone[1]));
            li.appendChild(dot);

            var body = el('div', 'aw-timeline-body');
            var head = el('div', 'aw-timeline-head');
            head.appendChild(el('span', 'aw-timeline-title', event.message));
            head.appendChild(el('span', 'aw-timeline-time', relative(event.date, now)));
            body.appendChild(head);

            var meta = [];
            if (event.computer_name) {
                meta.push(event.computer_name);
            } else if (event.user_name) {
                meta.push(event.user_name);
            }
            meta.push(formatDate(event.date));
            body.appendChild(el('div', 'aw-timeline-meta', meta.join(' • ')));

            if (event.jobs_id > 0) {
                li.classList.add('aw-clickable');
                li.title = 'Ver o log deste provisionamento';
                li.addEventListener('click', function () {
                    openDetails(event.jobs_id);
                });
            }

            li.appendChild(body);
            eventsList.appendChild(li);
        });
    }

    function render(data) {
        renderCounts(data.counts);
        renderJobs(data.jobs);
        renderEvents(data.events, data.now);
    }

    // ---------------------------------------------------------------- polling

    function schedule(ms) {
        clearTimeout(timer);
        timer = setTimeout(poll, ms);
    }

    function poll() {
        if (document.hidden) {
            return; // retoma no visibilitychange
        }
        var url = urls.data + '?jobs=' + encodeURIComponent(config.jobsLimit || 10)
            + '&events=' + encodeURIComponent(config.eventsLimit || 0);
        var filters = config.filters || {};
        Object.keys(filters).forEach(function (key) {
            url += '&' + encodeURIComponent(key) + '=' + encodeURIComponent(filters[key]);
        });
        request(url).then(function (data) {
            render(data);
            setLive(true);
            delayMs = refreshMs;
        }).catch(function (error) {
            setLive(false);
            // Sessao expirada/sem permissao: para de insistir.
            if (error.status === 401 || error.status === 403) {
                return Promise.reject(error);
            }
            delayMs = Math.min(maxBackoffMs, delayMs * 2);
        }).then(function () {
            schedule(delayMs);
        }, function () { /* parado */ });
    }

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            schedule(0);
        }
    });

    // ------------------------------------------------------ modal de detalhes

    var detailModal = document.getElementById('aw-job-details');

    function modalInstance(node) {
        if (!node || !window.bootstrap || !window.bootstrap.Modal) {
            return null;
        }
        return window.bootstrap.Modal.getOrCreateInstance(node);
    }


    function detailRow(label, value) {
        var col = el('div', 'col-sm-6 col-lg-4');
        col.appendChild(el('div', 'aw-detail-label', label));
        col.appendChild(el('div', 'aw-detail-value', value || '—'));
        return col;
    }

    function renderDetails(data) {
        var body = detailModal.querySelector('[data-aw-detail-body]');
        var job = data.job;
        detailModal.querySelector('[data-aw-detail-title]').textContent =
            'Provisionamento #' + job.id + ' · ' + job.computer_name;

        var computerBtn = detailModal.querySelector('[data-aw-detail-computer]');
        if (job.computers_id > 0) {
            computerBtn.href = computerHref(job.computers_id);
            computerBtn.classList.remove('d-none');
        } else {
            computerBtn.classList.add('d-none');
        }

        body.textContent = '';

        var top = el('div', 'd-flex flex-wrap align-items-center gap-3 mb-3');
        top.appendChild(statusPill(job));
        var progress = progressCell(job);
        progress.classList.add('flex-fill');
        top.appendChild(progress);
        body.appendChild(top);

        if (job.status_state === 'failed' || job.status_state === 'waiting') {
            var alert = el('div', 'alert ' + (job.status_state === 'failed' ? 'alert-danger' : 'alert-warning'));
            alert.appendChild(el('div', 'fw-bold mb-1', job.status_state === 'failed' ? 'Motivo da falha' : 'Por que está aguardando'));
            alert.appendChild(el('div', '', job.message || 'Nenhuma mensagem registrada.'));
            body.appendChild(alert);
        } else if (job.message) {
            body.appendChild(el('div', 'text-muted mb-3', job.message));
        }

        var grid = el('div', 'row g-3 mb-4');
        grid.appendChild(detailRow('Computador', job.computer_name));
        grid.appendChild(detailRow('Funcionário', job.employee_name));
        grid.appendChild(detailRow('Perfil', job.profile_name));
        grid.appendChild(detailRow('Responsável', job.requester));
        grid.appendChild(detailRow('Início', formatDate(job.date_start || job.date_creation)));
        grid.appendChild(detailRow('Fim', formatDate(job.date_end)));
        body.appendChild(grid);

        body.appendChild(el('h4', 'aw-detail-section', 'Etapas'));
        if (!data.steps.length) {
            body.appendChild(el('div', 'text-muted mb-4', 'O perfil não tinha etapas ativas quando este provisionamento foi criado.'));
        } else {
            var steps = el('ol', 'aw-steps mb-4');
            data.steps.forEach(function (step) {
                var tone = [step.tone || 'secondary', step.icon || 'ti-circle', step.status_label || step.status];
                var li = el('li', 'aw-step aw-step-' + tone[0]);
                var dot = el('span', 'aw-timeline-dot aw-dot-' + tone[0]);
                dot.appendChild(icon(tone[1]));
                li.appendChild(dot);
                var info = el('div', 'flex-fill');
                var head = el('div', 'd-flex justify-content-between gap-2');
                head.appendChild(el('span', 'fw-medium', step.name || step.step_type));
                head.appendChild(el('span', 'text-muted small', tone[2]));
                info.appendChild(head);
                if (step.message) {
                    info.appendChild(el('div', step.status === 'FAILED' ? 'text-danger small' : 'text-muted small', step.message));
                }
                li.appendChild(info);
                steps.appendChild(li);
            });
            body.appendChild(steps);
        }

        body.appendChild(el('h4', 'aw-detail-section', 'Log'));
        if (!data.events.length) {
            body.appendChild(el('div', 'text-muted', 'Nenhum evento registrado para este provisionamento.'));
        } else {
            var log = el('div', 'aw-log');
            data.events.forEach(function (event) {
                var line = el('div', 'aw-log-line aw-log-' + String(event.level).toLowerCase());
                line.appendChild(el('span', 'aw-log-date', formatDate(event.date)));
                line.appendChild(el('span', 'aw-log-level', event.level));
                line.appendChild(el('span', 'aw-log-message', event.message));
                if (event.context) {
                    line.appendChild(el('pre', 'aw-log-context', event.context));
                }
                log.appendChild(line);
            });
            body.appendChild(log);
        }
    }

    function loadDetails() {
        if (!detailJobId) {
            return;
        }
        var id = detailJobId;
        request(urls.job + '?id=' + encodeURIComponent(id)).then(function (data) {
            if (id !== detailJobId) {
                return;
            }
            renderDetails(data);
            clearTimeout(detailTimer);
            // Enquanto o job esta em aberto, o log continua atualizando.
            if (data.job.is_open) {
                detailTimer = setTimeout(loadDetails, refreshMs);
            }
        }).catch(function (error) {
            var body = detailModal.querySelector('[data-aw-detail-body]');
            body.textContent = '';
            body.appendChild(el('div', 'alert alert-danger mb-0', error.message || 'Não foi possível carregar o provisionamento.'));
        });
    }

    function openDetails(id) {
        if (!detailModal) {
            return;
        }
        detailJobId = id;
        var body = detailModal.querySelector('[data-aw-detail-body]');
        body.textContent = '';
        body.appendChild(el('div', 'text-center text-muted py-4', 'Carregando…'));
        var modal = modalInstance(detailModal);
        if (modal) {
            modal.show();
        }
        loadDetails();
    }

    if (detailModal) {
        detailModal.addEventListener('hidden.bs.modal', function () {
            detailJobId = 0;
            clearTimeout(detailTimer);
        });
    }

    // ------------------------------------------------ novo provisionamento

    var newModal = document.getElementById('aw-new-provisioning');
    var newForm = newModal ? newModal.querySelector('[data-aw-new-form]') : null;
    if (newForm) {
        var upnProfiles = [];
        try {
            upnProfiles = JSON.parse(newForm.getAttribute('data-aw-upn-profiles') || '[]').map(Number);
        } catch (e) {
            upnProfiles = [];
        }
        var profileSelect = newForm.querySelector('[name="plugin_ativaworkspace_provisioningprofiles_id"]');
        var upnInput = newForm.querySelector('[name="upn"]');
        var syncUpn = function () {
            if (!profileSelect || !upnInput) {
                return;
            }
            var needed = upnProfiles.indexOf(Number(profileSelect.value)) !== -1;
            upnInput.required = needed;
            var label = upnInput.id ? newForm.querySelector('label[for="' + upnInput.id + '"]') : null;
            if (label) {
                label.classList.toggle('required', needed);
            }
        };
        if (profileSelect) {
            // O GLPI usa select2: o "change" dele e disparado pelo jQuery.
            if (window.jQuery) {
                window.jQuery(profileSelect).on('change', syncUpn);
            } else {
                profileSelect.addEventListener('change', syncUpn);
            }
            syncUpn();
        }

        newForm.addEventListener('submit', function (event) {
            event.preventDefault();

            var errorBox = newForm.querySelector('[data-aw-new-error]');
            var submit = newForm.querySelector('[data-aw-new-submit]');
            if (!submit) {
                return;
            }
            if (errorBox) {
                errorBox.classList.add('d-none');
            }

            var formData = new FormData(newForm);
            var missing = '';
            if (!Number(formData.get('computers_id'))) {
                missing = 'Escolha o computador.';
            } else if (!String(formData.get('employee_name') || '').trim()) {
                missing = 'Informe o funcionário.';
            } else if (upnInput && upnInput.required && !String(formData.get('upn') || '').trim()) {
                missing = 'Informe a conta Microsoft do funcionário.';
            }
            if (missing) {
                if (errorBox) {
                    errorBox.textContent = missing;
                    errorBox.classList.remove('d-none');
                }
                return;
            }

            submit.disabled = true;
            request(urls.jobForm, {
                method: 'POST',
                body: formData,
                headers: { 'X-Glpi-Csrf-Token': csrfToken() }
            }).then(function (result) {
                var modal = modalInstance(newModal);
                if (modal) {
                    modal.hide();
                }
                newForm.reset();
                if (result.url) {
                    window.location.href = result.url;
                    return;
                }
                lastJobsKey = '';
                schedule(0);
            }).catch(function (error) {
                if (errorBox) {
                    errorBox.textContent = error.message || 'Não foi possível criar o provisionamento.';
                    errorBox.classList.remove('d-none');
                }
            }).then(function () {
                submit.disabled = false;
            });
        });
    }

    // ------------------------------------------------------------------ start

    render(config.initial || {});
    setLive(true);
    schedule(refreshMs);
})();
