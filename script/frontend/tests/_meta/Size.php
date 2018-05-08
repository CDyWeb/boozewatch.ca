<?

$meta[tbl_name('size')]=
<<<SQL
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sizegroup` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `orderby` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `sizegroup` (`sizegroup`)
SQL;

//end