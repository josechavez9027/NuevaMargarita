-- ============================================
-- Base de datos: eventospanaderia
-- Adaptado para MySQL 8 (Railway)
-- ============================================

USE `railway`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- --------------------------------------------------------
-- Tabla: categoria
-- --------------------------------------------------------

CREATE TABLE `categoria` (
  `id_categoria` int NOT NULL,
  `nombre_categoria` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `categoria` (`id_categoria`, `nombre_categoria`, `descripcion`, `created_at`, `updated_at`) VALUES
(1, 'Dulce', 'Productos de panadería dulce tradicional', '2026-05-11 02:27:09', '2026-05-11 02:27:09'),
(2, 'Salado', 'Panes y productos salados', '2026-05-11 02:27:09', '2026-05-11 02:27:09'),
(3, 'Especial', 'Productos especiales y premium', '2026-05-11 02:27:09', '2026-05-11 02:27:09'),
(4, 'Pasteles', 'Pasteles y tortas para eventos', '2026-05-11 02:27:09', '2026-05-11 02:27:09'),
(5, 'Hojaldres', 'Productos de hojaldre y masa quebrada', '2026-05-11 02:27:09', '2026-05-11 02:27:09'),
(6, 'Mini', 'Productos en tamaño mini para eventos', '2026-05-11 02:27:09', '2026-05-11 02:27:09'),
(7, 'Galletas', 'Variedad de galletas y cookies', '2026-05-11 02:27:09', '2026-05-11 02:27:09');

-- --------------------------------------------------------
-- Tabla: cliente
-- --------------------------------------------------------

CREATE TABLE `cliente` (
  `id_cliente` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido_paterno` varchar(100) NOT NULL,
  `apellido_materno` varchar(100) DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `correo_electronico` varchar(150) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `cliente` (`id_cliente`, `nombre`, `apellido_paterno`, `apellido_materno`, `telefono`, `correo_electronico`, `direccion`, `fecha_registro`, `updated_at`) VALUES
(1, 'Ana María', 'López', 'García', '555-123-4567', 'ana.lopez@email.com', 'Av. Principal #123, Col. Centro', '2026-05-11 02:27:09', '2026-05-11 02:27:09'),
(2, 'Carlos', 'Martínez', 'Rodríguez', '555-987-6543', 'carlos.martinez@email.com', 'Calle Secundaria #456, Col. Norte', '2026-05-11 02:27:09', '2026-05-11 02:27:09'),
(3, 'Empresa', 'Eventos', 'Felices', '555-555-5555', 'ventas@eventosfelices.com', 'Plaza Comercial #789, Zona Industrial', '2026-05-11 02:27:09', '2026-05-11 02:27:09'),
(4, 'María Elena', 'González', 'Pérez', '555-111-2233', 'maria.gonzalez@email.com', 'Privada Flores #234, Col. Jardines', '2026-05-11 02:27:09', '2026-05-11 02:27:09'),
(5, 'Roberto', 'Sánchez', 'Hernández', '555-444-5566', 'roberto.sanchez@email.com', 'Boulevard Central #567, Col. Moderna', '2026-05-11 02:27:09', '2026-05-11 02:27:09');

-- --------------------------------------------------------
-- Tabla: producto
-- --------------------------------------------------------

CREATE TABLE `producto` (
  `id_producto` int NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `id_categoria` int NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL CHECK (`precio_unitario` >= 0),
  `disponible` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `producto` (`id_producto`, `nombre`, `descripcion`, `id_categoria`, `precio_unitario`, `disponible`) VALUES
(1, 'Concha Tradicional', 'Concha clásica de vainilla y chocolate', 1, 22.00, 1),
(2, 'Cuerno de Crema', 'Cuerno relleno de crema pastelera', 1, 22.00, 1),
(3, 'Oreja', 'Hojaldre azucarado crujiente', 1, 22.00, 1),
(4, 'Churro', 'Churro espolvoreado con azúcar y canela', 1, 22.00, 1),
(5, 'Donas', 'Donas glaseadas de varios sabores', 1, 25.00, 1),
(6, 'Empanada de Fruta', 'Empanada rellena de fruta de temporada', 1, 22.00, 1),
(7, 'Bolillo', 'Bolillo clásico crujiente', 2, 18.00, 1),
(8, 'Teleras', 'Teleras para tortas', 2, 20.00, 1),
(9, 'Pan de Ajo', 'Pan de ajo con perejil', 2, 45.00, 1),
(10, 'Baguette', 'Baguette artesanal', 2, 38.00, 1),
(11, 'Rollos de Queso', 'Rollos rellenos de queso', 2, 42.00, 1),
(12, 'Pretzels', 'Pretzels suaves con sal', 2, 36.00, 1),
(13, 'Concha Rellena de crema de mazapan', 'Concha especial rellena de crema de mazapan', 3, 65.00, 1),
(14, 'Pan de Muerto Premium', 'Pan de muerto con ingredientes premium', 3, 120.00, 1),
(15, 'Rosca de Reyes Artesanal', 'Rosca de reyes con frutas cristalizadas', 3, 280.00, 1),
(16, 'Brioche de Mantequilla', 'Brioche francés con mantequilla', 3, 55.00, 1),
(17, 'Croissant de Almendra', 'Croissant relleno de almendra', 3, 48.00, 1),
(18, 'Pan Rústico', 'Pan rústico con masa madre', 3, 75.00, 1),
(19, 'Pastel de Chocolate', 'Pastel de chocolate triple capa', 4, 450.00, 1),
(20, 'Pastel de Zanahoria', 'Pastel de zanahoria con nuez', 4, 420.00, 1),
(21, 'Pastel de Tres Leches', 'Pastel de tres leches tradicional', 4, 380.00, 1),
(22, 'Cheesecake de Fresa', 'Cheesecake con cubierta de fresa', 4, 520.00, 1),
(23, 'Pastel de Bodas', 'Pastel elegante para bodas', 4, 800.00, 1),
(24, 'Tiramisú', 'Tiramisú italiano auténtico', 4, 460.00, 1),
(25, 'Palmera de Chocolate', 'Palmera de hojaldre con chocolate', 5, 32.00, 1),
(26, 'Milhojas', 'Milhojas con crema pastelera', 5, 45.00, 1),
(27, 'Vol-au-vent', 'Vol-au-vent para rellenar', 5, 38.00, 1),
(28, 'Palmera Natural', 'Palmera de hojaldre azucarada', 5, 28.00, 1),
(29, 'Tarta de Manzana', 'Tarta de manzana en hojaldre', 5, 85.00, 1),
(30, 'Strudel de Manzana', 'Strudel de manzana canela', 5, 42.00, 1),
(31, 'Mini Conchas', 'Paquete de 12 conchas mini', 6, 180.00, 1),
(32, 'Mini Donas', 'Paquete de 24 donas mini', 6, 220.00, 1),
(33, 'Mini Croissants', 'Paquete de 16 croissants mini', 6, 190.00, 1),
(34, 'Mini Empanadas', 'Paquete de 20 empanadas mini', 6, 240.00, 1),
(35, 'Mini Muffins', 'Paquete de 18 muffins mini', 6, 210.00, 1),
(36, 'Mini Galletas', 'Paquete de 30 galletas mini', 6, 160.00, 1),
(37, 'Galletas de Mantequilla', 'Galletas crujientes de mantequilla', 7, 85.00, 1),
(38, 'Galletas de Chispas', 'Galletas con chispas de chocolate', 7, 95.00, 1),
(39, 'Galletas de Avena', 'Galletas de avena con pasas', 7, 75.00, 1),
(40, 'Galletas de Jengibre', 'Galletas de jengibre decoradas', 7, 110.00, 1),
(41, 'Galletas Macarons', 'Macarons franceses de varios sabores', 7, 180.00, 1),
(42, 'Galletas Alfajores', 'Alfajores rellenos de dulce de leche', 7, 120.00, 1);

-- --------------------------------------------------------
-- Tabla: pedido
-- --------------------------------------------------------

CREATE TABLE `pedido` (
  `id_pedido` int NOT NULL,
  `id_cliente` int NOT NULL,
  `fecha_pedido` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_entrega` date NOT NULL,
  `hora_entrega` time NOT NULL,
  `estado` enum('pendiente','confirmado','en_proceso','listo','entregado','cancelado') DEFAULT 'pendiente',
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00 CHECK (`subtotal` >= 0),
  `anticipo` decimal(10,2) DEFAULT 0.00 CHECK (`anticipo` >= 0),
  `saldo` decimal(10,2) DEFAULT 0.00 CHECK (`saldo` >= 0),
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Tabla: detalle_pedido
-- --------------------------------------------------------

CREATE TABLE `detalle_pedido` (
  `id_detalle` int NOT NULL,
  `id_pedido` int NOT NULL,
  `id_producto` int NOT NULL,
  `cantidad` int NOT NULL DEFAULT 1 CHECK (`cantidad` > 0),
  `precio_unitario` decimal(10,2) NOT NULL CHECK (`precio_unitario` >= 0),
  `subtotal` decimal(10,2) NOT NULL CHECK (`subtotal` >= 0),
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Tabla: entrega
-- --------------------------------------------------------

CREATE TABLE `entrega` (
  `id_entrega` int NOT NULL,
  `id_pedido` int NOT NULL,
  `direccion_entrega` text NOT NULL,
  `encargado_entrega` varchar(200) DEFAULT NULL,
  `fecha_hora_entrega_real` timestamp NULL DEFAULT NULL,
  `estado_entrega` enum('programada','en_camino','entregada','fallida','cancelada') DEFAULT 'programada',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Índices
-- --------------------------------------------------------

ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id_categoria`),
  ADD KEY `idx_nombre_categoria` (`nombre_categoria`);

ALTER TABLE `cliente`
  ADD PRIMARY KEY (`id_cliente`),
  ADD KEY `idx_cliente_nombre` (`nombre`,`apellido_paterno`),
  ADD KEY `idx_cliente_telefono` (`telefono`);

ALTER TABLE `producto`
  ADD PRIMARY KEY (`id_producto`),
  ADD KEY `idx_producto_nombre` (`nombre`),
  ADD KEY `idx_producto_categoria` (`id_categoria`);

ALTER TABLE `pedido`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `idx_pedido_cliente` (`id_cliente`),
  ADD KEY `idx_pedido_fecha` (`fecha_entrega`),
  ADD KEY `idx_pedido_estado` (`estado`);

ALTER TABLE `detalle_pedido`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_pedido` (`id_pedido`),
  ADD KEY `id_producto` (`id_producto`);

ALTER TABLE `entrega`
  ADD PRIMARY KEY (`id_entrega`),
  ADD UNIQUE KEY `id_pedido` (`id_pedido`),
  ADD KEY `idx_entrega_pedido` (`id_pedido`),
  ADD KEY `idx_entrega_estado` (`estado_entrega`);

-- --------------------------------------------------------
-- AUTO_INCREMENT
-- --------------------------------------------------------

ALTER TABLE `categoria`
  MODIFY `id_categoria` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

ALTER TABLE `cliente`
  MODIFY `id_cliente` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `producto`
  MODIFY `id_producto` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

ALTER TABLE `pedido`
  MODIFY `id_pedido` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `detalle_pedido`
  MODIFY `id_detalle` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `entrega`
  MODIFY `id_entrega` int NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------
-- Foreign Keys
-- --------------------------------------------------------

ALTER TABLE `detalle_pedido`
  ADD CONSTRAINT `detalle_pedido_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id_pedido`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detalle_pedido_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`) ON UPDATE CASCADE;

ALTER TABLE `entrega`
  ADD CONSTRAINT `entrega_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id_pedido`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `pedido`
  ADD CONSTRAINT `pedido_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`) ON UPDATE CASCADE;

ALTER TABLE `producto`
  ADD CONSTRAINT `producto_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`) ON UPDATE CASCADE;

COMMIT;
