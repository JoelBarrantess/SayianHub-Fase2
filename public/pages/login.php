<?php
$errorKey = isset($_GET['error']) ? $_GET['error'] : null;
$logout = isset($_GET['logout']) && $_GET['logout'] === '1';

$messages = [
  'campos_vacios' => 'Por favor completa todos los campos.',
  'credenciales_invalidas' => 'Usuario o contraseña incorrectos.',
  'error_bd' => 'Error en el servidor. Intenta más tarde.'
];

$alertText = $logout ? 'Has cerrado sesión correctamente.' : ($errorKey ? ($messages[$errorKey] ?? 'Ha ocurrido un error.') : null);
$alertType = $logout ? 'success' : 'error';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>SaiyanHub — Login</title>
  <link rel="icon" type="image/x-icon" href="../assets/logo_simple.png" />
  <link rel="stylesheet" href="../css/login.css" />
  <script src="../js/login.js" defer></script>
</head>
<body>
  <main class="stage">
    <div class="box">
      <div class="left">
        <div class="logo-wrapper" aria-hidden="true">
          <img class="login-logo" src="../assets/logo_simple.png" alt="" />
        </div>
      </div>

      <div class="right">
        <form class="card" method="post" action="../../private/proc/procesar_login.php">
          <h2>Acceder</h2>

          <div class="form-group field">
            <label for="username" class="sr-only">Usuario</label>
            <input class="input" id="username" name="username" type="text" placeholder="Usuario" autocomplete="username" />
            <span id="errorUsuario" class="error" aria-live="polite"></span>
          </div>

          <div class="form-group field has-right-icon">
            <label for="password" class="sr-only">Contraseña</label>
            <div class="input-wrap">
              <input class="input" id="password" name="password" type="password" placeholder="Contraseña" autocomplete="current-password" />
              <button type="button" id="togglePassword" class="eye-btn" aria-label="Mostrar contraseña" aria-pressed="false">
                <img src="../assets/eye-closed.png" alt="" />
              </button>
            </div>
            <span id="errorPassword" class="error" aria-live="polite"></span>
          </div>

          <button class="submit" type="submit">Acceder</button>

          <div class="help">¿Olvidaste tu contraseña? · ¿Necesitas ayuda?</div>
        </form>
      </div>
    </div>
  </main>

  <?php if ($alertText): ?>
    <script>
      window.LOGIN_ALERT = <?php echo json_encode(['text' => $alertText, 'type' => $alertType]); ?>;
    </script>
  <?php endif; ?>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="../js/login.js"></script>
</body>
</html>
