<?php

global $site_config;
if (empty($site_config['database_layer'])) {
  $site_config['database_layer'] = 'legacy';
}
require "mysql.{$site_config['database_layer']}.inc.php";

