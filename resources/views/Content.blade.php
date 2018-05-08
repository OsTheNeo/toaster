@extends(config('toaster.template'))

@section(config('toaster.content'))

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.1.1/css/responsive.dataTables.min.css">

    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script src="//cdn.datatables.net/responsive/2.1.1/js/dataTables.responsive.min.js"></script>


    <div class="uk-container">
        <div>
            @foreach(\OsTheNeo\Toaster\BladeEngine::CssIncludes(@$contents) as $css)
                <link rel="stylesheet" href="{{$css}}">
            @endforeach

            @foreach (['danger', 'warning', 'success', 'info'] as $key)
                @if(Session::has($key))
                    <div class="uk-alert-{{ $key }}">
                        <a class="uk-alert-close" uk-close></a>
                        {{ Session::get($key) }}
                    </div>
                @endif
            @endforeach

            @if($errors->any())
                <ul class="uk-alert-warning">
                    <a class="uk-alert-close" uk-close></a>
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
                <div class="uk-container">
                    @php
                        unset($contents['class']);
                    @endphp


                    @if(isset($title))
                        <span class="uk-text-lead">{!! $title !!}</span>
                    @endif

                    @foreach($contents as $content)

                        @if(isset($content->title))
                            <h3 class="uk-text-lead">{!! $content->title !!}</h3>
                        @endif

                        <div class="uk-container">
                            @foreach(\OsTheNeo\Toaster\BladeEngine::buildButtons($content) as $position => $html)
                                <div class="{!! $position !!}">
                                    {!! $html !!}
                                </div>
                            @endforeach
                        </div>

                        <div class="uk-container">
                            @if($content->visualization == 'list')
                                <ul class="uk-list uk-list-striped">
                                    @foreach($content->data as $key => $value)
                                        <li> {!! $key !!} : {!! $value !!}</li>
                                    @endforeach
                                </ul>

                            @elseif($content->visualization == 'table')
                                <table id="{!! $content->schema !!}" class="uk-table uk-table-small uk-table-hover">
                                    <thead>
                                    {!!  \OsTheNeo\Toaster\BladeEngine::table($content) !!}
                                    </thead>

                                    @if($content->data != null and $content->data != "ajax")
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

                                        </tbody>
                                    @else
                                        <script src="{{ asset('public/datatable/datatable.pipeline.js') }}"></script>
                                        <script>
                                            $(document).ready(function () {
                                                var table = $('#{{ $content->schema }}').DataTable({
                                                    "columnDefs": [
                                                        {
                                                            "targets": [0],
                                                            "visible": false,
                                                            "searchable": false
                                                        }
                                                    ],
                                                    "order": [[{!! isset($content->orderBy)?$content->orderBy:'0, "desc"' !!}]],
                                                    "processing": true,
                                                    "serverSide": true,
                                                    "pageLength": 10,
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
                                    @endif
                                </table>

                                <script>
                                    $(document).ready(function () {
                                        var table = $('#{{ $content->schema }}').DataTable()
                                    });
                                </script>
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
                                    {!! Form::model($model, ['route' => [$model->routes[$access], $model->id], 'files'=>$model->files or false, 'method' => 'PUT', 'class'=>'uk-form-horizontal']) !!}
                                @else
                                    @php $model = $models[$content->model]; @endphp
                                    {!! Form::open(['route' => $model->routes[$access], 'files'=>$model->files or false, 'method'=>'POST', 'class'=>'uk-form-horizontal']) !!}
                                @endif

                                @if(isset($pre))
                                    @foreach($pre as $hidden)
                                        {!! Form::hidden($hidden[0], $hidden[1]) !!}
                                    @endforeach
                                @endif


                                @foreach(\OsTheNeo\Toaster\BladeEngine::buildFields($content, $model) as $key => $value)
                                    <div class="uk-margin">
                                        {!! $value->label !!}
                                        @if($value->field != null)
                                            <div class="uk-form-controls">
                                                {!! $value->field !!}
                                            </div>

                                        @else
                                        @endif
                                    </div>
                                @endforeach


                                @if(isset($gallery) and isset($model->id))
                                    @include('Toaster::Gallery')
                                @endif

                                @if(isset($custom))
                                    @include($custom)
                                @endif


                                {!! Form::close() !!}

                            @elseif($content->visualization == 'plain')
                                <dl>
                                    @foreach($content->rows as $key => $row)
                                        <dt>{{$row}}</dt>
                                        <dd>{{$model->$key}}</dd>
                                    @endforeach
                                </dl>
                            @endif

                        </div>
                    @endforeach
                </div>
            @endforeach

            @if(isset($saveButton))
                <div class="uk-margin uk-text-center">
                    <button type="button" class="uk-button uk-button-primary" onclick="saveForms();">Guardar Cambios</button>
                </div>
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

@endsection