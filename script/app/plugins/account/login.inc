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

function fb_user($fb_data,$user) {
  $set_sql='fb_id='.dbStr($fb_data['me']['id']).', fb_data='.dbStr(json_encode($fb_data));
  if (!empty($fb_data['gps'])) {
    $loc=$fb_data['gps'];
    $set_sql.=', location='.dbStr(implode(',',$loc));
  }
  $sql='update ccms_customer set '.$set_sql.' where id='.$user['id'];
  executeSql($sql);
  customerToSession($user);
}

if (isset($_GET['facebook'])) {
  require getConfigItem('script_app').'lib/auth/facebook.inc';
  
  if (!empty($_SERVER['_fb']['me']['id'])) {
    $exists=getOneRow('select * from ccms_customer where fb_id='.dbStr($_SERVER['_fb']['me']['id']));
    if ($exists) {
      fb_user($_SERVER['_fb'],$exists);
      $this->output->redirect($this->url());
      return;
    }

    if (!empty($_SERVER['_fb']['me']['email'])) {
      $exists=getOneRow('select * from ccms_customer where email='.dbStr($_SERVER['_fb']['me']['email']));
      if ($exists) {
        fb_user($_SERVER['_fb'],$exists);
        $this->output->redirect($this->url());
        return;
      }
    }

    //create new account
    /**
    if (!empty($_SERVER['_fb']['me'])) {
      $me=$_SERVER['_fb']['me'];
      if (empty($me['email'])) $me['email']=$me['username'].'@facebook.com';
      $sql=sprintf(
        'insert into ccms_customer set last_name=%s, first_name=%s, email=%s, password=%s',
        dbStr($me['last_name']),
        dbStr($me['first_name']),
        dbStr($me['email']),
        dbStr(md5(time().'|'.uniqid()))
      );
      executeSql($sql);
      $user=getOneRow('select * from ccms_customer where id='.mysql_insert_id());
      fb_user($_SERVER['_fb'],$user);
      $this->output->redirect($this->url());
      return;
    }
    **/
    
    $me=$_SERVER['_fb']['me'];
    $_SESSION['signup.data']=array(
      'first_name'=>$me['first_name'],
      'last_name'=>$me['last_name'],
      'email'=>$me['email'],
      'password'=>md5(time().'|'.uniqid()),
      'fb_id'=>$me['id'],
      'fb_data'=>json_encode($_SERVER['_fb']),
    );

    if (!empty($_SERVER['_fb']['gps'])) {
      $_SESSION['signup.data']['location']=array_values($_SERVER['_fb']['gps']);
    }
    
    $this->output->redirect($this->uri('signup?done'));
  }

  return;
}

?>

<script type="text/javascript">
  function pwlost() {
    $('#login-default').hide();
    $('#login-pwlost').load('<?= $this->url('pwlost') ?>').fadeIn();
  }
</script>

<div id="login">
  
  <div class="row">
    <div class="col-sm-5 text-right">
      <p id="sign-up">
        No account yet?
        &nbsp;&nbsp;&nbsp;
        <a href="<?= $this->url('signup') ?>" class="btn btn-primary">Sign up</a>
      </p>
    </div>
    
    <div class="col-sm-2">
      <p class="text-center">
        <em>-or-</em>
      </p>
      <p>&nbsp;</p>
    </div>
    
    <div class="col-sm-5 text-left">
      <p>
        <a href="<?= $this->url() ?>?facebook" id="fb-login"  class="btn btn-info">
          Sign in with Facebook
        </a>
      </p>
    </div>
  </div>
  
  <hr>
  
  <h2 class="center">Login with your email address</h2>
  
  <? if (isset($login_error)) { ?>
  <p class="center error">
    Login failed, please try again
  </p>
  <? } ?>
  
  <div id="login-default" class="col-sm-5">
    <form action="<?= $this->url() ?>" method="post" id="login-form"><fieldset>
      <p>
        <input type="email" class="ph email form-control" required="required" name="login" id="login-email" placeholder="your email address" <? if (isset($_COOKIE['login'])) echo 'value="'.$this->output->ent($_COOKIE['login']).'"' ?> />
      </p>
      <p>
        <input type="password" class="ph form-control" required="required" name="password" id="login-password" placeholder="password" <? /* if (isset($_COOKIE['password'])) echo 'value="'.$this->output->ent($_COOKIE['password']).'"' */ ?> />
      </p>
      <p class="checkbox">
        <label>
          <input class="checkbox" type="checkbox" <? if (isset($_COOKIE['remember'])) echo 'checked="checked"' ?> name="remember" id="login-remember" value="1" />
          remember me
        </label>
      </p>
      
      <p class="center">
        <button class="btn btn-success btn-large" type="submit">
          <i class="icon-ok icon-white icon-large" style="vertical-align:top"></i> Login
        </button>
      </p>
      <p class="center">
        <a href="<?= $this->url('pwlost') ?>" onclick="pwlost();return false;">Forgot your password?</a>
      </p>
      
    </fieldset></form>
  </div>
  
  <div id="login-pwlost">
    
  </div>

</div>
