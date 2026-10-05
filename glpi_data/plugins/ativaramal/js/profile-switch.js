(() => {
    'use strict';

    const addProfileSwitch = () => {
        const profileMeta = document.querySelector('meta[name="ativaramal-gestor-profile"]');
        const menu = document.querySelector('[data-testid="user-menu-dropdown"]')
            || document.querySelector('.user-menu .dropdown-menu');
        if (!profileMeta || !menu || menu.querySelector('[data-ativaramal-profile-switch]')) {
            return;
        }

        const profileId = profileMeta.getAttribute('content');
        if (!profileId || !/^\d+$/.test(profileId)) {
            return;
        }

        const csrf = document.querySelector('meta[property="glpi:csrf_token"]')?.getAttribute('content') || '';
        const divider = document.createElement('div');
        divider.className = 'dropdown-divider';

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `${window.CFG_GLPI?.root_doc || ''}/Session/ChangeProfile`;
        form.dataset.ativaramalProfileSwitch = '1';

        const id = document.createElement('input');
        id.type = 'hidden';
        id.name = 'id';
        id.value = profileId;
        form.appendChild(id);

        const token = document.createElement('input');
        token.type = 'hidden';
        token.name = '_glpi_csrf_token';
        token.value = csrf;
        form.appendChild(token);

        const button = document.createElement('button');
        button.type = 'submit';
        button.className = 'dropdown-item';
        button.innerHTML = '<i class="ti ti-switch-2 me-2"></i>Mudar para Ativa - Gestor';
        form.appendChild(button);

        menu.insertBefore(divider, menu.firstChild);
        menu.insertBefore(form, divider.nextSibling);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', addProfileSwitch, {once: true});
    } else {
        addProfileSwitch();
    }
})();
