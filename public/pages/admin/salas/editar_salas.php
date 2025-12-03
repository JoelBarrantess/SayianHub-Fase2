<?php
require_once __DIR__ . '/../../../../private/db/db_conn.php';

// if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'admin') {
//     header('Location: ../../login.html');
//     exit;
// }

$conn = connection($host, $user, $pass, $db);

$salaId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$salaId) {
    header('Location: admin_salas.php?status=invalid');
    exit;
}

$stmtSala = $conn->prepare(
    "SELECT id_sala, nombre_sala, tipo, capacidad_total
     FROM salas
     WHERE id_sala = :id"
);
$stmtSala->execute([':id' => $salaId]);
$sala = $stmtSala->fetch(PDO::FETCH_ASSOC);

if (!$sala) {
    header('Location: admin_salas.php?status=notfound');
    exit;
}

$stmtMesas = $conn->prepare(
    "SELECT m.id_mesa, m.nombre_mesa, m.num_sillas, m.estado
     FROM mesas m
     WHERE m.id_sala = :id
     ORDER BY m.nombre_mesa"
);
$stmtMesas->execute([':id' => $salaId]);
$mesas = $stmtMesas->fetchAll(PDO::FETCH_ASSOC);

$stmtEstados = $conn->query(
    "SELECT DISTINCT estado FROM mesas WHERE estado IS NOT NULL AND estado <> '' ORDER BY estado"
);
$estadosPermitidos = $stmtEstados->fetchAll(PDO::FETCH_COLUMN);
if (!$estadosPermitidos) {
    $estadosPermitidos = ['libre', 'ocupada', 'reservada'];
}

require_once __DIR__ . '/../../../../private/proc/update_salas.php';

$errores = $resultado['errores'] ?? [];
$formSala = $resultado['formSala'] ?? ['nombre' => $sala['nombre_sala']];
$formMesasPost = $resultado['formMesas'] ?? [];

$mesasParaMostrar = [];
foreach ($mesas as $mesa) {
    $key = (string)$mesa['id_mesa'];
    $mesasParaMostrar[$key] = [
        'id'      => (int)$mesa['id_mesa'],
        'nombre'  => $formMesasPost[$key]['nombre'] ?? $mesa['nombre_mesa'],
        'sillas'  => $formMesasPost[$key]['num_sillas'] ?? (int)$mesa['num_sillas'],
        'estado'  => $formMesasPost[$key]['estado'] ?? $mesa['estado'],
        'esNueva' => false
    ];
}

foreach ($formMesasPost as $key => $mesaPost) {
    if (($mesaPost['id_mesa'] ?? 0) === 0 && !isset($mesasParaMostrar[$key])) {
        $mesasParaMostrar[$key] = [
            'id'      => 0,
            'nombre'  => $mesaPost['nombre'] ?? '',
            'sillas'  => $mesaPost['num_sillas'] ?? '',
            'estado'  => $mesaPost['estado'] ?? '',
            'esNueva' => true
        ];
    }
}

$proximoIndice = count($mesasParaMostrar);

