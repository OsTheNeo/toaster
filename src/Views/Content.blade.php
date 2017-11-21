@foreach(\Ostheneo\Toaster\BladeEngine::CssIncludes($contents) as $css)
    <link rel="stylesheet" href="{{$css}}">
@endforeach

@foreach($contents as $content)
    <div class="{{ $content->css_class or null}}">


        @if($content['visualization'] == 'list')

        @elseif($content['visualization'] == 'table')

            {!!  \Ostheneo\Toaster\BladeEngine::table($content) !!}

        @elseif($content['visualization'] == 'timeline')
            <ul>
                @foreach($content as $item)
                    <li>{!! \Ostheneo\Toaster\BladeEngine::makeItemTimeline($item) !!}</li>
                @endforeach
            </ul>

        @elseif($content['visualization'] == 'form')




            @if(isset($content['model']))
                {!! Form::model($content['model'], ['route' => [$data->route, $model->id],'files'=>$data->file, 'method' => 'patch']) !!}
            @else
                {!! Form::open(['route' => $data->route, 'files'=>$data->file, 'method'=>'POST']) !!}
            @endif


            @if(isset($model))
                {!! \App\FormHandler::buildFields($model) !!}
            @else
                {!! \App\FormHandler::buildFields($premodel) !!}
            @endif

            @if(isset($hidden))
                @foreach($hidden as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
            @endif

            @if(isset($custom))
                @include($custom)
            @endif

            @if(isset($model))
                @if($gallery == true)
                    @include('backend.template.gallery')
                @endif
            @endif

            <div class="row">
                {!! Form::submit('Guardar cambios', ['class' => 'btn']) !!}
            </div>

            {!! Form::close() !!}


            @if(isset($editGallery) and isset($model))
                {{ \App\Gallery::editGallery($model) }}
            @endif



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

{{ \Ostheneo\Toaster\BladeEngine::defineVars() }}

@foreach(\Ostheneo\Toaster\BladeEngine::JsIncludes($contents) as $js)
    <link rel="stylesheet" href="{{$js}}">
@endforeach