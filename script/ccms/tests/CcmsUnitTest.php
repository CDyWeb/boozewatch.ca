<?php

class CcmsUnitTest extends UnitTest {

    public function __construct(){
        require_once dirname(__FILE__)."/../ccms.inc.php";
        global $config;
        $config["logging_dest"] = $config["logging_dest"] |= LOG_DEST_HTML;
        self::$instance = $this;
    }

}

//end