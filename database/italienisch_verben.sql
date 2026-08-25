-- Tabellenstruktur für `italienisch_verben`
-- Angepasst auf verb_it / verb_de und Volltextindex.

CREATE TABLE `italienisch_verben` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `verb_it` varchar(250) DEFAULT NULL,
  `verb_de` varchar(250) DEFAULT NULL,
  `praesens` varchar(500) DEFAULT NULL,
  `perfekt` varchar(500) DEFAULT NULL,
  `futur` varchar(500) DEFAULT NULL,
  `imperativ` varchar(500) DEFAULT NULL,
  `endung` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `ft_italienisch_verben` (`verb_it`,`verb_de`,`praesens`,`perfekt`,`futur`,`imperativ`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
