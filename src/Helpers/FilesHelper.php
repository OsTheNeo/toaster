<?php

namespace OsTheNeo\Toaster;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

/**
 * Class FilesHelper
 * @package App\Helpers
 */
class FilesHelper
{
    /**
     * @var
     */
    protected $data;
    /**
     * @var
     */
    protected $objectModel;
    /**
     * @var
     */
    protected $nameFile;
    /**
     * @var
     */
    protected $nameLeaf;
    /**
     * guarda los archivos en el servidor
     * @param Request $request
     * @param string $fileName - Nombre del campo en el html
     * @param string $fName - nombre que ba a tener el archivo
     * @param string $dir - nombre de la carpeta destino ejemplo (nombreCarpeta)
     * @param string $typeFile - tipo de archivo por defecto es image
     * @param mixed|null $resize - arraglo con los parametros para redimencionar una imagen
     * la estructura del arreglo es la siguiente ['width'=>"Ancho maximo",'height'=>"altura maxima",'ext'=>"extencioncon la que se guarda la imagen"]
     * si se envia un true se redimencionara a las dimenciones por defecto establecidas, si no se espesifica nada el valor sera false y no se
     * redimencionara la imagen
     * @param bool $stricName - indica si se usa el nombre que se envia o se genera el predeterminado usando el nombre enviado  |
     * @return string - nombre con el que queda la imagen
     * @throws \Throwable
     */
    public static function store(Request $request,$fileName,$fName,$dir,$typeFile='image',$resize=null,$stricName=false)
    {
        if($request->file($fileName)){
            $file=$request->file($fileName);
            return self::processFile($file,$fName,$dir,$typeFile,$resize,$stricName);
        }else{
            return "";
        }
    }

    /**
     * procesa y guarda el archivo indicado
     * @param string $file - archivo que se prosesara
     * @param string $fName - nombre que ba a tener el archivo
     * @param string $dir - nombre de la carpeta destino ejemplo (nombreCarpeta)
     * @param string $typeFile - tipo de archivo por defecto es image
     * @param mixed|null $resize - arraglo con los parametros para redimencionar una imagen
     * la estructura del arreglo es la siguiente ['width'=>"Ancho maximo",'height'=>"altura maxima",'ext'=>"extencioncon la que se guarda la imagen"]
     * si se envia un true se redimencionara a las dimenciones por defecto establecidas, si no se espesifica nada el valor sera false y no se
     * redimencionara la imagen
     * @param bool $stricName - indica si se usa el nombre que se envia o se genera el predeterminado usando el nombre enviado  |
     * @return string - nombre con el que queda la imagen
     * @throws \Throwable
     */
    public static function processFile($file,$fName,$dir,$typeFile='image',$resize=null,$stricName=false){
        $ext=$file->getClientOriginalExtension();
        if('image'==$typeFile and $resize!=false){
            if(is_array($resize)){
                $width=$resize['width'];
                $height=$resize['height'];
                $ext=$resize['ext'];
            }else{
                $width=config('laperla.fileUpload')['widthMax'];
                $height=config('laperla.fileUpload')['heightMax'];
            }
            $file=self::resizeImage($file,$width,$height);
        }
        return self::save($file,$fName,$ext,$dir,$stricName);
    }

    /**
     * guarda la imagen o archivo espesificado en la ruta indicada
     * @param $file - archivo o imagen
     * @param $fName - nombre que se usara para renombrar la imagen o archivo
     * @param $ext - extencion de la imagen o archivo
     * @param $dir - directorio donde se guardar la imagen o archivo
     * si este parametro no se indica se establecera como null, el formato del arreglo es el siguiente ['widthMax'=>valor,'heightMax'=>valor]
     * @param bool $stricName - indica si se usa un el nombre pasado
     * @return string - nombre final de la imagen o archivo
     */
    public static function save($file,$fName,$ext,$dir,$stricName=false){
        /**se establece el nombre para el archivo, si no se indica que se use el nombre estricto se genera con el formato predeterminado */
        if($stricName) $name=self::clean($fName).'.'.$ext;
        else $name=self::formatName(config('laperla.fileUpload')['prefixName'].$fName).'.'.$ext;
        /**se establece la ruta de guardado*/
        $path=public_path().'/'.config('laperla.fileUpload')['prefixUrl'].$dir.'/';
        /**se cuimprueba si existe el directorio de lo contrario se crea */
        if (!file_exists($path)) mkdir($path, 0777, true);
        /**determina el metodo que se usara para el guardado del archivo*/
        if(method_exists($file,'getClientOriginalExtension')):

            /**se guarda con el metodo por defecto para cualquier tipo de archivo*/
            $file->move($path,$name);
        else:
            /**se guarda con el metodo de guardado para imagenes de Intervention*/
            $file->save($path.$name);
        endif;
        return $name;
    }
    /**
     * guarda los archivos en el servidor
     * @param Request $request
     * @param string $fileName - Nombre del campo en el html
     * @param string $fName - nombre que ba a tener el archivo
     * @param string $dir - nombre de la carpeta destino ejemplo (nombreCarpeta)
     * @return string - nombre con el que queda la imagen
     */
    ///Storage::put('/firmas/' . $path . $input['signature'], gzcompress($signature, 9));
    /**
     * @param Request $request
     * @param string $fileName
     * @param string $fName
     * @param string $dir
     * @return string
     */
    public static function storeCompress(Request $request, $fileName, $fName, $dir)
    {
        if($request->file($fileName)){
            $file=$request->file($fileName);
            $name=self::formatName(config('laperla.fileUpload')['prefixName'].$fName);
            $path=public_path().'/'.config('laperla.fileUpload')['prefixUrl'].$dir.'/';
            Storage::put($path,gzuncompress($name));
            return $name;
        }else{
            return "";
        }
    }

