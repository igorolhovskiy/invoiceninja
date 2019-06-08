<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTelcorateCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(strtolower('telcorate_codes'), function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('telcorate_id')->index();

            $table->string('code');
            $table->integer('init_seconds')->nullable();
            $table->integer('increment_seconds')->nullable();
            $table->integer('rate')->nullable();
            $table->string('description')->nullable();

            $table->timestamps();

            $table->foreign('telcorate_id')->references('id')->on('telcorates')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(strtolower('telcorate_codes'));
    }
}
