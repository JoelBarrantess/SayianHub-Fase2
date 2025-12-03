<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../../public/pages/login.php');
    exit;
}

require_once __DIR__ . '/../../private/proc/procesar_logs.php';
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Logs - Ocupaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/logs.css">
</head>

<body class="bg-light">
    <header class="logs-header">
        <div class="container py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <a href="dashboard.php" class="btn btn-volver btn-back" aria-label="Volver">&larr; Volver</a>
                <h1 class="h4 mb-0 text-white">Logs de ocupaciones</h1>
            </div>
            <div class="btn-toolbar" role="toolbar" aria-label="Acciones">
                <div class="btn-group me-2" role="group">
                </div>
            </div>
        </div>
    </header>

    <div class="container py-4">

        <!-- Uso de filtros -->
        <form method="get" class="mb-3">
            <div class="d-flex flex-wrap align-items-end gap-2">
                <div class="form-group" style="min-width:160px;">
                    <label for="f_sala" class="form-label small mb-1">Sala</label>
                    <select id="f_sala" name="f_sala" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        <?php foreach ($salas as $s): ?>
                            <option value="<?= (int)$s['id_sala'] ?>" <?= (!empty($f_id_sala) && $f_id_sala == $s['id_sala']) ? 'selected' : '' ?>><?= htmlspecialchars($s['nombre_sala']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="min-width:160px;">
                    <label for="f_mesa" class="form-label small mb-1">Mesa</label>
                    <select id="f_mesa" name="f_mesa" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        <?php foreach ($mesas as $m): ?>
                            <option value="<?= (int)$m['id_mesa'] ?>" <?= (!empty($f_id_mesa) && $f_id_mesa == $m['id_mesa']) ? 'selected' : '' ?>><?= htmlspecialchars($m['nombre_mesa']) ?> (Sala <?= (int)$m['id_sala'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="min-width:160px;">
                    <label for="f_camarero" class="form-label small mb-1">Camarero</label>
                    <select id="f_camarero" name="f_camarero" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <?php foreach ($camareros as $u):
                            $unombre = trim((string)($u['nombre'] ?? ''));
                            $uusuario = trim((string)($u['usuario'] ?? ''));
                            if ($unombre !== '' && mb_strtolower($unombre) !== mb_strtolower($uusuario)) {
                                $label = $unombre . ' (' . $uusuario . ')';
                            } elseif ($unombre !== '') {
                                $label = $unombre;
                            } else {
                                $label = $uusuario;
                            }
                        ?>
                            <option value="<?= (int)$u['id_usuario'] ?>" <?= (!empty($f_id_usuario) && $f_id_usuario == $u['id_usuario']) ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group d-flex gap-2 align-items-center" style="min-width:320px;">
                    <div style="min-width:150px;">
                        <label for="f_start" class="form-label small mb-1">Desde:</label>
                        <input id="f_start" name="f_start" type="datetime-local" class="form-control form-control-sm" placeholder="Desde" value="<?= !empty($f_start) ? htmlspecialchars($f_start) : '' ?>">
                    </div>
                    <div style="min-width:150px;">
                        <label for="f_end" class="form-label small mb-1">Hasta:</label>
                        <input id="f_end" name="f_end" type="datetime-local" class="form-control form-control-sm" placeholder="Hasta" value="<?= !empty($f_end) ? htmlspecialchars($f_end) : '' ?>">
                    </div>
                </div>

                <div class="ms-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">Aplicar filtros</button>
                    <a href="logs.php" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                </div>
            </div>
        </form>

        <div class="table-responsive bg-white shadow-sm rounded">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Inicio</th>
                        <th>Mesa</th>
                        <th>Camarero</th>
                        <th>Fin</th>
                        <th>Sala</th>
                        <th>Duración</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">No hay datos que mostrar.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $r): ?>
                            <?php
                            $salaName = $r['nombre_sala'] ?? '-';
                            $salaClass = 'sala-default';
                            $lc = mb_strtolower($salaName);
                            if (strpos($lc, 'terra') !== false) {
                                $salaClass = 'sala-terraza';
                            } elseif (strpos($lc, 'priv') !== false) {
                                $salaClass = 'sala-privada';
                            } elseif (strpos($lc, 'comed') !== false || strpos($lc, 'comedor') !== false) {
                                $salaClass = 'sala-comedor';
                            }
                            ?>
                            <tr>
                                <td>
                                    <span class="date-cell"><?= htmlspecialchars($r['fecha_ocupacion'] ?? '-') ?></span>
                                </td>
                                <td>
                                    <span class="mesa-badge <?= $salaClass ?>"><?= htmlspecialchars($r['nombre_mesa'] ?? '-') ?></span>
                                </td>
                                <td><?= htmlspecialchars($r['camarero'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($r['fecha_liberacion'] ?? '-') ?></td>
                                <td>
                                    <span class="sala-badge <?= $salaClass ?>"><?= htmlspecialchars($salaName) ?></span>
                                </td>
                                <td><?= htmlspecialchars($r['duracion'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        // Paginación
        <?php if (!empty($totalPages) && $totalPages > 1): ?>
            <nav class="mt-3" aria-label="Paginación">
                <ul class="pagination">
                    <?php
                    $allowed = ['f_sala', 'f_mesa', 'f_camarero', 'f_start', 'f_end'];

                    $qs = array_intersect_key($_GET, array_flip($allowed));
                    $qs = array_filter($qs, fn($v) => $v !== '');
                    $base = http_build_query($qs);
                    $prefix = $base !== '' ? $base . '&' : '';

                    $currentPage = isset($page) ? (int)$page : 1;
                    $range = 3;
                    $start = max(1, $currentPage - $range);
                    $end = min($totalPages, $currentPage + $range);
                    ?>
                    <li class="page-item <?= $currentPage == 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="logs.php?<?= htmlspecialchars($prefix . 'page=' . max(1, $currentPage - 1)) ?>">&laquo; Prev</a>
                    </li>
                    <?php for ($p = $start; $p <= $end; $p++): ?>
                        <li class="page-item <?= $p == $currentPage ? 'active' : '' ?>">
                            <a class="page-link" href="logs.php?<?= htmlspecialchars($prefix . 'page=' . $p) ?>"><?= $p ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="logs.php?<?= htmlspecialchars($prefix . 'page=' . min($totalPages, $currentPage + 1)) ?>">Next &raquo;</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>