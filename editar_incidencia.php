<?php
require_once __DIR__ . '/includes/conexion.php';

$prioridadesValidas = ['alta', 'media', 'baja'];
$estadosValidos     = ['pendiente', 'en proceso', 'resuelta'];
$mensaje = '';

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT)
   ?: filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    die('Identificador no válido.');
}

try {
  
    $stmt = $pdo->prepare('select * from incidencias where id = :id');
    $stmt->execute([':id' => $id]);
    $incidencia = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$incidencia) {
        die('No existe ninguna incidencia con ese identificador.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $titulo        = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_SPECIAL_CHARS);
        $descripcion   = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_SPECIAL_CHARS);
        $prioridad     = filter_input(INPUT_POST, 'prioridad', FILTER_SANITIZE_SPECIAL_CHARS);
        $estado        = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_SPECIAL_CHARS);
        $email_cliente = filter_input(INPUT_POST, 'email_cliente', FILTER_VALIDATE_EMAIL);

        if (!$titulo || !$descripcion || !$email_cliente) {
            $mensaje = 'Todos los campos son obligatorios y el email debe ser válido.';
        } elseif (!in_array($prioridad, $prioridadesValidas, true)
               || !in_array($estado, $estadosValidos, true)) {
            $mensaje = 'La prioridad o el estado recibidos no son válidos.';
        } else {
            $sql = 'update incidencias
                       set titulo = :titulo,
                           descripcion = :descripcion,
                           prioridad = :prioridad,
                           estado = :estado,
                           email_cliente = :email_cliente
                     where id = :id';

            $actualizar = $pdo->prepare($sql);
            $actualizar->execute([
                ':titulo'        => $titulo,
                ':descripcion'   => $descripcion,
                ':prioridad'     => $prioridad,
                ':estado'        => $estado,
                ':email_cliente' => $email_cliente,
                ':id'            => $id,
            ]);

            $mensaje = $actualizar->rowCount() > 0
                ? 'Incidencia actualizada correctamente.'
                : 'No se modificó ninguna fila: no has cambiado nada.';

            $incidencia['titulo']        = $titulo;
            $incidencia['descripcion']   = $descripcion;
            $incidencia['prioridad']     = $prioridad;
            $incidencia['estado']        = $estado;
            $incidencia['email_cliente'] = $email_cliente;
        }
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    die('Error al acceder a la incidencia.');
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
    <h1>Editar incidencia #<?= (int) $incidencia['id'] ?></h1>

    <?php if ($mensaje !== ''): ?>
        <p><strong><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></strong></p>
    <?php endif; ?>

    <form method="post" action="editar_incidencia.php">
        <input type="hidden" name="id" value="<?= (int) $incidencia['id'] ?>">

        <p>
            <label for="titulo">Título:</label><br>
            <input type="text" name="titulo" id="titulo" maxlength="150"
                   value="<?= htmlspecialchars($incidencia['titulo'], ENT_QUOTES, 'UTF-8') ?>">
        </p>
        <p>
            <label for="descripcion">Descripción:</label><br>
            <textarea name="descripcion" id="descripcion" rows="5" cols="50"><?= htmlspecialchars($incidencia['descripcion'], ENT_QUOTES, 'UTF-8') ?></textarea>
        </p>
        <p>
            <label for="prioridad">Prioridad:</label><br>
            <select name="prioridad" id="prioridad">
                <?php $prioridadActual = $incidencia['prioridad'] ?: 'media'; ?>
                <?php foreach ($prioridadesValidas as $opcion): ?>
                    <option value="<?= $opcion ?>" <?= $opcion === $prioridadActual ? 'selected' : '' ?>>
                        <?= $opcion ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="email_cliente">Email del cliente:</label><br>
            <input type="text" name="email_cliente" id="email_cliente" maxlength="150"
                   value="<?= htmlspecialchars($incidencia['email_cliente'], ENT_QUOTES, 'UTF-8') ?>">
        </p>
        <p>
            <label for="estado">Estado:</label><br>
            <select name="estado" id="estado">
                <?php foreach ($estadosValidos as $opcion): ?>
                    <option value="<?= $opcion ?>" <?= $opcion === $incidencia['estado'] ? 'selected' : '' ?>>
                        <?= $opcion ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <button type="submit">Guardar cambios</button>
        </p>
    </form>

    <p><a href="index.php">Volver al listado</a></p>
</body>
</html>
