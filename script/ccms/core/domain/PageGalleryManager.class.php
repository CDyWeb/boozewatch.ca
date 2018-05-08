<?

class PageGalleryManager extends CCMSDomainManager {

  function __construct() {
  
    parent::__construct("PageGallery");

    $this->addFieldConfig("name=page_id;type=".CCMSDomainField::FIELDTYPE_FK.";required=1;attributes=table:".$this->getTablePrefix()."page,caption:name,delete:cascade");
    $this->addFieldConfig("name=user_id;type=".CCMSDomainField::FIELDTYPE_FK.";required=0;attributes=table:".$this->getTablePrefix()."user,caption:name,delete:cascade");
    $this->addFieldConfig("name=title;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");
    $this->addFieldConfig("name=pubdate;type=".CCMSDomainField::FIELDTYPE_TIMESTAMP.";required=1;defaultValue=DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    $this->addFieldConfig("name=description;type=".CCMSDomainField::FIELDTYPE_TEXT.";required=0");
    $this->addFieldConfig("name=enclosure;type=".CCMSDomainField::FIELDTYPE_IMG.";required=0");
    $this->addFieldConfig("name=link;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");
    $this->addFieldConfig("name=is_home;type=".CCMSDomainField::FIELDTYPE_BOOL.";required=0;defaultValue=1");
    $this->addFieldConfig("name=is_hot;type=".CCMSDomainField::FIELDTYPE_BOOL.";required=0;defaultValue=1");
        
    $this->addFieldData("orderby",CCMSDomainField::FIELDTYPE_ORDERINDEX);

    $this->setListFields(array("title","enclosure"));
    $this->setEditFields(getConfigItem("Plugin_Gallery_PageGalleryManager.editFields",array('title','enclosure')));

    $this->setFilterFieldName("page_id");

    $this->init();
  }
  
  public function getItemName($line) {
    if (isset($line["title"])) return $line["title"];
    return $line["id"];
  }
  
  protected function extraSetSqlInsert() {
    return ", user_id=".$_SESSION["user"]["id"];
  }
  
  public function tempImageUpload(&$err) {
    if (empty($_FILES)) {
      $err='no upload';
      return false;
    }
    $upload=current($_FILES);
    if (empty($upload['name']) || empty($upload['tmp_name']) || !file_exists($upload['tmp_name']) || (filesize($upload['tmp_name'])==0)) { // || (($ext=$this->checkUploadedImg($upload['tmp_name']))===false)) {
      $err='upload failed';
      return false;
    }
    if (!isset($_GET['name']) || ($_GET['name']!='list')) return parent::tempImageUpload($err);
    #--
    $data=array('title'=>$upload['name'],'enclosure'=>serialize($upload));
    $save_err=array();
    $res=$this->save(0,$data,$save_err);
    return $res;
  }

  public function save($id, $data, &$err) {
    $enclosure=null;
    if (!empty($data['enclosure'])) $enclosure=unserialize($data['enclosure']);
    #--
    if (!$id && !empty($enclosure) && preg_match("#\.zip$#i",$enclosure["name"])) {
      $res=false;

      _require("PclZip.class.php");
      $img=array();
      $zip = new PclZip($enclosure["tmp_name"]);
      $list = $zip->listContent();
      if ($list===0) {
        return false;
      } else {
      foreach ($list as $file) {
        if ($file["size"]<1) continue;
        if (!preg_match("#\.(jpg|jpeg|png|gif)$#i",$file["filename"],$match)) continue;
          $file["ext"]=$match[1];
          $img[]=$file;
        }
      }
      if (count($img)==0) {
        return false;
      } else {
        foreach ($img as $file) {
          $extract = $zip->extractByIndex($file["index"],PCLZIP_OPT_EXTRACT_AS_STRING);
          if (count($extract)==1) $extract=current($extract);
          if (@$extract["status"]!="ok") continue;

          $fn=$enclosure["tmp_name"].'.extract';
          file_put_contents($fn,$extract["content"]);

          $imgdata=array(
            'title'=>preg_replace('#\.[^.]*$#','',$file["filename"]),
            'enclosure'=>serialize(array(
            'name'=>$file["filename"],
            'type'=>'image/jpeg',
            'tmp_name'=>$fn,
            'error'=>0,
            'size'=>$file['size'],
            )),
          );
          #var_dump($imgdata);
          $res |= parent::save($id,$imgdata,$err);
        }
      }
      return $res;
    }
    #--
    if (!empty($enclosure) && !preg_match("#\.(jpg|jpeg|png|gif)$#i",$enclosure["name"])) {
      return false;
    }
    #--
    if (empty($data['title']) && !empty($enclosure)) {
      $data['title']=preg_replace('#\.[^.]*$#','',$enclosure['name']);
    }
    $res=parent::save($id,$data,$err);
    if ($res) {
      $page=$this->getFilter();
      executeSql("update ".$this->getTablePrefix()."page set lastmod=now() where id={$page}");
    }
    return $res;
  }

}

// end