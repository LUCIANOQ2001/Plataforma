-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 20-05-2025 a las 01:27:53
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
-- Base de datos: `plataformavirtual`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `admin`
--

CREATE TABLE `admin` (
  `Codigo` varchar(70) NOT NULL,
  `Nombres` varchar(70) NOT NULL,
  `Apellidos` varchar(70) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `admin`
--

INSERT INTO `admin` (`Codigo`, `Nombres`, `Apellidos`) VALUES
('AC87720821', 'Administrador', 'Principal');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clase`
--

CREATE TABLE `clase` (
  `id` int(7) NOT NULL,
  `Video` text NOT NULL,
  `Fecha` date NOT NULL,
  `Titulo` varchar(535) NOT NULL,
  `Tutor` varchar(100) NOT NULL,
  `Descripcion` text NOT NULL,
  `Adjuntos` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentarios`
--

CREATE TABLE `comentarios` (
  `idc` int(17) NOT NULL,
  `id` int(7) NOT NULL,
  `Fecha` datetime NOT NULL,
  `Comentario` text NOT NULL,
  `Adjunto` varchar(150) NOT NULL,
  `Tipo` varchar(20) NOT NULL,
  `Codigo` varchar(70) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `consultas`
--

CREATE TABLE `consultas` (
  `id` int(7) NOT NULL,
  `CodigoEstudiante` varchar(70) NOT NULL,
  `Fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `Asunto` varchar(255) NOT NULL,
  `Mensaje` text NOT NULL,
  `Estado` enum('pendiente','respondido') NOT NULL DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `consultas`
--

INSERT INTO `consultas` (`id`, `CodigoEstudiante`, `Fecha`, `Asunto`, `Mensaje`, `Estado`) VALUES
(50, 'EC01264993', '2025-05-19 18:09:42', 'PRUEBA', 'probando', 'pendiente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuenta`
--

CREATE TABLE `cuenta` (
  `id` int(7) NOT NULL,
  `Privilegio` int(1) NOT NULL,
  `Usuario` varchar(20) NOT NULL,
  `Clave` varchar(535) NOT NULL,
  `Tipo` varchar(20) NOT NULL,
  `Genero` varchar(15) NOT NULL,
  `Codigo` varchar(70) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `cuenta`
--

INSERT INTO `cuenta` (`id`, `Privilegio`, `Usuario`, `Clave`, `Tipo`, `Genero`, `Codigo`) VALUES
(1, 1, 'Administrador', 'NXVlQVZFeTRBV3pTL1R5WEFGY2dMdz09', 'Administrador', 'Masculino', 'AC87720821'),
(2, 4, 'admin1', 'cDM1Z3V6a1FLTUd4M2xMZklYT3FWZz09', 'Estudiante', 'Masculino', 'EC13876462'),
(3, 4, 'QGONZALEZLUCIAN', 'RHduVEdGR3NrWGt3Z3lEQzVLeTdCZz09', 'Estudiante', 'Masculino', 'EC01264993');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiante`
--

CREATE TABLE `estudiante` (
  `Codigo` varchar(70) NOT NULL,
  `Nombres` varchar(70) NOT NULL,
  `Apellidos` varchar(70) NOT NULL,
  `Email` varchar(70) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `estudiante`
--

INSERT INTO `estudiante` (`Codigo`, `Nombres`, `Apellidos`, `Email`) VALUES
('EC01264993', 'PRUEBA', 'PRUEBA', 'PRUEBA@PRUEBA'),
('EC13876462', 'Luciano', 'Quiroz', 'admin@admin.com');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`Codigo`);

--
-- Indices de la tabla `clase`
--
ALTER TABLE `clase`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  ADD PRIMARY KEY (`idc`),
  ADD KEY `fk_comentarios_clase` (`id`),
  ADD KEY `fk_comentarios_estudiante` (`Codigo`);

--
-- Indices de la tabla `consultas`
--
ALTER TABLE `consultas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_codigo_est` (`CodigoEstudiante`);

--
-- Indices de la tabla `cuenta`
--
ALTER TABLE `cuenta`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `estudiante`
--
ALTER TABLE `estudiante`
  ADD PRIMARY KEY (`Codigo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clase`
--
ALTER TABLE `clase`
  MODIFY `id` int(7) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  MODIFY `idc` int(17) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `consultas`
--
ALTER TABLE `consultas`
  MODIFY `id` int(7) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de la tabla `cuenta`
--
ALTER TABLE `cuenta`
  MODIFY `id` int(7) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `comentarios`
--
ALTER TABLE `comentarios`
  ADD CONSTRAINT `fk_comentarios_clase` FOREIGN KEY (`id`) REFERENCES `clase` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_comentarios_estudiante` FOREIGN KEY (`Codigo`) REFERENCES `estudiante` (`Codigo`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `consultas`
--
ALTER TABLE `consultas`
  ADD CONSTRAINT `fk_consultas_estudiante` FOREIGN KEY (`CodigoEstudiante`) REFERENCES `estudiante` (`Codigo`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
