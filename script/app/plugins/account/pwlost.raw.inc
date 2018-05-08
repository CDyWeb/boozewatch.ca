<?php
/*
------------------------------------------------------------

	CyaneCMS

$LastChangedRevision: 103 $
$LastChangedDate: 2009-05-22 22:51:00 +0200 (vr, 22 mei 2009) $
$LastChangedBy: erwin $

 Copyright (c) 2006-2009 Cyane Dynamic Web Solutions
 IT IS NOT ALLOWED TO USE OR MODIFY ANYTHING OF THIS SITE,
 WITHOUT THE PERMISION OF THE AUTHOR.    

 Info? Mail to ccms@cyane.nl
------------------------------------------------------------
*/

if (!empty($_POST['email'])) {
  $email=trim(strip_tags($_POST['email']));
  $customer=getOneRow('select * from ccms_customer where email='.dbStr($email));
  if (empty($customer)) {
    $user_not_found=$email;
  } else {
    $hash=md5($customer['id'].'|'.$customer['email'].'|'.$customer['password']);
    $link=$this->url('pwlost').'?c='.$customer['id'].'&h='.$hash;
    #--
    require getConfigItem('script_base').'shared/cyane/phpmailer.inc.php';
    $from=setting('company_email');
    if (empty($from)) $from='info@'.getConfigItem('domain');
    $subject='Password reset';
    $msg="<p>Please follow this link to reset your ".getConfigItem('domain')." password.</p>\n<p><a href=\"{$link}\">{$link}</a></p>\n";
    $result=CcmsPHPMailer::mail($from,$customer['email'],$subject,$msg);
    #--
    
    if (!$result) {
?>
    <p class="center error">
      Something went wrong. Please try again.<br />
      <a href="<?= $this->url() ?>">Back</a>
    </p>
<?
      return;
    }
?>
    <p class="center">
      We have sent you an e-mail with a password-reset link.<br />
      Check your spam folder when you haven't received the e-mail.<br />
      <a href="<?= $this->url() ?>">Back</a>
    </p>
<?
    return;
  }
}

if (!empty($_GET['c']) && !empty($_GET['h'])) {
  $customer=getOneRow('select * from ccms_customer where id='.intval($_GET['c']));
  if (!empty($customer)) {
    $hash=md5($customer['id'].'|'.$customer['email'].'|'.$customer['password']);
    if (strcasecmp($_GET['h'],$hash)!=0) {
?>
<p class="center error">
  This password-reset link is no longer valid.<br />
  <a href="<?= $this->url() ?>">Back</a>
</p>
<?
    } else {
    
      if (!empty($_POST['password']) && !empty($_POST['password2'])) {
        $p1=preg_replace('#\s#','',$_POST['password']);
        $p2=preg_replace('#\s#','',$_POST['password2']);
        $err=array();
        if (strcmp($_POST['password'],$p1)!=0) $err[]='No spaces allowed';
        if (strcmp($p1,$p2)!=0) $err[]='The two provided passwords did not match';
        if (strlen($p1)<4) $err[]='Use 4 or more characters please';

        if (empty($err)) {
          executeSql('update ccms_customer set password='.dbStr($p1).' where id='.$customer['id'].' limit 1');
          customerToSession($customer);
?>
<p class="center">
  <b>Your password has been changed.</b>
</p>
<?
          return;
        }
      }
      
?>
<script type="text/javascript">
  $(document).ready(function() {
    $('#pwlost-form').validate({
      'errorPlacement': function() {
        //noop
      }
    });
  });
</script>
<p class="center">
  <b>Forgot your password?</b><br />
  Hello <?= $customer['first_name'] ?>, please enter your new password.
</p>
<? if (!empty($err)) { ?>
<p class="center error">
  <?= implode('<br />',$err) ?>
</p>
<? } ?>
<form action="<?= $_SERVER['REQUEST_URI'] ?>" method="post" id="pwlost-form"><fieldset>
  <p class="center">
    <input type="password" class="ph required" name="password" id="reset-password" placeholder="your new password" />
  </p>
  <p class="center">
    <input type="password" class="ph required" name="password2" id="reset-password2" placeholder="confirm your new password" />
  </p>
  <p class="center">
    <button class="btn btn-success" type="submit">
      Save
    </button>
    &nbsp;&nbsp;&nbsp;
    <a href="<?= $this->url() ?>">Cancel</a>
  </p>
  
</fieldset></form>
<?
    }
    return;
  }
}

?>

<script type="text/javascript">
  $(document).ready(function() {
    $('#pwlost-form').validate({
      'errorPlacement': function() {
        //noop
      }
    });
  });
</script>

<p class="center">
  <b>Forgot your password?</b><br />
  Please enter your email address,<br />we will send you a password-reset link.
</p>

<? if (isset($user_not_found)) { ?>
<p class="center error">
  User not found: <?= $this->output->ent($email) ?>
</p>
<? } ?>

<form action="<?= $this->url('pwlost') ?>" method="post" id="pwlost-form"><fieldset>
  <p class="center">
    <input type="email" class="ph email required" name="email" id="login-email" placeholder="your email address" />
  </p>
  <p class="center">
    <button class="btn btn-success" type="submit">
      Send
    </button>
    &nbsp;&nbsp;&nbsp;
    <a href="<?= $this->url() ?>">Cancel</a>
  </p>
  
</fieldset></form>