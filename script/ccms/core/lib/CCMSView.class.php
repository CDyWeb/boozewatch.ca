<?

abstract class CCMSView {

	protected $model;

	public function __construct(CCMSModel $model=null) {
		$this->model=$model;
	}
	
	public function &getModel() {
		return $this->model;
	}
	public function setModel(CCMSModel $model) {
		$this->model = $model;
	}
	
	abstract function render();

}

//end