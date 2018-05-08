<?

class Order {

  protected $webshop;
  
  protected $id=null;
  protected $uid=null;
  protected $order_id=null;
  protected $customer_details=null;
  protected $cart=null;
  protected $status=null;
  //protected $leverwijze;
  
  protected $am_subtotal=0;
  protected $am_processing=0;
  protected $am_tax=0;
  protected $am_transport=0;
  protected $am_discount=0;
  protected $am_total=0;
  
  protected $payment;
  protected $shippingrate;
  protected $currency;
  protected $currency_factor;
  protected $product_count;
  protected $notes=null;
  protected $voucher=null;

  protected $language=null;
  protected $PON=null;
  
  public function __construct(Webshop $webshop=null) {
    _log("Order::__construct");
    $this->webshop=$webshop;
    $this->payment=setting("webshop.payment_default",null);
    if (empty($this->payment)) {
      $e=explode(",",setting("webshop.payment_methods",getConfigItem('webshop.payment_methods','in_advance')));
      $this->payment=$e[0];
    }
    $this->status='new';
  }
  
  public function __set($k,$v) {
    $this->$k=$v;
  }
  
  public function getWebshop() {
    return $this->webshop;
  }
  public function getId() {
    return $this->id;
  }

  public function getUid() {
    if (!$this->uid) {
      $this->uid=sha1(uniqid(rand(),true).uniqid(time(),true));
      if (function_exists("shop_stat")) shop_stat("webshop.order_create",$this->uid);
    }
    return $this->uid;
  }
  
  public function make() {
    global $config;
    $this->getUid();
    $this->cart=$this->webshop->getCart()->getProductsToArray();
    $this->product_count=$this->webshop->getCart()->getProductCount();
    $this->currency=getSessionCurrency();
    $this->currency_factor=getSessionCurrencyFactor();
    $this->customer_details=$this->webshop->getCustomer()->toArray();
    $this->calc();
    $this->webshop->toSession();
  }

  public function getPayment() {
    return $this->payment;
  }
  public function setPayment($payment) {
    $this->payment=$payment;
  }
  public function getShippingrate() {
    return $this->shippingrate;
  }
  public function setShippingrate($shippingrate) {
    $this->shippingrate=$shippingrate;
  }
  public function getNotes() {
    return $this->notes;
  }
  public function setNotes($notes) {
    $this->notes=$notes;
  }
  public function getStatus() {
    return $this->status;
  }
  public function setStatus($status) {
    $this->status=$status;
  }
  public function getCart() {
    return $this->cart;
  }
  public function getOrder_id() {
    return $this->order_id;
  }
  public function getAmountSubtotal() {
    return $this->am_subtotal;
  }
  public function getAmountTax() {
    return $this->am_tax;
  }
  public function getAmountTransport() {
    return $this->am_transport;
  }
  public function getAmountProcessing() {
    return $this->am_processing;
  }
  public function getAmountDiscount() {
    return $this->am_discount;
  }
  public function setAmountDiscount($f) {
    $this->am_discount=floatval($f);
  }
  public function getAmountTotal() {
    return $this->am_total;
  }
  public function setVoucher($voucher) {
    $this->voucher=$voucher;
  }
  public function getVoucher() {
    return $this->voucher;
  }
  public function setLanguage($language) {
    $this->language=$language;
  }
  public function getLanguage() {
    return $this->language;
  }
  public function getPON() {
    return $this->PON;
  }
  public function setPON($PON) {
    $this->PON=$PON;
  }
  
  protected function fetch_am_processing() {
    switch($this->payment) {
    case "in_advace" :
      $this->am_processing=floatval(setting("webshop.payment.in_advace.rate",0));
      break;
    case "account" :
      $this->am_processing=floatval(setting("webshop.payment.account.rate",0));
      break;
      default :
      $this->am_processing=0;
    }
  }
  
  protected function isWithTax() {
    $taxRate=$this->getTaxRate();
    return (isset($taxRate['percent']) && ($taxRate['percent']>0));
  }

