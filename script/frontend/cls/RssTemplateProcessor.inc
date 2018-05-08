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

class RssTemplateProcessor extends FrontendBase implements TemplateProcessor {

	protected function appendItems() {
		ob_start();
		if (file_exists($fn=getConfigItem('script_app')."plugins/{$this->page->attributes}/plugin.inc")) require $fn;
		else if (file_exists($fn="../shared/plugins/{$this->page->attributes}/plugin.inc")) require $fn;
		$res=ob_get_contents();
		ob_end_clean();
		$this->output->append($res);
	}

	protected function appendChannel() {
		$copyright="Copyright (c) ".date("Y").", ".getConfigItem("domain");
		$language=$this->frontend->getLanguage();
		$description="<![CDATA[{$this->page->meta_description}]]>";
		#$webmaster="info@".getConfigItem("domain"); <webMaster>{$webmaster}</webMaster>
		$pubDate=date("r");
    $title='<![CDATA['.$this->page->meta_title.']]>';
		$channel=
<<<XML
<channel>
	<title>{$title}</title>
	<copyright>{$copyright}</copyright>
	<link>{$this->router->url()}</link>
	<language>{$language}</language>
	<description>{$description}</description>
	<pubDate>{$pubDate}</pubDate>
	<!--{cursor}-->
</channel>
XML;
		$this->output->append($channel);
		$this->appendItems();
	}

	public function process(Router $router, $template, Output $output) {
		$this->output=$output;
		$this->router=$router;
		$this->page=$router->getPage();
		$this->appendChannel();
	}

}

