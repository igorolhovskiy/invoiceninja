<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddImportColtIdToInvoice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->integer('import_colt_id')->nullable();
        });

        DB::table('invoices')
            ->join(DB::raw("(SELECT DISTINCT invoice_id, import_colt_id FROM cdrs WHERE invoice_id IS not null) cdrGrp"), 
            'invoices.id', '=', 'cdrGrp.invoice_id')
            ->update(['invoices.import_colt_id' => DB::raw('cdrGrp.import_colt_id')]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            //
        });
    }
}
