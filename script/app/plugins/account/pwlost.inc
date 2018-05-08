<?php

if (isset($this->isAjax)) {
  require 'pwlost.raw.inc';
} else {
?>
<style type="text/css">
  @import url('/template/html/css/account.css');
</style>
<div id="login">
  <? require 'pwlost.raw.inc'; ?>
</div>
<?
}

//end