-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 16-09-2026 a las 22:01:07
-- Versión del servidor: 12.3.3-MariaDB
-- Versión de PHP: 8.5.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `michele_ca`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `fecha_creacion`) VALUES
(1, 'Frenos', 'Pastillas, discos y componentes de sistema de frenos', '2026-04-23 00:50:09'),
(2, 'Motor', 'Aceites, filtros y componentes para motor', '2026-04-23 00:50:09'),
(3, 'Iluminación', 'Faros, focos y luces LED para vehículos', '2026-04-23 00:50:09'),
(4, 'Suspensión', 'Amortiguadores, resortes y bujes', '2026-04-23 00:50:09'),
(5, 'Accesorios', 'Accesorios y repuestos generales para auto', '2026-04-23 00:50:09'),
(6, 'Gomas', 'Accesorios de Gomas y Mangueras', '2026-04-23 23:13:03'),
(7, 'Electrónicos', 'Productos electrónicos y gadgets', '2026-05-09 20:35:59'),
(8, 'Ropa', 'Prendas de vestir', '2026-05-09 20:35:59'),
(9, 'Alimentos', 'Productos alimenticios', '2026-05-09 20:35:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  `tipo` enum('persona','empresa') DEFAULT 'persona',
  `rif_cedula` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `nombre`, `email`, `telefono`, `direccion`, `fecha_creacion`, `tipo`, `rif_cedula`) VALUES
(1, 'Auto Servicios Rodríguez', 'ventas@asrodriguez.com', '5512345678', 'Av. del Taller 345', '2026-04-23 00:50:10', 'empresa', 'J-8041414141'),
(2, 'Distribuciones MotorSA', 'contacto@motorsa.com', '5587654321', 'Calle Industria 120', '2026-04-23 00:50:10', 'empresa', 'J-4588777444'),
(3, 'Repuestos Express', 'info@repuestosexpress.com', '5522334455', 'Parque Industrial 56', '2026-04-23 00:50:10', 'empresa', 'J-10244222544'),
(4, 'Escritorio Juridico Maria Jimenez', 'cliente@ejemplo.com', '04121234567', 'Dirección de ejemplo', '2026-05-09 20:35:59', 'empresa', 'J-254125554'),
(5, 'Luis Mendez', 'cliente@ejemplo.com', '04121234567', 'Dirección de ejemplo', '2026-05-09 21:25:24', 'persona', 'V-12345678'),
(6, 'Transporte Briceño', 'empresa@ejemplo.com', '04121234568', 'Dirección empresa', '2026-05-09 21:25:24', 'empresa', 'J-123456789'),
(7, 'Alejandro Garrido', '', '0414141414141', 'Santa Cruz', '2026-05-09 21:27:55', 'persona', 'V-10101010');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `id` int(11) NOT NULL,
  `fecha` timestamp NULL DEFAULT current_timestamp(),
  `proveedor_id` int(11) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `numero_factura` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `compras`
--

INSERT INTO `compras` (`id`, `fecha`, `proveedor_id`, `total`, `numero_factura`) VALUES
(1, '2026-04-05 14:30:00', 2, 3300.00, NULL),
(2, '2026-04-08 18:20:00', 1, 2400.00, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras_detalles`
--

CREATE TABLE `compras_detalles` (
  `id` int(11) NOT NULL,
  `compra_id` int(11) DEFAULT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `compras_detalles`
--

INSERT INTO `compras_detalles` (`id`, `compra_id`, `producto_id`, `cantidad`, `precio_unitario`) VALUES
(1, 1, 3, 12, 250.00),
(2, 1, 8, 10, 180.00),
(3, 2, 1, 10, 1200.00),
(4, 2, 2, 5, 1800.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `categoria` varchar(50) DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  `categoria_id` int(11) DEFAULT NULL,
  `codigo_barras` varchar(100) DEFAULT NULL,
  `precio_sin_iva` tinyint(1) DEFAULT 1,
  `proveedor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `precio`, `stock`, `categoria`, `fecha_creacion`, `categoria_id`, `codigo_barras`, `precio_sin_iva`, `proveedor_id`) VALUES