  protected function getTaxRate() {
    _log("Order::getTaxRate");
    switch(setting('webshop.taxBy',getConfigItem('webshop.taxBy','product'))) {

      case 'country' :
        $country=$this->webshop->getCustomer()->getShippingCountry();
        $with_tax=(bool)$country["with_tax"];
        if (!$with_tax) {
          _log("Order::getTaxRate - taxBy country, with_tax=false, no tax");
          return array('percent'=>0,'name'=>'No tax');
        }

        //@todo make this nicer
        _log("Order::getTaxRate - taxBy country, with_tax=true, return first tax record that has a positive percentage");
        return getOneRow('select * from '.tbl_name('tax').' where percent>0');

      case 'region' :
        $country=$this->webshop->getCustomer()->getShippingCountry();
        $with_tax=(bool)$country["with_tax"];
        if (!$with_tax) {
          _log("Order::getTaxRate - taxBy region, country with_tax=false, no tax");
          return array('percent'=>0,'name'=>'No tax');
        }
        $region=$this->webshop->getCustomer()->getShippingRegion();
        if (empty($region)) {
          _log("Order::getTaxRate - taxBy region, region not found, return default first tax record that has a positive percentage");
          return getOneRow('select * from '.tbl_name('tax').' where percent>0');
        }
        
        _log("Order::getTaxRate - taxBy region, with_tax=true, return region {$region['name']} tax: {$region['rate']} %");
        return array('percent'=>$region['rate'],'name'=>$region['name']);

      case 'product':
      default :
        //@todo make this nicer
        return getOneRow('select * from '.tbl_name('tax').' limit 1');
    }
  }
  
  protected function calcTax() {
    _log("Order::calcTax");
    
    $taxRate=$this->getTaxRate();
    $withTax=(isset($taxRate['percent']) && ($taxRate['percent']>0));
    if (!$withTax) {
      _log("Order::calcTax - not with Tax, return 0");
      return 0;
    }
    
    $calc_with_shipping=false;
    if (($this->am_transport>0) && isset($this->shippingrate['tax'])) {
      _log("Order::calcTax - there is shipping cost, fetch tax for that");
      $shipping_tax=getOneRow('select * from '.tbl_name('tax').' where id='.$this->shippingrate['tax']);
      if (isset($shipping_tax['percent']) && ($shipping_tax['percent']>0)) {
        _log("Order::calcTax - shipping_tax percent>0 so calc tax on shipping, calc_with_shipping=true");
        $calc_with_shipping=true;
      } else {
        _log("Order::calcTax - no shipping_tax percent, calc_with_shipping=false");
      }
    }

    $taxBy=setting('webshop.taxBy',getConfigItem('webshop.taxBy','product'));
    switch($taxBy) {
      case 'country':
      case 'region':
        _log("Order::calcTax - taxBy {$taxBy} - percentage = ".$taxRate['percent']);
        $percentage = ($taxRate['percent']/100);

        $am_subtotal=$this->getAmountSubtotal();
        $am_discount=$this->getAmountDiscount();
        $taxable = max(0,$am_subtotal-$am_discount);

        $tax = $percentage * $taxable;
        _log("Order::calcTax - basic tax=".$tax);
        if ($calc_with_shipping) $tax += $percentage * $this->am_transport;
        _log("Order::calcTax - basic tax with shpping=".$tax);
        if ($this->am_processing>0) $tax += $percentage * $this->am_processing;
        _log("Order::calcTax - basic tax with shpping and handling=".$tax);
        return $tax;

      case 'product':
      default:
        _log("Order::calcTax by product");
        $res=$this->webshop->getCart()->getTax($calc_with_shipping,$this->am_transport,$this->am_processing);
        _log("Order::calcTax result=".$res);
        return $res;
    }
  }
  
