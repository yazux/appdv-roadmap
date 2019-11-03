<?php

namespace App\Classes;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Resizable;
use Cache;
use App\Classes\Reader;
use App\RoadCategory;

class RoadCategoryImporter
{
    private $fillable = ['begin_location', 'end_location', 'dir_road_category_id'];

    private $Reader = null;

    private $File = [];

    public function __construct(Reader $Reader)
    {
        $this->Reader = $Reader;
    }

    public function getCategories($RoadId)
    {
        return $this->Reader->read('RoadId=' . $RoadId . '_RoadCategories.csv', ['begin_location', 'end_location', 'dir_road_category_id'], 1);
    }
}
