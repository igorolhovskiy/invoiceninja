<?php

namespace Modules\Telcorates\Repositories;

use DB;
use Modules\Telcorates\Models\Telcorates;
use Modules\Telcorates\Models\TelcorateCode;
use App\Ninja\Repositories\BaseRepository;
//use App\Events\TelcoratesWasCreated;
//use App\Events\TelcoratesWasUpdated;

class TelcoratesRepository extends BaseRepository
{
    public function getClassName()
    {
        return 'Modules\Telcorates\Models\Telcorates';
    }

    public function all()
    {
        return Telcorates::scope()
                ->orderBy('created_at', 'desc')
                ->withTrashed();
    }

    public function find($filter = null)
    {
        $query = DB::table('telcorates')
                    ->where('telcorates.account_id', '=', \Auth::user()->account_id)
                    ->select(
                        'telcorates.name',
                        'telcorates.description',
                        'telcorates.public_id',
                        'telcorates.deleted_at',
                        'telcorates.created_at',
                        'telcorates.is_deleted',
                        'telcorates.user_id'
                    );

        $this->applyFilters($query, 'telcorates');

        if ($filter) {
            $query->where(function ($filterQuery) use ($filter) {
                $filterQuery->where('name', 'like', "%$filter%")
                    ->orWhere('description', 'like', "%$filter%");
            });
        }
        return $query;
    }

    /**
     * Save new telcorate with codes
     */
    public function save($data, $telcorates = null)
    {
        $entity = $telcorates ?: Telcorates::createNew();

        $entity->fill($data);
        $entity->save();
        $codeIds = [];
        foreach ($data['codes'] as $item) {
            if (empty($item['id'])) {
                $code = $entity->codes()->create($item);
                $codeIds[] = $code->id;
            } else {
                $entity->codes()->find($item['id'])->update($item);
                $codeIds[] = $item['id'];
            }
        }
        $entity->codes()->whereNotIn('id', $codeIds)->delete();
        /*
        if (!$publicId || intval($publicId) < 0) {
            event(new ClientWasCreated($client));
        } else {
            event(new ClientWasUpdated($client));
        }
        */

        return $entity;
    }

    /**
     * Update telcorate header
     */
    public function update($data, $telcorates = null)
    {
        $entity = $telcorates ?: Telcorates::createNew();

        $entity->fill($data);
        $entity->save();
        /*
        if (!$publicId || intval($publicId) < 0) {
            event(new ClientWasCreated($client));
        } else {
            event(new ClientWasUpdated($client));
        }
        */

        return $entity;
    }

    public function checkActiveClient($id) {
        $client = \App\Models\Client::scope()
            ->whereHas('invoices', function($query) use ($id) {
                $query->whereHas('invoice_items', function($queryItems) use ($id) {
                    $queryItems->where('product_type', 'telcorates')
                        ->where('product_id', $id);
                });
            })
            ->first();

        return $client;
    }
    
    public function getCode($telcorate, $search)
    {
        return TelcorateCode
            ::where('telcorate_id', '=', $telcorate->id)
            ->when($search, function($query, $search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('code', 'like', "%$search%")
                        ->orWhere('description', 'like', "%$search%")
                        ->orWhere('rate', 'like', "%$search%");
                });
            })
            ->orderBy('code')
            ->paginate(10);
    }

    public function addCode($telcorate, $data)
    {
        return $telcorate->codes()->create($data);
    }

    public function uploadCodes($telcorate, $codes)
    {
        foreach ($codes as $item) {
            $telcorate->codes()->create($item);
        }
    }

    public function updateCode($telcorate, $data)
    {
        return TelcorateCode
            ::where('telcorate_id', '=', $telcorate->id)
            ->where('id', '=', $data['id'])
            ->update([
                'code' => $data['code'],
                'init_seconds' => $data['init_seconds'],
                'increment_seconds' => $data['increment_seconds'],
                'rate' => $data['rate'],
                'description' => $data['description']
            ]);
    }

    public function deleteCode($telcorate, $codeId)
    {
        return TelcorateCode
            ::where('telcorate_id', '=', $telcorate->id)
            ->where('id', '=', $codeId)
            ->delete();
    }
}
