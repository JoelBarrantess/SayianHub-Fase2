(function () {
  var datos = window.EDITAR_SALA_DATA || {};
  console.log("Datos recibidos:", datos);

  var estados =
    Array.isArray(datos.estados) && datos.estados.length
      ? datos.estados
      : ["libre", "ocupada"];
  var nextIndex = Number.isInteger(datos.nextIndex) ? datos.nextIndex : 0;

  var contenedor = document.getElementById("mesas-container");
  var aviso = document.getElementById("sin-mesas");
  var formulario = document.getElementById("form-nueva-mesa");
  var btnAdd = document.getElementById("btn-add-mesa");
  var btnCancelar = document.getElementById("cancelar-nueva-mesa");
  var btnGuardar = document.getElementById("guardar-nueva-mesa");
  var inputNombre = document.getElementById("nuevo-nombre");
  var inputSillas = document.getElementById("nuevas-sillas");
  var selectEstado = document.getElementById("nuevo-estado");

  console.log("Elementos encontrados:", {
    contenedor: !!contenedor,
    aviso: !!aviso,
    formulario: !!formulario,
    btnAdd: !!btnAdd,
    btnCancelar: !!btnCancelar,
    btnGuardar: !!btnGuardar,
    inputNombre: !!inputNombre,
    inputSillas: !!inputSillas,
    selectEstado: !!selectEstado,
  });

  function actualizarAviso() {
    if (!aviso || !contenedor) return;
    var hayMesas = contenedor.getElementsByClassName("mesa-item").length > 0;
    aviso.classList.toggle("d-none", hayMesas);
  }

  function mostrarFormulario() {
    console.log("mostrarFormulario llamado");
    if (!formulario) {
      console.error("Formulario no encontrado");
      return;
    }
    formulario.classList.remove("d-none");
    if (inputNombre) inputNombre.focus();
    console.log("Formulario mostrado");
  }

  function ocultarFormulario() {
    console.log("ocultarFormulario llamado");
    if (!formulario) return;
    formulario.classList.add("d-none");
    if (inputNombre) inputNombre.value = "";
    if (inputSillas) inputSillas.value = "";
    if (selectEstado) selectEstado.value = "";
  }

  function opcionesEstados(seleccionado) {
    return estados
      .map(function (estado) {
        var marcado = estado === seleccionado ? " selected" : "";
        var texto = estado.charAt(0).toUpperCase() + estado.slice(1);
        return (
          '<option value="' + estado + '"' + marcado + ">" + texto + "</option>"
        );
      })
      .join("");
  }

  function escaparHTML(texto) {
    var div = document.createElement("div");
    div.textContent = texto;
    return div.innerHTML;
  }

  function guardarMesa() {
    console.log("guardarMesa llamado");
    if (!contenedor) return;

    var nombre = (inputNombre && inputNombre.value.trim()) || "";
    var sillas = inputSillas ? parseInt(inputSillas.value, 10) : 0;
    var estado = selectEstado ? selectEstado.value : "";

    console.log("Datos de la mesa:", { nombre, sillas, estado });

    var errores = [];
    if (!nombre) errores.push("Escribe un nombre para la mesa.");
    if (!Number.isInteger(sillas) || sillas <= 0)
      errores.push("Indica una cantidad de sillas válida.");
    if (!estado) errores.push("Selecciona un estado.");

    if (errores.length) {
      Swal.fire({
        icon: "error",
        title: "Revisa los datos",
        html:
          '<ul class="text-start mb-0">' +
          errores
            .map(function (e) {
              return "<li>" + e + "</li>";
            })
            .join("") +
          "</ul>",
        confirmButtonColor: "#111827",
      });
      return;
    }

    var key = "new_" + nextIndex++;
    var nombreEscapado = escaparHTML(nombre);

    var tarjeta =
      '<div class="col-12 col-md-6 mesa-item" data-index="' +
      key +
      '">' +
      '<div class="border rounded-3 p-3 h-100">' +
      '<div class="d-flex justify-content-between align-items-center mb-3">' +
      '<span class="badge text-bg-light">Mesa nueva</span>' +
      '<button type="button" class="btn btn-link btn-sm text-danger p-0" data-remove-mesa>' +
      '<i class="bi bi-x-circle me-1"></i>Quitar' +
      "</button>" +
      "</div>" +
      '<input type="hidden" name="mesas[' +
      key +
      '][id]" value="">' +
      '<div class="mb-3">' +
      '<label class="form-label" for="nombre_mesa_' +
      key +
      '">Nombre de la mesa</label>' +
      '<input type="text" class="form-control" id="nombre_mesa_' +
      key +
      '" ' +
      'name="mesas[' +
      key +
      '][nombre]" value="' +
      nombreEscapado +
      '" required>' +
      "</div>" +
      '<div class="mb-3">' +
      '<label class="form-label" for="sillas_' +
      key +
      '">Número de sillas</label>' +
      '<input type="number" min="1" class="form-control" id="sillas_' +
      key +
      '" ' +
      'name="mesas[' +
      key +
      '][sillas]" value="' +
      sillas +
      '" required>' +
      "</div>" +
      "<div>" +
      '<label class="form-label" for="estado_' +
      key +
      '">Estado</label>' +
      '<select class="form-select" id="estado_' +
      key +
      '" name="mesas[' +
      key +
      '][estado]" required>' +
      '<option value="" disabled>Selecciona un estado</option>' +
      opcionesEstados(estado) +
      "</select>" +
      "</div>" +
      "</div>" +
      "</div>";

    contenedor.insertAdjacentHTML("beforeend", tarjeta);
    ocultarFormulario();
    actualizarAviso();

    Swal.fire({
      icon: "success",
      title: "Mesa añadida",
      confirmButtonColor: "#111827",
      timer: 1500,
      showConfirmButton: false,
    });
  }

  function quitarMesa(boton) {
    console.log("quitarMesa llamado");
    var tarjeta = boton ? boton.closest(".mesa-item") : null;
    if (tarjeta) {
      tarjeta.remove();
      actualizarAviso();
    }
  }

  if (contenedor) {
    contenedor.addEventListener("click", function (e) {
      var botonQuitar = e.target.closest("[data-remove-mesa]");
      if (botonQuitar) {
        e.preventDefault();
        quitarMesa(botonQuitar);
      }
    });
  }

  function inicializar() {
    console.log("Inicializando eventos...");
    actualizarAviso();

    if (btnAdd) {
      console.log("Asignando evento al botón añadir");
      btnAdd.addEventListener("click", function (e) {
        e.preventDefault();
        console.log("Click en botón añadir detectado");
        mostrarFormulario();
      });
    } else {
      console.error("Botón añadir no encontrado");
    }

    if (btnCancelar) {
      btnCancelar.addEventListener("click", function (e) {
        e.preventDefault();
        ocultarFormulario();
      });
    }

    if (btnGuardar) {
      btnGuardar.addEventListener("click", function (e) {
        e.preventDefault();
        guardarMesa();
      });
    }

    console.log("Inicialización completada");
  }

  if (document.readyState === "loading") {
    console.log("Esperando DOMContentLoaded...");
    document.addEventListener("DOMContentLoaded", inicializar);
  } else {
    console.log("DOM ya cargado, inicializando inmediatamente");
    inicializar();
  }
})();
