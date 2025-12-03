<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../../private/db/db_conn.php';

// Verificar sesión de administrador
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../../login.html');
    exit;
}

// Escapar texto manualmente sin función
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../../../private/proc/procesar_editar_usuario.php';
    if (!empty($result) && isset($result['success']) && $result['success'] === true) {
        $_SESSION['edit_user_success'] = $result['message'] ?? 'Usuario actualizado correctamente.';
        header('Location: ./admin.php');
        exit;
    } else {
        $error = $result['error'] ?? 'Error al procesar la solicitud.';
    }
}

$userData = null;
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id > 0) {
    try {
        $conn = connection($host, $user, $pass, $db);
        $stmt = $conn->prepare('SELECT id_usuario, usuario, nombre, apellidos, rol FROM usuarios WHERE id_usuario = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userData) {
            $_SESSION['edit_user_error'] = 'Usuario no encontrado.';
            header('Location: ./admin.php');
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['edit_user_error'] = 'Error de BD: ' . $e->getMessage();
        header('Location: ./admin.php');
        exit;
    }
} else {
    if (!isset($_POST['id_usuario'])) {
        $_SESSION['edit_user_error'] = 'ID de usuario no especificado.';
        header('Location: ./admin.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($error)) {
    $userData = [
        'id_usuario' => $_POST['id_usuario'] ?? $id,
        'usuario' => $_POST['usuario'] ?? '',
        'nombre' => $_POST['nombre'] ?? '',
        'apellidos' => $_POST['apellidos'] ?? '',
        'rol' => $_POST['rol'] ?? ''
    ];
}
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Editar usuario — SaiyanHub</title>
    <link rel="stylesheet" href="../../../css/edi_user.css">
    <style>
        .field-error {
            color: #ef4444;
            font-size: 0.9rem;
            margin-top: .25rem;
            display: none;
        }

        .close-link {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.5rem;
            text-decoration: none;
            color: #333;
        }

        .close-link:hover {
            color: #000;
        }
    </style>
</head>

<body>
    <main class="edit-stage">
        <div class="edit-card" style="position: relative;">
            <a href="./admin.php" class="close-link" aria-label="Cerrar">&times;</a>
            <h2>Editar usuario</h2>

            <?php if (!empty($error)): ?>
                <div class="alert-error"><?= $error, ENT_QUOTES, 'UTF-8' ?></div>
            <?php endif; ?>

            <form method="post" action="editar_user.php">
                <input type="hidden" name="id_usuario" value="<?= isset($userData['id_usuario']) ? $userData['id_usuario'] : '' ?>">

                <label class="form-row">Usuario (login)
                    <input id="usuario" class="input" type="text" name="usuario" value="<?= isset($userData['usuario']) ? $userData['usuario'] : '' ?>" required>
                </label>
                <div id="errorUsuario" class="field-error" data-for="usuario" aria-live="polite"></div>

                <label class="form-row">Nombre
                    <input id="nombre" class="input" type="text" name="nombre" value="<?= isset($userData['nombre']) ? $userData['nombre'] : '' ?>">
                </label>
                <div id="errorNombre" class="field-error" data-for="nombre" aria-live="polite"></div>

                <label class="form-row">Apellidos
                    <input id="apellidos" class="input" type="text" name="apellidos" value="<?= isset($userData['apellidos']) ? $userData['apellidos'] : '' ?>">
                </label>
                <div id="errorApellidos" class="field-error" data-for="apellidos" aria-live="polite"></div>

                <label class="form-row">Rol
                    <select id="rol" class="select" name="rol">
                        <option value="camarero" <?= (isset($userData['rol']) && $userData['rol'] === 'camarero') ? 'selected' : '' ?>>Camarero</option>
                        <option value="admin" <?= (isset($userData['rol']) && $userData['rol'] === 'admin') ? 'selected' : '' ?>>Admin</option>
                    </select>
                </label>
                <div id="errorRol" class="field-error" data-for="rol" aria-live="polite"></div>

                <div class="hr"></div>
                <p class="muted">Dejar en blanco para mantener la contraseña actual.</p>

                <label class="form-row">Nueva contraseña
                    <input id="password" class="input" type="password" name="password" autocomplete="new-password">
                </label>
                <div id="errorPassword" class="field-error" data-for="password" aria-live="polite"></div>

                <label class="form-row">Repetir nueva contraseña
                    <input id="password2" class="input" type="password" name="password2" autocomplete="new-password">
                </label>
                <div id="errorPassword2" class="field-error" data-for="password2" aria-live="polite"></div>

                <div class="actions" style="margin-top:1rem;">
                    <button type="submit" class="submit">Guardar cambios</button>
                </div>
            </form>
        </div>
    </main>
    <!-- incluir script de validación -->
    <script src="../../../js/admin_user/edi_user.js"></script>
</body>

</html>