<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Vendor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('vendors')) Schema::create('vendors', function(Blueprint $table) {
            $table->increments('id')->unsignet();
            $table->string('name')->nullable();
            $table->string('logo')->nullable();
            $table->string('hero_image')->nullable();
            $table->text('description', 50000)->nullable();
            $table->integer('sort')->nullable()->default(100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('vendors')) Schema::drop('vendors');
    }
}
