<?php

namespace OsTheNeo\Toaster\Controllers;

use App\Dictionary;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use OsTheNeo\Toaster\BladeEngine;
use OsTheNeo\Toaster\Models\Gallery;

class ToasterController extends Controller {

    /**
     * @param $name
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */

    protected $Repository;
    protected $Model;
    protected $message;
    protected $init;
    protected $NameDataViews;
    protected $options;
    protected $admin; // define si se usa el admin por defecto o uno custom


    public function index() {
        if (isset($this->admin))
            $this->options->extend = $this->admin;

        return view('Toaster::Content', $this->options);
    }

    function create() {
        $models = [];

        foreach ($this->options['models'] as $model) {
            $nameModel = explode(rtrim('\ '), $model);
            $models[end($nameModel)] = new $model;
        }

        $this->options['models'] = $models;
        $this->options['access'] = 'create';

        return view('Toaster::Content', $this->options);
    }

    function askToDatabase(Request $request, $alias) {
        $data = $request->all();
        $model = Dictionary::alias($alias);
        $replacement = Dictionary::replacemente($alias);

        $model = new $model;
        $links = false;

        $howShowData = $model->schemas[$alias];
        $nameTable = $model->getTable();

        if (isset($howShowData->parameters->customTable)) {
            $nameTable = $howShowData->parameters->customTable;
        }

        $query = DB::table($nameTable);
        if (isset($model->views[$alias])) {
            $query = DB::table($model->views[$alias]);
        }


        $columns = $howShowData;
        $position = array_search('_links', $columns);
        if ($position) {
            unset($columns[$position]);
            $links = true;
        }


        $query->select($columns);
        if(isset($data['filter'])){
            foreach ($data['filter'] as $key=>$filter){
                if(!is_array($filter)){
                    switch ($key){
                        case 'isNull':
                            $query->whereNull($filter);
                            break;
                        case 'notNull':
                            $query->whereNotNull($filter);
                            break;
                        default:
                            $query->where($key,$filter);
                            break;
                    }
                }else{
                    switch ($key){
                        case 'or':
                            $query->orWhere($filter[0],$filter[1],$filter[2]);
                            break;
                        default:
                            $query->where($filter[0],$filter[1],$filter[2]);
                            break;
                    }
                }
            }
        }
        $recordsTotal = $query->count();

        if ($data['search']['value'] != null) {
            $search = $data['search']['value'];
            $query->where(function ($query) use ($columns, $request, $search) {
                $firstWhere = true;
                foreach ($columns as $column) {
                    $namecolumn = $column;
                    if ($firstWhere == true) {
                        $query->where($namecolumn, 'like', '%' . $search . '%');
                    } else {
                        $query->orWhere($namecolumn, 'like', '%' . $search . '%');
                    }
                    $firstWhere = false;
                }
            });
        }

        if($data['order'][0]['column']){
            $orderColumn = $columns[$data['order'][0]['column']];
            $dir = $data['order'][0]['dir'];
            $query->orderBy($orderColumn, $dir);
        }

        $recordsFiltered = $query->count();

        $query->skip($request['start']);
        $query->take($request['length']);

        /* este pedazo elimina el indice de los array para dejar un array simple */
        $data = $query->get()->toArray();
        $temp = [];
        foreach ($data as $row) {
            $item = [];
            foreach ($row as $key => $value) {
                if (isset($replacement[$key])) {
                    $itemAux=null;
                    if ($replacement[$key]['kind'] == 'group') {
                        $data = Dictionary::groupDefinitions($key);
                        $itemAux = isset($data[$value]) ? $data[$value] : 'Indefinido';
                    }elseif ($replacement[$key]['kind'] == 'json'){
                        $itemAux=Dictionary::jsonDefinitionValue($value,$replacement[$key]['value'],isset($replacement[$key]['splitData'])?$replacement[$key]['splitData']:null);
                    }elseif ($replacement[$key]['kind'] == 'groupCustom'){
                        $itemAux=Dictionary::groupCustomDefinitions($key,$replacement[$key]['parameters'],compact('row','value','model'));
                    }
                    if($itemAux) $value=$itemAux;
                }
                $item[] = $value;
            }

            if ($links == true) {
                $item[] = BladeEngine::buildLinks($model, $alias, $row);

            }
            array_push($temp, $item);
        }

        return ["draw"            => isset ($request['draw']) ? intval($request['draw']) : 0,
                "recordsTotal"    => $recordsTotal,
                "recordsFiltered" => $recordsFiltered,
                "data"            => $temp];

    }


