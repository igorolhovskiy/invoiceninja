<?php

namespace Modules\Telcorates\Repositories;

use DB;
use Modules\Telcorates\Models\Telcorates;
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

    public function find($filter = null, $userId = false)
    {
        $query = DB::table('telcorates')
                    ->where('telcorates.account_id', '=', \Auth::user()->account_id)
                    ->select(
                        'telcorates.name', 
                        'telcorates.public_id',
                        'telcorates.deleted_at',
                        'telcorates.created_at',
                        'telcorates.is_deleted',
                        'telcorates.user_id'
                    );

        $this->applyFilters($query, 'telcorates');

        if ($userId) {
            $query->where('clients.user_id', '=', $userId);
        }

        if ($filter) {
            $query->where('name', 'like', "%$filter%");
        }

        return $query;
    }

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

}
