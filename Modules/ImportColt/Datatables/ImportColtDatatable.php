<?php

namespace Modules\ImportColt\Datatables;

use Utils;
use URL;
use Auth;
use App\Ninja\Datatables\EntityDatatable;
use Modules\ImportColt\Models\ImportColt;

class ImportColtDatatable extends EntityDatatable
{
    public $entityType = 'importcolt';
    public $isBulkEdit = false;
    public $hideClient = false;
    public $sortCol = 2;

    public function __construct() {}    

    public function columns()
    {
        return [
            [
                'name',
                function ($model) {
                    return $model->name;
                }
            ],
            [
                'invoice_date',
                function ($model) {
                    return $model->invoice_date;
                }
            ],        
            [
                'status',
                function ($model) {
                    return $model->status;
                }
            ],                
            [
                'created_at',
                function ($model) {
                    return Utils::fromSqlDateTime($model->created_at);
                }
            ],
        ];
    }

    public function actions()
    {
        return [
            [
                mtrans('importcolt', 'delete_importcolt'),
                function ($model) {
                    $confirmText = 'Delete Colt file with all cdr records and invoices.';
                    return "javascript:submitForm_importcolt('delete', {$model->public_id}, '{$confirmText}')";
                },
                function ($model) {
                    return Auth::user()->can('edit', ['importcolt', $model->user_id]);
                }
            ],            
            [
                mtrans('importcolt', 'renumber_importcolt'),
                function ($model) {
                    return URL::to("importcolt/{$model->public_id}/renumber-invoices");
                },
                function ($model) {
                    return Auth::user()->can('edit', ['importcolt', $model->user_id]);
                }
            ],
            [
                mtrans('importcolt', 'reprocess_file_importcolt'),
                function ($model) {
                    $confirmText = 'Reprocess COLT file. It will Regenerate CDR and recreate invoices.';
                    return "javascript:submitForm_importcolt('reprocessfile', {$model->public_id}, '{$confirmText}')";
                },
                function ($model) {
                    $importColtId = ImportColt::getPrivateId($model->public_id);
                    if (\App\Models\Invoice::scope()
                        ->whereHas('cdrs', function($query) use ($importColtId) {
                            $query->where('import_colt_id', $importColtId);
                        })
                        ->where('invoice_status_id', '<>', '1')
                        ->exists() ) {
                        return false;
                    }
                    
                    return Auth::user()->can('edit', ['importcolt', $model->user_id]);
                }
            ],            
            [
                mtrans('importcolt', 'send_invoices_importcolt'),
                function ($model) {
                    $confirmText = 'Send all invoices to email.';
                    return "javascript:submitForm_importcolt('emailInvoice', {$model->public_id}, '{$confirmText}')";
                },
                function ($model) {
                    return Auth::user()->can('edit', ['importcolt', $model->user_id]);
                }
            ],                                  
        ];
    }

    public function bulkActions()
    {
        return [];
    }    

}
