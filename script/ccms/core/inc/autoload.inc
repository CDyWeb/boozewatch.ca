<?

function getEzcAutoload() {
  global $ezcAutoload;
  if (empty($ezcAutoload)) {
    require SHARED_PATH.'/ezc/ezc.inc.php';
    $ezcAutoload=new CcmsEzcAutoload();
  }
  return $ezcAutoload;
}

function getResourcePath($resource) {
	$path = CMS_PATH.'/custom/'.$resource;
	if (!file_exists($path)) $path = APP_PATH.'/'.$resource;
	if (!file_exists($path)) $path = CMS_PATH.'/core/'.$resource;
	if (!file_exists($path)) $path = CMS_PATH.'/'.$resource;
	if (!file_exists($path)) $path = BASE_PATH.'/'.$resource;
	if (!file_exists($path)) $path = SHARED_PATH.'/cyane/'.$resource;
	if (!file_exists($path)) $path = false;
	return $path;
}

function _require($resource,$once=true) {
	if (!($path=getResourcePath($resource))) {
		error_log('FATAL: cannot find resource '.CMS_PATH." : $resource");
		throw new Exception('cannot find resource '.CMS_PATH." : $resource");
	}
	if ($once) require_once($path);
	else require $path;
}

function ccms_autoload($classname) {
	if (getResourcePath($fn="lib/{$classname}.class.php")) _require($fn);
	else if (getResourcePath($fn="lib/{$classname}.interface.php")) _require($fn);
	else if (getResourcePath($fn="domain/{$classname}.class.php")) _require($fn);
	else if (getResourcePath($fn="domain/{$classname}.interface.php")) _require($fn);
	else if (getResourcePath($fn="model/{$classname}.class.php")) _require($fn);
	else if (getResourcePath($fn="model/{$classname}.interface.php")) _require($fn);
	else if (getResourcePath($fn="view/{$classname}.class.php")) _require($fn);
	else if (getResourcePath($fn="view/domain/{$classname}.class.php")) _require($fn);
	else if (getResourcePath($fn="controller/{$classname}.class.php")) _require($fn);
	else if (getResourcePath($fn="inc/{$classname}.class.php")) _require($fn);
	else {
		$ezcAutoload=getEzcAutoload();
		if (!$ezcAutoload->autoload($classname)) {
      log_message("trace","__autoload - {$classname} not found");
      return false;
    }
	}
	log_message("trace","{$classname} auto loaded");
}

spl_autoload_register('ccms_autoload');


