<?php
	namespace OsTheNeo\Toaster\Models;
	
	use Eloquent as Model;
	use Illuminate\Database\Eloquent\SoftDeletes;
	
	/**
	 * Class Store
	 * @package App\Models\Store
	 * @version March 10, 2017, 4:28 pm UTC
	 */
    class Gallery extends Model {
        protected $table = 'gallery';

        use SoftDeletes;
        const CREATED_AT = 'created_at';
        const UPDATED_AT = 'updated_at';

        protected $dates = ['deleted_at'];

        public $fillable = ['icon',
            'images',
            'state',
            'binded',
            'videos', 'created_at'];

        public static $rules = [];
    }