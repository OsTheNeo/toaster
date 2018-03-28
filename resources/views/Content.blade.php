@extends('Toaster::Template.Layout')

@section('content')

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.1.1/css/responsive.dataTables.min.css">

    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.1.1/js/dataTables.responsive.min.js"></script>

    <style>
        form, table {
            width: 100%;
        }
    </style>


    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">

                @foreach(\OsTheNeo\Toaster\BladeEngine::CssIncludes(@$contents) as $css)
                    <link rel="stylesheet" href="{{$css}}">
                @endforeach

                @foreach (['danger', 'warning', 'success', 'info'] as $key)
                    @if(Session::has($key))
                        <p class="alert alert-{{ $key }}">{{ Session::get($key) }}</p>
                    @endif
                @endforeach



                @if($errors->any())
                    <ul class="alert error">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif

                @if(!isset($containers))
                    @php
                        if(!is_array($contents)){
                            $contents = [$contents];
                        }
                        $containers = ['contents' => $contents];
                    @endphp
                @endif


                @foreach($containers as $contents)
                    <div class="{{ $contents['class'] or 'col-12' }}">
                        @php
                            unset($contents['class']);
                        @endphp


                        @foreach($contents as $content)

                            <div class="row">
                                @foreach(\OsTheNeo\Toaster\BladeEngine::buildButtons($content) as $position => $html)
                                    <div class="{!! $position !!}">
                                        {!! $html !!}
                                    </div>
                                @endforeach
                            </div>

                            <div class="row">
                                @if($content->visualization == 'list')

                                @elseif($content->visualization == 'table')
                                    {!!  \OsTheNeo\Toaster\BladeEngine::table($content) !!}
                                    @if($content->data == "ajax")
                                        <script>
                                            $.fn.dataTable.pipeline = function (opts) {
                                                var conf = $.extend({
                                                    pages: 5, url: '', data: null, method: 'GET'
                                                }, opts);

                                                var cacheLower = -1;
                                                var cacheUpper = null;
                                                var cacheLastRequest = null;
                                                var cacheLastJson = null;

                                                return function (request, drawCallback, settings) {
                                                    var ajax = false;
                                                    var requestStart = request.start;
                                                    var drawStart = request.start;
                                                    var requestLength = request.length;
                                                    var requestEnd = requestStart + requestLength;

                                                    if (settings.clearCache) {
                                                        ajax = true;
                                                        settings.clearCache = false;
                                                    }
                                                    else if (cacheLower < 0 || requestStart < cacheLower || requestEnd > cacheUpper) {
                                                        ajax = true;
                                                    }
                                                    else if (JSON.stringify(request.order) !== JSON.stringify(cacheLastRequest.order) ||
                                                        JSON.stringify(request.columns) !== JSON.stringify(cacheLastRequest.columns) ||
                                                        JSON.stringify(request.search) !== JSON.stringify(cacheLastRequest.search)
                                                    ) {
                                                        ajax = true;
                                                    }
                                                    cacheLastRequest = $.extend(true, {}, request);
                                                    if (ajax) {
                                                        if (requestStart < cacheLower) {
                                                            requestStart = requestStart - (requestLength * (conf.pages - 1));
                                                            if (requestStart < 0) {
                                                                requestStart = 0;
                                                            }
                                                        }
                                                        cacheLower = requestStart;
                                                        cacheUpper = requestStart + (requestLength * conf.pages);
                                                        request.start = requestStart;
                                                        request.length = requestLength * conf.pages;
                                                        if ($.isFunction(conf.data)) {
                                                            var d = conf.data(request);
                                                            if (d) {
                                                                $.extend(request, d);
                                                            }
                                                        }
                                                        else if ($.isPlainObject(conf.data)) {
                                                            $.extend(request, conf.data);
                                                        }
                                                        settings.jqXHR = $.ajax({
                                                            "type": conf.method,
                                                            "url": conf.url,
                                                            "data": request,
                                                            "dataType": "json",
                                                            "cache": false,
                                                            "success": function (json) {
                                                                cacheLastJson = $.extend(true, {}, json);
                                                                if (cacheLower != drawStart) {
                                                                    json.data.splice(0, drawStart - cacheLower);
                                                                }
                                                                if (requestLength >= -1) {
                                                                    json.data.splice(requestLength, json.data.length);
                                                                }
                                                                drawCallback(json);
                                                            }
                                                        });
                                                    }
                                                    else {
                                                        json = $.extend(true, {}, cacheLastJson);
                                                        json.draw = request.draw; // Update the echo for each response
                                                        json.data.splice(0, requestStart - cacheLower);
                                                        json.data.splice(requestLength, json.data.length);
                                                        drawCallback(json);
                                                    }
                                                }
                                            };

                                            $.fn.dataTable.Api.register('clearPipeline()', function () {
                                                return this.iterator('table', function (settings) {
                                                    settings.clearCache = true;
                                                });
                                            });

                                            $(document).ready(function () {
                                                var table = $('#{{ $content->schema }}').DataTable({
                                                    "columnDefs": [
                                                        {
                                                            "targets": [0],
                                                            "visible": false,
                                                            "searchable": false
                                                        }
                                                    ],
                                                    "order": [[0, "desc"]],
                                                    "processing": true,
                                                    "serverSide": true,
                                                    "pageLength": 20,
                                                    "language": {
                                                        "url": "https://cdn.datatables.net/plug-ins/1.10.12/i18n/Spanish.json"
                                                    },
                                                    "ajax": $.fn.dataTable.pipeline({
                                                        url: '{{ route('server.pipeline', $content->schema) }}',
                                                        pages: 5
                                                    })
                                                });
                                            });
                                        </script>
                                    @elseif($content->data != null)
                                        <tbody>
                                        @foreach($content->data as $row)
                                            <tr>
                                                @foreach($content->model->schemas[$content->schema] as $field)
                                                    @if($field[0] != '_')
                                                        <td> {!! $row->$field !!}</td>
                                                    @else
                                                        @if($field == '_links')
                                                            <td>{!! \OsTheNeo\Toaster\BladeEngine::buildLinks($content->model, $content->schema, $row) !!}</td>
                                                        @endif
                                                    @endif
                                                @endforeach
                                            </tr>
                                        @endforeach
                                        @endif
                                        </tbody>
                                        </table>
                                    @elseif($content->visualization == 'timeline')
                                        <ul>
                                            @foreach($content as $item)
                                                <li>{!! \OsTheNeo\Toaster\BladeEngine::makeItemTimeline($item) !!}</li>
                                            @endforeach
                                        </ul>

                                    @elseif($content->visualization == 'form')

                                        @php
                                            $saveButton = true;
                                        @endphp

                                        @if($access == 'edit')
                                            {!! Form::model($model, ['route' => [$model->routes[$access], $model->id], 'files'=>$model->files or false, 'method' => 'PUT']) !!}
                                        @else
                                            @php $model = $models[$content->model]; @endphp
                                            {!! Form::open(['route' => $model->routes[$access], 'files'=>$model->files or false, 'method'=>'POST']) !!}
                                        @endif

                                        @if(isset($pre))
                                            @foreach($pre as $hidden)
                                                {!! Form::hidden($hidden[0], $hidden[1]) !!}
                                            @endforeach
                                        @endif



                                        @foreach(\OsTheNeo\Toaster\BladeEngine::buildFields($content, $model) as $key => $value)
                                            <div class="form-group">
                                                {!! $value->label !!}
                                                @if($value->field != null)
                                                    {!! $value->field !!}
                                                @else
                                                    @include("Store::$value->include")
                                                @endif
                                            </div>
                                        @endforeach


                                        @if(isset($custom))
                                            @include($custom)
                                        @endif


                                        @if(isset($gallery) and isset($model->id))
                                            @include('Toaster::Gallery')
                                        @endif



                                        {!! Form::close() !!}

                                    @elseif($content['visualization'] == 'plain')

                                    @else
                                        <dl>
                                            @foreach($content as $key => $value)
                                                <dt>{{$key}}</dt>
                                                <dd>{{$value}}</dd>
                                            @endforeach
                                        </dl>
                                    @endif

                            </div>
                        @endforeach
                    </div>
                @endforeach

                @if(isset($saveButton))
                    <button type="button" class="btn btn-dark" onclick="saveForms();">Guardar Cambios</button>
                @endif



                {{ \OsTheNeo\Toaster\BladeEngine::defineVars() }}

                @if($content->visualization == 'form')
                    <script>
                        $("form").submit(function (e) {
                            return false;
                        });

                        function saveForms() {

                            var forms = [];
                            var action = '';
                            $("form").each(function () {
                                if ($(this).attr('method') === 'POST') {
                                    action = $(this).attr('action');
                                    forms.push($(this).serialize());
                                }
                            });

                            var form = $(document.createElement('form'));
                            $(form).attr("action", action);
                            $(form).attr("method", "POST");

                            var input = $("<input>").attr("type", "hidden").attr("name", "forms").val(JSON.stringify(forms));
                            $(form).append($(input));
                            var input = $("<input>").attr("type", "hidden").attr("name", "_token").val('{{ csrf_token() }}');
                            $(form).append($(input));

                                    @if($access == 'edit')
                            var input = $("<input>").attr("type", "hidden").attr("name", "_method").val('patch');
                            $(form).append($(input));
                            @endif

                            form.appendTo(document.body);
                            $(form).submit();

                        }
                    </script>
                @endif

                @foreach(\OsTheNeo\Toaster\BladeEngine::JsIncludes($contents) as $js)
                    <link rel="stylesheet" href="{{$js}}">
                @endforeach

            </div>
        </div>
    </div>

@endsection