<?php

namespace Ostheneo\Toaster\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ToasterController extends Controller
{
    protected $Repository;
    protected $Model;
    protected $message;
    protected $init;
    protected $NameDataViews;
    protected $options;

    public function index()
    {
        return view('Toaster::Content', $this->options);
    }

    public function DataAsJson()
    {

    }

    function buildLinks($links, $model)
    {
        $data = null;
        foreach ($links as $link) {
            $available = true;

            if (isset($link['role'])) {
                if (!Auth::user()->hasrole($link['role'])) $available = false;
            }

            if (isset($link['permission'])) {
                if (!Auth::user()->can($link['permission'])) $available = false;
            }

            if ($available == true) {
                $parameters = $link['parameters'];
                $route = '';
                $nameroute = '';

                if (isset($parameters['data'])) {
                    if (isset($parameters['custom'])) {

                    } else {
                        $route = route($parameters[route], $model->id);
                    }
                } else {
                    $route = route($parameters['route']);
                }

                $nameroute = $parameters['routename'];
                $data .= "<a href='$route'>$nameroute</a>";
            }

        }
        return $data;
    }

    function askToDatabase(Request $request)
    {
        $data = $request->all();
        $howShowData = $data['alias'];

        $settings = $this->NameDataViews[$howShowData];
        $query = DB::table($settings->nameTable);
        $columns = $settings['columns'];

        $recordsTotal = $query->count();

        if ($data['search']) {
            $search = $data['search']['value'];
            $query->where(function ($query) use ($columns, $request, $search) {
                $firstWhere = true;
                foreach ($columns as $column) {
                    $namecolumn = $column['db'];
                    if ($firstWhere == true) {
                        $query->where($namecolumn, 'like', '%' . $search['value'] . '%');
                    } else {
                        $query->orWhere($namecolumn, 'like', '%' . $search['value'] . '%');
                    }
                    $firstWhere = false;
                }
            });
        }

        $recordsFiltered = $query->count();


        if ($data['order']) {
            $order = $data['order'];
            $query->orderBy($order['column'], $order['dir']);
        }

        $query->take($request['length']);
        $query->skip($request['start']);

        return ["draw"            => isset ($request['draw']) ? intval($request['draw']) : 0,
                "recordsTotal"    => $recordsTotal,
                "recordsFiltered" => $recordsFiltered,
                "data"            => $query->get()];


    }

    function create()
    {
        $models = [];

        foreach ($this->options['models'] as $model) {
            $nameModel = explode(rtrim('\ '), $model);
            $models[end($nameModel)] = new $model;
        }

        $this->options['models'] = $models;
        $this->options['access'] = 'create';

        return view('Toaster::Content', $this->options);
    }

    public function store(Request $request)
    {
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
                Session::flash('message', 'Fue una creacion exitosa. que sigue? <br>' . $this->options($model, $this->options->sucessOptions));
                return redirect(route($this->options->routeRedirect, $model->id));
            }
            return redirect($this->options->redirect);
        } else {
            return redirect()->back();
        }


    }

    public function update(Request $request, $id){
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

    public function edit($id){
        $models = [];

        foreach ($this->options['models'] as $model) {
            $nameModel = explode(rtrim('\ '), $model);
            $models[end($nameModel)] = new $model;
        }

        $this->options['model'] = $this->Model;
        $this->options['models'] = $models;
        $this->options['access'] = 'edit';
        return view('Toaster::Content', $this->options);
    }

    function populate($model, $form)
    {
        unset($form['_method']);
        foreach ($form as $field => $value) {
            $model->$field = $value;
        }
        return $model;
    }

    function options($model, $options)
    {
        $html = '';
        foreach ($options as $name => $options) {
            if (isset($options['required'])) {
                $parameters = [];
                if (is_array($options['required'])) {
                    foreach ($options['required'] as $field) {
                        array_push($parameters, $model->field);
                    }
                } else {
                    array_push($parameters, $model->$options['required']);
                }
                $html .= '<a href="' . route($options['route'], $parameters) . '">' . $name . '</a>';
            } else {
                $html .= '<a href="' . route($options['route']) . '">' . $name . '</a>';
            }

        }

        return $html;
    }

    public function Datatable($alias){

        dd($_GET, $alias);
    }

}
