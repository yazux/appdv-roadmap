<?php

namespace App\Classes;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Resizable;
use Cache;
use App\Classes\Reader;
use App\DiagnosticTotalsRating;
use App\CoversType;

class CoversTypeImporter
{
    private $fillable = ['pavement_layer_type_id', 'name', 'color_rgb'];

    private $Reader = null;

    private $File = [];

    public function __construct(Reader $Reader)
    {
        $this->Reader = $Reader;
    }

    public function getCoversTypes()
    {
        return $this->Reader->read('NamedObject_CoverTypes.csv', ['pavement_layer_type_id', 'name', 'color_rgb'], 1);
    }

    public function addCoversType($item = []) 
    {
        if (!$item['pavement_layer_type_id'] || !$item['name'] || !$item['color_rgb']) return false;

        $Item = CoversType::where([
            'pavement_layer_type_id' => $item['pavement_layer_type_id'],
            'name'      => $item['name'],
            'color_rgb' => $item['color_rgb']
        ])->first();

        if (!$Item) {
            $Item = CoversType::create($item);
            $Item->new = true;
        }

        return $Item;
    }

}
