<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PlanCurves extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('road_plan_curves')) Schema::create('road_plan_curves', function(Blueprint $table) {
            $table->increments('id')->unsignet();
            $table->unsignedInteger('road_id')->nullable()->index('plan_curves_road_id_foreign');
            $table->integer('begin_location')->nullable();
            $table->integer('end_location')->nullable();
            $table->foreign('road_id', 'plan_curves_road_id_foreign')->references('id')->on('roads')->onUpdate('NO ACTION')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('road_plan_curves')) {
            Schema::table('road_plan_curves', function (Blueprint $table) {
                $table->dropForeign('plan_curves_road_id_foreign');
            });
            Schema::drop('road_plan_curves');
        }
        
    }
}
