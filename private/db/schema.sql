CREATE DATABASE IF NOT EXISTS db_saiyan_hub;
USE db_saiyan_hub;

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellidos VARCHAR(100),
    usuario VARCHAR(50) UNIQUE NOT NULL,
    contrasena VARCHAR(255) NOT NULL,  
    rol ENUM('camarero', 'admin') DEFAULT 'camarero'
);

CREATE TABLE salas (
    id_sala INT AUTO_INCREMENT PRIMARY KEY,
    nombre_sala VARCHAR(50) NOT NULL,
    tipo ENUM('terraza', 'comedor', 'privada') NOT NULL,
    capacidad_total INT NOT NULL
);

CREATE TABLE mesas (
    id_mesa INT AUTO_INCREMENT PRIMARY KEY,
    id_sala INT NOT NULL,
    nombre_mesa VARCHAR(20) NOT NULL,
    num_sillas INT NOT NULL,
    estado ENUM('libre', 'ocupada') DEFAULT 'libre',
    FOREIGN KEY (id_sala) REFERENCES salas(id_sala)
);

CREATE TABLE sillas (
    id_silla INT AUTO_INCREMENT PRIMARY KEY,
    id_mesa INT NOT NULL,
    numero_silla INT NOT NULL,
    estado ENUM('libre', 'ocupada') DEFAULT 'libre',
    FOREIGN KEY (id_mesa) REFERENCES mesas(id_mesa)
);

