<?php

namespace App\Classes;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Resizable;
use Cache;
use App\Classes\Reader;
use App\Road;
use App\RoadBaseTrack;

class RoadImporter
{
    private $Reader = null;

    public function __construct(Reader $Reader)
    {
        $this->Reader = $Reader;
    }

    public function getRoadsInFile()
    {
        foreach ($this->Reader->getFiles() as $file) if ($file == 'RoadsList.csv') 
            return $this->Reader->read($file, ['id', 'name', 'full_name']);
    }

    public function addRoad($road = []) 
    {
        if (!$road['id'] || !$road['name'] || !$road['full_name']) return false;

        $Road = Road::where([
            'id'        => $road['id'],
            'name'      => $road['name'],
            'full_name' => $road['full_name']
        ])->first();

        if (!$Road) {
            $Road = Road::create($road);
            $Road->new = true;
        }

        return $Road;
    }

}
