<?

class CorePageGalleryList extends CoreGenericList {

	public function outputScripts() {
?>

$(document).ready(function() {
  $('#div-inp-upload').fileupload({
    dropZone:$('#div-inp-upload'),
    url:'/ccms/ajax/PageGalleryManager.html?imageUpload&name=list',
    autoUpload:true,
    dataType: 'json',
    done: function(e,data) {
      if (data && data.result && data.result.ok) {
        window.location.href=window.location.href;
        return;
      }
      alert('upload failed');
      $('#div-inp-upload .files').html('');
      $(this).fileupload('option', 'done');
    }
  });
});

<?
  }

  protected function tableRowAdd(&$tr,$manager) {
    #--
    if (!getConfigItem('jquery.jqupload',true)) return parent::tableRowAdd($tr,$manager);
    #--

		if ($manager->isAddable()) {
			$tr[]=<<<HTML

<tr class="trAdd"><td colspan="{$this->colCount}" align="right">
  <div id="div-inp-upload" class="pull-right">
    <div class="fileupload-buttonbar">
      <span class="btn btn-success fileinput-button">
        <i class="icon-plus icon-white"></i>
        <span>{$this->_("Add")}...</span>
        <input style="width:auto;" type="file" id="inp-upload" name="upload" />
      </span>
    </div>

    <div class="fileupload-loading"></div>
    <table class="table table-striped" width="100%"><tbody class="files" data-toggle="modal-gallery" data-target="#modal-gallery"></tbody></table>
    <br style="clear:both" />
  </div>
</td></tr><!-- trAdd -->

HTML;
		}
	}

}

//end