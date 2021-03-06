<?php
/**
 * Created by PhpStorm.
 * User: OsTheNeo
 * Date: 15/11/2017
 * Time: 3:29 PM
 */

namespace OsTheNeo\Toaster;

use Illuminate\Support\Facades\Auth;
use App\Dictionary;
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

        $columnDates = ['created_at', 'updated_at', 'deleted_at'];
        $header = '';
        $content = (object)$content;

        if (isset($content->model)) {

            $model = $content->model;

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
                    $header .= "<th>" . self::Translate($column, $content->model) . "</th>";
                }

            }
        }


        return $header;

    }


    public static function makeItemTimeline() {
    }

    public static function makeScriptDatatable() {

    }

    public static function defineVars() {

    }


    public static function buildFields($content, $model) {
        $table = collect(DB::select('describe ' . $model->getTable()));
        $construction = [];

        $fields = $model->fields;
        if (isset($content->schema)) {
            $fields = $model->schemas[$content->schema];
        }

        foreach ($fields as $key => $field) {
            $kindInput = '';
            $options=[];
            if(isset($field['options']))
                $options=$field['options'];

            if (!is_array($field)) {
                $details = (object)$table->where('Field', $field)->first();
                $parameters = self::DetailsTableField($details->Type);
                $kindInput = self::dictionary($parameters->type);
            }elseif(isset($field['type'])){
                $kindInput=$field['type'];
                unset($field['type']);
                if (count($field)>0) $parameters =(object) $field;
                $field=$key;
            }else {
                $details = $table->where('Field', $key)->first();
                $parameters = self::DetailsTableField($details->Type);
                $field = (object)$field;
                $field->field = $key;
                $kindInput = isset($field->kind)?$field->kind:self::dictionary($parameters->type);
                if (isset($field->group)) {
                    $parameters->group = $field->group;
                }

            }
            array_push($construction, self::buildHtmlField($field, $kindInput, $parameters, $model,$options));
        }

        return $construction;


    }

    static function buildHtmlField($field, $kindInput, $parameters, $model,$options) {
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
        /**se cargan las opciones*/
        $options=[];
        if(isset($model->fields[$field]['options']))
            $options=$model->fields[$field]['options'];
        /**se determina si hay una previsualizacion*/
        $preview=null;
        if(isset($options['_preview'])){
            $preview=$options['_preview'];
            unset($options['_preview']);
        }

        if($kindInput=='h2'){
            $construct->label = null;
            $construct->field = null;
            $construct->include=$model->fields[$field]['title'];
            return $construct;
        }

        $construct->label = Form::label($field, self::Translate($field, $model), array_merge(['class' => 'uk-form-label uk-text-right'],$options));

        switch ($kindInput) {

            case 'text':
                $construct->field = Form::text($field, null, array_merge(['class' => 'uk-input' , 'rows'=>3],$options));
                return $construct;
                break;

            case 'textarea':
                $construct->field = Form::textarea($field, null, array_merge(['rows' => '3', 'class' => 'uk-textarea'],$options));
                return $construct;
                break;

            case 'color':
                $construct->field = Form::text($field, null, array_merge(['class'=>'uk-input jscolor'],$options));
                return $construct;
                break;

            case 'password':
                $construct->field = Form::password($field, array_merge(['class'=>'uk-input jscolor'],$options));
                return $construct;
                break;

            case 'hidden':
                $construct->label = null;
                $construct->field = Form::hidden($field);
                return $construct;
                break;

            case 'number':
                $construct->field = Form::number($field, null, array_merge(['class' => 'uk-input'],$options));
                return $construct;
                break;

            case 'float':
                $construct->field = Form::number($field, null, array_merge(['class' => 'uk-input',"step"=>"any"],$options));
                return $construct;
                break;

            case 'date':
                $construct->field = Form::date($field, null, array_merge(['class' => "$class uk-input"],$options));
                return $construct;
                break;

            case 'select':
                if (isset($parameters->group)) {
                    $data = Dictionary::groupDefinitions($parameters->group);
                } else {
                    $data = self::makeOptions((array)$parameters);
                }

                $construct->field = Form::select($field, $data, null, array_merge(['class' => 'uk-select'],$options));
                return $construct;
                break;

            case 'checkbox':
                $data = null;
                if (isset($parameters->group)) {
                    $data = Dictionary::groupDefinitions($parameters->group);  
                } 
                $construct->field = self::makeCheckbox($field, $parameters->group, $model, $data);
                
                return $construct;
                break;

            case 'tags':
                $construct->field = Form::text($field, null, array_merge(['class' => "form-control tags"],$options));
                return $construct;
                break;

            case 'radio':
                break;
            case 'file':
                if($preview and $model->$field){
                    $construct->preview=$preview['path'];
                    if(isset($preview['pathDynamic'])){
                        $pathDynamic=$preview['pathDynamic'];
                        $construct->preview.='/'.$model->$pathDynamic;
                    }
                    $construct->preview.='/'.$model->$field;
                }
                $construct->field = Form::file($field,array_merge(['class' => 'uk-input'],$options));
                return $construct;
            case 'color':
                $construct->field = Form::color($field, null, array_merge(['class' => 'uk-form-width-medium'],$options));
                return $construct;
                break;
            case 'custom':
                $construct->label = null;
                $construct->field = null;
                $construct->include = $initField->include;
                return $construct;
                break;
            case 'ckeditor':
                $construct->field = Form::textarea($field, null, array_merge(['rows' => '3', 'class' => 'ckeditor', 'id' => $field],$options));
                return $construct;
                break;
        }
    }

    /**
     * carga el label para el campo desde uno de los diccionarios de la aplicacion
     */
    static function Translate($ask, $model) {
        /*Se cargan los diccionarios*/
        $dictionary = (object)trans('toaster.dictionary');
        $dictionaryModel = (object)trans('toaster.' . $model->getTable());
        /*se toma el label de uno de los diccionarios*/
        if (isset($dictionaryModel->$ask)) {/*si existe el label en el diccionario del modelo se toma de este*/
            return $dictionaryModel->$ask;
        } elseif (isset($dictionary->$ask)) {/*si no existe se busca en el diccionario general*/
            return $dictionary->$ask;
        }
        /*si no se envia el nombre del campo de la BD*/
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
                'numeric', 'fixed', 'bool','boolean'],
            'float'=>['decimal', 'dec','float', 'double', 'real'],
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


    public static function makeTextField() {

    }

    /**
     * crea los campos checkbox y los carga en el formulario
     * @param string $construct
     * @return string
     */
    public static function makeCheckbox($field, $group, $model, $dictionary = null) {
        $data = [];
        if ($model->$field != null) {
            $data = explode(',', $model->$field);
        }
        $options = self::makeOptions($group);
        $checks = "";
        if ($dictionary != null) {
            $options = $dictionary;
        }
        
        foreach ($options as $key => $item) {
            $checks .= '<div class="uk-form-controls-text"><label>'
                . Form::checkbox($field . '[]', $key, in_array($key, $data), ['id' => $field . $key, 'class' => 'uk-checkbox uk-margin-small-right'])
                . $item . '</label></div>';
        }
        return $checks;
    }

    static function makeOptions($options) {

        if (!isset($options['from'])) {
            return $options;
        } else {
            $take = $options['take'][1];
            $id = $options['take'][0];
            $temp = DB::table($options['from'])->select(DB::raw("CONCAT($take) as value, $id"));
            /**aplica condiciones*/
            if (isset($options['where'])) {
                foreach ($options['where'] as $key => $value) {
                    if (is_array($value)) {
                        switch ($value[0]) {
                            case 'whereIn':
                                $temp->whereIn($value[1]);
                                break;
                            case 'whereNotIn':
                                $temp->whereNotIn($value[0]);
                                break;
                            case 'whereNull':
                                $temp->whereNull($value[1]);
                                break;
                            case 'whewreNotNull':
                                $temp->whereNotNull($value[1]);
                                break;
                            default:
                                $temp->where($value[0], $value[1], $value[2]);
                                break;
                        }
                    } else {
                        $temp->where($key, $value);
                    }
                }
            }
            /**aplica ordenamientos*/
            if (isset($options['order'])) {
                $by = $options['order'][0];
                $order = isset($options['order'][1]) ? $options['order'][1] : 'ASC';
                $temp->orderBy($by, $order);
            }
            return $temp->pluck('value', $id);
        }
    }

    public static function buildButtons($content) {
        $html = [];
        if (isset($content->buttons)) {
            foreach ($content->buttons as $position => $parameters) {
                $button = '';
                $parameters = (object)$parameters;

                if ($parameters->kind == 'link') {
                    if (isset($parameters->parameters)) {
                        $button = "<a href='" . route($parameters->route, $parameters->parameters) . "' class='uk-button uk-button-default uk-button-small'>$parameters->text</a>";
                    } else {
                        $button = "<a href='" . route($parameters->route) . "' class='uk-button uk-button-default uk-button-small'>$parameters->text</a>";
                    }
                }

                if ($parameters->kind == 'action') {
                    $button = "<a class='uk-button uk-button-default uk-button-small' onclick='$parameters->function'>$parameters->text</a>";
                }

                if (!isset($html[$position])) {
                    $html[$position] = '';
                }

                if (isset($parameters->permission) and ! Auth::user()->can($parameters->permission)) {
                    $button = '';
                }
                $html[$position] .= $button;
            }
        }


        return $html;
    }

    public static function buildLinks($model, $alias, $row) {
        $links = "<div class='uk-button-group'>";
        foreach ($model->links[$alias] as $link) {
            if (sizeof($link) >= 3) {
                $parameters = [];
                foreach (collect($link[2]) as $parameter) {
                    if ($parameter[0] == '_') {
                        $parameters[] = substr($parameter, 1);
                    } else {
                        array_push($parameters, $row->$parameter);
                    }
                }
                if(isset($link[3])) {
                    if (($link[3]=='delete' or $link[3]=='destroy')) {
                        $links.=Form::open(['route'=>[$link[1],$parameters[0]],'method' => 'delete'])
                        ."<input type='submit' value='$link[0]' class='uk-button uk-button-default uk-button-small' onclick='return confirm(\"¿Desea eliminar el registro?\")'/>"
                        .Form::close();
                    }
                    else {
                        if (Auth::user()->can($link[3])) {
                            $links .= "<a class='uk-button uk-button-default uk-button-small' href='" . route($link[1], $parameters) . "'>" . $link[0] . "</a>";
                        }
                    }
                }else{
                    $links .= "<a class='uk-button uk-button-default uk-button-small' href='" . route($link[1], $parameters) . "'>" . $link[0] . "</a>";
                }
            } else {
                $links .= "<a class='uk-button uk-button-default uk-button-small' href='route($link[1])'>$link[0]</a>";
            }

        }
        $links .= '</div>';
        return $links;
    }

    public static function buildGallery() {

    }

    public static function formatPrice($price)
    {
        $price = explode('.', $price);
        $price = str_replace(',', '', $price[0]);
        return number_format(round($price), 0, ',', '.');
    }

}