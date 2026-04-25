-- update_existing_schema.sql
-- Script para actualizar la base de datos existente de michele_ca
-- Agrega tabla de categorías y nuevos campos en productos

CREATE DATABASE IF NOT EXISTS michele_ca;
USE michele_ca;

-- 1) Crear tabla de categorías si no existe
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2) Agregar columnas a productos si no existen
ALTER TABLE productos
    ADD COLUMN IF NOT EXISTS categoria_id INT DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS codigo_barras VARCHAR(100) UNIQUE DEFAULT NULL;

-- 3) Migrar categorías existentes en productos a tabla categorias
-- Nota: esto solo se ejecuta si la columna categoria contiene valores
INSERT INTO categorias (nombre)
SELECT DISTINCT categoria
FROM productos
WHERE categoria IS NOT NULL AND categoria <> ''
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- 4) Actualizar categoria_id en productos usando la tabla categorias
UPDATE productos p
LEFT JOIN categorias c ON c.nombre = p.categoria
SET p.categoria_id = c.id
WHERE p.categoria IS NOT NULL AND p.categoria <> '';

-- 5) Agregar FOREIGN KEY si no existe
DELIMITER $$
DROP PROCEDURE IF EXISTS add_fk_productos_categoria$$
CREATE PROCEDURE add_fk_productos_categoria()
BEGIN
    DECLARE fk_exists INT DEFAULT 0;
    SELECT COUNT(*) INTO fk_exists
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND TABLE_NAME = 'productos'
      AND CONSTRAINT_NAME = 'fk_productos_categoria';

    IF fk_exists = 0 THEN
        ALTER TABLE productos
            ADD CONSTRAINT fk_productos_categoria
            FOREIGN KEY (categoria_id) REFERENCES categorias(id)
            ON DELETE SET NULL;
    END IF;
END$$
CALL add_fk_productos_categoria()$$
DROP PROCEDURE IF EXISTS add_fk_productos_categoria$$
DELIMITER ;

-- 6) Mantener columna categoria para compatibilidad o removerla más adelante
-- Si deseas eliminarla después de verificar la migración, usa:
-- ALTER TABLE productos DROP COLUMN categoria;

-- 7) Información opcional: muestra el resultado de la migración
SELECT id, nombre FROM categorias ORDER BY nombre ASC;
SELECT id, nombre, categoria, categoria_id, codigo_barras FROM productos ORDER BY id ASC LIMIT 20;
