<?php

namespace Modules\ExportSepa\Datatables;

use Utils;
use URL;
use Auth;
use App\Ninja\Datatables\EntityDatatable;

class ExportSepaDatatable extends EntityDatatable
{
    public $entityType = 'exportsepa';
    public $sortCol = 1;

    public function columns()
    {
        return [
            [
                'created_at',
                function ($model) {
                    return Utils::fromSqlDateTime($model->created_at);
                }
            ],
            [
                'number_invoices',
                function ($model) {
                    return $model->items_count;
                }
            ],
            [
                'sum_invoices',
                function ($model) {
                    return $model->sum_invoices;
                }
            ]            
        ];
    }

    public function actions()
    {
        return [
            [
                mtrans('exportsepa', 'view_exportsepa'),
                function ($model) {
                    return URL::to("exportsepa/{$model->public_id}");
                },
                function ($model) {
                    return Auth::user()->can('editByOwner', ['exportsepa', $model->user_id]);
                }
            ],
            [
                mtrans('exportsepa', 'generate_sepa_xml'),
                function ($model) {
                    return URL::to("exportsepa/{$model->public_id}/generate-sepa");
                },
                function ($model) {
                    return Auth::user()->can('editByOwner', ['exportsepa', $model->user_id]);
                }
            ],            
        ];
    }

}
