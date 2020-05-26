<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReqColDateColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('exportsepa', function (Blueprint $table) {
            $table->date('requested_collection_date')->after('client_id')->nullable();
        });
        DB::table('exportsepa')
            ->update(['requested_collection_date' => DB::raw('DATE_ADD(created_at, INTERVAL 1 DAY)')]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('', function (Blueprint $table) {

        });
    }
}
