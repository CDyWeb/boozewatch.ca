CKEDITOR.config.toolbar_Basic =
[
['Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink']
];

CKEDITOR.editorConfig = function( config )
{
  config.docType = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
  config.skin = 'kama';
  config.toolbar = 'Basic';
};
