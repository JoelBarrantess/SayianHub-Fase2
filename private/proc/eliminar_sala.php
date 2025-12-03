<?php

require_once __DIR__ . '/../db/db_conn.php';

$conn = connection($host, $user, $pass, $db);

$salaId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$salaId) {
    header('Location: ../../public/pages/admin/salas/admin_salas.php?status=invalid');
    exit;
}

try {
    $conn->beginTransaction();

    $stmtSala = $conn->prepare('SELECT nombre_sala FROM salas WHERE id_sala = :id');
    $stmtSala->execute([':id' => $salaId]);
    $sala = $stmtSala->fetch(PDO::FETCH_ASSOC);

    if (!$sala) {
        $conn->rollBack();
        header('Location: admin_salas.php?status=notfound');
        exit;
    }

    $stmtMesas = $conn->prepare('SELECT id_mesa FROM mesas WHERE id_sala = :id');
    $stmtMesas->execute([':id' => $salaId]);
    $mesasIds = $stmtMesas->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($mesasIds)) {
        $placeholders = implode(',', array_fill(0, count($mesasIds), '?'));
        $stmtSillas = $conn->prepare("DELETE FROM sillas WHERE id_mesa IN ($placeholders)");
        $stmtSillas->execute($mesasIds);
    }

    $stmtDelMesas = $conn->prepare('DELETE FROM mesas WHERE id_sala = :id');
    $stmtDelMesas->execute([':id' => $salaId]);

    $stmtDelSala = $conn->prepare('DELETE FROM salas WHERE id_sala = :id');
    $stmtDelSala->execute([':id' => $salaId]);

    $conn->commit();

    header('Location: ../../public/pages/admin/salas/admin_salas.php?status=deleted');
    exit;
} catch (Throwable $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log('Error al eliminar sala: ' . $e->getMessage());
    header('Location: ../../public/pages/admin/salas/admin_salas.php?status=error');
    exit;
}
