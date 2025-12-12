<?php
require_once __DIR__ . '/../../../private/db/db_conn.php';
session_start();

if (!isset($_SESSION['id_usuario'])) {
  header('Location: ../login.php');
  exit;
}

$conn = connection($host, $user, $pass, $db);

$stmt = $conn->prepare("SELECT r.id_reserva, r.fecha, r.franja_horaria, u.usuario AS camarero, m.nombre_mesa
                        FROM reservas r
                        JOIN usuarios u ON r.id_usuario = u.id_usuario
                        JOIN recursos rec ON rec.id_recurso = r.id_recurso
                        LEFT JOIN mesas m ON m.id_mesa = r.id_recurso
                        ORDER BY r.fecha DESC, r.franja_horaria DESC");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Reservas · SaiyanHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4">
    <h1 class="mb-3">Listado de Reservas</h1>
    <a href="reservar.php" class="btn btn-primary mb-3">Nueva reserva</a>
    <div class="card shadow-sm">
      <div class="table-responsive">
        <table class="table table-striped mb-0">
          <thead>
            <tr>
              <th>#</th>
              <th>Fecha</th>
              <th>Franja</th>
              <th>Mesa</th>
              <th>Camarero</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $i => $r): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td><?= htmlspecialchars($r['fecha']) ?></td>
              <td><?= htmlspecialchars($r['franja_horaria']) ?></td>
              <td><?= htmlspecialchars($r['nombre_mesa'] ?? '—') ?></td>
              <td><?= htmlspecialchars($r['camarero'] ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>