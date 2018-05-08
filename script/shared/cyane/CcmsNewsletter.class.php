<?

abstract class CcmsNewsletter {

	abstract protected function getConfirmLink($hash);
	
	protected function translate($l,$k) {
		return getOneValue('select `value` from `'.tbl_name('static_translate').'` where `lang`=\''.$l.'\' and `key`=\''.$k.'\'');
	}
	protected function setting($k,$d) {
		if (function_exists('setting')) return setting($k,$d);
		$line=getOneRow('select * from `'.tbl_name('settings').'` where `key`=\''.$k.'\'');
		if (empty($line)) return $d;
		return $line["str"].$line["txt"];
	}
	
	public function log() {
	  return json_encode(array(
		  'dt'=>date('Y-m-d H:i:s'),
		  'ip'=>getClientIp(),
		  'host'=>getClientHost(),
		  'ua'=>$_SERVER["HTTP_USER_AGENT"],
		  'session'=>session_id(),
		  'URI'=>$_SERVER['REQUEST_URI'],
		));
	}

	protected function getEmailTemplate() {
		$email_css=getConfigItem('cke_css');
		if (!empty($email_css)) $email_css='<link href="'.$email_css.'" rel="stylesheet" type="text/css" />';
		return
<<<HTML
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
{$email_css}
</head><body>
%s
</body></html>
HTML;
	}

	protected function getActivateMsg($language) {
		$email_msg=$this->translate($language,'newsletter.email.activate');
		if (empty($email_msg) || (substr($email_msg,0,1)=='_')) switch($language) {
			case 'nl': $email_msg=nl2br($this->setting('plugin.newsletter.activate_msg_nl',
<<<HTML
Beste %naam%,


U ontvangt deze email omdat iemand zich heeft ingeschreven op %domain% onder dit email adres.

Indien u dit niet was - negeer dan dit bericht en de inschrijving wordt automatisch geannuleerd.

Echter, als u e-mailings van %domain% wilt ontvangen, zal u uw inschrijving moeten bevestigen door op onderstaande link te klikken.

%link%

Met vriendelijke groeten,
%domain%
HTML
			));
			break;
			
			default: $email_msg=nl2br($this->setting('plugin.newsletter.activate_msg',
<<<HTML
Hello %name%,

You have received this email because someone signed up at %domain% using your email address.

If this was not you - just ignore this email and the subscription will automatically be cancelled.

However, if you do want to receive e-mail newsletters from %domain%, you will need to confirm your subscription by clicking on this link.

%link%

Best Regards
%domain%
HTML
			));
		}
		return $email_msg;
	}

	public function confirm($hash) {
		$line=getOneRow("select * from ".tbl_name('newsletter_subscribe')." where hash='".db_escape($hash)."' limit 1");
		if ($line) {
			executeSql("update ".tbl_name('newsletter_subscribe')." set `log_confirmed`='".db_escape($this->log())."', `status`='confirmed' where id=".$line['id']);
			return true;
		}
		return false;
	}
	
	public function subscribe($email,$name=null,$language=null,$group=null,$residence=null) {
		if (!empty($group) && !is_numeric($group)) {
			$group_id=getOneValue('select id from '.tbl_name('newsletter_group').' where name='.dbStr($group));
			if (!$group_id) {
				$orderby=1+intval(getOneValue('select max(orderby) from '.tbl_name('newsletter_group')));
				global $insertedId;
				executeSql('insert into '.tbl_name('newsletter_group').' set name='.dbStr($group));
				$group_id=$insertedId;
			}
			$group=$group_id;
		}
		executeSql("delete from ".tbl_name('newsletter_subscribe')." where newsletter_group".($group?'='.$group:' is null ')." and email='".db_escape($email)."'");
		$hash=sha1($_SERVER['HTTP_HOST'].$email.$name.$group.$residence);
		executeSql(sprintf("replace into ".tbl_name('newsletter_subscribe')." set newsletter_group=%s, hash='%s', email='%s', name='%s', residence='%s', language='%s', `status`='pending', `log_pending`='%s'",
			$group?$group:'null',
			$hash,
			db_escape($email),
			db_escape($name),
			db_escape($residence),
			getSessionLanguage(),
			$this->log()
		));
    #--
    if (!empty($group)) {
      $auto_activate=getOneValue('select auto_activate from '.tbl_name('newsletter_group').' where id='.$group);
      if ($auto_activate) {
        $this->confirm($hash);
        return;
      }
    } else if (getConfigItem('newsletter_auto_activate',false)) {
      $this->confirm($hash);
      return;
    }
    #--
		$this->sendActivateMsg($hash,$email,$name,$language);
	}

