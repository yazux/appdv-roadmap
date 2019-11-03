<?php

namespace App\Console\Commands;

use Exception;
use App\Classes\Importer;
use Illuminate\Console\Command;
use App\Classes\Reader;
use App\Classes\RoadImporter;
use App\Classes\PlanCurvesImporter;
use App\Classes\CoversTypeImporter;
use App\Road;
use App\CoversType;

class ImportCoversType extends Command
{
    /** 9W1f1H5i dc7b19f2d890e6aa3863.xml
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:coverstype';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import cover stype';

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
        $this->info('[Импорт типов покрытий]: Старт...');
        $this->info('[Импорт типов покрытий]: Чтение файла...');
        
        $Reader   = new Reader();
        $Importer = new CoversTypeImporter($Reader);
        $Types = $Importer->getCoversTypes();
        if ($Types && count($Types)) {
            $this->info('[Импорт типов покрытий]: Файл прочитан, количество типов: ' . count($Types));
            $Count = 0;
            $bar = $this->output->createProgressBar(count($Types));
            foreach ($Types as $Type) {
                $Importer->addCoversType($Type);
                $bar->advance();
                $Count++;
            }
            $bar->finish();
        }
        $this->info(PHP_EOL);
        $this->info('[Импорт типов покрытий]: готово, добавленно типов: ' . $Count);
    }
}