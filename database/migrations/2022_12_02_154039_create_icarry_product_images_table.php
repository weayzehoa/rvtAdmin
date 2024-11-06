<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIcarryProductImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('DB_MIGRATE_ICARRY_PRODUCT_IMAGES')) {
            Schema::connection('icarry')->create('product_images', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('product_id')->comment('產品代號');
                $table->string('filename')->comment('檔案名稱');
                $table->float('sort', 11, 1)->unsigned()->nullable()->default(999999)->comment('排序');
                $table->boolean('is_top')->unsigned()->nullable()->default(0)->comment('置頂, 1:Yes 0:No');
                $table->boolean('is_on')->unsigned()->nullable()->default(0)->comment('啟用, 1:啟用 0:停用');
                $table->timestamps();
                //使用軟刪除
                $table->softDeletes();
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
        if (env('DB_MIGRATE_ICARRY_PRODUCT_IMAGES')) {
            Schema::connection('icarry')->dropIfExists('product_images');
        }
    }
}
