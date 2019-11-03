<?php

namespace App\Console\Commands;

use Exception;
use App\Classes\Importer;
use Illuminate\Console\Command;
use App\Classes\Reader;
use App\Classes\RoadTrackImporter;
use App\Road;
use App\RoadBaseTrack;
use App\RoadCover;
use App\Classes\RoadCoverImporter;
use DB;

class importRoadCovers extends Command
{
    /** 9W1f1H5i dc7b19f2d890e6aa3863.xml
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:roadcovers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import road covers';

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
        $this->info('[Импорт покрытий дорог]: Старт...');
        $this->info('[Импорт покрытий дорог]: Чтение файла...');

        $Reader = new Reader();
        //$Roads = Road::whereNotNull('id')->skip(3)->limit(1)->get();
        //$Roads = Road::whereNotNull('id')->limit(2)->get();
        $Roads = Road::whereNotNull('id')->get();
        $Importer = new RoadCoverImporter($Reader);
        $CoversCount = 0;
        //RoadCover::whereNotNull('id')->delete();
        
        $context = $this;
        $context->info('[Импорт покрытий дорог]: Будет обратанно дорог: ' . count($Roads));
        $bar = $this->output->createProgressBar(count($Roads));
        $Roads->transform(function ($Road) use ($Importer, $context, $bar, $CoversCount) {
            $covers = [];
            $this->info(PHP_EOL);
            $context->info('[Импорт покрытий дорог]: Обработка дороги: ' . $Road->name);
            $context->info('[Импорт покрытий дорог]: Чтение файла по дороге: ' . $Road->name);
            $covers = $Importer->getCovers($Road->id);

            if ($covers && count($covers)) {
                $context->info('[Импорт покрытий дорог]: Файл прочитан, количество покрытий: ' . count($covers));
                $CoversCount += $context->importCovers($Road, $covers, $context, $Importer);
            }
            
            $bar->advance();
            return $Road;
        });
        $bar->finish();
        $this->info(PHP_EOL);
        $context->info('[Импорт покрытий дорог]: Импорт завешен, обработанно дорог: ' . count($Roads) . ', обработанно покрытий: ' . $CoversCount);
    }

    
    public function importCovers($Road, $covers, $context, $Importer)
    {
        $this->info('[Импорт покрытий дорог]: Начат импорт покрытий по дороге: ' . $Road->name);
        $Covers = [];

        $this->info('[Импорт покрытий дорог]: всего покрытий по дороге: ' . count($covers));
        $bar = $this->output->createProgressBar(count($covers));
        foreach ($covers as $cover) {
            $cover['road_id'] = $Road->id;
            $Cover = RoadCover::where([
                'road_id'        => $cover['road_id'],
                'begin_location' => $cover['begin_location'],
                'end_location'   => $cover['end_location'],
            ])->first();

            if ($Cover) $Cover->update($cover);
            else {
                $Cover = RoadCover::create($cover);
                $Covers[] = $Cover;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->info(PHP_EOL);

        return count($Covers);
    }
    
}