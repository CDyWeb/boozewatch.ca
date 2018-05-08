<?

class ProductSizeManager extends CCMSDomainManager {

	function __construct() {

		parent::__construct('ProductSize');
    $this->addFieldConfig("name=product;type=".CCMSDomainField::FIELDTYPE_FK.";required=1;attributes=table:".$this->getTablePrefix()."product,caption:name,delete:cascade");
    $this->addFieldConfig("name=size;type=".CCMSDomainField::FIELDTYPE_FK.";required=1;attributes=table:".$this->getTablePrefix()."size,caption:name,delete:cascade");
    
		$this->addFieldConfig("name=active;type=".CCMSDomainField::FIELDTYPE_BOOL.";required=1;defaultValue=1");
    $this->addFieldConfig("name=sku;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");
    $this->addFieldConfig("name=price;type=".CCMSDomainField::FIELDTYPE_CUR.";required=0");
    $this->addFieldConfig("name=stock;type=".CCMSDomainField::FIELDTYPE_INT.";required=0;defaultValue=0");
    $this->addFieldConfig("name=img;type=".CCMSDomainField::FIELDTYPE_IMG.";required=0");
    
    $this->editFields=getConfigItem("ProductSizeManager.editFields",array('stock'));

		$this->init();
	}
  
  //@Override
  protected function createTable() {
    parent::createTable();
    executeSql("ALTER TABLE `{$this->getTableName()}` ADD UNIQUE ( `product` , `size` )");
  }

  public function byProduct($id) {
    if (is_array($id)) return getTableArray("select * from `{$this->getTableName()}` where `product` in (".implode(',',$id).")","size");
    return getTableArray("select * from `{$this->getTableName()}` where `product`=".intval($id),"size");
  }
  
  public function valuesFromProduct() {
    $res=array(
      'active'=>isset($_POST['product_size_active'])?$_POST['product_size_active']:array(),
    );
    $grp=array();
    foreach (getTableArray('select id, sizegroup from '.tbl_name('size')) as $line) {
      if (empty($_POST['sizegroup_'.$line['sizegroup']])) continue;
      $size_id=intval($line['id']);
      $grp[$size_id]=$size_id;
    }
    foreach ($this->getEditFields() as $f) {
      $res[$f]=array();
      foreach ($_POST as $k=>$v) if (preg_match('#^product_size_'.preg_quote($f).'_(\d+)$#',$k,$match)) {
        $size_id=intval($match[1]);
        if (isset($grp[$size_id])) $res[$f][$size_id]=$v;
      }
    }
    return $res;
  }

  public function saveFromProduct($id,$data) {
    $sizeManager=new SizeManager();
    $sizes=$sizeManager->getAllExt(array());
    $exists=$this->byProduct($id);
    $autoActive=getConfigItem('ProductSizeManager.autoActive',false);
    foreach ($sizes as $size) {
      if (!isset($data['stock'][$size['id']]) && !isset($data['price'][$size['id']]) && !isset($data['sku'][$size['id']])) {
        if (isset($exists[$size['id']])) {
          $ps=$exists[$size['id']];
          executeSql('delete from '.$this->getTableName().' where id='.$ps['id']);
        }
        continue;
      }
      $set_sql='`product`='.$id.', `size`='.$size['id'];
      
      if (isset($data['active'])) {
        if ($autoActive) {
          $is_active=false;
          foreach ($this->getEditFields() as $f) if (($f!='img') && (strlen($data[$f][$size['id']])>0)) $is_active=true;
        } else {
          $is_active=in_array($size['id'],$data['active']);
        }
        $set_sql.=', `active`='.($is_active?'1':'0');
      }

      foreach ($this->getEditFields() as $f) if ($f!='img') {
        //$field=$this->getField($f);
        if (isset($data[$f])) $set_sql.=", `{$f}`=".(strlen($data[$f][$size['id']])==0?'NULL':"'".db_escape($data[$f][$size['id']])."'");
      }

      if (isset($exists[$size['id']])) {
        $ps=$exists[$size['id']];
        $psid=$ps['id'];
        executeSql('update '.$this->getTableName().' set '.$set_sql.' where id='.$psid);
      } else {
        executeSql('insert into '.$this->getTableName().' set '.$set_sql);
        $psid=mysql_insert_id();
      }

      foreach ($this->getEditFields() as $f) if ($f=='img') {
        if (isset($_FILES['product_size_img_'.$size['id']])) {
          $n=$_FILES['product_size_img_'.$size['id']]['tmp_name'];
          if (file_exists($n) && (filesize($n)>0)) {
            $this->uploaded_image_resize($n,'img');
            $ext=explode('.',$_FILES['product_size_img_'.$size['id']]['name']);
            $ext=end($ext);
            $filename=sprintf("%s.%s.%s.%s.%s",$this->getName(),'img',$psid,md5(rand().time()),$ext);
            rename($n,'../'.$this->getImgDir('img').$filename);
            executeSql('update '.$this->getTableName().' set img='.dbStr($filename).' where id='.$psid);
          }
        }
      }
    }
  }
  
}

// end