-- Script de inicialización de la base de datos
-- Se ejecuta automáticamente la primera vez que arranca el contenedor de MariaDB

CREATE DATABASE IF NOT EXISTS weatherapp;
USE weatherapp;

-- Tabla que registra todas las consultas realizadas en la aplicación
CREATE TABLE IF NOT EXISTS consultas (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    nombre_ciudad VARCHAR(100) NOT NULL,
    codigo_pais   VARCHAR(5),
    latitud       DECIMAL(9,6),
    longitud      DECIMAL(9,6),
    tipo_consulta ENUM('actual', 'horas', 'semana') NOT NULL,
    fecha_consulta DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_usuario    VARCHAR(45),
    INDEX idx_ciudad     (nombre_ciudad),
    INDEX idx_fecha      (fecha_consulta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
