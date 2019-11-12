<?php

namespace App\Console\Commands;

use Exception;
use App\Classes\Importer;
use Illuminate\Console\Command;
use App\Classes\Reader;
use App\Classes\RoadImporter;
use App\Classes\PlanCurvesImporter;
use App\Road;

class ImportCurves extends Command
{
    /** 9W1f1H5i dc7b19f2d890e6aa3863.xml
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:curves';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import curves';

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
        $this->info('[Импорт прямых участков]: Старт...');
        $this->info('[Импорт прямых участков]: Чтение файла...');
        
        $Reader   = new Reader();
        $Importer = new PlanCurvesImporter($Reader);

        $Reader = new Reader();
        $Roads = Road::whereNotNull('id')->get()->toArray();
        
        $this->info('[Импорт прямых участков]: Файл прочитан, количество дорог: ' . count($Roads));
        $bar = $this->output->createProgressBar(count($Roads));
        foreach ($Roads as $Road) {
            $this->info(PHP_EOL);
            $this->info('[Импорт прямых участков]: Обработка дороги: ' . $Road['name']);
            $this->addCurves($this, $Importer, $Road);
            $bar->advance();
        }
        $bar->finish();
        $this->info(PHP_EOL);
        $this->info('[Импорт прямых участков]: готово');
    }

    public function addCurves($context, $Importer, $Road) 
    {
        $curves = $Importer->getCurves($Road['id']);

        $Curves = [];
        if ($curves && count($curves)) {
            $bar = $context->output->createProgressBar(count($curves));
            $this->info('[Импорт прямых участков]: Импорт участков');
            foreach($curves as $curve) {
                $curve = [
                    'road_id'        => $Road['id'],
                    'begin_location' => (int) $curve['begin_location'],
                    'end_location'   => (int) $curve['end_location'],
                ];
                $Curves[] = $Importer->addCurve($curve, $Road['id']);
                $bar->advance();
            }
            $bar->finish();
        }

        $this->info(PHP_EOL);
        $this->info('[Импорт прямых участков]: Дорога обработанна, импортировано участков: ' . count($Curves));
    }

}