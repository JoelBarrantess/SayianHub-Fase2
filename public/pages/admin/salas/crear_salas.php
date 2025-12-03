<?php
require_once __DIR__ . '/../../../../private/db/db_conn.php';

session_start();

$conn = connection($host, $user, $pass, $db);

$status = isset($_GET['status']) ? (string) $_GET['status'] : '';

try {
    $stmtTipos = $conn->query("SELECT DISTINCT tipo FROM salas WHERE tipo IS NOT NULL AND tipo <> '' ORDER BY tipo");
    $tiposDisponibles = $stmtTipos ? $stmtTipos->fetchAll(PDO::FETCH_COLUMN) : [];
} catch (PDOException $e) {
    error_log('Error al obtener tipos de sala: ' . $e->getMessage());
    $tiposDisponibles = [];
}

if (empty($tiposDisponibles)) {
    $tiposDisponibles = ['terraza', 'comedor', 'privada'];
}

require_once __DIR__ . '/../../../../private/proc/insertar_sala.php';

$errores = $resultado['errores'] ?? [];
$form = $resultado['form'] ?? ['nombre' => '', 'tipo' => '', 'capacidad' => ''];
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Nueva Sala · SaiyanHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="icon" href="data:,">
</head>
<body data-status="<?php echo htmlspecialchars((string)$status, ENT_QUOTES, 'UTF-8'); ?>">
  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h3 mb-0">Crear nueva sala</h1>
      <a href="admin_salas.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver
      </a>
    </div>

    <div class="card shadow-sm">
      <div class="card-body p-4">
        <form id="form-crear-sala" method="post" novalidate>
          <div class="mb-3">
            <label for="nombre" class="form-label">Nombre de la sala</label>
            <input type="text"
                   class="form-control<?php echo isset($errores['nombre']) ? ' is-invalid' : ''; ?>"
                   id="nombre"
                   name="nombre"
                   value="<?php echo htmlspecialchars($form['nombre']); ?>"
                   required>
            <small id="errorNombre" class="text-danger<?php echo isset($errores['nombre']) ? '' : ' d-none'; ?>">
              <?php echo $errores['nombre'] ?? ''; ?>
            </small>
          </div>

          <div class="mb-3">
            <label for="tipo" class="form-label">Tipo de sala</label>
            <select id="tipo"
                    name="tipo"
                    class="form-select<?php echo isset($errores['tipo']) ? ' is-invalid' : ''; ?>"
                    required>
              <option value="" disabled <?php echo $form['tipo'] === '' ? 'selected' : ''; ?>>
                Selecciona un tipo
              </option>
              <?php foreach ($tiposDisponibles as $tipo): ?>
                <option value="<?php echo $tipo; ?>"
                        <?php echo $form['tipo'] === $tipo ? 'selected' : ''; ?>>
                  <?php echo ucfirst($tipo); ?>
                </option>
              <?php endforeach; ?>
            </select>
            <small id="errorTipo" class="text-danger<?php echo isset($errores['tipo']) ? '' : ' d-none'; ?>">
              <?php echo $errores['tipo'] ?? ''; ?>
            </small>
          </div>

          <div class="mb-4">
            <label for="capacidad" class="form-label">Capacidad total</label>
            <input type="number"
                   min="1"
                   class="form-control<?php echo isset($errores['capacidad']) ? ' is-invalid' : ''; ?>"
                   id="capacidad"
                   name="capacidad"
                   value="<?php echo htmlspecialchars($form['capacidad']); ?>"
                   required>
            <small id="errorCapacidad" class="text-danger<?php echo isset($errores['capacidad']) ? '' : ' d-none'; ?>">
              <?php echo $errores['capacidad'] ?? ''; ?>
            </small>
          </div>

          <div class="d-flex justify-content-end gap-2">
            <a href="admin_salas.php" class="btn btn-light">Cancelar</a>
            <button type="submit" class="btn btn-dark">
              <i class="bi bi-check-circle me-1"></i> Guardar sala
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="../../../js/admin_salas/crear_salas.js"></script>
</body>
</html>