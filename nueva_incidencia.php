<?php
require_once __DIR__ . '/includes/conexion.php';

$prioridadesValidas = ['alta', 'media', 'baja'];
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titulo        = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS);
    $descripcion   = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_SPECIAL_CHARS);
    $prioridad     = filter_input(INPUT_POST, 'prioridad', FILTER_SANITIZE_SPECIAL_CHARS);
    $email_cliente = filter_input(INPUT_POST, 'email_cliente', FILTER_VALIDATE_EMAIL);

    if (!$titulo || !$descripcion || !$email_cliente) {
        $mensaje = 'Todos los campos son obligatorios y el email debe ser válido.';
    } elseif (!in_array($prioridad, $prioridadesValidas, true)) {
        $mensaje = 'La prioridad recibida no es válida.';
    } else {
        try {
            $sql = 'insert into incidencias (titulo, descripcion, prioridad, estado, email_cliente)
                    values (:titulo, :descripcion, :prioridad, :estado, :email_cliente)';

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':titulo'        => $titulo,
                ':descripcion'   => $descripcion,
                ':prioridad'     => $prioridad,
                ':estado'        => 'pendiente',
                ':email_cliente' => $email_cliente,
            ]);

            $nuevoId = $pdo->lastInsertId();

            header('Location: editar_incidencia.php?id=' . $nuevoId);
            exit;

        } catch (PDOException $e) {
            error_log($e->getMessage());
            $mensaje = 'Error al crear la incidencia.';
        }
    }
}
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

    <?php if ($mensaje !== ''): ?>
        <p><strong><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></strong></p>
    <?php endif; ?>

    <form method="post" action="nueva_incidencia.php">
        <p>
            <label for="titulo">Título:</label><br>
            <input type="text" name="titulo" id="titulo" maxlength="150">
        </p>
        <p>
            <label for="descripcion">Descripción:</label><br>
            <textarea name="descripcion" id="descripcion" rows="5" cols="50"></textarea>
        </p>
        <p>
            <label for="prioridad">Prioridad:</label><br>
            <select name="prioridad" id="prioridad">
                <?php foreach ($prioridadesValidas as $opcion): ?>
                    <option value="<?= $opcion ?>" <?= $opcion === 'media' ? 'selected' : '' ?>>
                        <?= $opcion ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="email_cliente">Email del cliente:</label><br>
            <input type="text" name="email_cliente" id="email_cliente" maxlength="150">
        </p>
        <p>
            <button type="submit">Crear incidencia</button>
        </p>
    </form>

    <p><a href="index.php">Volver al listado</a></p>
</body>
</html>
