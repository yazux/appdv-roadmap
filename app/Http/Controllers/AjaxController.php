<?php

namespace App\Http\Controllers;

use App\Vendor;
use App\Project;
use App\News;
use App\Connectors\BitrixConnector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Cache;
use Voyager;
use App\DiagnosticTotalsRating;
use App\Classes\Reader;
use App\Classes\RoadTrackImporter;
use App\Classes\PlanCurvesImporter;
use App\Classes\DiagnosticImporter;
use App\Classes\RoadCategoryImporter;
use App\Classes\RoadCoverImporter;
use App\Road;
use App\RoadBaseTrack;
use App\RoadPlanCurf;
use App\CoversType;
use App\RoadCover;
use App\RoadCategoryType;
use App\RoadCategory;
use App\RoadMessage;
use DB;

/**
 * Контроллер для работы с Ajax запросами
 *
 * @package App\Http\Controllers
 */
class AjaxController extends Controller
{
    public function getVendors(Request $request)
    {
        if ($request->get('count')) $Vendors = Vendor::whereNotNull('id')->paginate($request->get('count'));
        else $Vendors = Vendor::whereNotNull('id')->get();

        if ($Vendors instanceof LengthAwarePaginator) {
            $Vendors->getCollection()
                ->transform(function ($item) {
                    $item->projects_count = $item->ProjectsCount;
                    $item->projecs_in_works_count = $item->ProjecsInWorksCount;
                    $item->full_price = $item->FullPrice;
                    if (is_string($item->photos)) $item->photos = json_decode($item->photos);
                    return $item;
                });
        } elseif ($Vendors instanceof Collection) {
            $Vendors->transform(function ($item) {
                $item->projects_count = $item->ProjectsCount;
                $item->projecs_in_works_count = $item->ProjecsInWorksCount;
                $item->full_price = $item->FullPrice;
                if (is_string($item->photos)) $item->photos = json_decode($item->photos);
                return $item;
            });
        }
        return response()->json($Vendors);
    }

    public function getProjects(Request $request)
    {
        $vendorId = $request->get('vendor_id');

        $Projects = Project::whereNotNull('id');
        if ($vendorId) $Projects->where('vendor_id', $vendorId);
        if ($request->get('count')) $Projects = $Projects->paginate($request->get('count'));
        else $Projects = $Projects->get();

        return response()->json($Projects);
    }

    public function getNews(Request $request)
    {
        if ($request->get('count')) $News = News::whereNotNull('id')->orderBy('created_at', 'desc')->paginate($request->get('count'));
        else $News = News::whereNotNull('id')->get();

        return response()->json($News);
    }

    public function access(Request $request)
    {
        $access = false;
        $password = $request->get('password');
        if ($password == Voyager::setting('site.password', '')) $access = true;
        return response()->json(['access' => $access]);
    }





    public function getRoadMessages(Request $request) 
    {
        $Message = RoadMessage::whereNotNull('id')->with(['road' => function($q) { $q->with('curves'); }])->get();
        $zoom = $request->get('zoom');
        if (!$zoom) $zoom = 15;
        $ZoomSteps = [
            '7'  => '3000',
            '8'  => '800',
            '9'  => '500',            
            '10' => '250',
            '11' => '200',
            '12' => '150',
            '13' => '100',
            '14' => '70',
            '15' => '10'
        ];
        $step = ($zoom && isset($ZoomSteps[$zoom]) ? $ZoomSteps[$zoom] : 3000);

        $Message->transform(function($Message) use ($step) {
            $Tracks = RoadBaseTrack::where('road_id', $Message->road_id)
                ->whereRaw( "CRC32(picket) % " . $step .  " = 0" )
                ->whereBetween('picket', [$Message['begin_location'], $Message['end_location']])
                ->where(function ($q) use ($Message) {
                    foreach ($Message->road->curves as $i => $curve) {
                        if (!$i) $q->whereBetween('picket', [$curve['begin_location'], $curve['end_location']]);
                        else $q->orWhereBetween('picket', [$curve['begin_location'], $curve['end_location']]);
                    }
                })->orderBy('picket', 'ASC')->get()->toArray();
                $First = RoadBaseTrack::where('picket', $Message['begin_location'])->where('road_id', $Message->road->id)->limit(1)->get()->toArray();
                $Last  = RoadBaseTrack::where('picket', $Message['end_location'])->where('road_id', $Message->road->id)->limit(1)->get()->toArray();

            $ResultTracks = array_merge($First, $Tracks, $Last);
               
            $Tracks = [];
            foreach ($ResultTracks as $Track) if (isset($Track['latitude']) && isset($Track['longitude'])) $Tracks[] = ['latitude'  => $Track['latitude'], 'longitude' => $Track['longitude']];            
            $Message->tracks = $Tracks;
            unset($Message->road->curves);
            return $Message;
        });

        return response()->json($Message);
    }


