<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../../public/pages/login.html');
    exit;
}

require_once '../../private/proc/dashboard.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Dashboard | Saiyan Hub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>

<body>

    <div class="dashboard-container">
        
        <aside class="sidebar">
            <div>
                <div class="sidebar-header">
                    <h3><i class="bi bi-lightning-charge-fill"></i> Saiyan Hub</h3>
                    <div class="user-info">
                        <i class="bi bi-person-circle"></i>
                        <span><?= htmlspecialchars($_SESSION['nombre']) ?></span>
                        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                            <a href="admin/users/admin.php" class="admin-badge" title="Panel de administración">
                                <i class="bi bi-shield-lock-fill" style="color: #fff; font-size: 0.75rem;"></i>
                            </a>
                        <?php endif; ?>
                        <a href="logs.php" class="admin-badge" title="Registro de actividad" style="background: #6c757d; margin-left: 4px;">
                            <i class="bi bi-file-text-fill" style="color: #fff; font-size: 0.75rem;"></i>
                        </a>
                    </div>
                </div>

                <nav>
                    <h6 class="nav-section-title">Salas Disponibles</h6>
                    <div class="mb-3 text-center">
                        <a href="../pages/room_selection.php" class="btn btn-sm btn-outline-light w-100" style="font-weight: 500;">
                            <i class="bi bi-grid-fill"></i> Todas las salas
                        </a>
                    </div>
                    <ul class="nav flex-column">
                        <?php foreach ($salas as $sala): ?>
                            <li class="nav-item">
                                <a href="salas.php?id_sala=<?= urlencode($sala['id_sala']) ?>" class="nav-link">
                                    <?php if ($sala['tipo'] === 'terraza'): ?>
                                        <i class="bi bi-sun-fill"></i>
                                    <?php elseif ($sala['tipo'] === 'comedor'): ?>
                                        <i class="bi bi-egg-fried"></i>
                                    <?php else: ?>
                                        <i class="bi bi-door-closed-fill"></i>
                                    <?php endif; ?>
                                    <span><?= htmlspecialchars($sala['nombre_sala']) ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            </div>

            <div style="padding: 16px;">
                <a href="../../private/proc/logout.php" class="btn btn-logout">
                    <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                </a>
            </div>
        </aside>

        <main class="main-content">

            <button class="sidebar-toggle" aria-label="Abrir menú">
                <i class="bi bi-list"></i> Menú
            </button>

            <div class="page-header">
                <h1 class="page-title">Control</h1>
                <p class="page-subtitle">Gestión de mesas y ocupación en tiempo real</p>
            </div>

            <div class="dashboard-layout">

                <div class="hero-stats">
                    <div class="hero-card">
                        <div class="hero-card-header">
                            <div class="hero-card-icon">
                                <i class="bi bi-table"></i>
                            </div>
                        </div>
                        <div class="hero-card-content">
                            <div class="hero-card-title">Total de Mesas</div>
                            <div class="hero-card-value"><?= $resumen['totalMesas'] ?></div>
                            <div class="hero-card-subtitle">en todas las salas</div>
                        </div>
                    </div>

                    <div class="hero-card ocupadas">
                        <div class="hero-card-header">
                            <div class="hero-card-icon">
                                <i class="bi bi-lock-fill"></i>
                            </div>
                        </div>
                        <div class="hero-card-content">
                            <div class="hero-card-title">Mesas Ocupadas</div>
                            <div class="hero-card-value"><?= $resumen['mesasOcupadas'] ?></div>
                            <div class="hero-card-subtitle">actualmente en uso</div>
                        </div>
                    </div>

                    <div class="hero-card libres">
                        <div class="hero-card-header">
                            <div class="hero-card-icon">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                        </div>
                        <div class="hero-card-content">
                            <div class="hero-card-title">Mesas Libres</div>
                            <div class="hero-card-value"><?= $resumen['mesasLibres'] ?></div>
                            <div class="hero-card-subtitle">disponibles ahora</div>
                        </div>
                    </div>

                    <div class="hero-card">
                        <div class="hero-card-header">
                            <div class="hero-card-icon">
                                <i class="bi bi-person-lines-fill"></i>
                            </div>
                        </div>
                        <div class="hero-card-content">
                            <div class="hero-card-title">Capacidad Total</div>
                            <div class="hero-card-value"><?= $resumen['capacidadTotal'] ?></div>
                            <div class="hero-card-subtitle">personas máximo</div>
                        </div>
                    </div>
                </div>

                <div class="chart-card">
                    <h3 class="chart-card-title">
                        <i class="bi bi-pie-chart-fill"></i> Distribución
                    </h3>
                    <div class="chart-wrapper">
                        <canvas id="ocupacionMesas"></canvas>
                    </div>
                </div>

            </div>

            <div class="secondary-stats">
                <div class="stat-card-small">
                    <div class="stat-card-small-icon">
                        <i class="bi bi-door-open-fill"></i>
                    </div>
                    <div class="stat-card-small-value"><?= $resumen['totalSalas'] ?></div>
                    <div class="stat-card-small-title">Salas</div>
                </div>

                <div class="stat-card-small">
                    <div class="stat-card-small-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="stat-card-small-value"><?= $resumen['totalUsuarios'] ?></div>
                    <div class="stat-card-small-title">Usuarios</div>
                </div>

                <div class="stat-card-small">
                    <div class="stat-card-small-icon">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="stat-card-small-value"><?= $resumen['ocupacionesActivas'] ?></div>
                    <div class="stat-card-small-title">Activas</div>
                </div>

                <div class="stat-card-small">
                    <div class="stat-card-small-icon">
                        <i class="bi bi-grid-3x3-gap-fill"></i>
                    </div>
                    <div class="stat-card-small-value"><?= $resumen['promedioSillas'] ?></div>
                    <div class="stat-card-small-title">Promedio</div>
                </div>
            </div>

        </main>
    </div>

    <script>
        window.chartLabels = <?= $chartLabels ?>;
        window.chartData = <?= $chartData ?>;
    </script>

    <script src="../js/chart.js"></script>
    <script src="../js/sidebarToggle.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>