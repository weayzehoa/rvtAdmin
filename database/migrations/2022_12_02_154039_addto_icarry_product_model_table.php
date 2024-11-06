<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddtoIcarryProductModelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('DB_MIGRATE_ADD_TO_ICARRY_PRODUCT_MODEL_TABLE')) {
            Schema::connection('icarry')->table('product_model', function (Blueprint $table) {
                $table->string('name_en')->nullable()->comment('英文名稱');
                $table->string('name_jp')->nullable()->comment('日文名稱');
                $table->string('name_kr')->nullable()->comment('韓文名稱');
                $table->string('name_th')->nullable()->comment('泰文名稱');
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
        if (env('DB_MIGRATE_ADD_TO_ICARRY_PRODUCT_MODEL_TABLE')) {
            Schema::connection('icarry')->table('product_model', function (Blueprint $table) {
                $table->dropColumn('name_en');
                $table->dropColumn('name_jp');
                $table->dropColumn('name_kr');
                $table->dropColumn('name_th');
            });
        }
    }
}
