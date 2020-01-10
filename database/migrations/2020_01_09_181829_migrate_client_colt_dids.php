<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MigrateClientColtDids extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_colt_dids', function (Blueprint $table) {
            //
        });
        $clients = DB::table('clients')
            ->select('id', 'colt_dids')
            ->whereNotNull('colt_dids')
            ->get();
        foreach($clients as $client) {
            $dids = preg_split("/[,;]+/", $client->colt_dids);
            foreach($dids as $did) {
                DB::table('client_colt_dids')
                    ->insert([
                        'client_id' => $client->id,
                        'did' => trim($did)
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_colt_dids', function (Blueprint $table) {
            //
        });
    }
}
