<?php
/*
------------------------------------------------------------

	CyaneCMS

$LastChangedRevision: 103 $
$LastChangedDate: 2009-05-22 22:51:00 +0200 (vr, 22 mei 2009) $
$LastChangedBy: erwin $

 Copyright (c) 2006-2009 Cyane Dynamic Web Solutions
 IT IS NOT ALLOWED TO USE OR MODIFY ANYTHING OF THIS SITE,
 WITHOUT THE PERMISION OF THE AUTHOR.    

 Info? Mail to ccms@cyane.nl
------------------------------------------------------------
*/

function get_header() {
	require getWP()->theme_dir."/header.php";
}

function wp_head() { 
	$page=getWP()->getPage();
	echo <<<HEAD
<meta name="keywords" content="{$page->meta_keywords}" />
<meta name="description" content="{$page->meta_description}" />
HEAD;
}

function get_sidebar() {
	require getWP()->theme_dir."/sidebar.php";
}

function get_footer() {
	require getWP()->theme_dir."/footer.php";
}

function bloginfo($k) {
	echo getWP()->bloginfo($k);
}

function is_single() { return false; }
function wp_get_archives() { }
function next_posts_link() { }
function previous_posts_link() { }
function wp_title() { echo " - ".getWP()->getPage()->meta_title; }
function wp_list_pages($args) { echo getWP()->list_pages($args); }
function have_posts() { return !defined("THE_POSTS_VISITED"); }
function the_post() { define("THE_POSTS_VISITED",true); }
function the_permalink() { echo getWP()->uri(); }
function the_title() { echo getWP()->getPage()->name; }
function the_content() { echo getWP()->getMyContent(); }

function dynamic_sidebar() { 
	echo '<div class="sideblock"><h3>Sidebar</h3><ul><li>&nbsp;</li></ul></div>';
	return true;
}


class WPThemeProcessor extends DefaultTemplateProcessor {

	protected function mainContentWrap($txt) {
		return "<div style='min-height:200px'>{$txt}</div>";
	}

	public function getMyContent() {
		return $this->getMainContent();
	}
	
	public function bloginfo($k) {
		switch ($k) {
			case "html_type" : return $this->output->getContentType();
			case "charset" : return $this->output->getCharset();
			case "name" : return ucfirst(getConfigItem("domain"));
			case "version" : return "";
			case "url" : return $this->frontend->getSiteURL();
			case "stylesheet_directory" : return preg_replace("#/$#","",getConfigItem("html_base_href"));
			case "stylesheet_url" : return getConfigItem("html_base_href")."style.css";
			case "rss2_url" :
			case "rss_url" :
			case "atom_url" :
			case "pingback_url" :
				return "about:blank";
				
			case "description" : return $this->getHomePage()->meta_description;
			
		}
		throw new Exception($k);
	}
	
	public function list_pages($args) {
		$arg_depth=1;
		foreach (explode("&",$args) as $expr) if (preg_match("#^([^=]+)=(.*)$#",$expr,$match)) {
			$var="arg_".$match[1];
			$$var=$match[2];
		}
		if ($arg_depth==1) {
			$home=setting("home_page");
			foreach (getTableArray("select * from ".tbl_name("page")." where parent_id is null and active=1 and tree_id=(select id from ".tbl_name("tree")." where name='.MenuTop') order by orderby") as $line) {
				if ($line["id"]==$home) continue;
				echo "<li class='page_item'><a href=''>".$this->output->ent($line["name"])."</a></li>";
			}
		}
	}

	protected function makeUpHtml() {
		$this->theme_dir=getConfigItem("html_template_dir");
		ob_start();
		require "{$this->theme_dir}/page.php";
		$this->html=ob_get_contents();
		ob_end_clean();
		
		$this->html=preg_replace('#<meta name="generator" content=".*" />#Usi','<meta name="generator" content="CCMS - WPThemeProcessor v.'.date("Y-m-d",filemtime(__FILE__)).'" />',$this->html);
		
	}

	
	public function process(Router $router, $template, Output $output) {
		_log(get_class().":process ".get_class($router)." ".mb_strlen($template)." ".get_class($output));

		global $wp;
		$wp=$this;

		$this->router=$router;
		$this->page=$router->getPage();
		if (!$this->page) {
			_log("no page to process");
			return;
		}
		$this->output=$output;
		$this->html="";

		#--
		$this->makeUpHtml();
		if ($output->isRedirected()) return;
		#--

		$output->append($this->html);
	}

}

function getWP() {
	global $wp;
	return $wp;
}
