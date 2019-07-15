# store
##Intalacion Toster

###Requicitos minimos

- illuminate/support: 5.6.*|5.7.*
- php: ^7.0

####Ejecutar comando
 `composer require ostheneo/toaster`
 
#### o agrega a tu composer.json lo siguiente
`"ostheneo/toaster": "dev-master"`
y ejecuta **composer update**

- luego ejecuta el siguiente comando para publicar todas las opciones de configuracion, personalizacion y demas que dispone el Toster. Estos son los archivos que se publicaran.
     
        `php artisan vendor:publish --provider=OsTheNeo\Toaster\ToasterServiceProvider`
    
    - resources/views/vendor/Toaster (vistas) 
     
            `php artisan vendor:publish --tag=views`
    - resources/lang (diccionrio para multiples lenguajes)
    
            `php artisan vendor:publish --tag=dictionary`
    - config/toaster.php (archivo de configuracion)
   
            `php artisan vendor:publish --tag=config`

- crear publicacion de rutas para el toster - para ser personalizadas

Tambien puedes publicar cada uno usando el comando que se espesifico anteriormente, debajo de estos.


#### Implementando Usuarios y permisos

Para el manejo de usuarios y permisos, deves implementar la libreria _Zizaco/entrust_


#####Crear migraciones

- Ejecuta el siguuiente comando para crear la migracion de la tabla **“galery”**

        `php artisan make:migration toaster_create_galery_table --create=galery`

- dentro de la funcion _“up”_, en la migracion creada, pega el siguiente codigo.
   
    `Schema::create('gallery', function (Blueprint $table) { 
           
        $table->increments('id');
        $table->text('icon');
        $table->text('images');
        $table->integer('state');
        $table->string('binded');
        $table->string('videos',100);
        $table->timestamps();
        
    });`
    
 Luego ejecuta php artisan migrate. Con esto se creara la tabla “gallery” en la base de datos.
 
 #####Crea la clase “Dictionary” con la siguiente estructura y los siguientes metodos.
 
 `public static function alias($ask) {
 
          $dictionary = (object)[
              'productTable'  => Product::class,
              'variantTable'  => Variant::class,
          ];
  
          return $dictionary->$ask;
      }
  
      public static function replacemente($ask) {
          /**estructura de datos para las tablas de purchase*/
          $purchaseTables=[
              'delivery_state' => ['kind' => 'group'],
              'payment_state'  => ['kind' => 'group'],
              'state'          => ['kind' => 'group'],
              'note'          => ['kind' => 'json','value' => 'datetime','splitData'=>'data:']];
  
          $replacement = (object)[
              'purchaseTable' =>$purchaseTables,
              'purchaseTableBogota' => $purchaseTables,
              'purchaseTableBogotaNorte' => $purchaseTables
          ];
          if (isset($replacement->$ask))
              return $replacement->$ask;
          return null;
      }
  
      public static function groupDefinitions($group){//'0'=>'Pendiente de pago'
          $groups = (object)([
              'size'           => ['0s' => 'Pequeño', '1s' => 'Mediano', '2s' => 'Grande'],
              'delivery_state' => ['Pendiente de envío', 'Enviado', 'Recibido', 'Devuelto cliente', 'Devuelto despachadora'],
              'payment_state'  => ['0'=>'......','1'=>'Aprobado','2'=>'Rechazada','3'=>'En verificación','4'=>'Fallida',
                  '5'=>'N/D','6'=>'Reversada','7'=>'Retenida','8'=>'Iniciada','9'=>'Exprirada',
                  '10'=>'Abandonada','11'=>'Cancelada','12'=>'Antifraude'],
              'state'          => ['Pendiente', 'Aprobada', 'Cancelado usuario', 'Cancelado administrador'],
              'cities'=>config('store.cities'),
              'states'=>['1'=>'Activado','0'=>'Desactivado'],
          ]);
          if (isset($groups->$group)) {
              return $groups->$group;
          }
          return null;
      }
      
       public static function groupCustomDefinitions($group,$parameters,$data){
          $item='Indefinido';
          switch ($parameters['from']){
              case 'DB':
                  if(isset($parameters['data']['where'])){
                      $key=$parameters['data']['where'];
                      $parameters['data']['where']=[$key=>$data['row']->$group];
                  }
                  $item=BladeEngine::makeOptions($parameters['data'])->toArray()[$data['value']];
                  break;
              case 'group':
                  $item=self::groupDefinitions($parameters['table'])[$group][$data['value']];
                  break;
  
          }
          return $item;
      }
      
      /**
       * extra un dato de un json
      */
      public static function jsonDefinitionValue($value,$key,$splitData=null){
          if($splitData!=null) $value=explode($splitData,$value)[1];
          $data=json_decode($value);
          return $data->$key;
      }`
      
  ###Implementacion o modo de uso
  
 Extiende del controlador “ToasterController”
  
  `class TuController extends ToasterController`
  
  impĺementar index
  
  implementar create
  
  implementar edit
  
  implementar show