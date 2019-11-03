<?php

namespace App\Console\Commands;

use Exception;
use App\Classes\Importer;
use Illuminate\Console\Command;
use App\Classes\Reader;
use App\Classes\RoadTrackImporter;
use App\Road;
use App\RoadBaseTrack;
use App\RoadCategory;
use App\Classes\RoadCategoryImporter;
use DB;

class importCategories extends Command
{
    /** 9W1f1H5i dc7b19f2d890e6aa3863.xml
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import categories';

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
        $this->info('[Импорт категорий]: Старт...');
        $this->info('[Импорт категорий]: Чтение файла...');

        $Reader = new Reader();
        //$Roads = Road::whereNotNull('id')->skip(3)->limit(1)->get();
        //$Roads = Road::whereNotNull('id')->limit(2)->get();
        $Roads = Road::whereNotNull('id')->get();
        $Importer = new RoadCategoryImporter($Reader);
        $CategoryCount = 0;

        //RoadCategory::whereNotNull('id')->delete();
        
        $context = $this;
        $context->info('[Импорт категорий]: Будет обратанно дорог: ' . count($Roads));
        $bar = $this->output->createProgressBar(count($Roads));
        $Roads->transform(function ($Road) use ($Importer, $context, $bar, $CategoryCount) {
            $categories = [];
            $this->info(PHP_EOL);
            $context->info('[Импорт категорий]: Обработка дороги: ' . $Road->name);
            $context->info('[Импорт категорий]: Чтение файла по дороге: ' . $Road->name);
            $categories = $Importer->getCategories($Road->id);
            
            if ($categories && count($categories)) {
                $context->info('[Импорт категорий]: Файл прочитан, количество категорий: ' . count($categories));
                $CategoryCount += $context->importCategories($Road, $categories, $context, $Importer);
            }
            
            $bar->advance();
            return $Road;
        });
        $bar->finish();
        $this->info(PHP_EOL);
        $context->info('[Импорт категорий]: Импорт завешен, обработанно дорог: ' . count($Roads) . ', обработанно категорий: ' . $CategoryCount);
    }

    
    public function importCategories($Road, $categories, $context, $Importer)
    {
        $this->info('[Импорт категорий]: Начат импорт категорий по дороге: ' . $Road->name);
        $Categories = [];

        $this->info('[Импорт категорий]: всего категорий по дороге: ' . count($categories));
        $bar = $this->output->createProgressBar(count($categories));
        foreach ($categories as $category) {
            $category['road_id'] = $Road->id;
            $Category = RoadCategory::where([
                'road_id'        => $category['road_id'],
                'begin_location' => $category['begin_location'],
                'end_location'   => $category['end_location'],
            ])->first();

            if ($Category) $Category->update($category);
            else {
                $Category = RoadCategory::create($category);
                $Categories[] = $Category;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->info(PHP_EOL);

        return count($Categories);
    }
    
}