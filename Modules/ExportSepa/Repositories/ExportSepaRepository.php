<?php

namespace Modules\ExportSepa\Repositories;

use DB;
use Modules\ExportSepa\Models\ExportSepa;
use App\Ninja\Repositories\BaseRepository;
use App\Ninja\Repositories\PaymentRepository;
//use App\Events\ExportsepaWasCreated;
//use App\Events\ExportsepaWasUpdated;

class ExportsepaRepository extends BaseRepository
{
    public function __construct(PaymentRepository $paymentRepo)
    {
        $this->paymentRepo = $paymentRepo;
    }

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

    public function find($filter = null)
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

        $account = $entity->account;
        $endtoendid = $account->sepa_end_to_end_current_id;
        $invoiceIds = \App\Models\Invoice::scope()
            ->whereIn('public_id', $data['invoice_id'])
            ->get()
            ->map(function($item) use ($account) {
                $account->sepa_end_to_end_current_id += 1;
                return [
                    'invoice_id' => $item->id,
                    'endtoendid' => $account->sepa_end_to_end_current_id
                ];
            })
            ->all();
        if (count($invoiceIds)) {
            $entity->items()->createMany($invoiceIds);
            foreach($entity->items as $item) {
                $data = [
                    'client_id' => $item->invoice->client_id,
                    'invoice_id' => $item->invoice->id,
                    'amount' => $item->invoice->balance,
                    'payment_type_id' => PAYMENT_TYPE_SEPA
                ];
                $this->paymentRepo->save($data);
            }
            $account->save();
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
