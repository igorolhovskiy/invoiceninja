<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTelcopackageCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(strtolower('telcopackage_codes'), function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('telcopackage_id')->index();
            
            $table->string('code');
            $table->string('description');

            $table->timestamps();
            $table->foreign('telcopackage_id')->references('id')->on('telcopackages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(strtolower('telcopackage_codes'));
    }
}