  protected function calc() {

    _log("Order::calc");
    $this->am_subtotal=$this->webshop->getCart()->getSubTotal();

    $this->fetch_am_processing();

    //$setting_verzendkosten_rembours=floatval(setting("webshop.rembours",5));
    //$setting_max_verzendkosten_rembours=floatval(setting("iprocms_verzendkosten.max_rembours",350));
    
    $setting_shipping_free=floatval(getConfigItem('webshop.shipping_free',setting('webshop.shipping_free',0)));

    $this->shippingrate = $this->webshop->getCart()->getShippingrate();
    $shippingrate_rate=is_array($this->shippingrate)?$this->shippingrate["rate"]:0;
    //$this->leverwijze=is_array($verzendkosten_line)?$verzendkosten_line["id"]:null;
    
    switch($this->payment) {

      case "visit_in_advance" : 
      case "visit" : 
        _log("visit:0");
        $this->am_transport = 0;
        $this->am_tax = $this->calcTax(); //$this->isWithTax()?$this->webshop->getCart()->getTax(false,null,$this->am_processing) : 0;
        break;
        /**
        case "rembours" :
          if (($setting_max_verzendkosten_rembours>0.1) && ($this->am_subtotal>$setting_max_verzendkosten_rembours)) {
            _log("subtotaal > setting_max_verzendkosten_rembours:0");
            $this->am_transport = 0;
            $this->am_tax = $vzkLand["with_tax"]?$this->webshop->getCart()->getBtw(false,null,$this->am_processing) : 0;
            break;
          }
          _log("rembours:".($verzendkosten + $setting_verzendkosten_rembours));
          $this->am_transport = $verzendkosten + $setting_verzendkosten_rembours;
          if (@$vzkLand["vzk"]) $this->am_transport += $vzkLand["vzk"];
          $this->am_tax = $vzkLand["with_tax"]?$this->webshop->getCart()->getBtw(true,$this->am_transport,$this->am_processing) : 0;
          break;
  **/
        default : {
          _log ("setting_shipping_free={$setting_shipping_free}  subtotal=".$this->am_subtotal);
          if (($setting_shipping_free>0.1) && ($this->am_subtotal>$setting_shipping_free)) {
            _log("subtotal>setting_shipping_free:0");
            $this->am_transport = 0;
            $this->am_tax = $this->calcTax(); //$this->isWithTax()?$this->webshop->getCart()->getTax(false,null,$this->am_processing) : 0;
            break;
          }
          
          _log("shippingrate_rate:".$shippingrate_rate);
          $this->am_transport = $shippingrate_rate;

          $country=$this->webshop->getCustomer()->getShippingCountry();
          if (!empty($country) && ($country["free_shipping_offset"]>0) && ($this->am_subtotal>$country["free_shipping_offset"])) $this->am_transport=0;
          else if (!empty($country) && ($country["rate"]>0)) $this->am_transport += $country["rate"];

          $this->am_tax = $this->calcTax(); //$this->isWithTax()?$this->webshop->getCart()->getTax(true,$this->am_transport,$this->am_processing) : 0;
        }
    }

    #--
    $am_processing=$this->getAmountProcessing();
    $am_subtotal=$this->getAmountSubtotal();
    $am_tax=$this->getAmountTax();
    $am_transport=$this->getAmountTransport();
    $am_discount=$this->getAmountDiscount();

    if (Product::isTaxIncluded()) {
      $this->am_total = $am_processing + $am_subtotal + $am_transport - $am_discount;
      error_log("this->am_total = {$this->am_total} = {$am_processing} + {$am_subtotal} + {$am_transport} - {$am_discount}");
    } else {
      $this->am_total = $am_processing + $am_subtotal + $am_tax + $am_transport - $am_discount;
      error_log("this->am_total = {$this->am_total} = {$am_processing} + {$am_subtotal} + {$am_tax} + {$am_transport} - {$am_discount}");
    }
  }
  
  function save() {
    
    $customer_id=(isset($this->customer_details["id"]) && ($this->customer_details["id"]>0))?intval($this->customer_details["id"]):"NULL";
    
    _log("Order::save");
    $set_sql = sprintf(
    "set 
date_update=now(),
uid='%s',
payment='%s',
status='%s',
am_processing=%s,
am_subtotal=%s,
am_tax=%s,
am_transport=%s,
am_discount=%s,
am_total=%s,
customer=%s,
customer_details='%s',
customer_name='%s',
cart='%s',
currency='%s',
currency_factor='%s',
voucher='%s'",
    db_escape($this->uid),
    db_escape($this->payment),
    db_escape($this->status),
    db_escape($this->am_processing),
    db_escape($this->am_subtotal),
    db_escape($this->am_tax),
    db_escape($this->am_transport),
    db_escape($this->am_discount),
    db_escape($this->am_total),
    $customer_id,
    db_escape(serialize($this->customer_details)),
    db_escape($this->customer_details["last_name"].($this->customer_details["first_name"]?", ".$this->customer_details["first_name"]:"")),
    db_escape(serialize($this->cart)),
    db_escape($this->currency),
    db_escape($this->currency_factor),
    db_escape($this->voucher)
    );
    
    if ($this->notes!==null) $set_sql.=',notes='.dbStr($this->notes); 
    if ($this->PON!==null) $set_sql.=',PON='.dbStr($this->PON);

    //die($set_sql);

    /**
    if ($this->payment=="cc") {
      $cc_data = $_SESSION["cc_data"];
      $set_sql.=",cc='".db_escape($cc_data)."'";
    }
    **/

    if (!@$this->id) {
      
      $this->order_id = setting("webshop.order_id","1");
      set_setting("webshop.order_id",$this->order_id+1);

      $set_sql.=sprintf(",date_insert=now(),order_id='%s'",db_escape($this->order_id));
      $sql="insert into `".tbl_name("order")."` {$set_sql}";

      executeSql($sql);
      global $insertedId;
      $this->id=$insertedId; 
      if (!$this->id) die("insert failed!<br /><br />$sql");

      executeSql("insert into `".tbl_name("order_log")."` set `order`=".$this->id.", `status`='{$this->status}',`dt`=now()");
      foreach ($this->cart as $i=>$line) {
        if ($line["id"]>0) executeSql("replace into `".tbl_name("order_product_log")."` set `order`={$this->id}, `product`={$line["id"]}, `amount`={$line["amount"]}, `dt`=now(), `customer`={$customer_id}");
        if (function_exists("shop_stat")) shop_stat("webshop.order.product",$line["naam"],$line["id"].";".$line["amount"]);
      }
      
      if (function_exists('shop_stat')) shop_stat('webshop.order_done',$this->uid,$this->id);
      $this->webshop->toSession();
      
    } else {
      
      $sql='update '.tbl_name('order').' '.$set_sql.' where id='.$this->id;
      executeSql($sql);

    }
    
    if (isset($this->shippingrate['id']) && ($this->shippingrate['id']>0)) {
      try {
        executeSql('update '.tbl_name('order').' set shippingrate='.intval($this->shippingrate['id']).' where id='.$this->id);
      } catch (Exception $ex) {
        _log('error while updating shippingrate: '.$ex->getMessage());
      }
    }
    
    if (!empty($this->language)) {
      try {
        executeSql('update '.tbl_name('order').' set language='.dbStr($this->language).' where id='.$this->id);
      } catch (Exception $ex) {
        _log('error while updating language: '.$ex->getMessage());
      }
    }
    
    if (function_exists('shop_stat')) shop_stat('webshop.order_saved',$this->uid,$this->id);
  }
  
