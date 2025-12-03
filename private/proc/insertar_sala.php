<?php


$resultado = [
    'errores' => [],
    'form' => [
        'nombre'   => '',
        'tipo'     => '',
        'capacidad' => ''
    ]
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $tipo = strtolower(trim($_POST['tipo'] ?? ''));
    $capacidad = trim($_POST['capacidad'] ?? '');

    $resultado['form']['nombre'] = $nombre;
    $resultado['form']['tipo'] = $tipo;
    $resultado['form']['capacidad'] = $capacidad;

    if ($nombre === '') {
        $resultado['errores']['nombre'] = 'El nombre es obligatorio.';
    } else {
        $stmtCheck = $conn->prepare(
            "SELECT COUNT(*) FROM salas WHERE LOWER(nombre_sala) = LOWER(:nombre)"
        );
        $stmtCheck->execute([':nombre' => $nombre]);
        if ($stmtCheck->fetchColumn() > 0) {
            $resultado['errores']['nombre'] = 'Ya existe una sala con ese nombre.';
        }
    }

    if ($tipo === '' || !in_array($tipo, $tiposDisponibles, true)) {
        $resultado['errores']['tipo'] = 'Selecciona un tipo válido.';
    }

    if ($capacidad === '' || (int)$capacidad <= 0) {
        $resultado['errores']['capacidad'] = 'La capacidad debe ser mayor que cero.';
    }

    if (empty($resultado['errores'])) {
        $stmt = $conn->prepare(
            "INSERT INTO salas (nombre_sala, tipo, capacidad_total)
             VALUES (:nombre, :tipo, :capacidad)"
        );
        $stmt->execute([
            ':nombre'    => $nombre,
            ':tipo'      => $tipo,
            ':capacidad' => (int)$capacidad
        ]);

        header('Location: crear_salas.php?status=success');
        exit;
    }
}
