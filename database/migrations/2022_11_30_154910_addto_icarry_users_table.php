<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddtoIcarryUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('DB_MIGRATE_ADD_TO_ICARRY_USERS_TABLE')) {
            Schema::connection('icarry')->table('users', function (Blueprint $table) {
                $table->boolean('is_mark')->nullable()->default(0)->comment('標記')->index();
                $table->string('password')->comment('密碼');
                $table->string('remember_me')->nullable()->comment('記住我token');

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
        if (env('DB_MIGRATE_ADD_TO_ICARRY_USERS_TABLE')) {
            Schema::connection('icarry')->table('users', function (Blueprint $table) {
                $table->dropColumn('is_mark');
                $table->dropColumn('password');
                $table->dropColumn('remember_me');
            });
        }
    }
}
