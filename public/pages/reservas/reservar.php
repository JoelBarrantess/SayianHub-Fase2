<?php
require_once __DIR__ . '/../../../private/db/db_conn.php';
session_start();

if (!isset($_SESSION['id_usuario'])) {
  header('Location: ../login.php');
  exit;
}

$conn = connection($host, $user, $pass, $db);

// Mesa seleccionada desde la página de salas
$id_mesa = (int)($_GET['id_mesa'] ?? 0);
if ($id_mesa <= 0) {
  header('Location: ../salas.php');
  exit;
}

$stmtM = $conn->prepare("SELECT m.id_mesa, m.nombre_mesa, m.id_sala, s.nombre_sala FROM mesas m JOIN salas s ON s.id_sala=m.id_sala WHERE m.id_mesa=:id");
$stmtM->execute([':id' => $id_mesa]);
$mesa = $stmtM->fetch(PDO::FETCH_ASSOC);
if (!$mesa) {
  header('Location: ../salas.php');
  exit;
}

// Reservas existentes para esta mesa (fecha actual en adelante)
$stmtR = $conn->prepare("SELECT id_reserva, fecha, franja_horaria, estado FROM reservas WHERE id_recurso=:id_mesa AND fecha >= CURDATE() ORDER BY fecha, franja_horaria");
$stmtR->execute([':id_mesa' => $id_mesa]);
$reservas = $stmtR->fetchAll(PDO::FETCH_ASSOC);

$status = isset($_GET['status']) ? (string)$_GET['status'] : '';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Reservar · SaiyanHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4">
    <h1 class="mb-3">Reservar Mesa</h1>
    <p class="text-muted">Mesa: <strong><?= htmlspecialchars($mesa['nombre_mesa']) ?></strong> · Sala: <strong><?= htmlspecialchars($mesa['nombre_sala']) ?></strong></p>

    <?php if ($status === 'ok'): ?>
      <div class="alert alert-success">Reserva creada correctamente.</div>
    <?php elseif ($status === 'conflict'): ?>
      <div class="alert alert-warning">Conflicto: ya existe una reserva en esa franja.</div>
    <?php elseif ($status === 'error'): ?>
      <div class="alert alert-danger">Error al crear la reserva.</div>
    <?php endif; ?>

    <form class="card p-3 shadow-sm mb-4" method="post" action="../../../private/proc/reservas/crear_reserva.php">
      <input type="hidden" name="id_mesa" value="<?= (int)$mesa['id_mesa'] ?>">
      <div class="row g-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label">Fecha</label>
          <input type="date" name="fecha" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Hora inicio</label>
          <input type="time" name="hora_inicio" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Duración</label>
          <input type="text" class="form-control" value="1h 30min" disabled>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Nombre cliente (opcional)</label>
        <input type="text" name="nombre_cliente" class="form-control" maxlength="120">
      </div>
      <div class="mb-3">
        <label class="form-label">Observaciones (opcional)</label>
        <input type="text" name="observaciones" class="form-control" maxlength="255">
      </div>

      <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary">Reservar</button>
        <a href="../salas.php" class="btn btn-outline-secondary">Volver</a>
      </div>
    </form>

    <div class="card p-3 shadow-sm">
      <h5 class="mb-3">Reservas futuras para esta mesa</h5>
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Franja</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($reservas)): ?>
              <tr><td colspan="3" class="text-muted">Sin reservas</td></tr>
            <?php else: foreach ($reservas as $r): ?>
              <tr>
                <td><?= htmlspecialchars($r['fecha']) ?></td>
                <td><?= htmlspecialchars($r['franja_horaria']) ?></td>
                <td><?= htmlspecialchars($r['estado']) ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</body>
</html>