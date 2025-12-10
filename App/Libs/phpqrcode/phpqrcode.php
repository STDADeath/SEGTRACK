-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 05-12-2025 a las 16:35:41
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
-- Base de datos: `dbsegtrack`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bitacora`
--

CREATE TABLE `bitacora` (
  `IdBitacora` int(100) NOT NULL,
  `TurnoBitacora` enum('Jornada mañana','Jornada tarde','Jornada nocturna') NOT NULL,
  `NovedadesBitacora` varchar(500) DEFAULT NULL,
  `FechaBitacora` datetime NOT NULL DEFAULT current_timestamp(),
  `Estado` enum('Activo','Inactivo') NOT NULL,
  `IdFuncionario` int(11) DEFAULT NULL,
  `IdIngreso` int(11) NOT NULL,
  `IdDispositivo` int(11) DEFAULT NULL,
  `IdVisitante` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dispositivo`
--

CREATE TABLE `dispositivo` (
  `IdDispositivo` int(11) NOT NULL,
  `QrDispositivo` varchar(100) NOT NULL,
  `TipoDispositivo` enum('Computador','Tablet','Portátil','Otro') NOT NULL,
  `MarcaDispositivo` varchar(100) NOT NULL,
  `Estado` enum('Activo','Inactivo') NOT NULL,
  `IdFuncionario` int(11) DEFAULT NULL,
  `IdVisitante` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `dispositivo`
--

INSERT INTO `dispositivo` (`IdDispositivo`, `QrDispositivo`, `TipoDispositivo`, `MarcaDispositivo`, `Estado`, `IdFuncionario`, `IdVisitante`) VALUES
(27, 'codigo_Qr/Codigo_Qr_DispQR-DISP-27-69167f2745bd3.png', 'Portátil', 'Lenovo', 'Activo', 25, NULL),
(28, 'codigo_Qr/Codigo_Qr_DispQR-DISP-28-69167f6792a1d.png', 'Portátil', 'Assus', 'Activo', 26, NULL),
(29, 'Codigo_Qr_Disp/QR-DISP-29-69167ff3c4e2e.png', 'Portátil', 'HUAWEI', 'Activo', 24, NULL),
(30, 'qr/Qr_Dipo/QR-DISP-30-691fb8063ba25.png', 'Portátil', 'Gigabyte', 'Activo', 27, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dotacion`
--

CREATE TABLE `dotacion` (
  `IdDotacion` int(11) NOT NULL,
  `EstadoDotacion` enum('Buen estado','Regular','Dañado') NOT NULL,
  `TipoDotacion` enum('Uniforme','Equipo','Herramienta','Otro') NOT NULL,
  `NovedadDotacion` varchar(500) NOT NULL,
  `FechaDevolucion` datetime NOT NULL DEFAULT current_timestamp(),
  `FechaEntrega` datetime NOT NULL DEFAULT current_timestamp(),
  `Estado` enum('Activo','Inactivo') NOT NULL,
  `IdFuncionario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `dotacion`
--

INSERT INTO `dotacion` (`IdDotacion`, `EstadoDotacion`, `TipoDotacion`, `NovedadDotacion`, `FechaDevolucion`, `FechaEntrega`, `Estado`, `IdFuncionario`) VALUES
(12, 'Buen estado', 'Equipo', 'buena', '2025-12-26 23:22:00', '2025-12-01 11:31:00', 'Activo', 23);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `funcionario`
--

CREATE TABLE `funcionario` (
  `IdFuncionario` int(11) NOT NULL,
  `CargoFuncionario` enum('Personal Seguridad','Funcionario') NOT NULL,
  `QrCodigoFuncionario` varchar(100) NOT NULL,
  `NombreFuncionario` text NOT NULL,
  `TelefonoFuncionario` int(10) NOT NULL,
  `DocumentoFuncionario` bigint(10) NOT NULL,
  `CorreoFuncionario` varchar(100) NOT NULL,
  `Estado` enum('Activo','Inactivo') NOT NULL,
  `IdSede` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `funcionario`
--

INSERT INTO `funcionario` (`IdFuncionario`, `CargoFuncionario`, `QrCodigoFuncionario`, `NombreFuncionario`, `TelefonoFuncionario`, `DocumentoFuncionario`, `CorreoFuncionario`, `Estado`, `IdSede`) VALUES
(23, 'Personal Seguridad', 'QR-FUNC-23-69115d0caa06a.png', 'Camilo Carrillo', 2147483647, 1024787936, 'pasachoa2002@gmail.com', 'Activo', 25),
(24, '', 'QR-FUNC-24-69115d8d6d39a.png', 'Maicol Montoya', 2147483647, 1089546523, 'maicol@gmail.com', 'Activo', 27),
(25, 'Funcionario', 'QR-FUNC-25-69115e1b196b8.png', 'Wendy Rodriguez', 2147483647, 1032458796, 'Wendy@gmail.com', 'Activo', 28),
(26, 'Funcionario', 'QR-FUNC-26-69115e7929831.png', 'Laura Gómez Rincón', 2147483647, 3124589076, 'aura.gomez@udistrital.edu.co', 'Activo', 30),
(27, 'Personal Seguridad', 'QR-FUNC-27-69115ebf23b56.png', 'Daniela Herrera Morales', 2147483647, 1004983721, 'daniela.herrera@gmail.com', 'Activo', 26),
(28, 'Personal Seguridad', 'QR-FUNC-28-69115ef11a339.png', 'Carlos Medina', 2147483647, 1145632298, 'carlos.medina@gmail.com', 'Activo', 28),
(29, '', 'qr/Qr_Func/QR-FUNC-29-692cbd76f3e68.png', 'pedrooo', 2147483647, 23433338998, 'pedro@gmail.com', 'Activo', 29),
(30, '', 'qr/Qr_Func/QR-FUNC-30-692e646f9d721.png', 'pedrooo', 2147483647, 23433338998, 'pedro@gmail.com', 'Activo', 33);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ingreso`
--

CREATE TABLE `ingreso` (
  `IdIngreso` int(11) NOT NULL,
  `ObservacionesIngreso` varchar(500) NOT NULL,
  `TipoMovimiento` enum('Entrada','Salida') NOT NULL,
  `FechaIngreso` datetime NOT NULL DEFAULT current_timestamp(),
  `Estado` enum('Activo','Inactivo') NOT NULL,
  `IdSede` int(11) NOT NULL,
  `IdParqueadero` int(11) DEFAULT NULL,
  `IdFuncionario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ingreso`
--

INSERT INTO `ingreso` (`IdIngreso`, `ObservacionesIngreso`, `TipoMovimiento`, `FechaIngreso`, `Estado`, `IdSede`, `IdParqueadero`, `IdFuncionario`) VALUES
(17, '', 'Entrada', '2025-11-15 10:13:33', 'Activo', 28, NULL, 28),
(18, '', 'Entrada', '2025-11-15 10:49:20', 'Activo', 28, NULL, 28),
(19, '', 'Entrada', '2025-11-15 11:06:58', 'Activo', 28, NULL, 28),
(20, '', 'Salida', '2025-11-15 11:07:13', 'Activo', 28, NULL, 28),
(21, '', 'Entrada', '2025-11-18 19:35:49', 'Activo', 28, NULL, 28),
(22, '', 'Entrada', '2025-11-18 19:40:40', 'Activo', 27, NULL, 24),
(23, '', 'Entrada', '2025-11-18 19:41:45', 'Activo', 25, NULL, 23),
(24, '', 'Salida', '2025-11-18 19:47:03', 'Activo', 25, NULL, 23),
(25, '', 'Salida', '2025-11-18 19:47:07', 'Activo', 25, NULL, 23);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `institucion`
--

CREATE TABLE `institucion` (
  `IdInstitucion` int(11) NOT NULL,
  `EstadoInstitucion` enum('Activo','Inactivo') NOT NULL,
  `NombreInstitucion` varchar(150) NOT NULL,
  `Nit_Codigo` varchar(50) NOT NULL,
  `TipoInstitucion` enum('Universidad','Colegio','Empresa','ONG','Hospital','Otro') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `institucion`
--

INSERT INTO `institucion` (`IdInstitucion`, `EstadoInstitucion`, `NombreInstitucion`, `Nit_Codigo`, `TipoInstitucion`) VALUES
(11, 'Activo', 'Universidad Distrital Francisco José de Caldas', '899999063-1', 'Universidad'),
(12, 'Activo', 'Colegio Distrital Marco Fidel Suárez', '8600152212', 'Universidad'),
(13, 'Activo', 'Empresa de Acueducto y Alcantarillado de Bogotá', '899999034-3', 'Empresa'),
(16, 'Activo', 'rodrigo de triana', '1313131231', 'Universidad'),
(17, 'Inactivo', 'juan caldas', '1231231231', 'Colegio'),
(18, 'Activo', 'rodrigo de triana', '1313131231', 'Universidad');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `parqueadero`
--

CREATE TABLE `parqueadero` (
  `IdParqueadero` int(11) NOT NULL,
  `TipoVehiculo` enum('Bicicleta','Moto','Carro','Otro') NOT NULL,
  `PlacaVehiculo` varchar(7) DEFAULT NULL,
  `DescripcionVehiculo` varchar(500) NOT NULL,
  `TarjetaPropiedad` varchar(20) NOT NULL,
  `FechaParqueadero` datetime NOT NULL,
  `Estado` enum('Activo','Inactivo') NOT NULL,
  `QrVehiculo` varchar(255) DEFAULT NULL,
  `IdSede` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `parqueadero`
--

INSERT INTO `parqueadero` (`IdParqueadero`, `TipoVehiculo`, `PlacaVehiculo`, `DescripcionVehiculo`, `TarjetaPropiedad`, `FechaParqueadero`, `Estado`, `QrVehiculo`, `IdSede`) VALUES
(11, 'Bicicleta', 'No apli', 'roja', 'Tj 1564221', '2025-11-10 18:09:00', 'Activo', NULL, 25),
(12, 'Moto', 'bg-32-c', 'Honda color morada', 'Tj 1564221', '2025-11-11 19:05:00', 'Activo', '', 25),
(14, 'Bicicleta', 'no apli', 'naranja', 'Tj 1564221', '2025-11-11 19:16:00', 'Activo', 'qr/QR-VEHICULO-14-6913d1d3897e3.png', 25),
(15, 'Carro', 'jkh-778', 'Sandero', 'Tj 1859221', '2025-11-11 19:24:00', 'Activo', 'qr/QR-VEHICULO-15-6913d3a98f0c4.png', 25),
(16, 'Bicicleta', 'no apli', 'morada', 'Tj 1564221', '2025-11-11 20:45:00', 'Activo', 'qr/QR-VEHICULO-16-6913e6d9bec96.png', 26),
(17, 'Moto', 'bg-32-c', 'Edicion evangelion', 'Tj 1859221', '2025-11-11 20:53:00', 'Activo', 'qr/QR-VEHICULO-17-6913e8a95a798.png', 27),
(18, 'Moto', 'jh-77-b', 'Honda de color azul marino', 'Tj 1562321', '2025-11-13 19:00:00', 'Activo', '/qr/QR-VEHICULO-18-6916713303be4.png', 27),
(19, 'Bicicleta', 'No apli', 'Roja', 'tj 46354', '2025-11-20 18:27:00', 'Activo', 'qr/Qr_Parq/QR-VEHICULO-19-691fa3d284f8a.png', 26),
(20, 'Moto', 'GHP-356', 'Honda de color azul', 'tj 46354', '2025-11-20 18:36:00', 'Activo', 'qr/Qr_Parq/QR-VEHICULO-20-691fa6057e271.png', 27),
(21, 'Carro', 'Eho-456', 'Renaul, Kia, de color blanco', 'tj 495564', '2025-11-20 18:38:00', 'Activo', 'qr/Qr_Parq/QR-VEHICULO-21-691fa667e7815.png', 28),
(22, 'Bicicleta', 'No apli', 'Naranja', 'tj 495564', '2025-11-20 19:52:00', 'Activo', 'qr/Qr_Parq/QR-VEHICULO-22-6924f9576780d.png', 28);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sede`
--

CREATE TABLE `sede` (
  `IdSede` int(11) NOT NULL,
  `TipoSede` varchar(100) NOT NULL,
  `Ciudad` varchar(50) NOT NULL,
  `Estado` enum('Activo','Inactivo') NOT NULL,
  `IdInstitucion` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sede`
--

INSERT INTO `sede` (`IdSede`, `TipoSede`, `Ciudad`, `Estado`, `IdInstitucion`) VALUES
(25, 'shapinero', 'Bogotá', 'Activo', 11),
(26, 'Sede Tecnológica', 'Bogotá', 'Activo', 11),
(27, 'Principal', 'Bogotá', 'Activo', 12),
(28, 'Sede Norte', 'Bogotá', 'Activo', 12),
(29, 'Principal', 'Bogotá', 'Activo', 13),
(30, 'Centro Operativo', 'Bogotá', 'Activo', 13),
(33, 'loreal paris II', 'localidad de hennedy', 'Activo', 11),
(34, 'luis ferney caldas', 'Cucuta', 'Activo', 11);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `IdUsuario` int(11) NOT NULL,
  `TipoRol` enum('Supervisor','Personal Seguridad','Administrador') NOT NULL,
  `Contrasena` varchar(100) NOT NULL,
  `Estado` enum('Activo','Inactivo') NOT NULL,
  `IdFuncionario` int(11) NOT NULL,
  `TokenRecuperacion` varchar(255) DEFAULT NULL,
  `TokenExpiracion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`IdUsuario`, `TipoRol`, `Contrasena`, `Estado`, `IdFuncionario`, `TokenRecuperacion`, `TokenExpiracion`) VALUES
(8, 'Supervisor', '$2y$10$VKquBUsZ0UbCHAoVRcamA.TgRd3qP3LoQPv75.MQuQzMGFnjShlHW', 'Activo', 24, '308863', '2025-12-04 02:15:36'),
(9, 'Administrador', '$2y$10$kQHtxRVWsw7DeIARK6ejc.aiDn.Dt6xygkrjN5esDy5rW5H7HGRHm', 'Activo', 25, '552419', '2025-12-04 06:26:51'),
(10, 'Administrador', '$2y$10$xbiCU2tn.MJHq1rUQGteV.hCYpAVmHo/ZgPi4KfkOBSHanQFywnQy', 'Activo', 23, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `visitante`
--

CREATE TABLE `visitante` (
  `IdVisitante` int(11) NOT NULL,
  `IdentificacionVisitante` bigint(20) NOT NULL,
  `Estado` enum('Activo','Inactivo') NOT NULL,
  `NombreVisitante` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `visitante`
--

INSERT INTO `visitante` (`IdVisitante`, `IdentificacionVisitante`, `Estado`, `NombreVisitante`) VALUES
(6, 1029345678, 'Activo', 'Sofia Ramírez Torres'),
(7, 79845612, 'Activo', 'Mateo Rojas Castillo'),
(8, 1008342991, 'Activo', 'Valeria Rodríguez Peña'),
(9, 945672310, 'Activo', 'Diego Alejandro Torres'),
(10, 1103425567, 'Activo', 'Camila Fernanda Díaz');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD PRIMARY KEY (`IdBitacora`),
  ADD KEY `IdFuncionario` (`IdFuncionario`),
  ADD KEY `IdIngreso` (`IdIngreso`),
  ADD KEY `IdDispositivo` (`IdDispositivo`),
  ADD KEY `IdVisitante` (`IdVisitante`);

--
-- Indices de la tabla `dispositivo`
--
ALTER TABLE `dispositivo`
  ADD PRIMARY KEY (`IdDispositivo`),
  ADD KEY `IdFuncionario` (`IdFuncionario`),
  ADD KEY `IdVisitante` (`IdVisitante`);

--
-- Indices de la tabla `dotacion`
--
ALTER TABLE `dotacion`
  ADD PRIMARY KEY (`IdDotacion`),
  ADD KEY `IdFuncionario` (`IdFuncionario`);

--
-- Indices de la tabla `funcionario`
--
ALTER TABLE `funcionario`
  ADD PRIMARY KEY (`IdFuncionario`),
  ADD KEY `IdSede` (`IdSede`);

--
-- Indices de la tabla `ingreso`
--
ALTER TABLE `ingreso`
  ADD PRIMARY KEY (`IdIngreso`),
  ADD KEY `IdSede` (`IdSede`),
  ADD KEY `IdParqueadero` (`IdParqueadero`),
  ADD KEY `IdFuncionario` (`IdFuncionario`);

--
-- Indices de la tabla `institucion`
--
ALTER TABLE `institucion`
  ADD PRIMARY KEY (`IdInstitucion`);

--
-- Indices de la tabla `parqueadero`
--
ALTER TABLE `parqueadero`
  ADD PRIMARY KEY (`IdParqueadero`),
  ADD KEY `IdSede` (`IdSede`);

--
-- Indices de la tabla `sede`
--
ALTER TABLE `sede`
  ADD PRIMARY KEY (`IdSede`),
  ADD KEY `IdInstitucion` (`IdInstitucion`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`IdUsuario`),
  ADD KEY `IdFuncionario` (`IdFuncionario`);

--
-- Indices de la tabla `visitante`
--
ALTER TABLE `visitante`
  ADD PRIMARY KEY (`IdVisitante`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  MODIFY `IdBitacora` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `dispositivo`
--
ALTER TABLE `dispositivo`
  MODIFY `IdDispositivo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `dotacion`
--
ALTER TABLE `dotacion`
  MODIFY `IdDotacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `funcionario`
--
ALTER TABLE `funcionario`
  MODIFY `IdFuncionario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `ingreso`
--
ALTER TABLE `ingreso`
  MODIFY `IdIngreso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `institucion`
--
ALTER TABLE `institucion`
  MODIFY `IdInstitucion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `parqueadero`
--
ALTER TABLE `parqueadero`
  MODIFY `IdParqueadero` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `sede`
--
ALTER TABLE `sede`
  MODIFY `IdSede` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `IdUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `visitante`
--
ALTER TABLE `visitante`
  MODIFY `IdVisitante` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD CONSTRAINT `bitacora_ibfk_1` FOREIGN KEY (`IdFuncionario`) REFERENCES `funcionario` (`IdFuncionario`),
  ADD CONSTRAINT `bitacora_ibfk_2` FOREIGN KEY (`IdIngreso`) REFERENCES `ingreso` (`IdIngreso`),
  ADD CONSTRAINT `bitacora_ibfk_3` FOREIGN KEY (`IdDispositivo`) REFERENCES `dispositivo` (`IdDispositivo`),
  ADD CONSTRAINT `bitacora_ibfk_4` FOREIGN KEY (`IdVisitante`) REFERENCES `visitante` (`IdVisitante`);

--
-- Filtros para la tabla `dispositivo`
--
ALTER TABLE `dispositivo`
  ADD CONSTRAINT `dispositivo_ibfk_1` FOREIGN KEY (`IdFuncionario`) REFERENCES `funcionario` (`IdFuncionario`),
  ADD CONSTRAINT `dispositivo_ibfk_2` FOREIGN KEY (`IdVisitante`) REFERENCES `visitante` (`IdVisitante`);

--
-- Filtros para la tabla `dotacion`
--
ALTER TABLE `dotacion`
  ADD CONSTRAINT `dotacion_ibfk_1` FOREIGN KEY (`IdFuncionario`) REFERENCES `funcionario` (`IdFuncionario`);

--
-- Filtros para la tabla `funcionario`
--
ALTER TABLE `funcionario`
  ADD CONSTRAINT `funcionario_ibfk_1` FOREIGN KEY (`IdSede`) REFERENCES `sede` (`IdSede`);

--
-- Filtros para la tabla `ingreso`
--
ALTER TABLE `ingreso`
  ADD CONSTRAINT `ingreso_ibfk_1` FOREIGN KEY (`IdSede`) REFERENCES `sede` (`IdSede`),
  ADD CONSTRAINT `ingreso_ibfk_2` FOREIGN KEY (`IdParqueadero`) REFERENCES `parqueadero` (`IdParqueadero`),
  ADD CONSTRAINT `ingreso_ibfk_3` FOREIGN KEY (`IdFuncionario`) REFERENCES `funcionario` (`IdFuncionario`);

--
-- Filtros para la tabla `parqueadero`
--
ALTER TABLE `parqueadero`
  ADD CONSTRAINT `parqueadero_ibfk_1` FOREIGN KEY (`IdSede`) REFERENCES `sede` (`IdSede`);

--
-- Filtros para la tabla `sede`
--
ALTER TABLE `sede`
  ADD CONSTRAINT `sede_ibfk_1` FOREIGN KEY (`IdInstitucion`) REFERENCES `institucion` (`IdInstitucion`);

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`IdFuncionario`) REFERENCES `funcionario` (`IdFuncionario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
