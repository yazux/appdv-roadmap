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

class BuildCache extends Command
{
    /** 9W1f1H5i dc7b19f2d890e6aa3863.xml
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'build:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build cache';

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
        $zoom = 10;
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
        $step = $ZoomSteps[$zoom];
        //Cache::flush();
        
        $FullStart = time();
        
        
        //Построение кеша диагностики
        $start = time();
        $this->info('[Построение кеша диагностики]: Старт...');
        
        foreach($ZoomSteps as $zoom => $step) {
            $this->info('[Построение кеша диагностики]: зум: ' . $zoom . ', шаг: ' .  $step);
            $this->buildDiagnosticCacheNew($this, $zoom, $step);
        }
        
        $finish = time();
        $this->info('[Построение кеша диагностики]: Готово, затрачено времени: ' . (($finish - $start) / 60) . ' мин.');
        

        /*
        //Построение кеша покрытий
        $start = time();
        $this->info('[Построение кеша покрытий]: Старт...');
        
        $this->info('[Построение кеша покрытий]: зум: 19, шаг: 10');
        $Categories = $this->buildCoversCache($this, 19, 10);
        /*
        foreach($ZoomSteps as $zoom => $step) {
            $this->info('[Построение кеша покрытий]: зум: ' . $zoom . ', шаг: ' .  $step);
            $Categories = $this->buildCoversCache($this, $zoom, $step);
        }
        *
        $finish = time();
        $this->info('[Построение кеша покрытий]: Готово, затрачено времени: ' . (($finish - $start) / 60) . ' мин.');


        //Построение кеша категорий
        $start = time();
        $this->info('[Построение кеша категорий]: Старт...');
        $this->info('[Построение кеша категорий]: зум: 19, шаг: 10');
        $Categories = $this->buildCategoriesCache($this, 19, 10);
        /*
        foreach($ZoomSteps as $zoom => $step) {
            $this->info('[Построение кеша категорий]: зум: ' . $zoom . ', шаг: ' .  $step);
            $Categories = $this->buildCategoriesCache($this, $zoom, $step);
        }
        *
        $finish = time();
        $this->info('[Построение кеша категорий]: Готово, затрачено времени: ' . (($finish - $start) / 60) . ' мин.');
        

        
        //Построение кеша треков
        $start = time();
        $this->info('[Построение кеша треков]: Старт...');
        $this->info('[Построение кеша треков]: зум: 19, шаг: 10');
        $this->buildTracksCache($this, 19, 10);
        /*
        foreach($ZoomSteps as $zoom => $step) {
            $this->info('[Построение кеша треков]: зум: ' . $zoom . ', шаг: ' .  $step);
            $this->buildTracksCache($this, $zoom, $step);
        }
        *
        $finish = time();
        $this->info('[Построение кеша треков]: Готово, затрачено времени: ' . (($finish - $start) / 60) . ' мин.');
        */

