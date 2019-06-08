<?php

namespace Modules\Telcopackages\Repositories;

use DB;
use Modules\Telcopackages\Models\Telcopackages;
use App\Ninja\Repositories\BaseRepository;
//use App\Events\TelcopackagesWasCreated;
//use App\Events\TelcopackagesWasUpdated;

class TelcopackagesRepository extends BaseRepository
{
    public function getClassName()
    {
        return 'Modules\Telcopackages\Models\Telcopackages';
    }

    public function all()
    {
        return Telcopackages::scope()
                ->orderBy('created_at', 'desc')
                ->withTrashed();
    }

    public function find($filter = null, $userId = false)
    {
        $query = DB::table('telcopackages')
                    ->where('telcopackages.account_id', '=', \Auth::user()->account_id)
                    ->select(
                        'telcopackages.name', 'telcopackages.amount_of_minutes', 'telcopackages.price', 
                        'telcopackages.public_id',
                        'telcopackages.deleted_at',
                        'telcopackages.created_at',
                        'telcopackages.is_deleted',
                        'telcopackages.user_id'
                    );

        $this->applyFilters($query, 'telcopackages');

        if ($userId) {
            $query->where('clients.user_id', '=', $userId);
        }

        if ($filter) {
            $query->where(function ($query) use ($filter) {
                $query->where('name', 'like', "%$filter%")
                    ->orWhere('amount_of_minutes', '=', $filter)
                    ->orWhere('price', '=', $filter);
            });
        }

        return $query;
    }

    public function save($data, $telcopackages = null)
    {
        $entity = $telcopackages ?: Telcopackages::createNew();
    
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

}
