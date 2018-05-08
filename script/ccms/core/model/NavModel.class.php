<?

class NavModel extends CCMSManagedModel {

  public static function getInstance() {
    global $navModelClass;
    if (empty($navModelClass)) $navModelClass='NavModel';
    return new $navModelClass();
  }

  public function __construct() {
    parent::__construct('TreeManager');
  }
  
  public function getNavTree($root=null) {
    $res=array();
    foreach ($this->getDomainManager()->getAll() as $line) {
      $has_access=false;
      if (!empty($_SESSION['user']['user_type'])) switch ($_SESSION['user']['user_type']) {
        case 'super' : $has_access=true;
        case 'editor' :	if ($line['user_type']=='editor') $has_access=true;
        default :	if ($line['user_type']=='user') $has_access=true;
      }
      if (!$has_access) continue;
      if (!$line['parent_id']) $line['parent_id']='root';
      if ($line['args']=='__tech' && !UserModel::isTechAdmin()) continue;
      $res[$line['parent_id']][]=$line;
    }
    if ($root===null) return $res;
    else if (isset($res[$root])) return $res[$root];
    else return array();
  }
  
  public function getRoots() {
    $res=$this->getDomainManager()->getRoots();
    foreach ($res as $i=>$line) switch ($_SESSION['user']['user_type']) {
      case 'user' :	if ($line['user_type']=='editor') unset($res[$i]);
      case 'editor' :	if ($line['user_type']=='super') unset($res[$i]);
    }
    return $res;
  }

}



//end