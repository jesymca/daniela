-- update_schema_for_pos.sql
-- Actualizaciones para convertir el módulo de ventas en Punto de Venta con IVA

USE michele_ca;

-- Agregar campos a la tabla ventas para subtotal, iva y total
ALTER TABLE ventas
    ADD COLUMN IF NOT EXISTS subtotal DECIMAL(10,2) DEFAULT 0.00,
    ADD COLUMN IF NOT EXISTS iva DECIMAL(10,2) DEFAULT 0.00,
    ADD COLUMN IF NOT EXISTS total DECIMAL(10,2) DEFAULT 0.00;

-- Actualizar ventas existentes: asumir que el total actual es sin IVA, calcular IVA 16%
UPDATE ventas SET subtotal = total / 1.16, iva = total - (total / 1.16), total = total WHERE subtotal = 0;

-- Agregar campos a clientes para RIF/Cédula y tipo
ALTER TABLE clientes
    ADD COLUMN IF NOT EXISTS tipo ENUM('persona', 'empresa') DEFAULT 'persona',
    ADD COLUMN IF NOT EXISTS rif_cedula VARCHAR(20) UNIQUE DEFAULT NULL;

-- Agregar campo a productos para indicar si precio incluye IVA (opcional, asumir no incluye)
ALTER TABLE productos
    ADD COLUMN IF NOT EXISTS precio_sin_iva BOOLEAN DEFAULT TRUE;

-- Insertar datos de prueba si no existen
INSERT IGNORE INTO categorias (nombre, descripcion) VALUES
('Electrónicos', 'Productos electrónicos y gadgets'),
('Ropa', 'Prendas de vestir'),
('Alimentos', 'Productos alimenticios');

INSERT IGNORE INTO productos (nombre, descripcion, precio, stock, categoria_id, codigo_barras) VALUES
('Laptop', 'Laptop básica', 500.00, 10, 1, '123456789'),
('Camisa', 'Camisa de algodón', 20.00, 50, 2, '987654321'),
('Pan', 'Pan fresco', 2.00, 100, 3, '111222333');

INSERT IGNORE INTO clientes (nombre, email, telefono, direccion, tipo, rif_cedula) VALUES
('Cliente Genérico', 'cliente@ejemplo.com', '04121234567', 'Dirección de ejemplo', 'persona', 'V-12345678'),
('Empresa Ejemplo', 'empresa@ejemplo.com', '04121234568', 'Dirección empresa', 'empresa', 'J-123456789');

-- Usuario admin si no existe
INSERT IGNORE INTO usuarios (nombre, email, password, rol) VALUES
('Admin', 'admin@micheleca.com', '$2y$10$examplehashedpassword', 'admin');