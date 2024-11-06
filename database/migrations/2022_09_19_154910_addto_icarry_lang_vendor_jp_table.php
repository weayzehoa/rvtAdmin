<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddtoIcarryLangVendorJpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('DB_MIGRATE_ADD_TO_ICARRY_LANG_VENDOR_JP_TABLE')) {
            Schema::connection('icarryLang')->table('vendor_jp', function (Blueprint $table) {
                $table->string('bill_email')->nullable()->comment('對帳通知信箱');
                $table->string('notify_email')->nullable()->comment('採購通知信箱');
                $table->string('img_cover')->nullable()->comment('封面圖');
                $table->string('img_logo')->nullable()->comment('Logo圖');
                $table->string('img_site')->nullable()->comment('主視圖');
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
        if (env('DB_MIGRATE_ADD_TO_ICARRY_LANG_VENDOR_JP_TABLE')) {
            Schema::connection('icarryLang')->table('vendor_jp', function (Blueprint $table) {
                $table->dropColumn('bill_email');
                $table->dropColumn('notify_email');
                $table->dropColumn('img_cover');
                $table->dropColumn('img_logo');
                $table->dropColumn('img_site');
            });
        }
    }
}
