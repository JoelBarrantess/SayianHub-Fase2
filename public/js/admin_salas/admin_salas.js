window.onload = function () {

  var datosSalas = window.SALAS_DATA || [];
  var botonesSalas = document.getElementsByClassName("sala-card-btn");
  var contenedorPrincipal = document.getElementById("salas-wrapper");
  var panelDetalle = document.getElementById("sala-detail");
  var textoPlaceholder = document.getElementById("detail-placeholder");
  var tituloSala = document.getElementById("detail-title");
  var infoSala = document.getElementById("detail-meta");
  var cuerpoTabla = document.querySelector("#detail-table tbody");
  var mensajeSinMesas = document.getElementById("detail-empty");
  var botonVolver = document.getElementById("detail-back");
  var rejillaSalas = document.getElementById("salas-grid");
  var tarjetaSeleccionada = document.getElementById("selected-card");

  function resetearSalas() {
    for (var i = 0; i < botonesSalas.length; i++) {
      var columna = botonesSalas[i].closest(".sala-col");
      if (columna) columna.classList.remove("is-hidden", "is-active");
      botonesSalas[i].classList.remove("is-active");
      botonesSalas[i].blur();
    }
    if (tarjetaSeleccionada) tarjetaSeleccionada.innerHTML = "";
    rejillaSalas.hidden = false;
  }

  function ocultarDetalle() {
    contenedorPrincipal.classList.remove("salas-wrapper--detail");
    resetearSalas();
    panelDetalle.hidden = true;
    textoPlaceholder.hidden = false;
    botonVolver.hidden = true;

    var botonEditarSala = document.getElementById('detail-edit');
    if (botonEditarSala) botonEditarSala.hidden = true;
  }

  function mostrarDetalle(idSala, botonPulsado) {
    // normalizar id a string para evitar problemas de tipo (num vs string)
    var buscada = String(idSala);
    var sala = datosSalas.find(function (s) {
      return String(s.id) === buscada;
    });
    if (!sala) {
      ocultarDetalle();
      return;
    }

    // Rellenamos título e información básica.
    tituloSala.textContent = sala.nombre;
    infoSala.textContent =
      "Tipo: " +
      sala.tipo +
      " · Capacidad total: " +
      sala.capacidad +
      " · Mesas: " +
      sala.mesas.length;

    // Limpiamos la tabla para volver a pintarla.
    cuerpoTabla.innerHTML = "";

    // Si no hay mesas, mostramos el aviso y salimos.
    if (!sala.mesas.length) {
      mensajeSinMesas.hidden = false;
    } else {
      mensajeSinMesas.hidden = true;

      // Recorremos cada mesa para crear su fila en la tabla.
      sala.mesas.forEach(function (mesa) {
        var fila = document.createElement("tr");

        // Nombre de la mesa.
        var celdaNombre = document.createElement("td");
        celdaNombre.textContent = mesa.numero;
        fila.appendChild(celdaNombre);

        // Número de sillas.
        var celdaCapacidad = document.createElement("td");
        celdaCapacidad.textContent = mesa.capacidad;
        fila.appendChild(celdaCapacidad);

        // Estado (libre/ocupada) con una chapita de color.
        var celdaEstado = document.createElement("td");
        var chapita = document.createElement("span");
        chapita.className =
          "badge rounded-pill " +
          (mesa.estado === "libre"
            ? "bg-success-subtle text-success"
            : "bg-warning-subtle text-warning");
        chapita.textContent =
          mesa.estado.charAt(0).toUpperCase() + mesa.estado.slice(1);
        celdaEstado.appendChild(chapita);
        fila.appendChild(celdaEstado);

        // Aquí añadimos los botones de editar y borrar.

        // Aquí añadimos los botones de editar y borrar.
        var celdaAcciones = document.createElement("td");
        celdaAcciones.className = "text-end";
        celdaAcciones.innerHTML = `
          <a href="./editar_salas.php?id=${sala.id}&mesa_id=${mesa.id}" class="btn btn-sm btn-outline-primary me-2">Editar</a>
          <a href="../../../../private/proc/eliminar_mesa.php?id=${mesa.id}&sala_id=${sala.id}" class="btn btn-sm btn-outline-danger">Eliminar</a>
        `;
        fila.appendChild(celdaAcciones);

        cuerpoTabla.appendChild(fila);
      });
    }

    // Escondemos todas las demás cards y dejamos solo la que tocamos.
    for (var i = 0; i < botonesSalas.length; i++) {
      var columna = botonesSalas[i].closest(".sala-col");
      if (!columna) continue;

      if (botonesSalas[i] === botonPulsado) {
        columna.classList.remove("is-hidden");
        columna.classList.add("is-active");
        botonesSalas[i].classList.add("is-active");
      } else {
        columna.classList.remove("is-active");
        columna.classList.add("is-hidden");
        botonesSalas[i].classList.remove("is-active");
      }
    }

    // Copiamos la card de la sala elegida para mostrarla encima de la tabla.
    tarjetaSeleccionada.innerHTML = `
      <div class="card border-0 sala-card-btn text-start">
        <div class="card-body d-flex flex-column gap-3">
          <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 px-3 py-2 icon-chip">
              <i class="${sala.icon_class}"></i>
            </div>
            <div>
              <h5 class="card-title mb-1 fw-semibold text-dark">${
                sala.nombre
              }</h5>
              <small class="text-uppercase text-secondary fw-semibold">${sala.tipo.toUpperCase()}</small>
            </div>
          </div>
          <div>
            <span class="display-6 fw-bold d-block mb-1 text-dark">${
              sala.capacidad
            }</span>
            <span class="text-muted small fw-medium">Capacidad total · Mesas: ${
              sala.mesas.length
            }</span>
          </div>
        </div>
      </div>
    `;

    contenedorPrincipal.classList.add("salas-wrapper--detail");
    rejillaSalas.hidden = true;
    textoPlaceholder.hidden = true;
    panelDetalle.hidden = false;
    botonVolver.hidden = false;

    // asignar href dinámico al botón "Editar sala" y mostrarlo
    var botonEditarSala = document.getElementById('detail-edit');
    if (botonEditarSala) {
      botonEditarSala.href = './editar_salas.php?id=' + encodeURIComponent(sala.id);
      botonEditarSala.hidden = false;
    }

    var deleteInput = document.getElementById('detail-delete-id');
    var deleteForm  = document.getElementById('detail-delete-form');
    if (deleteInput) deleteInput.value = sala.id;
    if (deleteForm) deleteForm.action = "../../../../private/proc/eliminar_sala.php";
    
    // Después de dibujar las filas, activamos los botones con SweetAlert.
    activarBotonesMesa();
  }

  // Esta función pone el SweetAlert a cada botón de editar/borrar.
  function activarBotonesMesa() {
    document.querySelectorAll(".btn-mesa-action").forEach(function (boton) {
      boton.onclick = function () {
        var mesaId = this.getAttribute("data-mesa-id");
        var salaId = this.getAttribute("data-sala-id");
        var nombreMesa = this.getAttribute("data-mesa");
        var tipoAccion = this.getAttribute("data-action");

        if (!mesaId) return;

        var esEliminar = tipoAccion === "delete";

        Swal.fire({
          title: esEliminar ? "¿Qué deseas eliminar?" : "¿Qué deseas editar?",
          text: nombreMesa,
          icon: esEliminar ? "warning" : "question",
          showCancelButton: true,
          showDenyButton: true,
          confirmButtonText: "Mesa",
          denyButtonText: "Sillas",
          cancelButtonText: "Cancelar",
          confirmButtonColor: esEliminar ? "#ef4444" : "#2563eb",
          denyButtonColor: "#0f172a",
        }).then(function (resultado) {
          if (resultado.isConfirmed) {
            var urlMesa = esEliminar
              ? "../../private/proc/eliminar_mesa.php"
              : "../../private/proc/editar_mesa.php";
            window.location.href =
              urlMesa + "?mesa=" + mesaId + "&sala=" + salaId;
          } else if (resultado.isDenied) {
            var urlSillas = esEliminar
              ? "../../private/proc/eliminar_sillas.php"
              : "../../private/proc/editar_sillas.php";
            window.location.href =
              urlSillas + "?mesa=" + mesaId + "&sala=" + salaId;
          }
        });
      };
    });

    var deleteBtn = document.getElementById('detail-delete-btn');
    var deleteForm = document.getElementById('detail-delete-form');
    if (deleteBtn && deleteForm) {
      deleteBtn.onclick = function (evt) {
        evt.preventDefault();
        Swal.fire({
          title: '¿Eliminar sala?',
          text: 'Se eliminará la sala y todas sus mesas. Esta acción no se puede deshacer.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Sí, eliminar',
          cancelButtonText: 'Cancelar',
          reverseButtons: true
        }).then(function (result) {
          if (result.isConfirmed) {
            Swal.fire({
              title: 'Eliminando...',
              allowOutsideClick: false,
              didOpen: function () { Swal.showLoading(); }
            });
            deleteForm.submit();
          }
        });
      };
    }
  }

  // Empezamos con todo limpio.
  ocultarDetalle();

  // Cuando se hace clic en una card, mostramos su detalle.
  for (var i = 0; i < botonesSalas.length; i++) {
    botonesSalas[i].onclick = function () {
      var el = this;
      // intentar leer data en el propio botón, si no existe buscar en el ancestro .sala-col
      var idAttr = el.getAttribute("data-sala-id");
      if (!idAttr) {
        var parent = el.closest(".sala-col");
        idAttr = parent ? parent.getAttribute("data-sala-id") : null;
      }
      if (!idAttr) return;
      var idSala = parseInt(idAttr, 10);
      mostrarDetalle(idSala, this);
    };
  }

  botonVolver.onclick = ocultarDetalle;
};
