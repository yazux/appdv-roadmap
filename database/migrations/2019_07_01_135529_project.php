<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Project extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('projects')) Schema::create('projects', function(Blueprint $table) {
            $table->increments('id')->unsignet();
            $table->string('name')->nullable();
            $table->text('description', 50000)->nullable();
            $table->decimal('percent')->nullable()->default(0);
            $table->text('photo')->nullable();
            $table->text('photos')->nullable();
            $table->timestamp('start_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('end_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->text('status')->nullable();
            $table->text('docs')->nullable();
            $table->unsignedInteger('vendor_id')->nullable()->index('projects_vendor_id_foreign');
            $table->timestamps();
        });

        if (Schema::hasTable('projects')) Schema::table('projects', function(Blueprint $table) {
            $table->foreign('vendor_id', 'projects_vendor_id_foreign')->references('id')->on('vendors')->onUpdate('NO ACTION')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('projects')) Schema::table('projects', function(Blueprint $table) {
            $table->dropForeign('projects_vendor_id_foreign');
        });

        if (Schema::hasTable('projects')) Schema::drop('projects');
    }
}
