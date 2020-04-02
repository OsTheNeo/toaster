<?php

namespace OsTheNeo\Toaster\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Galleries
 * @package App\Models\Data
 *
 * @property string $binded
 * @property string $images
 * @property string $moments
 * @property Carbon $created_at
 */
class Galleries extends Model
{
    use SoftDeletes;
    /**
     * @var string
     */
    protected $table="galleries";
    /**
     * @var array
     */
    protected $fillable=['binded','images','moments'];
}
