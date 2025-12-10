<?php
require_once __DIR__ . '/../db/db_conn.php';
session_start();

$id = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : 0;
$usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$apellidos = isset($_POST['apellidos']) ? trim($_POST['apellidos']) : '';
$rol = isset($_POST['rol']) ? trim($_POST['rol']) : '';
$password = $_POST['password'] ?? '';
$password2 = $_POST['password2'] ?? '';

if ($id <= 0) {
    $_SESSION['edit_user_error'] = 'ID de usuario inválido.';
    header('Location: /sayianhub_fase2/public/pages/admin/users/admin.php');
    exit;
}

if ($usuario === '') {
    $_SESSION['edit_user_error'] = 'El nombre de usuario no puede estar vacío.';
    header('Location: /sayianhub_fase2/public/pages/admin/users/admin.php');
    exit;
}

// Validar rol permitido
$rolesPermitidos = ['camarero','admin','gerente','mantenimiento'];
if (!in_array($rol, $rolesPermitidos, true)) {
    $_SESSION['edit_user_error'] = 'Rol inválido.';
    header('Location: /sayianhub_fase2/public/pages/admin/users/admin.php');
    exit;
}

try {
    $conn = connection($host, $user, $pass, $db);

    $stmt = $conn->prepare('SELECT id_usuario FROM usuarios WHERE usuario = :usuario AND id_usuario != :id');
    $stmt->execute([':usuario' => $usuario, ':id' => $id]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        $_SESSION['edit_user_error'] = 'El nombre de usuario ya está en uso.';
        header('Location: /sayianhub_fase2/public/pages/admin/users/admin.php');
        exit;
    }

    $hashPassword = null;
    if ($password !== '' || $password2 !== '') {
        if ($password !== $password2) {
            $_SESSION['edit_user_error'] = 'Las contraseñas no coinciden.';
            header('Location: /sayianhub_fase2/public/pages/admin/users/admin.php');
            exit;
        }
        $hashPassword = password_hash($password, PASSWORD_DEFAULT);
    }

    if ($hashPassword) {
        $sql = 'UPDATE usuarios SET usuario = :usuario, nombre = :nombre, apellidos = :apellidos, rol = :rol, contrasena = :contrasena WHERE id_usuario = :id';
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':usuario' => $usuario,
            ':nombre' => $nombre,
            ':apellidos' => $apellidos,
            ':rol' => $rol,
            ':contrasena' => $hashPassword,
            ':id' => $id
        ]);
    } else {
        $sql = 'UPDATE usuarios SET usuario = :usuario, nombre = :nombre, apellidos = :apellidos, rol = :rol WHERE id_usuario = :id';
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':usuario' => $usuario,
            ':nombre' => $nombre,
            ':apellidos' => $apellidos,
            ':rol' => $rol,
            ':id' => $id
        ]);
    }

    $_SESSION['edit_user_success'] = 'Usuario actualizado correctamente.';
} catch (PDOException $e) {
    $_SESSION['edit_user_error'] = 'Error de BD: ' . $e->getMessage();
}

header('Location: /sayianhub_fase2/public/pages/admin/users/admin.php');
exit;
