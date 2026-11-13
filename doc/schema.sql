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
    ('marta rubio', 'marta.rubio@empresa.local', 0);

create user 'app_user'@'localhost' identified by 'cambia_esta_contrasena';
grant select, insert, update, delete on taskshield.* to 'app_user'@'localhost';
flush privileges;
