<?

$meta[tbl_name('aliasdomain')]=
<<<SQL
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `domain` varchar(255) NOT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_keywords` text,
  `meta_description` text,
  `text` text,
  `lastmod` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
SQL;

//end