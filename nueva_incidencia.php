<?php
require_once __DIR__ . '/includes/conexion.php';
$titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_STRING);
$descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
$prioridad = filter_input(INPUT_POST, 'prioridad', FILTER_SANITIZE_STRING);
$email_cliente = filter_input(INPUT_POST, 'email_cliente', FILTER_VALIDATE_EMAIL);
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Nueva incidencia</title>
    <link rel="stylesheet" href="css.css">
</head>
<body>
    <h1>Nueva incidencia</h1>
    <form method="post" action="nueva_incidencia.php">
        <p>
            <label for="titulo">Título:</label><br>
            <input type="text" name="titulo" id="titulo" maxlength="150" required>
        </p>
        <p>
            <label for="descripcion">Descripción:</label><br>
            <textarea name="descripcion" id="descripcion" rows="5" cols="50" required></textarea>
        </p>
        <p>
            <label for="prioridad">Prioridad:</label><br>
            <select name="prioridad" id="prioridad">
                <option value="alta">alta</option>
                <option value="media" selected>media</option>
                <option value="baja">baja</option>
            </select>
        </p>
        <p>
            <label for="email_cliente">Email del cliente:</label><br>
            <input type="email" name="email_cliente" id="email_cliente" maxlength="150" required>
        </p>
        <p>
            <button type="submit">Crear incidencia</button>
        </p>
    </form>
    <p><a href="index.php">Volver al listado</a></p>
    <?php
    if(!$titulo || !$descripcion || !$prioridad || !$email_cliente) {
    die('Todos los campos son obligatorios y deben ser válidos.');
}
try{

$sql='INSERT INTO incidencias ( titulo, descripcion, prioridad, estado, email_cliente) 
VALUES (:titulo, :descripcion, :prioridad, :estado, :email_cliente)';



$smtp = $pdo->prepare($sql);
$smtp->execute([
    ':titulo' => $titulo,
    ':descripcion' => $descripcion,
    ':prioridad' => $prioridad,
    ':estado' => 'Pendiente',
    ':email_cliente' => $email_cliente
]);

$nuevoId = $pdo->lastInsertId();
       
       

}catch(PDOException $e){
    error_log($e->getMessage());
    die('Error al crear la incidencia.');
}
    ?>
</body>
</html>