<?php
require_once __DIR__ . '/../db/db_conn.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo 'Método no permitido';
    exit;
}

$id_usuario = isset($_POST['id_usuario']) ? (int) $_POST['id_usuario'] : 0;

if ($id_usuario <= 0) {
    $_SESSION['edit_user_error'] = 'ID inválido';
    header('Location: ../../public/pages/admin/users/admin.php');
    exit;
}

try {
    $conn = connection($host, $user, $pass, $db);

    $stmt = $conn->prepare('SELECT rol FROM usuarios WHERE id_usuario = :id');
    $stmt->execute([':id' => $id_usuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        $_SESSION['edit_user_error'] = 'Usuario no encontrado';
        header('Location: ../../public/pages/admin/users/admin.php');
        exit;
    }

    if ($usuario['rol'] === 'admin') {
        $total_admins = $conn->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'admin'")->fetchColumn();
        if ($total_admins <= 1) {
            $_SESSION['edit_user_error'] = 'No se puede eliminar al último administrador';
            header('Location: ../../public/pages/admin/users/admin.php');
            exit;
        }
    }

    $stmt = $conn->prepare('DELETE FROM usuarios WHERE id_usuario = :id');
    $stmt->execute([':id' => $id_usuario]);

    $_SESSION['edit_user_success'] = 'Usuario eliminado correctamente.';
} catch (PDOException $e) {
    $_SESSION['edit_user_error'] = 'Error: ' . $e->getMessage();
}

header('Location: ../../public/pages/admin/users/admin.php');
exit;
