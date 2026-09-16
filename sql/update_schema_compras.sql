-- update_schema_compras.sql
-- Actualiza la estructura para compras con número de factura y datos de proveedor extendidos

USE michele_ca;

-- Agregar número de factura en compras
ALTER TABLE compras
    ADD COLUMN IF NOT EXISTS numero_factura VARCHAR(100) DEFAULT NULL;

-- Agregar campos de RIF/Cédula y tipo en proveedores
ALTER TABLE proveedores
    ADD COLUMN IF NOT EXISTS tipo ENUM('persona','empresa') DEFAULT 'persona',
    ADD COLUMN IF NOT EXISTS rif_cedula VARCHAR(20) UNIQUE DEFAULT NULL;

-- Agregar índice único sobre numero_factura si aún no existe
ALTER TABLE compras
    ADD UNIQUE INDEX IF NOT EXISTS idx_compras_numero_factura (numero_factura);
