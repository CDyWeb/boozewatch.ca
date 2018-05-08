<?

class NewsletterDispatcherValues {
  protected $newsletter=null;
  protected $batchSize=1;
  protected $recipients=array();
  protected $from=null;

  public function getFrom() {
    return $this->from;
  }
  public function setFrom($from) {
    return $this->from=$from;
  }

  public function getNewsletter() {
    return $this->newsletter;
  }
  public function setNewsletter($newsletter) {
    return $this->newsletter=$newsletter;
  }
  public function getNewsletter_id() {
    return $this->newsletter['id'];
  }

  public function getBatchSize() {
    return $this->batchSize;
  }
  public function setBatchSize($batchSize) {
    return $this->batchSize=$batchSize;
  }

  public function getRecipients() {
    return $this->recipients;
  }

  public function setRecipients($recipients) {
    return $this->recipients=$recipients;
  }
  public function emptyRecipients() {
    return empty($this->recipients);
  }
  public function toArray() {
    return get_object_vars($this);
  }
  public function fromArray($a) {
    foreach ($a as $k=>$v) $this->$k=$v;
  }
}

class NewsletterDispatcher {

  const SESS_TO = 'NewsletterDispatcher.instance';
  
  protected $newsletterDispatcherValues=null;
  
  public static $mailer=null;
  public static $todo=0;

  protected function __construct($newsletter=null,$recipients=null,$from=null) {
    if (!empty($newsletter)) {
      $this->getNewsletterDispatcherValues()->setNewsletter($newsletter);
      $this->getNewsletterDispatcherValues()->setRecipients($recipients);
      $this->getNewsletterDispatcherValues()->setFrom($from);
      $this->getNewsletterDispatcherValues()->setBatchSize(max(1,getConfigItem('NewsletterDispatcher.batchSize',10)));
    }
    _log(get_class().".__construct, batchsize={$this->getBatchSize()}, newsletter=".$newsletter['id'].", todo=".count($this->getRecipients()));
  }
  
  public function setNewsletterDispatcherValues($v) {
    $this->newsletterDispatcherValues=$v;
  }
  
  public function getNewsletterDispatcherValues() {
    if (empty($this->newsletterDispatcherValues)) $this->newsletterDispatcherValues=new NewsletterDispatcherValues();
    return $this->newsletterDispatcherValues;
  }
  
  public static function getMailer() {
    if (empty(self::$mailer)) {
      _require('HtmlMimeMail5.class.php');
      self::$mailer=new HtmlMimeMail5();
    }
    return self::$mailer;
  }
  
  public static function setMailer($mailer) {
    self::$mailer=$mailer;
  }

  public function getNewsletter() {
    return $this->getNewsletterDispatcherValues()->getNewsletter();
  }
  public function getNewsletter_id() {
    return $this->getNewsletterDispatcherValues()->getNewsletter_id();
  }

  public function getBatchSize() {
    return $this->getNewsletterDispatcherValues()->getBatchSize();
  }

  public function getRecipients() {
    return $this->getNewsletterDispatcherValues()->getRecipients();
  }

  public function setRecipients($recipients) {
    return $this->getNewsletterDispatcherValues()->setRecipients($recipients);
  }
  public function emptyRecipients() {
    return $this->getNewsletterDispatcherValues()->emptyRecipients();
  }
  public function getFrom() {
    return $this->getNewsletterDispatcherValues()->getFrom();
  }
  public function getValues() {
    return $this->getNewsletterDispatcherValues()->toArray();
  }
  public function setValues($a) {
    return $this->getNewsletterDispatcherValues()->fromArray($a);
  }


  public static function fromSession() {
    if (isset($_SESSION[self::SESS_TO])) {
      //$res=$_SESSION[self::SESS_TO];

      global $newsletterDispatcherClass;
      if (empty($newsletterDispatcherClass)) $newsletterDispatcherClass=get_class();
      $instance=new $newsletterDispatcherClass();
      $instance->setValues($_SESSION[self::SESS_TO]);

      _log(get_class().".fromSession > todo=".count($instance->getRecipients()));
      return $instance;
    }
    _log(get_class().".fromSession > null");
    return null;
  }
  
  public function toSession() {
    if ($this->emptyRecipients()) {
      unset($_SESSION[self::SESS_TO]);
      _log(get_class().".toSession > clear");
    } else {
      $_SESSION[self::SESS_TO]=$this->getValues();
      _log(get_class().".toSession > todo=".count($this->getRecipients()));
    }
  }
  
  public static function init($newsletter,$recipients,$from=null) {
    global $newsletterDispatcherClass;
    if (empty($newsletterDispatcherClass)) $newsletterDispatcherClass=get_class();
    $instance=new $newsletterDispatcherClass($newsletter,$recipients,$from);
    $instance->toSession();
  }

  public static function dispatch($from=null) {
    _log(get_class().".dispatch");
    $instance=self::fromSession();
    if (!empty($instance)) return $instance->dispatchMe();
    _log(get_class().":dispatch > job is done, no actual dispatch");
    return true; // << job is done
  }
  