    public function getTracks(Request $request) 
    {
        //$key = 'test_roads_' . $request->get('zoom');
        $key = 'test_roads_19';

        $zoom = $request->get('zoom');
        if (!$zoom) $zoom = 19;

        $file = base_path('/storage/app/public/JSON/tracks_' . $zoom . '.json');
        $file = file_get_contents($file);
        $Data = json_decode($file, true);

        $Roads = [];
        //if (Cache::has($key)) {
        if ($Data) {
            //$RoadsCache = Cache::get($key);
            //$RoadsCache = $file;
            $coords = $request->get('coords');
            if (!is_array($coords)) $coords = json_decode($coords, true);
            $latitude = $coords['latitude'];
            $longitude = $coords['longitude'];

            foreach ($Data as $RoadCache) {
                $Tracks = [];
                $Road = [
                    'name'   => $RoadCache['name'],
                    'tracks' => [],  
                ];
                foreach ($RoadCache['tracks'] as $Track) {
                    if (
                        (float) $Track['latitude']  >= (float) $latitude['from']  &&
                        (float) $Track['latitude']  <= (float) $latitude['to']    &&
                        (float) $Track['longitude'] >= (float) $longitude['from'] &&
                        (float) $Track['longitude'] <= (float) $longitude['to']
                    ) $Tracks[] = $Track;
                }
                if (count($Tracks)) {
                    $Road['tracks'] = $Tracks;
                    $Roads[] = $Road;
                }
            }
        }
        
        return response()->json(['roads' => $Roads]);
    }

    public function getTracksNew(Request $request) 
    {
        /* *
        $Response = [
            "type" => "FeatureCollection",
            "features" => [
                [
                    "type" => "Feature",
                    "id" => 0,
                    "geometry" => [
                        "type" => "LineString",
                        "coordinates" => [
                            [50.2678453, 127.73968655],
                            [50.2677787, 127.74335955],
                            [50.26777549, 127.74352777],
                            [50.26777366, 127.74361187],
                            [50.26777299, 127.7436399],
                            [50.26776152, 127.74397599],
                            [50.26775878, 127.74403193],
                            [50.26775729, 127.74405988],
                            [50.26774661, 127.74422734],
                            [50.26774557, 127.74424127],
                            [50.26774, 127.74431085]
                        ]
                    ],
                    'options' => [
                        'draggable'   => false,
                        'strokeColor' => 'rgb(0, 0, 0)',
                        'strokeWidth' => 5
                    ],
                    "properties" => [
                        "balloonContent" => "Содержимое балуна",
                        "clusterCaption" => "Метка 1",
                        "hintContent" => "Текст подсказки"
                    ]
                ]
            ]
        ];
        
        return response()->json($Response)->setCallback($request->get('callback'));
        /* */
        //$key = 'test_roads_' . $request->get('zoom');   
        $key = 'test_roads_19';
        $Response = [
            'features' => [],
            'type' => 'FeatureCollection',
        ];
        $Features = [];

        $Roads = [];
        if (Cache::has($key)) {
            $RoadsCache = Cache::get($key);
            $coords = $request->get('coords');
            if (!is_array($coords)) $coords = json_decode($coords, true);
            $latitude = $coords['latitude'];
            $longitude = $coords['longitude'];

            foreach ($RoadsCache as $i => $RoadCache) {
                if (!$i) {
                    $Feature = [
                        'type' => 'Feature',
                        'id'   => 'road_' . $RoadCache['id'],
                        'geometry' => [
                            'type' => 'LineString',
                            'coordinates' => []
                        ],
                        'properties' => [
                            'hintContent'    => $RoadCache['name'],
                            "balloonContent" => $RoadCache['name'],
                            "clusterCaption" => $RoadCache['name']
                        ],
                        'options' => [
                            'draggable'   => false,
                            'strokeColor' => 'rgb(0, 0, 0)',
                            'strokeWidth' => 5
                        ]
                    ];
                    foreach ($RoadCache['tracks'] as $j => $Track) $Feature['geometry']['coordinates'][] = [(float) $Track['latitude'], (float) $Track['longitude']];
                    $Features[] = $Feature;
                }
            }

            $Response['features'] = $Features;
        }
        
        
        return response()->json($Response)->setCallback($request->get('callback'));
        //return response()->json($Response);
        /**/
    }

