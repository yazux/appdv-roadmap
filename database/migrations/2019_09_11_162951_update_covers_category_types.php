<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCoversCategoryTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('road_category_types')) {
            Schema::table('road_category_types', function ($table) {
                $table->unsignedInteger('dir_road_category_id')->nullable()->change();
            });
        }

        if (Schema::hasTable('covers_types')) {
            Schema::table('covers_types', function ($table) {
                $table->unsignedInteger('pavement_layer_type_id')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('road_category_types')) {
            Schema::table('road_category_types', function ($table) {
                $table->integer('dir_road_category_id')->nullable()->change();
            });
        }

        if (Schema::hasTable('covers_types')) {
            Schema::table('covers_types', function ($table) {
                $table->integer('pavement_layer_type_id')->nullable()->change();
            });
        }
    }
}
