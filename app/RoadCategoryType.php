<?php

namespace App;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Resizable;
use Cache;

class RoadCategoryType extends Model
{
    use Resizable;

    protected $table = 'road_category_types';

    public $fillable = [
        'id',
        'dir_road_category_id',
        'name',
        'color_rgb'
    ];

    public $timestamps = false;

    
    /**
     * Связь с категориями
     * @return mixed
     */
    public function categories()
    {
        return $this->hasMany('App\RoadCategory', 'dir_road_category_id', 'dir_road_category_id');
    }

}
