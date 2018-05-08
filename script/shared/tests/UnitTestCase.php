<?php

class UnitTestCase extends PHPUnit_Framework_TestCase {

	private $tables=array();

	function __construct() {
		log_message('debug', get_class($this).' UnitTestCase constructed');
	}
  
  function setUpFixtures($data,$meta=null) {
    executeSql('SET FOREIGN_KEY_CHECKS=0');
    foreach ($data as $tbl_name=>$lines) {
      if (isset($meta[$tbl_name])) {
        executeSql('create table if not exists `'.$tbl_name.'` ('.$meta[$tbl_name].') ENGINE=InnoDB DEFAULT CHARSET=utf8');
      }
      foreach ($lines as $line) {
        $set=array();
        foreach ($line as $k=>$v) {
          if ($v===null) $set[]=$k.'=NULL';
          else $set[]='`'.$k.'`='.dbStr($v);
        }
        if (!empty($line['id'])) executeSql('replace into `'.$tbl_name.'` set '.implode($set,','));
        else executeSql('insert into `'.$tbl_name.'` set '.implode($set,','));
      }
    }
    executeSql('SET FOREIGN_KEY_CHECKS=1');
  }
  function tearDownFixtures($data) {
    #executeSql('SET FOREIGN_KEY_CHECKS=0');
    $data=array_reverse($data);
    foreach ($data as $tbl_name=>$lines) {
      if (isset($lines[0]['id'])) {
        $lines=array_reverse($lines);
        foreach ($lines as $line) {
          executeSql('delete from `'.$tbl_name.'` where id='.$line['id']);
        }
      } else {
        executeSql('truncate table `'.$tbl_name.'`');
      }
    }
    #executeSql('SET FOREIGN_KEY_CHECKS=1');
  }

	/**
	* simple timer function
	* start timer with: $start = $this->timeit();
	* stop timer with: $taken_time = $this->timeit($start);
	* can be done unlimited times for arbitrary starting points
	*/
	function timeit($timearr=array()){
		if($timearr!=array()){
			$end = split(' ', microtime());
			return $end[0]-$timearr[0]+$end[1]-$timearr[1];
		}else{
			return split(' ', microtime());
		}
	}

	/**
	* asserts that $arr2 wholly contains $arr
	*/
	function assertContaining($arr, $arr2, $msg='') {
		if (0 != count(array_diff_assoc($arr, $arr2))) {
			throw new PHPUnit_Framework_AssertionFailedError(
				"The given array does not contain the expected array!
				Given: ".print_r($arr2, true)."
				Expected to contain: ".print_r($arr, true)
			);
		}
		//PHPUnit_Framework_Assert::assertEquals(0, count(array_diff_assoc($arr, $arr2)), $msg);
	}

	/**
	* asserts an integer difference
	*/
	function assertDifference($diff, $count1, $count2){
		PHPUnit_Framework_Assert::assertEquals($diff, $count2-$count1);
	}

	/**
	* asserts an integer difference, with calling of an action in between);
	*/
	function assertDifferenceOfAction($diff, $count1, $action, $count2){
		PHPUnit_Framework_Assert::assertEquals($diff, $count2-$count1);
	}

	/**
	* asserts a key in the error array returned by the "errors()" method of that object
	*/
	function assertContainsError($key, $obj){
		$errors = $obj->getErrors();
		if(!isset($errors[$key])){
			throw new PHPUnit_Framework_AssertionFailedError(get_class($obj)." doesn't contain expected error: ".$key);
		}
	}

	/**
	* asserts a number of elements of $arr
	*/
	function assertCounts($count, $arr, $msg=""){
		//if($count!=count($arr)){
		//    throw new PHPUnit_Framework_AssertionFailedError("Array doesn't contain right number of elements.\n\nExpected array count: $count\nGot: ".count($arr));
		//}
		$this->assertEquals($count,count($arr),$msg);
	}

}