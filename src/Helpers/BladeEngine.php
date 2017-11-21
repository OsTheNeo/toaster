<?php
/**
 * Created by PhpStorm.
 * User: OsTheNeo
 * Date: 15/11/2017
 * Time: 3:29 PM
 */

namespace Ostheneo\Toaster;


use Illuminate\Support\Facades\DB;

class BladeEngine
{

    public $directory = [
        'depends' => [
            'jquery'     => '',
            'modernizer' => ''
        ],
        'css'     => [
            'datatable' => [],
            'datetime'  => [],
            'validator' => []

        ],
        'js'      => [
            'datatable' => [],
            'datetime'  => [],
            'validator' => []
        ]
    ];

    public static function JsIncludes($contents = null)
    {
        foreach ($contents as $content) {

        }

        return [];
    }

    public static function CssIncludes($contens = null)
    {
        return [];
    }

    public static function table($content)
    {
        $table = '<table>';
        $columnDates = ['created_at', 'updated_at', 'deleted_at'];
        $header = '';
        $content = (object)$content;
        if (isset($content->model)) {
            $model = new $content->model;
            if (!isset($model->fields)) {
                $columns = DB::select("describe $model->table");
            } else {
                $columns = $model->fields;
            }

            foreach ($columns as $column) {
                if (!isset($columnDates[$column->Field])){
                    $header .= "<th>$column->Field</th>";
                }

            }
        }
        $table .= "<thead>$header</thead>";


        return $table;

    }


    public static function makeItemTimeline()
    {
    }

    public static function makeScriptDatatable()
    {

    }

    public static function defineVars()
    {

    }


}