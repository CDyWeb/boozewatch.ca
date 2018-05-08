<?

class SupportView extends BodyView {
	function outputScripts() {}
	function outputStyles() {}
	function outputContent() {
?>

<div id="tabs-page">
  <ul>
    <li><a href="#tab-manual">Manuals</a>
    <li><a href="#tab-address">Contact</a>
  </ul>
  <div id="tab-manual">
    <div style='margin:50px'>
      <ul>
        <li>
          <a style="color:blue;text-decoration:underline" target="_blank" href="http://www.cdyweb.com/CCMS-manual.pdf">CMS Manual (PDF)</a>
        </li>
        <li>
          <a style="color:blue;text-decoration:underline" target="_blank" href="http://docs.cksource.com/FCKeditor_2.x/Users_Guide">FCK Editor users guide</a>
        </li>
      </ul>
    </div>
  </div>
  <div id="tab-address">
    <address style='margin:50px'>
      <p>CDyWeb / Cyane Dynamic Web Solutions</p>
      <p>Tel +1 613 766 0663</p>
      <p>Email <a href='mailto:ccms@cdyweb.com'>ccms@cdyweb.com</a></p>
      <p>Web <a href='http://www.cdyweb.com'>www.cdyweb.com</a></p>
    </address>
  </div>
</div>
<script type="text/javascript">
jq(function() {
  jq("#tabs-page").tabs();
});
</script>
<?
	}
}


//end