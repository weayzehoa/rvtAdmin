<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIcarryCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('DB_MIGRATE_ICARRY_COUNTRIES')) {
            Schema::connection('icarry')->create('countries', function (Blueprint $table) {
                $table->id();
                $table->string('name', 20)->comment('國家名稱');
                $table->string('name_en', 100)->comment('英文名稱');
                $table->string('name_jp', 100)->nullable()->comment('日文名稱');
                $table->string('name_kr', 100)->nullable()->comment('韓文名稱');
                $table->string('name_th', 100)->nullable()->comment('泰文名稱');
                $table->string('lang', 10)->comment('語言代碼');
                $table->string('code', 5)->comment('電話國際碼');
                $table->string('sms_vendor', 20)->nullable()->comment('簡訊供應商');
                $table->float('sort', 11, 1)->default(999999)->comment('排序');
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
        if (env('DB_MIGRATE_ICARRY_COUNTRIES')) {
            Schema::connection('icarry')->dropIfExists('countries');
        }
    }
}
