-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 28, 2025 at 06:03 PM
-- Server version: 5.7.44
-- PHP Version: 8.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cuenta2_osticket`
--
CREATE DATABASE IF NOT EXISTS `cuenta2_osticket` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `cuenta2_osticket`;

-- --------------------------------------------------------

--
-- Table structure for table `delitos`
--

CREATE TABLE `delitos` (
  `id` int(11) NOT NULL,
  `departamento` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `municipio` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zona` varchar(100) DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `codigo_dane` varchar(20) DEFAULT NULL,
  `armas_medios` varchar(100) DEFAULT NULL,
  `fecha_hecho` date DEFAULT NULL,
  `genero` varchar(20) DEFAULT NULL,
  `grupo_etario` varchar(50) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  `registro_hash` char(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `delitos_resumen`
--

CREATE TABLE `delitos_resumen` (
  `departamento` varchar(255) DEFAULT NULL,
  `municipio` varchar(255) DEFAULT NULL,
  `zona` varchar(255) DEFAULT NULL,
  `anio` int(11) DEFAULT NULL,
  `mes` int(11) DEFAULT NULL,
  `semana` int(11) DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `delitos_sexuales` int(11) DEFAULT NULL,
  `violencia` int(11) DEFAULT NULL,
  `hurto` int(11) DEFAULT NULL,
  `conflicto_armado` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `delitos_resumen_tiempo_municipio`
--

CREATE TABLE `delitos_resumen_tiempo_municipio` (
  `departamento` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `municipio` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zona` varchar(100) DEFAULT NULL,
  `anio` int(4) DEFAULT NULL,
  `mes` int(2) DEFAULT NULL,
  `semana` int(2) DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `delitos_sexuales` decimal(32,0) DEFAULT NULL,
  `violencia` decimal(32,0) DEFAULT NULL,
  `hurto` decimal(32,0) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `geopuntos`
--

CREATE TABLE `geopuntos` (
  `id` int(11) NOT NULL,
  `departamento` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `municipio` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lat` decimal(10,7) NOT NULL,
  `lng` decimal(10,7) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Stand-in structure for view `vista_delitos_tiempo_departamento`
-- (See below for the actual view)
--
CREATE TABLE `vista_delitos_tiempo_departamento` (
`departamento` varchar(255)
,`anio` int(4)
,`mes` int(2)
,`dia` int(2)
,`semana` int(2)
,`delitos_sexuales` decimal(32,0)
,`violencia` decimal(32,0)
,`hurto` decimal(32,0)
,`conflicto_armado` decimal(32,0)
);

-- --------------------------------------------------------

--
-- Structure for view `vista_delitos_tiempo_departamento`
--
DROP TABLE IF EXISTS `vista_delitos_tiempo_departamento`;

CREATE ALGORITHM=UNDEFINED DEFINER=`cuenta2`@`localhost` SQL SECURITY DEFINER VIEW `vista_delitos_tiempo_departamento`  AS SELECT `d`.`departamento` AS `departamento`, year(str_to_date(`d`.`fecha_hecho`,'%Y-%m-%d')) AS `anio`, month(str_to_date(`d`.`fecha_hecho`,'%Y-%m-%d')) AS `mes`, dayofmonth(str_to_date(`d`.`fecha_hecho`,'%Y-%m-%d')) AS `dia`, week(str_to_date(`d`.`fecha_hecho`,'%Y-%m-%d'),3) AS `semana`, sum((case when (`d`.`categoria` = 'SEXUAL') then `d`.`cantidad` else 0 end)) AS `delitos_sexuales`, sum((case when (`d`.`categoria` = 'VIOLENCIA_INTRAFAMILIAR') then `d`.`cantidad` else 0 end)) AS `violencia`, sum((case when (`d`.`categoria` = 'HURTO') then `d`.`cantidad` else 0 end)) AS `hurto`, sum((case when (`d`.`categoria` = 'CONFLICTO_ARMADO') then `d`.`cantidad` else 0 end)) AS `conflicto_armado` FROM `delitos` AS `d` GROUP BY `d`.`departamento`, `anio`, `mes`, `semana` ORDER BY `anio` ASC, `mes` ASC, `semana` ASC, `d`.`departamento` ASC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `delitos`
--
ALTER TABLE `delitos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unq_registro_hash` (`registro_hash`);

--
-- Indexes for table `geopuntos`
--
ALTER TABLE `geopuntos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `delitos`
--
ALTER TABLE `delitos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `geopuntos`
--
ALTER TABLE `geopuntos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
