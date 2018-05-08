<?

$_GET['edit']=$_SESSION['user']['id'];

if (file_exists('custom/view/domain/CustomUserEditor.class.php')) {
  require 'custom/view/domain/CustomUserEditor.class.php';
  class AccountView extends CustomUserEditor {
    protected function getFields() {
      return $this->getManager()->getProfileFields();
    }
  }

}
else {
  require 'core/view/domain/CoreUserEditor.class.php';
  class AccountView extends CoreUserEditor {
    protected function getFields() {
      return $this->getManager()->getProfileFields();
    }
  }
}



//end