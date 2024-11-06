<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIcarryProductLangsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('DB_MIGRATE_ICARRY_PRODUCT_LANGS')) {
            Schema::connection('icarry')->create('product_langs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('product_id')->comment('商品id');
                $table->string('lang', 10)->comment('語言代號');
                $table->string('name')->nullable()->comment('產品名稱');
                $table->string('brand')->nullable()->comment('品牌');
                $table->string('serving_size')->nullable()->comment('每份內容多少');
                $table->string('unable_buy')->nullable()->comment('無法結帳理由');
                $table->string('title')->nullable()->comment('特色(小標題)');
                $table->longText('intro')->nullable()->comment('簡介(商品描述)');
                $table->string('model_name')->nullable()->comment('款式名稱');
                $table->longText('specification')->nullable()->comment('規格');
                $table->text('curation_text_top')->nullable()->comment('策展上標文字');
                $table->text('curation_text_bottom')->nullable()->comment('策展下標文字');
                $table->timestamps();
                // === 索引 ===
                $table->index('product_id');
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
        if (env('DB_MIGRATE_ICARRY_PRODUCT_LANGS')) {
            Schema::connection('icarry')->dropIfExists('product_langs');
        }
    }
}
