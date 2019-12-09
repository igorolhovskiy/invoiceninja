<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAccountSepaSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('sepa_initiating_party_name')->nullable();
            $table->string('sepa_initiating_party_postal_address')->nullable();
            $table->string('sepa_initiating_party_identification')->nullable();
            $table->string('sepa_payment_method')->default('DD');
            $table->string('sepa_payment_sequence_type')->default('RCUR');
            $table->string('sepa_payment_iban')->nullable();
            $table->string('sepa_payment_bic')->nullable();
            $table->string('sepa_payment_creditor_id')->nullable();
            $table->string('sepa_end_to_end_current_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            //
        });
    }
}
