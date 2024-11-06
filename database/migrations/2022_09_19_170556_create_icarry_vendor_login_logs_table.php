<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateiCarryVendorLoginLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('DB_MIGRATE_ICARRY_VENDOR_LOGIN_LOGS')) {
            Schema::connection('icarry')->create('vendor_login_logs', function (Blueprint $table) {
                $table->id();
                $table->integer('vendor_account_id')->nullable()->comment('商家管理者id');
                $table->string('result')->nullable()->comment('登入結果');
                $table->string('account',50)->nullable()->comment('失敗帳號紀錄');
                $table->string('ip',20)->nullable()->comment('來源IP');
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
        if (env('DB_MIGRATE_ICARRY_VENDOR_LOGIN_LOGS')) {
            Schema::connection('icarry')->dropIfExists('vendor_login_logs');
        }
    }
}
