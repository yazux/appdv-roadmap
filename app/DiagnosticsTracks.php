<?php

namespace App;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Resizable;
use Cache;

class DiagnosticsTracks extends Model
{
    use Resizable;

    protected $table = 'diagnostics_tracks';

    public $fillable = [
        'id',
        'diagnostic_id',
        'track_id'
    ];

    public $timestamps = false;

    public function diagnostic()
    {
        return $this->belongsTo('App\DiagnosticTotalsRating', 'diagnostic_id', 'id');
    }

    public function tracks()
    {
        return $this->belongsTo('App\RoadBaseTrack', 'track_id', 'id');
    }
}
