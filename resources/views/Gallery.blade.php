<link href="{{ asset('public/uploader/jquery.fileuploader.min.css') }}" media="all" rel="stylesheet">
<link href="{{ asset('public/uploader/jquery.fileuploader-theme-thumbnails.css') }}" media="all" rel="stylesheet">

<script src="{{ asset('public/uploader/jquery.fileuploader.min.js') }}" type="text/javascript"></script>
<script>
    /* definition for custom */
    var urlForUpload = "{!! route('gallery.upload') !!}";
    var urlForRemove = "{!! route('gallery.remove') !!}";
    var urlForSort = "{!! route('gallery.sort') !!}";
    var binded = "{!! $gallery.'-'.$id !!}"
</script>
<script src="{{ asset('public/uploader/custom.js') }}" type="text/javascript"></script>

<input type="file" name="files">
