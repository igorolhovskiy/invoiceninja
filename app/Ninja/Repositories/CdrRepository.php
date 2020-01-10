<?php

namespace App\Ninja\Repositories;

use App\Models\Cdr;
use App\Models\ClientColtDid;
use App\Models\Clients;

use DB;
use Utils;

class CdrRepository extends BaseRepository
{

    public function getClassName()
    {
        return 'App\Models\Cdr';
    }

    public function save($data, $cdr = null)
    {
        $entity = $cdr ?: Cdr::createNew();

        $entity->fill($data);
        $entity->save();

        return $entity;
    }

    public function insertArray($data) 
    {
        if (\Auth::check()) {
            $user = \Auth::user();
            $account = \Auth::user()->account;
        } else {
            Utils::fatalError();
        }
        foreach ($data as $index => $item) {
            $data[$index]['user_id'] = $user->id;
            $data[$index]['account_id'] = $account->id;
        }

        Cdr::insert($data);
    }

    public function updateClient()
    {
        $cdrs = Cdr::scope()
            ->whereNotNull('import_colt_id')
            ->whereNull('client_id')
            ->get();
        $numberUpdatedRows = 0;
        foreach($cdrs as $cdr) {
            $client = ClientColtDid
                ::select('client_id')
                ->join('clients', 'client_colt_dids.client_id', '=', 'clients.id')
                ->where('account_id', \Auth::user()->account_id)
                ->whereRaw("'{$cdr->did}' like CONCAT(did, '%')")
                ->orderByRaw('LENGTH(did) DESC')
                ->first();
            if ($client) {
                $cdr->client_id = $client->client_id;
                $cdr->save();
                $numberUpdatedRows++;
            }
        }
        return $numberUpdatedRows;
    }

    public function findByAstppId($uniqueId) {
        return Cdr::where('astpp_cdr_uniqueid', $uniqueId)->first();
    }

    public function findRateNotFoundImportColt($importColtId) {
        return Cdr
            ::where('import_colt_id', $importColtId)
            ->where('status', 'RATE_NOT_FOUND')
            ->get();
    }

    public function findRateNotFoundAstppClient($clientId, $startDate, $endDate) {
        return Cdr
        ::whereNotNull('astpp_cdr_uniqueid')
        ->where('client_id', $client->id)
        ->whereBetween('callstart', [$startDate, $endDate])
        ->where('status', 'RATE_NOT_FOUND')
        ->get();
    }
}