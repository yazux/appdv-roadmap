<?php

namespace App\Console\Commands;

use DB;
use Cache;
use Exception;
use App\Classes\Importer;
use Illuminate\Console\Command;
use App\Road;
use App\RoadBaseTrack;
use App\RoadPlanCurf;
use App\Classes\Reader;
use App\Classes\RoadImporter;
use App\Classes\PlanCurvesImporter;
use App\RoadCategoryType;
use App\RoadCategory;
use App\RoadCover;
use App\DiagnosticTotalsRating;
use App\DiagnosticsTracks;

class BuildJson extends Command
{
    /** 9W1f1H5i dc7b19f2d890e6aa3863.xml
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'build:json';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build json';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $ZoomSteps = [
            /*
            '0' => '10000',
            '1' => '10000',
            '2' => '8000',
            '3' => '7000',
            '4' => '6000',
            '5' => '5000',
            '6' => '4000',            
            */

            
            '7' => '3000',
            '8' => '800',
            '9' => '500',            
            '10' => '250',
            '11' => '200',
            '12' => '150',
            '13' => '100',
            '14' => '70',
            


            /*
            '15' => '50',
            '16' => '25',
            '17' => '20',
            '18' => '15',
            '19' => '10',
            */
        ];

        
        $FullStart = time();
        
    
        //Старая логика для Яндекс карты
        /*
            //Построение кеша треков
            $start = time();
            $this->info('[Построение кеша треков]: Старт...');
            foreach($ZoomSteps as $zoom => $step) {
                $this->info('[Построение кеша треков]: зум: ' . $zoom . ', шаг: ' .  $step);
                $this->buildTracksJSON($this, $zoom, $step);
            }
            $finish = time();
            $this->info('[Построение кеша треков]: Готово, затрачено времени: ' . (($finish - $start) / 60) . ' мин.');
            
            //Построение кеша покрытий
            $start = time();
            $this->info('[Построение кеша покрытий]: Старт...');
            foreach($ZoomSteps as $zoom => $step) {
                $this->info('[Построение кеша покрытий]: зум: ' . $zoom . ', шаг: ' .  $step);
                $this->buildCoversJSON($this, $zoom, $step);
            }
            $finish = time();
            $this->info('[Построение кеша покрытий]: Готово, затрачено времени: ' . (($finish - $start) / 60) . ' мин.');
        
            
            //Построение кеша категорий
            $start = time();
            $this->info('[Построение кеша категорий]: Старт...');        
            foreach($ZoomSteps as $zoom => $step) {
                $this->info('[Построение кеша категорий]: зум: ' . $zoom . ', шаг: ' .  $step);
                $Categories = $this->buildCategoriesJSON($this, $zoom, $step);
            }
            $finish = time();
            $this->info('[Построение кеша категорий]: Готово, затрачено времени: ' . (($finish - $start) / 60) . ' мин.');
                
            //Построение кеша диагностики
            $start = time();
            $this->info('[Построение кеша диагностики]: Старт...');
            foreach($ZoomSteps as $zoom => $step) {
                $this->info('[Построение кеша диагностики]: зум: ' . $zoom . ', шаг: ' .  $step);
                $this->buildDiagnosticJSON($this, $zoom, $step);
            }
            $finish = time();
            $this->info('[Построение кеша диагностики]: Готово, затрачено времени: ' . (($finish - $start) / 60) . ' мин.');
        */


        //Просто треки сейчас не выводятся на карте, так что нет смысла строить кеш
        //$this->info('[Построение кеша треков]');
        //$this->buildTracksJSONOSM($this);
        
        if ($this->getCpuUsage() >= 1) sleep(10);
        $this->info('[Построение кеша покрытий]');
        $this->buildCoversJSONOSM($this);
        
        if ($this->getCpuUsage() >= 1) sleep(10);
        $this->info('[Построение кеша категорий]');
        $this->buildCategoriesJSONOSM($this);
        
        if ($this->getCpuUsage() >= 1) sleep(10);
        $this->info('[Построение кеша диагностики]');
        $this->buildDiagnosticJSONOSM($this);


        $FullFinish = time();
        $this->info('[Построение кеша]: Готово, затрачено времени: ' . (($FullFinish - $FullStart) / 60) . ' мин.');
    }

    public function getCpuUsage()
    {
        $load = sys_getloadavg();
        return $load[0];
    }

    public function convertColor($color) {
        $arr = explode(',', $color);
        foreach ($arr as &$item) {
            $item = hexdec($item);
        } unset($item);
        return 'rgb(' . implode(', ', $arr) . ')';
    }


    public function buildTracksJSON($context, $zoom, $step) 
    {
        if ($this->getCpuUsage() >= 1) sleep(10);
        $time = 60 * 60 * 24 * 31;
        $file = base_path('/storage/app/public/JSON/tracks_' . $zoom . '.json');
        //unlink($file); return;
        //if (file_exists($file)) return;

        DB::beginTransaction();
        $Roads = Road::whereNotNull('id')->with('curves')->get();
        $bar = $context->output->createProgressBar(count($Roads));
        $Roads->transform(function($Road) use ($bar, $time, $step) {
            if ($this->getCpuUsage() >= 1) sleep(2);
            $First = [];
            $Last = [];
            if (Cache::has('tracks_' . $Road->id . '_first_last')) {
                $FirstLast = Cache::get('tracks_' . $Road->id . '_first_last');
                $First = $FirstLast['first'];
                $Last = $FirstLast['last'];
            } else {
                $Road->minPicket = $Road->tracks()->min('picket');
                $Road->maxPicket = $Road->tracks()->max('picket');
                $First = $Road->tracks()->where('picket', $Road->minPicket)->limit(1)->get()->toArray();
                $Last  = $Road->tracks()->where('picket', $Road->maxPicket)->limit(1)->get()->toArray();
                Cache::add('tracks_' . $Road->id . '_first_last', ['first' => $First, 'last'  => $Last], $time);
            }
            //$First[0]['first_last'] = true;
            //$Last[0]['first_last'] = true;
            
            if (isset($First['picket']) && isset($Last['picket']) && ($Last['picket'] - $First['picket']) <= ($step)) $ResultTracks = [];

            $ResultTracks = [];
            $Tracks = $Road->tracks()->whereRaw( "CRC32(picket) % " . $step .  " = 0" )->where(function ($q) use ($Road, $step) {
                foreach ($Road->curves as $i => $curve) {
                    if (!$i) $q->whereBetween('picket', [$curve['begin_location'], $curve['end_location']]);
                    else $q->orWhereBetween('picket', [$curve['begin_location'], $curve['end_location']]);
                }
            })->orderBy('picket', 'ASC')->get()->toArray();
            $ResultTracks = array_merge($First, $Tracks, $Last);
            $bar->advance();

            unset($Road->tracks);
            $Tracks = [];
           
            if (count($ResultTracks)) 
                foreach ($ResultTracks as $Track) 
                    if (isset($Track['latitude']) && isset($Track['longitude'])) 
                        $Tracks[] = ['latitude'  => $Track['latitude'], 'longitude' => $Track['longitude']];            
           
            $Road->tracks = $Tracks;
            unset($Road->curves);
            return $Road;
        });
        DB::commit();
        $bar->finish();

        file_put_contents($file, json_encode($Roads) );

        $this->info(PHP_EOL);
    }

    public function buildCoversJSON($context, $zoom, $step) 
    {
        if ($this->getCpuUsage() >= 1) sleep(10);
        $time = 60 * 60 * 24 * 31;
        $file = base_path('/storage/app/public/JSON/covers_' . $zoom . '.json');
        //if (file_exists($file)) return;
        DB::beginTransaction();
        $Data = RoadCover::whereNotNull('id')->with(['coverType', 'road' => function ($q) {$q->with('curves');}])->get();
        $bar = $context->output->createProgressBar(count($Data));
        $Data->transform(function($Item) use ($bar, $time, $step) {
            if ($this->getCpuUsage() >= 1) sleep(2);
            $First = [];
            $Last = [];
            Cache::forget('covers_' . $Item->id . '_first_last');
            if (Cache::has('covers_' . $Item->id . '_first_last')) {
                $FirstLast = Cache::get('covers_' . $Item->id . '_first_last');
                $First = $FirstLast['first'];
                $Last = $FirstLast['last'];
            } else {
                if ((int) $Item['begin_location'] === 0) $Item['begin_location'] = 1;
                $First = RoadBaseTrack::where('picket', $Item['begin_location'])->where('road_id', $Item->road->id)->limit(1)->get()->toArray();
                $Last  = RoadBaseTrack::where('picket', $Item['end_location'])->where('road_id', $Item->road->id)->limit(1)->get()->toArray();
                Cache::add('covers_' . $Item->id . '_first_last', ['first' => $First, 'last'  => $Last], $time);
            }

            $Tracks = RoadBaseTrack::where('road_id', $Item->road_id)
                ->whereRaw( "CRC32(picket) % " . $step .  " = 0" )
                ->whereBetween('picket', [$Item['begin_location'], $Item['end_location']])
                ->where(function ($q) use ($Item) {
                    foreach ($Item->road->curves as $i => $curve) {
                        if (!$i) $q->whereBetween('picket', [$curve['begin_location'], $curve['end_location']]);
                        else $q->orWhereBetween('picket', [$curve['begin_location'], $curve['end_location']]);
                    }
                })->orderBy('picket', 'ASC')->get()->toArray();
            $ResultTracks = array_merge($First, $Tracks, $Last);

            if (isset($First['picket']) && isset($Last['picket']) && ($Last['picket'] - $First['picket']) <= ($step)) $ResultTracks = [];

            unset($Item->tracks);
            $Tracks = [];
            
            if (count($ResultTracks)) 
                foreach ($ResultTracks as $Track) 
                    if (isset($Track['latitude']) && isset($Track['longitude'])) 
                        $Tracks[] = ['latitude'  => $Track['latitude'], 'longitude' => $Track['longitude']];            
            
            $Item->tracks = $Tracks;
            $bar->advance();
            unset($Item->road->curves);
            return $Item;            
        });
        DB::commit();
        $bar->finish();
        file_put_contents($file, json_encode($Data));
        $this->info(PHP_EOL);
    }

    public function buildCategoriesJSON($context, $zoom, $step) 
    {
        if ($this->getCpuUsage() >= 1) sleep(10);
        $time = 60 * 60 * 24 * 31;
        $file = base_path('/storage/app/public/JSON/categories_' . $zoom . '.json');
        //if (file_exists($file)) return;
        DB::beginTransaction();
        $Data = RoadCategory::whereNotNull('id')->with(['categoryType', 'road' => function ($q) {$q->with('curves');}])->get();
        $bar = $context->output->createProgressBar(count($Data));
        $Data->transform(function($Item) use ($bar, $time, $step) {
            if ($this->getCpuUsage() >= 1) sleep(1);
            $First = [];
            $Last = [];
            Cache::forget('categories_' . $Item->id . '_first_last');
            if (Cache::has('categories_' . $Item->id . '_first_last')) {
                $FirstLast = Cache::get('categories_' . $Item->id . '_first_last');
                $First = $FirstLast['first'];
                $Last = $FirstLast['last'];
            } else {
                if ((int) $Item['begin_location'] === 0) $Item['begin_location'] = 1;
                $First = RoadBaseTrack::where('picket', $Item['begin_location'])->where('road_id', $Item->road->id)->limit(1)->get()->toArray();
                $Last  = RoadBaseTrack::where('picket', $Item['end_location'])->where('road_id', $Item->road->id)->limit(1)->get()->toArray();
                Cache::add('categories_' . $Item->id . '_first_last', ['first' => $First, 'last'  => $Last], $time);
            }

            $Tracks = RoadBaseTrack::where('road_id', $Item->road_id)
                ->whereRaw( "CRC32(picket) % " . $step .  " = 0" )
                ->whereBetween('picket', [$Item['begin_location'], $Item['end_location']])
                ->where(function ($q) use ($Item) {
                    foreach ($Item->road->curves as $i => $curve) {
                        if (!$i) $q->whereBetween('picket', [$curve['begin_location'], $curve['end_location']]);
                        else $q->orWhereBetween('picket', [$curve['begin_location'], $curve['end_location']]);
                    }
                })->orderBy('picket', 'ASC')->get()->toArray();
            $ResultTracks = array_merge($First, $Tracks, $Last);
            
            if (isset($First['picket']) && isset($Last['picket']) && ($Last['picket'] - $First['picket']) <= ($step)) $ResultTracks = [];

            unset($Item->tracks);
            $Tracks = [];
            
            if (count($ResultTracks)) 
                foreach ($ResultTracks as $Track) 
                    if (isset($Track['latitude']) && isset($Track['longitude'])) 
                        $Tracks[] = ['latitude'  => $Track['latitude'], 'longitude' => $Track['longitude']];            

            $Item->tracks = $Tracks;
            $bar->advance();
            unset($Item->road->curves);
            return $Item;
        });
        DB::commit();
        $bar->finish();
        file_put_contents($file, json_encode($Data) );
        $this->info(PHP_EOL);
    }

    public function buildDiagnosticJSON($context, $zoom, $step) 
    {
        if ($this->getCpuUsage() >= 1) sleep(10);
        $time = 60 * 60 * 24 * 31;
        $file = base_path('/storage/app/public/JSON/diagnostic_' . $zoom . '.json');
        $fileOSM = base_path('/storage/app/public/JSON/diagnostic_osm_' . $zoom . '.json');
        $OSMObject = [
            "type"     => "FeatureCollection",                                                                             
            "features" => []
        ];
        //if (file_exists($file)) return;

        $context->info('Получение данных...');
        $Data = DiagnosticTotalsRating::whereNotNull('id')->with(['road', 'tracks' => function ($q) use ($step) {$q->whereRaw( "CRC32(picket) % " . $step . " = 0" )->orderBy('picket', 'ASC');}])->get();
        DB::beginTransaction();
        $bar = $context->output->createProgressBar(count($Data));
        $Data->transform(function($Item) use ($bar, $time, $step, $context, $zoom, $OSMObject) {
            if ($context->getCpuUsage() >= 1) sleep(2);
            Cache::forget('diagnostic_' . $Item->id . '_first_last');
            if (Cache::has('diagnostic_' . $Item->id . '_first_last')) {
                $FirstLast = Cache::get('diagnostic_' . $Item->id . '_first_last');
                $First = $FirstLast['first'];
                $Last = $FirstLast['last'];
            } else {
                if ((int) $Item['begin_location'] === 0) $Item['begin_location'] = 1;
                $First = RoadBaseTrack::where('road_id', $Item->road_id)->where('picket', $Item['begin_location'])->first();
                $Last  = RoadBaseTrack::where('road_id', $Item->road_id)->where('picket', $Item['end_location'])->first();
                Cache::add('diagnostic_' . $Item->id . '_first_last', ['first' => $First, 'last'  => $Last], $time);
            }
            if ($First) $Item->tracks->prepend($First);
            if ($Last)  $Item->tracks->push($Last);
            $Item->color_rgb = ($Item->is_acceptable) ? 'rgb(51, 204, 0)' : 'rgb(255, 0, 0)';
            $ResultTracks = $Item->tracks->toArray();
            unset($Item->tracks);
            $Item->tracks = [];
    
            if (isset($First['picket']) && isset($Last['picket']) && ($Last['picket'] - $First['picket']) <= ($step)) $ResultTracks = [];

            $Tracks = [];
            $TracksOSM = [];
            if (count($ResultTracks))
                foreach ($ResultTracks as $Track) 
                    if (isset($Track['latitude']) && isset($Track['longitude'])) {
                        $Tracks[] = ['latitude'  => $Track['latitude'], 'longitude' => $Track['longitude']];
                        $TracksOSM[] = [$Track['latitude'], $Track['longitude']];
                    }

            $Item->tracks = $Tracks;
            $bar->advance();
            unset($Item->road->curves);


            $OSMObject['features'][] = [
                "type" => "Feature", 
                "id" => $Item->id, 
                "properties" => [
                    "NAME"    => $Item->road->name, 
                    'color'   => $Item->color_rgb,
                    'weight'  => 3,
                    'opacity' => 1,
                    'smoothFactor' => 1
                ], 
                "geometry" => [
                    "type" => "Polyline",
                    "coordinates" => $TracksOSM
                ]
            ];


            return $Item;
        });
        DB::commit();
        $bar->finish();

        unlink($file);
        file_put_contents($file, json_encode($Data));
        file_put_contents($fileOSM, json_encode($OSMObject));
        $this->info(PHP_EOL);
    }








    public function buildTracksJSONOSM($context) 
    {
        $step = 20;
        if ($this->getCpuUsage() >= 1) sleep(10);
        $time = 60 * 60 * 24 * 31;
        $file = base_path('/storage/app/public/JSON/tracks_osm.json');
        $OSMObject = [
            "type"     => "FeatureCollection",                                                                             
            "features" => []
        ];

        DB::beginTransaction();
        $Roads = Road::whereNotNull('id')->with('curves')->get();
        $bar = $context->output->createProgressBar(count($Roads));
        foreach ($Roads as $Road) {
            if ($this->getCpuUsage() >= 1) sleep(2);
            $First = [];
            $Last = [];
            if (Cache::has('tracks_' . $Road->id . '_first_last')) {
                $FirstLast = Cache::get('tracks_' . $Road->id . '_first_last');
                $First = $FirstLast['first'];
                $Last = $FirstLast['last'];
            } else {
                $Road->minPicket = $Road->tracks()->min('picket');
                $Road->maxPicket = $Road->tracks()->max('picket');
                $First = $Road->tracks()->where('picket', $Road->minPicket)->limit(1)->get()->toArray();
                $Last  = $Road->tracks()->where('picket', $Road->maxPicket)->limit(1)->get()->toArray();
                Cache::add('tracks_' . $Road->id . '_first_last', ['first' => $First, 'last'  => $Last], $time);
            }
            if (isset($First['picket']) && isset($Last['picket']) && ($Last['picket'] - $First['picket']) <= ($step)) $ResultTracks = [];

            $ResultTracks = [];
            $Tracks = $Road->tracks()->whereRaw( "CRC32(picket) % 20 = 0" )->where(function ($q) use ($Road, $step) {
                foreach ($Road->curves as $i => $curve) {
                    if (!$i) $q->whereBetween('picket', [$curve['begin_location'], $curve['end_location']]);
                    else $q->orWhereBetween('picket', [$curve['begin_location'], $curve['end_location']]);
                }
            })->orderBy('picket', 'ASC')->get()->toArray();
            $ResultTracks = array_merge($First, $Tracks, $Last);
            $bar->advance();

            unset($Road->tracks);
            $Tracks = [];
           
            if (count($ResultTracks)) 
                foreach ($ResultTracks as $Track) 
                    if (isset($Track['latitude']) && isset($Track['longitude'])) 
                        $Tracks[] = [$Track['longitude'], $Track['latitude']];
           
            $OSMObject['features'][] = [
                "type" => "Feature", 
                "id" => $Road->id, 
                "properties" => [
                    "name"    => $Road->name, 
                    'color'   => 'rgb(0, 0, 0)',
                    'weight'  => 3,
                    'opacity' => 1,
                    'smoothFactor' => 1
                ], 
                "geometry" => [
                    "type" => "LineString",
                    "coordinates" => $Tracks
                ]
            ];
        }

        DB::commit();
        $bar->finish();

        file_put_contents($file, json_encode($OSMObject));

        $this->info(PHP_EOL);
    }

    public function buildCoversJSONOSM($context) 
    {
        $step = 20;
        if ($this->getCpuUsage() >= 1) sleep(10);
        $time = 60 * 60 * 24 * 31;
        $file = base_path('/storage/app/public/JSON/covers_osm.json');
        $OSMObject = [
            "type"     => "FeatureCollection",                                                                             
            "features" => []
        ];
        //if (file_exists($file)) return;
        DB::beginTransaction();
        $Data = RoadCover::whereNotNull('id')->with(['coverType', 'road' => function ($q) {$q->with('curves');}])->get();
        $bar = $context->output->createProgressBar(count($Data));
        foreach ($Data as $Item) {
            if ($this->getCpuUsage() >= 1) sleep(2);
            $First = [];
            $Last = [];
            if (Cache::has('covers_' . $Item->id . '_first_last')) {
                $FirstLast = Cache::get('covers_' . $Item->id . '_first_last');
                $First = $FirstLast['first'];
                $Last = $FirstLast['last'];
            } else {
                if ((int) $Item['begin_location'] === 0) $Item['begin_location'] = 1;
                $First = RoadBaseTrack::where('picket', $Item['begin_location'])->where('road_id', $Item->road->id)->limit(1)->get()->toArray();
                $Last  = RoadBaseTrack::where('picket', $Item['end_location'])->where('road_id', $Item->road->id)->limit(1)->get()->toArray();
                Cache::add('covers_' . $Item->id . '_first_last', ['first' => $First, 'last'  => $Last], $time);
            }

            $Tracks = RoadBaseTrack::where('road_id', $Item->road_id)
                ->whereRaw( "CRC32(picket) % 20 = 0" )
                ->whereBetween('picket', [$Item['begin_location'], $Item['end_location']])
                ->where(function ($q) use ($Item) {
                    foreach ($Item->road->curves as $i => $curve) {
                        if (!$i) $q->whereBetween('picket', [$curve['begin_location'], $curve['end_location']]);
                        else $q->orWhereBetween('picket', [$curve['begin_location'], $curve['end_location']]);
                    }
                })->orderBy('picket', 'ASC')->get()->toArray();
            $ResultTracks = array_merge($First, $Tracks, $Last);

            if (isset($First['picket']) && isset($Last['picket']) && ($Last['picket'] - $First['picket']) <= ($step)) $ResultTracks = [];

            unset($Item->tracks);
            $Tracks = [];
            
            if (count($ResultTracks)) 
                foreach ($ResultTracks as $Track) 
                    if (isset($Track['latitude']) && isset($Track['longitude'])) 
                        $Tracks[] = [(float) $Track['longitude'], (float) $Track['latitude']];    
            
            
            $bar->advance();
            unset($Item->road->curves);   

            $OSMObject['features'][] = [
                "type" => "Feature", 
                "id" => $Item->id, 
                "properties" => [
                    "name"    => $Item->road->name . ' (' . $Item->coverType->name . ')', 
                    'color'   => $Item->coverType->color_rgb,
                    'cover_type_id' => $Item->coverType->id,
                    'weight'  => 3,
                    'opacity' => 1,
                    'smoothFactor' => 1
                ], 
                "geometry" => [
                    "type" => "LineString",
                    "coordinates" => $Tracks
                ]
            ];       
        }
        DB::commit();
        $bar->finish();
        file_put_contents($file, json_encode($OSMObject));
        $this->info(PHP_EOL);
    }

    public function buildCategoriesJSONOSM($context) 
    {
        $step = 20;
        if ($this->getCpuUsage() >= 1) sleep(10);
        $time = 60 * 60 * 24 * 31;
        $file = base_path('/storage/app/public/JSON/categories_osm.json');
        $OSMObject = [
            "type"     => "FeatureCollection",                                                                             
            "features" => []
        ];

        DB::beginTransaction();
        $Data = RoadCategory::whereNotNull('id')->with(['categoryType', 'road' => function ($q) {$q->with('curves');}])->get();
        $bar = $context->output->createProgressBar(count($Data));
        foreach ($Data as $Item) {
            if ($this->getCpuUsage() >= 1) sleep(1);
            $First = [];
            $Last = [];
            if (Cache::has('categories_' . $Item->id . '_first_last')) {
                $FirstLast = Cache::get('categories_' . $Item->id . '_first_last');
                $First = $FirstLast['first'];
                $Last = $FirstLast['last'];
            } else {
                if ((int) $Item['begin_location'] === 0) $Item['begin_location'] = 1;
                $First = RoadBaseTrack::where('picket', $Item['begin_location'])->where('road_id', $Item->road->id)->limit(1)->get()->toArray();
                $Last  = RoadBaseTrack::where('picket', $Item['end_location'])->where('road_id', $Item->road->id)->limit(1)->get()->toArray();
                Cache::add('categories_' . $Item->id . '_first_last', ['first' => $First, 'last'  => $Last], $time);
            }

            $Tracks = RoadBaseTrack::where('road_id', $Item->road_id)
                ->whereRaw( "CRC32(picket) % 20 = 0" )
                ->whereBetween('picket', [$Item['begin_location'], $Item['end_location']])
                ->where(function ($q) use ($Item) {
                    foreach ($Item->road->curves as $i => $curve) {
                        if (!$i) $q->whereBetween('picket', [$curve['begin_location'], $curve['end_location']]);
                        else $q->orWhereBetween('picket', [$curve['begin_location'], $curve['end_location']]);
                    }
                })->orderBy('picket', 'ASC')->get()->toArray();
            $ResultTracks = array_merge($First, $Tracks, $Last);
            
            if (isset($First['picket']) && isset($Last['picket']) && ($Last['picket'] - $First['picket']) <= ($step)) $ResultTracks = [];

            unset($Item->tracks);
            $Tracks = [];
            
            if (count($ResultTracks)) 
                foreach ($ResultTracks as $Track) 
                    if (isset($Track['latitude']) && isset($Track['longitude'])) 
                        $Tracks[] = [(float) $Track['longitude'], (float) $Track['latitude']];       

            if (count($Tracks)) $OSMObject['features'][] = [
                "type" => "Feature", 
                "id" => $Item->id, 
                "properties" => [
                    "name"    => $Item->road->name . ' (' . $Item->categoryType->name . ')', 
                    'color'   => $this->convertColor($Item->categoryType->color_rgb),
                    'category_type_id' => $Item->categoryType->id,
                    'weight'  => 3,
                    'opacity' => 1,
                    'smoothFactor' => 1
                ], 
                "geometry" => [
                    "type" => "LineString",
                    "coordinates" => $Tracks
                ]
            ];

            $bar->advance();
            unset($Item->road->curves);
        }
        DB::commit();
        $bar->finish();
        file_put_contents($file, json_encode($OSMObject));
        $this->info(PHP_EOL);
    }

    public function buildDiagnosticJSONOSM($context) 
    {
        $step = 50;
        if ($this->getCpuUsage() >= 1) sleep(10);
        $time = 60 * 60 * 24 * 31;
        $file = base_path('/storage/app/public/JSON/diagnostic_osm.json');
        $OSMObject = [
            "type"     => "FeatureCollection",                                                                             
            "features" => []
        ];

        $context->info('Получение данных...');
        $Data = DiagnosticTotalsRating::whereNotNull('id')->with(['road', 'tracks' => function ($q) {$q->whereRaw( "CRC32(picket) % 50 = 0")->orderBy('picket', 'ASC');}])->get();
        DB::beginTransaction();
        $bar = $context->output->createProgressBar(count($Data));
        foreach ($Data as $Item) {
            if ($context->getCpuUsage() >= 1) sleep(2);
            if (Cache::has('diagnostic_' . $Item->id . '_first_last')) {
                $FirstLast = Cache::get('diagnostic_' . $Item->id . '_first_last');
                $First = $FirstLast['first'];
                $Last = $FirstLast['last'];
            } else {
                if ((int) $Item['begin_location'] === 0) $Item['begin_location'] = 1;
                $First = RoadBaseTrack::where('road_id', $Item->road_id)->where('picket', $Item['begin_location'])->first();
                $Last  = RoadBaseTrack::where('road_id', $Item->road_id)->where('picket', $Item['end_location'])->first();
                Cache::add('diagnostic_' . $Item->id . '_first_last', ['first' => $First, 'last'  => $Last], $time);
            }
            if ($First) $Item->tracks->prepend($First);
            if ($Last)  $Item->tracks->push($Last);
            $Item->color_rgb = ($Item->is_acceptable) ? 'rgb(51, 204, 0)' : 'rgb(255, 0, 0)';
            $ResultTracks = $Item->tracks->toArray();
            if (isset($First['picket']) && isset($Last['picket']) && ($Last['picket'] - $First['picket']) <= ($step)) $ResultTracks = [];

            $Tracks = [];
            if (count($ResultTracks))
                foreach ($ResultTracks as $Track) 
                    if (isset($Track['latitude']) && isset($Track['longitude']))
                        $Tracks[] = [(float) $Track['longitude'], (float) $Track['latitude']];
                
            $bar->advance();

            $OSMObject['features'][] = [
                "type" => "Feature", 
                "id" => $Item->id, 
                "properties" => [
                    "name"    => $Item->road->name, 
                    'color'   => $Item->color_rgb,
                    'weight'  => 3,
                    'opacity' => 1,
                    'smoothFactor' => 1
                ], 
                "geometry" => [
                    "type" => "LineString",
                    "coordinates" => $Tracks
                ]
            ];
        }

        DB::commit();
        $bar->finish();
        file_put_contents($file, json_encode($OSMObject));
        $this->info(PHP_EOL);
    }

}