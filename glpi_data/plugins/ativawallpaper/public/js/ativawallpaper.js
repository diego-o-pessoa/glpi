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
});
