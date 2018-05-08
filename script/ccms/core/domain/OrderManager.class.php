<?

class OrderManager extends CCMSDomainManager {

  function __construct() {
    
    parent::__construct('Order');

    $this->addFieldConfig('name=order_id;type='.CCMSDomainField::FIELDTYPE_STRING.';required=1');
    $this->addFieldConfig('name=uid;type='.CCMSDomainField::FIELDTYPE_STRING.';length=40;required=1');
    $this->addFieldConfig('name=customer;type='.CCMSDomainField::FIELDTYPE_FK.';required=0;attributes=table:'.$this->getTablePrefix().'customer,caption:last_name,delete:set null');
    $this->addFieldConfig('name=customer_details;type='.CCMSDomainField::FIELDTYPE_TEXT.';required=1');
    $this->addFieldConfig('name=customer_name;type='.CCMSDomainField::FIELDTYPE_TEXT.';required=1');
    $this->addFieldConfig('name=cart;type='.CCMSDomainField::FIELDTYPE_TEXT.';required=1');
    $this->addFieldConfig('name=notes;type='.CCMSDomainField::FIELDTYPE_TEXT.';required=0');
    $this->addFieldConfig('name=pon;type='.CCMSDomainField::FIELDTYPE_STRING.';required=0');
    $this->addFieldConfig('name=printed;type='.CCMSDomainField::FIELDTYPE_BOOL.';required=1;defaultValue=0');
    $this->addFieldConfig('name=currency;type='.CCMSDomainField::FIELDTYPE_STRING.';required=1;length=3');
    $this->addFieldConfig('name=currency_factor;type='.CCMSDomainField::FIELDTYPE_FLOAT.';required=1;defaultValue=1');
    
    $this->addFieldConfig('name=date_insert;type='.CCMSDomainField::FIELDTYPE_DATE.';required=1;attributes=on_insert');
    $this->addFieldConfig('name=date_update;type='.CCMSDomainField::FIELDTYPE_DATE.';required=1;attributes=on_update');
    
    $this->addFieldConfig('name=status;type='.CCMSDomainField::FIELDTYPE_ENUM.';required=1;defaultValue=new;attributes=new,in_process,backorder,payed,sent,closed,cancelled');
    $this->addFieldConfig('name=payment;type='.CCMSDomainField::FIELDTYPE_ENUM.';required=1;defaultValue=in_advance;attributes=other,visit,in_advance,rembours,ideal,mrcash,paypal,account,cc,visit_in_advance,directebanking');
    $this->addFieldConfig('name=shippingrate;type='.CCMSDomainField::FIELDTYPE_FK.';required=0;attributes=table:'.$this->getTablePrefix().'shippingrate,caption:name,delete:set null');
    $this->addFieldConfig('name=transaction_id;type='.CCMSDomainField::FIELDTYPE_STRING.';required=0;');

    $this->addFieldConfig('name=am_subtotal;type='.CCMSDomainField::FIELDTYPE_CUR.';required=1');
    $this->addFieldConfig('name=am_processing;type='.CCMSDomainField::FIELDTYPE_CUR.';required=0');
    $this->addFieldConfig('name=am_transport;type='.CCMSDomainField::FIELDTYPE_CUR.';required=0');
    $this->addFieldConfig('name=am_discount;type='.CCMSDomainField::FIELDTYPE_CUR.';required=0');
    $this->addFieldConfig('name=am_tax;type='.CCMSDomainField::FIELDTYPE_CUR.';required=0');
    $this->addFieldConfig('name=am_total;type='.CCMSDomainField::FIELDTYPE_CUR.';required=1');
    
    $this->addFieldConfig('name=reward;type='.CCMSDomainField::FIELDTYPE_FLOAT.';required=0');

    $this->addFieldConfig('name=tracking;type='.CCMSDomainField::FIELDTYPE_STRING.';required=0;');
    $this->addFieldConfig('name=voucher;type='.CCMSDomainField::FIELDTYPE_STRING.';required=0;');
    $this->addFieldConfig("name=language;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0;length=5;attributes=type_is_char;");

    $this->setListFields(array('printed','order_id','date_insert','customer_name','am_total'));
    $this->setEditFields(array('printed','order_id','date_insert','customer','status','am_total','tracking'));
    
    $l=getConfigItem('language');
    if (count($l['available'])>1) {
      $this->listFields[]='language';
      $this->editFields[]='language';
    }
    
    $this->addable=false;

    $this->init();
  }
  
  //@Override
  public function getOrderBy() {
    return "id desc";
  }

  //@Override
  protected function getItemNameFieldName() {
    return 'order_id';
  }
  /*
  public function getItemName($line) {
    //return strtolower(get Domain Text("Order.order_id"))." ".$line["order_id"];
    return $line["order_id"];
  }
  */
  
  public function orderPayed($order) {
    if (!function_exists("on_order_payed")) require getConfigItem('script_base')."shared/cyane/events.inc.php";
    on_order_payed($order);
  }

