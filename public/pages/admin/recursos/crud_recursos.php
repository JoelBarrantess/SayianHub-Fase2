<?php
require_once __DIR__ . '/../../../../private/db/db_conn.php';
session_start();

// Verificación de rol (Admin, Gerente, Mantenimiento)
if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['rol'], ['admin', 'gerente', 'mantenimiento'])) {
    header('Location: ../../login.html');
    exit;
}

$conn = connection($host, $user, $pass, $db);

// Filtro por tipo
$filtroTipo = $_GET['tipo'] ?? '';
$sql = "SELECT r.*, u.path AS upload_path, u.filename AS upload_filename FROM recursos r LEFT JOIN uploads u ON r.id_upload = u.id_upload";
$params = [];

if ($filtroTipo) {
    $sql .= " WHERE tipo = :tipo";
    $params[':tipo'] = $filtroTipo;
}
$sql .= " ORDER BY tipo, nombre";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$recursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Recursos | SaiyanHub</title>
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
        <a class="tab tab-link active" href="./crud_recursos.php" aria-current="page">Recursos</a>
        <a class="tab tab-link" href="../../logs.php">Historial</a>
        <a href="../../dashboard.php" class="btn btn-volver">
            Volver al Dashboard
        </a>
    </nav>
</header>

<div class="recursos-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0"><i class="bi bi-box-seam"></i> Gestión de Recursos</h2>
        <a href="formulario_recurso.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nuevo Recurso</a>
    </div>

    <!-- Filtros -->
    <div class="card mb-4 border-0 shadow-sm filter-card">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-center">
                <div class="col-auto">
                    <label class="fw-bold">Filtrar por:</label>
                </div>
                <div class="col-auto">
                    <select name="tipo" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos los tipos</option>
                        <option value="sala" <?= $filtroTipo === 'sala' ? 'selected' : '' ?>>Salas</option>
                        <option value="mesa" <?= $filtroTipo === 'mesa' ? 'selected' : '' ?>>Mesas</option>
                        <option value="silla" <?= $filtroTipo === 'silla' ? 'selected' : '' ?>>Sillas</option>
                        <option value="otro" <?= $filtroTipo === 'otro' ? 'selected' : '' ?>>Otros</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Grid de Recursos -->
    <div class="row g-4">
        <?php if (empty($recursos)): ?>
            <div class="col-12 text-center py-5">
                <h4 class="text-muted">No se encontraron recursos.</h4>
            </div>
        <?php endif; ?>

        <?php foreach ($recursos as $recurso): ?>
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card card-resource h-100 border-0 shadow-sm">
                    <div class="position-relative">
                        <?php 
                        // Si hay upload asociado, servir vía script seguro
                        $img = !empty($recurso['upload_path']) 
                            ? "../../../serve_image.php?p=" . rawurlencode($recurso['upload_path']) 
                            : "../../../assets/placeholder.jpg"; 
                        ?>
                        <img src="<?= htmlspecialchars($img) ?>" class="resource-img" alt="Imagen recurso" onerror="this.src='https://placehold.co/400x300?text=Sin+Imagen'">
                        
                        <span class="status-badge status-<?= $recurso['estado'] ?>">
                            <?= ucfirst($recurso['estado']) ?>
                        </span>
                    </div>
                    
                    <div class="card-body">
                        <h5 class="card-title mb-1"><?= htmlspecialchars($recurso['nombre']) ?></h5>
                        <p class="text-muted small mb-2"><?= ucfirst($recurso['tipo']) ?></p>
                        
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted"><i class="bi bi-people-fill"></i> Cap: <?= $recurso['capacidad'] ?></small>
                            
                            <div class="btn-group">
                                <a href="formulario_recurso.php?id=<?= $recurso['id_recurso'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-resource" data-id="<?= $recurso['id_recurso'] ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../../js/admin_recursos/admin_recursos.js"></script>

</body>
</html>
