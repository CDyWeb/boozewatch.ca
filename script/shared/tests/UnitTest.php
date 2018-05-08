<?php

require_once 'UnitTestCase.php';
require_once 'UnitTestSuite.php';
if (!defined('UNIT_TESTING')) define('UNIT_TESTING', true);

class UnitTest {

  protected static $instance;
  #public static $spyc;
  #public static $fixture;

  public function __construct(){
    self::$instance = $this;
  }

  public function getInstance() {
    return self::$instance;
  }

  public static function files($pattern, $path=".", $addpath=FALSE) {
    if (strpos($path, '/') === FALSE){
      if (function_exists('realpath') AND @realpath(dirname(__FILE__)) !== FALSE){
        $system_folder = realpath(dirname(__FILE__)).'/'.$path;
      }
    } else{
      $path = str_replace("\\", "/", $path);
    }
    if(substr($path,-1)!="/"){$path.="/";}
    $dir_handle = @opendir($path) or die("Unable to open $path");
    $outarr=array();

    while (false !== ($file = readdir($dir_handle))) {
      if (preg_match($pattern, $file)){
        if($addpath){$file=$path.$file;}
        $outarr[]=$file;
      }
    }
    closedir($dir_handle);
    return $outarr;
  }
}

//end