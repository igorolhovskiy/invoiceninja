<?php

namespace Modules\Telcopackages\Datatables;

use Utils;
use URL;
use Auth;
use App\Ninja\Datatables\EntityDatatable;

class TelcopackagesDatatable extends EntityDatatable
{
    public $entityType = 'telcopackages';
    public $sortCol = 1;

    public function columns()
    {
        return [
            [
                'name',
                function ($model) {
                    return $model->name;
                }
            ],[
                'amount_of_minutes',
                function ($model) {
                    return $model->amount_of_minutes;
                }
            ],[
                'price',
                function ($model) {
                    return $model->price;
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
                mtrans('telcopackages', 'edit_telcopackages'),
                function ($model) {
                    return URL::to("telcopackages/{$model->public_id}/edit");
                },
                function ($model) {
                    return Auth::user()->can('editByOwner', ['telcopackages', $model->user_id]);
                }
            ],
        ];
    }

}
