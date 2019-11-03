<?php

namespace App;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Resizable;
use Cache;

class RoadPlanCurf extends Model
{
    use Resizable;

    protected $table = 'road_plan_curves';

    public $fillable = [
        'id',
        'road_id',
        'begin_location',
        'end_location'
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

}
