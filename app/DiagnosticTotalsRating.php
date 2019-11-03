<?php

namespace App;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Resizable;
use Cache;

class DiagnosticTotalsRating extends Model
{
    use Resizable;

    protected $table = 'diagnostic_totals_rating';

    public $fillable = [
        'id',
        'road_id',
        'begin_location',
        'end_location',
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

    public function tracks() 
    {
        return $this->belongsToMany('App\RoadBaseTrack', 'diagnostics_tracks', 'diagnostic_id', 'track_id');
    }

    public function lastTrack()
    {
        return $this->belongsToMany('App\RoadBaseTrack', 'diagnostics_tracks', 'diagnostic_id', 'track_id')->orderBy('picket', 'asc')->limit(1);
    }

    public function firstTrack()
    {
        return $this->belongsToMany('App\RoadBaseTrack', 'diagnostics_tracks', 'diagnostic_id', 'track_id')->orderBy('picket', 'DESC')->limit(1);
    }
}
