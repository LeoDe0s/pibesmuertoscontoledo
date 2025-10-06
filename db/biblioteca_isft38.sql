-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 06-10-2025 a las 03:29:10
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
-- Base de datos: `biblioteca_isft38`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`) VALUES
(1, 'Programación y Desarrollo'),
(2, 'Contabilidad y Finanzas'),
(3, 'Gestión de Recursos Humanos'),
(4, 'Sistemas Operativos y Redes'),
(5, 'Higiene y Seguridad Laboral'),
(6, 'Matemática Aplicada'),
(7, 'Estadística'),
(8, 'Normativas Ambientales'),
(9, 'Mecánica y Mantenimiento'),
(10, 'Lógica y Algoritmos'),
(11, 'Auditoría y Control'),
(12, 'Legislación Laboral'),
(13, 'Automatización Industrial'),
(14, 'Seguridad en IoT'),
(15, 'Ergonomía y Salud Ocupacional');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `libros`
--

CREATE TABLE `libros` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `autor` varchar(255) NOT NULL,
  `categorias` varchar(20) NOT NULL,
  `editorial` varchar(100) DEFAULT NULL,
  `cantidad` int(11) DEFAULT 0,
  `isbn` varchar(20) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `carrera_id` int(11) DEFAULT NULL,
  `categoria_id` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `libros`
--

INSERT INTO `libros` (`id`, `titulo`, `autor`, `categorias`, `editorial`, `cantidad`, `isbn`, `imagen`, `descripcion`, `carrera_id`, `categoria_id`) VALUES
(31, 'Mein Kampf', 'Adolf Hitler', '', 'Eher Verlad', 1, 'H1TL3R', 'uploads/img_68d1c8ecd70e1_descarga (1).jpg', 'Mein Kampf de Adolf Hitler es en parte una autobiografía y en parte un tratado político. Mein Kampf (que significa “Mi lucha”) promovía los componentes fundamentales del nazismo: un antisemitismo rabioso, una visión racista del mundo y una política exterior agresiva orientada a obtener espacio vital (Lebensraum) en Europa oriental.  ', NULL, 1),
(34, 'Administración de la Cadena de Suministro', 'Ronald H. Ballou', 'logistica', 'PEARSON Prentice Hall', 1, '1', 'uploads/img_68db06307b7d3_0.jpg', NULL, NULL, 1),
(36, 'Manual de Stocks', 'Nolberto J. Munier', 'logistica', 'Astrea', 1, '11', 'uploads/img_68db06d1824c4_1.jpg', NULL, NULL, 1),
(37, 'Manual Básico de Logística Integral', 'Aitor Urzelai Inza', 'logistica', 'Diaz de Santos', 1, '2', 'uploads/img_68db07169fa13_2.jpg', NULL, NULL, 1),
(38, 'La Logística Empresarial en el Nuevo Milenio', 'Daniel Serra de la Figuera', 'logistica', 'x', 1, '3', 'uploads/img_68db07822a553_3.jpg', NULL, NULL, 1),
(39, 'Cómo Mejorar la Logística de su Empresa Mediante la Simulación', 'Miquel Ángel Piera, Toni Guasch, Josep Casanovas, Juan José Ramos', 'logistica', 'Diaz de Santos', 1, '4', 'uploads/img_68db07fad259f_4.jpg', NULL, NULL, 1),
(40, 'Logística para el Desarrollo Competitivo', 'Alejandro Iglesias', 'logistica', 'Nobuko', 1, '5', 'uploads/img_68db08383c99b_5.jpg', NULL, NULL, 1),
(41, 'Mejores Prácticas en Latinoamérica', 'Octavio Carranza', 'logistica', 'Thomson', 1, '6', 'uploads/img_68db088719468_6.jpg', NULL, NULL, 1),
(42, 'Calidad 2da Edición', 'Pablo Alcalde San Miguel', 'logistica', 'Jerez Editores', 1, '7', 'uploads/img_68db08bf33e63_7.jpg', NULL, NULL, 1),
(43, 'La logística de aprovisionamientos para la integración de la cadena de suministros', 'Eva Pronce, Bernardo Prida', 'logistica', 'Prentice Hall', 1, '8', 'uploads/img_68db092879aec_8.jpg', '', NULL, 1),
(44, 'Logística comercial', 'Rodrigo López Fernández', 'logistica', 'Thomson', 1, '9', 'uploads/img_68db0966ea9d5_9.jpg', NULL, NULL, 1),
(45, 'Manual de logística integral', 'Jordi Pau Cos, Ricardo de Navascués ', 'logistica', 'Diaz de Santos', 1, '10', 'uploads/img_68db09bd0c404_10.jpg', NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prestamos`
--

CREATE TABLE `prestamos` (
  `id` int(11) NOT NULL,
  `miembro` varchar(100) DEFAULT NULL,
  `titulo_libro` varchar(200) DEFAULT NULL,
  `fecha_prestamo` date DEFAULT NULL,
  `fecha_devolucion` date DEFAULT NULL,
  `estado` enum('Devuelto','Prestado','Vencido') DEFAULT NULL,
  `vencimientos` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `libros`
--
ALTER TABLE `libros`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `isbn` (`isbn`),
  ADD UNIQUE KEY `isbn_2` (`isbn`);

--
-- Indices de la tabla `prestamos`
--
ALTER TABLE `prestamos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `libros`
--
ALTER TABLE `libros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT de la tabla `prestamos`
--
ALTER TABLE `prestamos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
