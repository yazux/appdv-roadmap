<?php

namespace App;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Resizable;
use Cache;

class RoadBaseTrack extends Model
{
    use Resizable;

    protected $table = 'road_base_tracks';

    public $fillable = [
        'id',
        'picket',
        'latitude',
        'longitude',
        'h',
        'road_id',
        'track_index',
        'is_acceptable'
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

    public function diagnostic() 
    {
        return $this->belongsToMany('App\DiagnosticTotalsRating', 'diagnostics_tracks', 'track_id', 'diagnostic_id');
    }

}
