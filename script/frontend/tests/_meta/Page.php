<?

require 'Settings.php';
require 'Aliasdomain.php';
require 'Tree.php';

$meta[tbl_name('page')]=
<<<SQL
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `tree_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `page_type` enum('text','menu','link','plugin') NOT NULL DEFAULT 'text',
  `attributes` varchar(255) DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_keywords` text,
  `meta_description` text,
  `uri` varchar(255) DEFAULT NULL,
  `text` text,
  `can_edit` tinyint(4) DEFAULT '1',
  `can_delete` tinyint(4) DEFAULT '1',
  `can_move` tinyint(4) DEFAULT '1',
  `can_have_children` tinyint(4) DEFAULT NULL,
  `lastmod` datetime DEFAULT NULL,
  `sitemap_changefreq` enum('always','hourly','daily','weekly','monthly','yearly','never') DEFAULT NULL,
  `sitemap_priority` decimal(3,1) DEFAULT '0.5',
  `orderby` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `tree_id` (`tree_id`),
  KEY `parent_id` (`parent_id`)
SQL;

//end