    public function edit($id) {
        $models = [];

        foreach ($this->options['models'] as $model) {
            $nameModel = explode(rtrim('\ '), $model);
            $models[end($nameModel)] = new $model;
        }
        $this->options['id'] = $id;
        $this->options['model'] = $this->Model;
        $this->options['models'] = $models;
        $this->options['access'] = 'edit';

        return view('Toaster::Content', $this->options);
    }

    public function store(Request $request) {
        $model = $this->Model;

        if (isset($this->options->forms)) {
            foreach ($this->options->forms as $form) {
                $model = $this->populate($model, $form);
            }
        } else {
        }

        $model->save();
        if (isset($this->options->redirect)) {
            if ($this->options->redirect == 'edit') {
                Session::flash('success', 'Fue una creacion exitosa. que sigue? <br>' . $this->options($model, $this->options->sucessOptions));
                return redirect(route($this->options->routeRedirect, $model->id));
            }
            return redirect($this->options->redirect);
        } else {
            return redirect()->back();
        }
    }

    public function update(Request $request, $id) {
        $model = $this->Model;

        if (isset($this->options->forms)) {
            foreach ($this->options->forms as $form) {
                $model = $this->populate($model, $form);
            }
        } else {

        }
        $model->save();
        if (isset($this->options->redirect)) {
            if ($this->options->redirect == 'edit') {
                Session::flash('message', 'Fue una actualizacion exitosa. que sigue? <br>' . $this->options($model, $this->options->sucessOptions));
                return redirect(route($this->options->routeRedirect, $model->id));
            }
            return redirect($this->options->redirect);
        } else {
            return redirect()->back();
        }
    }


    function populate($model, $form) {
        unset($form['_method']);
        foreach ($form as $field => $value) {
            $model->$field = $value;
        }
        return $model;
    }

    function options($model, $options) {
        $html = '';
        foreach ($options as $name => $option) {

            if (isset($option['required'])) {
                $parameters = [];
                if (is_array($option['required'])) {
                    foreach ($option['required'] as $field) {
                        array_push($parameters, $model->$field);
                    }
                } else {
                    array_push($parameters, null);
                }
                $html .= '<a href="' . route($option['route'], $parameters) . '">' . $name . '</a>';
            } else {
                $html .= '<a href="' . route($option['route']) . '">' . $name . '</a>';
            }

        }

        return $html;
    }

    public function show($id) {
        if (isset($this->admin))
            $this->options->extend = $this->admin;

        return view('Toaster::Content', $this->options);
    }

    public function delete($id) {

    }


    public function getJsonIcon($id, $variant) {

        $bin = [$variant => $id];
        $gallery = Gallery::where('binded', json_encode($bin))->first();
        if ($gallery == null) {
            $gallery = new Gallery();
            $gallery->binded = json_encode($bin);
            $gallery->images = json_encode([], true);
            $gallery->save();
        }
        $path = $this->path($gallery->created_at);
        if ($gallery->icon != null) {
            return '{"icon": "' . URL::asset('public/files/' . $path . '/' . $gallery->icon) . '"}';
        } else {
            return null;
        }

    }

    public function getJsonImages($id, $variant) {
        $bin = [$variant => $id];
        $gallery = Gallery::where('binded', json_encode($bin))->first();
        $path = $this->path($gallery->created_at);
        $images = json_decode($gallery->images);
        $temp = [];
        foreach ($images as $key => $value) {
            array_push($temp, URL::asset('public/files/' . $path . '/' . $value));
        }
        $temp = ['images' => $temp];

        return json_encode($temp);
    }

    public function saveIcon() {
    }

