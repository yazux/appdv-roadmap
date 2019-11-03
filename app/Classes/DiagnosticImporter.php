<?php

namespace App\Classes;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Resizable;
use Cache;
use App\Classes\Reader;
use App\DiagnosticTotalsRating;

class DiagnosticImporter
{
    private $fillable = ['begin_location', 'end_location', 'is_acceptable'];

    private $Reader = null;

    private $File = [];

    public function __construct(Reader $Reader)
    {
        $this->Reader = $Reader;
    }

    public function getDiagnostic($RoadId)
    {
        return $this->Reader->read('RoadId=' . $RoadId . '_DiagnosticTotalsRating.csv', ['begin_location', 'end_location', 'is_acceptable'], 1);
    }

    public function addDiagnosticItem($item = [], $roadId = null) 
    {
        if (!$item['begin_location'] || !$item['end_location'] || !$item['is_acceptable']) return false;

        $Item = DiagnosticTotalsRating::where([
            'begin_location' => $item['end_location'],
            'end_location'   => $item['end_location'],
            'is_acceptable'  => $item['is_acceptable'],
            'road_id'        => $roadId
        ])->first();

        if (!$Item) {
            if ($roadId) $item['road_id'] = $roadId;
            $Item = DiagnosticTotalsRating::create($item);
            $Item->new = true;
        }

        return $Item;
    }

}
