<?php

namespace App\Console\Commands;

use Exception;
use App\Classes\Importer;
use Illuminate\Console\Command;
use App\Classes\Reader;
use App\Classes\RoadTrackImporter;
use App\Road;
use App\RoadBaseTrack;
use App\DiagnosticTotalsRating;
use App\Classes\DiagnosticImporter;
use DB;

class ImportDiagnostic extends Command
{
    /** 9W1f1H5i dc7b19f2d890e6aa3863.xml
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:diagnostic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import diagnostic';

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
        $this->info('[Импорт треков]: Старт...');
        $this->info('[Импорт треков]: Чтение файла...');

        $Reader = new Reader();
        $Roads = Road::whereNotNull('id')->get();
        $Importer = new DiagnosticImporter($Reader);
        $Count = 0;
        DiagnosticTotalsRating::whereNotNull('id')->delete();
        
        $context = $this;
        $context->info('[Импорт записей диагностики]: Будет обратанно дорог: ' . count($Roads));
        $bar = $this->output->createProgressBar(count($Roads));
        $Roads->transform(function ($Road) use ($Importer, $context, $bar, $Count) {
            $this->info(PHP_EOL);
            $context->info('[Импорт записей диагностики]: Обработка дороги: ' . $Road->name);
            $context->info('[Импорт записей диагностики]: Чтение файла по дороге: ' . $Road->name);
            $Data = $Importer->getDiagnostic($Road->id);

            if ($Data && count($Data)) {
                $context->info('[Импорт записей диагностики]: Файл прочитан, количество записей: ' . count($Data));
                $Count += $context->importData($Road, $Data, $context, $Importer);
            }
            
            $bar->advance();
            return $Road;
        });
        $bar->finish();
        $this->info(PHP_EOL);
        $context->info('[Импорт записей диагностики]: Импорт завешен, обработанно дорог: ' . count($Roads) . ', обработанно записей: ' . $Count);
    }

    public function importData($Road, $Data, $context, $Importer)
    {
        $this->info('[Импорт записей диагностики]: Начат импорт записей по дороге: ' . $Road->name);

        $chunkedTracks = array_chunk($Data, 500);
        $this->info('[Импорт записей диагностики]: Чанки ' . count($chunkedTracks));
        $bar = $this->output->createProgressBar(count($chunkedTracks));
        $log = [];        
        $Items = [];
        foreach ($chunkedTracks as $chunk) {
            //DB::transaction(function() use ($chunk, $Data, $Road, $Importer, $bar) {
                DB::beginTransaction();
                $ItemsToAdd = [];
                foreach ($chunk as $item) {
                    if ($item['begin_location'] && $item['end_location'] && $item['is_acceptable']) {
                        $item['road_id'] = $Road->id;
                        $item['is_acceptable'] = (int) $item['is_acceptable'];

                        $Exist = DiagnosticTotalsRating::where([
                            'begin_location' => $item['begin_location'],
                            'end_location'   => $item['end_location'],
                            'road_id'        => $Road->id
                        ])->first();
                        if (!$Exist) $ItemsToAdd[] = $item;
                        else $Exist->update($item);
                    }
                    //$Data[] = $Importer->addTrack($item, $Road->id);
                }
                DB::commit();

                DB::beginTransaction();
                foreach ($ItemsToAdd as $item) $Items[] = DiagnosticTotalsRating::create($item);
                DB::commit();

            //});
            $bar->advance();
        }
        $bar->finish();
        $this->info(PHP_EOL);
        return count($Items);
    }
}