<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db/db_conn.php';

$pdo = null;

$pdo = connection($host, $user, $pass, $db);


$f_id_sala = filter_input(INPUT_GET, 'f_sala', FILTER_VALIDATE_INT);
$f_id_mesa = filter_input(INPUT_GET, 'f_mesa', FILTER_VALIDATE_INT);
$f_id_usuario = filter_input(INPUT_GET, 'f_camarero', FILTER_VALIDATE_INT);

$f_start = filter_input(INPUT_GET, 'f_start', FILTER_DEFAULT);
$f_end = filter_input(INPUT_GET, 'f_end', FILTER_DEFAULT);
$f_page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);

$page = (int)$f_page;
if ($page < 1) {
    $page = 1;
}


function normalize_datetime($v)
{
    $v = trim((string)$v);

    if ($v === '') return null;

    $v = str_replace('T', ' ', $v);

    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $v)) {
        $v .= ':00';
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $v)) {
        $view = substr($v, 0, 16);
        $view = str_replace(' ', 'T', $view);
        return ['sql' => $v, 'view' => $view];
    }
    return null;
}

try {

    $sql = "SELECT o.id_ocupacion, o.id_mesa, o.id_usuario, o.fecha_ocupacion, o.fecha_liberacion,
             m.nombre_mesa, s.nombre_sala, u.usuario, u.nombre
         FROM ocupaciones o
         LEFT JOIN mesas m ON o.id_mesa = m.id_mesa
         LEFT JOIN salas s ON m.id_sala = s.id_sala
         LEFT JOIN usuarios u ON o.id_usuario = u.id_usuario";

    $where = [];
    $params = [];

    if ($f_id_sala) {
        $where[] = 'm.id_sala = :id_sala';
        $params[':id_sala'] = $f_id_sala;
    }
    if ($f_id_mesa) {
        $where[] = 'm.id_mesa = :id_mesa';
        $params[':id_mesa'] = $f_id_mesa;
    }
    if ($f_id_usuario) {
        $where[] = 'o.id_usuario = :id_usuario';
        $params[':id_usuario'] = $f_id_usuario;
    }

    if (!empty($f_start)) {
        $norm = normalize_datetime($f_start);
        if ($norm !== null) {
            $where[] = 'o.fecha_ocupacion >= :f_start';
            $params[':f_start'] = $norm['sql'];
            $f_start = $norm['view'];
        } else {
            $f_start = null;
        }
    }
    if (!empty($f_end)) {
        $norm2 = normalize_datetime($f_end);
        if ($norm2 !== null) {
            $where[] = 'o.fecha_ocupacion <= :f_end';
            $params[':f_end'] = $norm2['sql'];
            $f_end = $norm2['view'];
        } else {
            $f_end = null;
        }
    }
    $limit = 10; 
    $offset = ($page - 1) * $limit;

    if (!empty($where)) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY o.fecha_ocupacion DESC';
    $sql .= ' LIMIT :limit OFFSET :offset';
    $stmt = $pdo->prepare($sql);

    foreach ($params as $k => $v) {
        if (strpos($k, ':id_') === 0) {
            $stmt->bindValue($k, $v, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($k, $v, PDO::PARAM_STR);
        }
    }
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    $rawRows = $stmt->fetchAll(PDO::FETCH_ASSOC);


    $countSql = "SELECT COUNT(*) AS cnt FROM ocupaciones o
                 LEFT JOIN mesas m ON o.id_mesa = m.id_mesa
                 LEFT JOIN salas s ON m.id_sala = s.id_sala
                 LEFT JOIN usuarios u ON o.id_usuario = u.id_usuario";
    if (!empty($where)) {
        $countSql .= ' WHERE ' . implode(' AND ', $where);
    }
    $countStmt = $pdo->prepare($countSql);
    foreach ($params as $k => $v) {
        if (strpos($k, ':id_') === 0) {
            $countStmt->bindValue($k, $v, PDO::PARAM_INT);
        } else {
            $countStmt->bindValue($k, $v, PDO::PARAM_STR);
        }
    }
    $countStmt->execute();
    $total = (int)$countStmt->fetchColumn();
    $totalPages = (int)ceil($total / $limit);
} catch (Exception $e) {
    $rawRows = [];
}


$rows = [];
$meses_short = [
    '01' => 'ene',
    '02' => 'feb',
    '03' => 'mar',
    '04' => 'abr',
    '05' => 'may',
    '06' => 'jun',
    '07' => 'jul',
    '08' => 'ago',
    '09' => 'sep',
    '10' => 'oct',
    '11' => 'nov',
    '12' => 'dic'
];

$format_datetime = function ($dt) use ($meses_short) {
    if (empty($dt)) return '-';
    try {
        $d = new DateTime($dt);
        $day = $d->format('d');
        $mon = $d->format('m');
        $year = $d->format('y');
        $hour = (int)$d->format('H');
        $min = $d->format('i');
        $mon_short = $meses_short[$mon] ?? $d->format('M');
        return sprintf('%s-%s-%s %02dh %02dm', $day, $mon_short, $year, $hour, $min);
    } catch (Exception $e) {
        return '-';
    }
};

foreach ($rawRows as $r) {
    $inicio = $r['fecha_ocupacion'];
    $fin = $r['fecha_liberacion'];

    $duration = '-';
    try {
        $d1 = new DateTime($inicio);
        if (!empty($fin)) {
            $d2 = new DateTime($fin);
            $in_course = false;
        } else {
            $d2 = new DateTime();
            $in_course = true;
        }
        $diff = $d1->diff($d2);
        $hours = $diff->h + ($diff->d * 24);
        $minutes = $diff->i;
        $timeStr = sprintf('%02dh %02dm', $hours, $minutes);
        $duration = $in_course ? ('En curso (' . $timeStr . ')') : $timeStr;
    } catch (Exception $e) {
        $duration = '-';
    }

    $nombre = trim((string)($r['nombre'] ?? ''));
    $usuario = trim((string)($r['usuario'] ?? ''));
    if ($nombre !== '') {
        if (mb_strtolower($nombre) !== mb_strtolower($usuario)) {
            $camarero = $nombre . ' (' . $usuario . ')';
        } else {
            $camarero = $nombre;
        }
    } else {
        $camarero = $usuario;
    }

    $rows[] = [
        'id_ocupacion' => $r['id_ocupacion'],
        'id_mesa' => $r['id_mesa'],
        'id_usuario' => $r['id_usuario'],
        'fecha_ocupacion' => $format_datetime($inicio),
        'fecha_liberacion' => $format_datetime($fin),
        'nombre_sala' => $r['nombre_sala'],
        'nombre_mesa' => $r['nombre_mesa'],
        'camarero' => $camarero,
        'duracion' => $duration,
    ];
}

try {
    $sal_stmt = $pdo->query("SELECT id_sala, nombre_sala FROM salas ORDER BY nombre_sala");
    $salas = $sal_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $salas = [];
}

try {
    if (!empty($f_id_sala)) {
        $mes_stmt = $pdo->prepare("SELECT id_mesa, id_sala, nombre_mesa FROM mesas WHERE id_sala = :id_sala ORDER BY nombre_mesa");
        $mes_stmt->bindValue(':id_sala', (int)$f_id_sala, PDO::PARAM_INT);
        $mes_stmt->execute();
        $mesas = $mes_stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $mes_stmt = $pdo->query("SELECT id_mesa, id_sala, nombre_mesa FROM mesas ORDER BY id_sala, nombre_mesa");
        $mesas = $mes_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $mesas = [];
}

try {
    $usr_stmt = $pdo->query("SELECT id_usuario, usuario, nombre FROM usuarios WHERE rol IN ('camarero','admin') ORDER BY nombre, usuario");
    $camareros = $usr_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $camareros = [];
}