  function load($id) {
    $row=getOneRow('select * from `'.tbl_name('order').'` where id='.intval($id));
    if (empty($row)) throw new Exception('order unknown');
    foreach ($row as $k=>$v) $this->$k=$v;
    $this->cart=unserialize($row['cart']);
    $this->customer_details=unserialize($row['customer_details']);
    return $this;
  }

  function getInvoice() {
    ob_start();
    $_GET["wrap"]=true;
    $_GET["id"]=$this->id;
    require getConfigItem('script_base').'shared/cyane/invoice.inc.php';
    $res=ob_get_contents();
    ob_end_clean();
    return $res;
  }
  
  function emailOrderConfirmation() {
    $cust=$this->customer_details;
    require_once getConfigItem('script_base').'shared/cyane/HtmlMimeMail5.class.php';
    $from=setting("webshop.email_from","info@".getConfigItem("domain"));
    $to=setting("webshop.email_to","info@".getConfigItem("domain"));
    $subject=translate("webshop.email_subject_merchant", $this->getOrder_id());
    //$url=getConfigItem("url_server").getInstance()->getRouter()->getPluginUri("webshop","/invoice/".intval(@$cust->id)."/{$this->getUid()}");
    $msg=$this->getInvoice(); //file_get_contents($url);
    if (!empty($to) && ($to!='-') && !empty($subject) && ($subject!='-')) HtmlMimeMail5::mail($from,$to,$subject,$msg,empty($cust['email'])?null:'Reply-to:'.$cust['email']);

    if (!empty($cust['email'])) {
      $to=$cust['email'];
      $subject=translate("webshop.email_subject_customer",$this->getOrder_id());
      HtmlMimeMail5::mail($from,$to,$subject,$msg);
    }
  }
  
  function done() {
    if (!$this->id) throw new Exception('order unknown');

    #status
    $this->status='in_process';
    if ($this->getAmountTotal()<0.01) $this->status='payed';
    executeSql('update `'.tbl_name('order').'` set `status`='.dbStr($this->status).' where id='.intval($this->id).' limit 1');
    executeSql('insert into `'.tbl_name('order_log').'` set `order`='.intval($this->id).', `status`='.dbStr($this->status).', `dt`=now()');
    
    #voucher
    if (!empty($this->voucher)) {
      executeSql('update `'.tbl_name('voucher').'` set `order`='.intval($this->id).' where `barcode`='.dbStr($this->voucher).' limit 1');
    }

    #stock
    foreach ($this->getCart() as $i=>$line) {
      if (($line['id']>0) && ($line['amount']>0)) {
        if (!empty($line['product_size'])) executeSql('update `'.tbl_name('productsize').'` set stock=stock-'.intval($line['amount']).' where size='.intval($line['product_size']).' and product='.intval($line['id']));
        else executeSql('update `'.tbl_name('product').'` set stock=stock-'.intval($line['amount']).' where id='.intval($line['id']).' limit 1');
      }
    }

    #reward
    $reward=0;
    foreach ($this->getCart() as $i=>$line) {
      if ($line['unit_price']==$line['price']) $reward += ($line['amount'] * $line['unit_price']);
    }
    executeSql('update `'.tbl_name('order').'` set `reward`='.floatval($reward).' where id='.intval($this->id).' limit 1');

    #email
    $this->emailOrderConfirmation();
  }

}

//end