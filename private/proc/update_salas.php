<?php
require_once '../../../../private/db/db_conn.php';

$errores = [];
$formSala = ['nombre' => ''];
$formMesas = [];
$procesado = false;
$estadosPermitidos = ['libre', 'ocupada', 'reservada']; 

if (!isset($conn) || !$conn) {
    // Crear conexión si no existe
    try {
        $conn = isset($host) ? connection($host, $user, $pass, $db) : null;
    } catch (Throwable $e) {
        $errores['internal'] = 'Sin conexión a la base de datos.';
    }
}

if (!isset($salaId) || !(int)$salaId) {
    $salaId = (int) filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
}

// 2. Proceso POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errores)) {
    $procesado = true;
    $idsMesasPost = [];

    $stmtMesasActuales = $conn->prepare("SELECT id_mesa FROM mesas WHERE id_sala = :id");
    $stmtMesasActuales->execute([':id' => $salaId]);
    $idsMesasActuales = $stmtMesasActuales->fetchAll(PDO::FETCH_COLUMN);

    $formSala['nombre'] = trim($_POST['nombre'] ?? '');

    if ($formSala['nombre'] === '') {
        $errores['nombre'] = 'El nombre es obligatorio.';
    } else {
        $stmtDup = $conn->prepare(
            "SELECT COUNT(*) FROM salas WHERE LOWER(nombre_sala) = LOWER(:nombre) AND id_sala <> :id"
        );
        $stmtDup->execute([
            ':nombre' => $formSala['nombre'],
            ':id'     => $salaId
        ]);
        if ($stmtDup->fetchColumn() > 0) {
            $errores['nombre'] = 'Ya existe una sala con ese nombre.';
        }
    }

    $mesasPost = $_POST['mesas'] ?? [];
    foreach ($mesasPost as $key => $datos) {
        $mesaId = isset($datos['id']) ? (int)$datos['id'] : 0;
        $nombreMesa = trim($datos['nombre'] ?? '');
        $estado = isset($datos['estado']) ? strtolower(trim($datos['estado'])) : 'libre';

        if ($mesaId > 0) {
            $idsMesasPost[] = $mesaId;
        }

        $totalSillasMesa = max(0, (int)($datos['sillas'] ?? 0));

        if ($totalSillasMesa > 10) {
            $errores["sillas_$key"] = 'Máximo 10 sillas por mesa.';
        }

        $formMesas[$key] = [
            'id_mesa' => $mesaId,
            'nombre'  => $nombreMesa,
            'estado'  => $estado,
            'sillas'  => $totalSillasMesa
        ];

        // Validación de sillas y estado
        if ($totalSillasMesa <= 0) {
            $errores["mesa_$key"] = 'Debes indicar al menos una silla.';
        }
        if (!in_array($estado, $estadosPermitidos, true)) {
            $errores["estado_$key"] = 'Estado inválido.';
        }
    }

    if (!empty($errores)) {
    } else {

        $totalSillas = 0;
        foreach ($formMesas as $mesaDatos) $totalSillas += $mesaDatos['sillas'];

        try {
            $conn->beginTransaction();
            
            $idsMesasAEliminar = array_diff($idsMesasActuales, $idsMesasPost);

            if (!empty($idsMesasAEliminar)) {
                $placeholders = implode(',', array_fill(0, count($idsMesasAEliminar), '?'));
                
                $stmtDelOcupaciones = $conn->prepare("DELETE FROM ocupaciones WHERE id_mesa IN ($placeholders)");
                $stmtDelOcupaciones->execute($idsMesasAEliminar);

                $stmtDelSillas = $conn->prepare("DELETE FROM sillas WHERE id_mesa IN ($placeholders)");
                $stmtDelSillas->execute($idsMesasAEliminar);

                $stmtDelMesas = $conn->prepare("DELETE FROM mesas WHERE id_mesa IN ($placeholders)");
                $stmtDelMesas->execute($idsMesasAEliminar);
            }

            $stmtSala = $conn->prepare(
                "UPDATE salas
                 SET nombre_sala = :nombre, capacidad_total = :capacidad
                 WHERE id_sala = :id"
            );
            $stmtSala->execute([
                ':nombre'    => $formSala['nombre'],
                ':capacidad' => $totalSillas,
                ':id'        => $salaId
            ]);

            $stmtUpdateMesa = $conn->prepare(
                "UPDATE mesas
                 SET nombre_mesa = :nombre, num_sillas = :sillas, estado = :estado
                 WHERE id_mesa = :id AND id_sala = :sala"
            );

            $stmtInsertMesa = $conn->prepare(
                "INSERT INTO mesas (id_sala, nombre_mesa, num_sillas, estado)
                 VALUES (:sala, :nombre, :sillas, :estado)"
            );
            
            $stmtInsertSilla = $conn->prepare(
                "INSERT INTO sillas (id_mesa, numero_silla, estado)
                 VALUES (:mesa, :numero, :estado)"
            );


            foreach ($formMesas as $mesaDatos) {
                $numSillas = (int)$mesaDatos['sillas'];
                $mesaEstado = $mesaDatos['estado'];

                if ($mesaDatos['id_mesa'] > 0) {
                    $mesaId = $mesaDatos['id_mesa'];

                    $stmtUpdateMesa->execute([
                        ':nombre' => $mesaDatos['nombre'],
                        ':sillas' => $numSillas, 
                        ':estado' => $mesaEstado,
                        ':id'     => $mesaId,
                        ':sala'   => $salaId
                    ]);

                    $stmtS = $conn->prepare("SELECT id_silla, numero_silla FROM sillas WHERE id_mesa = :mesa ORDER BY numero_silla ASC");
                    $stmtS->execute([':mesa' => $mesaId]);
                    $sillas = $stmtS->fetchAll(PDO::FETCH_ASSOC);
                    $actual = count($sillas);
                    
                    if ($actual > $numSillas) {
                        $aEliminar = array_slice(array_reverse($sillas), 0, $actual - $numSillas);
                        if ($aEliminar) {
                            $ids = array_column($aEliminar, 'id_silla');
                            $placeholders = implode(',', array_fill(0, count($ids), '?'));
                            // La tabla ocupaciones no referencia id_silla según el esquema,
                            // por lo que solo eliminamos sillas.
                            $stmtDel = $conn->prepare("DELETE FROM sillas WHERE id_silla IN ($placeholders)");
                            $stmtDel->execute($ids);
                        }
                    } elseif ($actual < $numSillas) {
                        for ($i = $actual + 1; $i <= $numSillas; $i++) {
                            $stmtInsertSilla->execute([
                                ':mesa'   => $mesaId, 
                                ':numero' => $i, 
                                ':estado' => $mesaEstado
                            ]);
                        }
                    }
                } else {
                    $stmtInsertMesa->execute([
                        ':sala'   => $salaId,
                        ':nombre' => $mesaDatos['nombre'],
                        ':sillas' => $numSillas, 
                        ':estado' => $mesaEstado
                    ]);
                    $nuevaMesaId = (int)$conn->lastInsertId();

                    for ($i = 1; $i <= $numSillas; $i++) {
                        $stmtInsertSilla->execute([
                            ':mesa'   => $nuevaMesaId, 
                            ':numero' => $i, 
                            ':estado' => $mesaEstado 
                        ]);
                    }
                }
            }

            $conn->commit();
            header('Location: editar_salas.php?id=' . $salaId . '&status=updated');
            exit;
        } catch (Throwable $e) {
            if ($conn->inTransaction()) $conn->rollBack();
            error_log('Error al actualizar sala: ' . $e->getMessage());
            header('Location: admin_salas.php?status=error_db');
            exit;
        }
    }
}

?>