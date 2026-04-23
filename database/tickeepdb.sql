-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-04-2026 a las 21:17:57
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `tickeepdb`
--
CREATE DATABASE IF NOT EXISTS `tickeepdb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tickeepdb`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `garantias`
--

CREATE TABLE `garantias` (
  `id_garantia` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `nombre_producto` varchar(150) NOT NULL,
  `tienda` varchar(100) DEFAULT NULL,
  `fecha_compra` date NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `archivo_ticket` varchar(255) DEFAULT NULL,
  `foto_producto` varchar(255) DEFAULT NULL,
  `comentarios` text DEFAULT NULL,
  `estado` enum('Vigente','Expira pronto','Caducada') DEFAULT 'Vigente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `garantias`
--

INSERT INTO `garantias` (`id_garantia`, `id_usuario`, `nombre_producto`, `tienda`, `fecha_compra`, `fecha_vencimiento`, `archivo_ticket`, `foto_producto`, `comentarios`, `estado`) VALUES
(2, 2, 'Auriculares', 'Mediamarkt', '2000-12-12', '2026-04-24', NULL, NULL, NULL, 'Expira pronto'),
(4, 2, 'CAFE', 'ame CAFE TEATRE', '1200-12-12', '2029-10-10', NULL, NULL, 'Tienda detectada: ame CAFE TEATRE | Producto detectado: ch As', 'Vigente'),
(5, 2, 'a', 'CAFE TEATRE', '2012-09-07', '2027-12-12', 'uploads/tickets/ticket_1776970536_2f2df449.jpg', NULL, NULL, 'Vigente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `opciones_configuracion`
--

CREATE TABLE `opciones_configuracion` (
  `id_usuario` int(11) NOT NULL,
  `foto_perfil` varchar(255) DEFAULT 'default_avatar.png',
  `idioma` varchar(5) DEFAULT 'es',
  `tema` enum('claro','oscuro') DEFAULT 'claro',
  `notificaciones_email` tinyint(1) DEFAULT 1,
  `aviso_vencimiento` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `opciones_configuracion`
--

INSERT INTO `opciones_configuracion` (`id_usuario`, `foto_perfil`, `idioma`, `tema`, `notificaciones_email`, `aviso_vencimiento`) VALUES
(2, 'default_avatar.png', 'es', 'claro', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `email` varchar(150) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `contrasena`, `email`, `fecha_registro`) VALUES
(2, 'a', '$2y$10$8Kd0A2DjZlCSEXf5Q2x2s.DgshZkWUMjXB1D0VAq1kMWZHE2lTNua', 'a@a', '2026-04-23 16:52:19');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `garantias`
--
ALTER TABLE `garantias`
  ADD PRIMARY KEY (`id_garantia`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `opciones_configuracion`
--
ALTER TABLE `opciones_configuracion`
  ADD PRIMARY KEY (`id_usuario`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `garantias`
--
ALTER TABLE `garantias`
  MODIFY `id_garantia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `garantias`
--
ALTER TABLE `garantias`
  ADD CONSTRAINT `garantias_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `opciones_configuracion`
--
ALTER TABLE `opciones_configuracion`
  ADD CONSTRAINT `opciones_configuracion_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
