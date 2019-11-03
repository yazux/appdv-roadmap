<?php

namespace App;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Resizable;
use Cache;

class Road extends Model
{
    use Resizable;

    protected $table = 'roads';

    public $fillable = [
        'id',
        'name',
        'full_name',
        'sort',
        'created_at',
        'updated_at'
    ];

    public $timestamps = true;

    /**
     * Связь с треками
     * @return mixed
     */
    public function tracks()
    {
        return $this->hasMany('App\RoadBaseTrack', 'road_id');
    }

    /**
     * Связь прямыми участками
     * @return mixed
     */
    public function curves()
    {
        return $this->hasMany('App\RoadPlanCurf', 'road_id');
    }
}
