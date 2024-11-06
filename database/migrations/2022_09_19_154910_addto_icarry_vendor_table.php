<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddtoIcarryVendorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('DB_MIGRATE_ADD_TO_ICARRY_VENDOR_TABLE')) {
            Schema::connection('icarry')->table('vendor', function (Blueprint $table) {
                $table->string('img_cover')->nullable()->comment('封面圖');
                $table->string('img_logo')->nullable()->comment('Logo圖');
                $table->string('img_site')->nullable()->comment('主視圖');
                $table->string('curation')->nullable()->comment('策展簡介');
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
        if (env('DB_MIGRATE_ADD_TO_ICARRY_VENDOR_TABLE')) {
            Schema::connection('icarry')->table('vendor', function (Blueprint $table) {
                $table->dropColumn('img_cover');
                $table->dropColumn('img_logo');
                $table->dropColumn('img_site');
                $table->dropColumn('curation');
            });
        }
    }
}
