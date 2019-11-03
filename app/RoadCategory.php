<?php

namespace App;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Resizable;
use Cache;

class RoadCategory extends Model
{
    use Resizable;

    protected $table = 'road_categories';

    public $fillable = [
        'id',
        'road_id',
        'begin_location',
        'end_location',
        'dir_road_category_id'
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
     * Связь с дорогой
     * @return mixed
     */
    public function categoryType()
    {
        return $this->belongsTo('App\RoadCategoryType', 'dir_road_category_id', 'dir_road_category_id');
    }

}
