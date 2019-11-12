<?php

namespace App\Classes;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Resizable;
use Cache;

class Reader
{
    private $files = null;

    private $importPath = '';

    public function __construct()
    {
        $this->importPath = storage_path() . '/app/public/import/';
        $this->files = scandir($this->importPath, 1);
    }

    public function getFiles() 
    {
        return $this->files;
    }

   public function read($path, $fields, $slice = 1)
   {
        $result = [];
        $row = 1;
        if (!file_exists($this->importPath . '/' . $path)) return $result;
        
        $content = file_get_contents($this->importPath . '/' . $path);
        $items = explode(PHP_EOL, $content);
        $items = array_slice($items, $slice);

        if (!$items || !count($items)) return $result;
        
        foreach ($items as &$item) {
            $item = explode(';', $item);
            if ($item && count($item) == count($fields)) {
                $resultItem = [];
                foreach ($fields as $i => $field) $resultItem[$field] = $item[$i];
                $result[] = $resultItem;
            }
        } unset($item);
 
        return $result;
   }

}
