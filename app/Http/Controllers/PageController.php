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
use App\Page;
use App\Http\Controllers\ServiceController;
use Illuminate\Mail\Markdown;

class PageController extends Controller
{
    public function __construct() 
    {
        //var_dump(session()->put('a', 'bbb'));
       //$visit = new Visit;
    }

    public function index(Request $request) 
    {
        return view('pages.home');
    }

    public function osm(Request $request) 
    {
        return view('pages.osm');
    }

    public function map(Request $request)
    {
        return view('pages.roads', []);
    }

    public function mapNew(Request $request)
    {
        return view('pages.map', []);
    }

    public function static(Request $request, $slug)
    {
        $Page = Page::where('slug', $slug)->first();
        if (!$Page) return abort(404);
        $Page->text = Markdown::parse($Page->text);
        return view('pages.static', ['page' => $Page]);
    }

    public function vendors(Request $request) 
    {
        return view('pages.vendors', []);
    }

    public function vendor(Request $request, $id = null)
    {
        if (!$id) return view('pages.vendor', ['vendor' => null]);
        else {
            $Vendor = Vendor::where('id', $id)->with('projects')->first();
            $Vendor->projects_count = $Vendor->ProjectsCount;
            $Vendor->projecs_in_works_count = $Vendor->ProjecsInWorksCount;
            $Vendor->full_price = $Vendor->FullPrice;
            return view('pages.vendor', ['vendor' => $Vendor]);
        }
    }

    public function projects(Request $request) 
    {
        $WorkProgress = ServiceController::getWorkProgress();

        $Vendors = Vendor::whereNotNull('id')->with('projects')->get();
        $Vendors->transform(function ($item) {
            $item->projects_count = $item->ProjectsCount;
            $item->projecs_in_works_count = $item->ProjecsInWorksCount;
            $item->full_price = $item->FullPrice;
            if (is_string($item->photos)) $item->photos = json_decode($item->photos);
            return $item;
        });


        return view('pages.projects', [
            'WorkProgress' => $WorkProgress,
            'vendors' => $Vendors,
        ]);
    }

    public function project(Request $request, $id = null) 
    {
        if (!$id) return view('pages.project', ['project' => null]);
        else {
            $Project = Project::where('id', $id)->with('vendor')->first();
            if ($Project->videos && strlen($Project->videos)) $Project->videos = explode(', ', $Project->videos);
            return view('pages.project', ['project' => $Project]);
        }
    }
}
