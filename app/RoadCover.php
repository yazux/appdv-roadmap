<?php

namespace App;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Resizable;
use Cache;

class RoadCover extends Model
{
    use Resizable;

    protected $table = 'road_covers';

    public $fillable = [
        'id',
        'road_id',
        'begin_location',
        'end_location',
        'pavement_layer_type_id'
    ];

    public $timestamps = false;

    
    /**
     * Связь с дорогой
     * @return mixed
     */
    public function road()
    {
        return $this->belongsTo('App\Road', 'road_id');
    }

    /**
     * Связь с типом покрытия
     * @return mixed
     */
    public function coverType()
    {
        return $this->belongsTo('App\CoversType', 'pavement_layer_type_id', 'pavement_layer_type_id');
    }

}