  protected function write_newsletter_log($newsletter_hash,$recipient) {
    $sql=sprintf(
      "replace into `".tbl_name('newsletter_log')."` set id=%s, `newsletter`=%d, `email`=%s %s"
      ,
      dbStr($newsletter_hash),
      intval($this->getNewsletter_id()),
      dbStr($recipient['email']),
      ($recipient['id']?", `recipient`=".$recipient['id']:"")
    );
    executeSql($sql);
  }
  
  protected function dispatchMe($from=null) {
    _log(get_class().".dispatchMe > batchSize={$this->getBatchSize()}, todo=".count($this->getRecipients()));
    
    $newsletter=$this->getNewsletter();
    if (empty($newsletter)) throw new Exception('no newsletter to dispatch');
    
    $mailer=self::getMailer();
    if (empty($mailer)) throw new Exception('no mailer to dispatch with');

    $mailer->setSubject($newsletter['name']);
    
    _require('valid_email.inc.php');
    if (empty($from)) $from=$this->getFrom();
    if (empty($from)) $from=formatNiceEmail($_SESSION["user"]["email"],trim($_SESSION['user']['first_name'].' '.$_SESSION['user']['last_name']),false);
    if (empty($from)) throw new Exception('no from address');
    $mailer->setFrom($from);

    $msg=$this->getEmailMessage();
    $recipients=$this->getRecipients();
    
    if (preg_match_all('#<a[^>]+href=[\'"]([^\'"]+)[\'"]#i',$msg,$matches,PREG_SET_ORDER)) {
      #print_r($matches);
      foreach ($matches as $match) {
        if ((substr($match[1],0,1)=='/') || (stripos($match[1],'http')===0)) $msg=str_replace($match[0],str_replace($match[1],get_site_url().'mail.click/%newsletter_hash%/'.base64_encode($match[1]),$match[0]),$msg);
      }
    }
    
    $bounce_email=SettingsManager::setting('bounce_email','bounce@cdyweb.com');
    $mailer->setReturnPath($bounce_email);
    $mailer->setHeader('Sender',preg_replace('#^.*\<(.*)\>.*$#','$1',$from));
    $mailer->setHeader('Errors-To',$bounce_email);

    $freeze=serialize($mailer);

    for ($i=0;$i<$this->getBatchSize();$i++) {
      if (empty($recipients)) break;
      $recipient=array_pop($recipients);
      #try { 
        $newsletter_hash=sha1($_SERVER['HTTP_HOST']."|".$this->getNewsletter_id()."|".$recipient['email']);
        $this->write_newsletter_log($newsletter_hash,$recipient);
      #} catch (Exception $ex) {
      #  log(get_class().":dispatchMe > ".$ex->getMessage());
      #}

      $s=str_replace('%recipient.email%',preg_replace('#^.*\<(.*)\>.*#','$1',$recipient['email']),$msg);
      $s=str_replace('%newsletter_hash%',$newsletter_hash,$s);

      //$s=str_replace('"#newsletter-view"','"'.get_site_url().$newsletter_hash.'/newsletter-view.html"',$msg);
      $s=str_replace('"#newsletter-view"','"'.get_site_url().'mail.online/'.$newsletter_hash.'/newsletter-view.html"',$s);

      $unsubscribe_link=get_site_url().'mail.unsubscribe/'.$newsletter_hash.'/newsletter-unsubscribe.html';
      $s=str_replace('"#newsletter-unsubscribe"','"'.$unsubscribe_link.'"',$s);
      
      $s=str_ireplace('</body>','<img src="'.get_site_url().'mail.track/'.$newsletter_hash.'/i" alt="" width="1" height="1"/></body>',$s);

      $mailer=unserialize($freeze);

      $mailer->setHeader('X-Return-Path-Hint','bouncelist.'.$newsletter_hash.'@'.getConfigItem('domain'));
      $mailer->setHeader('List-Unsubscribe',$unsubscribe_link);
      $mailer->setHeader('X-List-Unsubscribe',$unsubscribe_link);
      $mailer->setHeader('X-Unsubscribe-Web',$unsubscribe_link);

      $mailer->setHTML($s);
      $mailer->send($recipient['email']);
    }

    $this->setRecipients($recipients);
    $this->toSession();

    self::$todo = count($this->getRecipients());
    return $this->emptyRecipients();
  }
  
  protected function getEmailMessage() {
    $newsletter=$this->getNewsletter();
    $mailer=self::getMailer();
    #--
    $a = new ezcCcmsTranslationBackend();
    $m = new ezcCcmsTranslationManager($a);
    $translator=new ezcCcmsTranslation($m, $newsletter['language'], 'static');
    #--
    ob_start();
    require PLUGIN_PATH.'/newsletter/email.inc';
    $result=ob_get_contents();
    ob_end_clean();
    return $result;
  }
  
}

//end