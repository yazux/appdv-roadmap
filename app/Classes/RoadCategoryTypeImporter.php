<?php

namespace App\Classes;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Resizable;
use Cache;
use App\Classes\Reader;
use App\DiagnosticTotalsRating;
use App\RoadCategoryType;

class RoadCategoryTypeImporter
{
    private $fillable = ['dir_road_category_id', 'name', 'color_rgb'];

    private $Reader = null;

    private $File = [];

    public function __construct(Reader $Reader)
    {
        $this->Reader = $Reader;
    }

    public function getRoadCategoryTypes()
    {
        return $this->Reader->read('NamedObject_RoadCategoryTypes.csv', ['dir_road_category_id', 'name', 'color_rgb'], 1);
    }

    /**
     * Конвертирует rgb цвет типа 0x69,0x69,0x69 в HEX
     */
    public function convertColor($color)
    {
        if (!$color) return '#dfdfdf';
        $colors = explode(',', $color);
        
        foreach($colors as &$item) {
            $item = substr($item, 2);
            if ($item == 0) $item = '00';
        } unset($item);

        return '#' . implode('', $colors);
    }

    public function addRoadCategoryType($item = [])
    {
        if (!$item['dir_road_category_id'] || !$item['name'] || !$item['color_rgb']) return false;
        //$item['color_rgb'] = $this->convertColor($item['color_rgb']);
        $Item = RoadCategoryType::where([
            'dir_road_category_id' => $item['dir_road_category_id'],
            'name'      => $item['name']
        ])->first();

        if (!$Item) $Item = RoadCategoryType::create($item);
        else $Item->update($item);

        return $Item;
    }

}
