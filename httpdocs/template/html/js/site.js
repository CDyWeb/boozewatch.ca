$(document).ready(function() {
  $('a[rel^="external"]').bind('click',function() { window.open(this.href); return false; });
  $('input, textarea').placeholder();
  $('form').h5Validate();
});