    public function getTracksCache(Request $request) 
    {
        //$key = 'test_roads_' . $request->get('zoom');   
        $key = 'test_roads_19';
        //Cache::flush();
        if (Cache::has($key)) $Roads = Cache::get($key);
        else $Roads = [];
        
        return response()->json([
            'roads' => $Roads
        ]);
    }

    public function getCategoriesTracks(Request $request) 
    {
        //$key = 'test_roads_' . $request->get('zoom');   
        $key = 'categories_19';

        $zoom = $request->get('zoom');
        if (!$zoom) $zoom = 19;
        $file = base_path('/storage/app/public/JSON/categories_' . $zoom . '.json');
        $file = file_get_contents($file);
        $Data = json_decode($file, true);

        $Categories = [];
        //if (Cache::has($key)) {
        if ($Data) {
            
            $coords = $request->get('coords');
            if (!is_array($coords)) $coords = json_decode($coords, true);
            $latitude  = $coords['latitude'];
            $longitude = $coords['longitude'];
            //$DataCache = Cache::get($key);

            foreach ($Data as $CacheItem) {
                $Tracks = [];
                $Category = [
                    'id' =>  $CacheItem['id'],
                    'category_type' =>  $CacheItem['category_type'],
                    'road' =>  $CacheItem['road'],
                    'tracks' => []
                ];
                
                foreach ($CacheItem['tracks'] as $Track) {
                    if (
                        (float) $Track['latitude']  >= (float) $latitude['from']  &&
                        (float) $Track['latitude']  <= (float) $latitude['to']    &&
                        (float) $Track['longitude'] >= (float) $longitude['from'] &&
                        (float) $Track['longitude'] <= (float) $longitude['to']
                    ) $Tracks[] = $Track;
                }
                if (count($Tracks)) {
                    $Category['tracks'] = $Tracks;
                    $Categories[] = $Category;
                }
            }
        }
        
        return response()->json($Categories);
    }

    
    public function getCategoriesType(Request $request) 
    {
        return response()->json(RoadCategoryType::whereNotNull('id')->get());
    }

    public function getCoversTracks(Request $request) 
    {
        //$key = 'test_roads_' . $request->get('zoom');   
        $key = 'covers_19';

        $zoom = $request->get('zoom');
        if (!$zoom) $zoom = 19;
        $file = base_path('/storage/app/public/JSON/covers_' . $zoom . '.json');
        $file = file_get_contents($file);
        $Data = json_decode($file, true);

        $Covers = [];
        //if (Cache::has($key)) {
        if ($Data) {
            //$DataCache = Cache::get($key);
            
            $coords = $request->get('coords');
            if (!is_array($coords)) $coords = json_decode($coords, true);
            $latitude  = $coords['latitude'];
            $longitude = $coords['longitude'];
            
            foreach ($Data as $CacheItem) {
                $Tracks = [];
                $Cover = [
                    'id'         => $CacheItem['id'],
                    'cover_type' => $CacheItem['cover_type'],
                    'road'       => $CacheItem['road'],
                    'tracks'     => []
                ];
                
                foreach ($CacheItem['tracks'] as $Track) {
                    if (
                        (float) $Track['latitude']  >= (float) $latitude['from']  &&
                        (float) $Track['latitude']  <= (float) $latitude['to']    &&
                        (float) $Track['longitude'] >= (float) $longitude['from'] &&
                        (float) $Track['longitude'] <= (float) $longitude['to']
                    ) $Tracks[] = $Track;
                }
                if (count($Tracks)) {
                    $Cover['tracks'] = $Tracks;
                    $Covers[] = $Cover;
                }
            }
        }
        
        return response()->json($Covers);
    }

