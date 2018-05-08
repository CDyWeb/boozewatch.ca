<?

class MockHtmlMimeMail5 {
	public static function mail($from,$to,$subject,$msg,$headers=null,$return_path=null) {
		if (substr(trim($msg),0,1)!="<") $msg=nl2br($msg);
		$mailer = new MockHtmlMimeMail5();
		$mailer->setSubject($subject);
		$mailer->setHTML($msg);
		$mailer->setFrom($from);
		if ($headers!=null) foreach (explode("\n",$headers) as $token) if (preg_match("#^(.*):(.*)$#",trim($token),$match)) $mailer->setHeader(trim($match[1]),trim($match[2]));
		if ($return_path!=null) $mailer->setReturnPath($return_path);
		$mailer->send($to);
	}
	
	function setSubject($s) {
		$this->subject=$s;
	}
	function setHTML($s) {
		$this->html=$s;
	}
	function setFrom($s) {
		$this->from=$s;
	}
	function setReturnPath($s) {
		$this->returnPath=$s;
	}
	function send($to) {
		_log("MockHtmlMimeMail5.send from {$this->from} to {$to} subject {$this->subject} msg ".substr($this->html,0,150)."...");
	}
}

//end