<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddtoIcarryPayMethodTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('DB_MIGRATE_ADD_TO_ICARRY_PAY_METHOD_TABLE')) {
            Schema::connection('icarry')->table('pay_method', function (Blueprint $table) {
                $table->string('name_en')->comment('英文名稱');
                $table->string('value')->comment('值');
                $table->string('type')->comment('類別');
                $table->string('image')->nullable()->comment('圖片');
                $table->boolean('is_on')->default(0)->comment('0:下線, 1:上線');
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
        if (env('DB_MIGRATE_ADD_TO_ICARRY_PAY_METHOD_TABLE')) {
            Schema::connection('icarry')->table('pay_method', function (Blueprint $table) {
                $table->dropColumn('name_en');
                $table->dropColumn('value');
                $table->dropColumn('type');
                $table->dropColumn('image');
                $table->dropColumn('is_on');
                $table->dropColumn('sort');
                $table->dropColumn('created_at');
                $table->dropColumn('updated_at');
            });
        }
    }
}
