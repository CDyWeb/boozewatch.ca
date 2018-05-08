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

if (isset($_GET['done']) && !empty($_SESSION['signup.data'])) {
  $_SERVER['REQUEST_METHOD']='POST';
}

if ($_SERVER['REQUEST_METHOD']=='POST') {
  if (isset($_GET['done'])) {
    require_once 'signup.done.inc';
    if (!empty($profile_saved)) {
      $this->output->redirect($this->url());
      return;
    }
    return;
  } else if (isset($_POST['email']) && isset($_POST['last_name'])) {
    require_once 'signup.post.inc';
    if ($this->output->isRedirected()) return;
  }
}

?>

<script type="text/javascript">
  $(document).ready(function() {
    //#--
    $('.validate').validate({ 
      errorPlacement: function(error, element) {
        error.prependTo(element.closest('div'));
      },
      rules: {
        'password': {
          required: true,
          minlength: 5
        },
        'password2' : {
          required: true,
          equalTo: '#signup-password'
        }
			},
      messages: {
        'password': {
          required: "Please provide a password",
          minlength: "Please enter 5 or more characters"
        },
        'password2': {
          required: "Please confirm your password",
          equalTo: "Please enter the same password as above"
        }
      }
    });
    //#--

<? if (!empty($user_data)) {
?>
    var user_data=<?= json_encode($user_data) ?>;
    $.each(user_data,function (i,e) {
      $('#signup-'+i).val(e);
    });
<? } ?>

  });
  
</script>

<h1 class="underline">Sign Up</h1>

<div id="login" style="border:1px solid #ccc; box-shadow: 3px 3px 5px 6px #ccc; padding:20px;">

    <div style="float:right; text-align:center;width:100px; border:1px solid #888; border-radius:5px; padding:10px; margin-left:10px;">
      Already registered?<br />
      <a href="<?= $this->url() ?>">Sign in</a><br />
      <br />
      <a href="<?= $this->url() ?>?facebook" id="fb-small">
        Connect with facebook
      </a>
    </div>

  <p>
    Please fill out this form to create your account.
  </p>

  <div style="clear:both"><br /></div>
  
  <div id="signup-traditional" style="float:left; min-width:400px;">

    <h2>Signup with your e-mail address</h2>

<? if (!empty($err)) { ?>
    <p class="center error">
      <?= implode('<br />',$err) ?>
    </p>
<? } ?>
      
    <form action="<?= $this->url('signup') ?>" method="post" id="signup-form" autocomplete="off" class="form-horizontal validate"><fieldset>
      <div class="control-group">
        <label class="control-label" >First name: </label>
        <div class="controls"><input value="" type="text" class="ph required" name="first_name" id="signup-first_name" placeholder="your first name" title="your first name" /></div>
      </div>
      <div class="control-group">
        <label class="control-label" >Last name: </label>
        <div class="controls"><input value="" type="text" class="ph required" name="last_name" id="signup-last_name" placeholder="your last name" title="your last name" /></div>
      </div>
      <div class="control-group">
        <label class="control-label" >E-mail: </label>
        <div class="controls"><input value="" type="email" class="ph email required" name="email" id="signup-email" placeholder="your email address" title="your email address" /></div>
      </div>
      <div class="control-group">
        <label class="control-label" >Password: </label>
        <div class="controls"><input value="" type="password" class="ph required " name="password" id="signup-password" placeholder="password" title="password" /></div>
      </div>
      <div class="control-group">
        <div class="controls"><input value="" type="password" class="ph" name="password2" id="signup-password2" placeholder="confirm password" title="confirm password" /></div>
      </div>

      <div class="control-group"><div class="controls">
        <button class="btn btn-success btn-large" type="submit">
          <i class="icon-ok icon-white" style='vertical-align:top;'></i> Sign up
        </button>
      </div></div>

    </fieldset></form>

  </div>
  <div style="clear:both"></div>
</div>

