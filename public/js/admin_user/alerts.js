// SweetAlert helpers and feedback display for users module
function mostrarSweetAlert(tipo, mensaje) {
  if (typeof Swal === 'undefined') return;
  Swal.fire({
    icon: tipo === 'success' ? 'success' : 'error',
    title: tipo === 'success' ? '¡Hecho!' : 'Ups...',
    text: mensaje,
    confirmButtonText: 'Cerrar'
  });
}

(function(){
  const feedback = document.getElementById('feedback-messages');
  if (!feedback) return;
  const success = feedback.dataset.success || '';
  const error = feedback.dataset.error || '';
  if (success) {
    mostrarSweetAlert('success', success);
  } else if (error) {
    mostrarSweetAlert('error', error);
  }
})();
