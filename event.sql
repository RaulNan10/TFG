-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 05-06-2022 a las 19:37:47
-- Versión del servidor: 10.4.21-MariaDB
-- Versión de PHP: 7.4.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `TFG`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `event`
--

CREATE TABLE `event` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `image` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `event`
--

INSERT INTO `event` (`id`, `user_id`, `image`, `title`, `description`, `date`) VALUES
(13, 14, 'event1654021863.jpg', 'Fiesta en la playa', 'Organizamos una gran fiesta  en la playa.\r\nComenzaremos a las 19 horas, se trata de una fiesta con polvos de pintura en la playa, el motivo de la celebración es el FINAL DE EXÁMENES.\r\nSe realizará en la playa de Malvarrosa, quedas invitado.', '2022-06-30'),
(14, 15, 'event1654022194.jpg', 'Linternas de papel', '¡Queremos que se nos vea desde el espacio!\r\nVamos a lanzar linternas de papel al cielo el día de San Juan, una gran celebración merece un gran acto, y ¿qué mejor forma de inaugurar el verano?\r\nLo haremos el día 24 a las 00 horas, todo el mundo invitado;)', '2022-06-24'),
(16, 16, 'event1654030106.jpg', 'Concierto Nino Bravo', 'Para recordar a nuestro querido artista Nino Bravo, queremos recrear su último concierto.\r\nSi eres de los que todavía siguen escuchando sus canciones como nosotros, entra en nuestra web y compra tus entradas para el concierto. Sus canciones nos devolverán a aquellos viejos tiempos y crearán un increíble ambiente.\r\nComenzará a las 20:00 en la ciudad de las artes y las ciencias. Entra ya en nuestra web y compra tus entradas, te estamos esperando.', '2022-09-05'),
(20, 14, 'event1654449100.jpg', 'Inauguración de terraza', 'Nuestra terraza queda inaugurada a partir de la fecha indicada más arriba', '2022-07-08');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_3BAE0AA7A76ED395` (`user_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `event`
--
ALTER TABLE `event`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `event`
--
ALTER TABLE `event`
  ADD CONSTRAINT `FK_3BAE0AA7A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
