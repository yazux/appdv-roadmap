<?php

namespace App\Console\Commands;

use DB;
use Cache;
use Exception;
use App\Classes\Importer;
use Illuminate\Console\Command;
use App\Road;
use App\User;
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

class StartClear extends Command
{
    /** 9W1f1H5i dc7b19f2d890e6aa3863.xml
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'start:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start clear';

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
        $Start = time();

        $Items = RoadCover::whereNotNull('id')->get();
        $this->info('Удаление покрытий: ' . count($Items));
        $this->clear($Items);
        $this->info(PHP_EOL);

        $Items = RoadCategory::whereNotNull('id')->get();
        $this->info('Удаление категорий: ' . count($Items));
        $this->clear($Items);
        $this->info(PHP_EOL);

        $Items = DiagnosticTotalsRating::whereNotNull('id')->get();
        $this->info('Удаление данных диагностики: ' . count($Items));
        $this->clear($Items);
        $this->info(PHP_EOL);

        $Items = RoadPlanCurf::whereNotNull('id')->get();
        $this->info('Удаление кривых: ' . count($Items));
        $this->clear($Items);
        $this->info(PHP_EOL);

        $Finish = time(); 
    }

    public function clear($Items)
    {
        $bar = $this->output->createProgressBar(count($Items));
        foreach ($Items as $Item) {
            $Item->delete();
            $bar->advance();
        }
        $bar->finish();
    }
}