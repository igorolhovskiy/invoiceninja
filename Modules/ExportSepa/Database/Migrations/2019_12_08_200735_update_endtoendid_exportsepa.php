<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateEndtoendidExportsepa extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $exportsepa = DB::table('exportsepa')->orderBy('created_at')->get();
        foreach($exportsepa as $sepa) {
            $account = DB::table('accounts')->find($sepa->account_id);
            $endtoendid = $account->sepa_end_to_end_current_id;
            $items = DB::table('exportsepa_items')
                ->where('exportsepa_id', $sepa->id)
                ->orderBy('created_at')
                ->get();
            foreach($items as $item) {
                $endtoendid += 1;
                DB::table('exportsepa_items')
                    ->where('id', $item->id)
                    ->update(['endtoendid' => $endtoendid]);
            }
            DB::table('accounts')
                ->where('id', $account->id)
                ->update(['sepa_end_to_end_current_id' => $endtoendid]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
