<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyCdrsFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cdrs', function (Blueprint $table) {
            $table->unsignedInteger('import_colt_id')->nullable()->change();
            $table->string('astpp_cdr_uniqueid')->nullable()->after('import_colt_id');

            $table->index('astpp_cdr_uniqueid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cdrs', function (Blueprint $table) {
            //
        });
    }
}
