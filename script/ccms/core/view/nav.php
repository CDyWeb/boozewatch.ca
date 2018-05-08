<?

if (empty($_COOKIE['currentnav'])) $_COOKIE['currentnav']='';
function recursive_tree($that,$data,$id) {
  $result='';
  if (empty($data[$id])) return $result;
  
  foreach ($data[$id] as $node) {
    if (!$node['active']) continue;
    
    $name=$node["name"];
    if (substr($name,0,1)==":") $name=$that->domainTranslate(substr($name,1),"_title");
    if (substr($name,0,1)==".") $name=$that->domainTranslate("Tree".$name);
    
    if (!isset($node["url"])) {
      if ($node["page"]) $node["url"]="page/{$node["id"]}/".getPermalinkName($node["name"]).".html";
      else if ($node["class"]) $node["url"]="body/{$node["id"]}/".getPermalinkName($node["name"]).".html";
      else $node["url"]=null;
    }
    
    $result.='<li id="li'.$node['id'].'" '.($_COOKIE['currentnav']=='li'.$node['id']?'class="active"':'').' style="position:relative;">';
    if (!empty($data[$node['id']])) {
      $result.='<div style="position:absolute;left:-18px;top:0px;padding:10px 0"><a href="#" class="tree-toggle" rel="tree'.$node['id'].'">[+]</a></div>';
      if ($node['url'])
        $result.='<a target="frame_body" href="'.$node['url'].'">'.utf8_ent($name).'</a>';
      else 
        $result.=utf8_ent($name);

      $result.='<ul class="tree nav nav-pills nav-stacked" id="tree'.$node['id'].'" style="margin-left:20px;display:'.(empty($_COOKIE['tree'.$node['id']])?'none':'block').'">';
      $result.=recursive_tree($that,$data,$node['id']);
      $result.='</ul>';
    } else {
      $result.='<a href="'.$node["url"].'" target="frame_body" role="leaf">'.utf8_ent($name).'</a>';
    }
    $result.='</li>';
  }

  return $result;
}

$roots=$this->getModel()->getRoots();
$data_nav=$this->getModel()->getNavTree();
$result_nav=array();
foreach ($roots as $i=>$node) {
  $result_nav[$node['id']]=recursive_tree($this,$data_nav,$node['id']);
}

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest')) {
  echo json_encode($result_nav);
  exit();
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>CDyWeb CMS</title>
<? if (defined('MSIE_LT_7')) { ?><meta http-equiv="X-UA-Compatible" content="chrome=1" />
<?} ?>

  <meta charset="utf-8">

	<link rel="icon" type="image/x-icon" href="{resources_url}/img/favicon.ico">
	<link rel="stylesheet" href="{resources_url}/css/nav.css" type="text/css">

  <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
  <script type="text/javascript" src="{shared_url}/jquery/jquery.cookie.js"></script>
  
  <link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
  <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
  
	<script type="text/javascript">
    var dummy=<?= time() ?>;
    function refreshMe() {
      $.get('<?= $_SERVER['_URI'] ?>',{dummy:dummy++},tree_data,'json');
    }
    function tree_data(r) {
      $.each(r,function(i,e) {
        $('#tree'+i).html(e);
      });
      $('UL.tree A.tree-toggle').click(function () { 
        var ul=$('#'+this.rel);
        var th=$(this);
        if (th.hasClass('open')) {
          th.removeClass('open');
          $.cookie(this.rel,null);
          ul.hide();
        } else {
          th.addClass('open');
          $.cookie(this.rel,'open');
          ul.show();
        }
      });
      $('ul.tree LI').click(function(event) { 
        $('ul.tree LI').removeClass('active');
        $(this).addClass('active');
        $.cookie('currentnav',this.id);
        event.stopImmediatePropagation();
      });
    }
      
	</script>
  
  <style>
    UL.tree A { color: #33F; }
    .panel-body {
      padding: 10px 5px 10px 20px;
    }
    .nav > li > a {
      padding:10px 5px;
    }
  </style>
  
</head>

<body>
  <div id="nav" class="panel-group">
    <?= getConfigItem('nav.top','') ?>
<?


$first_root=true;
foreach ($roots as $i=>$node) {
	if (isset($node["active"]) && !$node["active"]) continue;
  
?>
      <div class="panel panel-default">
        <div class="panel-heading"><div class="panel-title">
          <a class="accordion-toggle" data-toggle="collapse" data-parent="#nav" href="#collapse<?= $node['id'] ?>"><?= utf8_ent($node["name"]) ?></a>
        </div></div>
        <div id="collapse<?= $node['id'] ?>" class="panel-collapse collapse<?= $first_root?' in':'' ?>">
          <div class="panel-body">
            <ul id="tree<?= $node['id'] ?>" class="tree nav nav-pills nav-stacked">
            </ul>
          </div>
        </div>
      </div>
<?
  $first_root=false;
}
?>
  </div>
  <script type="text/javascript">
$(window).ready(function() {
  tree_data(<?= json_encode($result_nav); ?>);
});
	</script>
</body>
</html>