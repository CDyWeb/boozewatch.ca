<?

class CustomerManager extends CCMSDomainManager {

	function __construct() {
	
		parent::__construct("Customer");

		$this->addFieldConfig("name=active;type=".CCMSDomainField::FIELDTYPE_BOOL.";defaultValue=1;required=1");
    $this->addFieldConfig("name=customer_id;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");

		$this->addFieldConfig("name=company;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");

		$this->addFieldConfig("name=title;type=".CCMSDomainField::FIELDTYPE_ENUM.";required=0;attributes=mr,mrs,ms,miss,dr,prof");
		$this->addFieldConfig("name=first_name;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");
		$this->addFieldConfig("name=last_name;type=".CCMSDomainField::FIELDTYPE_STRING.";required=1");
		$this->addFieldConfig("name=dob;type=".CCMSDomainField::FIELDTYPE_DATE.";required=0");
    $this->addFieldConfig("name=job_title;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");

		$this->addFieldConfig("name=email;type=".CCMSDomainField::FIELDTYPE_EMAIL.";required=1");
		$this->addFieldConfig("name=password;type=".CCMSDomainField::FIELDTYPE_STRING.";required=1");
		
		$this->addFieldConfig("name=newsletter;type=".CCMSDomainField::FIELDTYPE_BOOL.";defaultValue=1;required=1");
		
		$this->addFieldConfig("name=tel1;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");
		$this->addFieldConfig("name=tel2;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");
		$this->addFieldConfig("name=tel3;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");
		
		$this->addFieldConfig("name=adr1_address1;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");
		$this->addFieldConfig("name=adr1_address2;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");
		$this->addFieldConfig("name=adr1_city;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");
		$this->addFieldConfig("name=adr1_state;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");
		$this->addFieldConfig("name=adr1_zip;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");
		$this->addFieldConfig("name=adr1_country;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");
		
		$this->addFieldConfig("name=adr2_name;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");
		$this->addFieldConfig("name=adr2_address1;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");
		$this->addFieldConfig("name=adr2_address2;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");
		$this->addFieldConfig("name=adr2_city;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");
		$this->addFieldConfig("name=adr2_state;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");
		$this->addFieldConfig("name=adr2_zip;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");
		$this->addFieldConfig("name=adr2_country;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");
    
    $this->addFieldConfig('name=cart;type='.CCMSDomainField::FIELDTYPE_TEXT.';required=0');
		
		$this->addFieldConfig("name=credit;type=".CCMSDomainField::FIELDTYPE_CUR.";required=1;defaultValue=0");

		$this->setListFields(getConfigItem("CustomerManager.listFields",array('__NAME')));
		$this->setEditFields(array(
			"customer_id",
			"credit",
			"company",
			"title",
			"first_name",
			"last_name",
			"dob",
			"email",
			"password",
//			"newsletter",
			"tel1",
			"tel2",
			"tel3",
			"adr1_address1","adr1_address2","adr1_city","adr1_state","adr1_zip","adr1_country",
			"adr2_name",
			"adr2_address1","adr2_address2","adr2_city","adr2_state","adr2_zip","adr2_country",
		));

		$this->init();
	}
	
	public static function getCustomerName($line) {
		$name=array();
		if (@$line["last_name"]) $name[]=$line["last_name"];
		//if (@$line["tussenvoegsel"]) $naam[]=$line["tussenvoegsel"];
		if (@$line["first_name"]) $name[]=", ".$line["first_name"];
		return implode(" ",$name);
	}
	
	//@Override
	public function getOrderBy() {
		return "last_name, first_name";
	}

	//@Override
	public function getItemName($line) {
		return self::getCustomerName($line);
	}
	
	public function getRewards($line) {
		if (empty($this->rewards)) {
			$this->rewards=getTableArray('select customer,reward from `'.tbl_name('order').'` where `reward`>0');
		}
		$res=0;
		foreach ($this->rewards as $l) if ($l['customer']==$line['id']) $res+=floor($l['reward']);
		return $res;
	}

	public function doSearch($token) {
		$token=db_escape($token);
		$sql = 
"select * 
from {$this->getTableName()}
where 
	(customer_id like '%{$token}%') or 
	(first_name like '%{$token}%') or 
	(last_name like '%{$token}%') or 
	(email like '%{$token}%') 
order by {$this->getOrderBy()}";
		return getTableArray($sql);
	}
	
}

// end