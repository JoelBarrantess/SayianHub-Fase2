<?php


session_start();

if (!isset($_SESSION['id_usuario'])) {
  header('Location: ../../public/pages/login.html');
  exit;
}

require_once __DIR__ . '../../../private/db/db_conn.php';

try {
    $conn = connection($host, $user, $pass, $db);

    $sql = "
        SELECT s.id_sala, s.nombre_sala, COALESCE(s.tipo, '') AS tipo, COALESCE(s.capacidad_total, 0) AS capacidad_total,
               m.id_mesa, m.nombre_mesa, m.num_sillas, m.estado
        FROM salas s
        LEFT JOIN mesas m ON m.id_sala = s.id_sala
        ORDER BY s.nombre_sala, m.nombre_mesa
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('DB error en quick_rooms.php: ' . $e->getMessage());
    $rows = [];
}

$salas = [];

foreach ($rows as $row) {
    $id = (int)($row['id_sala'] ?? 0);
    if ($id === 0) {
        continue;
    }
    if (!isset($salas[$id])) {
        $salas[$id] = [
            'id'        => $id,
            'nombre'    => $row['nombre_sala'] ?? 'Sala',
            'tipo'      => $row['tipo'] ?? '',
            'capacidad' => (int)($row['capacidad_total'] ?? 0),
            'mesas'     => []
        ];
    }
    if (!empty($row['id_mesa'])) {
        $salas[$id]['mesas'][] = [
            'id'        => (int)$row['id_mesa'],
            'nombre'    => $row['nombre_mesa'],
            'capacidad' => (int)($row['num_sillas'] ?? 0),
            'estado'    => $row['estado'] ?? ''
        ];
    }
}

$iconMap = [
    'terraza' => 'bi bi-sun',
    'comedor' => 'bi bi-egg-fried',
    'privada' => 'bi bi-lock-fill',
];

foreach ($salas as &$sala) {
    $tipoRaw = trim((string)($sala['tipo'] ?? ''));
    $tipoKey = strtolower($tipoRaw);
    $sala['icon_class'] = $iconMap[$tipoKey] ?? 'bi bi-building-fill';
}
unset($sala);

// Helpers
function esc(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Salas · SaiyanHub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="" crossorigin="anonymous">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/room_selection.css">
</head>
<body class="salas-body">
  <main class="salas-wrapper" aria-labelledby="main-title">
    <header class="salas-header d-flex align-items-start justify-content-between flex-wrap gap-3">
      <div>
        <h1 id="main-title">Salas disponibles</h1>
        <p class="lead">Selecciona una sala para ver sus mesas y capacidad.</p>
      </div>
      <a href="../pages/dashboard.php" class="btn btn-volver" aria-label="Volver al dashboard">
        <i class="bi bi-arrow-left" aria-hidden="true"></i> Dashboard
      </a>
    </header>

    <section class="row g-4" id="salas-grid" role="list">
      <?php foreach ($salas as $sala): 
        $salaId = (int)$sala['id'];
        $numMesas = count($sala['mesas']);
        $dataMesasJson = esc(json_encode($sala['mesas'], JSON_UNESCAPED_UNICODE));
      ?>
        <div class="col-sm-6 col-lg-4 col-xl-3" role="listitem">
          <a href="../pages/salas.php?id_sala=<?= urlencode((string)$salaId); ?>"
             class="card sala-card-btn w-100 text-start text-decoration-none"
             aria-labelledby="sala-title-<?= $salaId; ?>"
             data-sala-id="<?= $salaId; ?>"
             data-num-mesas="<?= $numMesas; ?>"
             data-capacidad="<?= (int)$sala['capacidad']; ?>"
             data-mesas='<?= $dataMesasJson; ?>'>
            <div class="card-body d-flex flex-column gap-3">
              <div class="d-flex align-items-center gap-3">
                <div class="icon-chip" aria-hidden="true">
                  <i class="<?= esc($sala['icon_class']); ?>"></i>
                </div>
                <div>
                  <h5 id="sala-title-<?= $salaId; ?>" class="card-title mb-0"><?= esc($sala['nombre']); ?></h5>
                  <small class="text-muted text-uppercase"><?= esc(ucfirst($sala['tipo'] ?? '')); ?></small>
                </div>
              </div>

              <div class="d-flex align-items-end justify-content-between">
                <div>
                  <span class="display-6 fw-bold d-block mb-1"><?= (int)$sala['capacidad']; ?></span>
                  <span class="text-muted small">Capacidad total</span>
                </div>
                <div class="text-end">
                  <span class="d-block small text-muted">Mesas</span>
                  <span class="fw-semibold"><?= $numMesas; ?></span>
                </div>
              </div>

              <div class="mt-1">
                <span class="small text-muted">Haz clic para ver detalles</span>
              </div>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
      <?php if (empty($salas)): ?>
        <div class="col-12">
          <div class="alert alert-info mb-0" role="status">
            No hay salas registradas. Contacta con el administrador si corresponde.
          </div>
        </div>
      <?php endif; ?>
    </section>

    <p class="text-muted text-center mt-4">
      Selecciona una sala para ver sus mesas y sillas.
    </p>
  </main>
</body>
</html>