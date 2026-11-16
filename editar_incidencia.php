<?php
require_once __DIR__ . '/includes/conexion.php';

$titulo        = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS);
$descripcion   = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_SPECIAL_CHARS);
$prioridad     = filter_input(INPUT_POST, 'prioridad', FILTER_SANITIZE_SPECIAL_CHARS);
$email_cliente = filter_input(INPUT_POST, 'email_cliente', FILTER_VALIDATE_EMAIL);
$estado        = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_SPECIAL_CHARS);
$id            = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    die('Identificador no válido.');
}

try {
    $comprobar = $pdo->prepare('select id from incidencias where id = :id');
    $comprobar->execute([':id' => $id]);
    if (!$comprobar->fetch()) {
        die('No existe ninguna incidencia con ese id.');
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    die('Error al acceder a la incidencia.');
}

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$titulo || !$descripcion || !$prioridad || !$email_cliente || !$estado) {
        $mensaje = 'Todos los campos son obligatorios y deben ser válidos.';
    } else {
        try {
            $sql = 'UPDATE incidencias
                       SET titulo = :titulo,
                           descripcion = :descripcion,
                           prioridad = :prioridad,
                           estado = :estado,
                           email_cliente = :email_cliente
                     WHERE id = :id';

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':titulo'        => $titulo,
                ':descripcion'   => $descripcion,
                ':prioridad'     => $prioridad,
                ':estado'        => $estado,
                ':email_cliente' => $email_cliente,
                ':id'            => $id
            ]);

            $mensaje = $stmt->rowCount() > 0
                ? 'Incidencia actualizada correctamente.'
                : 'No se modificó ninguna fila.';
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $mensaje = 'Error al editar la incidencia.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Editar incidencia</title>
    <link rel="stylesheet" href="css.css">
</head>
<body>
    <h1>Editar incidencia</h1>

    <?php if ($mensaje !== ''): ?>
        <p><strong><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></strong></p>
    <?php endif; ?>

    <form method="post" action="editar_incidencia.php?id=<?= (int) $id ?>">
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
            <label for="estado">Estado:</label><br>
            <select name="estado" id="estado">
                <option value="pendiente">Pendiente</option>
                <option value="en proceso">En proceso</option>
                <option value="resuelta">Resuelta</option>
            </select>
        </p>
        <p>
            <button type="submit">Editar incidencia</button>
        </p>
    </form>

    <p><a href="index.php">Volver al listado</a></p>
</body>
</html>