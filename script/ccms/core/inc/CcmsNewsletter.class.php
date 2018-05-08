<?

require_once SHARED_PATH.'/cyane/CcmsNewsletter.class.php';

class ZZCcmsNewsletter extends CcmsNewsletter {

  protected $manager=null;

  public function __construct($manager) {
    $this->manager=$manager;
  }

	public function getConfirmLink($hash) {
		$url=getConfigItem('url_base');
		$page=getOneRow('select * from `'.tbl_name('page').'` where `active`=1 and `page_type`=\'plugin\' and `attributes`=\'newsletter\'');
		if ($page) {
			if ($page['uri']) $url.=$page['uri'];
			else {
				if ($page['parent_id']) $url.=getPermalinkName(getOneValue('select name from `'.tbl_name('page').'` where id='.$page['parent_id'])).'/';
				$url.=getPermalinkName($page['name']);
			}
		} else {
			$url.='__plugin/newsletter';
		}
		return $url.'/confirm/'.$hash;
	}
  
  protected function translate($l,$k) {
    /**
    if (!empty($this->manager)) return $this->manager->staticTranslate($l,$k,array(
      'naam'=>'%naam',
      'link'=>'%link',
      'domain'=>'%domain',
    ));
    return parent::translate($l,$k);
    **/
    require_once getConfigItem('script_base').'shared/cyane/CcmsObjectCache.class.php';
    $t=ezcCcmsTranslation::getInstance('static', $l, false, CcmsObjectCache::getInstance());
    return $t->getTranslation($k,array(
      'naam'=>'%naam',
      'link'=>'%link',
      'domain'=>'%domain',
    ));
  }

}

//end