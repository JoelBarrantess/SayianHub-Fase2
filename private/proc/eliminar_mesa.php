<?php

require_once '../db/db_conn.php';

$conn = connection($host, $user, $pass, $db);

$mesaId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$mesaId) {
    header('Location: ../../public/pages/admin/salas/admin_salas.php?status=invalid');
    exit;
}

try {
    $conn->beginTransaction();

    $stmtMesa = $conn->prepare('SELECT nombre_mesa, id_sala FROM mesas WHERE id_mesa = :id');
    $stmtMesa->execute([':id' => $mesaId]);
    $mesa = $stmtMesa->fetch(PDO::FETCH_ASSOC);

    if (!$mesa) {
        $conn->rollBack();
        header('Location: admin_salas.php?status=notfound');
        exit;
    }

    $stmtDelSillas = $conn->prepare('DELETE FROM sillas WHERE id_mesa = :id');
    $stmtDelSillas->execute([':id' => $mesaId]);

    $stmtDelMesa = $conn->prepare('DELETE FROM mesas WHERE id_mesa = :id');
    $stmtDelMesa->execute([':id' => $mesaId]);


    $conn->commit();

    header('Location: ../../public/pages/admin/salas/admin_salas.php?status=deleted');
    exit;
} catch (Throwable $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log('Error al eliminar mesa: ' . $e->getMessage());
    header('Location: ../../public/pages/admin/salas/admin_salas.php?status=error');
    exit;
}
