<?php
/**
 * Created by PhpStorm.
 * User: OsTheNeo
 * Date: 15/11/2017
 * Time: 3:29 PM
 */

namespace Ostheneo\Toaster;


use Illuminate\Support\Facades\DB;
use Collective\Html\FormFacade as Form;

class BladeEngine {

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

    public static function JsIncludes($contents = null) {
        foreach ($contents as $content) {

        }

        return [];
    }

    public static function CssIncludes($contens = null) {
        return [];
    }

    public static function table($content) {

        $table = '<table class="table" id="' . $content->alias . '">';
        $columnDates = ['created_at', 'updated_at', 'deleted_at'];
        $header = '';
        $content = (object)$content;

        if (isset($content->model)) {
            $model = new $content->model;

            if (isset($content->schema)) {
                $columns = $model->schemas[$content->schema];
            } else {
                if (!isset($model->fields)) {
                    $columns = DB::select("describe $model->table");
                } else {
                    $columns = $model->fields;
                }
            }

            foreach ($columns as $key => $value) {
                $column = $value;
                if (is_array($value)) {
                    $column = $key;
                }

                if (!isset($columnDates[$column])) {
                    $header .= "<th>" . self::Translate($column) . "</th>";
                }

            }
        }

        $table .= "<thead>$header</thead>";


        return $table;

    }


    public static function makeItemTimeline() {
    }

    public static function makeScriptDatatable() {

    }

    public static function defineVars() {

    }


    public static function buildFields($content, $model) {
        $table = collect(DB::select('describe ' . $model->table));
        $construction = [];

        $fields = $model->fields;
        if (isset($content->schema)) {
            $fields = $model->schemas[$content->schema];
        }

        foreach ($fields as $key => $field) {
            $kindInput = '';
            if (!is_array($field)) {
                $details = $table->where('Field', $field)->first();
                $parameters = self::DetailsTableField($details->Type);
                $kindInput = self::dictionary($parameters->type);
            } else {
                $details = $table->where('Field', $key)->first();
                $parameters = self::DetailsTableField($details->Type);
                $field = (object)$field;
                $field->field = $key;
                $kindInput = $field->kind;
            }

            array_push($construction, self::buildHtmlField($field, $kindInput, $parameters));

        }

        return $construction;


    }

    static function buildHtmlField($field, $kindInput, $parameters) {
        /* para los que son fechas usa el mismo pero asignando diferentes clases para restringir el tipo de input*/
        if ($kindInput == 'date' or $kindInput == 'datetime' or $kindInput == 'time' or $kindInput == 'year') {
            $class = $kindInput;
            $kindInput = 'date';
        }

        $construct = new \stdClass();

        if (is_object($field)) {
            $initField = $field;
            $field = $field->field;
        }

        $construct->label = Form::label($field, self::Translate($field));

        switch ($kindInput) {

            case 'text':
                $construct->field = Form::text($field, null, ['class' => 'form-control']);
                return $construct;
                break;

            case 'textarea':
                $construct->field = Form::textarea($field, null, ['rows' => '3', 'class' => 'form-control']);
                return $construct;
                break;

            case 'password':
                $construct->field = Form::password($field);
                return $construct;
                break;

            case 'hidden':
                $construct->label = null;
                $construct->field = Form::hidden($field);
                return $construct;
                break;

            case 'number':
                $construct->field = Form::number($field, null, ['class' => 'form-control']);
                return $construct;
                break;

            case 'date':
                $construct->field = Form::text($field, null, ['class' => "$class"]);
                return $construct;
                break;

            case 'select':
                $construct->field = Form::text($field, null, ['class' => "form-control"]);
                return $construct;
                break;

            case 'checkbox':
                break;

            case 'tags':
                $construct->field = Form::text($field, null, ['class' => "form-control tags"]);
                return $construct;
                break;

            case 'radio':
                break;
            case 'file':
                break;
            case 'custom':
                $construct->label = null;
                $construct->field = null;
                $construct->include = $initField->include;
                return $construct;
                break;
        }
    }

    static function Translate($ask) {
        $dictionary = (object)[
            'name' => 'Nombre', 'category' => 'Categorias', 'vendor' => 'Proveedor', 'brief' => 'Descripcion corta', 'description' => 'Descripcion', 'variant details' => 'Detalles'
        ];


        if (isset($dictionary->$ask)) {
            return $dictionary->$ask;
        }

        $ask = str_replace('_', ' ', $ask);
        return ucwords($ask);

    }

    static function DetailsTableField($field) {
        $field = explode('(', $field);
        $values = '';
        if (sizeof($field) != 1) {
            $values = str_replace(')', '', $field[1]);
        }

        return (object)['type' => $field[0], 'values' => $values];
    }

    static function dictionary($ask) {
        $dictionaryValues = [
            'text'     => ['char', 'varchar', 'tinytext', 'binary', 'varbinary', 'tinyblob'],
            'textarea' => ['text', 'mediumtext', 'longtext', 'blob', 'mediumblob', 'longtext'],
            'number'   => ['bit', 'tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint',
                'decimal', 'dec', 'numeric', 'fixed', 'float', 'double', 'real', 'float', 'bool',
                'boolean'],
            'date'     => ['date'],
            'datetime' => ['datetime', 'timestamp'],
            'time'     => ['time'],
            'year'     => ['year']
        ];

        foreach ($dictionaryValues as $kind => $type) {
            $type = collect($type);

            if ($type->search($ask) !== false) {
                return $kind;
            }
        }

        return 'undefined';

    }


    static function makeTextField() {

    }


    public
    static function buildButtons($content) {
        $html = [];
        if (isset($content->buttons)) {
            foreach ($content->buttons as $position => $parameters) {
                $button = '';
                $parameters = (object)$parameters;

                if ($parameters->kind == 'link') {
                    $button = "<a href='" . route($parameters->route) . "'>$parameters->text</a>";
                }


                if ($parameters->kind == 'action') {
                    $button = "<a onclick='$parameters->function'>$parameters->text</a>";
                }

                if (!isset($html[$position])) {
                    $html[$position] = '';
                }


                $html[$position] .= $button;
            }
        }


        return $html;
    }


}