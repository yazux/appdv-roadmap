<?php

namespace App\Classes;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Resizable;
use Cache;
use App\Classes\Reader;
use App\RoadBaseTrack;

class RoadTrackImporter
{
    private $fillable = ['picket', 'latitude', 'longitude', 'h'];

    private $Reader = null;

    private $File = [];

    public function __construct(Reader $Reader)
    {
        $this->Reader = $Reader;
    }

    public function getTracks($RoadId)
    {
        return $this->Reader->read('RoadId=' . $RoadId . '_BaseTrack.csv', ['picket', 'latitude', 'longitude', 'h'], 2);
    }

    public function addTrack($track = [], $roadId = null) 
    {
        if (!$track['picket'] || !$track['latitude'] || !$track['longitude'] || !$track['h']) return false;

        $Track = RoadBaseTrack::where([
            'picket'  => $track['picket'],
            'road_id' => $roadId
        ])->first();

        if (!$Track) {
            if ($roadId) $track['road_id'] = $roadId;
            $Track = RoadBaseTrack::create($track);
            $Track->new = true;
        }

        return $Track;
    }

}
