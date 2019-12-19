<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExportsepaItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(strtolower('exportsepa_items'), function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('exportsepa_id')->index();
            $table->unsignedInteger('invoice_id')->index();

            $table->timestamps();
            $table->foreign('exportsepa_id')->references('id')->on('exportsepa')->onDelete('cascade');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
