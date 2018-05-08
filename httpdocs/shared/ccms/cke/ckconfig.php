<?

chdir(dirname(__FILE__).'/../../../..');
require_once('script/globals.inc.php');
require_once('script/ccms/config.inc.php');
require_once('script/shared/cyane/site_config.inc.php');

$contentsCss=getConfigItem("cke_css");
if (defined('EDITOR_CSS')) $contentsCss=EDITOR_CSS;
if (isset($_GET['css'])) $contentsCss=$_GET['css'];

header('Expires: '.date("r",time()-60*60*24*7));
header('Content-Type: text/javascript');

global $config;

?>

//_source/plugins/toolbar/plugin.js
CKEDITOR.config.toolbar_Basic =
[
['Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink','-','About']
];
CKEDITOR.config.toolbar_Full =
[
['Source','-','Save','NewPage','Preview','-','Templates'],
['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt'],
['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField'],
'/',
['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
['Link','Unlink','Anchor'],	['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak'],
'/',
['Styles','Format','Font','FontSize'],
['TextColor','BGColor'],
['Maximize', 'ShowBlocks','-','About']
];

CKEDITOR.config.toolbar_MyBasic =
[
['Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink']
];
CKEDITOR.config.toolbar_MyFull =
[
['Source','-','NewPage','Preview'],
['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt'],
['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
'/',
['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
['Link','Unlink','Anchor'],	['Image','Flash','Table','HorizontalRule','SpecialChar','PageBreak'],
'/',
['Styles','Format','Font','FontSize'],
['TextColor','BGColor'],
['Maximize', 'ShowBlocks','-','About']
];


CKEDITOR.editorConfig = function( config )
{
  //config.baseHref = '';

  config.docType = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
  config.skin = 'kama';
  config.toolbar = 'MyFull';
  config.contentsCss = '<?= $contentsCss; ?>';
  
  config.filebrowserBrowseUrl = '<?= getConfigItem('url_base') ?>shared/ccms/ckfinder/ckfinder.html';
  config.filebrowserImageBrowseUrl = '<?= getConfigItem('url_base') ?>shared/ccms/ckfinder/ckfinder.html?Type=Images';
  config.filebrowserFlashBrowseUrl = '<?= getConfigItem('url_base') ?>shared/ccms/ckfinder/ckfinder.html?Type=Files';
  config.filebrowserUploadUrl = '<?= getConfigItem('url_base') ?>shared/ccms/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files';
  config.filebrowserImageUploadUrl = '<?= getConfigItem('url_base') ?>shared/ccms/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images';
  config.filebrowserFlashUploadUrl = '<?= getConfigItem('url_base') ?>shared/ccms/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files';

};

