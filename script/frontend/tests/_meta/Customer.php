<?

$meta[tbl_name('customer')]=
<<<SQL
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `title` enum('mr','mrs','ms') DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) NOT NULL DEFAULT '',
  `dob` date DEFAULT NULL,
  `email` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `newsletter` tinyint(4) NOT NULL DEFAULT '1',
  `tel1` varchar(255) DEFAULT NULL,
  `tel2` varchar(255) DEFAULT NULL,
  `tel3` varchar(255) DEFAULT NULL,
  `adr1_address1` varchar(255) DEFAULT NULL,
  `adr1_address2` varchar(255) DEFAULT NULL,
  `adr1_city` varchar(255) DEFAULT NULL,
  `adr1_state` varchar(255) DEFAULT NULL,
  `adr1_zip` varchar(255) DEFAULT NULL,
  `adr1_country` varchar(255) DEFAULT NULL,
  `adr2_name` varchar(255) DEFAULT NULL,
  `adr2_address1` varchar(255) DEFAULT NULL,
  `adr2_address2` varchar(255) DEFAULT NULL,
  `adr2_city` varchar(255) DEFAULT NULL,
  `adr2_state` varchar(255) DEFAULT NULL,
  `adr2_zip` varchar(255) DEFAULT NULL,
  `adr2_country` varchar(255) DEFAULT NULL,
  `credit` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`)
SQL;

//end