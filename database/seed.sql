-- =============================================================================
-- DATOS INICIALES (SEMILLA / SEED DATA)
-- Base de Datos: asistencia_db
-- =============================================================================

USE asistencia_db;

-- 1. Roles del Sistema
INSERT INTO rol (id_rol, nombre_rol) VALUES 
(1, 'Administrador'),
(2, 'Instructor'),
(3, 'Aprendiz')
ON DUPLICATE KEY UPDATE nombre_rol = VALUES(nombre_rol);

-- 2. Usuarios de Prueba (Contraseña: 123456)
-- Hash Bcrypt generado con password_hash('123456', PASSWORD_DEFAULT)
SET @pass_demo = '$2y$10$syYSFrwcl7/.1IcP0dhx2.8O.kqbZd4hLZVM3FoOzB3PsphZa1fl6';

INSERT INTO usuario (id_usuario, nombre_usuario, contrasena, nombre, apellido, identificacion, telefono, codigo_rfid, fk_rol) VALUES
(1, 'admin', @pass_demo, 'Administrador', 'General', '1000000001', '3001234567', NULL, 1),
(2, 'instructor', @pass_demo, 'Camilo', 'Instructor', '1000000002', '3001234568', 'INST-2001', 2),
(3, 'aprendiz', @pass_demo, 'Juan Diego', 'Pérez', '1000000003', '3001234569', 'RFID-3001', 3)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- 3. Ficha de Formación SENA
INSERT INTO ficha (id_ficha, numero_ficha, nombre_programa, jornada, fk_usuario) VALUES
(1, '3234082', 'ADSO - Análisis y Desarrollo de Software', 'Diurna', 2)
ON DUPLICATE KEY UPDATE nombre_programa = VALUES(nombre_programa);

-- 4. Vinculación de Aprendiz a la Ficha y Tarjeta RFID
INSERT INTO aprendiz (id_aprendiz, codigo_rfid, fk_ficha, fk_usuario) VALUES
(1, 'RFID-3001', 1, 3)
ON DUPLICATE KEY UPDATE codigo_rfid = VALUES(codigo_rfid);

-- 5. Horarios de Formación Programados (Lunes a Viernes 06:00 a 12:00)
INSERT INTO horario (dia_semana, entrada, salida, competencia, ambiente, fk_ficha, fk_usuario_instructor) VALUES
('Lunes', '06:00:00', '12:00:00', 'Desarrollo de Software y Base de Datos', 'Ambiente 204', 1, 2),
('Martes', '06:00:00', '12:00:00', 'Desarrollo de Software y Base de Datos', 'Ambiente 204', 1, 2),
('Miércoles', '06:00:00', '12:00:00', 'Desarrollo Web Frontend y Backend', 'Ambiente 204', 1, 2),
('Jueves', '06:00:00', '12:00:00', 'Desarrollo Web Frontend y Backend', 'Ambiente 204', 1, 2),
('Viernes', '06:00:00', '12:00:00', 'Pruebas de Software e Integración Continua', 'Ambiente 204', 1, 2);
