<?

class PageView extends BodyView {

	private $page;

	public function getPage() {
		return $this->page;
	}
	public function setPage($page) {
		$this->page=$page;
	}
	
	public function outputScripts() {
		//noop
	}
	
	public function outputStyles() {
		//noop
	}
	
	public function outputPagepathDiv() {
		if ($this->page=="welcome.php") return;
		parent::outputPagepathDiv();
	}
	
	public function outputContent() {
		if ($this->page) {
			_log(get_class().":outputContent {$this->page}");
			_require("view/pages/{$this->page}");
		}
	}
	
}

//end