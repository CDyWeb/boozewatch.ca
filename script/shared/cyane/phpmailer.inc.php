<?

if (!class_exists('PHPMailer')) require getConfigItem('script_base').'shared/PHPMailer/class.phpmailer.php';

if (!class_exists('Attachment')) {
  
  interface iEncoding {
      public function encode($input);
      public function getType();
  }

  class Base64Encoding implements iEncoding {
      public function encode($input) { }
      public function getType() { return 'base64'; }
  }
  class Attachment {
    public $data;
    public $name;
    public $contentType;
    public $encoding;
    public function __construct($data, $name, $contentType, iEncoding $encoding) {
      $this->data        = $data;
      $this->name        = $name;
      $this->contentType = $contentType;
      $this->encoding    = $encoding;
    }
  }
}

class MockDbMailer {
  public $headers='';
  public $to=array();
  function isHtml() {}
  function addStringAttachment() {}
  function setFrom($s) { $this->from=$s; }
  function addCc($s) { $this->addCustomHeader('Cc:'.$s); }
  function addBcc($s) { $this->addCustomHeader('Bcc:'.$s); }
  function addReplyTo($s) { $this->addCustomHeader('Reply-to:'.$s); }
  function addCustomHeader($s) { $this->headers.=$s."\n"; }
  function clearAddresses() { $this->to=array(); }
  function addAddress($s) { $this->to[]=$s; }
  function send() {
    #var_dump($this);die();
    try {
      executeSql('describe _htmlmimemail');
    } catch (Exception $ex) {
      executeSql('CREATE TABLE IF NOT EXISTS `_htmlmimemail` (`message_id` varchar(255) NOT NULL,`session_id` varchar(40) NOT NULL,`dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`ip` varchar(16) NOT NULL,`from` varchar(255) NOT NULL,`to` varchar(255) NOT NULL,`subject` varchar(255) DEFAULT NULL,`html` text,`body` longtext,`headers` text,`return_path` varchar(255) DEFAULT NULL,PRIMARY KEY (`message_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;');
    }
    $sql=sprintf(
      'insert into _htmlmimemail set `message_id`=%s ,`session_id`=%s ,`ip`=%s ,`from`=%s ,`to`=%s ,`subject`=%s ,`html`=%s ,`body`=%s ,`headers`=%s ,`return_path`=%s',
      dbStr(sha1(uniqid().time().rand(-999999,999999))),
      dbStr(session_id()),
      dbStr(function_exists('getClientIp')?getClientIp():NULL),
      dbStr(@$this->from),
      dbStr(implode(', ',$this->to)),
      dbStr(@$this->Subject),
      dbStr(@$this->Body),
      dbStr(@$this->AltBody),
      dbStr(@$this->headers),
      dbStr(@$this->Sender)
    );
    executeSql($sql);
    return true;
  }
}

//wrapper class
class CcmsPHPMailer {

	public static function mail($from,$to,$subject,$msg,$headers=null,$return_path=null) {
		if (substr(trim($msg),0,1)!="<") $msg=nl2br($msg);
		$mailer = new CcmsPHPMailer();
		$mailer->setSubject($subject);
		$mailer->setHTML($msg);
		$mailer->setFrom($from);

		if (!empty($headers)) {
      if (!is_array($headers)) {
        $arr=array();
        foreach (explode("\n",$headers) as $token) if (preg_match("#^(.*):(.*)$#",trim($token),$match)) $arr[trim($match[1])]=trim($match[2]);
        $headers=$arr;
      }
      foreach ($headers as $k=>$v) {
        if (strcasecmp($k,'Return-Path')==0) {
          $return_path=$v;
          continue;
        }
        $mailer->setHeader($k,$v);
      }
    }

		if (empty($return_path)) $return_path=$from;
    $mailer->setReturnPath($return_path);

		_log("HtmlMimeMail5::mail from {$from} to {$to} subject {$subject} msg ".substr($msg,0,50)."...");
		return $mailer->send($to);
	}

  public $error=null;
  
  function __construct() {
    $legacy=defined('HTMLMIMEMAIL5_TYPE') && (HTMLMIMEMAIL5_TYPE=='mock-db');
    if (getConfigItem('mock-db-mailer',$legacy)) {
      $this->mail=new MockDbMailer();
    } else {
      $this->mail=new PHPMailer(true);
      $this->mail->XMailer='CDyWeb CMS (http://www.cdyweb.com/cdyweb-cms)';
      $this->mail->CharSet='utf-8';
    }
  }
  function setSubject($subject) {
    $this->mail->Subject=$subject;
  }
  function setHTML($html) {
    $this->mail->Body=$html;
    $this->mail->AltBody=html_entity_decode(trim(strip_tags(preg_replace('/<(head|title|style|script)[^>]*>.*?<\/\\1>/s','',$html))),ENT_QUOTES,'UTF-8');
    $this->mail->isHTML(true);
  }
  function setBcc($bcc) {
    $this->mail->addBcc($bcc);
  }
  function setCc($cc) { 
    $this->mail->addCc($cc);
  }
  public static function strip_address($s) {
    return preg_replace('#^.*\<(.*)\>.*$#','$1',$s);
  }
  function setFrom($from) {
    $this->mail->SetFrom($from,'',false);
    $this->setReturnPath($from);
  }
  function setReturnPath($rp) {
    $this->mail->Sender=self::strip_address($rp);
  }
  function setHeader($key,$value) {
    try {
      $sw=trim(strtolower($key));
      switch ($sw) {
        case 'from' : 
          $this->setFrom($value);
          return;
        case 'reply-to' : 
          $this->mail->AddReplyTo($value);
          return;
        case 'return-path' : 
        case 'sender' :
          $this->setReturnPath($value);
          return;
      }
      $this->mail->addCustomHeader($key.':'.$value);
    } catch (Exception $ex) {
      $this->error=$ex->getMessage();
    }
  }
  function addAttachment(Attachment $attachment) {
    $this->mail->AddStringAttachment($attachment->data, $attachment->name, $attachment->encoding->getType(), $attachment->contentType);
  }
  function send($to) {
    try {
      $this->mail->clearAddresses();
      if (!is_array($to)) $to=explode("\n",str_replace(',',"\n",$to));
      $valid=false;
      foreach ($to as $one) {
        $one=trim($one);
        if (!empty($one)) {
          $this->mail->addAddress($one);
          $valid=true;
        }
      }
      if (!$valid) {
        $this->error='Not valid to: '.print_r($to,true);
        return false;
      }
#var_dump($this->mail);die();
      return $this->mail->send();
    } catch (Exception $ex) {
      if (empty($this->error)) $this->error=$ex->getMessage();
      return false;
    }
  }
  function getError() {
    return $this->error;
  }
}

//end