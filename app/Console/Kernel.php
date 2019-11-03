<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\ImportRoads::class,
        Commands\ImportTracks::class,
        Commands\ImportCurves::class,
        Commands\BuildCache::class,
        Commands\ImportDiagnostic::class,
        Commands\ImportCoversType::class,
        Commands\ImportRoadCategoryType::class,
        Commands\importCategories::class,
        Commands\BuildRelations::class,
        Commands\BuildJson::class,
        Commands\UpdateRelations::class,
        Commands\StartImport::class,
        Commands\StartCache::class,
        Commands\StartClear::class,
        Commands\StartTasks::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
