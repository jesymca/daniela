USE michele_ca;

-- Datos de prueba para categorías
INSERT INTO categorias (nombre, descripcion) VALUES
('Frenos', 'Pastillas, discos y componentes de sistema de frenos'),
('Motor', 'Aceites, filtros y componentes para motor'),
('Iluminación', 'Faros, focos y luces LED para vehículos'),
('Suspensión', 'Amortiguadores, resortes y bujes'),
('Accesorios', 'Accesorios y repuestos generales para auto');

-- Datos de prueba para productos
INSERT INTO productos (nombre, descripcion, precio, stock, categoria_id, codigo_barras) VALUES
('Pastillas de freno delanteras', 'Juego de pastillas de freno para autos compactos', 1200.00, 45, 1, '7508000000015'),
('Disco de freno trasero', 'Disco de freno trasero 280 mm', 1800.00, 30, 1, '7508000000022'),
('Filtro de aceite', 'Filtro de aceite compatible con motores 1.6/2.0', 250.00, 90, 2, '7508000000039'),
('Aceite sintético 5W-30', 'Aceite de motor sintético 5 litros', 1650.00, 60, 2, '7508000000046'),
('Amortiguador delantero', 'Amortiguador gas para suspensión delantera', 2200.00, 25, 4, '7508000000053'),
('Resorte trasero', 'Resorte para suspensión trasera estándar', 1100.00, 20, 4, '7508000000060'),
('Faro derecho halógeno', 'Faro halógeno con lámpara incluida', 1350.00, 18, 3, '7508000000077'),
('Bujía de encendido', 'Bujía de alto rendimiento para motor 4 cilindros', 180.00, 120, 2, '7508000000084'),
('Batería automotriz 12V', 'Batería 12V 70Ah para autos y camionetas', 4200.00, 15, 5, '7508000000091');

-- Datos de prueba para clientes
INSERT INTO clientes (nombre, email, telefono, direccion) VALUES
('Auto Servicio Rodríguez', 'ventas@asrodriguez.com', '5512345678', 'Av. del Taller 345'),
('Distribuciones MotorSA', 'contacto@motorsa.com', '5587654321', 'Calle Industria 120'),
('Repuestos Express', 'info@repuestosexpress.com', '5522334455', 'Parque Industrial 56');

-- Datos de prueba para proveedores
INSERT INTO proveedores (nombre, email, telefono, direccion) VALUES
('Proveedora Frenos MX', 'ventas@frenosmx.com', '5551231234', 'Av. Automotriz 200'),
('Importadora Motores', 'contacto@importmotores.com', '5559876543', 'Carretera Industrial 78'),
('Electronica AutoParts', 'info@electronicaautoparts.com', '5551112222', 'Polígono Comercial 14');

-- Compras de prueba
INSERT INTO compras (fecha, proveedor_id, total) VALUES
('2026-04-05 10:30:00', 2, 3300.00),
('2026-04-08 14:20:00', 1, 2400.00);

INSERT INTO compras_detalles (compra_id, producto_id, cantidad, precio_unitario) VALUES
(1, 3, 12, 250.00),
(1, 8, 10, 180.00),
(2, 1, 10, 1200.00),
(2, 2, 5, 1800.00);

-- Ventas de prueba
INSERT INTO ventas (fecha, cliente_id, total) VALUES
('2026-04-10 12:15:00', 1, 4200.00),
('2026-04-12 16:40:00', 3, 3070.00);

INSERT INTO ventas_detalles (venta_id, producto_id, cantidad, precio_unitario) VALUES
(1, 9, 1, 4200.00),
(2, 1, 2, 1200.00),
(2, 7, 1, 1350.00),
(2, 8, 2, 180.00);
