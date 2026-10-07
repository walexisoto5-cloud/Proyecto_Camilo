-- creamos la base de datos
CREATE DATABASE IF NOT EXISTS asistencia_db;

-- Seleccionamos la base de datos
USE asistencia_db;

-- 1. Tabla para definir los roles de usuario
CREATE TABLE IF NOT EXISTS rol (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(45) NOT NULL
);

-- 2. Tabla de usuarios para credenciales y datos personales
CREATE TABLE IF NOT EXISTS usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(45) NOT NULL,
    contrasena VARCHAR(400) NOT NULL,
    nombre VARCHAR(45) NOT NULL,
    apellido VARCHAR(45) NOT NULL,
    identificacion VARCHAR(45) NOT NULL,
    telefono VARCHAR(45),
    fk_rol INT NOT NULL,
    FOREIGN KEY (fk_rol) REFERENCES rol(id_rol)
);

-- 3. Tabla para la información de programas de formación y fichas
CREATE TABLE IF NOT EXISTS ficha (
    id_ficha INT AUTO_INCREMENT PRIMARY KEY,
    nombre_programa VARCHAR(45) NOT NULL,
    jornada VARCHAR(45) NOT NULL,
    fk_usuario INT,
    FOREIGN KEY (fk_usuario) REFERENCES usuario(id_usuario)
);

-- 4. Tabla para los horarios asignados a cada ficha
CREATE TABLE IF NOT EXISTS horario (
    id_horario INT AUTO_INCREMENT PRIMARY KEY,
    dia_semana VARCHAR(45) NOT NULL,
    entrada TIME NOT NULL,
    salida TIME NOT NULL,
    fk_ficha INT NOT NULL,
    FOREIGN KEY (fk_ficha) REFERENCES ficha(id_ficha)
);

-- 5. Tabla para vincular aprendices con fichas y tarjetas RFID
CREATE TABLE IF NOT EXISTS aprendiz (
    id_aprendiz INT AUTO_INCREMENT PRIMARY KEY,
    codigo_rfid VARCHAR(50),
    fk_ficha INT NOT NULL,
    fk_usuario INT NOT NULL,
    FOREIGN KEY (fk_ficha) REFERENCES ficha(id_ficha),
    FOREIGN KEY (fk_usuario) REFERENCES usuario(id_usuario)
);

-- 6. Tabla para el registro de asistencias por fecha y hora
CREATE TABLE IF NOT EXISTS asistencia (
    id_asistencia INT AUTO_INCREMENT PRIMARY KEY,
    fecha_asistencia DATE NOT NULL,
    entrada DATETIME,
    salida DATETIME,
    estado_entrada VARCHAR(45),
    estado_salida VARCHAR(45),
    minutos_retardo INT DEFAULT 0,
    minutos_salida_anticipada INT DEFAULT 0,
    fk_aprendiz INT NOT NULL,
    FOREIGN KEY (fk_aprendiz) REFERENCES aprendiz(id_aprendiz)
);

-- 7. Tabla para la gestión de excusas adjuntas
CREATE TABLE IF NOT EXISTS excusa (
    id_excusa INT AUTO_INCREMENT PRIMARY KEY,
    archivo VARCHAR(450) NOT NULL,
    observacion VARCHAR(45),
    estado VARCHAR(45) DEFAULT 'Pendiente',
    fecha_subida DATE NOT NULL,
    fecha_revision DATE,
    fk_asistencia INT NOT NULL,
    fk_usuario_instructor INT,
    FOREIGN KEY (fk_asistencia) REFERENCES asistencia(id_asistencia),
    FOREIGN KEY (fk_usuario_instructor) REFERENCES usuario(id_usuario)
);