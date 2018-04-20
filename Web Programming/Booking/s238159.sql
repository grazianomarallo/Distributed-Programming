-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Creato il: Lug 13, 2017 alle 17:24
-- Versione del server: 5.7.18-0ubuntu0.16.04.1
-- Versione PHP: 7.0.18-0ubuntu0.16.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `s238159`
--
CREATE DATABASE IF NOT EXISTS `s238159` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `s238159`;

-- --------------------------------------------------------

--
-- Struttura della tabella `available_time`
--

DROP TABLE IF EXISTS `available_time`;
CREATE TABLE `available_time` (
  `av_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `available_time`
--

INSERT INTO `available_time` (`av_time`) VALUES
(0);

-- --------------------------------------------------------

--
-- Struttura della tabella `booking`
--

DROP TABLE IF EXISTS `booking`;
CREATE TABLE `booking` (
  `user` varchar(50) NOT NULL,
  `request` int(11) DEFAULT NULL,
  `assigned` int(11) DEFAULT NULL,
  `date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `booking`
--

INSERT INTO `booking` (`user`, `request`, `assigned`, `date`) VALUES
('a@p.it', 30, 27, '2017-07-13 16:13:17'),
('b@p.it', 120, 108, '2017-07-13 16:13:43'),
('c@p.it', 50, 45, '2017-07-13 16:14:00');

-- --------------------------------------------------------

--
-- Struttura della tabella `members`
--

DROP TABLE IF EXISTS `members`;
CREATE TABLE `members` (
  `user` varchar(50) NOT NULL,
  `pass` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `members`
--

INSERT INTO `members` (`user`, `pass`) VALUES
('a@p.it', 'd818ba711a6f645c9c5c2f3234787fa3'),
('b@p.it', '65a385ccff5cd0519eb4f1e40f8b5ae4'),
('c@p.it', '092debd3d3106cf00700e8024a57600e');

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`user`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
