function alertSweet(icono, mensaje, callback) {
  if (typeof Swal === "undefined") return;
  Swal.fire({
    icon: icono,
    title: icono === "success" ? "¡Listo!" : "Ups...",
    html: mensaje,
    confirmButtonText: "Aceptar",
    confirmButtonColor: "#111827",
  }).then(function () {
    if (typeof callback === "function") callback();
  });
}

function limpiarError(campo, contenedor) {
  if (campo) campo.classList.remove("is-invalid");
  if (contenedor) {
    contenedor.textContent = "";
    contenedor.classList.add("d-none");
  }
}

function marcarError(campo, contenedor, mensaje) {
  if (campo) campo.classList.add("is-invalid");
  if (contenedor) {
    contenedor.textContent = mensaje;
    contenedor.classList.remove("d-none");
  }
}

function validarNombre() {
  var campo = document.getElementById("nombre");
  var error = document.getElementById("errorNombre");
  if (!campo) return true;
  if (campo.value.trim() === "") {
    marcarError(campo, error, "El nombre es obligatorio.");
    return false;
  }
  limpiarError(campo, error);
  return true;
}

function validarTipo() {
  var campo = document.getElementById("tipo");
  var error = document.getElementById("errorTipo");
  if (!campo) return true;
  if (campo.value.trim() === "") {
    marcarError(campo, error, "Selecciona un tipo válido.");
    return false;
  }
  limpiarError(campo, error);
  return true;
}

function validarCapacidad() {
  var campo = document.getElementById("capacidad");
  var error = document.getElementById("errorCapacidad");
  if (!campo) return true;
  var valor = campo.value.trim();
  if (valor === "" || parseInt(valor, 10) <= 0) {
    marcarError(campo, error, "La capacidad debe ser mayor que cero.");
    return false;
  }
  limpiarError(campo, error);
  return true;
}

window.onload = function () {
  var form = document.getElementById("form-crear-sala");
  if (!form) return;

  if ((document.body.dataset.status || "") === "success") {
    alertSweet("success", "Sala creada correctamente.", function () {
      window.location.href = "admin_salas.php";
    });
    return;
  }

  var nombre = document.getElementById("nombre");
  var tipo = document.getElementById("tipo");
  var capacidad = document.getElementById("capacidad");
  var errorNombre = document.getElementById("errorNombre");
  var errorTipo = document.getElementById("errorTipo");
  var errorCapacidad = document.getElementById("errorCapacidad");

  if (nombre) {
    nombre.onblur = validarNombre;
    nombre.oninput = function () {
      limpiarError(nombre, errorNombre);
    };
  }
  if (tipo) {
    tipo.onblur = validarTipo;
    tipo.onchange = function () {
      limpiarError(tipo, errorTipo);
    };
  }
  if (capacidad) {
    capacidad.onblur = validarCapacidad;
    capacidad.oninput = function () {
      limpiarError(capacidad, errorCapacidad);
    };
  }

  form.onsubmit = function (event) {
    var okNombre = validarNombre();
    var okTipo = validarTipo();
    var okCapacidad = validarCapacidad();

    if (!okNombre || !okTipo || !okCapacidad) {
      event.preventDefault();
      alertSweet(
        "error",
        '<ul class="text-start mb-0">' +
          (okNombre ? "" : "<li>El nombre es obligatorio.</li>") +
          (okTipo ? "" : "<li>Selecciona un tipo válido.</li>") +
          (okCapacidad
            ? ""
            : "<li>La capacidad debe ser mayor que cero.</li>") +
          "</ul>"
      );
    }
  };
};
