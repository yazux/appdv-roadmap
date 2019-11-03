<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RoadCategoryTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('road_category_types')) Schema::create('road_category_types', function(Blueprint $table) {
            $table->increments('id')->unsignet();
            $table->unsignedInteger('dir_road_category_id')->nullable();
            $table->string('name')->nullable();
            $table->string('color_rgb')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('road_category_types')) Schema::drop('road_category_types'); 
    }
}
