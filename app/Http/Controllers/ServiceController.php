<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use App\User;
use App\Vendor;
use App\Project;

class ServiceController extends Controller
{
    public static function getWorkProgress($Vendors = []) {
        $result = [
            'all'      => 0,
            'complete' => 0,
            'work'     => 0,
            'price'    => 0
        ];
        if (!$Vendors || !count($Vendors)) $Vendors = Vendor::whereNotNull('id')->get();
        if ($Vendors && count($Vendors)) {
            foreach($Vendors as $item) {
                $result['all']      += (int) $item->ProjectsCount;
                $result['complete'] += (int) $item->ProjecsInWorksCount;
                $result['price']    += (int) $item->FullPrice;
            }
            $result['work'] = (int) $result['all'] - (int) $result['complete'];
        }
        return $result;
    }

}
