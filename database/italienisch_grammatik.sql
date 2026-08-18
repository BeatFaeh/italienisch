CREATE TABLE `italienisch_grammatik` (
  `id` int(11) NOT NULL,
  `stichwort` varchar(250) DEFAULT NULL,
  `erklaerung` text DEFAULT NULL,
  `pdf` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

ALTER TABLE `italienisch_grammatik`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `italienisch_grammatik`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
