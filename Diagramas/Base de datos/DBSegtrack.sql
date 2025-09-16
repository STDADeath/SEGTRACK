-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 05-09-2025 a las 23:10:09
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
-- Base de datos: `proyecto21`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bitacora`
--

CREATE TABLE `bitacora` (
  `IdBitacora` int(11) NOT NULL,
  `TurnoBitacora` enum('Jornada mañana','Jornada tarde','Jornada nocturna') DEFAULT NULL,
  `NovedadesBitacora` varchar(500) DEFAULT NULL,
  `FechaBitacora` datetime DEFAULT NULL,
  `IdFuncionario` int(11) DEFAULT NULL,
  `IdIngreso` int(11) DEFAULT NULL,
  `IdDispositivo` int(11) DEFAULT NULL,
  `IdVisitante` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dispositivo`
--

CREATE TABLE `dispositivo` (
  `IdDispositivo` int(11) NOT NULL,
  `QrDispositivo` varchar(100) DEFAULT NULL,
  `TipoDispositivo` enum('Computador','Tablet','Portátil','Otro') DEFAULT NULL,
  `MarcaDispositivo` varchar(100) DEFAULT NULL,
  `IdFuncionario` int(11) DEFAULT NULL,
  `IdVisitante` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `dispositivo`
--

INSERT INTO `dispositivo` (`IdDispositivo`, `QrDispositivo`, `TipoDispositivo`, `MarcaDispositivo`, `IdFuncionario`, `IdVisitante`) VALUES
(5, 'QR-VIS-F26R1\r\n', 'Computador', 'PC GAMER ', 13, NULL),
(6, 'QR-VIS-E85P3', 'Tablet', 'lenovo', 12, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dotacion`
--

CREATE TABLE `dotacion` (
  `IdDotacion` int(11) NOT NULL,
  `EstadoDotacion` enum('Buen estado','Regular','Dañado') DEFAULT NULL,
  `TipoDotacion` enum('Uniforme','Equipo','Herramienta','Otro') DEFAULT NULL,
  `NovedadDotacion` varchar(500) DEFAULT NULL,
  `FechaDevolucion` datetime DEFAULT NULL,
  `FechaEntrega` datetime DEFAULT NULL,
  `IdFuncionario` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `funcionario`
--

CREATE TABLE `funcionario` (
  `IdFuncionario` int(11) NOT NULL,
  `CargoFuncionario` enum('Personal Seguridad','Funcionario','Empresario') DEFAULT NULL,
  `QrCodigoFuncionario` varchar(100) DEFAULT NULL,
  `NombreFuncionario` text DEFAULT NULL,
  `IdSede` int(11) DEFAULT NULL,
  `TelefonoFuncionario` varchar(20) DEFAULT NULL,
  `DocumentoFuncionario` bigint(20) DEFAULT NULL,
  `CorreoFuncionario` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `funcionario`
--

INSERT INTO `funcionario` (`IdFuncionario`, `CargoFuncionario`, `QrCodigoFuncionario`, `NombreFuncionario`, `IdSede`, `TelefonoFuncionario`, `DocumentoFuncionario`, `CorreoFuncionario`) VALUES
(12, 'Funcionario', 'QR-FUNC-A91X4', 'Andres camilo carrillo', 17, '3246503589', 1024474656, 'Carrimorro9080@gmail.com'),
(13, 'Funcionario', 'QR-FUNC-B72Z9', 'Anderson montenegro', 18, '3242152192', 100242523, 'anderson@gmail.com'),
(14, 'Personal Seguridad', 'QR-VIS-D54Q7', 'camilo rodriguez', 17, '3212421421', 103252935, 'camila@gmail.com'),
(15, 'Personal Seguridad', 'QR-VIS-E85P3', 'johana vargas ', 18, '31024125121', 1247948957, 'johana@gmail.com\r\n');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ingreso`
--

CREATE TABLE `ingreso` (
  `IdIngreso` int(11) NOT NULL,
  `ObservacionesIngreso` varchar(500) DEFAULT NULL,
  `TipoMovimiento` enum('Entrada','Salida') DEFAULT NULL,
  `FechaIngreso` datetime DEFAULT NULL,
  `IdSede` int(11) DEFAULT NULL,
  `IdParqueadero` int(11) DEFAULT NULL,
  `IdFuncionario` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `institucion`
--

CREATE TABLE `institucion` (
  `IdInstitucion` int(11) NOT NULL,
  `EstadoInstitucion` enum('Activo','Inactivo') DEFAULT NULL,
  `NombreInstitucion` varchar(150) DEFAULT NULL,
  `Nit_Codigo` varchar(50) DEFAULT NULL,
  `TipoInstitucion` enum('Universidad','Colegio','Empresa','ONG','Hospital','Otro') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `institucion`
--

INSERT INTO `institucion` (`IdInstitucion`, `EstadoInstitucion`, `NombreInstitucion`, `Nit_Codigo`, `TipoInstitucion`) VALUES
(9, 'Activo', 'Segtrack Chapinero', '900123456-1', 'Empresa'),
(10, 'Activo', 'Segtrack Soacha', '900123456-2', 'Empresa');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `parqueadero`
--

CREATE TABLE `parqueadero` (
  `IdParqueadero` int(11) NOT NULL,
  `TipoVehiculo` enum('Bicicleta','Moto','Carro','Otro') DEFAULT NULL,
  `PlacaVehiculo` varchar(7) DEFAULT NULL,
  `DescripcionVehiculo` varchar(500) DEFAULT NULL,
  `TarjetaPropiedad` varchar(20) DEFAULT NULL,
  `FechaParqueadero` datetime DEFAULT NULL,
  `IdSede` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sede`
--

CREATE TABLE `sede` (
  `IdSede` int(11) NOT NULL,
  `TipoSede` enum('Sede chapinero Segtrack','Sede Bosa Segtrack','Sede Soacha Segtrack','Sede Toberin Segtrack') DEFAULT NULL,
  `Ciudad` enum('Bogotá','Medellín','Cali','Barranquilla','Otra') DEFAULT NULL,
  `IdInstitucion` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sede`
--

INSERT INTO `sede` (`IdSede`, `TipoSede`, `Ciudad`, `IdInstitucion`) VALUES
(17, 'Sede chapinero Segtrack', 'Bogotá', 9),
(18, 'Sede Soacha Segtrack', 'Medellín', 10);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `IdUsuario` int(11) NOT NULL,
  `TipoRol` enum('Supervisor','Personal Seguridad','Administrador') DEFAULT NULL,
  `Contrasena` varchar(100) DEFAULT NULL,
  `IdFuncionario` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `visitante`
--

CREATE TABLE `visitante` (
  `IdVisitante` int(11) NOT NULL,
  `IdentificacionVisitante` bigint(20) DEFAULT NULL,
  `NombreVisitante` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  ADD PRIMARY KEY (`IdInstitucion`),
  ADD UNIQUE KEY `Nit_Codigo` (`Nit_Codigo`);

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
  MODIFY `IdBitacora` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `dispositivo`
--
ALTER TABLE `dispositivo`
  MODIFY `IdDispositivo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `dotacion`
--
ALTER TABLE `dotacion`
  MODIFY `IdDotacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `funcionario`
--
ALTER TABLE `funcionario`
  MODIFY `IdFuncionario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `ingreso`
--
ALTER TABLE `ingreso`
  MODIFY `IdIngreso` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `institucion`
--
ALTER TABLE `institucion`
  MODIFY `IdInstitucion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `parqueadero`
--
ALTER TABLE `parqueadero`
  MODIFY `IdParqueadero` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sede`
--
ALTER TABLE `sede`
  MODIFY `IdSede` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `IdUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `visitante`
--
ALTER TABLE `visitante`
  MODIFY `IdVisitante` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
