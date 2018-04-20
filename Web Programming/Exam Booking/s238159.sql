-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Ago 17, 2017 alle 17:02
-- Versione del server: 10.1.21-MariaDB
-- Versione PHP: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `s238159`
--
CREATE DATABASE IF NOT EXISTS `s238159` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `s238159`;

-- --------------------------------------------------------

--
-- Struttura della tabella `booking`
--
-- Creazione: Ago 17, 2017 alle 14:23
--

DROP TABLE IF EXISTS `booking`;
CREATE TABLE IF NOT EXISTS `booking` (
  `user` varchar(50) NOT NULL,
  `first` int(11) DEFAULT NULL,
  `second` int(11) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `booking`
--

INSERT INTO `booking` (`user`, `first`, `second`, `date`) VALUES
('a@p.it', 3, 1, '2017-08-17 16:55:57'),
('b@p.it', NULL, 2, '2017-08-17 16:58:31'),
('c@p.it', 1, 3, '2017-08-17 16:56:38'),
('d@p.it', 2, 1, '2017-08-17 16:59:09'),
('e@p.it', 2, 3, '2017-08-17 16:57:55');

-- --------------------------------------------------------

--
-- Struttura della tabella `calls`
--
-- Creazione: Ago 17, 2017 alle 14:23
--

DROP TABLE IF EXISTS `calls`;
CREATE TABLE IF NOT EXISTS `calls` (
  `call1` int(11) DEFAULT NULL,
  `call2` int(11) DEFAULT NULL,
  `call3` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `calls`
--

INSERT INTO `calls` (`call1`, `call2`, `call3`) VALUES
(3, 3, 3);

-- --------------------------------------------------------

--
-- Struttura della tabella `members`
--
-- Creazione: Ago 17, 2017 alle 14:23
--

DROP TABLE IF EXISTS `members`;
CREATE TABLE IF NOT EXISTS `members` (
  `user` varchar(50) NOT NULL,
  `pass` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `members`
--

INSERT INTO `members` (`user`, `pass`) VALUES
('a@p.it', '980a8f4f8b386a83ddbdd6ef81f1db24'),
('b@p.it', '4fca08d95657d01fc80cd5c19d894a18'),
('c@p.it', '424f7298305bb878304cfcafa8355fb4'),
('d@p.it', '3584efb07ee8cab3c1677370102a860a'),
('e@p.it', '6fdd56b024b91c82a916c4f030c781c3'),
('f@p.it', 'e7bd30d9110e0c9fbf03055f97d012e4');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
