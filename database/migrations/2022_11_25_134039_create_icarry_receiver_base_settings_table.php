<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIcarryReceiverBaseSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('DB_MIGRATE_RECEIVER_BASE_SETTINGS')) {
            Schema::connection('icarry')->create('receiver_base_settings', function (Blueprint $table) {
                $table->id();
                $table->date('select_date')->comment('選擇時間');
                $table->boolean('week')->nullable()->default(0)->comment('星期幾');
                $table->string('type',10)->comment('類別, call 叫貨, logistics 物流, out 出貨, pickup 提貨');
                $table->boolean('is_ok')->default(0)->comment('0:可 1:不可');
                $table->string('memo')->nullable()->comment('備註');
                $table->unsignedInteger('admin_id')->comment('修改的管理員id');
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
        if (env('DB_MIGRATE_RECEIVER_BASE_SETTINGS')) {
            Schema::connection('icarry')->dropIfExists('receiver_base_settings');
        }
    }
}
