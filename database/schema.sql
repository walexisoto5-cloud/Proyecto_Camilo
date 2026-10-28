-- =============================================================================
-- SCRIPT DE BASE DE DATOS: Sistema de Control de Asistencia SENA
-- Base de Datos: asistencia_db
-- Motor: InnoDB | Cotejamiento: utf8mb4_unicode_ci
-- =============================================================================

-- 1. Eliminación y recreación limpia de la base de datos completa
-- (Esto evita cualquier error de clave foránea #1451 con tablas existentes)
DROP DATABASE IF EXISTS asistencia_db;

CREATE DATABASE asistencia_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE asistencia_db;

-- -----------------------------------------------------------------------------
-- TABLA 1: rol
-- -----------------------------------------------------------------------------
CREATE TABLE rol (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(45) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- TABLA 2: usuario (Credenciales y datos personales)
-- -----------------------------------------------------------------------------
CREATE TABLE usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(45) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    nombre VARCHAR(60) NOT NULL,
    apellido VARCHAR(60) NOT NULL,
    identificacion VARCHAR(45) NOT NULL UNIQUE,
    telefono VARCHAR(45) NULL,
    codigo_rfid VARCHAR(50) NULL,
    estado VARCHAR(20) DEFAULT 'Activo',
    fk_rol INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuario_rol FOREIGN KEY (fk_rol) REFERENCES rol(id_rol) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_usuario_login (nombre_usuario, identificacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- TABLA 3: ficha (Programas de formación y fichas SENA)
-- -----------------------------------------------------------------------------
CREATE TABLE ficha (
    id_ficha INT AUTO_INCREMENT PRIMARY KEY,
    numero_ficha VARCHAR(50) NOT NULL UNIQUE,
    nombre_programa VARCHAR(150) NOT NULL,
    jornada VARCHAR(45) NOT NULL DEFAULT 'Diurna',
    fk_usuario INT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ficha_usuario FOREIGN KEY (fk_usuario) REFERENCES usuario(id_usuario) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_ficha_numero (numero_ficha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- TABLA 4: horario (Bloques de formación programados por ficha)
-- -----------------------------------------------------------------------------
CREATE TABLE horario (
    id_horario INT AUTO_INCREMENT PRIMARY KEY,
    dia_semana VARCHAR(45) NOT NULL,
    entrada TIME NOT NULL,
    salida TIME NOT NULL,
    competencia VARCHAR(150) NULL,
    ambiente VARCHAR(45) NULL,
    fk_ficha INT NOT NULL,
    fk_usuario_instructor INT NULL,
    CONSTRAINT fk_horario_ficha FOREIGN KEY (fk_ficha) REFERENCES ficha(id_ficha) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_horario_instructor FOREIGN KEY (fk_usuario_instructor) REFERENCES usuario(id_usuario) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_horario_ficha_dia (fk_ficha, dia_semana)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- TABLA 5: aprendiz (Vinculación a ficha y código RFID)
-- -----------------------------------------------------------------------------
CREATE TABLE aprendiz (
    id_aprendiz INT AUTO_INCREMENT PRIMARY KEY,
    codigo_rfid VARCHAR(50) NULL,
    fk_ficha INT NOT NULL,
    fk_usuario INT NOT NULL UNIQUE,
    CONSTRAINT fk_aprendiz_ficha FOREIGN KEY (fk_ficha) REFERENCES ficha(id_ficha) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_aprendiz_usuario FOREIGN KEY (fk_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_aprendiz_rfid (codigo_rfid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- TABLA 6: asistencia (Marcajes de entrada/salida y retardos)
-- -----------------------------------------------------------------------------
CREATE TABLE asistencia (
    id_asistencia INT AUTO_INCREMENT PRIMARY KEY,
    fecha_asistencia DATE NOT NULL,
    entrada DATETIME NULL,
    salida DATETIME NULL,
    estado_entrada VARCHAR(45) NULL,
    estado_salida VARCHAR(45) NULL,
    minutos_retardo INT DEFAULT 0,
    minutos_salida_anticipada INT DEFAULT 0,
    fk_aprendiz INT NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_asistencia_aprendiz FOREIGN KEY (fk_aprendiz) REFERENCES aprendiz(id_aprendiz) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_asistencia_aprendiz_fecha (fk_aprendiz, fecha_asistencia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- TABLA 7: ingresos (Registro histórico simplificado de marcajes diarios)
-- -----------------------------------------------------------------------------
CREATE TABLE ingresos (
    id_ingreso INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    fecha DATE NOT NULL,
    hora_entrada TIME NULL,
    hora_salida TIME NULL,
    estado VARCHAR(45) NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ingresos_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY uk_ingreso_usuario_fecha (id_usuario, fecha),
    INDEX idx_ingresos_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- TABLA 8: excusa (Justificaciones médicas o laborales)
-- -----------------------------------------------------------------------------
CREATE TABLE excusa (
    id_excusa INT AUTO_INCREMENT PRIMARY KEY,
    archivo VARCHAR(450) NOT NULL,
    observacion TEXT NULL,
    estado VARCHAR(45) DEFAULT 'Pendiente',
    fecha_subida DATE NOT NULL,
    fecha_revision DATE NULL,
    fk_asistencia INT NOT NULL,
    fk_usuario_instructor INT NULL,
    CONSTRAINT fk_excusa_asistencia FOREIGN KEY (fk_asistencia) REFERENCES asistencia(id_asistencia) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_excusa_instructor FOREIGN KEY (fk_usuario_instructor) REFERENCES usuario(id_usuario) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_excusa_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- TABLA 9: comite (Remisiones a Comité por inasistencias y deserción)
-- -----------------------------------------------------------------------------
CREATE TABLE comite (
    id_comite INT AUTO_INCREMENT PRIMARY KEY,
    fk_aprendiz INT NOT NULL,
    fk_usuario_instructor INT NOT NULL,
    fecha_remision DATETIME NOT NULL,
    total_faltas INT NOT NULL DEFAULT 0,
    porcentaje_fallas DECIMAL(5,2) DEFAULT 0,
    motivo TEXT NOT NULL,
    estado VARCHAR(30) DEFAULT 'Pendiente',
    observacion_admin TEXT NULL,
    fecha_resolucion DATETIME NULL,
    CONSTRAINT fk_comite_aprendiz FOREIGN KEY (fk_aprendiz) REFERENCES aprendiz(id_aprendiz) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_comite_instructor FOREIGN KEY (fk_usuario_instructor) REFERENCES usuario(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_comite_aprendiz (fk_aprendiz),
    INDEX idx_comite_instructor (fk_usuario_instructor),
    INDEX idx_comite_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- DATOS INICIALES (SEMILLA / SEED DATA)
-- Contraseña general para todos los usuarios: 123456
-- Hash Bcrypt: $2y$10$syYSFrwcl7/.1IcP0dhx2.8O.kqbZd4hLZVM3FoOzB3PsphZa1fl6
-- =============================================================================

-- 1. Insertar Roles Principales
INSERT INTO rol (id_rol, nombre_rol) VALUES 
(1, 'Administrador'),
(2, 'Instructor'),
(3, 'Aprendiz');

-- 2. Insertar Usuarios de Prueba (Clave: 123456)
SET @pass_demo = '$2y$10$syYSFrwcl7/.1IcP0dhx2.8O.kqbZd4hLZVM3FoOzB3PsphZa1fl6';

INSERT INTO usuario (id_usuario, nombre_usuario, contrasena, nombre, apellido, identificacion, telefono, codigo_rfid, fk_rol) VALUES
(1, 'admin', @pass_demo, 'Administrador', 'General', '1000000001', '3001234567', NULL, 1),
(2, 'instructor', @pass_demo, 'Camilo', 'Instructor', '1000000002', '3001234568', 'INST-2001', 2),
(3, 'aprendiz', @pass_demo, 'Juan Diego', 'Pérez', '1000000003', '3001234569', 'RFID-3001', 3);

-- 3. Insertar Ficha Demo
INSERT INTO ficha (id_ficha, numero_ficha, nombre_programa, jornada, fk_usuario) VALUES
(1, '3234082', 'ADSO - Análisis y Desarrollo de Software', 'Diurna', 2);

-- 4. Asociar Usuario Aprendiz a la Ficha
INSERT INTO aprendiz (id_aprendiz, codigo_rfid, fk_ficha, fk_usuario) VALUES
(1, 'RFID-3001', 1, 3);

-- 5. Horarios de Formación (Lunes a Viernes 06:00 a 12:00)
INSERT INTO horario (dia_semana, entrada, salida, competencia, ambiente, fk_ficha, fk_usuario_instructor) VALUES
('Lunes', '06:00:00', '12:00:00', 'Desarrollo de Software y Base de Datos', 'Ambiente 204', 1, 2),
('Martes', '06:00:00', '12:00:00', 'Desarrollo de Software y Base de Datos', 'Ambiente 204', 1, 2),
('Miércoles', '06:00:00', '12:00:00', 'Desarrollo Web Frontend y Backend', 'Ambiente 204', 1, 2),
('Jueves', '06:00:00', '12:00:00', 'Desarrollo Web Frontend y Backend', 'Ambiente 204', 1, 2),
('Viernes', '06:00:00', '12:00:00', 'Pruebas de Software e Integración Continua', 'Ambiente 204', 1, 2);
