<?php

namespace App\Classes;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Resizable;
use Cache;
use App\Classes\Reader;
use App\RoadBaseTrack;
use App\RoadPlanCurf;

class PlanCurvesImporter
{
    private $Reader = null;

    private $File = [];

    public function __construct(Reader $Reader)
    {
        $this->Reader = $Reader;
    }

    public function getCurves($RoadId)
    {
        return $this->Reader->read('RoadId=' . $RoadId . '_PlanCurves.csv', ['begin_location', 'end_location'], 1);
    }

    public function addCurve($curve = [], $roadId = null) 
    {
        if (!$curve['begin_location'] || !$curve['end_location'] || !$roadId) return false;

        $Curve = RoadPlanCurf::where([
            'road_id' => $roadId,
            'begin_location' => $curve['begin_location'],
            'end_location' => $curve['end_location'],
        ])->first();
        
        if (!$Curve) {
            $curve = [
                'road_id'        => $roadId,
                'begin_location' => $curve['begin_location'],
                'end_location'   => $curve['end_location'],
            ];
            $Curve = RoadPlanCurf::create($curve);
        }
        return $Curve;
    }

}
