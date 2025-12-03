function mostrarSweetAlert(tipo, mensaje) {
  if (typeof Swal === 'undefined') return;
  Swal.fire({
    icon: tipo === 'success' ? 'success' : 'error',
    title: tipo === 'success' ? '¡Hecho!' : 'Ups...',
    text: mensaje,
    confirmButtonText: 'Cerrar'
  });
}

window.onload = function () {
  const u = document.getElementById('username');
  const p = document.getElementById('password');

  if (u) u.onblur = validarUsuario;
  if (p) p.onblur = validarPassword;

  if (p) p.type = 'password';

  const toggle = document.getElementById('togglePassword');
  if (toggle) {
    toggle.onclick = togglePasswordVisibility;
    toggle.setAttribute('aria-pressed', 'false');
    const imgs = toggle.getElementsByTagName('img');
    if (imgs && imgs.length) imgs[0].src = '../assets/eye-closed.png';
  }

  if (window.LOGIN_ALERT && window.LOGIN_ALERT.text) {
    mostrarSweetAlert(window.LOGIN_ALERT.type || 'error', window.LOGIN_ALERT.text);
    window.LOGIN_ALERT = null;
  }
  try {
    const err1 = new URLSearchParams(window.location.search).get('error');
    if (err1 === 'credenciales_invalidas' && typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'error',
        title: 'Credenciales incorrectas',
        text: 'El usuario o la contraseña no son válidos.'
      });
      if (window.history && window.history.replaceState) window.history.replaceState({}, document.title, window.location.pathname);
    }

    const err2 = new URLSearchParams(window.location.search).get('error');
    if (err2 === 'error_bd' && typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'error',
        title: 'Error en el servidor',
        text: 'Error en la base de datos. Inténtalo más tarde.'
      });
      if (window.history && window.history.replaceState) window.history.replaceState({}, document.title, window.location.pathname);
    }

    const err3 = new URLSearchParams(window.location.search).get('error');
    if (err3 === 'campos_vacios' && typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'error',
        title: 'Campos vacíos',
        text: 'Por favor, introduce usuario y contraseña.'
      });
      if (window.history && window.history.replaceState) window.history.replaceState({}, document.title, window.location.pathname);
    }
  } catch (e) {
  }
};



function validarUsuario() {
  const input = document.getElementById('username');
  const error = document.getElementById('errorUsuario');
  if (!input) return;
  const valor = input.value.trim();

  if (valor === '') {
    input.style.border = '2px solid red';
    if (error) { error.textContent = 'El usuario es obligatorio.'; error.style.color = 'red'; }
  } else if (valor.length < 3) {
    input.style.border = '2px solid red';
    if (error) { error.textContent = 'Debe tener al menos 3 caracteres.'; error.style.color = 'red'; }
  } else {
    input.style.border = '';
    if (error) error.textContent = '';
  }
}

function validarPassword() {
  const input = document.getElementById('password');
  const error = document.getElementById('errorPassword');
  if (!input) return;
  const valor = input.value;
  if (valor === '') {
    input.style.border = '2px solid red';
    if (error) { error.textContent = 'La contraseña es obligatoria.'; error.style.color = 'red'; }
  } else {
    input.style.border = '';
    if (error) error.textContent = '';
  }
}

function togglePasswordVisibility() {
  const passwordInput = document.getElementById('password');
  const toggleBtn = document.getElementById('togglePassword');
  if (!passwordInput || !toggleBtn) return;

  const imgs = toggleBtn.getElementsByTagName('img');
  const img = (imgs && imgs.length) ? imgs[0] : null;

  if (passwordInput.type === 'password') {
    passwordInput.type = 'text';
    if (img) img.src = '../assets/eye-open.png';
    toggleBtn.setAttribute('aria-pressed', 'true');
  } else {
    passwordInput.type = 'password';
    if (img) img.src = '../assets/eye-closed.png';
    toggleBtn.setAttribute('aria-pressed', 'false');
  }
}



