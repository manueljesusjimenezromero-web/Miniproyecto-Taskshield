# Taskshield

Intranet de gestion de incidencias para un departamento de soporte informatico.
Permite registrar averias, consultarlas, editarlas, asignarlas a un tecnico y eliminarlas.

## Metodologia de trabajo

Desarrollado en pareja mediante *pair programming*, alternando los roles de driver
y navigator a lo largo de la practica. El driver se ocupa de la implementacion
inmediata; el navigator revisa en paralelo, vigila la coherencia del conjunto y
anticipa el siguiente paso.

El reparto rotativo ha permitido que ambos integrantes conozcan la totalidad del
codigo entregado, y ha adelantado la deteccion de varios errores al momento de
escribirlos: rutas de `require` mal resueltas, literales que no coincidian con los
`enum` definidos en el esquema y consultas de escritura sin clausula `where`.

**Integrantes:** Manuel Jesus Jimenez Romero y (nombre del companero)

## Arquitectura

```
Miniproyecto-Taskshield/
├── config.php               credenciales de la base de datos (NO se sube al repo)
├── includes/
│   └── conexion.php         instancia de PDO con excepciones activadas
├── index.php                listado de incidencias y asignacion de tecnico
├── nueva_incidencia.php     formulario de alta (INSERT)
├── editar_incidencia.php    formulario de modificacion (UPDATE)
├── eliminar_incidencia.php  borrado con confirmacion (DELETE)
├── css.css                  hoja de estilos
└── doc/
    ├── schema.sql           estructura de tablas e inserciones de prueba
    ├── README.md            este archivo
    └── CasoDePrueba.md      casos de prueba ejecutados
```

### Capa de acceso a datos

`config.php` centraliza las credenciales y esta excluido del repositorio mediante
`.gitignore`. `includes/conexion.php` lo carga y crea el objeto PDO con:

- `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`, para que cualquier fallo lance
  una excepcion y pueda capturarse con try/catch.
- `PDO::ATTR_EMULATE_PREPARES => false`, para usar sentencias preparadas reales
  del servidor y no una emulacion de PHP.
- `PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC`.

Todas las consultas usan `prepare()` y `execute()` con marcadores con nombre, de
modo que los datos del usuario nunca se concatenan a la sentencia SQL. Los errores
tecnicos se registran con `error_log()` y al usuario solo se le muestra un mensaje
generico.

### Base de datos

Dos tablas en InnoDB:

- `tecnicos` (id, nombre, email, tareas_activas)
- `incidencias` (id, titulo, descripcion, prioridad, estado, email_cliente, tecnico_id)

`prioridad` y `estado` son `enum`, asi que el propio gestor rechaza un valor fuera
de la lista. `incidencias.tecnico_id` es clave ajena a `tecnicos.id`. El motor
InnoDB es obligatorio porque es el unico que soporta transacciones.

### Transaccion

La asignacion de un tecnico se procesa en `index.php` dentro de un bloque
`beginTransaction()` / `commit()` / `rollBack()`, porque implica dos escrituras
interdependientes: cambiar el estado y el tecnico de la incidencia, e incrementar
el contador `tareas_activas` del tecnico. Si una de las dos falla se deshacen
ambas, de modo que nunca queda el contador descuadrado respecto a las incidencias.

## Instalacion

1. Copiar el proyecto en `htdocs` de XAMPP y arrancar Apache y MySQL.
2. Importar `doc/schema.sql` desde phpMyAdmin (pestana Importar).
3. Antes de importarlo, sustituir `CAMBIA_ESTA_CONTRASENA` por la contrasena que
   se vaya a usar para el usuario `app_user`.
4. Crear `config.php` en la raiz del proyecto con esa misma contrasena:

```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'taskshield');
define('DB_USER', 'app_user');
define('DB_PASS', 'la_misma_del_paso_3');
define('DB_CHARSET', 'utf8mb4');
define('DB_DSN', 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET);
```

5. Abrir `http://localhost/DWS1/Miniproyecto-Taskshield/index.php`.

Si aparece "Error de conexion al servidor", el motivo real esta en el log de
Apache (`xampp/apache/logs/error.log`). El error 1045 significa que la contrasena
del paso 3 y la del paso 4 no coinciden.

## Como probarlo

- **Listado**: la pagina principal muestra todas las incidencias con su tecnico.
- **Alta**: "+ Nueva incidencia". Probar con un email mal formado para ver el filtro.
- **Edicion**: boton Editar. Probar tambien con un id inexistente en la URL.
- **Borrado**: boton Eliminar. Pide confirmacion antes de borrar.
- **Asignacion**: desplegable de la columna Tecnico. Al elegir un tecnico la
  incidencia pasa a "en proceso" y sube el contador del tecnico; al dejarla en
  "sin asignar" vuelve a "pendiente".

Los casos de prueba ejecutados estan documentados en `CasoDePrueba.md`.