    public function getCoversType(Request $request) 
    {
        return response()->json(CoversType::whereNotNull('id')->get());
    }


    public function getTracks2(Request $request)
    {
        $coords = $request->get('coords');
        $zoom = $request->get('zoom');
        $ZoomSteps = [
            '0' => '1400',
            '1' => '1300',
            '2' => '1200',
            '3' => '1000',
            '4' => '900',
            '5' => '800',
            '6' => '700',
            '7' => '600',
            '8' => '500',
            '9' => '400',
            '10' => '300',
            '11' => '250',
            '12' => '200',
            '13' => '150',
            '14' => '100',
            '15' => '50',
            '16' => '10',
            '17' => '10',
            '18' => '10',
            '19' => '10',
        ];
        $step = $ZoomSteps[$zoom];

        if (is_string($coords)) $coords = json_decode($coords, true);

        DB::beginTransaction();
        $Roads = Road::whereHas('tracks', function ($q) use ($coords) {
            $q->where('latitude', '>=', $coords['latitude']['from'])
                ->where('latitude', '<=', $coords['latitude']['to'])
                ->where('longitude', '>=', $coords['longitude']['from'])
                ->where('longitude', '<=', $coords['longitude']['to']);
        })->limit(2)->with('curves')->get();
        
        $Roads->transform(function($Road) use ($coords, $step) {

            $Tracks = $Road->tracks()->where(function ($q) use ($coords) {
                $q->where('latitude', '>=', $coords['latitude']['from'])
                    ->where('latitude', '<=', $coords['latitude']['to'])
                    ->where('longitude', '>=', $coords['longitude']['from'])
                    ->where('longitude', '<=', $coords['longitude']['to']);
            })->where(function ($q) use ($Road, $step) {
                $q->whereRaw( "CRC32(picket) % " . $step .  " = 0" );
                foreach ($Road->curves as $i => $curve) {
                    if (!$i) $q->whereBetween('picket', [$curve['begin_location'], $curve['end_location']]);
                    else $q->orWhereBetween('picket', [$curve['begin_location'], $curve['end_location']]);
                }
            }); //->get()->toArray();

            /*
            foreach ($Tracks as &$Track) {
                $Track = [$Track['latitude'], $Track['longitude']];
            } unset($Track);
            */

            $Road->tracks_sql = $Tracks->toSql();
            $Road->tracks = $Tracks->get()->toArray();
            unset($Road->curves);
            return $Road;
        });
        DB::commit();

        return response()->json([
            'coords' => $coords,
            'roads' => $Roads,
            'step'  => $step
        ]);
    }

    public function getTracksOld(Request $request)
    {
        $coords = $request->get('coords');
        $zoom = $request->get('zoom');
        $ZoomSteps = [
            '10' => '3000',
            '11' => '250',
            '12' => '200',
            '13' => '150',
            '14' => '100',
            '15' => '50',
            '16' => '10'
        ];
        $step = $ZoomSteps[$zoom];

        if (is_string($coords)) $coords = json_decode($coords, true);

        $Roads = Road::whereHas('tracks', function ($q) use ($coords) {
            $q->where('latitude', '>=', $coords['latitude']['from'])
                ->where('latitude', '<=', $coords['latitude']['to'])
                ->where('longitude', '>=', $coords['longitude']['from'])
                ->where('longitude', '<=', $coords['longitude']['to']);
        })->with('curves')->get();
        
        $Roads->transform(function($Road) use ($coords, $step) {

            $Tracks = $Road->tracks()->whereNotNull('id')->where(function ($q) use ($Road, $step) {
                foreach ($Road->curves as $i => $curve) {
                    if (!$i) $q->whereBetween('picket', [$curve['begin_location'], $curve['end_location']]);
                    else $q->orWhereBetween('picket', [$curve['begin_location'], $curve['end_location']]);
                }
            });

            $Road->tracks = $Tracks->where('latitude', '>=', $coords['latitude']['from'])
                ->where('latitude', '<=', $coords['latitude']['to'])
                ->where('longitude', '>=', $coords['longitude']['from'])
                ->where('longitude', '<=', $coords['longitude']['to'])
                ->whereRaw( "CRC32(id) % " . $step .  " = 0" )->get();
            unset($Road->curves);
            return $Road;
        });

        return response()->json([
            'coords' => $coords,
            'roads' => $Roads,
            'step'  => $step
        ]);
    }

