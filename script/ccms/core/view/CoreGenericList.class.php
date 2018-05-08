<?

class CoreGenericList extends BodyView {

  public $colsizable=true;
  public $sortable=true;
  public static $endlistHtml=array();

	protected function getManager() {
		return $this->getModel()->getDomainManager();
	}
	
	public function startList() {
    echo <<<HTML
<!-- startList -->
HTML;
	}
  
  public function outputFancybox() {
?>

<script type="text/javascript" src="<?= getConfigItem('url_base') ?>/shared/jquery/fancybox/jquery.fancybox-1.3.1.js"></script>
<style type="text/css"> @import url('<?= getConfigItem('url_base') ?>/shared/jquery/fancybox/jquery.fancybox-1.3.1.css'); </style>
<script type="text/javascript" charset="utf-8">
$(document).ready(function(){
  $("a.fancybox").fancybox({
    showNavArrows:false,
    centerOnScroll:true,
    transitionIn:'elastic',
    transitionOut:'elastic'
  });
});
</script>

<?
  }

	public function outputDescription() {
    #--
    if (!empty($_SESSION['crud.delete.err']) && is_array($_SESSION['crud.delete.err']) && (count($_SESSION['crud.delete.err'])>0)) {
?>
<style type="text/css">
UL.error { margin:10px;padding:10px;border:1px solid red; background-color:#FDD; display:block; width:800px; list-style:none; }
</style>
<ul class="error">
<? foreach ($_SESSION['crud.delete.err'] as $id=>$err) { ?>
  <li><?= $this->_('Crud.Cannot delete',array('id'=>$id)) ?></li>
<? } ?>
</ul>
<?
    }
    unset($_SESSION['crud.delete.err']);
    #--
		$d=get_class($this->getModel())." > ".get_class($this);
		echo <<<HTML
<!-- {$d} -->

HTML;
	}
	
	protected function getRowButtons(CCMSDomainManagerInterface $manager) {
		$res=array();
		if ($manager->isEditable()) $res[]="edit";
		if ($manager->isDeletable()) $res[]="delete";
		#if ($manager->isMovable()) $res[]="up";
		#if ($manager->isMovable()) $res[]="down";
    if ($manager->isMovable()) $res[]="drag";
		return $res;
	}
	
	protected function uri($line=null) {
		return $_SERVER["_URI"];
	}
	
	protected function getRowButton($btnName,$line,$lineCount,$dataCount) {
		switch ($btnName) {
			case "edit" :
				if (isset($line['__readonly'])) return "<td width='20'>{$this->dot(20)}</td>";
				$this->lineUrl=$this->uri($line);
        return 
<<<HTML
<td>
	<a href="{$this->lineUrl}?edit={$line["id"]}" style="color:#666;font-size:16px;padding:4px;" title="{$this->_('Edit')}">
    <span class="glyphicon glyphicon-pencil"></span>
	</a>
</td>
HTML;

			case "delete" :
				if (isset($line['__readonly'])) return "<td width='20'>{$this->dot(20)}</td>";
        $this->lineUrl=$this->uri($line);
				return 
<<<HTML
<td>
	<a href="javascript:;" style="color:#F66;font-size:10px;margin-left:5px" title="{$this->_('Delete')}" onclick="if (window.confirm('{$this->_("Delete.confirm",getSafeName($this->getModel()->getItemName($line),' '))}')) window.location.href='{$this->lineUrl}?delete={$line["id"]}'">
    <span class="glyphicon glyphicon-remove-sign"></span>
	</a>
</td>
HTML;

			case "drag" :
				if (isset($line["can_move"]) && !$line["can_move"]) return "<td width='20'>{$this->dot(20)}</td>";
				return 
"<td width='20' class='dragHandle'>{$this->dot(20)}</td>";

			case "up" :
				if (isset($line["can_move"]) && !$line["can_move"]) return "<td width='20'>{$this->dot(20)}</td>";
				if ($lineCount==0) return "<td width='20'>{$this->dot(20)}</td>";
				if (isset($this->data) && (isset($this->data[$lineCount-1])) && isset($this->data[$lineCount-1]['can_move']) && !$this->data[$lineCount-1]['can_move']) return "<td width='20'>{$this->dot(20)}</td>";
				return 
"<td width='20'>
	<a href='{$this->uri($line)}?up={$line["id"]}'>
		<img height='16' src='{$this->resources_url('/img/icon/up.png')}' alt='' title='{$this->_("Up")}' border='0' />
	</a>
</td>";
			case "down" :
				if (isset($line["can_move"]) && !$line["can_move"]) return "<td width='20'>{$this->dot(20)}</td>";
				if ($lineCount==$dataCount-1) return "<td width='20'>{$this->dot(20)}</td>";
				return 
"<td width='20'>
	<a href='{$this->uri($line)}?down={$line["id"]}'>
		<img height='16' src='{$this->resources_url('/img/icon/down.png')}' alt='' title='{$this->_("Down")}' border='0' />
	</a>
</td>";
		}
	}
	
	protected function tableRow($id,$td,$class='trData') {
		return "<tr ".($id===0?'':"id='tr-{$id}'")." class='{$class}'>\n\t\t".implode("\n\t\t",$td)."\n\t</tr>";
	}

	protected function tableRowAdd(&$tr,$manager) {
		if ($manager->isAddable()) {
			$tr[]="<tr class='trAdd'><td colspan='{$this->colCount}' align='left'><a href='{$this->uri(null)}?edit=0'>[+] {$this->_("Add")}</a></td></tr>";
		}
	}
  
  protected function formatCurrency($n) {
    $cur=getConfigItem("currency");
    return $cur["html"][$cur["base"]]." ".number_format(floatval($n),2);
  }
  
  public function getColumnWidth($manager,$fieldName) {
		#--
    if ($fieldName=='_cb') return 20;
    #--
    $result=100;
    $field=$manager->getField($fieldName);
		if ($field) switch ($field->type) {
      case CCMSDomainField::FIELDTYPE_BOOL:
        return 50; //data width does not matter
      case CCMSDomainField::FIELDTYPE_IMG:
        return 75; //data width does not matter
      case CCMSDomainField::FIELDTYPE_FLOAT: 
      case CCMSDomainField::FIELDTYPE_DECIMAL: 
      case CCMSDomainField::FIELDTYPE_INT: 
      case CCMSDomainField::FIELDTYPE_PERCENT:
        $result=75; break;
      case CCMSDomainField::FIELDTYPE_CUR:
        $result=100; break;
      case CCMSDomainField::FIELDTYPE_DATE:
        $result=100; break;
      case CCMSDomainField::FIELDTYPE_DATETIME:
        $result=150; break;
      default:
        $result=200; break; //string
		}
    if (isset($this->dataWidth[$fieldName])) {
      $w=8 * $this->dataWidth[$fieldName];
      if ($w<$result) return max(50,$w);
      if ($w>$result) return min(200,$w);
    }
    return $result;
  }
	
	public function getListValue($manager,$fieldName,$line,$maxlength) {
		#--
    if ($fieldName=='_cb') return '<input class="checkbox" type="checkbox" name="_cb[]" value="'.$line['id'].'" />';
    #--
    $field=$manager->getField($fieldName);
		$res=$line[$fieldName];
		if ($field && $field->type==CCMSDomainField::FIELDTYPE_IMG) {
			if (!$res) return "";
			return "<a href='{$this->base_url()}/../shared/cyane/thumb.php?maxwidth=0&maxheight=0&path=".base64_encode($manager->getImgDir($fieldName).$res)."' target='_blank'><img src='{$this->base_url()}/../shared/cyane/thumb.php?maxwidth=64&maxheight=50&path=".base64_encode($manager->getImgDir($fieldName).$res)."' alt='' border='0' /></a>";
		}
		if ($field && $field->type==CCMSDomainField::FIELDTYPE_FK) {
			if (!$res) return "";
			$attributes = $field->getAttributes();
			foreach (explode(",",$attributes) as $expr) if (preg_match("#^([^:]+):(.*)$#",$expr,$match)) {
				$match[1]="lookup_".$match[1];
				$$match[1]=$match[2];
			}
			if (!isset($manager->fk_lookup[$lookup_table.":".$lookup_caption])) {
				$manager->fk_lookup[$lookup_table.":".$lookup_caption]=getListTableArray("select id,{$lookup_caption} from {$lookup_table}");
			}
			$res=$manager->fk_lookup[$lookup_table.":".$lookup_caption][$res];
		}
		if ($field && $field->type==CCMSDomainField::FIELDTYPE_CUR) {
			if (empty($res)) return '';
      return $this->formatCurrency($res);
		}
    if ($field && $field->type==CCMSDomainField::FIELDTYPE_DATETIME) {
      if (empty($res)) return '';
      return date('Y-m-d H:i',strtotime($res));
    }
		if ($field && $field->type==CCMSDomainField::FIELDTYPE_PERCENT) {
			$cur=getConfigItem("currency");
			if (empty($res)) return '';
      return number_format($res,1)." %";
		}
		if ($field && $field->type==CCMSDomainField::FIELDTYPE_ENUM) {
			$transl=$this->domainTranslate($manager->getClassName().".".$fieldName.".".$res,null,null,$res);
			return $transl?$transl:$res;
		}
		if ($field && $field->type==CCMSDomainField::FIELDTYPE_BOOL) {
			return $res?$this->cmsTranslate("Yes"):$this->cmsTranslate("No");
		}
		if ($field && $field->type==CCMSDomainField::FIELDTYPE_TEXT) {
			$res=trim(strip_tags($res));
		}
		if ($field && $field->isFloating()) {
			if ($res) return number_format(floatval($res),2);
		}
		if ($field && $field->name=='id') {
			return $res;
		}
		if ($field && $field->isNumeric()) {
			if ($res) return number_format($res,0);
		}
    if ($field && $field->type==CCMSDomainField::FIELDTYPE_LINK) {
      if (empty($res)) return '';
      $caption=$res;
      if (!empty($maxlength)) $caption=text_limit($res,$maxlength);
      return '<a href="'.$res.'" target="_blank">'.$caption.'</a>';
    }
		if (!empty($maxlength)) return text_limit($res,$maxlength);
    return $res;
	}
  
  protected function getTableBodyRowCellTd($manager,$fieldName,$line,$cell) {
    return "<td>".$cell."</td>";
  }
  
  protected function getTableBodyRowCell($manager,$fieldName,$line) {
    $cell=$this->getListValue($manager,$fieldName,$line,50);
    $cell_len=strlen($cell);
    if (!isset($this->dataWidth[$fieldName]) || ($cell_len>$this->dataWidth[$fieldName])) $this->dataWidth[$fieldName]=$cell_len;
    return $this->getTableBodyRowCellTd($manager,$fieldName,$line,$cell);
  }

  protected function getTableBodyRow($manager,$line,$rowButtons,$lineCount,$dataCount,$fields,&$dataRows,&$ids) {
    $td=array();
    foreach($rowButtons as $btnName) {
      $td[]=$this->getRowButton($btnName,$line,$lineCount,$dataCount);
    }
    if (count($rowButtons)>0) $td[]="<td class='space'>{$this->dot(10)}</td>";
    foreach($fields as $fieldName) {
      $td[]=$this->getTableBodyRowCell($manager,$fieldName,$line);
    }
    if (isset($line["id"]) && (intval($line["id"])!==0)) {
      $ids[]=$line["id"];
      $dataRows[$line["id"]]=$td;
    }
    else $dataRows[]=$td;
  }
	
	protected function getTableBody(&$tr,$manager,$data,$rowButtons,$fields,$plain) {
		$dataRows=array();
		
		$lineCount=0;
		$dataCount=count($data);
		$ids=array();
    
    $this->dataWidth=array();

		if ($dataCount==0) {
			$dataRows[]=$this->tableRow(0,array("<td colspan='".($this->colCount)."'> - {$this->_("No data")} - </td>"),'trNoData');
		} else foreach ($data as $line) {
      $this->getTableBodyRow($manager,$line,$rowButtons,$lineCount,$dataCount,$fields,$dataRows,$ids);
      $lineCount++;
		}
		_log(get_class().".ids = ".implode(",",$ids));
		$_SESSION[get_class().".ids"]=$ids;

		foreach ($dataRows as $id=>$td) {
			if (is_array($td)) $tr[]=$this->tableRow($id,$td);
			else $tr[]=$td;
		}
	}
  
  public function dot($w=1,$h=1) {
    return '<img src="'.$this->resources_url('/img/dot.gif').'" width="'.$w.'" height="'.$h.'"   />';
  }

  protected function withCheckboxes() {
    if (getConfigItem('withCheckboxes_'.$this->getManager()->getClassName())) return true;
    return isset($this->withCheckboxes) && $this->withCheckboxes;
  }

  protected function getListFields() {
		$manager=$this->getManager();
		$res=$manager->getListFields();
    if ($this->withCheckboxes()) $res[]='_cb';
    return $res;
  }

  protected function getTH($w,$fieldName) {
    #--
    if ($fieldName=='_cb') return '<td class="tdThCheckbox" align="center"><input value="0" class="checkbox" type="checkbox" onclick="$(\'#body-'.$this->tableId.' INPUT\').attr(\'checked\',this.checked?\'checked\':null)"/></td>';
    #--
    return "<td class='tdTh'>{$this->domainTranslate($this->getModel()->getName(),$fieldName)}</td>";
  }
  
  protected function trFootCheckboxItems($url) {
    $result=array();

    $e=$this->getManager()->isEditable();
    $d=$this->getManager()->isDeletable();

    if ($e) $result['e']=
<<<HTML
<td width='35' align='center'>
<a href='{$this->uri(null)}' onclick="var ids=$('#body-{$this->tableId} INPUT').serialize(); if (ids=='') return false; this.href='{$url}?edit=many&'+ids; return true;"><img height='16' src='{$this->resources_url('/img/icon/edit.png')}' alt='' title='{$this->cmsTranslate("Edit")}' border='0' /></a>
</td>
HTML;
    
    if ($d) $result['d']=
<<<HTML
<td width='35' align='center'>
<a href='{$this->uri(null)}' onclick="var ids=$('#body-{$this->tableId} INPUT').serialize(); if (ids=='') return false; if (!window.confirm('{$this->_("Delete.confirmMany")}')) return false; this.href='{$url}?delete=many&'+ids; return true;"><img height='16' src='{$this->resources_url('/img/icon/delete.png')}' alt='' title='{$this->cmsTranslate("Delete")}' border='0' /></a>
</td>
HTML;
    
    return $result;
  }
  
  protected function getFooterText() {
    return null;
  }
  
  protected function plainFooterText(&$trFoot) {
    $colspan=$this->colCount;
    $footerText=$this->getFooterText();
    if (!empty($footerText)) $trFoot[]="<tr><td></td><td colspan='{$colspan}'>{$footerText}</td><td></td></tr>";
  }
  
  protected function trFootCheckboxes(&$trFoot) {
    $colspan=$this->colCount;
    
    $arr=$this->trFootCheckboxItems(!empty($this->lineUrl)?$this->lineUrl:$this->uri(null));
    if (empty($arr) || (count($arr)==0)) {
      $this->plainFooterText($trFoot);
      return;
    }

    $footerText=$this->getFooterText();
    $td=implode('',$arr);
    $trFoot[]=
<<<HTML
<tr class="tr-footer-text"><td colspan="{$colspan}" align="right" class="footer-text">
<span style="float:left">{$footerText}</span>
<table cellspacing="0" cellpadding="5"><tr>
<td>{$this->cmsTranslate("with selected")}: </td>
{$td}
<td><img src="{$this->resources_url('/img/icon/arrow_rtl.png')}"></td>
</tr></table>
</td></tr>
HTML;
  }
  
  protected function trFoot(&$trFoot,$data=null) {
    if (!empty($data) && $this->withCheckboxes()) $this->trFootCheckboxes($trFoot);
    else $this->plainFooterText($trFoot);
  }

	public function getTableHtml($data=null,$plain=false) {
		$manager=$this->getManager();
		$fields=$this->getListFields();
		$rowButtons=$this->getRowButtons($manager);
		
		if ($data===null) $data=$manager->getListData();
		$this->data=$data;

    $trHead=array();
		$trBody=array();
    $trFoot=array();
    $colgroup=array();
		
    $this->colCount = $this->fieldCount = count($fields);
    if (($c=count($rowButtons))>0) $this->colCount += $c+1;

		#-- add
		if (!$plain) $this->tableRowAdd($trHead,$manager);

		#-- data
		$this->getTableBody($trBody,$manager,$data,$rowButtons,$fields,$plain);
    
		#if (!isset($this->tableId)) 
    $this->tableId='tbl-'.md5(rand().'|'.time());

		#-- columns
		$td=array();
		if (($c=count($rowButtons))>0) {
      #$td[]="<td colspan='{$c}'>{$this->dot()}</td>";
      foreach($rowButtons as $btnName) {
        $td[]="<td class='column-{$btnName}'>{$this->dot()}</td>";
        $colgroup[]='<col width="30" />';
      }

      //spacer
      $td[]="<td>{$this->dot(10)}</td>";
      $colgroup[]='<col width="10" />';
    }

    $totalWidth=0;
    foreach ($fields as $fieldName) {
      $totalWidth+=$this->getColumnWidth($manager,$fieldName);
    }

		foreach($fields as $i=>$fieldName) {
      if ($fieldName=='_cb') $colWidth=20;
      else {
        $colWidth=$this->getColumnWidth($manager,$fieldName);
        if ($totalWidth>0) $colWidth=round(100*($colWidth/$totalWidth)).'%';
      }
			$td[]=$this->getTH($colWidth,$fieldName);
      $colgroup[]='<col width="'.$colWidth.'" />';
		}
		if (!$plain) {
      $trHead[]="<tr class='trColumns'>\n\t\t".implode("\n\t\t",$td)."\n\t</tr>";
    }

		#-- border bottom
    if (!$plain) $trFoot[]=$this->tableRow(0,array("<td colspan='".($this->colCount)."'>{$this->dot(1,1)}</td>"),'trBorderBottom');
    $this->trFoot($trFoot,$data);

		$trHead=implode("\n\t",$trHead);
    $trBody=implode("\n\t",$trBody);
    $trFoot=implode("\n\t",$trFoot);
    $colgroup=implode("\n\t",$colgroup);
		
		$class="table list-table";
		if ($plain) $class="list-table-plain";

    $managerClass=$manager->getName().'Manager';
    
    $colsizable=(!$plain && $this->colsizable)?'$("#'.$this->tableId.'").kiketable_colsizable({ dragCells : ".tdTh", dragMove : false, dragProxy : "area" });':'';
    $sortable=(!$plain && $this->sortable)?'$("#'.$this->tableId.'").tablesorter({ selectorHeaders : ".tdTh" }).addClass("tablesorter");':'';

    self::$endlistHtml[]=
<<<JS
<script type="text/javascript">
$(document).ready(function() {
    $("#{$this->tableId}").tableDnD({
        onDrop: function(table, row) {
            jq.post('{$this->base_url()}/ajax/{$managerClass}.html?orderby',$("#body-{$this->tableId}").tableDnDSerialize(),function(data){ if (data.error) alert(data.error); },'json');
        },
        dragHandle: "dragHandle"
    });
    $("#body-{$this->tableId} td.dragHandle").hover(function() {
      $(this).addClass('hover');
    }, function() {
      $(this).removeClass('hover');
    });
    {$colsizable}
    {$sortable}
});
</script>
JS;

		return
<<<HTML
<table class='{$class}' cellspacing='0' cellpadding='0' id='{$this->tableId}'><colgroup>
  {$colgroup}
</colgroup><thead>
  {$trHead}
</thead><tfoot>
  {$trFoot}
</tfoot><tbody id='body-{$this->tableId}'>
	{$trBody}
</tbody></table>
HTML;
	}

	public function outputTable($data=null) {
		echo $this->getTableHtml($data);
	}
	
	public function endList() {
		echo <<<HTML
<!-- endList -->
HTML;
    echo implode("\n",array_reverse(self::$endlistHtml));
	}
	
	public function outputScripts() {
	}
	
	protected function getWidth() {
    if (!isset($this->listWidth)) {
      $manager=$this->getManager();
      $fields=$this->getListFields();
      $totalWidth=0;
      foreach ($fields as $fieldName) {
        $totalWidth+=$this->getColumnWidth($manager,$fieldName);
      }
      if ($totalWidth>700) $this->listWidth='100%';
      else $this->listWidth='700px';
    }
    return $this->listWidth;
  }

	public function outputStyles() {
		echo <<<CSS
.list-table {
	width:{$this->getWidth()};
 }
.list-table-plain {
	width:100%;
}
.trAdd TD, .tr-footer-text TD {
  border:0 !important;
}
.list-table TD:first-child {
  border-left: 1px solid #049ADB;
}
.list-table TD:last-child {
  border-right: 1px solid #049ADB;
}

.list-table TD {
  padding:3px 0;
  vertical-align:top;
}
.list-table .trAdd TD {
  padding:10px 0 !important;
}
.list-table .trColumns TD {
	border-top:1px solid rgb(4,154,219);
  background-color:#BEEBFF;
	color:rgb(0,77,110);
	padding:2px;
}

.list-table .trData.trHover TD, .list-table .trData:hover TD {
	background-color:#EEF
}
.list-table .trData TD {
	padding:2px 5px;
	white-space:nowrap;
	vertical-align:center;
}
.list-table .trData TD A {
	color:blue;
  border:0;
}
.list-table .trData TD.space {
  cursor:default;
}
.list-table TD.column-drag {
  background-image:url("{$this->resources_url('/img/icon/column-drag.gif')}");
  background-position:center center;
  background-repeat:no-repeat;
}
.list-table TD.dragHandle.hover {
  background-image:url("{$this->resources_url('/img/icon/drag.gif')}");
  background-position:center 3px;
  background-repeat:no-repeat;
  cursor:move;
}
.list-table .trBorderTop TD {
	border-bottom:1px solid rgb(4,154,219);
}
.list-table .trBorderBottom TD {
	border-bottom:1px solid rgb(4,154,219);
  padding:2px;
}
P.noData {
	margin:10px;
}
CSS;
	}

	public function outputContent() {
		$this->startList();
		$this->outputFancybox();
    $this->outputDescription();
		$this->outputTable();
		$this->endList();
	}

}

//end