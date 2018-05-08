<?

class CoreProductGalleryList extends CoreGenericList {

	protected function tableRowAdd(&$tr,$manager) {
    #--
    if (!getConfigItem('jquery.jqupload',true)) return parent::tableRowAdd($tr,$manager);
    #--

		if ($manager->isAddable()) {
			$tr[]=<<<HTML

<tr class='trAdd'><td colspan='{$this->colCount}' align='right'>

  <div id="div-inp-upload" style="float:right">
    <div class="row fileupload-buttonbar">
      <span class="btn btn-success fileinput-button">
        <i class="icon-plus icon-white"></i>
        <span>{$this->_("Add")}...</span>
        <input style="width:auto;" type="file" id="inp-upload" name="upload" />
      </span>
    </div>

    <div class="fileupload-loading"></div>
    <table class="table table-striped"><tbody class="files" data-toggle="modal-gallery" data-target="#modal-gallery"></tbody></table>
    <br style='clear:both' />
  </div>

<script type="text/javascript">
$(document).ready(function() {
  $('#div-inp-upload').fileupload({
    dropZone:$('#div-inp-upload'),
    url:'/ccms/ajax/ProductGalleryManager.html?imageUpload&name=list',
    autoUpload:true,
    dataType: 'json',
    done: function(e,data) {
      var that = $(this).data('fileupload');
      if (data && data.result && data.result.ok) {
        window.location.href=window.location.href;
      }
      else alert('upload failed');
      that.options.filesContainer.html('');
      that._trigger('completed', e, data);
    }
  });
  $('#div-inp-upload .files').imagegallery();
});
</script>

</td></tr><!-- trAdd -->

HTML;
		}
	}

}

//end