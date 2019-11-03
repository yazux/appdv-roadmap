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
use DB;

class ImportTracks extends Command
{
    /** 9W1f1H5i dc7b19f2d890e6aa3863.xml
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:tracks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import tracks';

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
        //$Roads = Road::whereNotNull('id')->skip(3)->limit(1)->get();
        //$Roads = Road::whereNotNull('id')->limit(2)->get();
        $Roads = Road::whereNotNull('id')->get();
        $Importer = new RoadTrackImporter($Reader);
        $TracksCount = 0;

        //RoadBaseTrack::whereNotNull('id')->delete();
        
        $context = $this;
        $context->info('[Импорт треков]: Будет обратанно дорог: ' . count($Roads));
        $bar = $this->output->createProgressBar(count($Roads));
        $Roads->transform(function ($Road) use ($Importer, $context, $bar, $TracksCount) {
            $this->info(PHP_EOL);
            $context->info('[Импорт треков]: Обработка дороги: ' . $Road->name);
            $context->info('[Импорт треков]: Чтение файла по дороге: ' . $Road->name);
            $tracks = $Importer->getTracks($Road->id);
            if ($tracks && count($tracks)) {
                $existTracks = RoadBaseTrack::where('road_id', $Road->id)->select('id')->count();

                $context->info('[Импорт треков]: Файл прочитан, количество треков: ' . count($tracks));
                $context->info('[Импорт треков]: Количество уже добавленных треков: ' . $existTracks);

                if (count($tracks) == $existTracks) $context->info('[Импорт треков]: Количество треков в импорте совпадает с уже добавленными, пропускаем импорт треков по дороге: ' . $Road->name);
                else $TracksCount += $context->importTracks($Road, $tracks, $context, $Importer);
            }
            $bar->advance();
            return $Road;
        });
        $bar->finish();
        $this->info(PHP_EOL);
        $context->info('[Импорт треков]: Импорт завешен, обработанно дорог: ' . count($Roads) . ', обработанно треков: ' . $TracksCount);
    }

    public function importTracks($Road, $tracks, $context, $Importer)
    {
        $this->info('[Импорт треков]: Начат импорт треков по дороге: ' . $Road->name);

        $chunkedTracks = array_chunk($tracks, 5000);
        $this->info('[Импорт треков]: Чанки ' . count($chunkedTracks));
        $bar = $this->output->createProgressBar(count($chunkedTracks));
        $log = [];        
        $Tracks = [];
        foreach ($chunkedTracks as $chunk) {
            //DB::transaction(function() use ($chunk, $Tracks, $Road, $Importer, $bar) {
                DB::beginTransaction();
                $TracksToAdd = [];
                foreach ($chunk as $track) {
                    if (($track['picket'] || (int) $track['picket'] === 0) && $track['latitude'] && $track['longitude']) {
                        $track['road_id'] = $Road->id;
                        //$track['track_index'] = $Road->id . $track['picket'];
                        //$Exist = RoadBaseTrack::where('track_index', $track['track_index'])->where('road_id', $Road->id)->first();
                        $Exist = RoadBaseTrack::where('picket', $track['picket'])->where('road_id', $Road->id)->first();
                        if (!$Exist) $TracksToAdd[] = $track;
                    }
                    //$Tracks[] = $Importer->addTrack($track, $Road->id);
                }
                DB::commit();

                DB::beginTransaction();
                foreach ($TracksToAdd as $track) $Tracks[] = RoadBaseTrack::create($track);
                DB::commit();

            //});
            $bar->advance();
        }
        $bar->finish();
        $this->info(PHP_EOL);
        return count($Tracks);
    }
}