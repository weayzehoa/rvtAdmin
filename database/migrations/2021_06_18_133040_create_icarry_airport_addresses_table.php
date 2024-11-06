<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIcarryAirportAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('DB_MIGRATE_ICARRY_AIRPORT_ADDRESSES')) {
            Schema::connection('icarry')->create('airport_addresses', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('country_id')->comment('國家id');
                $table->string('name')->comment('機場地址名稱');
                $table->string('value')->comment('value');
                $table->string('name_en')->nullable()->comment('機場地址英文名稱');
                $table->string('pickup_time_start',10)->nullable()->comment('提貨開始時間');
                $table->string('pickup_time_end',10)->nullable()->comment('提貨結束時間');
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
        if (env('DB_MIGRATE_ICARRY_AIRPORT_ADDRESSES')) {
            Schema::dropIfExists('airport_addresses');
        }
    }
}
