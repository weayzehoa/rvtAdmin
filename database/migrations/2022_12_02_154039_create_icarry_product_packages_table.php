<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIcarryProductPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('DB_MIGRATE_ICARRY_PRODUCT_PACKAGES')) {
            Schema::connection('icarry')->create('product_packages', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('product_id')->comment('商品id');
                $table->unsignedInteger('product_model_id')->comment('款式id');
                $table->timestamps();
                //使用軟刪除
                $table->softDeletes();
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
        if (env('DB_MIGRATE_ICARRY_PRODUCT_PACKAGES')) {
            Schema::connection('icarry')->dropIfExists('product_packages');
        }
    }
}