        $FullFinish = time();
        $this->info('[Построение кеша]: Готово, затрачено времени: ' . (($FullFinish - $FullStart) / 60) . ' мин.');
    }

    public function getCpuUsage()
    {
        $load = sys_getloadavg();
        return $load[0];
    }

    public function updateDiagnostic($Data, $context)
    {
        if (count($Data) <= 1) return $Data;

        $bar = $context->output->createProgressBar(count($Data));
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
            $bar->advance();
        }
        $bar->finish();
        $this->info(PHP_EOL);
        return $Data;
    }

    public function buildTracksCache($context, $zoom, $step) 
    {
        $key = 'test_roads_' . $zoom;
        if (Cache::has($key)) {
            $Roads = Cache::get($key);
        } else {
            DB::beginTransaction();
            $Roads = Road::whereNotNull('id')->with('curves')->get();
            
            $bar = $context->output->createProgressBar(count($Roads));

            $Roads->transform(function($Road) use ($step, $context, $bar) {
                $Road->minPicket = $Road->tracks()->min('picket');
                $Road->maxPicket = $Road->tracks()->max('picket');
                $ResultTracks = [];
                $first = $Road->tracks()->where('picket', $Road->minPicket)->first();
                if ($first) $ResultTracks[] = $first;
                $Tracks = $Road->tracks()->whereRaw( "CRC32(picket) % " . $step .  " = 0" )->where(function ($q) use ($Road, $step) {
                    foreach ($Road->curves as $i => $curve) {
                        if (!$i) $q->whereBetween('picket', [$curve['begin_location'], $curve['end_location']]);
                        else $q->orWhereBetween('picket', [$curve['begin_location'], $curve['end_location']]);
                    }
                });//->count();

                //$Road->tracks_sql = $Tracks->toSql();
                $Tracks = $Tracks->get()->toArray();
                $ResultTracks = array_merge($ResultTracks, $Tracks);
                $last = $Road->tracks()->where('picket', $Road->maxPicket)->first();
                if ($last) $ResultTracks[] = $last;

                $Road->tracks = $ResultTracks;
                unset($Road->curves);
                $bar->advance();
                return $Road;
            });
            DB::commit();
            $bar->finish();
            $this->info(PHP_EOL);
            
            $time = 60 * 60 * 24 * 31;
            Cache::add($key, $Roads, $time);
        }
    }

    public function buildCategoriesCache($context, $zoom, $step) 
    {
        $key = 'categories_' . $zoom;
        DB::beginTransaction();
        $Data = RoadCategory::whereNotNull('id')->with([
            'categoryType', 'road' => function ($q) {
                $q->with('curves');
            }
        ])->get();

        $bar = $context->output->createProgressBar(count($Data));

        $Data->transform(function($Item) use ($context, $step, $bar) {
            $ResultTracks = [];

            //$fist = RoadBaseTrack::where('picket', $Item['begin_location'])->where('road_id', $Item->road->id)->first();
            //if ($fist) $ResultTracks[] = $fist;

            $Tracks = RoadBaseTrack::where('road_id', $Item->road_id)
                ->whereRaw( "CRC32(picket) % " . $step .  " = 0" )
                ->whereBetween('picket', [$Item['begin_location'], $Item['end_location']])
                ->where(function ($q) use ($Item) {
                    foreach ($Item->road->curves as $i => $curve) {
                        if (!$i) $q->whereBetween('picket', [$curve['begin_location'], $curve['end_location']]);
                        else $q->orWhereBetween('picket', [$curve['begin_location'], $curve['end_location']]);
                    }
                });//->limit(10);

                $Tracks = $Tracks->get()->toArray();
                $ResultTracks = array_merge($ResultTracks, $Tracks);
               
                $last = RoadBaseTrack::where('picket', $Item['end_location'])->where('road_id', $Item->road->id)->first();
                if ($last) $ResultTracks[] = $last;

                $Item->tracks = $ResultTracks;

                unset($Item->road->curves);

                //$context->info($Item->road->name . ': ' . $ResultTracks);
                $bar->advance();
            return $Item;
        });
        DB::commit();

        $bar->finish();
        $this->info(PHP_EOL);

        $time = 60 * 60 * 24 * 31;
        Cache::add($key, $Data, $time);

        return $Data;
    }

    public function buildCoversCache($context, $zoom, $step) 
    {
        $key = 'covers_' . $zoom;
        //DB::beginTransaction();
        $Data = RoadCover::whereNotNull('id')->with(['coverType', 'road' => function ($q) {$q->with('curves');}])->get();

        $bar = $context->output->createProgressBar(count($Data));

        $Data->transform(function($Item) use ($context, $step, $bar) {
            $ResultTracks = [];

            //$fist = RoadBaseTrack::where('picket', $Item['begin_location'])->where('road_id', $Item->road->id)->first();
            //if ($fist) $ResultTracks[] = $fist;

            $Tracks = RoadBaseTrack::where('road_id', $Item->road_id)
                ->whereRaw( "CRC32(picket) % " . $step .  " = 0" )
                ->whereBetween('picket', [$Item['begin_location'], $Item['end_location']])
                ->where(function ($q) use ($Item) {
                    foreach ($Item->road->curves as $i => $curve) {
                        if (!$i) $q->whereBetween('picket', [$curve['begin_location'], $curve['end_location']]);
                        else $q->orWhereBetween('picket', [$curve['begin_location'], $curve['end_location']]);
                    }
                });//->limit(10);

                $Tracks = $Tracks->get()->toArray();
                $ResultTracks = array_merge($ResultTracks, $Tracks);
               
                $last = RoadBaseTrack::where('picket', $Item['end_location'])->where('road_id', $Item->road->id)->first();
                if ($last) $ResultTracks[] = $last;

                $Item->tracks = $ResultTracks;

                unset($Item->road->curves);

                //$context->info($Item->road->name . ': ' . $ResultTracks);
                $bar->advance();
            return $Item;
        });
        //DB::commit();

        $bar->finish();
        $this->info(PHP_EOL);

        $time = 60 * 60 * 24 * 31;
        Cache::add($key, $Data, $time);

        return $Data;
    }


    public function buildDiagnosticCache($context, $zoom, $step) 
    {
        $key = 'diagnostic_' . $zoom;
        $time = 60 * 60 * 24 * 31;
        $Result = [];

        Cache::forget($key);

        //if (Cache::has($key)) return;
        //->where('road_id', 208)
        $Data = DiagnosticTotalsRating::whereNotNull('id')->with([
            'road' => function ($q) {
                $q->with('curves');
            }
        ])->get();

        //$context->info('Всего записей: ' . $Data->count());
        $bar = $context->output->createProgressBar(count($Data));
        $Data->transform(function($Item) use ($context, $bar, $step) {
            if ($this->getCpuUsage() >= 1) sleep(1);

            DB::beginTransaction();
            $First = RoadBaseTrack::where('road_id', $Item->road_id)->where('picket', $Item['begin_location'])->limit(1)->get()->toArray();
            $Last  = RoadBaseTrack::where('road_id', $Item->road_id)->where('picket', $Item['end_location'])->limit(1)->get()->toArray();

            $DBTracks = RoadBaseTrack::where('road_id', $Item->road_id)
                    ->whereBetween('picket', [$Item['begin_location'], $Item['end_location']])
                    ->where(function ($q) use ($Item) {
                        foreach ($Item->road->curves as $i => $curve) {
                            if (!$i) $q->whereBetween('picket', [$curve['begin_location'], $curve['end_location']]);
                            else $q->orWhereBetween('picket', [$curve['begin_location'], $curve['end_location']]);
                        }
                    })->whereRaw( "CRC32(picket) % " . $step . " = 0" )->get()->toArray();

            DB::commit();

            $Item->tracks = array_merge([], $First, $DBTracks, $Last);

            $Item->color_rgb = ($Item->is_acceptable) ? 'rgb(51, 204, 0)' : 'rgb(255, 0, 0)';
            $bar->advance();
            return $Item;
        });


        Cache::add($key, $Data, $time);

        $bar->finish();
        $context->info(PHP_EOL);
    }

    public function buildDiagnosticCacheNew($context, $zoom, $step) 
    {
        $key = 'diagnostic_' . $zoom;
        $time = 60 * 60 * 24 * 31;
        $Result = [];

        Cache::forget($key);

        $context->info('Получение записей...');

        //if (Cache::has($key)) return;

        $Data = DiagnosticTotalsRating::whereNotNull('id')->with([
            'road', 'tracks' => function ($q) use ($step) {
                $q->whereRaw( "CRC32(picket) % " . $step . " = 0" );
            }
        ])->get();
        $context->info('Всего записей: ' . $Data->count());
        
        DB::beginTransaction();
        $bar = $context->output->createProgressBar(count($Data));
        $Data->transform(function($Item) use ($context, $bar, $step, $time) {
            if ($context->getCpuUsage() >= 1) sleep(2);

            if (Cache::has('diagnostic_' . $Item->id . '_first_last')) {
                $FirstLast = Cache::get('diagnostic_' . $Item->id . '_first_last');
                $First = $FirstLast['first'];
                $Last = $FirstLast['last'];
            } else {
                $First = RoadBaseTrack::where('road_id', $Item->road_id)->where('picket', $Item['begin_location'])->first();
                $Last  = RoadBaseTrack::where('road_id', $Item->road_id)->where('picket', $Item['end_location'])->first();
                Cache::add('diagnostic_' . $Item->id . '_first_last', [
                    'first' => $First,
                    'last'  => $Last
                ], $time);
            }

            
            if ($First) $Item->tracks->prepend($First);
            if ($Last)  $Item->tracks->push($Last);
            
            $Item->color_rgb = ($Item->is_acceptable) ? 'rgb(51, 204, 0)' : 'rgb(255, 0, 0)';
            $bar->advance();
            return $Item;
        });
        DB::commit();
        $Result = array_merge($Result, $Data->toArray());

        Cache::add($key, $Result, $time);

        $bar->finish();
        $context->info(PHP_EOL);
    }

}