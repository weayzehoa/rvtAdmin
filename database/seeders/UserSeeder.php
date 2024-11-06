<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\iCarryUser as UserDB;
use DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (env('DB_MIGRATE_ADD_TO_ICARRY_USERS_TABLE')) {
            DB::connection('icarry')->statement("update users set password = pwd, remember_me = UUID() where 1");
            echo "Users 遷移完成\n";
        }
    }
}
