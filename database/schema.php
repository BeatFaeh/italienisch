<?php
declare(strict_types=1);
$db->query("CREATE TABLE IF NOT EXISTS italienisch_woerter_und_saetze (
 id INT NOT NULL AUTO_INCREMENT,
 wort_de VARCHAR(250) DEFAULT NULL,
 wort_it VARCHAR(250) DEFAULT NULL,
 satz_de VARCHAR(4000) DEFAULT NULL,
 satz_it VARCHAR(4000) DEFAULT NULL,
 lektion INT DEFAULT NULL,
 PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$db->query("CREATE TABLE IF NOT EXISTS italienisch_einstellungen (
 einstellungsname VARCHAR(100) NOT NULL,
 einstellungswert TEXT NOT NULL,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(einstellungsname)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$db->query("CREATE TABLE IF NOT EXISTS italienisch_verben (
 id INT NOT NULL AUTO_INCREMENT,
 verb_it VARCHAR(250) DEFAULT NULL,
 verb_de VARCHAR(250) DEFAULT NULL,
 praesens VARCHAR(500) DEFAULT NULL,
 perfekt VARCHAR(500) DEFAULT NULL,
 futur VARCHAR(500) DEFAULT NULL,
 imperativ VARCHAR(500) DEFAULT NULL,
 endung VARCHAR(50) DEFAULT NULL,
 PRIMARY KEY(id),
 FULLTEXT KEY ft_italienisch_verben (verb_it, verb_de, praesens, perfekt, futur, imperativ)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$db->query("CREATE TABLE IF NOT EXISTS italienisch_grammatik (
 id INT NOT NULL AUTO_INCREMENT,
 stichwort VARCHAR(250) DEFAULT NULL,
 erklaerung TEXT DEFAULT NULL,
 pdf VARCHAR(500) DEFAULT NULL,
 PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
