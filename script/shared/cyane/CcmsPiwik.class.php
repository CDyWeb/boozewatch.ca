<?

require 'PiwikTracker.php';

class MyPiwikTracker extends PiwikTracker {

  public static $use_socket=true;
  
  //@Override to provide raw socket comm
  protected function sendRequest($url) {
    if (!self::$use_socket) return parent::sendRequest($url);
    #--
    $port=80;
    if (substr($url,0,8)=="https://") {
      $url = substr($url,8);
      $port=443;
    } else if (substr($url,0,7)=="http://") {
      $url = substr($url,7);
    }

    list ($host,$uri) = explode("/",$url,2);
    $uri = '/'.$uri;

    $remote = fsockopen($host, $port, $errno, $errstr, 5);
    if ($errno) throw new Exception($errstr);
    if (!$remote) throw new Exception("Connection to $host failed");

    $headers=array(
      'User-Agent: '.$this->userAgent,
      'Accept-Language: '.$this->acceptLanguage,
      'Cookie: '. $this->requestCookie,
      'Content-length: 0'
    );

    $get = 
"GET $uri HTTP/1.0\r\n".
"Host: $host\r\n".
implode("\r\n",$headers).
"\r\n\r\n";

    fputs($remote, $get);
    $response='';
    while(!feof($remote)) {
      $a = fgets($remote,4096);
      $response .= $a;
    }

    list ($header, $content) = explode("\r\n\r\n", $response, 2);
    list ($temp, $statuscode) = explode(" ", $header, 2);
    list ($statuscode) = explode("\r\n", $statuscode);

    if (substr($statuscode,0,3)!="200") throw new Exception ("Unexpected response from $host $uri : $$statuscode");
    
    //copy from parent code, as this is not an isolated method, as it should be
    preg_match_all('/^Set-Cookie: (.*?);/m', $header, $cookie);
		if (!empty($cookie[1])) {
			// in case several cookies returned, we keep only the latest one (ie. XDEBUG puts its cookie first in the list)
			if (is_array($cookie[1])) $cookie = end($cookie[1]);
			else $cookie = $cookie[1];
			if (strpos($cookie, 'XDEBUG')===false) $this->requestCookie = $cookie;
		}
		return $content;
  }
  
  //parse image data sent to this proxy, call tracker API
  //beforeDo is an optional callback function for last minute object manipulation, like setting auth
  public static function doTrackPageViewFromImageData($apiUrl=false, $data=null, $beforeDo=null) {
    if (empty($data)) $data=$_GET;
    if (isset($data['idsite'])) $idSite=$data['idsite']; else throw new Exception('idsite not in data');
    if (isset($data['action_name'])) $documentTitle=$data['action_name']; else 
      if (isset($data['download'])) $download=$data['download']; else 
      if (isset($data['link'])) $link=$data['link']; else 
        throw new Exception('action_name, download and link not in data');

    //create instance
    $object=new MyPiwikTracker($idSite,$apiUrl);

    #void setAttributionInfo (string $jsonEncoded)
    if (isset($data['_rcn']) && isset($data['_rck']) && isset($data['_refts']) && isset($data['_ref'])) {
      $object->setAttributionInfo(json_encode(array(
        $data['_rcn'],
        $data['_rck'],
        $data['_refts'],
        $data['_ref']
      )));
    }
    
    #void setBrowserHasCookies (bool $bool)
    if (isset($data['cookie'])) {
      $object->setBrowserHasCookies((bool)$data['cookie']);
    }
    
    #void setBrowserLanguage (string $acceptLanguage)
    // parent contructor takes care of this: $this->acceptLanguage = @$_SERVER['HTTP_ACCEPT_LANGUAGE'];
    
    #void setCustomVariable (int $id, string $name, string $value, [string $scope = 'visit'])
    foreach (array('_cvar'=>'visit','cvar'=>'page') as $key=>$scope) {
      if (isset($data[$key])) {
        $json=json_decode($data[$key],true);
        if (!empty($json)) foreach ($json as $id=>$arr) if (is_array($arr)) {
          list($name,$value)=$arr;
          $object->setCustomVariable($id, $name, $value, $scope);
        }
      }
    }
    
    
    #void setDebugStringAppend (string $string)
    //not implemented
    
    #void setEcommerceView ([string $sku = false], [string $name = false], [string|array $category = false], [float $price = false])
    //this is just a wrapper around Custom Variables and thus covered by cvar

    #void setForceVisitDateTime (string $dateTime)
    if (!empty($data['cdt'])) $object->setForceVisitDateTime($data['cdt']);
    
    #void setIp (string $ip)
    if (!empty($data['cip'])) $object->setIp($data['cip']);
    
    #void setLocalTime (string $time)
    if (isset($data['h']) && isset($data['m']) && isset($data['s'])) {
      $object->setLocalTime(implode(':',array($data['h'],$data['m'],$data['s'])));
    }

    #void setPlugins ([bool $flash = false], [bool $java = false], [bool $director = false], [bool $quickTime = false], [bool $realPlayer = false], [bool $pdf = false], [bool $windowsMedia = false], [bool $gears = false], [bool $silverlight = false])
    //this cries for a better encoding, why not combine this in one single json parameter?
    $object->setPlugins(
      !empty($data['fla']),
      !empty($data['java']),
      !empty($data['dir']),
      !empty($data['qt']),
      !empty($data['realp']),
      !empty($data['pdf']),
      !empty($data['wma']),
      !empty($data['gears']),
      !empty($data['ag'])
    );
    
    #void setResolution (int $width, int $height)
    if (!empty($data['res'])) {
      list($x,$y)=explode('x',$data['res']);
      $object->setResolution($x,$y);
    }

    #void setTokenAuth (string $token_auth)
    if (isset($data['token_auth'])) $object->setTokenAuth($data['token_auth']);
    
    #void setUrl (string $url)
    if (isset($data['url'])) $object->setUrl($data['url']);
    
    #void setUrlReferrer (string $url)
    if (isset($data['urlref'])) $object->setUrlReferrer($data['urlref']);
    
    #void setUserAgent (string $userAgent)
    // parent contructor takes care of this: $this->userAgent = @$_SERVER['HTTP_USER_AGENT'];
    
    #void setVisitorId (string $visitorId)
    if (isset($data['cid'])) $object->setVisitorId($visitorId);
    
    #parent provides no setter for this
    if (isset($data['_id'])) $object->visitorId=$data['_id'];
    
    #callback
    if (!empty($beforeDo)) $beforeDo($object);

    #GO!
    if (isset($documentTitle)) return $object->doTrackPageView($documentTitle); else
    if (isset($download)) return $object->doTrackAction($download,'download'); else
    if (isset($link)) return $object->doTrackAction($link,'link');
  }

}

//end