-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-12-2025 a las 16:15:23
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `bbdd_projecte`
--

CREATE DATABASE bbdd_projecte;
USE bbdd_projecte;


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `coches`
--

CREATE TABLE `coches` (
  `ID` int(10) UNSIGNED NOT NULL,
  `marca` text NOT NULL,
  `model` text NOT NULL,
  `owner_id` int(10) UNSIGNED NOT NULL,
  `ruta_img` text DEFAULT 'public/assets/img/default.webp'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `coches`
--

INSERT INTO `coches` (`ID`, `marca`, `model`, `owner_id`, `ruta_img`) VALUES
(3, 'Seat', 'Leon', 1, 'public/assets/img/default.webp'),
(4, 'Audi', 'RS6', 1, 'public/assets/img/default.webp'),
(5, 'Volkswagen', 'Passat', 1, 'public/assets/img/default.webp'),
(6, 'Porsche', 'Panamera', 1, 'public/assets/img/default.webp'),
(15, 'Seat', 'Arona', 2, 'public/assets/img/default.webp'),
(16, 'BMW', 'M3', 2, 'public/assets/img/default.webp'),
(17, 'Mercedes', 'A 45 AMG', 2, 'public/assets/img/default.webp'),
(18, 'Peugeot', '208', 2, 'public/assets/img/default.webp'),
(19, 'Seat', 'Ibiza', 1, 'public/assets/img/ibiza.webp');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` text NOT NULL,
  `email` text NOT NULL,
  `password` text NOT NULL,
  `remember_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `username`, `email`, `password`, `remember_token`) VALUES
(1, 'admin', 'admin@gmail.com', '$2y$10$Y3cK2kCahAPJ250Bq1jPlevaz0Ez8xihQ9uIXl/IaYOjNTNu653A2', '1123956af157d3a8e61543d418893c3636527a3d16404d041247b945e9d097c6'),
(2, 'Mindundi', 'mindundi@genil.com', '$2y$10$nnfAp97aXeI9iEx8yjWHUeATxTtog02ApC/9Go.JN2zSb6abtbEqi', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `coches`
--
ALTER TABLE `coches`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unico` (`username`) USING HASH,
  ADD UNIQUE KEY `correounico` (`email`) USING HASH;

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `coches`
--
ALTER TABLE `coches`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `coches`
--
ALTER TABLE `coches`
  ADD CONSTRAINT `fk_coches_owner` FOREIGN KEY (`owner_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
