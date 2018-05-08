/**/
String.prototype.str_trim=function String_trim() {
	return this.replace(/^ +| +$/g,'');
}
/**/
function isEmpty(str){
	return str.str_trim()=='';
}
function emailCheck(str) {
	var filter  = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	return filter.test(str);
}
function popup(mylink, windowname) {
	if (!window.focus)return true;
	var href;
	if (typeof(mylink) == 'string') href=mylink; else href=mylink.href;
	window.open(href, windowname, 'width=750,height=750,scrollbars=no');
	return false;
}
function submitOnEnter(e,o) {
	keynum = 0;
	if(window.event) // IE
	{
		keynum = e.keyCode
	}
	else if (e.which) // Netscape/Firefox/Opera
	{
		keynum = e.which
	}
	//alert(keynum);
	if (keynum==13) o.form.submit();
}
/**/