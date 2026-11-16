# Casos de prueba ejecutados

| # | Caso | Como se prueba | Resultado esperado | Resultado obtenido |
|---|------|----------------|--------------------|--------------------|
| 1 | Email invalido en el alta | En `nueva_incidencia.php`, escribir `juan@@correo` en el campo Email del cliente y enviar | `FILTER_VALIDATE_EMAIL` devuelve `false`, no se ejecuta el INSERT y se muestra un aviso | Correcto: aparece "Todos los campos son obligatorios y deben ser validos" y la tabla no cambia |
| 2 | Editar un id inexistente | Abrir `editar_incidencia.php?id=9999` | El SELECT previo devuelve `false` y se corta con un mensaje controlado, sin error de PHP | Correcto: "No existe ninguna incidencia con ese identificador" |
| 3 | Fallo en la segunda parte de la transaccion | Asignar un tecnico cuyo id no existe en la tabla `tecnicos` | La clave ajena rechaza el UPDATE, salta el `rollBack()` y no se guarda ninguno de los dos cambios | Correcto: la incidencia sigue en "pendiente" y `tareas_activas` no se incrementa |

## Detalle del caso 3

Es el que justifica el uso de transacciones. La asignacion hace dos escrituras:

1. `UPDATE incidencias SET tecnico_id = ?, estado = 'en proceso' WHERE id = ?`
2. `UPDATE tecnicos SET tareas_activas = tareas_activas + 1 WHERE id = ?`

Sin transaccion, si la segunda fallara la incidencia quedaria asignada a un tecnico
cuyo contador no refleja esa carga de trabajo: datos incoherentes.

Para forzar el fallo se puede modificar temporalmente el formulario y enviar un
`tecnico_id` que no exista, por ejemplo 999. La clave ajena de `incidencias` lanza
una `PDOException` en el primer UPDATE, el `catch` ejecuta `rollBack()` y al
consultar la tabla se comprueba que no se guardo nada:

```sql
select id, estado, tecnico_id from incidencias where id = 1;
select id, tareas_activas from tecnicos;
```

## Nota sobre rowCount()

En el caso 5 conviene tener en cuenta que `rowCount()` devuelve 0 tambien cuando
un UPDATE no cambia realmente ningun valor (por ejemplo, guardar el mismo estado
que ya tenia la incidencia). Por eso en `editar_incidencia.php` el mensaje de 0
filas no se trata como un error, sino como "no se ha modificado nada".
