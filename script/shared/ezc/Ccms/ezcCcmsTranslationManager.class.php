<?php

class ezcCcmsTranslationManager extends ezcTranslationManager {

	protected $myBackend;
  
  protected static $ccmsInstance=null;

  public static function getCcmsInstance() {
    if (empty(self::$ccmsInstance)) {
      $a = new ezcCcmsTranslationBackend();
      self::$ccmsInstance=new ezcCcmsTranslationManager($a);
    }
    return self::$ccmsInstance;
  }

	function __construct(ezcTranslationBackend $backend) {
		parent::__construct($backend);
		$this->myBackend=$backend;
	}

	public function getBackend() {
		return $this->myBackend;
	}

}

//end