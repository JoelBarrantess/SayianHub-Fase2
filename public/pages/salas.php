<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../../public/pages/login.php');
    exit;
}

require_once '../../private/proc/sala_detalles_process.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($sala['nombre_sala']) ?> | Saiyan Hub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/salas.css">
</head>
<body>

<div class="container-fluid px-4 py-4">
    
    <div class="header-sala d-flex justify-content-between align-items-center">
        <div>
            <h2>
                <i class="bi bi-diagram-3"></i> 
                <?= htmlspecialchars($sala['nombre_sala']) ?>
                <span class="badge-tipo"><?= htmlspecialchars($sala['tipo']) ?></span>
            </h2>
        </div>
        <a href="room_selection.php" class="btn btn-volver">
            Ver todas las salas
        </a>
    </div>

    <div class="leyenda">
        <div class="leyenda-item">
            <div class="leyenda-color libre"></div>
            <span>Silla Libre</span>
        </div>
        <div class="leyenda-item">
            <div class="leyenda-color ocupada"></div>
            <span>Silla Ocupada</span>
        </div>
        <div class="leyenda-item">
            <i class="bi bi-info-circle"></i>
            <span>Haz clic en las sillas (individual) o en el cuerpo de la mesa (completa) para cambiar su estado</span>
        </div>
    </div>

    <div class="sala-plano">
        <div class="mesas-grid">
            <?php foreach ($mesas as $mesa): ?>
                <?php
                    $ocupada = $mesa['estado'] === 'ocupada';
                    $mesaColorClase = $ocupada ? 'ocupada' : 'libre';
                    $num_sillas = (int)$mesa['num_sillas'];
                    
                    $nuevoEstadoMesa = $ocupada ? 'libre' : 'ocupada';
                    $urlMesa = "../../private/proc/mesa_toggle.php?id_mesa={$mesa['id_mesa']}&estado={$nuevoEstadoMesa}&id_sala={$sala['id_sala']}";
                ?>
                <div class="mesa-container">
                    <div class="mesa <?= $mesaColorClase ?>" 
                        id="mesa-<?= $mesa['id_mesa'] ?>"
                        data-url="<?= htmlspecialchars($urlMesa) ?>">
                        
                        <div class="mesa-cuerpo">
                            <p class="mesa-nombre"><?= htmlspecialchars($mesa['nombre_mesa']) ?></p>
                            <p class="mesa-capacidad">
                                <?= $mesa['sillas_ocupadas'] ?>/<?= $num_sillas ?> ocupadas
                            </p>
                            <?php if (!$ocupada): ?>
                            <a href="reservas/reservar.php?id_mesa=<?= (int)$mesa['id_mesa'] ?>" class="btn btn-sm btn-outline-primary" title="Reservar esta mesa">
                                <i class="bi bi-calendar-plus"></i> Reservar
                            </a>
                            <?php endif; ?>
                        </div>

                        <?php
                        $sillasArray = $mesa['sillas'];
                        for ($i = 0; $i < count($sillasArray); $i++):
                            $silla = $sillasArray[$i];
                            // calcular el angulo dependiendo la capacidad de la mesa.
                            $angle = ($num_sillas > 0) ? (360 / $num_sillas) * $i : 0;
                            $sillaOcupada = $silla['estado'] === 'ocupada';
                            $sillaColorClase = $sillaOcupada ? 'ocupada' : 'libre';
                            $urlSilla = "../../private/proc/silla_toggle.php?id_silla={$silla['id_silla']}&id_sala={$sala['id_sala']}";
                        ?>
                            <div class="silla <?= $sillaColorClase ?>" 
                                data-angle="<?= $angle ?>" 
                                data-url="<?= htmlspecialchars($urlSilla) ?>"
                                data-mesa="<?= htmlspecialchars($mesa['nombre_mesa']) ?>"
                                data-numero="<?= $silla['numero_silla'] ?>"
                                data-ocupada="<?= $sillaOcupada ? 'true' : 'false' ?>"
                                title="Silla <?= $silla['numero_silla'] ?>">
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../js/salas.js"></script>

</body>
</html>