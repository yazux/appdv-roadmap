<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RoadCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('road_categories')) Schema::create('road_categories', function(Blueprint $table) {
            $table->increments('id')->unsignet();
            $table->unsignedInteger('road_id')->nullable()->index('road_categories_road_id_foreign');
            $table->integer('begin_location')->nullable();
            $table->integer('end_location')->nullable();
            $table->unsignedInteger('dir_road_category_id')->nullable()->index('road_categories_category_id_foreign');

            $table->foreign('road_id', 'road_categories_road_id_foreign')->references('id')->on('roads')->onUpdate('NO ACTION')->onDelete('SET NULL');
            $table->foreign('dir_road_category_id', 'road_categories_category_id_foreign')->references('dir_road_category_id')->on('road_category_types')->onUpdate('NO ACTION')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('road_categories')) {
            Schema::table('road_categories', function (Blueprint $table) {
                $table->dropForeign('road_categories_road_id_foreign');
                $table->dropForeign('road_categories_category_id_foreign');
            });
            Schema::drop('road_road_categories');
        }
    }
}
