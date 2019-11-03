<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RoadCovers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('road_covers')) Schema::create('road_covers', function(Blueprint $table) {
            $table->increments('id')->unsignet();
            $table->unsignedInteger('road_id')->nullable()->index('road_covers_road_id_foreign');
            $table->integer('begin_location')->nullable();
            $table->integer('end_location')->nullable();
            $table->unsignedInteger('pavement_layer_type_id')->nullable()->index('road_covers_layer_type_id_foreign');

            $table->foreign('road_id', 'road_covers_road_id_foreign')->references('id')->on('roads')->onUpdate('NO ACTION')->onDelete('SET NULL');
            $table->foreign('pavement_layer_type_id', 'road_covers_layer_type_id_foreign')->references('pavement_layer_type_id')->on('covers_types')->onUpdate('NO ACTION')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('road_covers')) {
            Schema::table('road_covers', function (Blueprint $table) {
                $table->dropForeign('road_covers_road_id_foreign');
                $table->dropForeign('pavement_layer_type_id');
            });
            Schema::drop('road_covers');
        }
    }
}
