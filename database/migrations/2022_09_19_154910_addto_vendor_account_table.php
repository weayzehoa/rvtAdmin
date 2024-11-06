<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddtoVendorAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('DB_MIGRATE_ADD_TO_VENDOR_ACCOUNT_TABLE')) {
            Schema::connection('icarry')->table('vendor_account', function (Blueprint $table) {
                $table->string('password',40)->nullable()->comment('密碼');
                $table->string('icarry_token')->nullable()->comment('token');
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
        if (env('DB_MIGRATE_ADD_TO_VENDOR_ACCOUNT_TABLE')) {
            Schema::connection('icarry')->table('vendor_account', function (Blueprint $table) {
                $table->dropColumn('password');
                $table->dropColumn('icarry_token');
            });
        }
    }
}
