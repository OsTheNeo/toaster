<?php


namespace OsTheNeo\Toaster;

use Illuminate\Http\Request;
use OsTheNeo\Toaster\Models\Galleries;

/**
 * Class GalleryHelper
 * @package App\Helpers
 */
class GalleryHelper
{
    /**@var string $imageDefault - imagen por defecto*/
    protected static $imageDefault='public\img\set\empty.jpg';
    /**
     * Carga la carpeta base de la galeria
     */
    public static function loadFile($created_at){
        $created = explode(" ", $created_at);
        $created = explode('-', $created[0]);
        return "$created[0]/$created[1]/$created[2]";
    }
    /**
     * carga el path de la galeria
     * @param $created_at
     * @return string
     */
    public static function path($created_at) {
        return config('toaster.fileUpload.prefixUrl').self::loadFile($created_at);
    }

    /**
     * carga la primera imagen de una galeria
     * @param $id
     * @return string|null
     */
    public static function thumb($id) {
        $default=asset(self::$imageDefault);
        $gallery = Galleries::find($id);
        if ($gallery == null or $gallery->images == null) {
            return $default;
        }
        $path = self::path($gallery->created_at);

        $images = json_decode($gallery->images, true);

        if(sizeof($images) !== 0 and isset($images[0])){
            $gallery->images = str_replace('{','[',str_replace('}',']',$gallery->images));
            $gallery->save();
            $images = json_decode($gallery->images, true);
        }

        if (sizeof($images) !== 0 and isset($images[0])) {
            /**se verifica que la imagen exista, si no existe se retorna la imagen por defecto*/
            if(file_exists("$path/$images[0]")) return asset("$path/$images[0]");
            return $default;
        }
        return $default;
    }

    /**
     * carga el primer thumbnail de una galeria
     * @param $id
     * @return string
     * @throws \Throwable
     */
    public static function loadThumbnail($id) {
        $default=asset(self::$imageDefault);
        $gallery = Galleries::find($id);
        if ($gallery == null or $gallery->images == null) {
            return $default;
        }
        $path = self::path($gallery->created_at);
        $images = json_decode($gallery->images, true);

        if (sizeof($images) == 0 or !isset($images[0])) return $default;

        /**se verifica que el thumbnail exista, si no existe se crea*/
        $folder=self::loadFile($gallery->created_at);
        return self::loadOrCreatedThumbnail($images[0],$folder,$path);
    }

    /**
     * carga toda una galleria
     * @param null $id
     * @return array
     */
    public static function gallery($id = null) {
        if ($id == null) return [];
        /**@var Galleries $gallery*/
        $gallery = Galleries::find($id);

        if(empty($gallery)) return null;

        $images = json_decode($gallery->images, true);
        $path = self::path($gallery->created_at);
        return ['images' => $images?$images:[], 'path' => $path,'default'=>self::$imageDefault];
    }

    /**
     * carga toda una galleria
     * @param null $id
     * @return array
     * @throws \Throwable
     */
    public static function galleryThumbnail($id = null) {
        $images=[];
        if ($id == null) return $images;
        /**@var Galleries $gallery*/
        $gallery = Galleries::find($id);
        if(empty($gallery)) return null;
        $imagesList = json_decode($gallery->images, true);
        $path = self::path($gallery->created_at);
        foreach ($imagesList as $image){
            $images[]=self::loadOrCreatedThumbnail($image,self::loadFile($gallery->created_at),$path);
        }
        return ['images' => $images?$images:[], 'path' => $path,'default'=>self::$imageDefault];
    }

    /**
     * guarda y agrega una nuva imagen a la galeria
     * @param Request $request
     * @param $id
     * @return bool
     * @throws \Throwable
     */
    public static function storeGallery($request, $id) {
        $gallery = Galleries::find($id);
        $folder = self::loadFile($gallery->created_at);
        $images = json_decode($gallery->images, true);
        ini_set('memory_limit', '-1');
        $nameFiles=FilesHelper::storeDropzone($request,'files','g',$folder);
        ini_set('memory_limit', '-1');
        self::generateThumbnail($nameFiles[0],$folder,self::path($gallery->created_at));
        $images=array_merge($images?$images:[],$nameFiles);
        $gallery->images = json_encode($images, true);
        if(count($images)<=0) $gallery->images = null;
        $gallery->save();
        return $images;
    }

    /**
     * erifica que el thumbnail exista, si no existe se crea y se retorna
     * @param string $image
     * @param string $folder
     * @param string $path
     * @return string
     * @throws \Throwable
     */
    public static function loadOrCreatedThumbnail(string $image, string $folder, string $path){
        if(!file_exists("$path/$image")) return asset(self::$imageDefault);
        $thumbnail=config('toaster.fileUpload.prefixUrl').config('toaster.fileUpload.prefixUrlThumbnail')."$folder/$image";
        ini_set('memory_limit', '-1');
        if(!file_exists($thumbnail)) self::generateThumbnail($image,$folder,$path);
        if(!file_exists($thumbnail)) return asset("$path/$image");
        return asset($thumbnail);
    }

    /**
     * genera el thumbnail de una imagen
     * @param $fileName
     * @param $folder
     * @param $path
     * @return string
     * @throws \Throwable
     */
    public static function generateThumbnail($fileName, $folder,$path){
        $name=explode('.',$fileName);
        return FilesHelper::generateThumbnail("$path/$fileName",[
            'width'=>config('toaster.fileUpload.widthMaxThumbnail'),
            'height'=>config('toaster.fileUpload.heightMaxThumbnail'),
            'stricName'=>true,
            'name'=>$name[0],
            'ext'=>last($name),
            'dir'=>config('toaster.fileUpload.prefixUrlThumbnail').$folder
        ]);
    }

    /**
     * elimina una imagen de la galeria
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public static function deleteFromGallery($request, $id) {
        $images=[];
        $gallery = Galleries::find($id);
        if($gallery){
            $input=$request->all();
            $images = json_decode($gallery->images, true);
            foreach ($images as $key => $value) {
                if ($value == $input['file']) {
                    unset($images[$key]);
                    break;
                }
            }
            $gallery->images = json_encode($images, true);
            if(count($images)<=0) $gallery->images = null;
            $gallery->save();
            /**se elimina la imagen*/
            $image=self::loadFile($gallery->created_at).'/'.$input['file'];
            FilesHelper::destroy($image);
            /**se elimina la galeria*/
            $thumbnail=config('toaster.fileUpload.prefixUrlThumbnail').$image;
            FilesHelper::destroy($thumbnail);
        }
        return $images;
    }

    /**
     * elimina un registro de galeria, junto con todas las imagenes en el servidor, asciadas al registro
     * @param $id
     * @return bool
     * @throws \Exception
     */
    public static function deleteGallery($id){
        /**@var Galleries $gallery*/
        if(empty($gallery=Galleries::find($id))) return false;
        $images = json_decode($gallery->images, true);
        if($images)
            foreach ($images as $key => $value) {
                /**se elimina la imagen*/
                $image=self::loadFile($gallery->created_at).'/'.$value;
                FilesHelper::destroy($image);
                /**se elimina la galeria*/
                $thumbnail=config('toaster.fileUpload.prefixUrlThumbnail').$image;
                FilesHelper::destroy($thumbnail);
            }
        $gallery->delete();
        return true;
    }

}