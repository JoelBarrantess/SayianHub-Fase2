<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db/db_conn.php';

// if (!isset($_SESSION['id_usuario'])) {
//     header('Location: ../../public/pages/login.html');
//     exit;
// }

try {
    $pdo = connection($host, $user, $pass, $db);

    $stmt = $pdo->query("SELECT id_usuario, nombre, apellidos, usuario, rol FROM usuarios ORDER BY nombre, usuario");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SELECT id_sala, nombre_sala, tipo, capacidad_total FROM salas ORDER BY nombre_sala");
    $salas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SELECT m.id_mesa, m.id_sala, m.nombre_mesa, m.num_sillas, m.estado, s.nombre_sala
                        FROM mesas m
                        LEFT JOIN salas s ON m.id_sala = s.id_sala
                        ORDER BY m.id_sala, m.nombre_mesa");
    $mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SELECT o.id_ocupacion, o.id_mesa, o.id_usuario, o.fecha_ocupacion, o.fecha_liberacion,
                                m.nombre_mesa, s.nombre_sala, u.usuario, u.nombre
                         FROM ocupaciones o
                         LEFT JOIN mesas m ON o.id_mesa = m.id_mesa
                         LEFT JOIN salas s ON m.id_sala = s.id_sala
                         LEFT JOIN usuarios u ON o.id_usuario = u.id_usuario
                         ORDER BY o.fecha_ocupacion DESC
                         LIMIT 200");
    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "Error al conectar o consultar la base de datos: " . $e->getMessage();
}

if (!function_exists('h')) {
    function h($s)
    {
        return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
    }
}
