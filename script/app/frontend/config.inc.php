<?

#define('MAILFORM_CAPTCHA','_captcha');

if (false && !isset($_SERVER["WINDIR"])) {
	$config["logging_sql"] = LOG_LEVEL_NONE;
	$config["logging_level"] = LOG_LEVEL_NONE;
	$config["logging_dest"] = 0; //LOG_DEST_FILE;
  //$config["logging_file"] = 'log.txt';
}

$config['html_output_type'] = 'HTML5';

$config["html_template_dir"] = SITE_BASE_PATH."template/html/";
$config["html_base_href"] = SITE_BASE_URL."template/html/";


//end