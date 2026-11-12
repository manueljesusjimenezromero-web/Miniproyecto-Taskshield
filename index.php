<?php

require_once __DIR__ . '/includes/conexion.php';

$sql = 'SELECT i.id,
               i.titulo,
               i.descripcion,
               i.prioridad,
               i.estado,
               i.email_cliente,
               t.nombre AS tecnico
          FROM incidencias i
          LEFT JOIN tecnicos t ON t.id = i.tecnico_id
         WHERE i.estado != :estado
         ORDER BY FIELD(i.prioridad, "Alta", "Media", "Baja"), i.id DESC';

$sentencia = $pdo->prepare($sql);
$sentencia->execute([':estado' => 'Resuelta']);
$incidencias = $sentencia->fetchAll(PDO::FETCH_ASSOC);
$total       = count($incidencias);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Incidencias activas</title>
</head>
<body>
    <h1>Incidencias Activas<h1>
    <p><a href="nueva_incidencia.php">+ Nueva incidencia</a><p>
    <p>Total de incidencias activas: <strong><?= $total ?></strong></p>
    
    <?php if ($total === 0): ?>

        <p>No hay incidencias activas en este momento.</p>

    <?php else: ?>

        <table border="1" cellpadding="6" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Descripción</th>
                    <th>Prioridad</th>
                    <th>Estado</th>
                    <th>Cliente</th>
                    <th>Técnico</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($incidencias as $inc): ?>
                    <tr>
                        <td><?= (int) $inc['id'] ?></td>

                        <td><?= htmlspecialchars($inc['titulo'], ENT_QUOTES, 'UTF-8') ?></td>

                        <td><?= htmlspecialchars($inc['descripcion'], ENT_QUOTES, 'UTF-8') ?></td>

                        <td><?= htmlspecialchars($inc['prioridad'], ENT_QUOTES, 'UTF-8') ?></td>

                        <td><?= htmlspecialchars($inc['estado'], ENT_QUOTES, 'UTF-8') ?></td>

                        <td><?= htmlspecialchars($inc['email_cliente'], ENT_QUOTES, 'UTF-8') ?></td>

                        <td>
                            <?= htmlspecialchars($inc['tecnico'] ?? 'Sin asignar', ENT_QUOTES, 'UTF-8') ?>
                        </td>

                        <td>
                            <a href="editar_incidencia.php?id=<?= (int) $inc['id'] ?>">Editar</a>
                            |
                            <a href="asignar_incidencia.php?id=<?= (int) $inc['id'] ?>">Asignar</a>
                            |
                            <a href="eliminar_incidencia.php?id=<?= (int) $inc['id'] ?>">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>

</body>
</html>
</body>
<html>