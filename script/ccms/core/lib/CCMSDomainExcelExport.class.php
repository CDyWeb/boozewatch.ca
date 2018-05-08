<?

class CCMSDomainExcelExport {
  protected $manager=null;
  public function __construct(CCMSDomainManager $manager) {
    $this->manager=$manager;
  }
  public function export(array $ids=null, array $options=null) {
    throw new Exception('not implemented');
  }
}

//end