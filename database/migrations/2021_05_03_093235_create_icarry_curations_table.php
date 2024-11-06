<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIcarryCurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('DB_MIGRATE_ICARRY_CURATIONS')) {
            Schema::connection('icarry')->create('curations', function (Blueprint $table) {
                $table->id();
                $table->integer('vendor_id')->nullable()->default(null)->comment('商家id');
                $table->integer('old_curation_id')->nullable()->comment('舊策展id');
                $table->string('category')->comment('策展類別 home,category');
                $table->string('main_title')->comment('主標題');
                $table->boolean('show_main_title')->default(1)->comment('顯示主標題');
                $table->string('main_title_background')->nullable()->comment('主標題背景顏色');
                $table->boolean('show_main_title_background')->default(0)->comment('顯示主標題背景顏色');
                $table->string('sub_title')->nullable()->comment('副標題');
                $table->boolean('show_sub_title')->default(1)->comment('顯示副標題');
                $table->string('background_color')->nullable()->comment('背景顏色');
                $table->string('background_image')->nullable()->comment('背景圖片');
                $table->string('background_css')->nullable()->comment('背景CSS');
                $table->string('show_background_type',20)->nullable()->default('off')->comment('顯示背景類型');
                $table->boolean('columns')->nullable()->default(4)->comment('layout欄數');
                $table->boolean('rows')->nullable()->default(1)->comment('layout列數');
                $table->text('caption')->nullable()->comment('說明文案');
                $table->string('type')->comment('策展版型, image,vendor,product,block,event');
                $table->string('url')->nullable()->comment('策展頁面連結');
                $table->string('old_url')->nullable()->comment('舊策展頁面連結');
                $table->string('old_text_layout')->nullable()->comment('舊圖片位置');
                $table->boolean('url_open_window')->nullable()->default(0)->comment('另開視窗, 0:關閉 1:啟用');
                $table->boolean('show_url')->nullable()->default(0)->comment('顯示連結/按鈕, 0:關閉 1:啟用');
                $table->dateTime('start_time')->nullable()->comment('開始時間');
                $table->dateTime('end_time')->nullable()->comment('結束時間');
                $table->boolean('is_on')->default(0)->comment('0:關閉 1:啟用');
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
        if (env('DB_MIGRATE_ICARRY_CURATIONS')) {
            Schema::connection('icarry')->dropIfExists('curations');
        }
    }
}
