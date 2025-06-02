-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 03-06-2025 a las 01:56:12
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
('AC080139916', 'PRUEBA DOCENTE', 'PRUEBA'),
('AC747142713', 'PRUEBAAAA', 'AAAAAA'),
('AC814654411', 'adddddmon', 'admon');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `anuncio`
--

CREATE TABLE `anuncio` (
  `id` int(11) NOT NULL,
  `CursoId` int(11) NOT NULL,
  `Titulo` varchar(255) NOT NULL,
  `Contenido` text NOT NULL,
  `Fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `anuncio`
--

INSERT INTO `anuncio` (`id`, `CursoId`, `Titulo`, `Contenido`, `Fecha`) VALUES
(3, 12, '12', '12', '2025-05-22 01:13:35'),
(4, 23, 'ANUNCIO 1', 'HOLA FAVOR', '2025-05-27 09:36:54'),
(5, 24, 'Anuncio1', 'anuncio', '2025-05-28 18:31:32'),
(6, 23, 'Anuncio2', 'anuncio2', '2025-05-28 18:31:57'),
(7, 24, 'ANUNCIOOOO', 'HOLA QUE ONDA', '2025-05-29 11:47:47'),
(8, 23, 'ANUNCIOOOO', 'HOLAAAAAAAA', '2025-05-29 11:48:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia`
--

CREATE TABLE `asistencia` (
  `id` int(7) NOT NULL,
  `sesion_id` int(11) NOT NULL,
  `estudiante` varchar(70) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` enum('presente','ausente','justificado') NOT NULL DEFAULT 'presente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `asistencia`
--

INSERT INTO `asistencia` (`id`, `sesion_id`, `estudiante`, `fecha`, `estado`) VALUES
(1, 8, '682fa098dcf14', '2025-05-28 01:16:41', 'justificado'),
(2, 7, '682fa098dcf14', '2025-05-28 01:16:52', 'presente');

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
(4, '12222', '2025-05-22', '1234', 'prueba5', '1234', ''),
(5, 'REDES', '2025-05-26', 'REDES', 'REDES', '', 'sanchez-shapiama.docx,QUIROZ-LACERNA-EA01_(1).pdf'),
(7, '4444', '2025-05-27', '1244', '44444', '', '');

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
(6, 2, 'test', 'cDM1Z3V6a1FLTUd4M2xMZklYT3FWZz09', 'Docente', 'Masculino', 'AC50913775'),
(8, 1, 'test2', 'MUtkT2RKUjVwNTBuNGg5UWRPTmR2QT09', 'Administrador', 'Masculino', 'AC32636467'),
(9, 2, 'testp', 'cDM1Z3V6a1FLTUd4M2xMZklYT3FWZz09', 'Docente', 'Femenino', 'AC33902998'),
(10, 2, 'test3', 'cDM1Z3V6a1FLTUd4M2xMZklYT3FWZz09', 'Docente', 'Masculino', 'AC79691459'),
(11, 2, 'docente', 'RHduVEdGR3NrWGt3Z3lEQzVLeTdCZz09', 'Docente', 'Masculino', 'AC509395110'),
(12, 2, 'test1', 'MUtkT2RKUjVwNTBuNGg5UWRPTmR2QT09', 'Docente', 'Masculino', 'AC744869210'),
(24, 2, 'admon01', 'RHduVEdGR3NrWGt3Z3lEQzVLeTdCZz09', 'Docente', 'Masculino', 'AC814654411'),
(25, 4, 'admon03', '$2y$10$ahwDxyarsgJcbAUyjwmegOYpLUHYnU.DMNNyCVqTs5RhqbHxxo6yy', 'Estudiante', 'Masculino', '682f9eb545009'),
(26, 2, 'admin04', 'RHduVEdGR3NrWGt3Z3lEQzVLeTdCZz09', 'Docente', 'Femenino', 'AC747142713'),
(27, 4, 'admin05', 'RHduVEdGR3NrWGt3Z3lEQzVLeTdCZz09', 'Estudiante', 'Masculino', '682fa098dcf14'),
(28, 4, '12345', '$2y$10$l305cZKQUu/vu1oSmpZu9OjP7xGWckVzhHso7YpeJg4jxuzdR/tQW', 'Estudiante', 'Masculino', '683094028f860'),
(29, 2, 'prueba01', 'RHduVEdGR3NrWGt3Z3lEQzVLeTdCZz09', 'Docente', 'Masculino', 'AC080139916');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `curso`
--

CREATE TABLE `curso` (
  `id` int(11) NOT NULL,
  `Nombre` varchar(255) NOT NULL,
  `Descripcion` text NOT NULL,
  `DocenteCodigo` varchar(70) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `curso`
--

INSERT INTO `curso` (`id`, `Nombre`, `Descripcion`, `DocenteCodigo`) VALUES
(12, 'INVESTI', 'ADAD', 'AC50913775'),
(13, 'INVESTI', 'ADAD', 'AC50913775'),
(14, 'REDES', 'Esta clase dictará redes e informatica', 'AC68948906'),
(15, 'REDES', 'Esta clase dictará redes', 'AC744869210'),
(16, 'INFORMATICA', 'SE DICTARA INFO', 'AC33902998'),
(17, 'INFORMATICA', 'SE DICTARA INFO', 'AC33902998'),
(18, 'INFOOOO', '124', 'AC813028115'),
(23, 'holasoy', 'hola', 'AC747142713'),
(24, 'CURSO PRUEBA', 'HOLA PROBANDO', 'AC747142713'),
(25, 'PROBANDO CURSOOO', 'ANTE TODO BUENAS TARDES', 'AC080139916');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `curso_estudiante`
--

CREATE TABLE `curso_estudiante` (
  `id` int(11) NOT NULL,
  `CursoId` int(11) NOT NULL,
  `EstudianteCodigo` varchar(70) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `curso_estudiante`
--

INSERT INTO `curso_estudiante` (`id`, `CursoId`, `EstudianteCodigo`) VALUES
(8, 17, '683094028f860'),
(7, 18, '682fa098dcf14'),
(6, 23, '682fa098dcf14');

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
('AC080139916', 'prueba01', 'prueba01', ''),
('AC33902998', 'testp', 'testp', ''),
('AC50913775', 'test', 'test', ''),
('AC68948906', 'test1', 'test1', ''),
('AC744869210', 'test1', 'test1', ''),
('AC747142713', 'admin04', 'admin04', ''),
('AC813028115', 'prueba5', 'prueba5', '');

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
('682fa098dcf14', 'LUCHANO', 'quiroz', 'asd@gmail.com'),
('683094028f860', 'LUCIANO', 'LUCIANO', 'LUCIANO3@GMAIL.COM');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evaluacion`
--

CREATE TABLE `evaluacion` (
  `id` int(11) NOT NULL,
  `SesionId` int(11) NOT NULL,
  `Titulo` varchar(255) NOT NULL,
  `FechaCreacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `evaluacion`
--

INSERT INTO `evaluacion` (`id`, `SesionId`, `Titulo`, `FechaCreacion`) VALUES
(1, 7, 'Evaluación 1', '2025-06-02 18:15:39'),
(2, 8, 'Evaluación 2', '2025-06-02 18:46:11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `foro`
--

CREATE TABLE `foro` (
  `id` int(11) NOT NULL,
  `sesion_id` int(11) NOT NULL,
  `Titulo` varchar(255) NOT NULL,
  `Descripcion` text NOT NULL,
  `FechaSubida` datetime NOT NULL DEFAULT current_timestamp(),
  `FechaCierre` datetime DEFAULT NULL,
  `Archivo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `foro`
--

INSERT INTO `foro` (`id`, `sesion_id`, `Titulo`, `Descripcion`, `FechaSubida`, `FechaCierre`, `Archivo`) VALUES
(1, 4, 'FORO 1', 'aasdas', '2025-05-22 00:44:56', NULL, 'Introducción.pdf');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `foro_comentario`
--

CREATE TABLE `foro_comentario` (
  `id` int(11) NOT NULL,
  `ForoId` int(11) NOT NULL,
  `UsuarioCodigo` varchar(70) NOT NULL,
  `Comentario` text NOT NULL,
  `Adjunto` varchar(255) DEFAULT NULL,
  `Fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `foro_comentario`
--

INSERT INTO `foro_comentario` (`id`, `ForoId`, `UsuarioCodigo`, `Comentario`, `Adjunto`, `Fecha`) VALUES
(1, 1, 'AC50913775', 'aaa', NULL, '2025-05-22 00:45:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grabacion`
--

CREATE TABLE `grabacion` (
  `id` int(11) NOT NULL,
  `sesion_id` int(11) NOT NULL,
  `archivo` varchar(255) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `material`
--

CREATE TABLE `material` (
  `id` int(11) NOT NULL,
  `sesion_id` int(11) NOT NULL,
  `Titulo` varchar(255) NOT NULL,
  `Archivo` varchar(255) NOT NULL,
  `Fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `material`
--

INSERT INTO `material` (`id`, `sesion_id`, `Titulo`, `Archivo`, `Fecha`) VALUES
(12, 7, '124234', '1748383588_QUIROZ-LACERNA-EA01__1_.pdf', '2025-05-27 17:06:28'),
(13, 8, 'Material2', '1748383634_QUIROZ-LACERNA-TRABAJO_DE_INVESTIGACION-solocontenido__1_.pdf', '2025-05-27 17:07:14'),
(14, 7, 'subiendo', '1748384314_Peering_redes_virtual_en_Azure.jpg', '2025-05-27 17:18:34'),
(15, 9, 'ANTE TODO MUY BUENAS TARDES', '1748384673_QUIROZ-LACERNA-EA01__1_.pdf', '2025-05-27 17:24:33'),
(16, 10, 'ESTUDIEN ESTOOO', '1748384895_QUIROZ-LACERNA-EA01__1_.pdf', '2025-05-27 17:28:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `opcion`
--

CREATE TABLE `opcion` (
  `id` int(11) NOT NULL,
  `PreguntaId` int(11) NOT NULL,
  `TextoOpcion` varchar(255) NOT NULL,
  `EsCorrecta` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `opcion`
--

INSERT INTO `opcion` (`id`, `PreguntaId`, `TextoOpcion`, `EsCorrecta`) VALUES
(1, 1, 'Si', 1),
(2, 3, 'Si', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pregunta`
--

CREATE TABLE `pregunta` (
  `id` int(11) NOT NULL,
  `EvaluacionId` int(11) NOT NULL,
  `TextoPregunta` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `pregunta`
--

INSERT INTO `pregunta` (`id`, `EvaluacionId`, `TextoPregunta`) VALUES
(1, 1, 'Eres Gay'),
(3, 2, '¿Eres furro?');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuesta_estudiante`
--

CREATE TABLE `respuesta_estudiante` (
  `id` int(11) NOT NULL,
  `EvaluacionId` int(11) NOT NULL,
  `EstudianteCodigo` varchar(70) NOT NULL,
  `PreguntaId` int(11) NOT NULL,
  `OpcionElegidaId` int(11) NOT NULL,
  `Fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `respuesta_estudiante`
--

INSERT INTO `respuesta_estudiante` (`id`, `EvaluacionId`, `EstudianteCodigo`, `PreguntaId`, `OpcionElegidaId`, `Fecha`) VALUES
(1, 1, '682fa098dcf14', 1, 1, '2025-06-02 18:36:16'),
(2, 2, '682fa098dcf14', 3, 2, '2025-06-02 18:46:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resultado`
--

CREATE TABLE `resultado` (
  `id` int(11) NOT NULL,
  `EvaluacionId` int(11) NOT NULL,
  `EstudianteCodigo` varchar(70) NOT NULL,
  `Nota` decimal(5,2) NOT NULL,
  `Fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `resultado`
--

INSERT INTO `resultado` (`id`, `EvaluacionId`, `EstudianteCodigo`, `Nota`, `Fecha`) VALUES
(1, 1, '682fa098dcf14', 20.00, '2025-06-02 18:36:16'),
(2, 2, '682fa098dcf14', 0.00, '2025-06-02 18:46:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesion`
--

CREATE TABLE `sesion` (
  `id` int(11) NOT NULL,
  `CursoId` int(11) NOT NULL,
  `Titulo` varchar(255) NOT NULL,
  `Fecha` datetime NOT NULL,
  `Video` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `sesion`
--

INSERT INTO `sesion` (`id`, `CursoId`, `Titulo`, `Fecha`, `Video`) VALUES
(1, 13, 'SESÓN 1', '2025-06-12 00:00:00', ''),
(2, 13, 'A', '2025-05-17 00:00:00', ''),
(3, 13, 'SESIÓN 2', '2025-05-31 00:00:00', 'https://meet.google.com/wfr-gpjv-jhc?authuser=0'),
(4, 12, 'SESIÓN 01', '2025-05-25 00:00:00', 'https://meet.google.com/landing?authuser=0'),
(5, 13, 'SEXOOOO', '2025-05-26 00:00:00', 'https://meet.google.com/vkx-biks-oku'),
(6, 12, 'SEXOOO', '2025-05-27 00:00:00', 'https://quillbot.com/es/parafrasear'),
(7, 23, 'SESIÓN 01', '2025-05-30 00:00:00', 'https://www.youtube.com/'),
(8, 23, 'SESIÓN 02', '2025-06-24 00:00:00', '1245'),
(9, 24, 'SESIÓN 01', '2025-05-31 00:00:00', ''),
(10, 25, 'PRIMERO LA EDUCACIÓN XD', '2025-05-31 00:00:00', 'https://workspace.google.com/products/meet/'),
(11, 24, 'SESIÓN 02', '2025-05-18 00:00:00', '');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`Codigo`);

--
-- Indices de la tabla `anuncio`
--
ALTER TABLE `anuncio`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_anuncio_curso` (`CursoId`);

--
-- Indices de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_asistencia_sesion_estudiante` (`sesion_id`,`estudiante`),
  ADD KEY `fk_asistencia_sesion` (`sesion_id`),
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
-- Indices de la tabla `curso`
--
ALTER TABLE `curso`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_curso_docente` (`DocenteCodigo`);

--
-- Indices de la tabla `curso_estudiante`
--
ALTER TABLE `curso_estudiante`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_curso_estudiante` (`CursoId`,`EstudianteCodigo`),
  ADD KEY `fk_ce_curso` (`CursoId`),
  ADD KEY `fk_ce_estudiante` (`EstudianteCodigo`);

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
-- Indices de la tabla `evaluacion`
--
ALTER TABLE `evaluacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_evaluacion_sesion` (`SesionId`);

--
-- Indices de la tabla `foro`
--
ALTER TABLE `foro`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_foro_sesion` (`sesion_id`);

--
-- Indices de la tabla `foro_comentario`
--
ALTER TABLE `foro_comentario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_fc_foro` (`ForoId`);

--
-- Indices de la tabla `grabacion`
--
ALTER TABLE `grabacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_grab_sesion` (`sesion_id`);

--
-- Indices de la tabla `material`
--
ALTER TABLE `material`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_material_sesion` (`sesion_id`);

--
-- Indices de la tabla `opcion`
--
ALTER TABLE `opcion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_opcion_pregunta` (`PreguntaId`);

--
-- Indices de la tabla `pregunta`
--
ALTER TABLE `pregunta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pregunta_evaluacion` (`EvaluacionId`);

--
-- Indices de la tabla `respuesta_estudiante`
--
ALTER TABLE `respuesta_estudiante`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_re_eval` (`EvaluacionId`),
  ADD KEY `fk_re_pregunta` (`PreguntaId`),
  ADD KEY `fk_re_opcion` (`OpcionElegidaId`);

--
-- Indices de la tabla `resultado`
--
ALTER TABLE `resultado`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_res_eval` (`EvaluacionId`);

--
-- Indices de la tabla `sesion`
--
ALTER TABLE `sesion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sesion_curso` (`CursoId`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `anuncio`
--
ALTER TABLE `anuncio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  MODIFY `id` int(7) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `clase`
--
ALTER TABLE `clase`
  MODIFY `id` int(7) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  MODIFY `idc` int(17) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `consultas`
--
ALTER TABLE `consultas`
  MODIFY `id` int(7) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT de la tabla `cuenta`
--
ALTER TABLE `cuenta`
  MODIFY `id` int(7) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `curso`
--
ALTER TABLE `curso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `curso_estudiante`
--
ALTER TABLE `curso_estudiante`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `evaluacion`
--
ALTER TABLE `evaluacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `foro`
--
ALTER TABLE `foro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `foro_comentario`
--
ALTER TABLE `foro_comentario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `grabacion`
--
ALTER TABLE `grabacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `material`
--
ALTER TABLE `material`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `opcion`
--
ALTER TABLE `opcion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `pregunta`
--
ALTER TABLE `pregunta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `respuesta_estudiante`
--
ALTER TABLE `respuesta_estudiante`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `resultado`
--
ALTER TABLE `resultado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `sesion`
--
ALTER TABLE `sesion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `anuncio`
--
ALTER TABLE `anuncio`
  ADD CONSTRAINT `fk_anuncio_curso` FOREIGN KEY (`CursoId`) REFERENCES `curso` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD CONSTRAINT `fk_asistencia_est` FOREIGN KEY (`estudiante`) REFERENCES `estudiante` (`Codigo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_asistencia_sesion` FOREIGN KEY (`sesion_id`) REFERENCES `sesion` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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

--
-- Filtros para la tabla `curso`
--
ALTER TABLE `curso`
  ADD CONSTRAINT `fk_curso_docente` FOREIGN KEY (`DocenteCodigo`) REFERENCES `docente` (`Codigo`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `curso_estudiante`
--
ALTER TABLE `curso_estudiante`
  ADD CONSTRAINT `fk_ce_curso` FOREIGN KEY (`CursoId`) REFERENCES `curso` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ce_estudiante` FOREIGN KEY (`EstudianteCodigo`) REFERENCES `estudiante` (`Codigo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `evaluacion`
--
ALTER TABLE `evaluacion`
  ADD CONSTRAINT `fk_evaluacion_sesion` FOREIGN KEY (`SesionId`) REFERENCES `sesion` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `foro`
--
ALTER TABLE `foro`
  ADD CONSTRAINT `fk_foro_sesion` FOREIGN KEY (`sesion_id`) REFERENCES `sesion` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `foro_comentario`
--
ALTER TABLE `foro_comentario`
  ADD CONSTRAINT `fk_fc_foro` FOREIGN KEY (`ForoId`) REFERENCES `foro` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `grabacion`
--
ALTER TABLE `grabacion`
  ADD CONSTRAINT `fk_grab_sesion` FOREIGN KEY (`sesion_id`) REFERENCES `sesion` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `material`
--
ALTER TABLE `material`
  ADD CONSTRAINT `fk_material_sesion` FOREIGN KEY (`sesion_id`) REFERENCES `sesion` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `opcion`
--
ALTER TABLE `opcion`
  ADD CONSTRAINT `fk_opcion_pregunta` FOREIGN KEY (`PreguntaId`) REFERENCES `pregunta` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `pregunta`
--
ALTER TABLE `pregunta`
  ADD CONSTRAINT `fk_pregunta_evaluacion` FOREIGN KEY (`EvaluacionId`) REFERENCES `evaluacion` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `respuesta_estudiante`
--
ALTER TABLE `respuesta_estudiante`
  ADD CONSTRAINT `fk_re_eval` FOREIGN KEY (`EvaluacionId`) REFERENCES `evaluacion` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_re_opcion` FOREIGN KEY (`OpcionElegidaId`) REFERENCES `opcion` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_re_pregunta` FOREIGN KEY (`PreguntaId`) REFERENCES `pregunta` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `resultado`
--
ALTER TABLE `resultado`
  ADD CONSTRAINT `fk_res_eval` FOREIGN KEY (`EvaluacionId`) REFERENCES `evaluacion` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `sesion`
--
ALTER TABLE `sesion`
  ADD CONSTRAINT `fk_sesion_curso` FOREIGN KEY (`CursoId`) REFERENCES `curso` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
