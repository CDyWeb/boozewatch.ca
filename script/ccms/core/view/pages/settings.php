<?

require 'SettingsPage.class.php';
_require('view/pages/SettingsPage.class.php');
if (class_exists('MySettingsPage')) $p=new MySettingsPage();
else $p=new SettingsPage();

$p->invoke();

//end