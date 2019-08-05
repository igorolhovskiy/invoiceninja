<?php

namespace Modules\ImportColt\Http\Requests;

use App\Http\Requests\EntityRequest;

class ImportColtRequest extends EntityRequest
{
    protected $entityType = 'importcolt';
}
