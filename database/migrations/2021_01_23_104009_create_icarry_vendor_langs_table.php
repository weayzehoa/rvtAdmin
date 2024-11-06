<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIcarryVendorLangsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('DB_MIGRATE_CREATE_ICARRY_VENDOR_LANGS')) {
            Schema::connection('icarry')->create('vendor_langs', function (Blueprint $table) {
                $table->id();
                $table->integer('vendor_id')->unsigned()->comment('vendor資料表id');
                $table->string('lang')->comment('語言代號'); //以國家代號, ex: en,jp,kr,th...
                $table->string('name')->nullable()->comment('名稱');
                $table->text('summary')->nullable()->comment('簡介');
                $table->longText('description')->nullable()->comment('描述');
                $table->string('curation')->nullable()->comment('策展簡介');
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
        if (env('DB_MIGRATE_CREATE_ICARRY_VENDOR_LANGS')) {
            Schema::connection('icarry')->dropIfExists('vendor_langs');
        }
    }
}
