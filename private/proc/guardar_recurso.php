<?php
session_start();
require_once __DIR__ . '/../db/db_conn.php';

if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['rol'], ['admin', 'gerente', 'mantenimiento'])) {
    header('Location: ../../public/pages/login.html');
    exit;
}

$conn = connection($host, $user, $pass, $db);

$id = $_POST['id_recurso'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$tipo = $_POST['tipo'] ?? 'otro';
$capacidad = (int)($_POST['capacidad'] ?? 0);
$estado = $_POST['estado'] ?? 'disponible';
// soporte para quitar imagen actual (edición)
$currentUploadId = isset($_POST['current_id_upload']) && $_POST['current_id_upload'] !== '' ? (int)$_POST['current_id_upload'] : null;
$removeImage = isset($_POST['remove_image']) && $_POST['remove_image'] === '1';

// Manejo de la imagen
$uploadId = null;
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    // Guardar en private/uploads (no accesible directamente desde la web)
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $originalName = $_FILES['imagen']['name'];
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $filename = uniqid('res_') . ($ext ? ('.' . $ext) : '');
    $destPath = $uploadDir . $filename;
    
    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $destPath)) {
        // Insertar registro en tabla uploads
        $mime = $_FILES['imagen']['type'] ?? null;
        $size = (int)($_FILES['imagen']['size'] ?? 0);
        $pathRel = 'uploads/' . $filename;
        $stmtUp = $conn->prepare("INSERT INTO uploads (filename, path, mime, size, uploaded_by) VALUES (:filename, :path, :mime, :size, :uploaded_by)");
        $stmtUp->execute([
            ':filename' => $filename,
            ':path' => $pathRel,
            ':mime' => $mime,
            ':size' => $size,
            ':uploaded_by' => $_SESSION['id_usuario'] ?? null,
        ]);
        $uploadId = (int)$conn->lastInsertId();
    }
}

try {
    if ($id) {
        // Actualizar
        $sql = "UPDATE recursos SET nombre = :nombre, tipo = :tipo, capacidad = :capacidad, estado = :estado";
        $params = [
            ':nombre' => $nombre,
            ':tipo' => $tipo,
            ':capacidad' => $capacidad,
            ':estado' => $estado,
            ':id' => $id
        ];
        
        if ($uploadId) {
            $sql .= ", id_upload = :id_upload";
            $params[':id_upload'] = $uploadId;
        } elseif ($removeImage) {
            $sql .= ", id_upload = NULL";
            // borrar archivo físico y registro si existe currentUploadId
            if ($currentUploadId) {
                // obtener path
                $stmtSel = $conn->prepare("SELECT path FROM uploads WHERE id_upload = :id");
                $stmtSel->execute([':id' => $currentUploadId]);
                $up = $stmtSel->fetch(PDO::FETCH_ASSOC);
                if ($up && !empty($up['path'])) {
                    $full = __DIR__ . '/../' . $up['path'];
                    if (is_file($full)) { @unlink($full); }
                }
                // eliminar registro
                $stmtDel = $conn->prepare("DELETE FROM uploads WHERE id_upload = :id");
                $stmtDel->execute([':id' => $currentUploadId]);
            }
        }
        
        $sql .= " WHERE id_recurso = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
    } else {
        // Crear
        $sql = "INSERT INTO recursos (nombre, tipo, capacidad, estado, id_upload) VALUES (:nombre, :tipo, :capacidad, :estado, :id_upload)";
        // corregir typo en parámetro :capacidad_tipo -> :tipo
        $sql = "INSERT INTO recursos (nombre, tipo, capacidad, estado, id_upload) VALUES (:nombre, :tipo, :capacidad, :estado, :id_upload)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':tipo' => $tipo,
            ':capacidad' => $capacidad,
            ':estado' => $estado,
            ':id_upload' => $uploadId // puede ser null si no se subió
        ]);
    }
    
    header('Location: ../../public/pages/admin/recursos/crud_recursos.php?msg=success');
    exit;

} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}
