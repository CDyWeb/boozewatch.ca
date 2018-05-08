<?

$meta[tbl_name('tree')]=
<<<SQL
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `parent_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `page` varchar(255) DEFAULT NULL,
  `args` varchar(255) DEFAULT NULL,
  `class` varchar(255) DEFAULT NULL,
  `text` text,
  `orderby` int(11) NOT NULL DEFAULT '1',
  `user_type` enum('user','editor','super') NOT NULL DEFAULT 'editor',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
SQL;

//end