-- Seed data for asistencia_db
-- Run: mysql -u root -p asistencia_db < database/seed.sql

-- 1. Roles
INSERT IGNORE INTO rol (nombre_rol) VALUES 
('admin'),
('instructor'),
('aprendiz');

-- 2. Usuarios de prueba (password: 123456)
-- Hash generado con password_hash('123456', PASSWORD_DEFAULT)
SET @pass = '$2y$10$syYSFrwcl7/.1IcP0dhx2.8O.kqbZd4hLZVM3FoOzB3PsphZa1fl6';

INSERT IGNORE INTO usuario (nombre_usuario, contrasena, nombre, apellido, identificacion, telefono, fk_rol) VALUES
('admin', @pass, 'Administrador', 'Sistema', '1000000001', '3001234567', (SELECT id_rol FROM rol WHERE nombre_rol = 'admin')),
('instructor', @pass, 'Juan', 'Pérez', '1000000002', '3001234568', (SELECT id_rol FROM rol WHERE nombre_rol = 'instructor')),
('aprendiz', @pass, 'Carlos', 'Gómez', '1000000003', '3001234569', (SELECT id_rol FROM rol WHERE nombre_rol = 'aprendiz'));