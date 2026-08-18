-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Erstellungszeit: 17. Aug 2026 um 14:57
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
-- Tabellenstruktur für Tabelle `italienisch_woerter_und_saetze`
--

CREATE TABLE `italienisch_woerter_und_saetze` (
  `id` int(11) NOT NULL,
  `wort_de` varchar(250) DEFAULT NULL,
  `wort_it` varchar(250) DEFAULT NULL,
  `satz_de` varchar(4000) DEFAULT NULL,
  `satz_it` varchar(4000) DEFAULT NULL,
  `lektion` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `italienisch_woerter_und_saetze`
--
ALTER TABLE `italienisch_woerter_und_saetze`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `italienisch_woerter_und_saetze`
--
ALTER TABLE `italienisch_woerter_und_saetze`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
