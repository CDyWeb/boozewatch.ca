<?

class VoucherManager extends CCMSDomainManager {

	function __construct() {
	
		parent::__construct('Voucher');

		$this->addFieldConfig('name=active;type='.CCMSDomainField::FIELDTYPE_BOOL.';required=1;defaultValue=1');
    $this->addFieldConfig('name=barcode;type='.CCMSDomainField::FIELDTYPE_STRING.';required=1');
		$this->addFieldConfig('name=value;type='.CCMSDomainField::FIELDTYPE_CUR.';required=0');
    $this->addFieldConfig('name=percent;type='.CCMSDomainField::FIELDTYPE_FLOAT.';required=0');
    $this->addFieldConfig('name=free_shipping;type='.CCMSDomainField::FIELDTYPE_BOOL.';required=0');
    $this->addFieldConfig('name=free_shipping_above;type='.CCMSDomainField::FIELDTYPE_CUR.';required=0');
		$this->addFieldConfig('name=date_insert;type='.CCMSDomainField::FIELDTYPE_DATE.';required=0;attributes=on_insert');
    $this->addFieldConfig('name=date_update;type='.CCMSDomainField::FIELDTYPE_DATE.';required=0;attributes=on_update');
    $this->addFieldConfig('name=date_exp;type='.CCMSDomainField::FIELDTYPE_DATE.';required=0;');
		$this->addFieldConfig('name=uid;type='.CCMSDomainField::FIELDTYPE_STRING.';length=40;required=1');
		$this->addFieldConfig('name=order;type='.CCMSDomainField::FIELDTYPE_FK.';required=0;attributes=table:'.$this->getTablePrefix().'order,caption:order_id,delete:cascade');
		
		$this->setListFields(array('barcode','value','percent')); //,'order'));
		$this->setEditFields(getConfigItem("VoucherManager.editFields",array('barcode','value','percent'))); //,'order'));

		$this->init();
	}
	
}

// end