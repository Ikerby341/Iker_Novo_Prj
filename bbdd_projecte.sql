-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 26-01-2026 a las 17:53:18
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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `coches`
--

CREATE DATABASE IF NOT EXISTS `bbdd_projecte` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `bbdd_projecte`;

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
(3, 'Seat', 'Leon', 1, 'public/assets/img/leon.webp'),
(4, 'Audi', 'RS6', 1, 'public/assets/img/RS6_17688469704c5e1d08.webp'),
(5, 'Volkswagen', 'Passat', 1, 'public/assets/img/passat_176530010042bd42a5.png'),
(6, 'Porsche', 'Panamera', 1, 'public/assets/img/panamera_17653008134cb77a97.png'),
(15, 'Seat', 'Arona', 2, 'public/assets/img/arona_176537673606019567.webp'),
(16, 'BMW', 'M3', 2, 'public/assets/img/m3_17653769398e85a483.png'),
(17, 'Mercedes', 'A 45 AMG', 2, 'public/assets/img/a45_1765377036f23f1edc.png'),
(18, 'Peugeot', '208', 2, 'public/assets/img/208_1765378671cdab29df.png'),
(19, 'Seat', 'Ibiza', 1, 'public/assets/img/ibiza.webp'),
(20, 'Volkswagen', 'Scirocco', 1, 'public/assets/img/scirocco_176529885739002241.webp'),
(21, 'Volkswagen', 'Beetle', 1, 'public/assets/img/beetle_1765300716bdcfaafa.webp'),
(24, 'Cupra', 'Leon', 5, 'public/assets/img/leon_1765381183645e8d08.jpg'),
(25, 'Cupra', 'Formentor', 5, 'public/assets/img/formentor_176538113320c8bf38.jpg'),
(26, 'Cupra', 'Terramar', 5, 'public/assets/img/terramar_176538123024749b0b.webp'),
(27, 'Cupra', 'Tavascan', 5, 'public/assets/img/tavascan_176538128288ed354a.jpg'),
(28, 'Cupra', 'Born', 5, 'public/assets/img/born_17653813388c8f107e.webp'),
(29, 'Cupra', 'Ateca', 5, 'public/assets/img/ateca_1765381369c7266e1d.jpg'),
(33, 'Audi', 'RS3', 1, 'public/assets/img/rs3_1768847027e1c63fec.jpg'),
(34, 'Audi', 'A1', 1, 'public/assets/img/A1_1768846772953ff9dd.jpg'),
(35, 'Audi', 'RS4', 1, 'public/assets/img/RS4_17688468221329d70c.jpg'),
(36, 'Audi', 'S5', 1, 'public/assets/img/S5_17688468693aae180a.webp'),
(37, 'Audi', 'R8', 1, 'public/assets/img/R8_1768846894238fb13b.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` text NOT NULL,
  `email` text NOT NULL,
  `password` text NOT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `admin` tinyint(1) NOT NULL DEFAULT 0,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `username`, `email`, `password`, `remember_token`, `admin`, `reset_token`, `reset_token_expires`) VALUES
(1, 'admin', 'admin@gmail.com', '$2y$10$fQWkDQQnuk6WmX9bE1vXu.l0DIPeaPNA4b.xSWI9WNjBu6z52p6JW', NULL, 1, NULL, NULL),
(2, 'Mindundi', 'mindundi@gmail.com', '$2y$10$nnfAp97aXeI9iEx8yjWHUeATxTtog02ApC/9Go.JN2zSb6abtbEqi', NULL, 0, NULL, NULL),
(5, 'Cupra', 'cupra@gmail.com', '$2y$10$t.cIM.pCrZ.GbhB7J4PrsedT/5zOtRAyfhMeDcSZDlUldExOPSRA6', NULL, 0, NULL, NULL),
(6, 'Hola', 'hola@gmail.com', '$2y$10$b9qXEVk7yVlRlR0McpzaieVMYJM61pJd.Ge3Lhdz907qm4p6hyyEK', NULL, 0, NULL, NULL),
(8, 'inovo', 'i.novo@sapalomera.cat', '$2y$10$ITgzfjcmR7X4OamruDwBIOdCN/Nts04.wy/OxZOjQBdeOCu/.uQ8S', NULL, 0, NULL, NULL);

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
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

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
