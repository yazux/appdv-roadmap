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
use App\ImportTask;

class StartTasks extends Command
{
    /** 9W1f1H5i dc7b19f2d890e6aa3863.xml
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'start:tasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start tasks';

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
        $ProcessTask = ImportTask::where('status', 2)->orderBy('id', 'asc')->first();
        if ($ProcessTask) {
            $this->info('Одна из задач уже выполняется, закрытие задачи.');
        } else {
            $Start = time();
            $Task = ImportTask::where('status', 1)->orderBy('id', 'asc')->first();
            if (!$Task) {
                $this->info('Нет задач для выполнения');
                return;
            }
        
            $this->info('Выполнение задачи номер ' . $Task->id);
            $Task->setStatus(2);
            switch ($Task->task) {
                case 'import': 
                    $this->info('Тип задачи: Импорт данных, выполняется...');
                    $this->call('start:import');
                break;
                case 'cache': 
                    $this->info('Тип задачи: Кеширование данных, выполняется...');
                    $this->call('start:cache');    
                break;
                case 'clear':
                    $this->info('Тип задачи: Очистка данных, выполняется...');
                    $this->call('start:clear');
                break;
            }
            $Finish = time();
            $Task->setStatus(3);
            $Task->setCompleteDate();
            $time = ceil(((($Finish - $Start) / 60) . ' мин.'));
            $Task->setTime($time);

            $this->info(PHP_EOL);
            $this->info('Задача выполненна, затрачено времени: ' . (($Finish - $Start) / 60) . ' мин.');
        }
    }
}