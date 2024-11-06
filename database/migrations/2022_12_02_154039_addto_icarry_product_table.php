<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddtoIcarryProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('DB_MIGRATE_ADD_TO_ICARRY_PRODUCT_TABLE')) {
            Schema::connection('icarry')->table('product', function (Blueprint $table) {
                $table->string('allow_country_ids')->nullable()->comment('寄送國家ids');
                $table->integer('unit_name_id')->nullable()->comment('單位名稱id');
                $table->integer('from_country_id')->nullable()->comment('發貨地區國家id');
                $table->boolean('model_type')->nullable()->comment('款式類型');
                $table->string('curation_text_top')->nullable()->comment('策展上方文字');
                $table->string('curation_text_bottom')->nullable()->comment('策展下方文字');
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
        if (env('DB_MIGRATE_ADD_TO_ICARRY_PRODUCT_TABLE')) {
            Schema::connection('icarry')->table('product', function (Blueprint $table) {
                $table->dropColumn('allow_country_ids');
                $table->dropColumn('unit_name_id');
                $table->dropColumn('from_country_id');
                $table->dropColumn('model_type');
                $table->dropColumn('curation_text_top');
                $table->dropColumn('curation_text_bottom');
            });
        }
    }
}
