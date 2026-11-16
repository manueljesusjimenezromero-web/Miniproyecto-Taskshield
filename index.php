<?php

require_once __DIR__ . '/includes/conexion.php';


// Si llega un POST es que alguien ha tocado el desplegable de tecnicos
// Lo proceso aqui arriba porque luego uso header y no puede haber salida antes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Filtro los dos id para asegurarme de que son numeros
    $idInc  = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $idTec  = filter_input(INPUT_POST, 'tecnico_id', FILTER_VALIDATE_INT) ?: null;
    // Si hay tecnico la incidencia pasa a en proceso, si no vuelve a pendiente
    $estado = $idTec ? 'en proceso' : 'pendiente';

    if (!$idInc) {
        die('Identificador de incidencia no valido.');
    }

    try {
        // Abro la transaccion, hasta el commit no se guarda nada de verdad
        $pdo->beginTransaction();

        // Averiguar que tecnico tenia asignado antes
        $previo = $pdo->prepare('select tecnico_id 
                                from incidencias 
                                where id = :id');
        $previo->execute([':id' => $idInc]);
        $idTecAnterior = $previo->fetchColumn();

        // UPDATE 1: asignar el tecnico y cambiar el estado
        $a = $pdo->prepare('update incidencias
                               set tecnico_id = :tec,
                                   estado = :estado
                             where id = :id');
        $a->execute([':tec' => $idTec,
                     ':estado' => $estado,
                     ':id' => $idInc]);

        // Solo se tocan los contadores si el tecnico ha cambiado
        if ($idTecAnterior != $idTec) {

            // UPDATE 2: restar uno al tecnico que la tenia
            if ($idTecAnterior) {
                $b = $pdo->prepare('update tecnicos
                                       set tareas_activas = tareas_activas - 1
                                     where id = :tec');
                $b->execute([':tec' => $idTecAnterior]);
            }

            // UPDATE 3: sumar uno al tecnico nuevo
            if ($idTec) {
                $c = $pdo->prepare('update tecnicos
                                       set tareas_activas = tareas_activas + 1
                                     where id = :tec');
                $c->execute([':tec' => $idTec]);
            }
        }

        // Si he llegado hasta aqui sin errores, confirmo los cambios
        $pdo->commit();

    } catch (PDOException $e) {
        // Algo ha fallado, asi que deshago todo y dejo la base como estaba
        $pdo->rollBack();
        error_log($e->getMessage());
        die('Error al asignar la incidencia. No se ha guardado ningun cambio.');
    }

    // Recargo la pagina para que al refrescar no se repita la asignacion
    header('Location: index.php');
    exit;
}

// Saco las incidencias junto al nombre del tecnico
// Uso left join porque una incidencia puede no tener tecnico todavia
$sql = 'select i.*, t.nombre as tecnico
          from incidencias i
          left join tecnicos t on t.id = i.tecnico_id
         order by i.id asc';

$sentencia = $pdo->prepare($sql);
$sentencia->execute();
$incidencias = $sentencia->fetchAll(PDO::FETCH_ASSOC);
$total = count($incidencias);

// Y aparte la lista de tecnicos para rellenar el desplegable de cada fila
$tec = $pdo->prepare('select id, nombre from tecnicos order by nombre asc');
$tec->execute();
$tecnicos = $tec->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Incidencias activas</title>
    <link rel="stylesheet" href="css.css">
</head>
<body>
    <h1>Incidencias Activas</h1>

    <p><a href="nueva_incidencia.php">+ Nueva incidencia</a></p>

    <p>Total de incidencias: <strong><?= $total ?></strong></p>

    <?php if ($total === 0): ?>

        <p>No hay incidencias en este momento.</p>

    <?php else: ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titulo</th>
                    <th>Descripcion</th>
                    <th>Prioridad</th>
                    <th>Estado</th>
                    <th>Cliente</th>
                    <th>Tecnico</th>
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
                            <form method="post" action="index.php">
                                <input type="hidden" name="id" value="<?= (int) $inc['id'] ?>">
                                <select name="tecnico_id" onchange="this.form.submit()">
                                    <option value="">sin asignar</option>
                                    <?php foreach ($tecnicos as $tecnico): ?>
                                        <option value="<?= (int) $tecnico['id'] ?>"
                                            <?= $tecnico['id'] == $inc['tecnico_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($tecnico['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td>
                            <a class="editar" href="editar_incidencia.php?id=<?= (int) $inc['id'] ?>">Editar</a>
                            <a class="eliminar" href="eliminar_incidencia.php?id=<?= (int) $inc['id'] ?>">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>

</body>
</html>
