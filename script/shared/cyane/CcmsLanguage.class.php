<?

class CcmsLanguage {
  
  public $language;
  public $extlang;
  public $script;
  public $region;
  public $variants;
  public $extensions;
  public $privateuse;
  
  public $name=null;
  public $country=null;
  public $inEurope=false;
  public $redwood=null;

  public function __construct($language,$extlang=null,$script=null,$region=null,$variants=null,$extensions=null,$privateuse=null) {
    $this->language=$language;
    $this->extlang=$extlang;
    $this->script=$script;
    $this->country=$this->region=$region;
    $this->variants=$variants;
    $this->extensions=$extensions;
    $this->privateuse=$privateuse;
  }
  
  public function __toString() {
    return $this->code('-');
  }
  
  public function currencyStr($amount,$currency,$opt=array('TRIM'=>true)) {
    switch ($this->language) {
      case 'fr':
      case 'de':
      case 'nl':
        $res=number_format($amount,2,',','.').$currency;
        if (isset($opt['TRIM'])) $res=preg_replace('#,00#','',$res);
        break;
      default:
        $res=$currency.number_format($amount,2,'.',',');
        if (isset($opt['TRIM'])) $res=preg_replace('#\.00#','',$res);
    }
    return $res;
  }

  public function code($sep='_') {
    $arr=array($this->language);
    foreach (array('extlang','script','region','variants','extensions','privateuse') as $v) if (!empty($this->$v)) $arr[]=$this->$v;
    return implode($sep,$arr);
  }
  
  public function __equals($l) {
    if (is_array($l)) return $this->__equals(current($l));
    if (is_object($l)) return $this->__equals($l->language);
    return strcasecmp($this->language,$l)===0;
  }
  
  public function inEurope() {
    $this->inEurope=true;
    return $this;
  }
  
  public function name($name) {
    $this->name=$name;
    return $this;
  }
  
  public function country($country) {
    $this->country=$country;
    return $this;
  }
  
  public function redwood($redwood) {
    $this->redwood=$redwood;
    return $this;
  }
  
  public function getLocales() {
    $result=array();
    
    $l=$this->language;
    $r=!empty($this->region)?$this->region:$this->country;

    $ur=empty($r)?'':'_'.$r;

    if (isset($_SERVER['WINDIR'])) {
      if (!empty($this->redwood)) $result[]=$this->redwood;
      if (!empty($this->name)) {
        $result[]=$this->name;
        $result[]=$this->name.$ur;
      }
    } else {
      if ($this->inEurope) $result[]=$l.$ur.'@euro';
      $result[]=$l.$ur;
      $result[]=$l.$ur.'.UTF-8';
      $result[]=$this->name;
      $result[]=$this->name.$ur;
    }
    return $result;
  }

  public static function parse($s) {
    $n=str_replace('-','_',$s);
    if (method_exists('CcmsLanguage',($m='_l'.$n))) return self::$m();
    $e=explode('_',str_replace('-','_',$s));
    if (count($e)==2) $e=array($e[0],null,null,$e[1]);
    while(count($e)<7) $e[]=null;
    return new CcmsLanguage($e[0],$e[1],$e[2],$e[3],$e[4],$e[5],$e[6]);
  }

  public static function cLanguage($language) {
    return new CcmsLanguage(strtolower($language));
  }
  public static function cLanguageRegion($language,$region) {
    return new CcmsLanguage(strtolower($language),null,null,strtoupper($region));
  }

  public static function _lNL() {
    return self::cLanguage('nl')->name('Dutch')->country('NL')->inEurope()->redwood('Dutch_Netherlands.1252');
  }
  public static function _lDE() {
    return self::cLanguage('de')->name('German')->country('DE')->inEurope()->redwood('German_Germany.1252');
  }
  public static function _lFR() {
    return self::cLanguage('fr')->name('French')->country('FR')->inEurope()->redwood('French_France.1252');
  }
  public static function _lES() {
    return self::cLanguage('es')->name('Spanish')->country('ES')->inEurope()->redwood('Spanish_Spain.1252');
  }
  public static function _lEN() {
    return self::cLanguage('en')->name('English')->country('GB')->redwood('English_United Kingdom.1252');
  }

  public static function _lEN_US() {
    return self::cLanguageRegion('en','US')->name('English')->redwood('English_United States.1252');
  }
  public static function _lEN_CA() {
    return self::cLanguageRegion('en','CA')->name('English')->redwood('English_Canada.1252');
  }
  public static function _lEN_GB() {
    return self::cLanguageRegion('en','GB')->name('English')->redwood('English_United Kingdom.1252');
  }
  public static function _lEN_IE() {
    return self::cLanguageRegion('en','IE')->name('English')->inEurope()->redwood('English_Ireland.1252');
  }

}

//end