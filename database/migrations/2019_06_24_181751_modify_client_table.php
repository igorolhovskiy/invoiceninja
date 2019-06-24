<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyClientTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->integer('astpp_client_id')->nullable();
            $table->index('astpp_client_id');
            $table->string('colt_dids')->nullable();
            $table->decimal('call_cost_limit', 12, 2)->nullable();
            $table->string('crm_code')->nullable();
            $table->string('iban')->nullable();
            $table->string('bic')->nullable();
            $table->string('sepa')->nullble();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            //
        });
    }
}
