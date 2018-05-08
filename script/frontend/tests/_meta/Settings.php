<?

$meta[tbl_name('settings')]=
<<<SQL
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `str` varchar(255) DEFAULT NULL,
  `txt` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
SQL;

//end