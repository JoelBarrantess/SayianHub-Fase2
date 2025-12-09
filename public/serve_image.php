<?php
// Sirve archivos almacenados en private/uploads de forma controlada
// Parámetro: p = ruta relativa almacenada en BD (ej. 'uploads/res_abc.jpg')

session_start();

$pathRel = $_GET['p'] ?? '';
if ($pathRel === '') {
    http_response_code(404);
    exit('Not found');
}

// Normalizar y evitar path traversal
$pathRel = str_replace(['..', '\\'], ['', '/'], $pathRel);
$base = __DIR__ . '/../private/';
$full = realpath($base . '/' . $pathRel);

if (!$full || strpos($full, realpath($base)) !== 0 || !is_file($full)) {
    http_response_code(404);
    exit('Not found');
}

// Detectar mime
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $full) ?: 'application/octet-stream';
finfo_close($finfo);

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($full));
// Cache básico
header('Cache-Control: max-age=86400');
readfile($full);
