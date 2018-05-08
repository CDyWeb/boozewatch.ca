<?php

$fb_id='';
$fb_secret='';

switch ($_GET['facebook']) {
  case 'callback' : {
    
    if(isset($_GET['error'])) {
      ?>
      <div class='alert alert-block alert-error'>
        <h4>Facebook Log In Error</h4>
        There was an error logging you in through facebook.  To log in with facebook,
        you must allow the permissions requested.
        <ul>
          <li>
            <a href="<?= $this->url('login') ?>">Go back to the log in page</a>
          </li>
        </ul>
      </div>
      <?
    } elseif (isset($_GET['access_token'])) {
    
//require dirname(__FILE__).'/../inc/CurlUtil.php';
//$curl=new CurlUtil();
      
      $arr=array();
      $arr['access_token']=$_GET['access_token'];
      $arr['me']=json_decode(file_get_contents('https://graph.facebook.com/v2.4/me?access_token='.$_GET['access_token'].'&fields=first_name,last_name,email'),true);
      
      //var_dump($arr);
      $_SERVER['_fb']=$arr;

    } else {   
      ?>
<html><head>
  <script type="text/javascript">
/* <![CDATA[ */
var match = /#access_token=([^&]+)/.exec(window.location.href);
if (match) {
  url='<?php echo $_SERVER['_MY_URL'] ?>?auth=facebook&facebook=callback&access_token='+match[1];
  window.location.href=url;
} else {
  alert('An error occured logging you in through facebook.');
  window.location.href='<?php echo $_SERVER['_MY_URL'] ?>';
}
/* ]]> */
  </script>
</head><body>
</body></html>




<?php
    }
    return;
  }
  default : {
    //$_SERVER['_MY_URL']='http://mylcbo.cdyweb.com/index';
    $redirect_uri=rawurlencode($_SERVER['_MY_URL'].'?auth=facebook&facebook=callback');
    $scope=array('public_profile,email'); 
    header("Location: https://graph.facebook.com/oauth/authorize?client_id={$fb_id}&redirect_uri={$redirect_uri}&type=user_agent&display=page&scope=".implode(',',$scope));
    exit();
  }
}

//end