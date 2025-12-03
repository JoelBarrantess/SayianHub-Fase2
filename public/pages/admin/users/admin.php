<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../../private/proc/admin_page.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>SaiyanHub — Admin</title>
    <link rel="icon" type="image/x-icon" href="../../../assets/logo_simple.png" />
    <link rel="stylesheet" href="../../../css/admin.css">
</head>

<body class="admin-page">

    <header class="admin-header">
        <div class="brand">
            <h1>Panel de administración</h1>
        </div>



        <nav class="admin-nav" aria-label="Navegación de administración">
            <a class="tab tab-link" href="./admin.php" aria-current="page">Gestión de usuarios</a>
            <a class="tab tab-link" href="../salas/admin_salas.php">Salas y Mesas</a>
            <a class="tab tab-link" href="../../logs.php">Historial</a>
            <a href="../../dashboard.php" class="btn btn-volver">
                <i class="bi bi-arrow-left"></i> Volver al Dashboard
            </a>

        </nav>
    </header>

    <main class="admin-content">
        <section id="users" class="admin-section active">
            <h2>Gestión de usuarios</h2>
            <p class="muted">Lista de usuarios registrados. Acciones: editar, eliminar, cambiar rol.</p>
            <div class="table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre apellidos</th>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td data-label="ID"><?php echo $user['id_usuario']; ?></td>
                                <td data-label="Nombre"><?php echo trim(($user['nombre'] ?? '') . ' ' . ($user['apellidos'] ?? '')); ?></td>
                                <td data-label="Usuario"><?php echo $user['usuario'] ?? ''; ?></td>
                                <td data-label="Rol"><?php echo $user['rol'] ?? ''; ?></td>
                                <td>
                                    <a class="btn-simple btn-edit" href="editar_user.php?id=<?= $user['id_usuario'] ?>">Editar</a>
                                    <form method="post" action="../../../../private/proc/eliminar_usuario.php" style="display:inline" class="delete-user-form" data-username="<?= $user['usuario'] ?? '' ?>">
                                        <input type="hidden" name="id_usuario" value="<?= $user['id_usuario'] ?>">
                                        <button class="btn-simple btn-delete" type="submit">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>


        <section id="historial" class="admin-section">
            <h2>Historial</h2>
            <p class="muted">Registro de acciones relevantes: cambios en usuarios, salas y mesas, y ocupaciones.</p>
            <div class="table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Acción</th>
                            <th>Detalle</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historial as $r): ?>
                            <tr>
                                <td><?= $r['fecha_ocupacion'] ?? '' ?></td>
                                <td><?= !empty($r['nombre']) ? ($r['nombre'] . ' (' . $r['usuario'] . ')') : $r['usuario'] ?></td>
                                <td>Ocupación</td>
                                <td><?= (!empty($r['nombre_mesa']) ? $r['nombre_mesa'] . ' — ' : '') . ($r['nombre_sala'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>


        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="../../../js/admin_user/delete-user.js" defer></script>


</body>

</html>