<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Pages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('pages')) Schema::create('pages', function(Blueprint $table) {
            $table->increments('id')->unsignet();
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->text('text')->nullable();
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
        if (Schema::hasTable('pages')) Schema::drop('pages');
    }
}
