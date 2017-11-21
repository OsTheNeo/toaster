<?php

namespace Ostheneo\Toaster\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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


}
