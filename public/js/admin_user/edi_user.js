
function mostrarErrorPorId(errorId, mensaje) {
  const el = document.getElementById(errorId);
  if (!el) return;
  el.textContent = mensaje;
  el.style.display = mensaje ? 'block' : 'none';
}

function marcarInputError(inputEl, active) {
  if (!inputEl) return;
  inputEl.style.border = active ? '2px solid #ef4444' : '';
}

function validarUsuario() {
  const input = document.getElementById('usuario');
  const valor = input ? input.value.trim() : '';
  if (!input) return true;
  if (valor.length === 0) {
    marcarInputError(input, true);
    mostrarErrorPorId('errorUsuario', 'El usuario es obligatorio.');
    return false;
  }
  if (valor.length < 3) {
    marcarInputError(input, true);
    mostrarErrorPorId('errorUsuario', 'Debe tener al menos 3 caracteres.');
    return false;
  }
  marcarInputError(input, false);
  mostrarErrorPorId('errorUsuario', '');
  return true;
}

function validarNombre() {
  const input = document.getElementById('nombre');
  if (!input) return true;
  const valor = input.value.trim();
  if (valor.length < 3) {
    marcarInputError(input, true);
    mostrarErrorPorId('errorNombre', 'Debe tener al menos 3 caracteres.');
    return false;
  }
  marcarInputError(input, false);
  mostrarErrorPorId('errorNombre', '');
  return true;
}

function validarApellidos() {
  const input = document.getElementById('apellidos');
  if (!input) return true;
  const valor = input.value.trim();
  if (valor.length < 3) {
    marcarInputError(input, true);
    mostrarErrorPorId('errorApellidos', 'Debe tener al menos 3 caracteres.');
    return false;
  }
  marcarInputError(input, false);
  mostrarErrorPorId('errorApellidos', '');
  return true;
}

function validarPassword() {
  const input = document.getElementById('password');
  if (!input) return true;
  const valor = input.value;
  if (valor.length > 0 && valor.length < 6) {
    marcarInputError(input, true);
    mostrarErrorPorId('errorPassword', 'La contraseña debe tener al menos 6 caracteres.');
    return false;
  }
  marcarInputError(input, false);
  mostrarErrorPorId('errorPassword', '');
  // si hay valor en password2, revalidarlo para mantener sincronía
  const pw2 = document.getElementById('password2');
  if (pw2 && pw2.value.length > 0) validarPassword2();
  return true;
}

function validarPassword2() {
  const pw = document.getElementById('password');
  const pw2 = document.getElementById('password2');
  if (!pw2) return true;
  const v1 = pw ? pw.value : '';
  const v2 = pw2.value;
  if (v1.length > 0 && v1 !== v2) {
    marcarInputError(pw2, true);
    mostrarErrorPorId('errorPassword2', 'Las contraseñas no coinciden.');
    return false;
  }
  marcarInputError(pw2, false);
  mostrarErrorPorId('errorPassword2', '');
  return true;
}

window.onload = function () {
  const u = document.getElementById('usuario');
  const n = document.getElementById('nombre');
  const a = document.getElementById('apellidos');
  const p = document.getElementById('password');
  const p2 = document.getElementById('password2');
  const form = document.querySelector('.edit-card form');

  if (u) { u.onblur = validarUsuario; u.oninput = validarUsuario; }
  if (n) { n.onblur = validarNombre; n.oninput = validarNombre; }
  if (a) { a.onblur = validarApellidos; a.oninput = validarApellidos; }
  if (p) { p.onblur = validarPassword; p.oninput = validarPassword; }
  if (p2) { p2.onblur = validarPassword2; p2.oninput = validarPassword2; }

  if (!form) return;
  form.onsubmit = function (e) {
    // ejecutar todas las validaciones
    const ok = validarUsuario() & validarNombre() & validarApellidos() & validarPassword() & validarPassword2();
    if (!ok) {
      e.preventDefault();
      // enfocar primer error visible
      const firstError = document.querySelector('.field-error:not(:empty)');
      if (firstError) {
        // buscar input asociado por data-for o por id convencional
        const forAttr = firstError.getAttribute('data-for');
        const target = forAttr ? document.querySelector('[name="' + forAttr + '"]') || document.getElementById(forAttr) : null;
        if (target) target.focus();
      }
      return false;
    }
    // evitar doble envío
    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) submitBtn.disabled = true;
  };
};
