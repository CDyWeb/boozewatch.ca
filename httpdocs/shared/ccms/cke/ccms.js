function preview_click(elementId) {
  document.getElementByid('preview:'+elementId).style.display='none';
  CKEDITOR.replace(elementId, {customConfig : 'ckconfig.php'});
}
