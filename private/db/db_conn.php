<?php
date_default_timezone_set('Europe/Madrid');

$host = 'localhost:3308';
$user = 'root';
$pass = '';
$db = 'db_saiyan_hub';

function connection($host, $user, $pass, $db) {
    try {
        $dsn = "mysql:host=$host;dbname=$db;charset=utf8";
        $conexion = new PDO($dsn, $user, $pass);
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conexion;
    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}
?>

