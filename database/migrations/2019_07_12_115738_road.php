<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Road extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('roads')) Schema::create('roads', function(Blueprint $table) {
            $table->increments('id')->unsignet();
            $table->string('name')->nullable();
            $table->string('full_name')->nullable();
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
        if (Schema::hasTable('roads')) Schema::drop('roads');
    }
}
