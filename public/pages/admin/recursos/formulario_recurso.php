<?php
require_once __DIR__ . '/../../../../private/db/db_conn.php';
session_start();

if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['rol'], ['admin', 'gerente', 'mantenimiento'])) {
    header('Location: ../../login.html');
    exit;
}

$conn = connection($host, $user, $pass, $db);
$id = $_GET['id'] ?? null;
$recurso = [
    'nombre' => '',
    'tipo' => 'sala',
    'capacidad' => 0,
    'estado' => 'disponible',
    'id_upload' => null,
    'upload_path' => ''
];
$titulo = "Nuevo Recurso";

if ($id) {
    $titulo = "Editar Recurso";
    $stmt = $conn->prepare("SELECT r.*, u.path AS upload_path FROM recursos r LEFT JOIN uploads u ON r.id_upload = u.id_upload WHERE r.id_recurso = :id");
    $stmt->execute([':id' => $id]);
    $recurso = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$recurso) {
        die("Recurso no encontrado");
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $titulo ?> | SaiyanHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../../css/admin-recursos.css" rel="stylesheet">
</head>
<body>

<header class="admin-header">
    <div class="brand">
        <h1>Panel de administración</h1>
    </div>

    <nav class="admin-nav" aria-label="Navegación de administración">
        <a class="tab tab-link" href="../users/admin.php">Gestión de usuarios</a>
        <a class="tab tab-link" href="../salas/admin_salas.php">Salas y Mesas</a>
        <a class="tab tab-link active" href="./crud_recursos.php">Recursos</a>
        <a class="tab tab-link" href="../../logs.php">Historial</a>
        <a href="../../dashboard.php" class="btn btn-volver">
            Volver al Dashboard
        </a>
    </nav>
</header>

<div class="recursos-wrapper">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow border-0 form-card">
                <div class="form-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><?= $titulo ?></h4>
                    <a href="crud_recursos.php" class="btn btn-sm btn-outline-light">Volver</a>
                </div>
                <div class="card-body p-4">
                    <form id="form-recurso" action="../../../../private/proc/guardar_recurso.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id_recurso" value="<?= $id ?>">
                        <input type="hidden" name="current_id_upload" value="<?= htmlspecialchars($recurso['id_upload'] ?? '') ?>">
                        
                           <input type="hidden" name="MAX_FILE_SIZE" value="2097152">
                        <!-- Nombre -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre del Recurso</label>
                            <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($recurso['nombre']) ?>">
                        </div>

                        <!-- Tipo y Capacidad -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tipo</label>
                                <select name="tipo" class="form-select" required>
                                    <option value="sala" <?= $recurso['tipo'] == 'sala' ? 'selected' : '' ?>>Sala</option>
                                    <option value="mesa" <?= $recurso['tipo'] == 'mesa' ? 'selected' : '' ?>>Mesa</option>
                                    <option value="silla" <?= $recurso['tipo'] == 'silla' ? 'selected' : '' ?>>Silla</option>
                                    <option value="otro" <?= $recurso['tipo'] == 'otro' ? 'selected' : '' ?>>Otro</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Capacidad (Personas)</label>
                                <input type="number" name="capacidad" class="form-control" min="0" value="<?= $recurso['capacidad'] ?>">
                            </div>
                        </div>

                        <!-- Estado -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Estado</label>
                            <select name="estado" class="form-select">
                                <option value="disponible" <?= $recurso['estado'] == 'disponible' ? 'selected' : '' ?>>Disponible</option>
                                <option value="mantenimiento" <?= $recurso['estado'] == 'mantenimiento' ? 'selected' : '' ?>>Mantenimiento</option>
                                <option value="baja" <?= $recurso['estado'] == 'baja' ? 'selected' : '' ?>>Baja</option>
                            </select>
                        </div>

                        <!-- Imagen -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Imagen del Recurso</label>
                               <input type="file" class="form-control" id="imagen" name="imagen" accept=".jpg,.jpeg,.png,.gif,.webp,.svg,.txt,.zip,.xls,.doc,.pdf">
                            <?php if (!empty($recurso['upload_path'])): ?>
                                <div class="mt-2">
                                    <small class="text-muted">Imagen actual:</small><br>
                                    <img src="../../../<?= htmlspecialchars($recurso['upload_path']) ?>" alt="Actual" style="height: 80px; border-radius: 4px; margin-top: 5px;">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" value="1" id="removeImage" name="remove_image">
                                        <label class="form-check-label" for="removeImage">
                                            Quitar imagen actual
                                        </label>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid gap-2">
                                                        <button type="submit" class="btn btn-primary btn-lg">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Validación simple con SweetAlert
(function(){
    const form = document.getElementById('form-recurso');
    form.addEventListener('submit', function(e){
        const nombre = form.nombre.value.trim();
        const tipo = form.tipo.value;
        const capacidad = parseInt(form.capacidad.value || '0', 10);
        const archivo = form.imagen.files[0];
        const quitar = form.remove_image && form.remove_image.checked;

        if (!nombre) {
            e.preventDefault();
            Swal.fire({icon:'warning', title:'Nombre requerido', text:'Indica el nombre del recurso.'});
            return;
        }
        if (!['sala','mesa','silla','otro'].includes(tipo)) {
            e.preventDefault();
            Swal.fire({icon:'warning', title:'Tipo inválido', text:'Selecciona un tipo válido.'});
            return;
        }
        if (isNaN(capacidad) || capacidad < 0) {
            e.preventDefault();
            Swal.fire({icon:'warning', title:'Capacidad inválida', text:'La capacidad debe ser 0 o mayor.'});
            return;
        }
        if (archivo) {
            const maxMB = 5; // límite 5MB
            const validTypes = ['image/jpeg','image/png','image/webp'];
            if (!validTypes.includes(archivo.type)) {
                e.preventDefault();
                Swal.fire({icon:'error', title:'Formato no soportado', text:'Sube JPEG, PNG o WEBP.'});
                return;
            }
            if (archivo.size > maxMB * 1024 * 1024) {
                e.preventDefault();
                Swal.fire({icon:'error', title:'Archivo demasiado grande', text:`Máximo ${maxMB} MB.`});
                return;
            }
        }
        // Si se selecciona quitar imagen y no se sube nueva, seguimos.
    });
})();
</script>
</body>
</html>
