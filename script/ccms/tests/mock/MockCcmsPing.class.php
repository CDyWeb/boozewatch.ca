<?

class MockCcmsPing extends DefaultCcmsPing {
  protected function doPing($ping) {
    _log('Mock ping: '.print_r($ping,true));
    return array('ok'=>'999');
  }
}

//end