<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIcarryCurationImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('DB_MIGRATE_ICARRY_CURATION_IMAGES')) {
            Schema::connection('icarry')->create('curation_images', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('curation_id')->comment('策展id');
                $table->string('style')->comment('隸屬哪個版型');
                $table->string('open_method')->nullable()->comment('開啟方式');
                $table->string('main_title')->nullable()->comment('主標題');
                $table->boolean('show_main_title')->default(1)->comment('顯示主標題');
                $table->string('sub_title')->nullable()->comment('副標題');
                $table->boolean('show_sub_title')->default(1)->comment('顯示副標題');
                $table->text('caption')->nullable()->comment('說明文案');
                $table->string('text_position')->nullable()->comment('文字位置');
                $table->longText('modal_content')->nullable()->comment('Modal內容');
                $table->boolean('row')->nullable()->default(1)->comment('第幾列數');
                $table->boolean('url_open_window')->nullable()->default(0)->comment('另開視窗, 0:關閉 1:啟用');
                $table->string('url')->nullable()->comment('連結位置');
                $table->string('old_url')->nullable()->comment('舊連結位置');
                $table->string('image')->nullable()->comment('圖片');
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
        if (env('DB_MIGRATE_ICARRY_CURATION_IMAGES')) {
            Schema::connection('icarry')->dropIfExists('curation_images');
        }
    }
}
