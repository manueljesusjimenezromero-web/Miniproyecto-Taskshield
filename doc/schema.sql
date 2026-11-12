drop database if exists taskshield;
create database taskshield character set utf8mb4 collate utf8mb4_unicode_ci;
use taskshield;

create table tecnicos (
    id int unsigned not null auto_increment,
    nombre varchar(100) not null,
    email varchar(150) not null,
    tareas_activas int unsigned not null default 0,
    primary key (id),
    unique key uq_tecnicos_email (email)
) engine = innodb;

create table incidencias (
    id int unsigned not null auto_increment,
    titulo varchar(150) not null,
    descripcion text not null,
    prioridad enum('alta', 'media', 'baja') not null default 'media',
    estado enum('pendiente', 'en proceso', 'resuelta') not null default 'pendiente',
    email_cliente varchar(150) not null,
    tecnico_id int unsigned null default null,
    fecha_creacion datetime not null default current_timestamp,
    primary key (id),
    key idx_incidencias_estado (estado),
    key idx_incidencias_tecnico (tecnico_id),
    constraint fk_incidencias_tecnico
        foreign key (tecnico_id) references tecnicos (id)
        on delete set null
        on update cascade
) engine = innodb;

insert into tecnicos (nombre, email, tareas_activas) values
    ('ana torres', 'ana.torres@empresa.local', 0),
    ('luis moreno', 'luis.moreno@empresa.local', 0),
    ('marta rubio', 'marta.rubio@empresa.local', 0);

insert into incidencias (titulo, descripcion, prioridad, estado, email_cliente, tecnico_id) values
    ('impresora de planta 2 sin red', 'la impresora no aparece en la lista de dispositivos desde el reinicio del switch.', 'alta', 'pendiente', 'recepcion@cliente.local', null),
    ('portatil muy lento al arrancar', 'el equipo tarda mas de cinco minutos en llegar al escritorio.', 'media', 'pendiente', 'jlopez@cliente.local', null),
    ('solicitud de alta de correo', 'dar de alta la cuenta de la nueva incorporacion del departamento de ventas.', 'baja', 'en proceso', 'rrhh@cliente.local', 2),
    ('no se puede acceder al crm', 'error de credenciales al entrar en el crm desde cualquier equipo de la oficina.', 'alta', 'en proceso', 'ventas@cliente.local', 1),
    ('monitor secundario sin senal', 'el segundo monitor dejo de detectarse tras cambiar el cable hdmi.', 'media', 'resuelta', 'soporte@cliente.local', 3);

update tecnicos t set t.tareas_activas = (
    select count(*) from incidencias i where i.tecnico_id = t.id and i.estado <> 'resuelta'
);

create user 'app_user'@'localhost' identified by 'cambia_esta_contrasena';
grant select, insert, update, delete on taskshield.* to 'app_user'@'localhost';
flush privileges;
