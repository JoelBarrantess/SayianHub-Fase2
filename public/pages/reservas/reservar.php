<?php
require_once __DIR__ . '/../../../private/db/db_conn.php';
session_start();

if (!isset($_SESSION['id_usuario'])) {
  header('Location: ../login.php');
  exit;
}

$conn = connection($host, $user, $pass, $db);

// Obtener salas y mesas activas para selector
$salas = $conn->query("SELECT id_sala, nombre_sala FROM salas ORDER BY nombre_sala")->fetchAll(PDO::FETCH_ASSOC);
$mesas = $conn->query("SELECT id_mesa, id_sala, nombre_mesa, num_sillas FROM mesas ORDER BY id_sala, nombre_mesa")->fetchAll(PDO::FETCH_ASSOC);

// Mapa de mesas por sala
$mesasPorSala = [];
foreach ($mesas as $m) {
  $sid = (int)$m['id_sala'];
  if (!isset($mesasPorSala[$sid])) $mesasPorSala[$sid] = [];
  $mesasPorSala[$sid][] = $m;
}

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
    <h1 class="mb-3">Nueva Reserva</h1>
    <p class="text-muted">Selecciona sala, mesa, fecha y franja horaria.</p>

    <?php if ($status === 'ok'): ?>
      <div class="alert alert-success">Reserva creada correctamente.</div>
    <?php elseif ($status === 'conflict'): ?>
      <div class="alert alert-warning">Conflicto: ya existe una reserva en esa franja.</div>
    <?php elseif ($status === 'error'): ?>
      <div class="alert alert-danger">Error al crear la reserva.</div>
    <?php endif; ?>

    <form class="card p-3 shadow-sm" method="post" action="../../../private/proc/reservas/crear_reserva.php">
      <div class="mb-3">
        <label class="form-label">Sala</label>
        <select id="sala" name="id_sala" class="form-select" required>
          <option value="">Selecciona sala</option>
          <?php foreach ($salas as $s): ?>
            <option value="<?= (int)$s['id_sala'] ?>"><?= htmlspecialchars($s['nombre_sala']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Mesa</label>
        <select id="mesa" name="id_mesa" class="form-select" required>
          <option value="">Selecciona mesa</option>
        </select>
      </div>

      <div class="row mb-3">
        <div class="col-md-4">
          <label class="form-label">Fecha</label>
          <input type="date" name="fecha" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Hora inicio</label>
          <input type="time" name="hora_inicio" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Hora fin</label>
          <input type="time" name="hora_fin" class="form-control" required>
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

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Reservar</button>
        <a href="../dashboard.php" class="btn btn-outline-secondary">Volver</a>
      </div>
    </form>
  </div>

  <script>
    // Pre-cargar mesas por sala en el cliente
    const MESAS = <?= json_encode($mesasPorSala) ?>;
    const salaSel = document.getElementById('sala');
    const mesaSel = document.getElementById('mesa');
    salaSel.addEventListener('change', function() {
      const sid = parseInt(this.value, 10);
      mesaSel.innerHTML = '<option value="">Selecciona mesa</option>';
      if (!isNaN(sid) && MESAS[sid]) {
        MESAS[sid].forEach(m => {
          const opt = document.createElement('option');
          opt.value = m.id_mesa;
          opt.textContent = m.nombre_mesa + ' (' + m.num_sillas + 'p)';
          mesaSel.appendChild(opt);
        });
      }
    });
  </script>
</body>
</html>