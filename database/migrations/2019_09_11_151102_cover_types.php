<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoverTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('covers_types')) Schema::create('covers_types', function(Blueprint $table) {
            $table->increments('id')->unsignet();
            $table->integer('pavement_layer_type_id')->nullable();
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
        if (Schema::hasTable('covers_types')) Schema::drop('covers_types'); 
    }
}
