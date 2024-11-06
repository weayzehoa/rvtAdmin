<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddtoIcarryPromoBoxTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('DB_MIGRATE_ADD_TO_ICARRY_PROMO_BOX_TABLE')) {
            Schema::connection('icarry')->table('promo_box', function (Blueprint $table) {
                $table->string('title_kr',150)->nullable()->comment('韓文名稱');
                $table->string('text_teaser_kr',300)->nullable()->comment('韓文內容');
                $table->string('text_complete_kr',2000)->nullable()->comment('韓文完整內容');
                $table->string('title_th',150)->nullable()->comment('泰文名稱');
                $table->string('text_teaser_th',300)->nullable()->comment('泰文內容');
                $table->string('text_complete_th',2000)->nullable()->comment('泰文完整內容');
                $table->dateTime('start_time')->nullable()->comment('開始時間');
                $table->dateTime('end_time')->nullable()->comment('結束時間');
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
        if (env('DB_MIGRATE_ADD_TO_ICARRY_PROMO_BOX_TABLE')) {
            Schema::connection('icarry')->table('promo_box', function (Blueprint $table) {
                $table->dropColumn('title_kr');
                $table->dropColumn('text_teaser_kr');
                $table->dropColumn('text_complete_kr');
                $table->dropColumn('title_th');
                $table->dropColumn('text_teaser_th');
                $table->dropColumn('text_complete_th');
                $table->dropColumn('start_time');
                $table->dropColumn('end_time');
            });
        }
    }
}
