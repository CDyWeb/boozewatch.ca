<?

ini_set('session.use_only_cookies', '1');
ini_set('session.use_trans_sid', '0');
ini_set('session.hash_function', '1'); //sha1
ini_set('session.hash_bits_per_character','6');
ini_set('session.gc_probability','10');
ini_set('session.gc_divisor','100');

define('LOG_LEVEL_NONE',0);
define('LOG_LEVEL_INFO',1);
define('LOG_LEVEL_DEBUG',2);
define('LOG_LEVEL_TRACE',3);
define('LOG_LEVEL_ERROR',1);
define('LOG_LEVEL_WARN',2);

define('LOG_DEST_HTML',1);
define('LOG_DEST_ERROR_LOG',2);
define('LOG_DEST_FILE',4);
define('LOG_DEST_FIREPHP',8);

if (empty($_SERVER['HTTP_HOST'])) $_SERVER['HTTP_HOST']='localhost';
if (empty($_SERVER['_URI'])) $_SERVER['_URI'] = isset($_SERVER['REQUEST_URI'])?preg_replace('#\?.*$#','',$_SERVER['REQUEST_URI']):'/';
if (empty($_SERVER['_MY_URL'])) $_SERVER['_MY_URL']=(isset($_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['_URI'];

global $site_config;
$site_config=array();

$site_config['database_driver']='mysql';
$site_config['database_host']='localhost';
$site_config['database_names']='utf8';
$site_config['database_scheme']=''; // aka database name
$site_config['database_username']='';
$site_config['database_password']='';
$site_config['database_prefix']=defined('UNIT_TESTING')?'test_':'ccms_';

$site_config['domain']='mydomain.com';
$site_config['rel_base']='/';

$site_config['host_base']='www.'.$site_config['domain'];
if (isset($_SERVER['HTTP_HOST']) and $_SERVER['HTTP_HOST']=='localhost') $site_config['host_base']='localhost';
$site_config['url_server']='http'.(isset($_SERVER['HTTPS'])?'s':'').'://'.$site_config['host_base'];
$site_config['url_base']=$site_config['url_server'].$site_config['rel_base'];

$site_config['rel_script']='script/';
$site_config['rel_httpdocs']='httpdocs/';

$site_config['abs_base']=realpath(dirname(__FILE__).'/..').'/';
$site_config['script_base']=$site_config['abs_base'].$site_config['rel_script'];
$site_config['public_base']=$site_config['abs_base'].$site_config['rel_httpdocs'];

$site_config['rel_app']='app/';
$site_config['script_app']=$site_config['script_base'].$site_config['rel_app'];
$site_config['public_app']=$site_config['public_base'].'__custom__/';

$site_config['language']=array(
	'available'=>array(
		'en',
	),
	'default'=>'en',
	'base'=>'en',
);

$site_config['currency']=array(
	'available'=>array(
		'USD'
	),
	'default'=>'USD',
	'base'=>'USD',
	'html'=>array(
		'EUR'=>'&euro;',
		'USD'=>'$',
		'CAD'=>'CA $',
		'GBP'=>'',
	),
	'locale'=>array(
		'EUR'=>'nl_NL',
		'USD'=>'en_US',
		'CAD'=>'en_CA',
		'GBP'=>'en_GB',
	),
);

$site_config['userimg_max_size']=array(1024,768);
$site_config['userimg_jpeg_quality']=90;

$site_config['rel_userfiles']='userfiles/';
$site_config['rel_userfiles_htmlfile']=$site_config['rel_userfiles'].'html_file/';
$site_config['rel_userfiles_userfile']=$site_config['rel_userfiles'].'user_file/';
$site_config['rel_userfiles_htmlimg']=$site_config['rel_userfiles'].'html_img/';
$site_config['rel_userfiles_userimg']=$site_config['rel_userfiles'].'user_img/';

$site_config['session.gc_maxlifetime']=(8*60*60);

# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# custom settings
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if (file_exists(dirname(__FILE__).'/custom.globals.inc.php')) require 'custom.globals.inc.php';

$site_config['abs_userfiles_htmlfile']=$site_config['public_base'].$site_config['rel_userfiles_htmlfile'];
$site_config['abs_userfiles_userfile']=$site_config['public_base'].$site_config['rel_userfiles_userfile'];
$site_config['abs_userfiles_htmlimg']=$site_config['public_base'].$site_config['rel_userfiles_htmlimg'];
$site_config['abs_userfiles_userimg']=$site_config['public_base'].$site_config['rel_userfiles_userimg'];

$site_config['url_userfiles_htmlfile']=$site_config['url_base'].$site_config['rel_userfiles_htmlfile'];
$site_config['url_userfiles_userfile']=$site_config['url_base'].$site_config['rel_userfiles_userfile'];
$site_config['url_userfiles_htmlimg']=$site_config['url_base'].$site_config['rel_userfiles_htmlimg'];
$site_config['url_userfiles_userimg']=$site_config['url_base'].$site_config['rel_userfiles_userimg'];

if (function_exists('date_default_timezone_set')) {
  if (empty($site_config['date_default_timezone'])) {
    $tld=preg_replace('#^.*\.(\w+)$#','$1',$site_config['domain']);
    switch($tld) {
      case 'be' :
      case 'nl' : $site_config['date_default_timezone']='Europe/Amsterdam'; break;
      case 'fr' : $site_config['date_default_timezone']='Europe/Paris'; break;
      case 'de' : $site_config['date_default_timezone']='Europe/Berlin'; break;
      case 'uk' : $site_config['date_default_timezone']='Europe/London'; break;
      case 'ca' : $site_config['date_default_timezone']='America/Toronto'; break;
      default : $site_config['date_default_timezone']='America/New_York'; break;
    }
  }
  date_default_timezone_set($site_config['date_default_timezone']); //stupid php 5 strict
}
ini_set('session.gc_maxlifetime', $site_config['session.gc_maxlifetime']);
session_set_cookie_params($site_config['session.gc_maxlifetime']);

if (empty($site_config['cke_css'])) $site_config['cke_css']=$site_config['url_base'].'shared/ccms/cke/cke.css';

define('DATABASE_PREFIX',$site_config['database_prefix']);

define('SITE_DOMAIN',$site_config['domain']);
define('SITE_BASE_PATH',$site_config['public_base']);
define('SITE_BASE_URI',$site_config['rel_base']);
define('SITE_BASE_URL',$site_config['url_base']);

//end