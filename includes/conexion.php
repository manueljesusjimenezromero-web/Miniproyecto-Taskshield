<?php

// Traigo las credenciales desde el archivo de configuracion
// Ese archivo esta fuera del repositorio para no publicar la contrasena
require_once __DIR__ . '/../config.php';

// Abro la conexion una sola vez y la reutilizo desde el resto de paginas
try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [
        // Con esto cualquier fallo lanza una excepcion y puedo capturarla abajo
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Sin emulacion, las sentencias preparadas las hace el servidor de verdad
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // El detalle tecnico va al log del servidor
    // Al usuario solo le enseño un mensaje generico, asi no filtro nada
    error_log($e->getMessage());
    die('Error de conexión al servidor.');
}
