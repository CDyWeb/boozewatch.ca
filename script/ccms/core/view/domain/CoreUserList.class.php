<?

class CoreUserList extends GenericList {

	//@Override
	public function getListValue($manager,$fieldName,$line,$maxlength) {
		if ($fieldName=="_name") return "{$line["first_name"]} {$line["last_name"]}";
		return parent::getListValue($manager,$fieldName,$line,$maxlength);
	}
  
  protected function getRowButton($btnName,$line,$lineCount,$dataCount) {
    if ($line['id']==1) {
      if ($btnName=='delete') return '<td></td>';
      if (($btnName=='edit') && ($_SESSION['user']['id']!=1)) return '<td></td>';
    }
    return parent::getRowButton($btnName,$line,$lineCount,$dataCount);
  }

}


//end