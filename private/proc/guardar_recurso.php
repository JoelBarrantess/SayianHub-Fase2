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

// Manejo de la imagen siguiendo buenas prácticas de PHP uploads
$uploadId = null;
$file = $_FILES['imagen'] ?? null;
$maxFormSize = isset($_POST['MAX_FILE_SIZE']) ? (int)$_POST['MAX_FILE_SIZE'] : 0;

if ($file && isset($file['error'])) {
    $error = (int)$file['error'];
    if ($error === UPLOAD_ERR_OK) {
        $tmpPath = $file['tmp_name'] ?? '';
        $originalName = $file['name'] ?? '';
        $size = (int)($file['size'] ?? 0);
        $mime = $file['type'] ?? '';

        // Validaciones de extensión y tamaño
        $parts = explode('.', $originalName);
        $extension = strtolower(end($parts) ?: '');
        $allowed = ['jpg','jpeg','gif','png','webp','svg','zip','txt','xls','doc','pdf'];
        if (!in_array($extension, $allowed, true)) {
            $_SESSION['edit_user_error'] = 'El archivo es de un tipo no permitido.';
            header('Location: ../../public/pages/admin/recursos/formulario_recurso.php?id=' . urlencode((string)$id));
            exit;
        }
        if ($maxFormSize > 0 && $size > $maxFormSize) {
            $_SESSION['edit_user_error'] = 'El fichero excede el tamaño máximo permitido.';
            header('Location: ../../public/pages/admin/recursos/formulario_recurso.php?id=' . urlencode((string)$id));
            exit;
        }

        // Directorio destino privado (no accesible directamente)
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0775, true)) {
                $_SESSION['edit_user_error'] = 'No se pudo preparar el directorio de subida.';
                header('Location: ../../public/pages/admin/recursos/formulario_recurso.php?id=' . urlencode((string)$id));
                exit;
            }
        }

        // Renombrado único para evitar colisiones
        $baseName = $parts[0] ?? 'upload';
        $filename = $baseName . '_' . uniqid('', true) . '.' . $extension;
        $destPath = $uploadDir . $filename;

        // Mover a destino definitivo
        if (!move_uploaded_file($tmpPath, $destPath)) {
            $_SESSION['edit_user_error'] = 'Error al mover el fichero subido.';
            header('Location: ../../public/pages/admin/recursos/formulario_recurso.php?id=' . urlencode((string)$id));
            exit;
        }

        // Persistir referencia en tabla uploads
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
    } elseif ($error !== UPLOAD_ERR_NO_FILE) {
        // Mapear errores de subida a mensajes amigables
        $msg = 'Error desconocido en la subida.';
        switch ($error) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $msg = 'El fichero excede el tamaño máximo permitido.'; break;
            case UPLOAD_ERR_PARTIAL:
                $msg = 'El fichero no se subió completamente.'; break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $msg = 'Falta la carpeta temporal.'; break;
            case UPLOAD_ERR_CANT_WRITE:
                $msg = 'No se pudo escribir el fichero en el disco.'; break;
            case UPLOAD_ERR_EXTENSION:
                $msg = 'Subida de fichero detenida por una extensión.'; break;
        }
        $_SESSION['edit_user_error'] = $msg;
        header('Location: ../../public/pages/admin/recursos/formulario_recurso.php?id=' . urlencode((string)$id));
        exit;
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
                    // archivo está en private/uploads
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
