<?

$debug=false;

if (empty($_SESSION['signup.data'])) {
	_log('!register_post:isValidSessionPost');
  echo "<p>your_session_has_expired, please try again</p>";
  return;
}

_log('register_post:isValidSessionPost');

$post=$_SESSION['signup.data'];
foreach ($_POST as $k=>$v) {
  if ($k=="_submit_token") continue;
  if (is_array($v)) foreach ($v as $vk=>$vv) $post[$k][$vk]=trim(strip_tags($vv));
  else $post[$k]=trim(strip_tags($v));
}

$sql=sprintf(
  "insert into {$this->tbl("customer")} set first_name=%s, last_name=%s, email=%s, password=%s, tel1=%s, tel2=%s, adr1_address1=%s, adr1_city=%s, adr1_state=%s, adr1_zip=%s, adr1_country=%s, location=%s",
  dbStr(@$post['first_name']),
  dbStr(@$post['last_name']),
  dbStr(@$post['email']),
  dbStr(@$post['password']),
  dbStr(@$post['tel1']),
  dbStr(@$post['tel2']),
  dbStr(@$post['address']),
  dbStr(@$post['city']),
  dbStr(@$post['state']),
  dbStr(@$post['zip']),
  dbStr(@$post['country']),
  is_array(@$post['location'])?dbStr(implode(',',$post['location'])):'NULL'
);

if (isset($post['fb_id'])) $sql.=', fb_id='.dbStr($post['fb_id']);
if (isset($post['fb_data'])) $sql.=', fb_data='.dbStr($post['fb_data']);

if ($debug || $profile_saved=executeSql($sql)) {

  unset($_SESSION['signup.data']);

  #--
  $id=mysql_insert_id();
  if ($id) customerToSession(getOneRow('select * from ccms_customer where id='.$id));
  #--
  
}
//end