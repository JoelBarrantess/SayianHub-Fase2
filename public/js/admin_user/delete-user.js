
document.addEventListener('DOMContentLoaded', function () {
  const forms = document.querySelectorAll('.delete-user-form');
  if (!forms.length) return;

  forms.forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();

      const username = form.dataset.username || '';

      Swal.fire({
        title: '¿Eliminar usuario?',
        html: '<strong>' + (username ? username : '') + '</strong><br><small>Esta acción no se puede deshacer</small>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-trash"></i> Eliminar',
        cancelButtonText: '<i class="bi bi-x-circle"></i> Cancelar',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        background: 'var(--card)',
        color: 'var(--primary)',
        customClass: {
          popup: 'swal-popup-custom',
          confirmButton: 'swal-btn-confirm',
          cancelButton: 'swal-btn-cancel'
        }
      }).then(function(result) {
        if (result.isConfirmed) {
          // Enviamos el formulario cuando el usuario confirma
          form.submit();
        }
      });
    });
  });
});
