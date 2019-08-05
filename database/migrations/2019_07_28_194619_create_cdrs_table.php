<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCdrsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cdrs', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('account_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('client_id')->nullable();
            $table->unsignedInteger('import_colt_id');
            $table->unsignedInteger('invoice_id')->nullable();
            $table->string('did');
            $table->datetime('datetime');
            $table->string('dst');
            $table->string('dur');
            $table->decimal('cost', 12, 2)->default(0);
            $table->boolean('done')->default(0);
            
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');  
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('import_colt_id')->references('id')->on('import_colts')->onDelete('cascade');
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
        Schema::dropIfExists('cdrs');
    }
}
