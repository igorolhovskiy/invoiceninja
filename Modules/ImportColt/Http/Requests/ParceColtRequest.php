<?php

namespace Modules\ImportColt\Http\Requests;

use App\Http\Requests\EntityRequest;

class ParceColtRequest extends EntityRequest
{
    protected $entityType = 'importcolt';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|file|mimes:csv,txt'
        ];
    }    
}
