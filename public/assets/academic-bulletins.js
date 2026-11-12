(function () {
  'use strict';

  const maxDimension = 1800;
  const qualities = [0.82, 0.72, 0.62];

  function createJpegBlob(canvas, quality) {
    return new Promise(function (resolve, reject) {
      canvas.toBlob(function (blob) {
        if (blob) {
          resolve(blob);
          return;
        }
        reject(new Error('Le navigateur n’a pas pu compresser une image.'));
      }, 'image/jpeg', quality);
    });
  }

  async function compressImage(file) {
    if (typeof window.createImageBitmap !== 'function' || typeof window.DataTransfer !== 'function') {
      throw new Error('La compression des images n’est pas prise en charge par ce navigateur. Utilisez un navigateur récent.');
    }

    const bitmap = await window.createImageBitmap(file);
    try {
      const scale = Math.min(1, maxDimension / Math.max(bitmap.width, bitmap.height));
      const canvas = document.createElement('canvas');
      canvas.width = Math.max(1, Math.round(bitmap.width * scale));
      canvas.height = Math.max(1, Math.round(bitmap.height * scale));
      const context = canvas.getContext('2d');
      if (!context) {
        throw new Error('Impossible de préparer la compression des images.');
      }

      context.fillStyle = '#fff';
      context.fillRect(0, 0, canvas.width, canvas.height);
      context.drawImage(bitmap, 0, 0, canvas.width, canvas.height);

      let smallestBlob = null;
      for (const quality of qualities) {
        const blob = await createJpegBlob(canvas, quality);
        if (!smallestBlob || blob.size < smallestBlob.size) {
          smallestBlob = blob;
        }
        if (blob.size < file.size) {
          break;
        }
      }

      if (!smallestBlob || smallestBlob.size >= file.size) {
        return file;
      }

      const baseName = file.name.replace(/\.[^.]+$/, '') || file.name;
      return new File([smallestBlob], baseName + '.jpg', {
        type: 'image/jpeg',
        lastModified: file.lastModified
      });
    } finally {
      bitmap.close();
    }
  }

  function formatBytes(bytes) {
    return new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 1 }).format(bytes / (1024 * 1024)) + ' Mo';
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.academic-bulletin-input').forEach(function (fileInput) {
      const metadataContainer = fileInput.closest('fieldset, section')?.querySelector('.academic-bulletin-metadata')
        || fileInput.form?.querySelector('.academic-bulletin-metadata');
      if (!metadataContainer) {
        return;
      }
      const form = fileInput.form;
      const status = fileInput.closest('fieldset, section')?.querySelector('[data-bulletin-compression-status]');

      fileInput.addEventListener('change', function () {
        metadataContainer.replaceChildren();
        Array.from(fileInput.files).forEach(function (file, index) {
          const card = document.createElement('div');
          card.className = 'card card-body bg-light mb-2';
          const heading = document.createElement('strong');
          heading.className = 'mb-2';
          heading.textContent = 'Bulletin ' + (index + 1) + ' : ' + file.name;
          card.appendChild(heading);

          const row = document.createElement('div');
          row.className = 'row';
          const fields = [
            { name: 'ecole_provenance', label: 'École de provenance', type: 'text', value: fileInput.dataset.defaultOrigin || '', maxLength: 150 },
            { name: 'annee_document', label: 'Année scolaire du bulletin', type: 'text', placeholder: '2025-2026', pattern: '[0-9]{4}-[0-9]{4}' },
            { name: 'mention', label: 'Mention', type: 'select' }
          ];

          fields.forEach(function (field) {
            const column = document.createElement('div');
            column.className = 'col-md-4 mb-2';
            const fieldId = fileInput.id + '-' + field.name + '-' + index;
            const label = document.createElement('label');
            label.className = 'form-label';
            label.htmlFor = fieldId;
            label.textContent = field.label;

            let control;
            if (field.type === 'select') {
              control = document.createElement('select');
              control.className = 'form-select';
              [
                ['', 'Sélectionnez une mention'],
                ['reussite', 'Réussite'],
                ['echec', 'Échec']
              ].forEach(function (optionData) {
                const option = document.createElement('option');
                option.value = optionData[0];
                option.textContent = optionData[1];
                control.appendChild(option);
              });
            } else {
              control = document.createElement('input');
              control.type = 'text';
              control.className = 'form-control';
              if (field.placeholder) control.placeholder = field.placeholder;
              if (field.pattern) control.pattern = field.pattern;
              if (field.maxLength) control.maxLength = field.maxLength;
              if (field.value) control.value = field.value;
            }

            control.id = fieldId;
            control.name = field.name + '[' + index + ']';
            control.required = true;
            column.append(label, control);
            row.appendChild(column);
          });

          card.appendChild(row);
          metadataContainer.appendChild(card);
        });
      });

      if (!form) {
        return;
      }
      const submitButton = form.querySelector('button[type="submit"], input[type="submit"]');
      if (submitButton) {
        form.dataset.bulletinSubmitLabel = submitButton.textContent || submitButton.value;
        form.dataset.bulletinSubmitDisabled = submitButton.disabled ? 'true' : 'false';
      }

      form.addEventListener('submit', async function (event) {
        if (form.dataset.bulletinsCompressed === 'true') {
          delete form.dataset.bulletinsCompressed;
          return;
        }
        if (event.defaultPrevented || fileInput.files.length === 0) {
          return;
        }
        if (form.dataset.bulletinCompressionInProgress === 'true') {
          event.preventDefault();
          return;
        }

        event.preventDefault();
        form.dataset.bulletinCompressionInProgress = 'true';
        const submitter = event.submitter || submitButton;
        if (submitter) {
          submitter.disabled = true;
        }
        if (status) {
          status.className = 'small text-muted mt-2';
          status.textContent = 'Compression des images en cours…';
        }

        try {
          const originalFiles = Array.from(fileInput.files);
          const compressedFiles = await Promise.all(originalFiles.map(compressImage));
          const transfer = new window.DataTransfer();
          compressedFiles.forEach(function (file) {
            transfer.items.add(file);
          });
          fileInput.files = transfer.files;

          const originalSize = originalFiles.reduce(function (total, file) { return total + file.size; }, 0);
          const compressedSize = compressedFiles.reduce(function (total, file) { return total + file.size; }, 0);
          if (status) {
            status.className = 'small text-success mt-2';
            status.textContent = compressedSize < originalSize
              ? 'Images optimisées avant envoi : ' + formatBytes(originalSize) + ' → ' + formatBytes(compressedSize) + '.'
              : 'Les images sont déjà optimisées ; les fichiers originaux sont conservés.';
          }

          form.dataset.bulletinsCompressed = 'true';
          if (submitter) {
            submitter.disabled = false;
          }
          form.requestSubmit(submitter || undefined);
        } catch (error) {
          console.error('Échec de la compression des bulletins :', error);
          if (submitter) {
            submitter.disabled = form.dataset.bulletinSubmitDisabled === 'true';
            if ('value' in submitter && submitter.tagName === 'INPUT') {
              submitter.value = form.dataset.bulletinSubmitLabel || '';
            } else {
              submitter.textContent = form.dataset.bulletinSubmitLabel || '';
            }
          }
          if (status) {
            status.className = 'small text-danger mt-2';
            status.textContent = error.message || 'Impossible de compresser les images. Aucune image n’a été envoyée.';
          }
        } finally {
          delete form.dataset.bulletinCompressionInProgress;
        }
      });
    });
  });
})();
