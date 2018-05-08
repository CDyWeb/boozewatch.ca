<?

global $error_handler_counter;
$error_handler_counter=0;

function myErrorHandler($errno, $errstr, $errfile, $errline) {
  
  global $error_handler_counter;
  $error_handler_counter++;

  error_log(__FILE__ . ": errno $errno, errstr $errstr, errfile $errfile, errline $errline");

  if (($errno!=E_NOTICE) && ($error_handler_counter<3) && (!preg_match('#(should be compatible with that of|mysql extension is deprecated)#',$errstr))) {
    $msg="
errno: $errno
errstr: $errstr
errfile: $errfile
errline: $errline";
    
    global $config;
    
    if (isset($_SERVER['WINDIR'])) {
      $fp=fopen('c:/php.err.txt','ab');
      fwrite($fp,__FILE__ . ": ".$msg);
      fclose($fp);
    } else {
      if (!defined('PHPUnit_MAIN_METHOD') && (ini_get("smtp_port") || ini_get("sendmail_path"))) {
        $from=$_SERVER["HTTP_HOST"]."<info@".$_SERVER["HTTP_HOST"].">";
        mail($config["support_email"],$errstr,$msg,"From: ".$from."\n");
        error_log("mail sent, subj=$errstr from=$from recipient={$config["support_email"]}");
      }
    }

    if (function_exists("_log")) _log("errno $errno, errstr $errstr, errfile $errfile, errline $errline",LOG_LEVEL_ERROR);
    
  }

  return false;
}

set_error_handler("myErrorHandler");