$status = isset($_GET['status']) ? $_GET['status'] : '';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Editar sala · SaiyanHub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="icon" href="data:,">
</head>
<body class="bg-light" data-status="<?php echo htmlspecialchars($status); ?>">
  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
      <div>
        <h1 class="h3 mb-1">Editar sala</h1>
        <p class="text-muted mb-0">
          Sala ID <?php echo htmlspecialchars((string)$salaId); ?> · <?php echo htmlspecialchars($sala['tipo']); ?>
        </p>
      </div>
      <div class="d-flex gap-2">
        <a href="admin_salas.php" class="btn btn-outline-dark btn-sm">Volver</a>
      </div>
    </div>

    <div class="card shadow-sm">
      <div class="card-body p-4">
        <form method="post" id="form-editar-sala" novalidate>
          <div class="mb-4">
            <label for="nombre" class="form-label">Nombre de la sala</label>
            <input
              type="text"
              id="nombre"
              name="nombre"
              class="form-control<?php echo isset($errores['nombre']) ? ' is-invalid' : ''; ?>"
              value="<?php echo htmlspecialchars($formSala['nombre']); ?>"
              required
            >
            <?php if (isset($errores['nombre'])): ?>
              <div class="invalid-feedback d-block"><?php echo $errores['nombre']; ?></div>
            <?php endif; ?>
          </div>

          <h2 class="h5 mb-3">Mesas de la sala</h2>

          <div class="alert alert-info<?php echo $mesasParaMostrar ? ' d-none' : ''; ?>" id="sin-mesas">
            Esta sala no tiene mesas. Pulsa “Añadir mesa” para crear la primera.
          </div>

          <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <p class="text-muted mb-0">Gestiona nombres, estados y sillas de cada mesa.</p>
            <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-mesa">
              <i class="bi bi-plus-circle me-1"></i>Añadir mesa
            </button>
          </div>

          <div id="form-nueva-mesa" class="border rounded-3 p-3 mb-3 d-none">
            <h3 class="h6 mb-3">Nueva mesa</h3>
            <div class="row g-3">
              <div class="col-12 col-md-4">
                <label class="form-label" for="nuevo-nombre">Nombre</label>
                <input type="text" class="form-control" id="nuevo-nombre">
              </div>
              <div class="col-12 col-md-4">
                <label class="form-label" for="nuevas-sillas">Sillas</label>
                <input type="number" min="1" class="form-control" id="nuevas-sillas">
              </div>
              <div class="col-12 col-md-4">
                <label class="form-label" for="nuevo-estado">Estado</label>
                <select class="form-select" id="nuevo-estado">
                  <option value="" selected disabled>Selecciona un estado</option>
                  <?php foreach ($estadosPermitidos as $estado): ?>
                    <option value="<?php echo $estado; ?>"><?php echo ucfirst($estado); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-3">
              <button type="button" class="btn btn-light btn-sm" id="cancelar-nueva-mesa">Cancelar</button>
              <button type="button" class="btn btn-dark btn-sm" id="guardar-nueva-mesa">Guardar mesa</button>
            </div>
          </div>

          <div id="mesas-container" class="row g-3">
            <?php foreach ($mesasParaMostrar as $key => $mesaData): ?>
              <?php
                $mesaId = (int)$mesaData['id'];
                $esNueva = $mesaData['esNueva'];
                $errorNombre = $errores["nombre_$key"] ?? null;
                $errorSillas = $errores["mesa_$key"] ?? null;
                $errorEstado = $errores["estado_$key"] ?? null;
              ?>
              <div class="col-12 col-md-6 mesa-item" data-index="<?php echo htmlspecialchars((string)$key); ?>">
                <div class="border rounded-3 p-3 h-100">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge text-bg-light">
                      <?php echo $esNueva ? 'Mesa nueva' : 'Mesa ID ' . htmlspecialchars((string)$mesaId); ?>
                    </span>
                    <button type="button"
                            class="btn btn-link btn-sm text-danger p-0 <?php echo $esNueva ? '' : 'd-none'; ?>"
                            data-remove-mesa>
                      <i class="bi bi-x-circle me-1"></i>Quitar
                    </button>
                  </div>

                  <input type="hidden"
                         name="mesas[<?php echo htmlspecialchars((string)$key); ?>][id]"
                         value="<?php echo $mesaId > 0 ? $mesaId : ''; ?>">

                  <div class="mb-3">
                    <label class="form-label" for="nombre_mesa_<?php echo htmlspecialchars((string)$key); ?>">
                      Nombre de la mesa
                    </label>
                    <input type="text"
                           class="form-control<?php echo $errorNombre ? ' is-invalid' : ''; ?>"
                           id="nombre_mesa_<?php echo htmlspecialchars((string)$key); ?>"
                           name="mesas[<?php echo htmlspecialchars((string)$key); ?>][nombre]"
                           value="<?php echo htmlspecialchars((string)$mesaData['nombre']); ?>"
                           required>
                    <?php if ($errorNombre): ?>
                      <div class="invalid-feedback d-block"><?php echo $errorNombre; ?></div>
                    <?php endif; ?>
                  </div>

                  <div class="mb-3">
                    <label class="form-label" for="sillas_<?php echo htmlspecialchars((string)$key); ?>">
                      Número de sillas
                    </label>
                    <input type="number"
                           min="1"
                           class="form-control<?php echo $errorSillas ? ' is-invalid' : ''; ?>"
                           id="sillas_<?php echo htmlspecialchars((string)$key); ?>"
                           name="mesas[<?php echo htmlspecialchars((string)$key); ?>][sillas]"
                           value="<?php echo htmlspecialchars((string)$mesaData['sillas']); ?>"
                           required>
                    <?php if ($errorSillas): ?>
                      <div class="invalid-feedback d-block"><?php echo $errorSillas; ?></div>
                    <?php endif; ?>
                  </div>

                  <div>
                    <label class="form-label" for="estado_<?php echo htmlspecialchars((string)$key); ?>">
                      Estado
                    </label>
                    <select class="form-select<?php echo $errorEstado ? ' is-invalid' : ''; ?>"
                            id="estado_<?php echo htmlspecialchars((string)$key); ?>"
                            name="mesas[<?php echo htmlspecialchars((string)$key); ?>][estado]"
                            required>
                      <option value="" disabled <?php echo $mesaData['estado'] === '' ? 'selected' : ''; ?>>
                        Selecciona un estado
                      </option>
                      <?php foreach ($estadosPermitidos as $estado): ?>
                        <option value="<?php echo $estado; ?>" <?php echo $mesaData['estado'] === $estado ? 'selected' : ''; ?>>
                          <?php echo ucfirst($estado); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <?php if ($errorEstado): ?>
                      <div class="invalid-feedback d-block"><?php echo $errorEstado; ?></div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="admin_salas.php" class="btn btn-light">Cancelar</a>
            <button type="submit" class="btn btn-dark">
              <i class="bi bi-save me-1"></i> Guardar cambios
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    window.EDITAR_SALA_DATA = {
      estados: <?php echo json_encode(array_values($estadosPermitidos), JSON_UNESCAPED_UNICODE); ?>,
      nextIndex: <?php echo (int)$proximoIndice; ?>
    };
  </script>
  <script src="../../../js/admin_salas/editar_salas.js"></script>
  <script>
    (function () {
      var status = document.body.dataset.status || '';
      if (!status) return;
      if (status === 'updated') {
        Swal.fire({ icon: 'success', title: 'Sala actualizada', confirmButtonColor: '#111827' })
            .then(function () {
              var url = new URL(window.location.href);
              url.searchParams.delete('status');
              window.history.replaceState({}, '', url);
            });
      }
    })();
  </script>
</body>
</html>