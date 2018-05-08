<?

class CustomCustomerList extends CoreCustomerList {

	//@Override
	function getListValue($manager,$fieldName,$line,$maxlength) {
		switch($fieldName) {
			case '__facebook' : {
        if (empty($line['fb_data'])) return 'no';
        if (!preg_match('#"link"\:"[^"]+"#',$line['fb_data'],$match)) return 'unknown';
        $json=json_decode('{'.$match[0].'}',true);
        return '<a href="'.$json['link'].'" title="'.$json['link'].'" target="_blank">'.text_limit($json['link'],40).'</a>';
      }
			default : return parent::getListValue($manager,$fieldName,$line,$maxlength);
		}
	}

}


//end