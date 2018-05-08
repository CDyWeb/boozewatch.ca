<?

require 'Product.php';
require 'Customer.php';
require 'ShippingRate.php';
require 'Tax.php';
require 'Ship_country.php';
require 'Sizegroup.php';
require 'Size.php';

$meta[tbl_name('order_log')]=
<<<SQL
  `order` int(11) NOT NULL DEFAULT '0',
  `status` varchar(255) NOT NULL DEFAULT '',
  `data` varchar(255) DEFAULT NULL,
  `dt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`order`,`status`,`dt`)
SQL;


$meta[tbl_name('order_product_log')]=
<<<SQL
  `order` int(11) NOT NULL DEFAULT '0',
  `customer` int(11) DEFAULT NULL,
  `amount` int(11) NOT NULL DEFAULT '0',
  `product` int(11) NOT NULL DEFAULT '0',
  `dt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`order`,`product`,`dt`),
  KEY `customer` (`customer`)
SQL;

$meta[tbl_name('order')]=
<<<SQL
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(255) NOT NULL DEFAULT '',
  `uid` varchar(40) NOT NULL DEFAULT '',
  `customer` int(11) DEFAULT NULL,
  `customer_details` text NOT NULL,
  `customer_name` text NOT NULL,
  `cart` text NOT NULL,
  `notes` text,
  `printed` tinyint(4) NOT NULL DEFAULT '0',
  `currency` varchar(3) NOT NULL DEFAULT '',
  `currency_factor` float NOT NULL DEFAULT '1',
  `date_insert` date NOT NULL DEFAULT '0000-00-00',
  `date_update` date NOT NULL DEFAULT '0000-00-00',
  `status` enum('new','in_process','backorder','payed','sent','closed','cancelled') NOT NULL DEFAULT 'new',
  `payment` enum('other','visit','in_advance','rembours','ideal','paypal','account','cc','visit_in_advance') NOT NULL DEFAULT 'in_advance',
  `transaction_id` varchar(255) DEFAULT NULL,
  `am_subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `am_processing` decimal(10,2) DEFAULT NULL,
  `am_transport` decimal(10,2) DEFAULT NULL,
  `am_discount` decimal(10,2) DEFAULT NULL,
  `am_tax` decimal(10,2) DEFAULT NULL,
  `am_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tracking` varchar(255) DEFAULT NULL,
  `voucher` varchar(255) DEFAULT NULL,
  `reward` float DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer` (`customer`)
SQL;

//end