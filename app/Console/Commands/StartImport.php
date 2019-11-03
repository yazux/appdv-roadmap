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

class StartImport extends Command
{
    /** 9W1f1H5i dc7b19f2d890e6aa3863.xml
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'start:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start import';

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
        $this->call('import:roads');
        $this->call('import:roadcategorytype');
        $this->call('import:coverstype');
        $this->call('import:tracks');
        $this->call('import:roadcovers');
        $this->call('import:diagnostic');
        $this->call('import:curves');
        $this->call('import:categories');
        $this->call('build:relations');
        $Finish = time(); 
    }
}