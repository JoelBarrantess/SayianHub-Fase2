<?php
session_start();
require_once __DIR__ . '/../db/db_conn.php';

$conn = null;
try {
    $conn = connection($host, $user, $pass, $db);
} catch (Exception $e) {
    header('Location: ../../public/pages/login.html?error=error_bd');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    header('Location: ../../public/pages/login.html?error=campos_vacios');
    exit;
}

try {
    $stmt = $conn->prepare('SELECT id_usuario, nombre, apellidos, usuario, contrasena, rol FROM usuarios WHERE usuario = :username LIMIT 1');
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['contrasena'])) {
        header('Location: ../../public/pages/login.html?error=credenciales_invalidas');
        exit;
    }

    session_regenerate_id(true);
    $_SESSION['id_usuario'] = $user['id_usuario'];
    $_SESSION['username']   = $user['usuario'];
    $_SESSION['nombre']     = $user['nombre'] ?? '';
    $_SESSION['rol']        = $user['rol'] ?? 'camarero';

    $rol = $_SESSION['rol'];

    // Lógica de redirección simple según el rol
    if ($rol === 'admin' || $rol === 'gerente' || $rol === 'mantenimiento') {
        // Roles de gestión van al Dashboard
        header('Location: ../../public/pages/dashboard.php');
        exit;
    } else {
        // Camareros y otros van a la operativa diaria (Reservas/Salas)
        header('Location: ../../public/pages/room_selection.php');
        exit;
    }
} catch (PDOException $e) {
    header('Location: ../../public/pages/login.html?error=error_bd');
    exit;
}
?>
