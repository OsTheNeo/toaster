<link href="{{ asset('public/uploader/jquery.fileuploader.min.css') }}" media="all" rel="stylesheet">
<link href="{{ asset('public/uploader/jquery.fileuploader-theme-thumbnails.css') }}" media="all" rel="stylesheet">

<script src="{{ asset('public/uploader/jquery.fileuploader.min.js') }}" type="text/javascript"></script>
<script>
    /* definition for custom */
    var urlForUpload = "{!! route('gallery.upload') !!}";
    var urlForRemove = "{!! route('gallery.remove') !!}";
    var urlForSort = "{!! route('gallery.sort') !!}";
    var urlForEditor = "{!! route('gallery.edit') !!}";
    var binded = "{!! $gallery.'-'.$id !!}"
    var loadedFiles = {};

    $.ajax({
        url : "{!! route('gallery.request', [$gallery.'-'.$id]) !!}",
        type : "get",
        async: false,
        success : function(data) {
            loadedFiles = JSON.parse(data);
        },
        error: function() {
            loadedFiles = {};
        }
    });



</script>
<script src="{{ asset('public/uploader/custom.js') }}" type="text/javascript"></script>

<input type="file" name="files">
