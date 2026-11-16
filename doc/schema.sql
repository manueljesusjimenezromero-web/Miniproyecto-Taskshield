drop database if exists taskshield;
create database taskshield character set utf8mb4 collate utf8mb4_unicode_ci;
use taskshield;


create table tecnicos (
    id int unsigned auto_increment primary key,
    nombre varchar(100) not null,
    email varchar(150) not null unique,
    tareas_activas int unsigned not null default 0
) engine = innodb;



create table incidencias (
    id int unsigned auto_increment primary key,
    titulo varchar(150) not null,
    descripcion text not null,
    prioridad enum('alta', 'media', 'baja') not null default 'media',
    estado enum('pendiente', 'en proceso', 'resuelta') not null default 'pendiente',
    email_cliente varchar(150) not null,
    tecnico_id int unsigned null,
    foreign key (tecnico_id) references tecnicos (id)
) engine = innodb;

insert into tecnicos (nombre, email, tareas_activas) values
    ('ana torres', 'ana.torres@empresa.local', 0),
    ('luis moreno', 'luis.moreno@empresa.local', 0),
    ('marta rubio', 'marta.rubio@empresa.local', 0),
    ('morton rubio', 'morton.rubio@empresa.local', 0);

insert into incidencias (titulo, descripcion, prioridad, estado, email_cliente, tecnico_id) values
    ('impresora de planta 2 sin red', 'la impresora no aparece entre los dispositivos de red.', 'alta', 'pendiente', 'recepcion@cliente.local', null),
    ('portatil lento al arrancar', 'el equipo tarda mas de cinco minutos en llegar al escritorio.', 'media', 'pendiente', 'jlopez@cliente.local', null),
    ('alta de cuenta de correo', 'crear la cuenta de la nueva incorporacion de ventas.', 'baja', 'en proceso', 'rrhh@cliente.local', 2),
    ('monitor secundario sin senal', 'el segundo monitor dejo de detectarse.', 'media', 'resuelta', 'soporte@cliente.local', 3);

update tecnicos t set t.tareas_activas = (
    select count(*) from incidencias i where i.tecnico_id = t.id and i.estado <> 'resuelta'
);


