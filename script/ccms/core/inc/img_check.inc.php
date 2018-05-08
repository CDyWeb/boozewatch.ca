<?

//ini_set("display_errors","on");

function img_check() {
	if (isset($_SESSION["iprocms.img_checked"]) && !isset($_GET["img_check"])) return;
	debug("img check...");
	
	$images=array();
	
	$classes=array();
	$files=array_merge(_readdir_ls("./core/domain"),_readdir_ls("./custom/domain"));
	foreach ($files as $fn) if (preg_match("#/(\w+)Table.class.php$#",$fn,$match)) {
		$classes[$match[1]]=$fn;
	}
	debug(count($classes)." classes");
	
	foreach ($classes as $element=>$fn) {
		require_once $fn;
		$class="{$element}Table";
		$table = new $class();
		$table->setElementName($element);

		$table->imageCheck($images);
	}
	
	if (!defined("DO_IMG_CHECK_SIZE") || DO_IMG_CHECK_SIZE) {
		
		global $config;
		$max_width=@$config["userimg_max_width"]?$config["userimg_max_width"]:1200;
		$max_height=@$config["userimg_max_height"]?$config["userimg_max_height"]:900;
		
		$counter=0;
		foreach ($images as $loc=>$files) if ($files) {
			debug("about to check sizes in image directy {$loc}");
			foreach ($files as $fn=>$dummy) {
				$path="{$loc}/{$fn}";
				$info=getimagesize($path);
				if ($info && ($info[0]>$max_width) || ($info[1]>$max_height)) {
					$ratio=min($max_width/$info[0],$max_height/$info[1]);
					
					debug("Image [{$path}] has to be be resized from {$info[0]}x{$info[1]} to {$max_width}x{$max_height} - ratio={$ratio}");
					if ($counter++>10) {
						debug("That's a job for the next request...");
						continue;
					}
					
					$img_src = imagecreatefromstring(file_get_contents($path));
					$img_dst = imagecreatetruecolor($ratio*$info[0], $ratio*$info[1]);
					imagecopyresampled($img_dst, $img_src, 0, 0, 0, 0, $ratio*$info[0], $ratio*$info[1], $info[0], $info[1]);
					if($info["mime"]=="image/jpeg") {
						imagejpeg($img_dst,$path);
					} else {
						imagepng($img_dst,$path);
					}
					imagedestroy($img_src);
					imagedestroy($img_dst);
				}
			}
		}
	}
	
	//debug("image_check result: ".print_r($images,true));
	if (!defined("DO_IMG_CHECK_PURGE") || DO_IMG_CHECK_PURGE) {
		foreach ($images as $loc=>$files) if ($files) {
			debug("about to purge image directy {$loc}");
			foreach ($files as $fn=>$path) {
				if ($path=="found") continue;
				if (!defined("DO_IMG_CHECK_PURGE")) debug("define config DO_IMG_CHECK_PURGE to remove {$path}");
				else {
					debug("real: remove {$path}");
					if (!@unlink($path)) debug(" *** cannot remove {$path}");
				}
			}
		}
	}
	
	$_SESSION["iprocms.img_checked"]=true;
}

//end