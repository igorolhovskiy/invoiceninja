<?php

namespace Modules\Telcorates\Datatables;

use Utils;
use URL;
use Auth;
use App\Ninja\Datatables\EntityDatatable;

class TelcoratesDatatable extends EntityDatatable
{
    public $entityType = 'telcorates';
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
                mtrans('telcorates', 'edit_telcorates'),
                function ($model) {
                    return URL::to("telcorates/{$model->public_id}/edit");
                },
                function ($model) {
                    return Auth::user()->can('edit', ['telcorates', $model->user_id]);
                }
            ],
        ];
    }

}
