<?

class CorePageList extends GenericList {

	private $children=array();
  
  public $sortable=false;

	public function outputTable($data=null) {
		if ($data===null) $data=$this->getManager()->getListData();
		$root=array();
		foreach (array_keys($data) as $key) {
			$pid=$data[$key]["parent_id"];
			if (!$pid) $root[]=$data[$key];
			else $this->children[$pid][]=$data[$key];
		}
		parent::outputTable($root);
	}
  
  protected function getRowButton($btnName,$line,$lineCount,$dataCount) {
    if (($btnName=='delete') && !$line['can_delete']) return "<td width='20'></td>";
    if (($btnName=='edit') && !$line['can_edit']) return "<td width='20'></td>";
    return parent::getRowButton($btnName,$line,$lineCount,$dataCount);
  }
  
  public function getListValue($manager,$fieldName,$line,$maxlength) {
    $res=parent::getListValue($manager,$fieldName,$line,$maxlength);
    if (($fieldName=='name') && isset($this->children[$line['id']])) {
      $res="<div>{$res}</div><div style='margin:5px 0; padding:5px 0; border:1px dashed #CCC'>".parent::getTableHtml($this->children[$line['id']],true)."</div>";
    }
    return $res;
  }
	
	protected function tableRow($id,$td,$class='trData') {
		$res=parent::tableRow($id,$td,$class);
/**
		if (isset($this->children[$id])) {
			$colspan=$this->colCount-2;
			$res.=
"<tr>
	<td class='tdBorderLeft'>&nbsp;</td>
	<td colspan='{$colspan}'><div style='margin:2px 0 20px 45px; padding:5px 0 5px; border:1px dashed #CCC'>".parent::getTableHtml($this->children[$id],true)."</div></td>
	<td class='tdBorderRight'>&nbsp;</td>
</tr>";

		}
**/
		return $res;
	}

}

//end