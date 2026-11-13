<?php
require_once __DIR__ . '/includes/conexion.php';

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT)
   ?: filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    die('Identificador no válido.');
}

$mensaje = '';

try {
    $stmt = $pdo->prepare('select id, titulo from incidencias where id = :id');
    $stmt->execute([':id' => $id]);
    $incidencia = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$incidencia) {
        die('No existe ninguna incidencia con ese identificador.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $borrar = $pdo->prepare('delete from incidencias where id = :id');
        $borrar->execute([':id' => $id]);

        $mensaje = $borrar->rowCount() > 0
            ? 'Incidencia eliminada correctamente.'
            : 'No se eliminó ninguna fila.';
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    die('Error al eliminar la incidencia.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Eliminar incidencia</title>
    <link rel="stylesheet" href="css.css">
</head>
<body>
    <h1>Eliminar incidencia</h1>

    <?php if ($mensaje !== ''): ?>

        <p><strong><?= $mensaje ?></strong></p>

    <?php else: ?>

        <p>¿Seguro que quieres eliminar la incidencia
           <strong><?= htmlspecialchars($incidencia['titulo'], ENT_QUOTES, 'UTF-8') ?></strong>?</p>

        <form method="post" action="eliminar_incidencia.php">
            <input type="hidden" name="id" value="<?= (int) $incidencia['id'] ?>">
            <button type="submit">Sí, eliminar</button>
        </form>

    <?php endif; ?>

    <p><a href="index.php">Volver al listado</a></p>
</body>
</html>