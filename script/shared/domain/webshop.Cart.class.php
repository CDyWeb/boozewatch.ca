<?

class Cart {

  protected $products;
  
  public function __construct() {
    _log("Cart::__construct");
    $this->products=array();
  }
  
  public function serialize() {
    $stored_encoding=mb_internal_encoding();
    mb_internal_encoding('UTF-8');
    #--
    $res=serialize($this->products);
    #--
    mb_internal_encoding($stored_encoding);
    return $res;
  }
  public function unserialize($str) {
    $stored_encoding=mb_internal_encoding();
    mb_internal_encoding('UTF-8');
    #--
    if (empty($str)) $this->products=array();
    else $this->products=unserialize($str);
    #--
    mb_internal_encoding($stored_encoding);
  }
  public function clear() {
    $this->products=null;
  }

  public function getProducts() {
    if (!is_array($this->products)) $this->products=array();
    return $this->products;
  }
  public function add(Product $product) {
    $this->products[] = $product;
  }

  public function size() {
    return count($this->getProducts());
  }
  
  public function isEmpty() {
    return empty($this->products);
  }
  
  public function getProduct($index) {
    $p=$this->getProducts();
    if (!isset($p[$index])) return null;
    return $p[$index];
  }
  
  public function getProductsToArray() {
    $res=array();
    foreach ($this->getProducts() as $product) {
      $res[] = $product->toArray();
    }
    return $res;
  }
  
  protected function lookup_productsize($ids) {
    if (!isset($this->lookup_productsize_arr)) {
      $this->lookup_productsize_arr=array();
      foreach (getTableArray("select s.*,ps.* from ccms_productsize ps, ccms_size s where ps.active=1 and ps.size=s.id and ps.product in (".implode(",",$ids).")") as $line) {
        $this->lookup_productsize_arr[$line['product']][$line['size']]=$line;
      }
      _log('Cart::lookup_productsize - '.print_r($this->lookup_productsize_arr,true));
    }
    _log('Cart::lookup_productsize - returns lookup_productsize_arr');
    return $this->lookup_productsize_arr;
  }
  
  public function update() {
    #--
    unset($this->lookup_productsize_arr);
    #--
    $ids=array();
    foreach ($this->getProducts() as $p) {
      if ($p->id>0) $ids[]=$p->id;
    }
    if (count($ids)>0) {
      $arr=getTableArray("select id,price,stock from ".(tbl_name("product"))." where id in (".implode(",",$ids).")","id");
      $products=$this->getProducts();
      foreach (array_keys($products) as $i) {
        $p=$products[$i];
        if ($p->id<1) continue;
        if (!isset($arr[$p->id])) {
          _log('Cart::update - product '.$p->id.' not in array '.print_r($arr,true));
          unset($this->products[$i]);
        } else {
          _log('Cart::update - product '.$p->id.' stock:'.$arr[$p->id]["stock"].', price:'.$arr[$p->id]["price"]);
          if (isset($p->product_size)) {
            $arr_size=$this->lookup_productsize($ids);
            if (!isset($arr_size[$p->id][$p->product_size])) {
              _log('Cart::update - product '.$p->id.' not in array '.print_r($arr_size,true));
              unset($this->products[$i]);
              continue;
            }
            $p->product_size_info=$arr_size[$p->id][$p->product_size];
            $p->stock=$p->product_size_info['stock']; //min(0,
            if (floatval($p->product_size_info['price'])>0) $p->price=$p->product_size_info['price'];
            Product::calc($p);
            continue;
          }
          $p->stock=$arr[$p->id]["stock"];
          $p->price=$arr[$p->id]["price"];
          Product::calc($p);
        }
      }
    }
  }
  
  public function addFromArray($line, $request=null) {
    if (class_exists('CustomProduct')) $product=new CustomProduct($line);
    else $product=new Product($line);
    $this->add($product);
    return $product;
  }
  
  public function addProductFromRequest() {
    $add=intval(@$_REQUEST["add"]);
    $amount=max(1,intval(@$_REQUEST["amount"]));
    _log("Cart::addProductFromRequest - add: ".$add." amount:".$amount);

    if ($add<1) {
      $res=false;
      if (file_exists($fn=getConfigItem('script_base').'frontend/custom/plugin_webshop_special_addProductFromRequest.inc')) { require $fn; }
      if (file_exists($fn=getConfigItem('script_app').'frontend/plugin_webshop_special_addProductFromRequest.inc')) { require $fn; }
      return $res;
    }
    
    return $this->addById($add, $amount, $_REQUEST);
  }
  
  protected function productIsEqual($productRef,$id,$request) {
    return $productRef->equalsRequest($id,$request);
  }
  
  public function getProductSizeInfo($product_id,$size_id) {
    return getOneRow('select s.*,ps.* from '.(tbl_name("productsize")).' ps, '.(tbl_name("size")).' s where ps.active=1 and ps.size=s.id and s.id='.$size_id.' and  ps.product='.$product_id.' order by s.orderby');
  }

