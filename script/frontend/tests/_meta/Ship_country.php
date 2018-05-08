<?

$meta[tbl_name('ship_country')]=
<<<SQL
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `rate` decimal(10,2) NOT NULL DEFAULT '0.00',
  `with_tax` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
SQL;

//end