
<!-- The jQuery UI widget factory, can be omitted if jQuery UI is already included -->
<script src="{shared_url}/jquery/jqupload/js/vendor/jquery.ui.widget.js"></script>

<!-- The Templates plugin is included to render the upload/download listings -->
<!-- script src="http://blueimp.github.io/JavaScript-Templates/js/tmpl.min.js"></script -->
<!-- The Load Image plugin is included for the preview images and image resizing functionality -->
<!-- script src="http://blueimp.github.io/JavaScript-Load-Image/js/load-image.min.js"></script -->
<!-- The Canvas to Blob plugin is included for image resizing functionality -->
<!-- script src="http://blueimp.github.io/JavaScript-Canvas-to-Blob/js/canvas-to-blob.min.js"></script -->
<!-- blueimp Gallery script -->
<!-- script src="http://blueimp.github.io/Gallery/js/jquery.blueimp-gallery.min.js"></script -->

<!-- script src="{shared_url}/jquery/jqupload/blueimp.js"></script -->
<script src="{shared_url}/jquery/jqupload/js/tmpl.min.js"></script>
<script src="{shared_url}/jquery/jqupload/js/load-image.min.js"></script>
<script src="{shared_url}/jquery/jqupload/js/canvas-to-blob.min.js"></script>
<script src="{shared_url}/jquery/jqupload/js/jquery.blueimp-gallery.min.js"></script>

<!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
<script src="{shared_url}/jquery/jqupload/js/jquery.iframe-transport.js"></script>
<!-- The basic File Upload plugin -->
<script src="{shared_url}/jquery/jqupload/js/jquery.fileupload.js"></script>
<!-- The File Upload processing plugin -->
<script src="{shared_url}/jquery/jqupload/js/jquery.fileupload-process.js"></script>
<!-- The File Upload image preview & resize plugin -->
<script src="{shared_url}/jquery/jqupload/js/jquery.fileupload-image.js"></script>
<!-- The File Upload audio preview plugin -->
<script src="{shared_url}/jquery/jqupload/js/jquery.fileupload-audio.js"></script>
<!-- The File Upload video preview plugin -->
<script src="{shared_url}/jquery/jqupload/js/jquery.fileupload-video.js"></script>
<!-- The File Upload validation plugin -->
<script src="{shared_url}/jquery/jqupload/js/jquery.fileupload-validate.js"></script>
<!-- The File Upload user interface plugin -->
<script src="{shared_url}/jquery/jqupload/js/jquery.fileupload-ui.js"></script>
<script src="{shared_url}/jquery/jqupload/js/locale.js"></script>

<!--[if gte IE 8]><script src="{shared_url}/jquery/jqupload/js/cors/jquery.xdr-transport.js"></script><![endif]-->

<!-- blueimp Gallery styles -->
<!-- link rel="stylesheet" href="http://blueimp.github.io/Gallery/css/blueimp-gallery.min.css" -->
<link rel="stylesheet" href="{shared_url}/jquery/jqupload/css/blueimp-gallery.min.css">

<!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
<link rel="stylesheet" href="{shared_url}/jquery/jqupload/css/jquery.fileupload.css">
<link rel="stylesheet" href="{shared_url}/jquery/jqupload/css/jquery.fileupload-ui.css">
<!--[if lt IE 9]><script src="https://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->

<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-upload fade">
        <td class="preview"><span class="fade"></span></td>
        <td class="name"><span>{%=file.name%}</span></td>
        <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
        {% if (file.error) { %}
            <td class="error" colspan="2"><span class="label label-important">{%=locale.fileupload.error%}</span> {%=locale.fileupload.errors[file.error] || file.error%}</td>
        {% } else if (o.files.valid && !i) { %}
            <td>
                <div class="progress progress-success progress-striped active"><div class="bar" style="width:0%;"></div></div>
            </td>
            <td class="start">{% if (!o.options.autoUpload) { %}
                <button class="btn btn-primary">
                    <i class="icon-upload icon-white"></i>
                    <span>{%=locale.fileupload.start%}</span>
                </button>
            {% } %}</td>
        {% } else { %}
            <td colspan="2"></td>
        {% } %}
        <td class="cancel">{% if (!i) { %}
            <button class="btn btn-warning">
                <i class="icon-ban-circle icon-white"></i>
                <span>{%=locale.fileupload.cancel%}</span>
            </button>
        {% } %}</td>
    </tr>
{% } %}
</script>
<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-download fade">
        {% if (file.error) { %}
            <td></td>
            <td class="name"><span>{%=file.name%}</span></td>
            <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
            <td class="error" colspan="2"><span class="label label-important">{%=locale.fileupload.error%}</span> {%=locale.fileupload.errors[file.error] || file.error%}</td>
        {% } else { %}
            <td class="preview">{% if (file.thumbnail_url) { %}
                <a href="{%=file.url%}" title="{%=file.name%}" rel="gallery" download="{%=file.name%}"><img src="{%=file.thumbnail_url%}"></a>
            {% } %}</td>
            <td class="name">
                <a href="{%=file.url%}" title="{%=file.name%}" rel="{%=file.thumbnail_url&&'gallery'%}" download="{%=file.name%}">{%=file.name%}</a>
            </td>
            <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
            <td colspan="2"></td>
        {% } %}
        <!--
        td class="delete">
            <button class="btn btn-danger" data-type="{%=file.delete_type%}" data-url="{%=file.delete_url%}">
                <i class="icon-trash icon-white"></i>
                <span>{%=locale.fileupload.destroy%}</span>
            </button>
            <input type="checkbox" name="delete" value="1">
        </td
        -->
    </tr>
{% } %}
</script>