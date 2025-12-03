<?php
require_once '../../private/db/db_conn.php';

if (!isset($_GET['id_sala'])) {
    die("Falta el ID de la sala.");
}

$id_sala = (int) $_GET['id_sala'];

try {
    $conn = connection($host, $user, $pass, $db);

    $stmt = $conn->prepare("SELECT * FROM salas WHERE id_sala = :id");
    $stmt->bindParam(':id', $id_sala, PDO::PARAM_INT);
    $stmt->execute();
    $sala = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sala) {
        die("Sala no encontrada.");
    }

    $stmt = $conn->prepare("
        SELECT id_mesa, id_sala, nombre_mesa, num_sillas, estado
        FROM mesas 
        WHERE id_sala = :id
        ORDER BY id_mesa ASC
    ");
    $stmt->bindParam(':id', $id_sala, PDO::PARAM_INT);
    $stmt->execute();
    $mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $mesas_map = [];
    foreach ($mesas as $mesa) {
        $mesa['sillas'] = [];
        $mesa['sillas_ocupadas'] = 0;
        $mesas_map[$mesa['id_mesa']] = $mesa;
    }

    $ids_mesas = array_keys($mesas_map);
    if (!empty($ids_mesas)) {
        $placeholders = implode(',', array_fill(0, count($ids_mesas), '?'));

        $stmt_sillas = $conn->prepare("
            SELECT id_silla, id_mesa, numero_silla, estado
            FROM sillas 
            WHERE id_mesa IN ($placeholders)
            ORDER BY id_mesa, numero_silla
        ");
        $stmt_sillas->execute($ids_mesas);
        $todas_sillas = $stmt_sillas->fetchAll(PDO::FETCH_ASSOC);

        foreach ($todas_sillas as $silla) {
            $id_mesa = $silla['id_mesa'];
            if (isset($mesas_map[$id_mesa])) {
                $mesas_map[$id_mesa]['sillas'][] = $silla;

                if ($silla['estado'] === 'ocupada') {
                    $mesas_map[$id_mesa]['sillas_ocupadas']++;
                }
            }
        }
    }

    $mesas = array_values($mesas_map);
} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}
