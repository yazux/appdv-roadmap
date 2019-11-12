<?php

namespace App\Console\Commands;

use Exception;
use App\Classes\Importer;
use Illuminate\Console\Command;
use App\Classes\Reader;
use App\Classes\RoadImporter;

class ImportRoads extends Command
{
    /** 9W1f1H5i dc7b19f2d890e6aa3863.xml
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:roads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import roads';

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
        $this->info('[Импорт дорог]: Старт...');
        $this->info('[Импорт дорог]: Чтение файла...');
        
        $Reader   = new Reader();
        $Importer = new RoadImporter($Reader);
        $roads = $Importer->getRoadsInFile();

        $Roads = [];
        if ($roads && count($roads)) {
            $this->info('[Импорт дорог]: Файл прочитан, количество дорог: ' . count($roads));
            $bar = $this->output->createProgressBar(count($roads));
            foreach ($roads as $road) {
                $Roads[] = $Importer->addRoad($road);
                $bar->advance();
            }
            $bar->finish();
        }

        $this->info(PHP_EOL);

        $this->info('[Импорт дорог]: готово, импортировано дорог: ' . count($Roads));
    }

}