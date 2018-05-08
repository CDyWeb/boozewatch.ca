<?

class CoreCatList extends GenericList {

  public $withCheckboxes=true;
  
  public function getListValue($manager,$fieldName,$line,$maxlength) {
    $res=parent::getListValue($manager,$fieldName,$line,$maxlength);
    if (($fieldName=='name') && !empty($line['id'])) {
      $res='<a href="'.getConfigItem('base_url').'/body/'.$line['id'].'/'.getPermalinkName($line['name']).'.html">'.$res.'</a>';
    }
    return $res;
  }
  
  protected function trFootCheckboxItems($url) {
    $result=parent::trFootCheckboxItems($url);
    unset($result['d']);
    #--
    $result['xls']=
<<<HTML
<td width='35' align='center'>
<a href='{$this->uri(null)}' onclick="var ids=jq('#body-{$this->tableId} INPUT').serialize(); if (ids=='') return false; this.href='{$url}?export=xls&'+ids; return true;"><img height='16' src='{$this->resources_url('/img/icon/xls.png')}' alt='' title='{$this->cmsTranslate("XLS Export")}' border='0' /></a>
</td>
HTML;
    #--
    return $result;
  }

}


//end