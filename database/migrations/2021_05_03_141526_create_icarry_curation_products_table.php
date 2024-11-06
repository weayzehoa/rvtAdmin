<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIcarryCurationProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('DB_MIGRATE_ICARRY_CURATION_PRODUCTS')) {
            Schema::connection('icarry')->create('curation_products', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('curation_id')->comment('策展id');
                $table->unsignedInteger('product_id')->comment('商品id');
                $table->float('sort', 11, 1)->default(999999)->comment('排序');
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
        if (env('DB_MIGRATE_ICARRY_CURATION_PRODUCTS')) {
            Schema::connection('icarry')->dropIfExists('curation_products');
        }
    }
}
