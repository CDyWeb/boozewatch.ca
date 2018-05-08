<?

$meta[tbl_name('brand')]=
<<<SQL
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `img` varchar(255) DEFAULT NULL,
  `description` text,
  `link` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
SQL;

//end