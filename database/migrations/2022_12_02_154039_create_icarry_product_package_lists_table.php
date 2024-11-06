<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIcarryProductPackageListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('DB_MIGRATE_ICARRY_PRODUCT_PACKAGE_LISTS')) {
            Schema::connection('icarry')->create('product_package_lists', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('product_package_id')->comment('組合商品id');
                $table->unsignedInteger('product_model_id')->comment('商品model_id');
                $table->unsignedInteger('quantity')->default(0)->comment('數量');
                $table->timestamps();
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
        if (env('DB_MIGRATE_ICARRY_PRODUCT_PACKAGE_LISTS')) {
            Schema::connection('icarry')->dropIfExists('product_package_lists');
        }
    }
}
