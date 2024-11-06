<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIcarryCategoryLangsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('DB_MIGRATE_ICARRY_CATEGORY_LANGS')) {
            Schema::connection('icarry')->create('category_langs', function (Blueprint $table) {
                $table->id();
                $table->integer('category_id')->unsigned()->comment('category資料表id');
                $table->string('lang')->comment('語言代號'); //以國家代號, ex: en,jp,kr,th...
                $table->string('name')->nullable()->comment('名稱');
                $table->string('intro')->nullable()->comment('介紹');
                $table->timestamps();
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
        if (env('DB_MIGRATE_ICARRY_CATEGORY_LANGS')) {
            Schema::connection('icarry')->dropIfExists('category_langs');
        }
    }
}
