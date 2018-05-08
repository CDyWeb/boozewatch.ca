<?

$req_translation='translation.52.php';

$version = explode('.', phpversion());
if ($version[0]>=5) {
  if ($version[1]>=3) {
    $req_translation='translation.53.php';
  }
}

require $req_translation;

//end