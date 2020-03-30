<?php

namespace Modules\ImportColt\Repositories;

use DB;
use Utils;

use Modules\ImportColt\Models\ImportColt;
use App\Ninja\Repositories\BaseRepository;
use App\Jobs\SendInvoiceEmail;
use Modules\ImportColt\Jobs\ParseColt;
//use App\Events\ImportcoltWasCreated;
//use App\Events\ImportcoltWasUpdated;

class ImportcoltRepository extends BaseRepository
{
    public function getClassName()
    {
        return 'Modules\ImportColt\Models\ImportColt';
    }

    public function all()
    {
        return ImportColt::scope()
                ->orderBy('created_at', 'desc')
                ->withTrashed();
    }

    public function find($filter = null, $userId = false)
    {
        $query = DB::table('import_colts')
                    ->where('import_colts.account_id', '=', \Auth::user()->account_id)
                    ->select(
                        'import_colts.name', 
                        'import_colts.file_path',
                        'import_colts.invoice_date',
                        'import_colts.status',  
                        'import_colts.public_id',
                        'import_colts.deleted_at',
                        'import_colts.created_at',
                        'import_colts.is_deleted',
                        'import_colts.user_id'
                    );

        $this->applyFilters($query, 'import_colt');

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

    public function save($data, $importcolt = null)
    {
        $entity = $importcolt ?: ImportColt::createNew();

        $data['invoice_date'] = Utils::toSqlDate($data['invoice_date']);

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

    public function getById($id)
    {
        return ImportColt::scope()
            ->findOrFail($id);
    }

    /**
     * @param $entity
     */
    public function delete($entity)
    {
        abort_if (\App\Models\Invoice::scope()
            ->whereHas('cdrs', function($query) use ($entity) {
                $query->where('import_colt_id', $entity->id);
            })
            ->where('invoice_status_id', '<>', '1')
            ->exists(),
        403, 'It is exist the Invoice with status not Draft');
        // Delete invoices
        $invoices = \App\Models\Invoice::scope()
            ->whereHas('cdrs', function($query) use ($entity) {
                $query->where('import_colt_id', $entity->id);
            });
        foreach($invoices->get() as $invoice) {
            $invoice->invoice_items()->forceDelete();
        }
        $invoices->forceDelete();
        // Delete cdrs
        \App\Models\Cdr::scope()
            ->where('import_colt_id', $entity->id)
            ->forceDelete();

        // Delete import colt
        $entity->forceDelete();
    }

    /**
     * @param $entity
     */
    public function emailInvoice($entity)
    {
        $invoices = \App\Models\Invoice::scope()
        ->whereHas('cdrs', function($query) use ($entity) {
            $query->where('import_colt_id', $entity->id);
        });
        foreach($invoices->get() as $invoice) {
            dispatch(new SendInvoiceEmail($invoice));
        }
    }

    /**
     * @param $entity
     */
    public function reprocessfile($entity)
    {
        abort_if (\App\Models\Invoice::scope()
            ->whereHas('cdrs', function($query) use ($entity) {
                $query->where('import_colt_id', $entity->id);
            })
            ->where('invoice_status_id', '<>', '1')
            ->exists(),
        403, 'It is exist the Invoice with status not Draft');
        // Delete invoices
        $invoices = \App\Models\Invoice::scope()
        ->whereHas('cdrs', function($query) use ($entity) {
            $query->where('import_colt_id', $entity->id);
        });
        foreach($invoices->get() as $invoice) {
            $invoice->invoice_items()->forceDelete();
        }
        $invoices->forceDelete();
        $entity->update([
            'status' => 'new'
        ]);
        // Delete cdrs
        \App\Models\Cdr::scope()
            ->where('import_colt_id', $entity->id)
            ->forceDelete();
        $job = new ParseColt(\Auth::user(), $entity);
        dispatch($job);
    }
}
