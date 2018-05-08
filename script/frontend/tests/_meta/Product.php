<?

require 'Brand.php';

$meta[tbl_name('product')]=
<<<SQL
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tree_id` int(11) NOT NULL DEFAULT '0',
  `sku` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `brand` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tax` int(11) NOT NULL DEFAULT '1',
  `discount_absolute` decimal(10,2) DEFAULT NULL,
  `discount_percent` decimal(10,2) DEFAULT NULL,
  `discount_start` date DEFAULT NULL,
  `discount_end` date DEFAULT NULL,
  `shippingrate` int(11) DEFAULT NULL,
  `description` text,
  `img` varchar(255) DEFAULT NULL,
  `subimg1` varchar(255) DEFAULT NULL,
  `subimg2` varchar(255) DEFAULT NULL,
  `subimg3` varchar(255) DEFAULT NULL,
  `option1` varchar(255) DEFAULT NULL,
  `option2` varchar(255) DEFAULT NULL,
  `option3` varchar(255) DEFAULT NULL,
  `is_new` tinyint(4) NOT NULL DEFAULT '1',
  `is_hot` tinyint(4) NOT NULL DEFAULT '0',
  `is_home` tinyint(4) NOT NULL DEFAULT '0',
  `stock` int(11) DEFAULT NULL,
  `units` enum('piece') NOT NULL DEFAULT 'piece',
  `quantity` float NOT NULL DEFAULT '1',
  `deliveryperiod` int(11) DEFAULT '10',
  `no_stock_action` enum('ignore','notify','hide') NOT NULL DEFAULT 'notify',
  `orderby` int(11) NOT NULL DEFAULT '1',
  `active` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `tree_id` (`tree_id`),
  KEY `brand` (`brand`),
  KEY `tax` (`tax`),
  KEY `shippingrate` (`shippingrate`)
SQL;

//end