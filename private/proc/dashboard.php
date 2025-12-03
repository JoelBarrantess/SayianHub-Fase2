<?php
include_once '../../private/db/db_conn.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../../public/pages/login.php');
    exit;
}

try {
    $conn = connection($host, $user, $pass, $db);

    $resumen = [];
    $queries = [
        'totalMesas' => "SELECT COUNT(*) FROM mesas",
        'mesasOcupadas' => "SELECT COUNT(*) FROM mesas WHERE estado='ocupada'",
        'mesasLibres' => "SELECT COUNT(*) FROM mesas WHERE estado='libre'",
        'totalUsuarios' => "SELECT COUNT(*) FROM usuarios",
        'totalSalas' => "SELECT COUNT(*) FROM salas",
        'ocupacionesActivas' => "SELECT COUNT(*) FROM ocupaciones WHERE fecha_liberacion IS NULL",
        'capacidadTotal' => "SELECT SUM(capacidad_total) FROM salas",
        'promedioSillas' => "SELECT ROUND(AVG(num_sillas),1) FROM mesas"
    ];

    foreach ($queries as $key => $sql) {
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $resumen[$key] = $stmt->fetchColumn() ?? 0;
    }

    $stmt_salas = $conn->prepare("SELECT id_sala, nombre_sala, tipo FROM salas ORDER BY nombre_sala");
    $stmt_salas->execute();
    $salas = $stmt_salas->fetchAll(PDO::FETCH_ASSOC);

    $chartLabels = json_encode(['Mesas Libres', 'Mesas Ocupadas']);
    $chartData = json_encode([$resumen['mesasLibres'], $resumen['mesasOcupadas']]);
} catch (PDOException $e) {
    die("Error en dashboard_process.php: " . $e->getMessage());
}
