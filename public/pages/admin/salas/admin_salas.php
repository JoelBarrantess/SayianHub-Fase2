<?php
require_once __DIR__ . '/../../../../private/db/db_conn.php';
$conn = connection($host, $user, $pass, $db);

session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'admin') {
  header('Location: ../../login.html');
  exit;
}

$stmt = $conn->prepare(
  "SELECT s.id_sala, s.nombre_sala, s.tipo, s.capacidad_total,
          m.id_mesa, m.nombre_mesa, m.num_sillas, m.estado
   FROM salas s
   LEFT JOIN mesas m ON m.id_sala = s.id_sala
   ORDER BY s.nombre_sala, m.nombre_mesa"
);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$salas = [];
foreach ($rows as $row) {
  $id = (int)$row['id_sala'];
  if (!isset($salas[$id])) {
    $salas[$id] = [
      'id'        => $id,
      'nombre'    => $row['nombre_sala'],
      'tipo'      => $row['tipo'],
      'capacidad' => $row['capacidad_total'],
      'mesas'     => []
    ];
  }
  if (!empty($row['id_mesa'])) {
    $salas[$id]['mesas'][] = [
      'id'        => (int)$row['id_mesa'],
      'numero'    => $row['nombre_mesa'],
      'capacidad' => $row['num_sillas'],
      'estado'    => $row['estado']
    ];
  }
}

$iconMap = [
  'terraza' => 'bi bi-sun',
  'comedor' => 'bi bi-egg-fried',
  'privada' => 'bi bi-lock-fill',
];

foreach ($salas as &$sala) {
  $tipoRaw = trim($sala['tipo'] ?? '');
  $tipoKey = strtolower($tipoRaw);
  $sala['tipo'] = $tipoRaw;
  $sala['icon_class'] = $iconMap[$tipoKey] ?? 'bi bi-building-fill';
}
unset($sala);
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8" />
  <title>Salas · SaiyanHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../../../css/admin-salas.css">
</head>

<body class="salas-body">


  <header class="admin-header">
    <div class="brand">
      <h1>Panel de administración</h1>
    </div>



    <nav class="admin-nav" aria-label="Navegación de administración">
      <a class="tab tab-link" href="../recursos/crud_recursos.php" aria-current="page">Gestión de recursos</a>
      <a class="tab tab-link" href="../users/admin.php">Gestión de usuarios</a>
      <a class="tab tab-link active" href="./admin_salas.php" aria-current="page">Salas y Mesas</a>
      <a class="tab tab-link" href="../../logs.php">Historial</a>
      <a href="../../dashboard.php" class="btn btn-volver">
        Volver al Dashboard
      </a>

    </nav>
  </header>

  <main class="salas-wrapper" id="salas-wrapper">

    <div class="d-flex justify-content-end align-items-center mb-3">
      <a href="./crear_salas.php" class="btn btn-volver">
        <i class="bi bi-plus-lg me-2"></i>Crear sala
      </a>
    </div>

    <section class="row g-4" id="salas-grid">
      <?php foreach ($salas as $sala): ?>
        <div class="col-sm-6 col-lg-4 col-xl-3 sala-col" data-sala-id="<?php echo $sala['id']; ?>">
          <button type="button"
            class="card shadow-sm border-0 sala-card-btn w-100 text-start"
            data-sala-id="<?php echo $sala['id']; ?>"
            data-icon-class="<?php echo htmlspecialchars($sala['icon_class']); ?>">
            <div class="card-body d-flex flex-column gap-2">
              <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary-subtle text-primary fs-4 px-3 py-2">
                  <i class="<?php echo htmlspecialchars($sala['icon_class']); ?>"></i>
                </div>
                <div>
                  <h5 class="card-title mb-0 fw-semibold"><?php echo htmlspecialchars($sala['nombre']); ?></h5>
                  <small class="text-muted text-uppercase fw-semibold"><?php echo ucfirst(htmlspecialchars($sala['tipo'])); ?></small>
                </div>
              </div>
              <div>
                <span class="display-6 fw-bold d-block mb-1"><?php echo (int)$sala['capacidad']; ?></span>
                <span class="text-muted small">Capacidad total · Mesas: <?php echo count($sala['mesas']); ?></span>
              </div>
            </div>
          </button>
        </div>
      <?php endforeach; ?>
    </section>

    <section class="card shadow-sm border-0 mt-4 sala-detail-card" id="sala-detail" hidden>
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
          <div>
            <h4 class="mb-1" id="detail-title"></h4>
            <div class="text-muted small" id="detail-meta"></div>
          </div>

          <div class="d-flex align-items-center gap-2">
            <form id="detail-delete-form"
              action="../../../../private/proc/eliminar_sala.php"
              method="GET"
              style="display:inline;">
              <input type="hidden" name="id" id="detail-delete-id" value="">
              <button type="submit" class="btn btn-sm btn-outline-danger" id="detail-delete-btn">Eliminar sala</button>
            </form>

            <a id="detail-edit" href="#" class="btn btn-outline-primary btn-sm" hidden>
              <i class="bi bi-plus-lg me-1"></i>Añadir mesa
            </a>

            <button type="button" class="btn btn-outline-secondary btn-sm" id="detail-back">Ver todas las salas</button>
          </div>
        </div>

        <div id="selected-card" class="selected-card mb-4"></div>

        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" id="detail-table">
            <thead class="table-light">
              <tr>
                <th>Mesa</th>
                <th>Sillas</th>
                <th>Estado</th>
                <th class="text-end">Acciones</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
        <div class="alert alert-info mt-3 mb-0" id="detail-empty" hidden>Sin mesas registradas.</div>
      </div>
    </section>

    <p class="text-muted text-center mt-4" id="detail-placeholder">
      Selecciona una sala para ver sus mesas y sillas.
    </p>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
      window.SALAS_DATA = <?php echo json_encode(array_values($salas), JSON_UNESCAPED_UNICODE); ?>;
    </script>
    <script src="../../../js/admin_salas/admin_salas.js"></script>
  </main>

</body>

</html>