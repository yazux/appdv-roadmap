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

class BuildRelations extends Command
{
    /** 9W1f1H5i dc7b19f2d890e6aa3863.xml
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'build:relations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build relations';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function getCpuUsage()
    {
        $load = sys_getloadavg();
        return $load[0];
    }

    public function handle()
    {
        $FullStart = time();
    
        $this->info('Удаление старых записей...');
        $this->deleteRelations($this);
        $this->info('Записи удалены, импорт...');

        $Data = DiagnosticTotalsRating::whereNotNull('id')->with([
            'road' => function ($q) {
                $q->with('curves');
            }
        ])->get();

        $this->buildDiagnosticTracksRelation($this, $Data);


        $FullFinish = time();
        $this->info('Готово, затрачено времени: ' . (($FullFinish - $FullStart) / 60) . ' мин.');
    }

    public function deleteRelations($context) 
    {
        $Count = DiagnosticsTracks::whereNotNull('id')->count();
        $chunk = ceil($Count/5000); //удаляем по 5000 записей

        $bar = $context->output->createProgressBar($chunk);
        for ($i = 0; $i <= $chunk; $i++) {
            if ($this->getCpuUsage() >= 1) sleep(10);
            DiagnosticsTracks::whereNotNull('id')->skip( ($i * 5000) )->take(5000)->delete();
            $bar->advance();
        }
        $bar->finish();
        $this->info(PHP_EOL);
    }

    public function buildDiagnosticTracksRelation($context, $Data)
    { 
        $context->info('Количество записей диагностики: ' . $Data->count());

        $bar = $context->output->createProgressBar(count($Data));
        foreach ($Data as $Item) {
            $key = 'diagnostic_relation_' . $Item->id;
            $time = 60 * 60 * 24 * 31;
            $Result = [];

            //Cache::forget($key);
           
            /** */

            //if (!Cache::has($key)) {

                DB::beginTransaction();
                $First = RoadBaseTrack::where('road_id', $Item->road_id)->where('picket', $Item['begin_location'])->first();//->limit(1)->get()->toArray();
                $Last  = RoadBaseTrack::where('road_id', $Item->road_id)->where('picket', $Item['end_location'])->first();//->limit(1)->get()->toArray();

                if (!$First) $context->info('Первый не найден');
                if (!$Last) $context->info('Послелний не найден');

                
                $DBTracks = RoadBaseTrack::where('road_id', $Item->road_id)
                    ->whereBetween('picket', [$Item['begin_location'], $Item['end_location']])
                    ->where(function ($q) use ($Item) {
                        foreach ($Item->road->curves as $i => $curve) {
                            if (!$i) $q->whereBetween('picket', [$curve['begin_location'], $curve['end_location']]);
                            else $q->orWhereBetween('picket', [$curve['begin_location'], $curve['end_location']]);
                        }
                    })->get()->toArray();
                
                DB::commit();
                $Tracks = array_merge([$First], $DBTracks, [$Last]);

                
                $TracksBar = $context->output->createProgressBar(count($Tracks));
                DB::beginTransaction();
                foreach ($Tracks as $Track) {
                    $Result[] = DiagnosticsTracks::firstOrCreate([
                        'track_id'      => $Track['id'],
                        'diagnostic_id' => $Item['id']
                    ]);
                    $TracksBar->advance();
                }
                DB::commit();
                $TracksBar->finish();
            //}
            //Cache::add($key, 'exist', $time);
            
            
            /**/
            $bar->advance();
        }
        $bar->finish();
        $this->info(PHP_EOL);
    }

}