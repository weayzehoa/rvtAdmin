<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddtoIcarryCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('DB_MIGRATE_ADD_TO_ICARRY_CATEGORY_TABLE')) {
            Schema::connection('icarry')->table('category', function (Blueprint $table) {
                $table->float('sort',11,1)->default(999999)->comment('排序');
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
        if (env('DB_MIGRATE_ADD_TO_ICARRY_CATEGORY_TABLE')) {
            Schema::connection('icarry')->table('category', function (Blueprint $table) {
                $table->dropColumn('sort');
                $table->dropColumn('created_at');
                $table->dropColumn('updated_at');
            });
        }
    }
}
