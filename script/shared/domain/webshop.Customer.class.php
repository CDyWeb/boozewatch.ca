<?

class Customer {

  public $id=0;

  public function __construct(array $line=null) {
    if ($line==null) {
      $line=array(
      "first_name"=>"",
      "last_name"=>"",
      );
    }
    $this->syncFromArray($line);
  }
  
  public function __get($k) {
    if (!isset($this->$k)) return "";
    return $this->$k;
  }
  
  public function toArray() {
    return get_object_vars($this);
  }
  
  public function syncFromArray(array $line) {
    foreach ($line as $k=>$v) {
      if ($k=='cart') continue;
      $this->$k=$v;
      if (((strlen($v)==0) || (empty($line["adr2_name"]))) && preg_match("#^adr2_(.*)$#",$k,$match)) {
        $copyFrom="adr1_".$match[1];
        if ($match[1]=="name") {
          $this->$k=trim($line['first_name'].' '.$line['last_name']);
          continue;
        }
        if (!empty($line[$copyFrom])) $this->$k=$line[$copyFrom];
      }
    }
  }
  
  public function getId() {
    return $this->id;
  }

  public function getCountry() {
    $res=@$this->adr2_country?$this->adr2_country:$this->adr1_country;
    _log("Customer::getCountry {$res}");
    return $res;
  }
  public function getRegion() {
    $res=@$this->adr2_state?$this->adr2_state:$this->adr1_state;
    _log("Customer::getRegion {$res}");
    return $res;
  }

  public function getShippingCountry() {
    $c=db_escape($this->getCountry());
    if (empty($c)) $sql = "select * from ".tbl_name("ship_country")." limit 1";
    else $sql = "select * from ".tbl_name("ship_country")." where name like '{$c}' limit 1";
    $res=getOneRow($sql);
    if (empty($res)) $res=getOneRow("select * from ".tbl_name("ship_country")." limit 1");
    _log("Customer::getShippingCountry");
    _log($res);
    return $res;
  }
  
  public function getShippingRegion() {
    $country=$this->getShippingCountry();
    if (empty($country['id'])) $sql = "select * from ".tbl_name("region")." limit 1";
    else {
      $c=db_escape($this->getRegion());
      if (empty($c)) $sql = "select * from ".tbl_name("region")." where `country`={$country['id']} limit 1";
      else $sql = "select * from ".tbl_name("region")." where `country`={$country['id']} and name like '{$c}' limit 1";
      $res=getOneRow($sql);
    }
    _log("Customer::getShippingRegion");
    _log($res);
    return $res;
  }
  
  public function storeCart(Cart $cart) {
    if (empty($this->id)) return;
    if (empty($cart)) {
      executeSql('update '.tbl_name('customer').' set `cart`=null where id='.$this->id);
      return;
    }
    $str=$cart->serialize();
    if (empty($str)) executeSql('update '.tbl_name('customer').' set `cart`=null where id='.$this->id);
    else executeSql('update '.tbl_name('customer').' set `cart`='.dbStr($str).' where id='.$this->id);
    return $cart;
  }

  public function loadCart(Cart $cart) {
    if (empty($cart)) return;
    if (empty($this->id)) return null;
    $str=getOneValue('select `cart` from '.tbl_name('customer').' where id='.$this->id);
    #die($str);
    $cart->unserialize($str);
    return $cart;
  }
  
}

//end