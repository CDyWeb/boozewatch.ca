<?

chdir('../../frontend');
require "inc/ccms.inc";

$config["logging_level"] = LOG_LEVEL_TRACE;
$config["logging_dest"] = LOG_DEST_FIREPHP;

$event=$_GET['event'];
if (!$event) die('event?');

$arg1=@$_GET['arg1'];

require dirname(__FILE__)."/../cyane/events.inc.php";

$event($arg1);

//end