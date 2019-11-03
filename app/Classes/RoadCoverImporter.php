<?php

namespace App\Classes;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Resizable;
use Cache;
use App\Classes\Reader;
use App\DiagnosticTotalsRating;
use App\CoversType;

class RoadCoverImporter
{
    private $fillable = ['begin_location', 'end_location', 'pavement_layer_type_id'];

    private $Reader = null;

    private $File = [];

    public function __construct(Reader $Reader)
    {
        $this->Reader = $Reader;
    }

    public function getCovers($RoadId)
    {
        return $this->Reader->read('RoadId=' . $RoadId . '_RoadCovers.csv', ['begin_location', 'end_location', 'pavement_layer_type_id'], 1);
    }
}
