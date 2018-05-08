<?

$meta[tbl_name('shippingrate')]=
<<<SQL
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `rate` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tax` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tax` (`tax`)
SQL;

//end