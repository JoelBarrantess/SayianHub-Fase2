<?php
session_start();
require_once __DIR__ . '/../db/db_conn.php';

if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['rol'], ['admin', 'gerente', 'mantenimiento'])) {
    header('Location: ../../public/pages/login.html');
    exit;
}

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $conn = connection($host, $user, $pass, $db);
        $stmt = $conn->prepare("DELETE FROM recursos WHERE id_recurso = :id");
        $stmt->execute([':id' => $id]);
        
        header('Location: ../../public/pages/admin/recursos/crud_recursos.php?msg=deleted');
        exit;
    } catch (PDOException $e) {
        die("Error al eliminar: " . $e->getMessage());
    }
} else {
    header('Location: ../../public/pages/admin/recursos/crud_recursos.php?error=noid');
    exit;
}
