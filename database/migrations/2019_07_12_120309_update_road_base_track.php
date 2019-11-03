<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateRoadBaseTrack extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('road_base_tracks') && Schema::hasTable('roads')) Schema::table('road_base_tracks', function (Blueprint $table) {
            $table->unsignedInteger('road_id')->nullable()->index('base_track_road_id_foreign');
            $table->foreign('road_id', 'base_track_road_id_foreign')->references('id')->on('roads')->onUpdate('NO ACTION')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('road_base_tracks')) Schema::table('road_base_tracks', function (Blueprint $table) {
            $table->dropForeign('base_track_road_id_foreign');
            $table->dropColumn(['road_id']);
        });
    }
}
