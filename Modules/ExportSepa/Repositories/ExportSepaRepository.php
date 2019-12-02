<?php

namespace Modules\ExportSepa\Repositories;

use DB;
use Modules\ExportSepa\Models\ExportSepa;
use App\Ninja\Repositories\BaseRepository;
//use App\Events\ExportsepaWasCreated;
//use App\Events\ExportsepaWasUpdated;

class ExportsepaRepository extends BaseRepository
{
    public function getClassName()
    {
        return 'Modules\ExportSepa\Models\ExportSepa';
    }

    public function all()
    {
        return ExportSepa::scope()
                ->orderBy('created_at', 'desc')
                ->withTrashed();
    }

    public function find($filter = null, $userId = false)
    {
        $query = ExportSepa::where('exportsepa.account_id', '=', \Auth::user()->account_id)
                    ->select(
                        'exportsepa.public_id',
                        'exportsepa.deleted_at',
                        'exportsepa.created_at',
                        'exportsepa.is_deleted',
                        'exportsepa.user_id',
                        DB::raw(
                            '(select sum(amount) from invoices ' 
                            . 'join exportsepa_items on invoices.id = exportsepa_items.invoice_id '
                            . 'where exportsepa_items.exportsepa_id = exportsepa.id)  as sum_invoices'
                        )
                    )
                    ->withCount('items');

        $this->applyFilters($query, 'exportsepa');

        if ($userId) {
            $query->where('clients.user_id', '=', $userId);
        }

        /*
        if ($filter) {
            $query->where();
        }
        */

        return $query;
    }

    public function save($data, $exportsepa = null)
    {
        $entity = $exportsepa ?: Exportsepa::createNew();
        
        $entity->save();

        $invoiceIds = \App\Models\Invoice::scope()
            ->whereIn('public_id', $data['invoice_id'])
            ->get()
            ->map(function($item) {
                return ['invoice_id' => $item->id];
            })
            ->all();
        if (count($invoiceIds)) {
            $entity->items()->createMany($invoiceIds);
        }

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
