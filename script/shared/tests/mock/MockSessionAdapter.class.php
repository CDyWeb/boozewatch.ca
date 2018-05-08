<?

class MockSessionAdapter extends CcmsSession_Adapter {

  public function validateIp($check) {
    return parent::validateIp($check);
  }

  public function initialize() {
  }
  public function read($id) {
  }
  public function write($id, $data) {
  }
  public function destroy($id) {
  }

}

//end