(1, 'Pastillas de freno delanteras', 'Juego de pastillas de freno para autos compactos', 1200.00, 45, NULL, '2026-04-23 00:50:10', 1, '7508000000015', 1, NULL),
(2, 'Disco de freno trasero', 'Disco de freno trasero 280 mm', 1800.00, 30, NULL, '2026-04-23 00:50:10', 1, '7508000000022', 1, NULL),
(3, 'Filtro de aceite', 'Filtro de aceite compatible con motores 1.6/2.0', 250.00, 90, NULL, '2026-04-23 00:50:10', 2, '7508000000039', 1, NULL),
(4, 'Aceite sintético 5W-30', 'Aceite de motor sintético 5 litros', 1650.00, 60, NULL, '2026-04-23 00:50:10', 2, '7508000000046', 1, 4),
(5, 'Amortiguador delantero', 'Amortiguador gas para suspensión delantera', 2200.00, 25, NULL, '2026-04-23 00:50:10', 4, '7508000000053', 1, 2),
(6, 'Resorte trasero', 'Resorte para suspensión trasera estándar', 1100.00, 19, NULL, '2026-04-23 00:50:10', 4, '7508000000060', 1, NULL),
(7, 'Faro derecho halógeno', 'Faro halógeno con lámpara incluida', 1350.00, 18, NULL, '2026-04-23 00:50:10', 3, '7508000000077', 1, NULL),
(8, 'Bujía de encendido', 'Bujía de alto rendimiento para motor 4 cilindros', 180.00, 120, NULL, '2026-04-23 00:50:10', 2, '7508000000084', 1, NULL),
(9, 'Batería automotriz 12V', 'Batería 12V 70Ah para autos y camionetas', 4200.00, 15, NULL, '2026-04-23 00:50:10', 5, '7508000000091', 1, 3),
(10, 'Barra Estabilizadora', 'Barra Estabilizadora', 800.00, 10, NULL, '2026-04-23 23:12:09', 5, '7508000660046', 1, NULL),
(11, 'Laptop', 'Laptop básica', 500.00, 10, NULL, '2026-05-09 20:35:59', 1, '123456789', 1, NULL),
(12, 'Camisa', 'Camisa de algodón', 20.00, 48, NULL, '2026-05-09 20:35:59', 2, '987654321', 1, NULL),
(13, 'Pan', 'Pan fresco', 2.00, 100, NULL, '2026-05-09 20:35:59', 3, '111222333', 1, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  `tipo` enum('persona','empresa') DEFAULT 'persona',
  `rif_cedula` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id`, `nombre`, `email`, `telefono`, `direccion`, `fecha_creacion`, `tipo`, `rif_cedula`) VALUES
(1, 'Proveedora Frenos MX', 'ventas@frenosmx.com', '5551231234', 'Av. Automotriz 200', '2026-04-23 00:50:10', 'empresa', 'J-122211111'),
(2, 'Importadora Motores', 'contacto@importmotores.com', '5559876543', 'Carretera Industrial 78', '2026-04-23 00:50:10', 'empresa', 'J-522223333'),
(3, 'Electronica AutoPartes', 'info@electronicaautoparts.com', '5551112222', 'Polígono Comercial 14', '2026-04-23 00:50:10', 'empresa', 'J-623322222'),
(4, 'Aceites Roma', '', '+5841225544455', 'Zona Colonial', '2026-05-09 22:05:29', 'empresa', 'J-1999988866');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` varchar(50) NOT NULL DEFAULT 'vendedor',
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `rol`, `fecha_creacion`) VALUES
(1, 'Admin', 'admin@micheleca.com', 'admin', 'admin', '2026-04-22 22:10:34'),
(4, 'Vendedor Test', 'vendedor@micheleca.com', 'vendedor', 'vendedor', '2026-09-16 20:55:25'),
(5, 'Almacenista Test', 'almacenista@micheleca.com', 'almacenista', 'almacenista', '2026-09-16 20:55:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `fecha` timestamp NULL DEFAULT current_timestamp(),
  `cliente_id` int(11) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) DEFAULT 0.00,
  `iva` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id`, `fecha`, `cliente_id`, `total`, `subtotal`, `iva`) VALUES
(1, '2026-04-10 16:15:00', 1, 4200.00, 3620.69, 579.31),
(2, '2026-04-12 20:40:00', 3, 3070.00, 2646.55, 423.45),
(3, '2026-05-09 21:28:43', 7, 1322.40, 1140.00, 182.40);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas_detalles`
--

CREATE TABLE `ventas_detalles` (
  `id` int(11) NOT NULL,
  `venta_id` int(11) DEFAULT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ventas_detalles`
--

INSERT INTO `ventas_detalles` (`id`, `venta_id`, `producto_id`, `cantidad`, `precio_unitario`) VALUES
(1, 1, 9, 1, 4200.00),
(2, 2, 1, 2, 1200.00),
(3, 2, 7, 1, 1350.00),
(4, 2, 8, 2, 180.00),
(5, 3, 12, 2, 20.00),
(6, 3, 6, 1, 1100.00);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rif_cedula` (`rif_cedula`);

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_compras_numero_factura` (`numero_factura`),
  ADD KEY `proveedor_id` (`proveedor_id`);

--
-- Indices de la tabla `compras_detalles`
--
ALTER TABLE `compras_detalles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `compra_id` (`compra_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_barras` (`codigo_barras`),
  ADD KEY `fk_productos_categoria` (`categoria_id`),
  ADD KEY `fk_productos_proveedor` (`proveedor_id`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rif_cedula` (`rif_cedula`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Indices de la tabla `ventas_detalles`
--
ALTER TABLE `ventas_detalles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venta_id` (`venta_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `compras_detalles`
--
ALTER TABLE `compras_detalles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `ventas_detalles`
--
ALTER TABLE `ventas_detalles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `1` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `compras_detalles`
--
ALTER TABLE `compras_detalles`
  ADD CONSTRAINT `1` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `fk_productos_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_productos_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `ventas_detalles`
--
ALTER TABLE `ventas_detalles`
  ADD CONSTRAINT `1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