    public function getDiagnostic(Request $request)
    {
        $key = 'diagnostic_' . $request->get('zoom');

        $zoom = $request->get('zoom');
        if (!$zoom) $zoom = 19;
        $file = base_path('/storage/app/public/JSON/diagnostic_' . $zoom . '.json');
        $file = file_get_contents($file);
        $Data = json_decode($file, true);

        $Diagnostic = [];
        //if (Cache::has($key)) {
        if ($Data) {
            $coords = $request->get('coords');
            if (!is_array($coords)) $coords = json_decode($coords, true);
            $latitude  = isset($coords['latitude']) ? $coords['latitude'] : 0;
            $longitude = isset($coords['longitude']) ? $coords['longitude'] : 0;

            //$DataCache = Cache::get($key);
            foreach ($Data as $CacheItem) {
                if (count($CacheItem['tracks'])) {
                    $Tracks = [];
                    $DiagnosticItem = [
                        'id'            => $CacheItem['id'],
                        'is_acceptable' => $CacheItem['is_acceptable'],
                        'color_rgb'     => $CacheItem['color_rgb'],
                        'tracks'        => $CacheItem['tracks'],
                        'road'          => $CacheItem['road'],
                        'tracks'        => []
                    ];
                    

                    if ($latitude && $longitude)
                        foreach ($CacheItem['tracks'] as $Track) {
                            if (
                                (float) $Track['latitude']  >= (float) $latitude['from']  &&
                                (float) $Track['latitude']  <= (float) $latitude['to']    &&
                                (float) $Track['longitude'] >= (float) $longitude['from'] &&
                                (float) $Track['longitude'] <= (float) $longitude['to']
                            ) $Tracks[] = $Track;
                        }
                    else foreach ($CacheItem['tracks'] as $Track) $Tracks[] = $Track;
                    
                    if (count($Tracks)) {
                        $DiagnosticItem['tracks'] = $Tracks;
                        $Diagnostic[] = $DiagnosticItem;
                    }
                }
            }
        }
        
        return response()->json($Diagnostic);
    }

    public function testImport(Request $request) 
    {
        $step = 19;
        /*
        $Data = DiagnosticTotalsRating::whereNotNull('id')->where('is_acceptable', 0)->limit(10)
        ->with([
            'tracks' => function ($q) use ($step) {
                $q->limit(10);
                //->whereRaw( "CRC32(picket) % " . $step . " = 0" )
            }
        ])->get();
        */

        $Data = RoadBaseTrack::whereHas('diagnostic', function ($q) {
            $q->where('is_acceptable', 0);
        })->limit(10)->get();


        return response()->json([
            'data' => $Data
        ]);
    }


    public function updateDiagnostic($Data)
    {
        $Left = 0;
        foreach ($Data as $i => &$Current) {
            if (!$i || $i < $Left) {
                $EndLocation = $Current['end_location'];
                foreach ($Data as $Item) {
                    if ($Current['id'] != $Item['id']) {
                        if ((int) $Item['begin_location'] === (int) $EndLocation) {
                            $EndLocation = $Item['end_location'];
                            $Left = $i;
                        }
                    }
                }
                $Current->end_location = $EndLocation;
                $Current->save();
            } else $Current->delete();
        }

        return $Data;
    }
}