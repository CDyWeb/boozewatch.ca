<?

$meta[tbl_name('tax')]=
<<<SQL
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `percent` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
SQL;

//end