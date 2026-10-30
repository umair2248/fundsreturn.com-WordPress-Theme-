/* Image controls on Appearance > Theme Content; WordPress supplies the media modal. */
(() => {
  'use strict';
  document.addEventListener('click', event => {
    const select = event.target.closest('[data-cfr-select-image]');
    const reset = event.target.closest('[data-cfr-reset-image]');
    if (reset) {
      event.preventDefault();
      const name = reset.dataset.cfrResetImage;
      document.getElementById('cfr-image-' + name).value = '0';
      document.getElementById('cfr-image-preview-' + name).textContent = 'The bundled image will be used after saving.';
    }
    if (!select || !window.wp || !window.wp.media) return;
    event.preventDefault();
    const name = select.dataset.cfrSelectImage;
    const frame = window.wp.media({title: 'Choose site image', button: {text: 'Use this image'}, library: {type: 'image'}, multiple: false});
    frame.on('select', () => {
      const image = frame.state().get('selection').first().toJSON();
      document.getElementById('cfr-image-' + name).value = image.id;
      const preview = document.createElement('img');
      preview.src = image.sizes && image.sizes.medium ? image.sizes.medium.url : image.url;
      preview.alt = image.alt || '';
      preview.style.maxWidth = '100%';
      document.getElementById('cfr-image-preview-' + name).replaceChildren(preview);
    });
    frame.open();
  });
})();
