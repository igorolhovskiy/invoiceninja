<?php

namespace Modules\ImportColt\Datatables;

use Utils;
use URL;
use Auth;
use App\Ninja\Datatables\EntityDatatable;

class ImportColtDatatable extends EntityDatatable
{
    public $entityType = 'importcolt';
    public $sortCol = 1;

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
                mtrans('importcolt', 'edit_importcolt'),
                function ($model) {
                    return URL::to("importcolt/{$model->public_id}/edit");
                },
                function ($model) {
                    return Auth::user()->can('editByOwner', ['importcolt', $model->user_id]);
                }
            ],
        ];
    }

}
