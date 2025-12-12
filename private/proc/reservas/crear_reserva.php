<?php
session_start();
require_once __DIR__ . '/../../db/db_conn.php';

if (!isset($_SESSION['id_usuario'])) {
  header('Location: ../../public/pages/login.php');
  exit;
}

$conn = connection($host, $user, $pass, $db);

$id_usuario = (int)$_SESSION['id_usuario'];
$id_mesa = (int)($_POST['id_mesa'] ?? 0);
$fecha = trim($_POST['fecha'] ?? '');
$hora_inicio = trim($_POST['hora_inicio'] ?? '');
$nombre_cliente = trim($_POST['nombre_cliente'] ?? '');
$observaciones = trim($_POST['observaciones'] ?? '');

if ($id_mesa <= 0 || $fecha === '' || $hora_inicio === '') {
  header('Location: ../../public/pages/reservas/reservar.php?status=error');
  exit;
}

try {
  // Calcular hora_fin fija de 1h30min
  $dtStart = DateTime::createFromFormat('H:i', $hora_inicio);
  if (!$dtStart) { header('Location: ../../public/pages/reservas/reservar.php?status=error'); exit; }
  $dtEnd = clone $dtStart; $dtEnd->modify('+90 minutes');
  $hora_fin = $dtEnd->format('H:i');
  // Comprobar conflicto: misma mesa, misma fecha, solape horario
  $stmt = $conn->prepare(
    "SELECT COUNT(*) FROM reservas 
     WHERE id_recurso = :id_mesa
       AND fecha = :fecha
       AND (
         (franja_horaria = :franja) OR
         (
           SUBSTRING_INDEX(franja_horaria,'-',1) < :hora_fin AND
           SUBSTRING_INDEX(franja_horaria,'-',-1) > :hora_inicio
         )
       )"
  );
  $franja = $hora_inicio . '-' . $hora_fin;
  $stmt->execute([
    ':id_mesa' => $id_mesa,
    ':fecha' => $fecha,
    ':franja' => $franja,
    ':hora_inicio' => $hora_inicio,
    ':hora_fin' => $hora_fin,
  ]);
  $exists = (int)$stmt->fetchColumn();
  if ($exists > 0) {
    header('Location: ../../public/pages/reservas/reservar.php?status=conflict');
    exit;
  }

  // Insertar
  $stmtIns = $conn->prepare(
    "INSERT INTO reservas (id_recurso, id_usuario, fecha, franja_horaria, estado, created_at)
     VALUES (:id_mesa, :id_usuario, :fecha, :franja, 'confirmada', NOW())"
  );
  $stmtIns->execute([
    ':id_mesa' => $id_mesa,
    ':id_usuario' => $id_usuario,
    ':fecha' => $fecha,
    ':franja' => $franja,
  ]);

  header('Location: ../../public/pages/reservas/reservar.php?status=ok');
  exit;
} catch (PDOException $e) {
  header('Location: ../../public/pages/reservas/reservar.php?status=error');
  exit;
}