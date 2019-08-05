<?php

namespace App\Ninja\Repositories;

use App\Models\Cdr;

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
        return DB::update('update `cdrs` inner join `clients` ' 
            . 'on FIND_IN_SET(cdrs.did, colt_dids) > 0 '
            . 'set `cdrs`.`client_id` = clients.id ' 
            . 'where `cdrs`.`account_id` = ? and `clients`.`account_id` = ? '
            . 'and cdrs.client_id is null',
            [\Auth::user()->account_id, \Auth::user()->account_id]);
    }
}