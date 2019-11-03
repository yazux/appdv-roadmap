<?php

namespace App;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Resizable;
use Cache;

class CoversType extends Model
{
    use Resizable;

    protected $table = 'covers_types';

    public $fillable = [
        'id',
        'pavement_layer_type_id',
        'name',
        'color_rgb'
    ];

    public $timestamps = false;

    /**
     * Связь с покрытиями
     * @return mixed
     */
    public function covers()
    {
        return $this->hasMany('App\RoadCover', 'pavement_layer_type_id', 'pavement_layer_type_id');
    }
}
