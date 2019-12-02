<?php

namespace Modules\ExportSepa\Http\Requests;

use App\Http\Requests\EntityRequest;

class ExportSepaRequest extends EntityRequest
{
    protected $entityType = 'exportsepa';
}
