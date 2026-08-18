-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Erstellungszeit: 18. Aug 2026 um 11:18
-- Server-Version: 10.11.10-MariaDB-cll-lve-log
-- PHP-Version: 8.4.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `italienisch_db`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `italienisch_verben`
--

CREATE TABLE `italienisch_verben` (
  `id` int(11) NOT NULL,
  `verb` varchar(250) DEFAULT NULL,
  `praesens` varchar(250) DEFAULT NULL,
  `perfekt` varchar(250) DEFAULT NULL,
  `futur` varchar(250) DEFAULT NULL,
  `imperativ` varchar(250) DEFAULT NULL,
  `endung` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `italienisch_verben`
--
ALTER TABLE `italienisch_verben`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `italienisch_verben`
--
ALTER TABLE `italienisch_verben`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