  public function orderCancelled($order) {
    #--
    if (!function_exists("on_order_cancelled")) require getConfigItem('script_base')."shared/cyane/events.inc.php";
    on_order_cancelled($order);
    #--
    if (!getConfigItem('OrderManager.stock',true)) return;
    #--
    $c=getOneValue("select count(*) from `".tbl_name("order_log")."` where status='cancelled' and `order`={$order['id']}");
    if ($c==1) {
      $productModel=new CCMSManagedModel("ProductManager");
      $cart=unserialize($order["cart"]);
      foreach ($cart as $product) if (($product['id']>0) && ($product['amount']>0)) {
        $current=$productModel->get($product["id"]);
        if ($current && !empty($product['product_size_info'])) {
          $productSizeModel=new CCMSManagedModel('ProductSizeManager');
          $ps=$productSizeModel->get($product['product_size_info']['id']);
          if ($ps && (strlen($ps["stock"])>0)) {
            $ps["stock"]+=$product["amount"];
            $e=array();
            $d=array("stock"=>$ps["stock"]);
            $productSizeModel->getDomainManager()->save($ps["id"],$d,$e);
            $productModel->getDomainManager()->on_restock($product["id"],$current);
          }
          continue;
        }
        if ($current && (strlen($current["stock"])>0)) {
          $current["stock"]+=$product["amount"];
          $e=array();
          $d=array("stock"=>$current["stock"]);
          $productModel->getDomainManager()->save($product["id"],$d,$e);
          $productModel->getDomainManager()->on_restock($product["id"],$current);
        }
      }
    }
  }

  public function setStatus($id,$status) {
    executeSql("update {$this->getTableName()} set `status`='{$status}' where id=".intval($id));
    executeSql("insert into `".tbl_name("order_log")."` set `order`={$id}, `status`='{$status}',`dt`=now()");
    if ($status=="cancelled") {
      $this->orderCancelled($this->get($id));
    }
    if ($status=="payed") {
      $this->orderPayed($this->get($id));
    }
  }
  
  function getInvoice($id) {
    ob_start();
    $_GET["wrap"]=true;
    $_GET["id"]=$id;
    require getConfigItem('script_base').'shared/cyane/invoice.inc.php';
    $res=ob_get_contents();
    ob_end_clean();
    return $res;
  }

  public function sendStatusNotification($id,$status) {
    $line=$this->get($id);
    $cust=unserialize($line['customer_details']);
    if (empty($cust['email'])) return;

    #--
    if (!function_exists('ccmsTranslate_static')) CCMSTranslator::instance();
    #--

    require_once getConfigItem('script_base').'shared/cyane/HtmlMimeMail5.class.php';
    $from=SettingsManager::setting("webshop.email_from","info@".getConfigItem("domain"));
    $to=SettingsManager::setting("webshop.email_to","info@".getConfigItem("domain"));
    $msg=$this->getInvoice($id);
    
    $to=$cust['email'];
    //$subject=ccmsTranslate_static("webshop.email_subject_customer",$id);
    $subject=ccmsTranslate_static('order.email_subject.'.$status);
    HtmlMimeMail5::mail($from,$to,$subject,$msg);
  }

  //@Override
  public function save($id, $data, &$err) {
    $old_line=$id>0?$this->get($id):null;
    $res=parent::save($id,$data,$err);

    if ($res && (!$old_line || ($data["status"]!=$old_line["status"]))) {
      $this->setStatus($res,$data["status"]);
      if (isset($_POST['input_send_status_notification'])) {
        $this->sendStatusNotification($res,$data["status"]);
      }
    }

    if ($res && ($data["customer"]!=$old_line["customer"])) {
      $c=getOneRow('select * from `'.tbl_name('customer').'` where id='.$data["customer"]);
      executeSql('update `'.$this->getTableName().'` set customer_name='.dbStr(empty($c)?'-':trim($c['first_name'].' '.$c['last_name'])).' where id='.$res);
    }

    return $res;
  }
  
  //@Override
  //check dependencies
  protected function checkMeta($create=false,$update=true) {
    //my dependencies
    if (!isset($_SESSION["meta.checked.{$this->tableName}"])) {
      $with_dependencies=true;
      CCMSManagedModel::getManager("CustomerManager");
      CCMSManagedModel::getManager("ProductManager");
    }
    
    parent::checkMeta($create,$update);
    
    //parent of
    if (isset($with_dependencies)) {
      CCMSManagedModel::getManager("VoucherManager");
    }
  }

  public function createTable() {
    parent::createTable();
    
    $prefix=$this->getTablePrefix();
    
    $sql=
<<<SQL

CREATE TABLE IF NOT EXISTS `{$prefix}order_log` (
`order` int(11) NOT NULL,
`status` varchar(255) NOT NULL,
`data` varchar(255) NULL,
`dt` datetime NOT NULL,
PRIMARY KEY  (`order`,`status`,`dt`)
) ENGINE=InnoDB;

ALTER TABLE `{$prefix}order_log`
ADD CONSTRAINT `{$prefix}order_log_ibfk_1` FOREIGN KEY (`order`) REFERENCES `{$prefix}order` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE TABLE IF NOT EXISTS `{$prefix}order_product_log` (
`order` int(11) NOT NULL,
`customer` int(11),
`amount` int(11) NOT NULL,
`product` int(11) NOT NULL,
`dt` datetime NOT NULL,
INDEX (customer),
PRIMARY KEY  (`order`,`product`,`dt`)
) ENGINE=InnoDB;

ALTER TABLE `{$prefix}order_product_log`
ADD CONSTRAINT `{$prefix}order_product_log_ibfk_1` FOREIGN KEY (`order`) REFERENCES `{$prefix}order` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `{$prefix}order_product_log_ibfk_2` FOREIGN KEY (`customer`) REFERENCES `{$prefix}customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `{$prefix}order_product_log_ibfk_3` FOREIGN KEY (`product`) REFERENCES `{$prefix}product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

SQL;

    executeTransSql(explode(";",$sql));
  }
  
}

// end