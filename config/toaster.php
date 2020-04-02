<?php
return [
    /*
    |-------------------------------------------------------------------------------------------------------------------
    |     ****************      opcione visuales para las plantillas del toaster ************************
    |-------------------------------------------------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | opcione de plantilla de Content
    |--------------------------------------------------------------------------
    |
    | En esta variable se especifica la plantilla para la vista de Content
    |
    */
    'template'=>'Toaster::Template.Layout',
    /*
    |--------------------------------------------------------------------------
    | seccion de encabezados
    |--------------------------------------------------------------------------
    |
    | En esta variable se especifica la seccion de encabezados donde se incluira la vista de Content
    |
    */
    'header'=>'header',
    /*
    |--------------------------------------------------------------------------
    | seccion de contenido
    |--------------------------------------------------------------------------
    |
    | En esta variable se especifica la seccion de contenido donde se incluira el contenido de  la vista de Content
    |
    */
    'content'=>'content',
    /**
     * configuraciones globales para los archivos subidos en el sistema
     */
    'fileUpload'=>[
        /**
         * prefijo para el nombre de los archivos subidos
         */
        'prefixName'=>'toaster-',
        /**
         * ruta predefinida donde se subiran todos los archivos
         */
        'prefixUrl'=>'public/files/',
        /**
         * ruta predefinida donde se subiran los Thumbnail
         */
        'prefixUrlThumbnail'=>'thumbnail/',
        /**
         * ancho maximo para la imagen
         */
        'widthMax'=>1080,
        /**
         * ancho maximo para el Thumbnail
         */
        'widthMaxThumbnail'=>350,
        /**
         * haltura maxima para la imagen
         */
        'heightMax'=>null,
        /**
         * haltura maxima para el Thumbnail
         */
        'heightMaxThumbnail'=>null,
    ],

];