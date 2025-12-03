<?php
require_once '../../private/db/db_conn.php';
session_start();

if (!isset($_GET['id_silla'], $_GET['id_sala'])) {
    die("Datos insuficientes.");
}

$id_silla = (int) $_GET['id_silla'];
$id_sala = (int) $_GET['id_sala'];

if (!isset($_SESSION['id_usuario'])) {
    die("Debe iniciar sesión para realizar esta acción.");
}

$id_usuario = $_SESSION['id_usuario'];



try {
    $conn = connection($host, $user, $pass, $db);
    $conn->beginTransaction();

    $stmt = $conn->prepare("SELECT s.estado, s.id_mesa FROM sillas s WHERE s.id_silla = :id_silla");
    $stmt->bindParam(':id_silla', $id_silla, PDO::PARAM_INT);
    $stmt->execute();
    $silla = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$silla) {
        throw new Exception("Silla no encontrada");
    }

    $id_mesa = $silla['id_mesa'];
    $nuevoEstado = $silla['estado'] === 'ocupada' ? 'libre' : 'ocupada';

    $stmt = $conn->prepare("UPDATE sillas SET estado = :estado WHERE id_silla = :id_silla");
    $stmt->bindParam(':estado', $nuevoEstado, PDO::PARAM_STR);
    $stmt->bindParam(':id_silla', $id_silla, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = $conn->prepare("SELECT SUM(CASE WHEN estado = 'ocupada' THEN 1 ELSE 0 END) as ocupadas_count FROM sillas WHERE id_mesa = :id_mesa");
    $stmt->bindParam(':id_mesa', $id_mesa, PDO::PARAM_INT);
    $stmt->execute();
    $conteo = $stmt->fetch(PDO::FETCH_ASSOC);
    $sillas_ocupadas = (int)$conteo['ocupadas_count'];

    $estadoMesa = ($sillas_ocupadas > 0) ? 'ocupada' : 'libre';

    $stmt = $conn->prepare("UPDATE mesas SET estado = :estado WHERE id_mesa = :id_mesa");
    $stmt->bindParam(':estado', $estadoMesa, PDO::PARAM_STR);
    $stmt->bindParam(':id_mesa', $id_mesa, PDO::PARAM_INT);
    $stmt->execute();

    if ($nuevoEstado === 'ocupada' && $sillas_ocupadas === 1) {
        $stmt = $conn->prepare("INSERT INTO ocupaciones (id_mesa, id_usuario, fecha_ocupacion) 
                                 VALUES (:id_mesa, :id_usuario, NOW())");
        $stmt->bindParam(':id_mesa', $id_mesa, PDO::PARAM_INT);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
    } elseif ($nuevoEstado === 'libre' && $sillas_ocupadas === 0) {
        $stmt = $conn->prepare("UPDATE ocupaciones 
                                 SET fecha_liberacion = NOW() 
                                 WHERE id_mesa = :id_mesa 
                                 AND fecha_liberacion IS NULL
                                 ORDER BY fecha_ocupacion DESC 
                                 LIMIT 1");
        $stmt->bindParam(':id_mesa', $id_mesa, PDO::PARAM_INT);
        $stmt->execute();
    }

    $conn->commit();

    header("Location: ../../public/pages/salas.php?id_sala=$id_sala");
    exit;
} catch (Exception $e) {
    $conn->rollBack();
    die("Error al actualizar la silla: " . $e->getMessage());
}
