<?php

namespace App\Console\Commands;

use Exception;
use App\Classes\Importer;
use Illuminate\Console\Command;
use App\Classes\Reader;
use App\Classes\RoadImporter;
use App\Classes\PlanCurvesImporter;
use App\Classes\RoadCategoryTypeImporter;
use App\Road;
use App\RoadCategoryType;

class ImportRoadCategoryType extends Command
{
    /** 9W1f1H5i dc7b19f2d890e6aa3863.xml
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:roadcategorytype';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import road category type';

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
        $this->info('[Импорт категорий дорог]: Старт...');
        $this->info('[Импорт категорий дорог]: Чтение файла...');
        
        $Reader   = new Reader();
        $Importer = new RoadCategoryTypeImporter($Reader);
        $Types = $Importer->getRoadCategoryTypes();

        if ($Types && count($Types)) {
            $this->info('[Импорт категорий дорог]: Файл прочитан, количество категорий: ' . count($Types));
            $Count = 0;
            $bar = $this->output->createProgressBar(count($Types));
            foreach ($Types as $Type) {
                $Importer->addRoadCategoryType($Type);
                $bar->advance();
                $Count++;
            }
            $bar->finish();
        }

        $this->info(PHP_EOL);
        $this->info('[Импорт категорий дорог]: готово, добавленно категорий: ' . $Count);
    }
}