    public function saveImages(Request $request) {
        $input = $request->all();
        $bin = [$input['route'] => $input['model']];
        $gallery = Gallery::where('binded', json_encode($bin))->first();
        $path = $this->path($gallery->created_at);
        $image = $request->file('image');
        $filename = time() . '-' . $image->getClientOriginalName() . '.' . $image->getClientOriginalExtension();
        $image->move(('public/files/' . $path), $filename);
        $images = json_decode($gallery->images);
        array_push($images, $filename);
        $gallery->images = json_encode($images);
        $gallery->save();
    }

    public function deleteIcon(Request $request) {
        $input = $request->all();
        $bin = [$input['route'] => $input['model']];
        $gallery = Gallery::where('binded', json_encode($bin))->first();
        $path = $this->path($gallery->created_at);
        $image = $request->file('image');
        $filename = time() . '-' . $image->getClientOriginalName() . '.' . $image->getClientOriginalExtension();
        $image->move(('public/files/' . $path), $filename);
        $gallery->icon = $filename;
        $gallery->save();
    }

    public function deleteImages(Request $request) {
        $input = $request->all();
        $bin = [$input['route'] => $input['model']];
        $gallery = Gallery::where('binded', json_encode($bin))->first();
        $images = json_decode($gallery->images, true);
        $img = (explode('/', $input['img']));
        $img = end($img);
        foreach ($images as $key => $value) {
            if ($value == $img) {
                unset($images[$key]);
            }
        }
        $gallery->images = json_encode($images);
        $gallery->save();
    }

    function path($date) {
        $date = explode(' ', $date);
        $date = explode('-', $date[0]);

        return $date[0] . '/' . $date[1];
    }

    public function galleryUpload(Request $request) {
        $input = $request->all();
        $image = $request->file('files');
        $image = $image[0];

        $gallery = Gallery::where('binded', $input['binded'])->first();

        if ($gallery == null) {
            $gallery = new Gallery();
            $gallery->images = json_encode([], true);
            $gallery->binded = $input['binded'];
            $gallery->save();
        }

        $path = $this->path($gallery->created_at);
        $filename = $image->getClientOriginalName();
        if ($image->move(('public/files/' . $path), $filename)) {
            $array = json_decode($gallery->images, true);
            $array[] = $filename;
            $gallery->images = json_encode($array, true);
            $gallery->save();
            return [];
        } else {
            return false;
        }
    }

    public function gallerySort(Request $request) {
        $input = $request->all();
        $currentGallery = Gallery::where('binded', $input['binded'])->first();
        $images = [];

        foreach (json_decode($input['_list'], true) as $item) {
            $images[$item['index']] = $item['name'];
        }

        $currentGallery->images = json_encode($images, true);
        $currentGallery->save();
    }


    public function galleryRequest($binded) {
        $currentGallery = Gallery::where('binded', $binded)->first();
        $images = json_decode($currentGallery->images, true);
        $path = $this->path($currentGallery->created_at);
        $files = [];
        foreach ($images as $image) {
            $files[] = ['name' => $image,
                        'size' => 1024,
                        'type' => 'image/jpeg',
                        'file' => url('public/files') . '/' . $path . '/' . $image,
                        'data' => [
                            'url' => url('public/files') . '/' . $path . '/' . $image]
            ];
        }

        return json_encode($files);
    }

    public function galleryRemove(Request $request) {
        $input = $request->all();
        $gallery = Gallery::where('binded', $input['binded'])->first();
        $images = json_decode($gallery->images, true);
        $position = array_search($input['file'], $images);
        unset($images[$position]);
        $gallery->images = json_encode($images, true);
        $gallery->save();

        $path = $this->path($gallery->created_at);
        unlink('public/files/' . $path . '/' . $input['file']);
    }


    public function unserializeForms($forms) {
        $formData = [];
        $decode = json_decode($forms);

        foreach ($decode as $form) {
            parse_str($form, $vars);
            unset($vars['_token']);
            if (isset($vars['var-name'])) {
                $variant_details = '';
                foreach ($vars['var-name'] as $key => $value) {
                    $variant_details[$value] = $vars['var-option'][$key];
                }
                unset($vars['var-name']);
                unset($vars['var-option']);
                $vars['variant_details'] = json_encode($variant_details);
            }
            array_push($formData, $vars);
        }

        foreach ($formData as $key => $form) {
            if (isset($form['forms'])) {
                unset($formData[$key]);
            }
        }

        return $formData;
    }


}