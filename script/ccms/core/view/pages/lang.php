<?

require 'LangPage.class.php';
_require('view/pages/LangPage.class.php');
if (class_exists('MyLangPage')) $p=new MyLangPage();
else $p=new LangPage();

$p->invoke();

//end