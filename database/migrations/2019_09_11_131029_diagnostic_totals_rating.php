<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DiagnosticTotalsRating extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('diagnostic_totals_rating')) Schema::create('diagnostic_totals_rating', function(Blueprint $table) {
            $table->increments('id')->unsignet();
            $table->unsignedInteger('road_id')->nullable()->index('diagnostic_totals_road_id_foreign');
            $table->integer('begin_location')->nullable();
            $table->integer('end_location')->nullable();
            $table->integer('is_acceptable')->nullable();
            $table->foreign('road_id', 'diagnostic_totals_road_id_foreign')->references('id')->on('roads')->onUpdate('NO ACTION')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('diagnostic_totals_rating')) {
            Schema::table('diagnostic_totals_rating', function (Blueprint $table) {
                $table->dropForeign('diagnostic_totals_road_id_foreign');
            });
            Schema::drop('diagnostic_totals_rating');
        }
    }
}
