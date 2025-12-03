window.onload = function() {
  inicializarSillas();
  inicializarMesas(); 
};

const inicializarSillas = () => {
  let sillas = document.getElementsByClassName('silla');
  for (let i = 0; i < sillas.length; i++) {
    let silla = sillas[i];
    let angle = silla.getAttribute('data-angle');
    silla.style.setProperty('--angle', angle);
    silla.onclick = crearManejadorClickSilla(silla);
  }
}

const crearManejadorClickSilla = (silla) => {
  return function(event) {
    event.stopPropagation();
    
    let url = silla.getAttribute('data-url');
    let ocupada = silla.getAttribute('data-ocupada') === 'true';
    let mesaNombre = silla.getAttribute('data-mesa');
    let numeroSilla = silla.getAttribute('data-numero');

    Swal.fire({
      title: ocupada ? '¿Liberar silla?' : '¿Ocupar silla?',
      html: '<strong>' + mesaNombre + ' - Silla ' + numeroSilla + '</strong>',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: ocupada ? '<i class="bi bi-check-circle"></i> Liberar' : '<i class="bi bi-lock-fill"></i> Ocupar',
      cancelButtonText: '<i class="bi bi-x-circle"></i> Cancelar',
      confirmButtonColor: ocupada ? '#10b981' : '#ef4444', 
      cancelButtonColor: '#6b7280',
      background: '#ffffff',
      color: '#212529'
    }).then(function(result) {
      if (result.isConfirmed) {
        window.location.href = url;
      }
    });
  };
}

const inicializarMesas = () => {
  let mesas = document.getElementsByClassName('mesa');
  for (let i = 0; i < mesas.length; i++) {
    let mesa = mesas[i];
    mesa.onclick = crearManejadorClickMesa(mesa);
  }
}

const crearManejadorClickMesa = (mesa) => {
  return function(event) {
    if (event.target.classList.contains('silla')) return;

    let url = mesa.getAttribute('data-url');
    let ocupada = mesa.classList.contains('ocupada'); 
    let mesaNombre = mesa.querySelector('.mesa-nombre').textContent;

    Swal.fire({
      title: ocupada ? '¿Liberar TODA la mesa?' : '¿Ocupar TODA la mesa?',
      html: '<strong>' + mesaNombre + '</strong><br><small>Esta acción afectará a <b>TODAS</b> las sillas.</small>',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: ocupada ? '<i class="bi bi-check-circle"></i> Liberar Todo' : '<i class="bi bi-lock-fill"></i> Ocupar Todo',
      cancelButtonText: '<i class="bi bi-x-circle"></i> Cancelar',
      confirmButtonColor: ocupada ? '#0d6efd' : '#dc3545',
      cancelButtonColor: '#6b7280',
      background: '#ffffff',
      color: '#111827'
    }).then(function(result) {
      if (result.isConfirmed) { 
        window.location.href = url; 
      }
    });
  };
}
