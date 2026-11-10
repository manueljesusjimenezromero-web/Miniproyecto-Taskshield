<?php
// archivo: includes/conexion.php
    require_once __DIR__ . '/../config/config.php';
    try {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        die('Error de conexión al servidor.');
    }
?>