  public function addById($id, $amount, $request=array()) {
    _log("addById add:{$id} amount:{$amount}");

    foreach ($this->products as &$productRef) {
      if ($this->productIsEqual($productRef,$id,$request)) {
        $productRef->amount+=$amount;
        return $productRef->id;
      }
    }

    $line=getOneRow("select p.*, b.name as brandname from ".(tbl_name("product"))." p left join ".(tbl_name("brand"))." b on b.id=p.brand where p.id={$id}");
    if (empty($line)) {
      _log("addById **error** product {$id} not found");
      return false;
    }
    
    _log("addProductFromRequest product:".$line["name"]);

    $line["amount"]=$amount;
    if (isset($request["o1"])) $line["o1"]=@$request["o1"];
    if (isset($request["o2"])) $line["o2"]=@$request["o2"];
    if (isset($request["o3"])) $line["o3"]=@$request["o3"];
    if (isset($request["o4"])) $line["o4"]=@$request["o4"];
    if (isset($request["o5"])) $line["o5"]=@$request["o5"];
    if (isset($request["o6"])) $line["o6"]=@$request["o6"];
    if (isset($request["o7"])) $line["o7"]=@$request["o7"];
    if (isset($request["o8"])) $line["o8"]=@$request["o8"];
    if (isset($request["o9"])) $line["o9"]=@$request["o9"];

    if (isset($request["product_size"])) {
      $line["product_size"]=intval($request["product_size"]);
      $line["product_size_info"]=$this->getProductSizeInfo($line['id'],$line['product_size']);
      $line["stock"]=$line["product_size_info"]['stock']; //min(0,);
      if (floatval($line['product_size_info']['price'])>0) $line["price"]=$line["product_size_info"]['price'];
      if (!empty($line['product_size_info']['sku'])) $line['sku']=$line['product_size_info']['sku'];
    }

    if (isset($request["product_color"])) {
      $line["product_color"]=@$request["product_color"];
    }

    unset($line["description"]);

    $line["properties"]=array();
    foreach ($request as $k=>$v) if (preg_match("#^prop_(\d+)$#",$k,$match)) {
      $prop_id = $match[1];
      $prop_line = getOneRow("select * from ".(tbl_name("product_property"))." where id={$prop_id}");
      if ($prop_line) $line["properties"][$prop_id]=array("caption"=>$prop_line["name"],"value"=>$v);
    }

    $product=$this->addFromArray($line,$request);
    return $product->id;
  }
  
  public function remove($index) {
    $newlist=array();
    foreach ($this->products as $i=>$product) {
      if ($i==$index) continue;
      $newlist[] = $product;
    }
    $this->products=$newlist;
  }
  
  public function setamount($index,$amount) {
    $product = $this->products[$index];
    if (is_object($product)) $product->amount=$amount;
  }
  
  public function getProductCount() {
    $res=0;
    foreach ($this->getProducts() as $product) $res+=$product->getPacks();
    return $res;
  }
  
  public function getSubTotal() {
    $res=0;
    foreach ($this->getProducts() as $product) {
      $p=$product->getCartPrice();
      $res+=$p;
    }
    return $res;
  }
  
  public function getSubTotalEx() {
    $res=$this->getSubTotal();
    if (Product::isTaxIncluded()) $res -= $this->getTax();
    return $res;
  }

  public function getTax($with_shipping=true, $amount=null, $processing_fee=0) {
    $res=0;
    $tax_tbl = getTableArray("select * from ".tbl_name("tax"),"id");
    foreach ($this->getProducts() as $product) {
      $tax = @$tax_tbl[@$product->tax];
      if ($tax) {
        if (Product::isTaxIncluded()) {
          $price=$product->getCartPrice();
          $res += $price - ($price/(1+($tax["percent"]/100)));
        } else {
          $res += ($tax["percent"]/100) * $product->getCartPrice();
        }
      }
    }
    
    #vzk
    if ($with_shipping) {
      $rate=$this->getShippingrate();
      if ($rate && isset($tax_tbl[$rate["tax"]])) {
        $tax = $tax_tbl[$rate["tax"]];
        if ($amount===null) $amount=$rate["rate"];
        if (Product::isTaxIncluded()) {
          $res += $amount - ($amount/(1+($tax["percent"]/100)));
        } else {
          $res += ($tax["percent"]/100) * $amount;
        }
      }
    }
    
    #admin
    if ($processing_fee!=0) {
      $admin_tax=intval(setting('webshop.admin_tax',0));
      $tax = getOneRow("select * from ".tbl_name("tax")." where id=".$admin_tax);
      if ($tax) {
        if (Product::isTaxIncluded()) {
          $res += $processing_fee - ($processing_fee/(1+($tax["percent"]/100)));
        } else {
          $res += ($tax["percent"]/100) * $processing_fee;
        }
      }
    }
    return $res;
  }
  
  public function getShippingrate() {
    $res=null;
    $arr=getTableArray("select * from ".tbl_name("shippingrate"),"id");
    foreach ($this->getProducts() as $product) if (isset($arr[$product->shippingrate])) {
      $rate = $arr[$product->shippingrate];
      if ($rate && (!$res || ($res["rate"]<$rate["rate"]))) $res=$rate;
    }
    return $res;
  }
  
  public function getTotal() {
    $res=$this->getSubTotal();
    if (!Product::isTaxIncluded()) $res+=$this->getTax();
    $rate=$this->getShippingrate();
    if ($rate) $res+=$rate["rate"];
    return $res;
  }

  public function orderDone() {
    //noop
  }
  
}

//end