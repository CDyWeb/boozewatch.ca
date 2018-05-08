<?

class CoreSidebarEditor extends GenericEditor {

	protected $settingsFile=null;

	public function outputContent() {
		$this->settingsFile=$this->getManager()->getSettingsFile($this->line);
		parent::outputContent();
	}

	protected function outputStart() {
		if ($this->settingsFile) {
			require_once $this->settingsFile;
		}
    #--
		$func = "sidebarEditor_preStartContents";
		if (function_exists($func)) $func($this);
    #--
		parent::outputStart();
    #--
		$func = "sidebarEditor_postStartContents";
		if (function_exists($func)) $func($this);
    #--
	}

	protected function getTypes() {
		$result=array('text'=>$this->domainTranslate('Sidebar.sidebar_type.text'));

    $arr=array();
		if (file_exists($pn=APP_PATH.'sidebar')) $arr=array_merge(readdir_ls($pn),$arr); 

		foreach ($arr as $fn=>$path) {
			if ($fn=="_base") continue;
			if (!file_exists($path."/id.txt")) continue;

			$name=$fn;
			$txt=explode("\n",@file_get_contents($path."/id.txt"));
			if (count($txt)>0) {
				$name=$txt[0];
				$lang=preg_replace('#_.*$#','',getConfigItem('system_lang'));
				foreach ($txt as $i=>$expr) if (($i>0) && preg_match("#^".$lang.":(.*)$#",$expr,$match)) {
					$name=trim($match[1]);
					break;
				}
			}
			$result[$fn]=$name;
		}
		return $result;
	}
	
	protected function customEdit($field) {
		if ($field->name=="sidebar_type") {
      $field->type=CCMSDomainField::FIELDTYPE_ENUM;
      $field->attributes=$this->getTypes();
    }
    return parent::customEdit($field);
	}
	
	public function editField($field) {
    #--
    $func = "sidebarEditor_preEditField";
		if (function_exists($func)) $func($this,$field);
    #--
		parent::editField($field);
    #--
    $func = "sidebarEditor_postEditField";
		if (function_exists($func)) $func($this,$field);
    #--
	}
	
	protected function outputEnd() {
		if ($this->settingsFile) {
			require_once $this->settingsFile;
		}
    #--
		$func = "sidebarEditor_preEndContents";
		if (function_exists($func)) $func($this);
    #--
		parent::outputEnd();
    #--
		$func = "sidebarEditor_postEndContents";
		if (function_exists($func)) $func($this);
    #--
	}

}

//end