	public function sendActivateMsg($hash,$email,$name=null,$language=null,$msg=null) {
		$email_msg=empty($msg)?$this->getActivateMsg($language):$msg;
		$email_template=$this->getEmailTemplate();
		$email_link=$this->getConfirmLink($hash);
    $unsubscribe_link=getConfigItem('url_base').'?newsletter-unsubscribe&h='.$hash;
		$str_domain=getConfigItem('domain');
		$str_website=$this->setting('company_website','www.'.$str_domain);
		$str_email=$this->setting('company_email','info@'.$str_domain);
    $from_email=$this->setting('newsletter_email','info@'.$str_domain);
    $bounce_email=$this->setting('bounce_email','bounce@cdyweb.com');
		$email_msg=sprintf(
			$email_template,
			str_replace(
				array(
					'%name%',
					'%naam%',
					'[name]',
					'[naam]',
					'%link%',
					'[link]',
					'%domain%',
					'[domain]',
					'%website%',
					'[website]',
					'%email%',
					'[email]'
				),array(
					$name,
					$name,
					$name,
					$name,
					'<a href="'.$email_link.'">'.$email_link.'</a>',
					'<a href="'.$email_link.'">'.$email_link.'</a>',
					$str_domain,
					$str_domain,
					'<a href="http://'.$str_website.'">'.$str_website.'</a>',
					'<a href="http://'.$str_website.'">'.$str_website.'</a>',
					'<a href="mailto:'.$str_email.'">'.$str_email.'</a>',
					'<a href="mailto:'.$str_email.'">'.$str_email.'</a>'
				),
				$email_msg
			)
		);

		ini_set('display_errors','off');
		$subject=$this->translate($language,'newsletter.email.subject');
		require_once('HtmlMimeMail5.class.php');
    $headers=array(
      'Sender'=>($from_email),
      'Errors-To'=>($bounce_email?$bounce_email:$from_email),
      'X-Return-Path-Hint'=>'bounce.'.$hash.'@'.$str_domain,
      'List-Unsubscribe'=>$unsubscribe_link,
      'X-List-Unsubscribe'=>$unsubscribe_link,
      'X-Unsubscribe-Web'=>$unsubscribe_link,
    );
		HtmlMimeMail5::mail($from_email,$email,$subject,$email_msg,$headers,$bounce_email);
	}
	
	public function unsubscribe($email=null,$hash=null) {
		if (!empty($email)) {
			executeSql(sprintf("delete from ".tbl_name('newsletter_subscribe')." where email='%s'",
				db_escape($email)
			));
      foreach (getTableArray('show tables') as $l) {
        $n=current($l);
        if ($n==tbl_name('customer')) {
          executeSql(sprintf("update ".tbl_name('customer')." set newsletter=0 where email='%s'",
            db_escape($email)
          ));
          break;
        }
      }
		}
		if (!empty($hash)) {
			$newsletter_subscribe=getOneValue(sprintf("select * from ".tbl_name('newsletter_subscribe')." where hash='%s'",
				db_escape($hash)
			));
			if (empty($newsletter_subscribe)) {
				return false;
			} else {
				if (!empty($newsletter_subscribe['recipient'])) executeSql("update ".tbl_name('newsletter_log')." set recipient=NULL where recipient=".intval($newsletter_subscribe['recipient']));
				executeSql(sprintf("delete from ".tbl_name('newsletter_subscribe')." where hash='%s'",
					db_escape($hash)
				));
				return true;
			}
		}
	}

}

//end