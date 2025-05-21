-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-05-2025 a las 01:32:40
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
('AC25250328', 'Docentedos', 'docenteeedos'),
('AC48624827', 'Docente', 'docenteee'),
('AC78352785', 'Pruebados', 'Pruebados'),
('AC82248384', 'Luciano', 'Quiroz');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia`
--

CREATE TABLE `asistencia` (
  `id` int(7) NOT NULL,
  `clase_id` int(7) NOT NULL,
  `estudiante` varchar(70) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` enum('presente','ausente','justificado') NOT NULL DEFAULT 'presente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `asistencia`
--

INSERT INTO `asistencia` (`id`, `clase_id`, `estudiante`, `fecha`, `estado`) VALUES
(7, 1, 'EC01264993', '2025-05-20 13:31:12', 'justificado'),
(8, 1, 'EC87644404', '2025-05-20 13:31:12', 'justificado'),
(9, 1, 'EC13876462', '2025-05-20 13:31:12', 'justificado'),
(10, 2, 'EC01264993', '2025-05-20 16:56:15', 'presente'),
(11, 2, 'EC87644404', '2025-05-20 16:56:15', 'presente'),
(12, 2, 'EC13876462', '2025-05-20 16:56:15', 'presente');

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

--
-- Volcado de datos para la tabla `clase`
--

INSERT INTO `clase` (`id`, `Video`, `Fecha`, `Titulo`, `Tutor`, `Descripcion`, `Adjuntos`) VALUES
(1, 'https://meet.google.com/vkx-biks-oku', '2025-05-23', 'SESIÓN 02 - REDES E INFORMÁTICA', 'DOCENTE UNO', 'Esta es una prueba&nbsp;', 'FORMATO_ARTÍCULO-QUIROZ-LACERNA_(2)_(1).docx,Introducción.pdf'),
(2, '457894612', '2025-05-31', 'Clase 02', 'Victor', 'Hoalaaaaaaaaaaa', ''),
(3, 'GAAAAAAAA', '2025-06-25', 'CLASECUATRO', 'TUESTA VICTOR', 'GAAA', '');

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
(2, 4, 'admin1', 'cDM1Z3V6a1FLTUd4M2xMZklYT3FWZz09', 'Estudiante', 'Masculino', 'EC13876462'),
(3, 4, 'QGONZALEZLUCIAN', 'RHduVEdGR3NrWGt3Z3lEQzVLeTdCZz09', 'Estudiante', 'Masculino', 'EC01264993'),
(4, 1, 'admin', 'cDM1Z3V6a1FLTUd4M2xMZklYT3FWZz09', 'Administrador', 'Masculino', 'AC82248384'),
(5, 4, 'ADMIN12', 'dW4vMkhZV1oyd0xuOFgzU29LRHVmUT09', 'Estudiante', 'Masculino', 'EC87644404'),
(6, 1, 'QGONZALESLUCIAN', 'RHduVEdGR3NrWGt3Z3lEQzVLeTdCZz09', 'Administrador', 'Masculino', 'AC78352785'),
(8, 2, 'mperez', '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918', 'Docente', 'Femenino', 'DC00123456'),
(9, 1, 'pruebatres', 'RHduVEdGR3NrWGt3Z3lEQzVLeTdCZz09', 'Administrador', 'Masculino', 'AC48624827'),
(10, 2, 'pruebacuatro', 'RHduVEdGR3NrWGt3Z3lEQzVLeTdCZz09', 'Docente', 'Femenino', 'AC25250328');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `docente`
--

CREATE TABLE `docente` (
  `Codigo` varchar(70) NOT NULL,
  `Nombres` varchar(70) NOT NULL,
  `Apellidos` varchar(70) NOT NULL,
  `Email` varchar(70) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `docente`
--

INSERT INTO `docente` (`Codigo`, `Nombres`, `Apellidos`, `Email`) VALUES
('DC00123456', 'María', 'Pérez', 'mperez@instituto.edu');

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
('EC13876462', 'Luciano', 'Quiroz', 'admin@admin.com'),
('EC87644404', 'PRUEBA', 'PRUEBA', 'PRUEBA@GMAIL.COM');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`Codigo`);

--
-- Indices de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_asistencia_clase` (`clase_id`),
  ADD KEY `fk_asistencia_est` (`estudiante`);

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
-- Indices de la tabla `docente`
--
ALTER TABLE `docente`
  ADD PRIMARY KEY (`Codigo`);

--
-- Indices de la tabla `estudiante`
--
ALTER TABLE `estudiante`
  ADD PRIMARY KEY (`Codigo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  MODIFY `id` int(7) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `clase`
--
ALTER TABLE `clase`
  MODIFY `id` int(7) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `id` int(7) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD CONSTRAINT `fk_asistencia_clase` FOREIGN KEY (`clase_id`) REFERENCES `clase` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_asistencia_est` FOREIGN KEY (`estudiante`) REFERENCES `estudiante` (`Codigo`) ON UPDATE CASCADE;

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
