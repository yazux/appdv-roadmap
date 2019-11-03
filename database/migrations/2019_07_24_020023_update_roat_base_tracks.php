<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateRoatBaseTracks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('road_base_tracks')) Schema::table('road_base_tracks', function (Blueprint $table) {
            $table->integer('track_index')->nullable()->index('base_track_track_index');
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
            $table->dropForeign('base_track_track_index');
            $table->dropColumn(['track_index']);
        });
    }
}
