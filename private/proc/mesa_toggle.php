<?php
require_once '../../private/db/db_conn.php';
session_start();

if (!isset($_GET['id_mesa'], $_GET['estado'], $_GET['id_sala'])) {
  die("Datos insuficientes.");
}

$id_mesa = (int) $_GET['id_mesa'];
$estado = $_GET['estado'] === 'ocupada' ? 'ocupada' : 'libre';
$id_sala = (int) $_GET['id_sala'];

if (!isset($_SESSION['id_usuario'])) {
  die("Debe iniciar sesión para realizar esta acción.");
}

$id_usuario = $_SESSION['id_usuario'];

try {
  $conn = connection($host, $user, $pass, $db);
  $conn->beginTransaction();

  $stmt = $conn->prepare("UPDATE mesas SET estado = :estado WHERE id_mesa = :id_mesa");
  $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
  $stmt->bindParam(':id_mesa', $id_mesa, PDO::PARAM_INT);
  $stmt->execute();

  $stmt = $conn->prepare("UPDATE sillas SET estado = :estado WHERE id_mesa = :id_mesa");
  $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
  $stmt->bindParam(':id_mesa', $id_mesa, PDO::PARAM_INT);
  $stmt->execute();

  if ($estado === 'ocupada') {
    $stmt = $conn->prepare("INSERT INTO ocupaciones (id_mesa, id_usuario, fecha_ocupacion) 
                VALUES (:id_mesa, :id_usuario, NOW())");
    $stmt->bindParam(':id_mesa', $id_mesa, PDO::PARAM_INT);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
  } else {
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
} catch (PDOException $e) {
  $conn->rollBack();
  die("Error al actualizar la mesa: " . $e->getMessage());
}
