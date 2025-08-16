-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 15-08-2025 a las 03:50:12
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
-- Base de datos: `sinagua`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `account_numbers`
--

CREATE TABLE `account_numbers` (
  `id` int(11) NOT NULL,
  `num` varchar(16) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `account_numbers`
--

INSERT INTO `account_numbers` (`id`, `num`, `status`) VALUES
(1, '1637263546372635', 'active'),
(2, '1987463514253546', 'active'),
(3, '1932141224130212', 'active'),
(4, '8473645261625364', 'active'),
(5, '1234567890123456', 'active');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `debts`
--

CREATE TABLE `debts` (
  `id` int(11) NOT NULL,
  `quantity` decimal(10,0) NOT NULL,
  `account_num_id` int(11) NOT NULL,
  `paid` tinyint(1) NOT NULL,
  `debt_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `debts`
--

INSERT INTO `debts` (`id`, `quantity`, `account_num_id`, `paid`, `debt_date`) VALUES
(2, 700, 1, 1, '2025-07-22 03:21:09'),
(3, 751, 1, 1, '2025-07-27 04:32:32'),
(4, 1000, 1, 1, '2025-08-04 00:58:01'),
(6, 2000000, 3, 1, '2025-08-04 18:42:56'),
(7, 9000, 3, 1, '2025-08-04 18:54:12'),
(8, 2000000, 4, 0, '2025-08-04 19:00:33'),
(9, 200, 1, 1, '2025-08-05 13:45:14'),
(10, 1000000, 1, 1, '2025-08-06 16:14:28'),
(11, 700, 1, 1, '2025-08-06 17:38:57'),
(12, 900, 1, 1, '2025-08-07 18:52:15'),
(13, 1000, 1, 1, '2025-08-08 02:04:07'),
(14, 5679, 1, 1, '2025-08-08 22:00:04'),
(15, 2000000, 1, 1, '2025-08-08 22:46:38'),
(16, 2000000, 1, 1, '2025-08-08 23:25:06'),
(17, 685, 1, 1, '2025-08-13 17:32:31'),
(18, 87236, 1, 0, '2025-08-13 18:20:03'),
(19, 9767, 1, 0, '2025-08-14 23:46:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `debt_id` int(11) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `payments`
--

INSERT INTO `payments` (`id`, `debt_id`, `payment_date`) VALUES
(1, 2, '2025-08-04 00:55:23'),
(3, 4, '2025-08-04 01:11:06'),
(4, 6, '2025-08-04 18:53:05'),
(5, 7, '2025-08-04 18:55:54'),
(6, 9, '2025-08-05 13:54:34'),
(7, 10, '2025-08-06 16:18:18'),
(9, 11, '2025-08-08 02:01:35'),
(10, 13, '2025-08-08 02:07:45'),
(11, 14, '2025-08-08 22:13:06'),
(12, 15, '2025-08-08 22:54:15'),
(13, 16, '2025-08-08 23:28:15'),
(14, 17, '2025-08-13 17:48:47'),
(15, 2, '2025-08-15 00:19:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `status`
--

CREATE TABLE `status` (
  `id` int(11) NOT NULL,
  `num` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `account_num_id` int(11) NOT NULL,
  `name1` varchar(30) NOT NULL,
  `name2` varchar(30) DEFAULT NULL,
  `lastname1` varchar(30) NOT NULL,
  `lastname2` varchar(30) DEFAULT NULL,
  `address` varchar(100) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `account_num_id`, `name1`, `name2`, `lastname1`, `lastname2`, `address`, `status`) VALUES
(1, 1, 'Emanuel', NULL, 'Santacruz', 'Carbajal', 'Calle Corvus 212, Villas del guadiana 2', 'active'),
(2, 2, 'Francisco', 'Alejandro', 'Se me olvido', NULL, 'La gamiz ', 'active'),
(3, 3, 'Margarita', NULL, 'Santacruz', 'Carbajal', 'No se la calle', 'active'),
(4, 4, 'Gerardo', NULL, 'Ruiz', 'Guzman', 'Por tierra blanca y no se donde mas', 'active');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `account_numbers`
--
ALTER TABLE `account_numbers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `num` (`num`);

--
-- Indices de la tabla `debts`
--
ALTER TABLE `debts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_num_id` (`account_num_id`);

--
-- Indices de la tabla `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `debt_id` (`debt_id`);

--
-- Indices de la tabla `status`
--
ALTER TABLE `status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `num` (`num`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `account_num_id` (`account_num_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `account_numbers`
--
ALTER TABLE `account_numbers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `debts`
--
ALTER TABLE `debts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `status`
--
ALTER TABLE `status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `debts`
--
ALTER TABLE `debts`
  ADD CONSTRAINT `debts_ibfk_1` FOREIGN KEY (`account_num_id`) REFERENCES `account_numbers` (`id`);

--
-- Filtros para la tabla `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`debt_id`) REFERENCES `debts` (`id`);

--
-- Filtros para la tabla `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`account_num_id`) REFERENCES `account_numbers` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
