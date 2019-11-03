<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DiagnosticTracks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('diagnostics_tracks')) Schema::create('diagnostics_tracks', function(Blueprint $table) {

            $table->increments('id')->unsignet();
            $table->unsignedInteger('diagnostic_id')->nullable()->index('diagnostics_tracks_diagnostic_id_foreign');
            $table->unsignedInteger('track_id')->nullable()->index('diagnostics_tracks_track_id_foreign');

            $table->foreign('diagnostic_id', 'diagnostics_tracks_diagnostic_id_foreign')->references('id')->on('diagnostic_totals_rating')->onUpdate('NO ACTION')->onDelete('SET NULL');
            $table->foreign('track_id',           'diagnostics_tracks_track_id_foreign')->references('id')->on('road_base_tracks')->onUpdate('NO ACTION')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('diagnostics_tracks')) {
            Schema::table('diagnostics_tracks', function (Blueprint $table) {
                $table->dropForeign('diagnostics_tracks_diagnostic_id_foreign');
                $table->dropForeign('diagnostics_tracks_track_id_foreign');
            });
            Schema::drop('diagnostics_tracks');
        }
    }
}
