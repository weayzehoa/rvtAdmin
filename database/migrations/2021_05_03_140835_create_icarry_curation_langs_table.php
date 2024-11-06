<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIcarryCurationLangsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('DB_MIGRATE_ICARRY_CURATION_LANGS')) {
            Schema::connection('icarry')->create('curation_langs', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('curation_id')->comment('策展id');
                $table->string('lang')->comment('語言代號');
                $table->string('main_title')->nullable()->comment('主標題');
                $table->string('sub_title')->nullable()->comment('副標題');
                $table->text('caption')->nullable()->comment('說明文案');
                $table->longText('modal_content')->nullable()->comment('Modal內容');
                $table->timestamps();
                // === 索引 ===
                $table->index('curation_id');
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
        if (env('DB_MIGRATE_ICARRY_CURATION_LANGS')) {
            Schema::connection('icarry')->dropIfExists('curation_langs');
        }
    }
}
