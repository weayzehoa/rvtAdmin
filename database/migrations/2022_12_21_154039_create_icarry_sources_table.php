<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIcarrySourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('DB_MIGRATE_ICARRY_SOURCES')) {
            Schema::connection('icarry')->create('sources', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('source')->comment('渠道');
                $table->string('name')->comment('名稱');
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
        if (env('DB_MIGRATE_ICARRY_SOURCES')) {
            Schema::connection('icarry')->dropIfExists('sources');
        }
    }
}
