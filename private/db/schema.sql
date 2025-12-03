CREATE DATABASE IF NOT EXISTS db_saiyan_hub;
USE db_saiyan_hub;

-- --------------------------------------------------------
-- FASE 1 TABLES
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellidos VARCHAR(100),
    usuario VARCHAR(50) UNIQUE NOT NULL,
    contrasena VARCHAR(255) NOT NULL,  
    rol ENUM('camarero', 'admin', 'gerent', 'manteniment') DEFAULT 'camarero'
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS salas (
    id_sala INT AUTO_INCREMENT PRIMARY KEY,
    nombre_sala VARCHAR(50) NOT NULL,
    tipo ENUM('terraza', 'comedor', 'privada') NOT NULL,
    capacidad_total INT NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS mesas (
    id_mesa INT AUTO_INCREMENT PRIMARY KEY,
    id_sala INT NOT NULL,
    nombre_mesa VARCHAR(20) NOT NULL,
    num_sillas INT NOT NULL,
    estado ENUM('libre', 'ocupada') DEFAULT 'libre',
    FOREIGN KEY (id_sala) REFERENCES salas(id_sala)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS sillas (
    id_silla INT AUTO_INCREMENT PRIMARY KEY,
    id_mesa INT NOT NULL,
    numero_silla INT NOT NULL,
    estado ENUM('libre', 'ocupada') DEFAULT 'libre',
    FOREIGN KEY (id_mesa) REFERENCES mesas(id_mesa)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ocupaciones (  
    id_ocupacion INT AUTO_INCREMENT PRIMARY KEY,
    id_mesa INT NOT NULL,
    id_usuario INT NOT NULL,
    fecha_ocupacion DATETIME NOT NULL,
    fecha_liberacion DATETIME,
    FOREIGN KEY (id_mesa) REFERENCES mesas(id_mesa),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- FASE 2 NEW TABLES
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS recursos (
    id_recurso INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('sala', 'mesa', 'silla', 'otro') NOT NULL,
    capacidad INT DEFAULT 0,
    imagen VARCHAR(255),
    estado ENUM('disponible', 'mantenimiento', 'baja') DEFAULT 'disponible'
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reservas (
    id_reserva INT AUTO_INCREMENT PRIMARY KEY,
    id_recurso INT NOT NULL,
    id_usuario INT NOT NULL,
    fecha DATE NOT NULL,
    franja_horaria VARCHAR(50) NOT NULL, -- Ej: '13:00-14:00'
    estado ENUM('confirmada', 'cancelada', 'pendiente') DEFAULT 'confirmada',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_recurso) REFERENCES recursos(id_recurso),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- SEED DATA
-- --------------------------------------------------------

-- Usuarios
INSERT IGNORE INTO usuarios (nombre, apellidos, usuario, contrasena, rol) VALUES
('Admin','Sistema','admin','$2y$10$9p1Hr8/yzaG5HR051ZQDX.FORM750axFXkPt5eGcYeQZRuZYjfEiS','admin'),
('Juan','Pérez','juan','$2y$10$Yu6Nx.Q2RsBU7G.lfqKo6ebWmJ73YHvmewQV6xcEpAbjmpKM2TYi2','camarero'),
('Ana','López','ana','$2y$10$KxWT418OpJQ2QtB3vVzCu.IJ/vt5ONZ5.hs0NMsN/23i75YWCP3HC','camarero'),
('Luis','García','luis','$2y$10$OxEhWFKqvuQVoZGD7yZeY.63YOCa.OWZwSnn0kyzpa.z/p/rxNh1m','camarero'),
('María','Santos','maria','$2y$10$6nH41f65nyGldf9pdJIqme0vDW87nhJuTosmng3u0IZ9pMqgeMF2S','camarero'),
('Carlos','Ruiz','carlos','$2y$10$Thc3K1GZEXqk9wVHANVRc.aXkMLn45uLy9YcmFSw.RFv3FI4p9knW','camarero'),
('Gerente','General','gerente','$2y$10$9p1Hr8/yzaG5HR051ZQDX.FORM750axFXkPt5eGcYeQZRuZYjfEiS','gerent'),
('Mantenimiento','Staff','mante','$2y$10$9p1Hr8/yzaG5HR051ZQDX.FORM750axFXkPt5eGcYeQZRuZYjfEiS','manteniment');

-- Salas (Fase 1)
INSERT IGNORE INTO salas (nombre_sala, tipo, capacidad_total) VALUES
('Terraza 1','terraza',30),
('Terraza 2','terraza',20),
('Terraza 3','terraza',15),
('Comedor 1','comedor',40),
('Comedor 2','comedor',35),
('Privada 1','privada',10),
('Privada 2','privada',10),
('Privada 3','privada',8),
('Privada 4','privada',12);

-- Recursos (Fase 2)
INSERT IGNORE INTO recursos (nombre, tipo, capacidad, estado, imagen) VALUES
('Sala Terraza 1', 'sala', 30, 'disponible', 'img/terraza1.jpg'),
('Sala Terraza 2', 'sala', 20, 'disponible', 'img/terraza2.jpg'),
('Sala Comedor Principal', 'sala', 50, 'disponible', 'img/comedor1.jpg'),
('Sala Comedor Secundario', 'sala', 30, 'mantenimiento', 'img/comedor2.jpg'),
('Sala Privada VIP', 'sala', 10, 'disponible', 'img/vip1.jpg'),
('Sala Reuniones', 'sala', 12, 'disponible', 'img/reuniones.jpg'),
('Mesa T1-01', 'mesa', 4, 'disponible', 'img/mesa_std.jpg'),
('Mesa T1-02', 'mesa', 4, 'disponible', 'img/mesa_std.jpg'),
('Mesa T1-03', 'mesa', 2, 'disponible', 'img/mesa_small.jpg'),
('Mesa C1-01', 'mesa', 6, 'disponible', 'img/mesa_large.jpg'),
('Mesa C1-02', 'mesa', 6, 'disponible', 'img/mesa_large.jpg'),
('Mesa VIP-01', 'mesa', 8, 'disponible', 'img/mesa_vip.jpg'),
('Mesa VIP-02', 'mesa', 8, 'mantenimiento', 'img/mesa_vip.jpg'),
('Proyector 4K', 'otro', 0, 'disponible', 'img/proyector.jpg'),
('Equipo de Sonido', 'otro', 0, 'disponible', 'img/sonido.jpg'),
('Pizarra Digital', 'otro', 0, 'baja', 'img/pizarra.jpg');

-- Reservas (Seed Data)
INSERT IGNORE INTO reservas (id_recurso, id_usuario, fecha, franja_horaria, estado) VALUES
(1, 2, '2025-11-01', '13:00-14:00', 'confirmada'),
(1, 2, '2025-11-01', '14:00-15:00', 'confirmada'),
(3, 3, '2025-11-02', '20:00-21:00', 'confirmada'),
(5, 4, '2025-11-02', '21:00-22:00', 'cancelada'),
(1, 2, CURDATE() + INTERVAL 1 DAY, '13:00-14:00', 'confirmada'),
(1, 3, CURDATE() + INTERVAL 1 DAY, '14:00-15:00', 'pendiente'),
(2, 2, CURDATE() + INTERVAL 2 DAY, '13:00-14:00', 'confirmada'),
(3, 5, CURDATE() + INTERVAL 2 DAY, '20:00-21:00', 'confirmada'),
(4, 6, CURDATE() + INTERVAL 3 DAY, '15:00-16:00', 'mantenimiento'),
(6, 2, CURDATE() + INTERVAL 4 DAY, '10:00-11:00', 'confirmada'),
(6, 2, CURDATE() + INTERVAL 4 DAY, '11:00-12:00', 'confirmada'),
(7, 3, CURDATE() + INTERVAL 5 DAY, '13:00-14:00', 'confirmada'),
(8, 4, CURDATE() + INTERVAL 5 DAY, '13:00-14:00', 'confirmada'),
(9, 5, CURDATE() + INTERVAL 1 DAY, '09:00-10:00', 'confirmada'),
(10, 2, CURDATE() + INTERVAL 1 DAY, '09:00-10:00', 'confirmada');
