<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateProjects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('projects')) Schema::table('projects', function (Blueprint $table) {
            $table->integer('price')->nullable()->default(0);
            $table->text('videos')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('projects')) Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['price', 'videos']);
        });
    }
}
