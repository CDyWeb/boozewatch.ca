<?

if (class_exists('Product')) return;

class Product {

	public $price=0;
	protected static $lookup_cat=null;

	public function __construct(array $line) {
		foreach ($line as $k=>$v) {
			if ($k=="description") continue;
			$this->$k=$v;
		}
		$this->id = intval($this->id);
		$this->doCalc();
	}
	
	public function __get($k) {
		if (!isset($this->$k)) return "";
		return $this->$k;
	}
	
	public static function product_price(array $arr) {
		$obj=new Product($arr);
		return $obj->unit_price;
	}
  
	public static function calc($product) {
    $product->doCalc();
	}
	
  protected function doCalc() {
		$this->unit_price=$this->price=0+$this->price;
	
		if (isset($this->discount_start) && $this->discount_start && (time()<strtotime($this->discount_start))) $this->discount_absolute=$this->discount_percent=0;
		if (isset($this->discount_end) && $this->discount_end && (time()>strtotime($this->discount_end))) $this->discount_absolute=$this->discount_percent=0;

		if (isset($this->discount_absolute) && $this->discount_absolute!=0) $this->unit_price -= $this->discount_absolute;
		if (isset($this->discount_percent) && $this->discount_percent!=0) $this->unit_price *= ((100-$this->discount_percent)/100);

		for ($i=1;$i<9;$i++) $this->unit_price += $this->price_options($i);
    
    $this->unit_price=max(0,$this->unit_price);

		_log("product::calc {$this->id} price:{$this->price} unit_price:{$this->unit_price}");
  }
	
	public function toArray() {
		return get_object_vars($this);
	}
	
	public function equals(Product $compareTo) {
		if ($compareTo==null) return false;
		if ($compareTo->id!=$this->id) return false;
	}
	
	public function equalsRequest($id,$request=null) {
    if (empty($request)) $request=$_REQUEST;

		if (intval($id)!==$this->id) return false;

		if ((isset($this->o1) || !empty($request["o1"])) && (@$request["o1"]!==$this->o1)) return false;
		if ((isset($this->o2) || !empty($request["o2"])) && (@$request["o2"]!==$this->o2)) return false;
		if ((isset($this->o3) || !empty($request["o3"])) && (@$request["o3"]!==$this->o3)) return false;
    if ((isset($this->o4) || !empty($request["o4"])) && (@$request["o4"]!==$this->o4)) return false;
    if ((isset($this->o5) || !empty($request["o5"])) && (@$request["o5"]!==$this->o5)) return false;
    if ((isset($this->o6) || !empty($request["o6"])) && (@$request["o6"]!==$this->o6)) return false;
    if ((isset($this->o7) || !empty($request["o7"])) && (@$request["o7"]!==$this->o7)) return false;
    if ((isset($this->o8) || !empty($request["o8"])) && (@$request["o8"]!==$this->o8)) return false;
    if ((isset($this->o9) || !empty($request["o9"])) && (@$request["o9"]!==$this->o9)) return false;
    
		if (isset($this->product_size) && (intval(@$request["product_size"])!==intval($this->product_size))) return false;
		if (isset($this->product_color) && (@$request["product_color"]!==$this->product_color)) return false;

		if (isset($this->properties)) foreach ($this->properties as $key=>$arr) if (@$request["prop_".$key]!=$arr["value"]) return false;

		return true;
	}

	public static function isTaxIncluded() {
		return (bool)getConfigItem('Product.isTaxIncluded',setting("Product.isTaxIncluded",true));
	}
	
	public function getOptions($i) {
		$options="option".$i;
		if (!isset($this->$options)) return null;
		if (is_array($this->$options)) return $this->$options;
		if (!preg_match("#^([^:]*):(.*)$#",$this->$options,$match)) return null;

		$res=array("caption"=>$match[1],"options"=>array());
		foreach (explode(";",trim($match[2])) as $expr) if (preg_match("#^(.+)([+-][\d\.,]+)$#",$expr=trim($expr),$match)) {
			$rate=floatval(str_replace(",",".",$match[2]));
			if ($this->discount_percent!=0) $rate *= ((100-$this->discount_percent)/100);
			$res["options"][trim($match[1])]=$rate;
		} else if (strlen($expr)>0) {
			$res["options"][$expr]=floatval(0);
		}
		return ($this->$options=$res);
	}
	
	public function getOption($i) {
		$option="o".$i;
		if (!isset($this->$option)) return null;
		return $this->$option;
	}

	public function getOptionInfo($i) {
		$option="o".$i;
		if (!isset($this->$option)) return null;
		$opt=$this->getOptions($i);
		if (!$opt) return null;
		return array("caption"=>$opt["caption"],"value"=>$this->$option);
	}
	
	protected function price_options($i) {
		$options=$this->getOptions($i);
		$o=$this->getOption($i);
		if (!$options || !$o) return 0;
		if (isset($options["options"][$o])) return $options["options"][$o];
		return 0;
	}
	
	public function getName() {
		return trim($this->naam.(@$this->brandname?" (".$this->brandname.")":""));
	}
	
	public function getAmount() {
		$this->amount = max(1,@$this->amount);
		return $this->amount;
	}
	
	public function getPacks() {
		$qty = $this->quantity;
		$amount = $this->getAmount();
		if ($qty<=0) $qty=1;
		if ($qty==1) return $amount;
		return ceil($amount/$qty);
	}

	public function getPackedAmount() {
		$qty = $this->quantity;
		$packs = $this->getPacks();
		return $packs*$qty;
	}

	public function getCartPrice() {
		$price = $this->unit_price;
		return $this->getPackedAmount() * $price;
	}

	public static function getUri(array $line) {
		$uri=array(getPermalinkName($line["name"]));
		if (isset($line["brandname"]) && $line["brandname"]) array_unshift($uri,getPermalinkName($line["brandname"]));
		if (!$line["tree_id"]) array_unshift($uri,"root");
		else {
			if (!isset(self::$lookup_cat)) self::$lookup_cat=getTableArray("select * from ".tbl_name("tree")." where parent_id<>11 and class in ('".implode("','",getConfigItem("plugin.products.treeClass",array('Product','Cat')))."')","id");
			$node=self::$lookup_cat[$line["tree_id"]];
			array_unshift($uri,getPermalinkName($node["name"]));
			if (isset(self::$lookup_cat[$node["parent_id"]])) array_unshift($uri,getPermalinkName(self::$lookup_cat[$node["parent_id"]]["name"]));
		}
		array_unshift($uri,$line["id"]);
		return implode("/",$uri);
	}

}

//end