    /**
     * Guarda multiples archivos que son enviado a la vez atravez de un arreglo al servidor
     * @param Request $request
     * @param string $fileName - Nombre del campo en el html
     * @param string $fName - nombre que ba a tener el archivo
     * @param string $dir - nombre de la carpeta destino ejemplo (nombreCarpeta)
     * @param string $typeFile - tipo de archivo por defecto es image
     * @param mixed|null $resize - arraglo con los parametros para redimencionar una imagen
     * la estructura del arreglo es la siguiente ['width'=>"Ancho maximo",'height'=>"altura maxima",'ext'=>"extencioncon la que se guarda la imagen"]
     * si se envia un true se redimencionara a las dimenciones por defecto establecidas, si no se espesifica nada el valor sera false y no se
     * redimencionara la imagen
     * @param bool $stricName - indica si se usa el nombre que se envia o se genera el predeterminado usando el nombre enviado  |
     * @return array - nombres con los que quedas las imagenes
     */
    public static function storeDropzone(Request $request,$fileName,$fName,$dir,$typeFile='image',$resize=true,$stricName=false)
    {
        if($files=$request->file($fileName)){
            /**@var array $names arreglo cn los nombres de los archivos subidos*/
            $names=[];
            foreach ($files as $file){
                $ext=$file->getClientOriginalExtension();
                if('image'==$typeFile and $resize!=false){
                    if(is_array($resize)){
                        $width=$resize['width'];
                        $height=$resize['height'];
                        $ext=$resize['ext'];
                    }else{
                        $width=config('laperla.fileUpload')['widthMax'];
                        $height=config('laperla.fileUpload')['heightMax'];
                    }
                    $file=self::resizeImage($file,$width,$height);
                }
                $names[]=self::save($file,$fName,$ext,$dir,$stricName);
            }
            return $names;
        }else{
            return [];
        }
    }

    /**
     * se genera thumbnail de la imagen indicada
     * @param string $filePath
     * @param array $options [width,height,name,height,ext,dir]
     * @return string
     * @throws \Throwable
     */
    public static function generateThumbnail(string $filePath, array $options){
            $strictName=isset($options['stricName'])?$options['stricName']:false;
            $thumbnail=self::resizeImage($filePath,$options['width'],$options['height']);
            return self::save($thumbnail,$options['name'],$options['ext'],$options['dir'],$strictName);
    }
    /**
     * controla si hay un cambio del archivo de imagen, si se esta subiendo una nueva imagen
     * se borrar la existente y se copea la nueva imagen en el servidor
     * @param Request $request
     * @param string $fileName - Nombre del campo en el html
     * @param string $fName - nombre que ba a tener el archivo
     * @param string $dir - nobre de la carpeta en el servidor ejemplo (nombreCarpeta)
     * @param string $oldFileName - nombre actual de la imagen en el servidor (nombreImagen.extencion)
     * @return string - nombre de la imagen
     */
    public static function update(Request $request,$fileName,$fName,$dir,$oldFileName,$typeFile='image',$resize=null,$stricName=false)
    {
        if($request->file($fileName)){
            $file=$request->file($fileName);
            $name=$file->getClientOriginalName().'.'.$file->getClientOriginalExtension();
            //se valida si el archivo es el mismo
            if($name != $oldFileName){
                //si el archivo no es el mismo se procede a borrar el viejo y copear el nuevo archivo al servidor
                if($oldFileName) self::destroy($dir.'/'.$oldFileName);
                return self::store($request,$fileName,$fName,$dir,$typeFile,$resize,$stricName);
            }else{
                return $oldFileName;
            }
        }else{
            return $oldFileName;
        }
    }
    /**
     * formatea el nombre que se le asignara al archivo
     * @param $name - nombre del archivo
     * @return string nombre formateado
     */
    public static function formatName($name){
        return self::clean($name)."-".date_timestamp_get(date_create()).rand();
    }
    /**
     * limpia el nombre de caracteres erroneos y espacios
     * @param $name - nombre del archivo
     * @return string nombre limpiado
     */
    public static function clean($name){
        $string = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $name)));
        return preg_replace('/-+/', '-', $string);
    }
    /**
     * redimenciona una imagen al tamaño espesificado
     * @param $image - imagen que va a ser convertida
     * @param $width - ancho
     * @param $height - halto
     * @return retorna la imagen
     * @throws \Throwable
     */
    public static function resizeImage($image,$width, $height){
        $img=Image::make($image);
        $img=$img->resize($width, $height,function ($constraint) {
            $constraint->aspectRatio();
        });
        return $img;
    }

    /**
     * convierte una imagen de base64 a jpg
     * @param $base64String - imagen en base64
     * @return Image - imagen convertida a jpg
     */
    public static function base64ToJpg($base64String){
        $img=Image::make($base64String)->encode('jpg',75);
        return $img;
    }
    /**
     *elimina el archivo indicado del servidor
     * @param string $dirAndName - nombre del archivo y la carpeta donde se encuentra ejemplo (nombreDirectorio/archivo.extencion)
     */
    public static function destroy($dirAndName)
    {
       if(file_exists(public_path().'/'.config('laperla.fileUpload')['prefixUrl'].$dirAndName)) unlink(public_path().'/'.config('laperla.fileUpload')['prefixUrl'].$dirAndName);
    }
}