CREATE TABLE ocupaciones (  
    id_ocupacion INT AUTO_INCREMENT PRIMARY KEY,
    id_mesa INT NOT NULL,
    id_usuario INT NOT NULL,
    fecha_ocupacion DATETIME NOT NULL,
    fecha_liberacion DATETIME,
    FOREIGN KEY (id_mesa) REFERENCES mesas(id_mesa),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

-- Las contraseñas en claro originales eran: adminpass, juanpass, anapass, luispass, mariapass, carlospass
INSERT INTO usuarios (nombre, apellidos, usuario, contrasena, rol) VALUES
('Admin','Sistema','admin','$2y$10$9p1Hr8/yzaG5HR051ZQDX.FORM750axFXkPt5eGcYeQZRuZYjfEiS','admin'),
('Juan','Pérez','juan','$2y$10$Yu6Nx.Q2RsBU7G.lfqKo6ebWmJ73YHvmewQV6xcEpAbjmpKM2TYi2','camarero'),
('Ana','López','ana','$2y$10$KxWT418OpJQ2QtB3vVzCu.IJ/vt5ONZ5.hs0NMsN/23i75YWCP3HC','camarero'),
('Luis','García','luis','$2y$10$OxEhWFKqvuQVoZGD7yZeY.63YOCa.OWZwSnn0kyzpa.z/p/rxNh1m','camarero'),
('María','Santos','maria','$2y$10$6nH41f65nyGldf9pdJIqme0vDW87nhJuTosmng3u0IZ9pMqgeMF2S','camarero'),
('Carlos','Ruiz','carlos','$2y$10$Thc3K1GZEXqk9wVHANVRc.aXkMLn45uLy9YcmFSw.RFv3FI4p9knW','camarero');

-- SALAS
INSERT INTO salas (nombre_sala, tipo, capacidad_total) VALUES
('Terraza 1','terraza',30),
('Terraza 2','terraza',20),
('Terraza 3','terraza',15),
('Comedor 1','comedor',40),
('Comedor 2','comedor',35),
('Privada 1','privada',10),
('Privada 2','privada',10),
('Privada 3','privada',8),
('Privada 4','privada',12);

INSERT INTO mesas (id_sala, nombre_mesa, num_sillas, estado) VALUES
-- Terraza 1 (id_sala = 1) — Saiyans
(1,'Goku',4,'libre'),
(1,'Vegeta',2,'libre'),
(1,'Gohan',6,'ocupada'),
(1,'Trunks',4,'libre'),
(1,'Goten',8,'libre'),
-- Terraza 2 (id_sala = 2) — Namekians
(2,'Piccolo',4,'libre'),
(2,'Dende',4,'libre'),
(2,'Nail',2,'libre'),
(2,'Saonel',6,'libre'),
-- Terraza 3 (id_sala = 3) — Frieza Race
(3,'Frieza',2,'libre'),
(3,'Cooler',4,'libre'),
(3,'KingCold',6,'ocupada'),
-- Comedor 1 (id_sala = 4) — Humanos
(4,'Krillin',6,'libre'),
(4,'Yamcha',6,'libre'),
(4,'Tien',8,'libre'),
(4,'Chiaotzu',4,'libre'),
(4,'MrSatan',10,'libre'),
(4,'MasterRoshi',12,'libre'),
-- Comedor 2 (id_sala = 5) — Androides
(5,'Android16',6,'libre'),
(5,'Android17',8,'libre'),
(5,'Android18',4,'libre'),
(5,'Android19',2,'libre'),
(5,'Cell',6,'libre'),
-- Privada 1 (id_sala = 6) — Majin
(6,'MajinBuu',8,'libre'),
(6,'Dabura',6,'libre'),
-- Privada 2 (id_sala = 7) — Deidades
(7,'Beerus',6,'libre'),
(7,'Whis',4,'libre'),
-- Privada 3 (id_sala = 8) — Dragones
(8,'Shenron',10,'libre'),
-- Privada 4 (id_sala = 9) — Otros
(9,'Jiren',8,'libre');

INSERT INTO sillas (id_mesa, numero_silla, estado) VALUES
-- Terraza 1 (mesas id 1..5)
(1,1,'libre'),(1,2,'libre'),(1,3,'libre'),(1,4,'libre'),
(2,1,'libre'),(2,2,'libre'),
(3,1,'ocupada'),(3,2,'ocupada'),(3,3,'ocupada'),(3,4,'ocupada'),(3,5,'ocupada'),(3,6,'ocupada'),
(4,1,'libre'),(4,2,'libre'),(4,3,'libre'),(4,4,'libre'),
(5,1,'libre'),(5,2,'libre'),(5,3,'libre'),(5,4,'libre'),(5,5,'libre'),(5,6,'libre'),(5,7,'libre'),(5,8,'libre'),

-- Terraza 2 (mesas id 6..9)
(6,1,'libre'),(6,2,'libre'),(6,3,'libre'),(6,4,'libre'),
(7,1,'libre'),(7,2,'libre'),(7,3,'libre'),(7,4,'libre'),
(8,1,'libre'),(8,2,'libre'),
(9,1,'libre'),(9,2,'libre'),(9,3,'libre'),(9,4,'libre'),(9,5,'libre'),(9,6,'libre'),

-- Terraza 3 (mesas id 10..12)
(10,1,'libre'),(10,2,'libre'),
(11,1,'libre'),(11,2,'libre'),(11,3,'libre'),(11,4,'libre'),
(12,1,'ocupada'),(12,2,'ocupada'),(12,3,'ocupada'),(12,4,'ocupada'),(12,5,'ocupada'),(12,6,'ocupada'),

-- Comedor 1 (mesas id 13..18)
(13,1,'libre'),(13,2,'libre'),(13,3,'libre'),(13,4,'libre'),(13,5,'libre'),(13,6,'libre'),
(14,1,'libre'),(14,2,'libre'),(14,3,'libre'),(14,4,'libre'),(14,5,'libre'),(14,6,'libre'),
(15,1,'libre'),(15,2,'libre'),(15,3,'libre'),(15,4,'libre'),(15,5,'libre'),(15,6,'libre'),(15,7,'libre'),(15,8,'libre'),
(16,1,'libre'),(16,2,'libre'),(16,3,'libre'),(16,4,'libre'),
(17,1,'libre'),(17,2,'libre'),(17,3,'libre'),(17,4,'libre'),(17,5,'libre'),(17,6,'libre'),(17,7,'libre'),(17,8,'libre'),(17,9,'libre'),(17,10,'libre'),
(18,1,'libre'),(18,2,'libre'),(18,3,'libre'),(18,4,'libre'),(18,5,'libre'),(18,6,'libre'),(18,7,'libre'),(18,8,'libre'),(18,9,'libre'),(18,10,'libre'),(18,11,'libre'),(18,12,'libre'),

-- Comedor 2 (mesas id 19..23)
(19,1,'libre'),(19,2,'libre'),(19,3,'libre'),(19,4,'libre'),(19,5,'libre'),(19,6,'libre'),
(20,1,'libre'),(20,2,'libre'),(20,3,'libre'),(20,4,'libre'),(20,5,'libre'),(20,6,'libre'),(20,7,'libre'),(20,8,'libre'),
(21,1,'libre'),(21,2,'libre'),(21,3,'libre'),(21,4,'libre'),
(22,1,'libre'),(22,2,'libre'),
(23,1,'libre'),(23,2,'libre'),(23,3,'libre'),(23,4,'libre'),(23,5,'libre'),(23,6,'libre'),

-- Privada 1 (mesas id 24..25)
(24,1,'libre'),(24,2,'libre'),(24,3,'libre'),(24,4,'libre'),(24,5,'libre'),(24,6,'libre'),(24,7,'libre'),(24,8,'libre'),
(25,1,'libre'),(25,2,'libre'),(25,3,'libre'),(25,4,'libre'),(25,5,'libre'),(25,6,'libre'),

-- Privada 2 (mesas id 26..27)
(26,1,'libre'),(26,2,'libre'),(26,3,'libre'),(26,4,'libre'),(26,5,'libre'),(26,6,'libre'),
(27,1,'libre'),(27,2,'libre'),(27,3,'libre'),(27,4,'libre'),

-- Privada 3 (mesa id 28)
(28,1,'libre'),(28,2,'libre'),(28,3,'libre'),(28,4,'libre'),(28,5,'libre'),(28,6,'libre'),(28,7,'libre'),(28,8,'libre'),(28,9,'libre'),(28,10,'libre'),

-- Privada 4 (mesa id 29)
(29,1,'libre'),(29,2,'libre'),(29,3,'libre'),(29,4,'libre'),(29,5,'libre'),(29,6,'libre'),(29,7,'libre'),(29,8,'libre');

INSERT INTO ocupaciones (id_mesa, id_usuario, fecha_ocupacion, fecha_liberacion) VALUES
(3,2,'2025-11-06 13:15:00', NULL),
(12,4,'2025-11-05 20:00:00','2025-11-05 21:30:00'),
(18,3,'2025-11-05 13:00:00','2025-11-05 15:10:00'),
(21,5,'2025-11-04 19:30:00','2025-11-04 21:00:00'),
(7,2,'2025-11-03 12:45:00','2025-11-03 13:50:00'),
(15,6,'2025-11-03 14:00:00','2025-11-03 15:45:00'),
(1,2,'2025-11-02 20:00:00','2025-11-02 21:30:00'),
(5,4,'2025-11-02 18:30:00','2025-11-02 19:10:00'),
(29,1,'2025-11-01 21:00:00','2025-11-01 23:00:00'),
(24,5,'2025-11-01 20:00:00','2025-11-01 22:15:00'),
(13,2,'2025-10-31 13:00:00','2025-10-31 14:20:00'),
(9,3,'2025-10-30 19:00:00','2025-10-30 20:10:00'),
(20,4,'2025-10-30 12:30:00','2025-10-30 13:45:00'),
(2,6,'2025-10-29 20:15:00','2025-10-29 21:50:00'),
(16,5,'2025-10-28 14:00:00','2025-10-28 15:10:00'),
(8,3,'2025-10-27 19:30:00','2025-10-27 20:30:00'),
(25,2,'2025-10-26 18:00:00','2025-10-26 19:30:00'),
(28,4,'2025-10-25 12:00:00','2025-10-25 14:30:00'),
(17,6,'2025-10-24 20:00:00','2025-10-24 21:00:00'),
(6,5,'2025-10-23 13:15:00','2025-10-23 14:00:00'),
(14,2,'2025-10-22 13:00:00','2025-10-22 14:10:00'),
(11,3,'2025-10-21 19:30:00','2025-10-21 20:20:00'),
(4,4,'2025-10-20 12:30:00','2025-10-20 13:00:00'),
(10,5,'2025-10-19 21:00:00','2025-10-19 22:30:00'),
(22,2,'2025-10-18 18:00:00','2025-10-18 19:00:00'),
(23,3,'2025-10-17 14:00:00','2025-10-17 15:20:00'),
(19,4,'2025-10-16 20:15:00','2025-10-16 21:20:00'),
(26,5,'2025-10-15 12:45:00','2025-10-15 13:30:00'),
(27,6,'2025-10-14 19:00:00','2025-10-14 20:45:00'),
(1,3,'2025-10-13 13:30:00','2025-10-13 14:10:00'),
(2,4,'2025-10-12 20:00:00','2025-10-12 21:00:00'),
(3,5,'2025-10-11 18:30:00','2025-10-11 19:20:00'),
(12,2,'2025-10-10 13:00:00','2025-10-10 14:00:00'),
(21,6,'2025-10-09 21:00:00','2025-10-09 22:00:00'),
(24,3,'2025-10-08 12:00:00','2025-10-08 13:30:00');
