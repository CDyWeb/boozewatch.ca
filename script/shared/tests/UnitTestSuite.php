<?php

abstract class UnitTestSuite {

  #public function run(PHPUnit_Framework_TestResult $result = NULL, $filter = FALSE, array $groups = array(), array $excludeGroups = array()) {
    #die("---");
    #}

  public function _main() {
    ob_start();
    PHPUnit_TextUI_TestRunner::run($this->suite());
    $res=ob_get_contents();
    ob_end_clean();
    echo $res;
  }

  public static function main() {
    $e=explode("::",PHPUnit_MAIN_METHOD);
    eval("\$obj=new {$e[0]}();");
    $obj->_main();
  }
}